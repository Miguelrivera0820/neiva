<?php

if (!function_exists('neiva_app_root')) {
    function neiva_app_root(): string
    {
        return dirname(__DIR__, 2);
    }
}

if (!function_exists('neiva_env')) {
    function neiva_env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}

if (!function_exists('neiva_app_env')) {
    function neiva_app_env(): string
    {
        return strtolower(neiva_env('APP_ENV', 'development'));
    }
}

if (!function_exists('neiva_app_base_url')) {
    function neiva_app_base_url(): string
    {
        $configured = rtrim((string) neiva_env('APP_BASE_URL', ''), '/');
        if ($configured !== '') {
            return $configured;
        }

        $scheme = neiva_request_is_secure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = neiva_app_base_path();

        return $scheme . '://' . $host . $basePath;
    }
}

if (!function_exists('neiva_app_base_path')) {
    function neiva_app_base_path(): string
    {
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptFilename = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $appRoot = str_replace('\\', '/', neiva_app_root());

        if ($scriptName !== '' && $scriptFilename !== '' && str_starts_with($scriptFilename, $appRoot)) {
            $relativeScript = ltrim(substr($scriptFilename, strlen($appRoot)), '/');
            if ($relativeScript !== '' && str_ends_with($scriptName, $relativeScript)) {
                $basePath = substr($scriptName, 0, -strlen($relativeScript));
                $basePath = rtrim($basePath, '/');
                return $basePath === '' ? '' : $basePath;
            }
        }

        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/.');
        return $basePath === '/' ? '' : $basePath;
    }
}

if (!function_exists('neiva_app_url')) {
    function neiva_app_url(string $path = ''): string
    {
        $baseUrl = neiva_app_base_url();
        if ($path === '') {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('neiva_request_is_secure')) {
    function neiva_request_is_secure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        if (($_SERVER['SERVER_PORT'] ?? null) === '443') {
            return true;
        }

        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        return $forwardedProto === 'https';
    }
}

if (!function_exists('neiva_is_production')) {
    function neiva_is_production(): bool
    {
        return neiva_app_env() === 'production';
    }
}

if (!function_exists('neiva_private_storage_path')) {
    function neiva_private_storage_path(): string
    {
        $configured = neiva_env('PRIVATE_STORAGE_PATH');
        if ($configured !== null) {
            return rtrim($configured, "\\/");
        }

        return rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'neiva_private_storage';
    }
}

if (!function_exists('neiva_join_paths')) {
    function neiva_join_paths(string ...$segments): string
    {
        $filtered = [];
        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                continue;
            }

            $filtered[] = $index === 0
                ? rtrim($segment, "\\/")
                : trim($segment, "\\/");
        }

        return implode(DIRECTORY_SEPARATOR, $filtered);
    }
}

if (!function_exists('neiva_normalize_relative_path')) {
    function neiva_normalize_relative_path(string $relativePath): string
    {
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relativePath));
        $parts = [];

        foreach (explode(DIRECTORY_SEPARATOR, $relativePath) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                throw new RuntimeException('La ruta relativa no es válida.');
            }

            $parts[] = $part;
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}

if (!function_exists('neiva_private_path')) {
    function neiva_private_path(string $relativePath = ''): string
    {
        if ($relativePath === '') {
            return neiva_private_storage_path();
        }

        return neiva_join_paths(
            neiva_private_storage_path(),
            neiva_normalize_relative_path($relativePath)
        );
    }
}

if (!function_exists('neiva_ensure_directory')) {
    function neiva_ensure_directory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible crear el directorio requerido.');
        }
    }
}

if (!function_exists('neiva_bootstrap')) {
    function neiva_bootstrap(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => neiva_request_is_secure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', neiva_request_is_secure() ? '1' : '0');
        session_name('NEIVASESSID');
        session_start();

        if (!isset($_SESSION['_neiva_bootstrapped'])) {
            $_SESSION['_neiva_bootstrapped'] = time();
        }
    }
}

if (!function_exists('neiva_is_api_request')) {
    function neiva_is_api_request(): bool
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || $requestedWith === 'xmlhttprequest'
            || str_contains($uri, '/api/');
    }
}

