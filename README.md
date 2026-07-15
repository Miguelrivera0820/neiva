# ARBIMaps Neiva

Aplicación PHP/MySQL para gestión catastral y operativa de la ciudad de Neiva. El proyecto funciona sobre PHP 8.2 y concentra módulos de trámites, seguimiento, soporte, usuarios, certificados catastrales, restricciones de predios y gestión de personal.

---

## Estado y Seguridad

El sistema cuenta con una arquitectura de seguridad endurecida:
- **Centralización de Configuración:** Toda configuración sensible (credenciales, URLs base, rutas de almacenamiento y zona horaria) se carga desde el archivo `.env` mediante `Arbimaps/includes/bootstrap.php`.
- **Sesiones Seguras:** La sesión del sistema (`NEIVASESSID`) se inicializa automáticamente y de forma segura configurando los flags `HttpOnly`, `Secure` y `SameSite=Lax` para mitigar ataques CSRF y XSS.
- **Rutas Dinámicas:** No existen rutas locales absolutas hardcodeadas ni dependencias rígidas de aliases `/arbimaps`. Toda URL y path local se calcula en runtime.

---

## Estructura y Helpers Principales

El archivo central de inicialización es [bootstrap.php](file:///c:/xampp/htdocs/neiva/Arbimaps/includes/bootstrap.php). Este provee las siguientes utilidades globales:

### Resolutores de Rutas y Archivos
- `neiva_public_path(string $relativePath = '')`: Retorna la ruta física absoluta dentro de la raíz pública del proyecto.
- `neiva_private_path(string $relativePath = '')`: Retorna la ruta física absoluta dentro del almacenamiento privado y seguro.
- `neiva_temp_path(string $relativePath = '')`: Retorna la ruta física absoluta dentro del directorio de archivos temporales.
- `neiva_document_path(string $relativePath = '')`: Retorna la ruta física absoluta dentro de la carpeta de documentos y soportes del sistema.

### URLs y Redirecciones
- `neiva_app_base_url()`: Retorna la URL base configurada (o autodetectada).
- `neiva_app_url(string $path = '')`: Genera una URL absoluta y limpia hacia cualquier recurso del sistema.
- `neiva_redirect(string $url)`: Redirige al navegador de forma segura resolviendo URLs dinámicas y finaliza la ejecución del script inmediatamente.

---

## Requisitos del Sistema

- PHP 8.2+
- MySQL/MariaDB
- Apache 2.4+ (con `mod_rewrite` habilitado)
- Composer

---

## Guía de Instalación y Despliegue

La documentación detallada para realizar una instalación desde cero en entornos locales, guías de permisos de carpetas y listas de verificación de paso a servidores de producción se encuentra en:

👉 [**Guía de Despliegue Seguro (DEPLOYMENT.md)**](file:///c:/xampp/htdocs/neiva/DEPLOYMENT.md)

---

## Comandos de Mantenimiento y Auditoría

### Auditoría de Contraseñas
Ejecuta un reporte de la base de datos para identificar usuarios cuyas contraseñas no utilicen hashes modernos:
```bash
C:\xampp\php\php.exe scripts/audit_passwords.php
```

### Auditoría de Seguridad (Fase 4)
Escanea el código fuente buscando posibles secretos duros, referencias locales absolutas obsoletas o sesiones manuales:
```bash
C:\xampp\php\php.exe scripts/audit_phase4.php
```

### Corrección Automática de Rutas Heredadas
Sanea y reemplaza URLs antiguas `/arbimaps` o `/Arbimaps` por el helper `neiva_app_url()` dinámico:
```bash
C:\xampp\php\php.exe scripts/fix_phase4_paths.php --write
```
