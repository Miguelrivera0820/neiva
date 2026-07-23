@echo off
:: Script de Configuracion Inicial para Colaboradores - ARBIMaps Neiva
:: Este script automatiza la configuracion inicial del proyecto en Windows.

setlocal enabledelayedexpansion

echo ==========================================================
echo       BIENVENIDO AL CONFIGURADOR DE ARBIMaps NEIVA
echo ==========================================================
echo Este asistente preparara tu entorno de desarrollo local.
echo.

:: 1. Copiar archivo .env si no existe
echo [1/4] Configurando archivo de variables de entorno (.env)...
if not exist ".env" (
    copy ".env.example" ".env" > nul
    echo   - Se ha creado el archivo .env a partir de .env.example.
    echo   - IMPORTANTE: Recuerda abrir el archivo .env y configurar tus credenciales de base de datos.
) else (
    echo   - El archivo .env ya existe. Omitiendo copia.
)
echo.

:: 2. Buscar ejecutable de PHP
echo [2/4] Buscando PHP en el sistema...
set "PHP_CMD=php"

where php > nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_CMD=C:\xampp\php\php.exe"
        echo   - PHP encontrado en: C:\xampp\php\php.exe
    ) else (
        echo   - [ERROR] No se pudo encontrar PHP en tu PATH ni en C:\xampp\php\php.exe.
        echo             Asegurate de que PHP este instalado y agregado a las variables de entorno.
        pause
        exit /b 1
    )
) else (
    echo   - PHP esta disponible globalmente en el PATH.
)
echo.

:: 3. Buscar e instalar dependencias de Composer
echo [3/4] Instalando dependencias de Composer...
where composer > nul 2>nul
if %errorlevel% neq 0 (
    :: Verificar si hay un composer.phar en la raiz
    if exist "composer.phar" (
        echo   - Usando composer.phar local.
        echo   - Ejecutando instalacion de dependencias...
        "!PHP_CMD!" composer.phar install
    ) else (
        echo   - [ERROR] No se encontro Composer instalado globalmente ni composer.phar en la raiz.
        echo             Por favor, instala Composer desde https://getcomposer.org/
        pause
        exit /b 1
    )
) else (
    echo   - Composer esta disponible globalmente en el PATH.
    echo   - Ejecutando instalacion de dependencias...
    composer install
)

if %errorlevel% neq 0 (
    echo.
    echo   - [ERROR] Hubo un problema al ejecutar 'composer install'.
    echo             Revisa los mensajes de arriba.
    pause
    exit /b 1
)
echo   - Dependencias instaladas correctamente.
echo.

:: 4. Recordatorio de Base de Datos
echo [4/4] Configuracion de Base de Datos...
echo   - El esquema inicial de la base de datos se encuentra en:
echo      migrations\schema.sql
echo.
echo   - Instrucciones rapidas para importar:
echo      1. Crea una base de datos vacia llamada 'neiva' (o el nombre que prefieras).
echo      2. Importa el archivo 'migrations\schema.sql'.
echo      3. Configura DB_NAME, DB_USER y DB_PASSWORD en tu archivo '.env'.
echo.
echo ==========================================================
echo   CONFIGURACION COMPLETADA CON EXITO
echo ==========================================================
echo Tu entorno local de desarrollo esta casi listo.
echo Por favor abre tu servidor local (ej. XAMPP) y asegurate
echo de que Apache y MySQL esten corriendo.
echo ==========================================================
pause
