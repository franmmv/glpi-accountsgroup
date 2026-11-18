# AccountsGroup para GLPI

Complemento para [Accounts de InfotelGLPI](https://github.com/InfotelGLPI/accounts). Añade al listado de **Cuentas** una vista agrupada por **Tipo → Estado**. Usa los filtros, el orden y las reglas de acceso del listado de Accounts.

## Instalación

1. Comprueba que usas GLPI **12** y que **Accounts 3.3.0** está instalado y activado.
2. Descarga `accountsgroup-<versión>.zip` desde [Releases](https://github.com/franmmv/glpi-accountsgroup/releases). Descomprime el ZIP y copia `accountsgroup/` al directorio `plugins/` de GLPI.
3. En **Configuración → Plugins**, instala y activa **Agrupación de cuentas**.

## Uso

- Marca la casilla de agrupación en la barra de Cuentas. Se cargan todos los resultados de la búsqueda, sin paginación. Tipo y Estado aparecen plegados y muestran el número de cuentas de cada grupo.
- Usa la doble flecha para desplegar o contraer todos los grupos. Desmarca la casilla para volver a la tabla habitual.
- En **Preferencias de agrupación**, marca **Usar como vista predeterminada** para abrir Cuentas en modo agrupado.
- Desmarca **Mostrar botón «Vista por tipo»** para ocultar el botón original de Accounts. La opción solo aparece si Accounts muestra ese botón a tu perfil. Las dos preferencias se guardan en el navegador.

El complemento funciona en solo lectura y excluye la columna de secreto TOTP de su vista agrupada. Los cambios de datos y las acciones masivas se hacen desde el listado habitual.

## Licencia

[GPL-3.0-or-later](LICENSE).
