# Guía de Despliegue Seguro e Instalación - ARBIMaps Neiva

Este documento detalla los pasos para realizar una instalación limpia del sistema, configurarlo adecuadamente por entorno (Desarrollo vs. Producción) y asegurar que no existan filtraciones de credenciales ni accesos públicos indebidos en servidores.

---

## 1. Requisitos Previos del Sistema

- **PHP 8.2+** con las siguientes extensiones instaladas y activas:
  - `openssl` (seguridad y tokens)
  - `mysqli` (conexión a base de datos)
  - `fileinfo` (detección segura de tipos MIME en cargues)
  - `zip` (requerido para manipulación de reportes y plantillas Excel/Word)
  - `gd` o `imagick` (para procesamiento de imágenes si aplica)
- **Composer** (gestor de dependencias de PHP)
- **Servidor Web:** Apache 2.4+ (con `mod_rewrite` habilitado para archivos `.htaccess`) o Nginx.
- **Base de Datos:** MySQL 8.0+ o MariaDB 10.4+.

---

## 2. Instalación Limpia desde el Repositorio

Siga este procedimiento para desplegar el sistema en un nuevo servidor:

### Paso 1: Clonar el Repositorio
Clone el repositorio de manera privada en el directorio de servicio del servidor (ej. `/var/www/html/neiva` o `C:\xampp\htdocs\neiva`):
```bash
git clone git@github.com:tu-organizacion/neiva.git
cd neiva
```

### Paso 2: Crear y Configurar las Variables de Entorno
Copie el archivo de ejemplo para crear el archivo `.env` operativo:
```bash
cp .env.example .env
```
Edite `.env` y configure los parámetros reales del servidor:
- **`APP_ENV`:** Establezca en `production` en el servidor final.
- **`APP_DEBUG`:** Establezca en `false` para deshabilitar la visualización de errores detallados.
- **`APP_BASE_URL`:** URL base del aplicativo (ej. `https://neiva.arbimaps.com` o `http://localhost/neiva`).
- **`PRIVATE_STORAGE_PATH`:** Ruta física absoluta fuera de la carpeta pública del servidor web (ej. `/var/www/neiva_private_storage` o `C:\neiva_private_storage`).
- **`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`:** Credenciales de la base de datos de producción.

### Paso 3: Instalar Dependencias de Composer
Instale las librerías necesarias ejecutando:
```bash
composer install --no-dev --optimize-autoloader
```
*(Nota: El uso de `--no-dev` y `--optimize-autoloader` optimiza el rendimiento y elimina dependencias de desarrollo no requeridas en producción).*

### Paso 4: Inicializar la Base de Datos
Restaurar un volcado de base de datos limpio y seguro. 
> [!WARNING]
> No restaure volcados con contraseñas reales de prueba ni contraseñas en texto plano. Asegúrese de que todas las cuentas administrativas y operativas utilicen contraseñas seguras hasheadas con `password_hash()`.

### Paso 5: Permisos de Carpetas y Directorios (Linux/Apache)
Configure los permisos correctos en el servidor para evitar que el servidor web escriba en directorios de código, permitiéndole escribir únicamente en carpetas de datos temporales y de almacenamiento privado:

1. **Código fuente (solo lectura para el servidor web):**
   ```bash
   chown -R root:www-data /var/www/html/neiva
   find /var/www/html/neiva -type d -exec chmod 755 {} \;
   find /var/www/html/neiva -type f -exec chmod 644 {} \;
   ```
2. **Directorio temporal local (lectura/escritura para el servidor web):**
   ```bash
   chmod -R 775 /var/www/html/neiva/tmp
   chown -R www-data:www-data /var/www/html/neiva/tmp
   ```
3. **Almacenamiento Privado de Documentos (fuera de la carpeta pública web, lectura/escritura):**
   ```bash
   mkdir -p /var/www/neiva_private_storage
   chown -R www-data:www-data /var/www/neiva_private_storage
   chmod -R 770 /var/www/neiva_private_storage
   ```

