<?php
require_once __DIR__ . '/Arbimaps/includes/bootstrap.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_auth();
neiva_require_csrf('logout');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        (bool) ($params['secure'] ?? false),
        (bool) ($params['httponly'] ?? true)
    );
}

session_destroy();

header('Location: ' . neiva_app_base_url() . '/login.php', true, 302);
exit;
