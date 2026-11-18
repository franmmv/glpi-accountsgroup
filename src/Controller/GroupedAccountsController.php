<?php

namespace GlpiPlugin\Accountsgroup\Controller;

use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\DependencyInjection\PublicService;
use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Accounts\Account;
use GlpiPlugin\Accounts\Accountstate;
use GlpiPlugin\Accounts\Accounttype;
use Plugin;
use Search;
use Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GroupedAccountsController implements PublicService
{
    #[Route('/grouped-accounts', name: 'grouped_accounts', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        global $DB;

        Session::checkLoginUser();
        if (!plugin_accountsgroup_accounts_supported() || !Plugin::isPluginLoaded('accounts') || !Account::canView()) {
            throw new AccessDeniedHttpException();
        }
        Account::checkReAuthenticationOrRedirect();

        // Keep the native search filters and sort. Ignore all caller-supplied output and scope parameters.
        $keys = array_flip(['criteria', 'metacriteria', 'sort', 'order', 'is_deleted', 'nodefault']);
        $params = array_intersect_key($request->query->all(), $keys)
            + array_intersect_key($_SESSION['glpisearch'][Account::class] ?? [], $keys);
        // GLPI may write defaults even with usesession=false. Keep the native table's session intact.
        $saved_search = $_SESSION['glpisearch'][Account::class] ?? null;
        try {
            $params = Search::manageParams(Account::class, $params, false);
            $data = Search::getDatas(Account::class, array_replace($params, [
                'display_type' => Search::HTML_OUTPUT,
                'export_all' => 1,
                'start' => 0,
            ]));
        } finally {
            if ($saved_search === null) {
                unset($_SESSION['glpisearch'][Account::class]);
            } else {
                $_SESSION['glpisearch'][Account::class] = $saved_search;
            }
        }

        if (!isset($data['data']['totalcount'])) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $rows = $data['data']['rows'] ?? [];
        $fields = [];
        // Fetch only grouping attributes for IDs returned by Accounts' permission-scoped search.
        $visibility = Account::getVisibilityCriteria();
        foreach (array_chunk(array_column($rows, 'id'), 500) as $ids) {
            $where = ['id' => $ids];
            if ($visibility !== []) {
                $where[] = $visibility;
            }
            foreach ($DB->request([
                'SELECT' => ['id', 'plugin_accounts_accounttypes_id', 'plugin_accounts_accountstates_id'],
                'FROM' => Account::getTable(),
                'WHERE' => $where,
            ]) as $account) {
                $fields[(int) $account['id']] = $account;
            }
        }

        $groups = [];
        $type_names = [0 => __('None')];
        $state_names = [0 => __('None')];
        foreach ($rows as $row) {
            $account = $fields[(int) $row['id']] ?? null;
            if ($account === null) {
                continue;
            }
            $type = (int) ($account['plugin_accounts_accounttypes_id'] ?? 0);
            $state = (int) ($account['plugin_accounts_accountstates_id'] ?? 0);
            $type_names[$type] ??= Dropdown::getDropdownName(Accounttype::getTable(), $type, tooltip: false, default: __('None'));
            $state_names[$state] ??= Dropdown::getDropdownName(Accountstate::getTable(), $state, tooltip: false, default: __('None'));
            $groups[$type]['name'] = $type_names[$type];
            $groups[$type]['count'] = ($groups[$type]['count'] ?? 0) + 1;
            $groups[$type]['states'][$state]['name'] = $state_names[$state];
            $groups[$type]['states'][$state]['rows'][] = $row;
        }
        $by_name = static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']);
        uasort($groups, $by_name);
        foreach ($groups as &$group) {
            uasort($group['states'], $by_name);
        }
        unset($group);

        return new Response(
            TemplateRenderer::getInstance()->render('@accountsgroup/grouped.html.twig', [
                'columns' => array_filter(
                    $data['data']['cols'] ?? [],
                    static fn(array $column): bool => ($column['searchopt']['field'] ?? '') !== 'encrypted_totp_secret',
                ),
                'groups' => $groups,
            ]),
            headers: ['Cache-Control' => 'no-store'],
        );
    }
}