if (!function_exists('neiva_abort')) {
    function neiva_abort(int $statusCode, string $message, bool $forceJson = false): never
    {
        http_response_code($statusCode);

        $json = $forceJson || neiva_is_api_request();
        if ($statusCode === 401 && !$json) {
            $loginUrl = neiva_app_base_url() . '/login.php';
            header('Location: ' . $loginUrl, true, 302);
            exit;
        }

        if ($json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'status' => $statusCode,
                'message' => $message,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error</title></head><body>';
        echo '<h1>Error ' . (int) $statusCode . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '</body></html>';
        exit;
    }
}

if (!function_exists('neiva_require_methods')) {
    function neiva_require_methods(array|string $methods, bool $forceJson = false): void
    {
        $allowed = array_map('strtoupper', (array) $methods);
        $current = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if (!in_array($current, $allowed, true)) {
            header('Allow: ' . implode(', ', $allowed));
            neiva_abort(405, 'Método HTTP no permitido.', $forceJson);
        }
    }
}

if (!function_exists('neiva_is_authenticated')) {
    function neiva_is_authenticated(): bool
    {
        return !empty($_SESSION['id_usuario']);
    }
}

if (!function_exists('neiva_require_auth')) {
    function neiva_require_auth(bool $forceJson = false): void
    {
        neiva_bootstrap();
        if (!neiva_is_authenticated()) {
            neiva_abort(401, 'Debe iniciar sesión para continuar.', $forceJson);
        }
    }
}

if (!function_exists('neiva_get_user_roles')) {
    function neiva_get_user_roles(): array
    {
        return array_values(array_filter([
            $_SESSION['rol_usuario'] ?? null,
            $_SESSION['rol_usuario_dos'] ?? null,
            $_SESSION['rol_usuario_tres'] ?? null,
        ]));
    }
}

if (!function_exists('getRolesUsuario')) {
    function getRolesUsuario(): array
    {
        return neiva_get_user_roles();
    }
}

if (!function_exists('usuarioTieneAlgunRol')) {
    function usuarioTieneAlgunRol($rolesNecesarios): bool
    {
        $rolesNecesarios = is_array($rolesNecesarios) ? $rolesNecesarios : [$rolesNecesarios];
        return count(array_intersect($rolesNecesarios, neiva_get_user_roles())) > 0;
    }
}

if (!function_exists('neiva_user_has_permission')) {
    function neiva_user_has_permission(string $permiso, ?array $permissionMatrix = null): bool
    {
        if ($permissionMatrix === null) {
            $permissionFile = dirname(__DIR__) . '/config/permisos.php';
            if (file_exists($permissionFile)) {
                require $permissionFile;
                $permissionMatrix = $PERMISOS ?? [];
            } else {
                $permissionMatrix = [];
            }
        }

        if (!array_key_exists($permiso, $permissionMatrix)) {
            return false;
        }

        return usuarioTieneAlgunRol($permissionMatrix[$permiso]);
    }
}

if (!function_exists('neiva_require_permission')) {
    function neiva_require_permission(string $permiso, ?array $permissionMatrix = null, bool $forceJson = false): void
    {
        neiva_require_auth($forceJson);

        if (!neiva_user_has_permission($permiso, $permissionMatrix)) {
            neiva_abort(403, 'No tiene permisos para realizar esta acción.', $forceJson);
        }
    }
}

if (!function_exists('neiva_csrf_token')) {
    function neiva_csrf_token(string $scope = 'global'): string
    {
        neiva_bootstrap();
        if (!isset($_SESSION['_csrf_tokens']) || !is_array($_SESSION['_csrf_tokens'])) {
            $_SESSION['_csrf_tokens'] = [];
        }

        if (empty($_SESSION['_csrf_tokens'][$scope])) {
            $_SESSION['_csrf_tokens'][$scope] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_tokens'][$scope];
    }
}

if (!function_exists('neiva_validate_csrf_token')) {
    function neiva_validate_csrf_token(?string $token, string $scope = 'global'): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $stored = $_SESSION['_csrf_tokens'][$scope] ?? null;
        return is_string($stored) && hash_equals($stored, $token);
    }
}

if (!function_exists('neiva_require_csrf')) {
    function neiva_require_csrf(string $scope = 'global', bool $forceJson = false): void
    {
        neiva_bootstrap();

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!neiva_validate_csrf_token($token, $scope)) {
            neiva_abort(419, 'Token CSRF inválido.', $forceJson);
        }
    }
}

