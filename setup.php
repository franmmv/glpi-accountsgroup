<?php

function plugin_init_accountsgroup(): void
{
    global $PLUGIN_HOOKS;

    if (!plugin_accountsgroup_accounts_supported()) {
        return;
    }

    $PLUGIN_HOOKS[Glpi\Plugin\Hooks::ADD_JAVASCRIPT]['accountsgroup'] = 'js/grouping.js';
}

function plugin_version_accountsgroup(): array
{
    return [
        'name' => 'Agrupación de cuentas',
        'version' => '1.0.0',
        'author' => 'Fran M.',
        'license' => 'GPL-3.0-or-later',
        'requirements' => ['glpi' => [
            'min' => '12.0.0',
            'max' => '12.99.99',
            'plugins' => ['accounts'],
        ]],
    ];
}

function plugin_accountsgroup_accounts_supported(): bool
{
    return Plugin::isPluginActive('accounts') && Plugin::getPluginFilesVersion('accounts') === '3.3.0';
}

function plugin_accountsgroup_check_prerequisites(): bool
{
    if (plugin_accountsgroup_accounts_supported()) {
        return true;
    }

    echo 'Se requiere Accounts 3.3.0 instalado y activado / Accounts 3.3.0 must be installed and enabled.';
    return false;
}
