<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Метод не дозволений.'], 405);
}

$data = read_json_body();
$login = (string)($data['login'] ?? '');
$password = (string)($data['password'] ?? '');
$configuredLogin = admin_username();
$configuredPasswordHash = admin_password_hash();

if ($configuredLogin === '' || $configuredPasswordHash === '') {
    json_response(['error' => 'Адмін-доступ не налаштовано.'], 500);
}

$lockSeconds = login_lock_seconds_remaining($login);

if ($lockSeconds > 0) {
    json_response([
        'error' => 'Забагато невдалих спроб. Спробуйте пізніше.',
        'retryAfter' => $lockSeconds,
    ], 429);
}

if ($login !== $configuredLogin || !password_verify($password, $configuredPasswordHash)) {
    $lockSeconds = register_failed_login($login);

    if ($lockSeconds > 0) {
        json_response([
            'error' => 'Забагато невдалих спроб. Спробуйте пізніше.',
            'retryAfter' => $lockSeconds,
        ], 429);
    }

    json_response(['error' => 'Невірний логін або пароль.'], 401);
}

session_regenerate_id(true);
$_SESSION['admin_logged_in'] = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
clear_failed_login($login);

json_response([
    'authenticated' => true,
    'csrfToken' => $_SESSION['csrf_token'],
]);