---

## 3. Checklist de Publicación en GitHub (Repositorio Privado)

Antes de subir commits o hacer push al repositorio remoto en GitHub:
- [ ] **Validar `.gitignore`:** Asegurarse de que el archivo `.env`, archivos con extensiones `.sql`, `.log`, `.bak`, directorios de runtime (`tmp/`, `dat_neiva/`, `neiva_private_storage/`, `vendor/`) no estén siendo rastreados por Git.
- [ ] **Comprobar archivos en caché de Git:** Si alguno de los archivos ignorados fue subido anteriormente, retírelo de la caché sin eliminarlo físicamente:
  ```bash
  git rm --cached .env
  git rm -r --cached Arbimaps/DOCUMENTOS/
  ```
- [ ] **Auditar secretos:** Ejecutar el script de auditoría provisto en el sistema:
  ```bash
  C:\xampp\php\php.exe scripts/audit_phase4.php
  ```
  *(Asegúrese de que la salida del script no reporte claves duras, contraseñas ni URLs tipo `localhost` o `C:\xampp` en la rama principal).*
- [ ] **Configurar Repositorio Privado:** Confirmar que la visibilidad del repositorio en GitHub esté configurada como **Private**.

---

## 4. Checklist de Paso a Producción

Durante la ventana de lanzamiento al servidor de producción:
- [ ] **HTTPS Forzado:** Verificar que el servidor cuente con certificado SSL/TLS (ej. Let's Encrypt) y que se fuerce el tráfico HTTPS mediante configuración de Apache (`.htaccess`) o el proxy reverso.
- [ ] **Variables de Producción en `.env`:**
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `DB_PASSWORD` configurada con contraseña robusta de alta entropía.
- [ ] **Permisos del Filesystem:** Confirmar que el directorio `PRIVATE_STORAGE_PATH` esté ubicado completamente fuera de la carpeta pública de Apache y que sus permisos impidan el listado de archivos por parte de otros usuarios del sistema.
- [ ] **Bloqueos HTTP en Apache:** Validar que el archivo `.htaccess` esté configurado en el raíz del proyecto para denegar acceso directo a archivos `.env`, archivos de log, scripts de mantenimiento y volcados SQL:
  ```apache
  <FilesMatch "^\.env|.*\.log|.*\.sql|.*\.bak$">
      Order allow,deny
      Deny from all
  </FilesMatch>
  ```
- [ ] **Desactivar listado de directorios:** Asegurar que `Options -Indexes` esté configurado en el servidor web.
- [ ] **Remover scripts de prueba y scripts de mantenimiento:** Asegurar que archivos como `scripts/audit_phase4.php`, `scripts/fix_phase4_paths.php` y scripts legacy de prueba en vistas estén borrados o inaccesibles en producción.

---

## 5. Validaciones Post-Arranque

Una vez la aplicación esté en línea en producción, valide el funcionamiento con las siguientes comprobaciones:

1. **Verificación de Inicio de Sesión y Sesiones Seguras:**
   - Inicie sesión en el sistema y verifique en la consola de desarrollo del navegador que la cookie `NEIVASESSID` tenga las propiedades `HttpOnly`, `Secure` (si está en HTTPS) y `SameSite=Lax`.
2. **Prueba de Depuración (Debug Mode):**
   - Intente provocar un error menor (ej. acceder a una página inexistente o pasar parámetros inválidos) y verifique que el sistema muestre una pantalla de error genérica sin revelar nombres de base de datos, consultas SQL o rutas absolutas locales del servidor.
3. **Validación de Cargas Privadas:**
   - Suba un archivo de soporte (ej. en el flujo de cuentas o soporte técnico) y confirme que el documento se escriba en el directorio `PRIVATE_STORAGE_PATH` y no en el árbol público del servidor. Intente acceder al archivo directamente vía URL pública y confirme que Apache retorne un error `403 Forbidden` o `404 Not Found`.