if (!function_exists('neiva_csrf_input')) {
    function neiva_csrf_input(string $scope = 'global'): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(neiva_csrf_token($scope), ENT_QUOTES, 'UTF-8')
            . '">';
    }
}

if (!function_exists('neiva_detect_mime_type')) {
    function neiva_detect_mime_type(string $filePath): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return (string) $finfo->file($filePath);
    }
}

if (!function_exists('neiva_validate_upload')) {
    function neiva_validate_upload(array $file, array $policy): array
    {
        $required = $policy['required'] ?? true;
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new RuntimeException('Debe adjuntar un archivo.');
            }

            return [];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('La carga del archivo falló.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('El archivo cargado no es válido.');
        }

        $originalName = trim((string) ($file['name'] ?? ''));
        if ($originalName === '' || substr_count($originalName, '.') > 1) {
            throw new RuntimeException('El nombre del archivo no es válido.');
        }

        if (($file['size'] ?? 0) < 1) {
            throw new RuntimeException('No se permiten archivos vacíos.');
        }

        $maxBytes = (int) ($policy['max_bytes'] ?? 0);
        if ($maxBytes > 0 && (int) $file['size'] > $maxBytes) {
            throw new RuntimeException('El archivo excede el tamaño máximo permitido.');
        }

        $mime = neiva_detect_mime_type($tmpName);
        $allowedMimeMap = $policy['allowed_mimes'] ?? [];
        if (!isset($allowedMimeMap[$mime])) {
            throw new RuntimeException('El tipo de archivo no está permitido.');
        }

        $extension = $allowedMimeMap[$mime];
        $lowerOriginal = strtolower($originalName);
        $blockedFragments = ['.php', '.phtml', '.phar', '.svg', '.html', '.htm'];
        foreach ($blockedFragments as $fragment) {
            if (str_contains($lowerOriginal, $fragment)) {
                throw new RuntimeException('El archivo fue rechazado por seguridad.');
            }
        }

        if (($policy['image_decode'] ?? false) === true) {
            $imageInfo = @getimagesize($tmpName);
            if ($imageInfo === false) {
                throw new RuntimeException('La imagen cargada no es válida.');
            }
        }

        if (($policy['require_doc_signature'] ?? false) === true) {
            $signature = file_get_contents($tmpName, false, null, 0, 8);
            if ($signature !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
                throw new RuntimeException('El archivo DOC no tiene una firma binaria válida.');
            }
        }

        return [
            'tmp_name' => $tmpName,
            'original_name' => $originalName,
            'mime' => $mime,
            'extension' => $extension,
            'size' => (int) $file['size'],
        ];
    }
}

if (!function_exists('neiva_resolve_existing_path')) {
    function neiva_resolve_existing_path(string $path, array $allowedBases): string
    {
        if (preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $path)) {
            throw new RuntimeException('No se permiten wrappers o esquemas en rutas locales.');
        }

        $resolvedPath = realpath($path);
        if ($resolvedPath === false || !is_file($resolvedPath)) {
            throw new RuntimeException('La ruta local solicitada no existe.');
        }

        foreach ($allowedBases as $allowedBase) {
            $resolvedBase = realpath($allowedBase);
            if ($resolvedBase === false) {
                continue;
            }

            $baseWithSlash = rtrim($resolvedBase, "\\/") . DIRECTORY_SEPARATOR;
            if (str_starts_with($resolvedPath, $baseWithSlash) || $resolvedPath === $resolvedBase) {
                return $resolvedPath;
            }
        }

        throw new RuntimeException('La ruta local está fuera del almacenamiento autorizado.');
    }
}
