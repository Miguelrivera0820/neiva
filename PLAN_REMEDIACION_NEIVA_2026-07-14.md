# Plan de remediacion pendiente - Neiva

## Estado actual

El proyecto ya tiene una base inicial de endurecimiento:

- `bootstrap.php` centraliza sesion, autenticacion, permisos, CSRF y utilidades de rutas.
- `login.php`, `logout.php`, `conexion.php` e `Arbimaps/index.php` ya fueron reforzados.
- Se corrigieron los flujos mas expuestos de usuarios, soporte, perfil, restricciones de predios y parte de base catastral.
- El acceso por URL a paginas registradas en el router ahora pasa por validacion de autenticacion y permisos.

Sin embargo, el sistema todavia no esta listo para produccion. La deuda restante sigue siendo importante en modulos legacy y endpoints directos.

## Hallazgos pendientes confirmados

Pendientes aun visibles en el codigo y estructura actual:

1. `dat_neiva` sigue bajo el arbol publico y `predios.json` todavia se referencia desde varias vistas.
2. La base activa todavia tiene usuarios con contrasenas sin hash moderno.
3. `composer.lock` sigue apuntando a `phpoffice/phpspreadsheet` `2.1.11`, aunque `composer.json` ya fue ajustado.
4. Persisten muchas rutas hardcodeadas a `/arbimaps/Arbimaps` o variantes similares.
5. Persisten muchos archivos con `session_start()` manual en lugar de usar el bootstrap comun.
6. Persisten muchos endpoints con respuestas `die(...)`, mensajes SQL internos o errores tecnicos visibles.
7. Aun hay cargas y lecturas de archivos dentro del arbol publico.

Indicadores rapidos del barrido actual:

- Aproximadamente `90` archivos aun contienen rutas hardcodeadas a `/arbimaps` o `/Arbimaps`.
- Aproximadamente `136` archivos aun contienen `session_start()`.
- Aproximadamente `135` archivos aun exponen `die(...)`, `$mysqli->error` o `$stmt->error`.
- Hay al menos `4` referencias directas a `dat_neiva/predios.json`.

## Plan por fases

### Fase 1 - Cierre de exposicion publica

Objetivo: impedir descargas directas de datos, documentos internos y rutas tecnicas.

Tareas:

1. Mover `dat_neiva` fuera de `C:\xampp\htdocs\neiva`.
2. Mover documentos subidos y repositorios documentales a almacenamiento privado.
3. Reemplazar lecturas directas de `predios.json` por acceso controlado desde backend o rutas privadas.
4. Revisar carpetas publicas como `DOCUMENTOS`, logs y soportes para eliminar acceso directo por URL.
5. Validar que `.htaccess` o la configuracion de Apache bloquee SQL, logs, respaldos, JSON internos y archivos tecnicos.

Criterio de cierre:

- Ningun archivo sensible debe ser descargable desde `http://localhost/neiva/...`.
- Las vistas no deben depender de `../dat_neiva/predios.json`.

### Fase 2 - Credenciales y librerias vulnerables

Objetivo: cerrar el riesgo de cuentas inseguras y dependencias conocidas como vulnerables.

Tareas:

1. Identificar usuarios con contrasena sin hash.
2. Forzar reseteo o migracion controlada de todas las contrasenas restantes.
3. Confirmar que todos los flujos de alta, cambio y login usen exclusivamente `password_hash()` y `password_verify()`.
4. Actualizar `composer.lock` y la carpeta `vendor` para salir de PhpSpreadsheet `2.1.11`.
5. Retirar copias manuales o duplicadas de PhpSpreadsheet si ya no son necesarias.

Criterio de cierre:

- Ningun registro de usuario queda con contrasena en texto plano.
- `composer.lock` deja de referenciar la version vulnerable.

### Fase 3 - Estandarizacion de auth, permisos y CSRF

Objetivo: que ningun endpoint sensible funcione fuera del flujo comun de seguridad.

Tareas:

1. Barrer endpoints directos que aun usan `session_start()` manual.
2. Migrarlos a `require bootstrap`, `neiva_require_auth()`, `neiva_require_permission()` y `neiva_require_csrf()` segun el caso.
3. Convertir operaciones destructivas y de escritura a `POST` o `POST + JSON`.
4. Revisar especialmente los modulos:
   - `seguimiento/acciones`
   - `tramites/acciones`
   - `soporte/acciones`
   - `cuentas/acciones` y `cuentas/radicacion/acciones`
   - `Personal/acciones`
   - `baqueanos/solicitudes/acciones`
5. Definir una matriz minima permiso -> endpoint para no depender solo del menu visual.

Criterio de cierre:

- Ningun endpoint de escritura ejecuta acciones sin sesion valida, permiso y CSRF.
- Un usuario sin permiso recibe `403` en modulos restringidos.

### Fase 4 - Normalizacion de rutas y despliegue local

Objetivo: eliminar quiebres por asumir instalacion en `/arbimaps`.

Tareas:

1. Reemplazar rutas hardcodeadas por `neiva_app_url()`, `neiva_app_base_url()` o helpers equivalentes.
2. Reemplazar includes con `$_SERVER['DOCUMENT_ROOT'] . '/arbimaps/...` por rutas relativas robustas.
3. Revisar JS, formularios, redirecciones, `fetch`, assets e impresiones PDF/HTML.
4. Priorizar modulos con mas ocurrencias:
   - `soporte`
   - `Personal`
   - `cuentas`
   - `tramites`
   - `Licitaciones`
   - `baqueanos`

Criterio de cierre:

- La app debe funcionar igual en `http://localhost/neiva` sin depender de aliases.

### Fase 5 - Manejo seguro de errores y archivos

Objetivo: reducir filtracion de informacion interna y endurecer almacenamiento.

Tareas:

1. Sustituir `die(...)` tecnicos y mensajes con `$mysqli->error` por errores genericos al usuario y `error_log(...)`.
2. Centralizar validacion de cargas usando helpers del bootstrap.
3. Revisar endpoints que aun escriben o leen archivos publicos sin control estricto.
4. Donde aplique, devolver JSON consistente o abortos controlados con `neiva_abort()`.

Criterio de cierre:

- El usuario final no ve errores SQL ni rutas internas.
- Ninguna carga permite confiar en extensiones o MIME del navegador.

### Fase 6 - Ordenamiento del proyecto

Objetivo: dejar una base mantenible para estabilizacion y salida a produccion.

Tareas:

1. Eliminar modulos retirados, backups y archivos de prueba que ya no deban existir.
2. Separar configuracion local de configuracion versionable.
3. Crear `.env.example` y documentar variables reales requeridas.
4. Corregir `README`, pipeline y comandos de validacion.
5. Preparar una bateria minima de pruebas de humo para login, permisos, tramites y soporte.

Criterio de cierre:

- El repositorio refleja el alcance real del sistema y puede desplegarse con un procedimiento claro.

## Orden recomendado de ejecucion

1. Fase 1
2. Fase 2
3. Fase 3
4. Fase 4
5. Fase 5
6. Fase 6

## Siguiente bloque sugerido

El siguiente bloque mas rentable es:

1. mover `dat_neiva` y documentos fuera del arbol publico,
2. barrer rutas hardcodeadas en `soporte`,
3. migrar `seguimiento/acciones` al bootstrap comun.

Ese orden reduce riesgo real inmediato y, al mismo tiempo, ataca las dos fuentes de errores que ya aparecieron en pruebas locales: rutas rotas y endpoints legacy sin validaciones comunes.
