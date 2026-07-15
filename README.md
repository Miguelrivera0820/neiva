# ARBIMaps Neiva

Aplicación PHP/MySQL para gestión catastral y operativa de Neiva. El proyecto funciona sobre XAMPP/PHP 8.2 y concentra módulos de trámites, seguimiento, soporte, usuarios, certificados, restricciones de predios y gestión de personal.

## Estado

El sistema está en proceso de endurecimiento de seguridad. Este repositorio no debe publicarse sin saneamiento previo de datos, documentos y configuraciones locales.

## Módulos principales

- `Arbimaps/`: núcleo de la aplicación y router principal.
- `vistas/tramites/`: radicación, asignación, revisión y cierre de trámites.
- `vistas/seguimiento/`: devoluciones, entregas, subsanaciones y resoluciones.
- `vistas/soporte/`: tickets, chat y mesa de ayuda.
- `vistas/Usuarios/`: administración de usuarios.
- `vistas/Personal/`: perfiles, contratación, otrosí y aprobaciones.
- `vistas/baqueanos/`: solicitudes y cuentas del flujo de baqueanos.

## Requisitos

- PHP 8.2+
- MySQL/MariaDB
- Apache
- Extensión PHP `zip`
- Composer
- Node.js/npm solo si se van a usar tareas frontend heredadas

## Variables de entorno

Crear un archivo `.env` local a partir de `.env.example`.

Variables usadas actualmente:

- `APP_ENV`
- `APP_BASE_URL`
- `PRIVATE_STORAGE_PATH`
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

## Instalación local

1. Clonar o copiar el proyecto dentro de `C:\xampp\htdocs\neiva`.
2. Crear `.env` a partir de `.env.example`.
3. Instalar dependencias PHP:

```bash
composer install
```

4. Restaurar una base de datos de desarrollo segura.
5. Verificar que Apache sirva el proyecto en una URL equivalente a `http://localhost/neiva`.

## Arranque

La autenticación pública entra por:

- `login.php`

El panel interno usa:

- `Arbimaps/index.php`

## Seguridad y publicación

Antes de subir este proyecto a GitHub:

- No incluir `.env`, dumps `.sql`, logs ni respaldos.
- No incluir `dat_neiva/`, `tramites_conservacion/`, `uploads/`, `tmp/` ni documentos generados.
- No incluir `vendor/` ni `node_modules/`.
- Confirmar que no existan credenciales, IPs internas o datos reales incrustados en el código.
- Mantener el repositorio como `private` hasta terminar el saneamiento.

## Dependencias backend

El proyecto usa Composer para, al menos:

- `phpoffice/phpspreadsheet`
- `dompdf/dompdf`

## Validaciones útiles

Sintaxis PHP:

```bash
C:\xampp\php\php.exe -l ruta\archivo.php
```

Auditoría de contraseñas:

```bash
C:\xampp\php\php.exe scripts\audit_passwords.php
```

## Notas

- El `README` anterior del template SB Admin 2 ya no describe este proyecto.
- La estructura aún contiene módulos legacy y deuda técnica en proceso de remediación.
- El endurecimiento en curso está documentado en `PLAN_REMEDIACION_NEIVA_2026-07-14.md`.
