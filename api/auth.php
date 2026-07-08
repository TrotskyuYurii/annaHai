<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array
{
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody ?: '{}', true);

    if (!is_array($data)) {
        json_response(['error' => 'Некоректний JSON.'], 400);
    }

    return $data;
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        json_response(['error' => 'Потрібна авторизація.'], 401);
    }
}

function require_csrf(): void
{
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($headerToken) || !is_string($sessionToken) || !hash_equals($sessionToken, $headerToken)) {
        json_response(['error' => 'Сесія застаріла. Увійдіть ще раз.'], 403);
    }
}

function client_ip(): string
{
    return (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function login_attempt_key(string $login): string
{
    return hash('sha256', strtolower(trim($login)) . '|' . client_ip());
}

function update_login_attempts(callable $callback): mixed
{
    $file = login_attempts_file();
    $directory = dirname($file);

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        json_response(['error' => 'Не вдалося створити папку для даних.'], 500);
    }

    $handle = fopen($file, 'c+');

    if ($handle === false) {
        json_response(['error' => 'Не вдалося відкрити файл захисту входу.'], 500);
    }

    flock($handle, LOCK_EX);

    $rawData = stream_get_contents($handle);
    $attempts = json_decode($rawData ?: '{}', true);

    if (!is_array($attempts)) {
        $attempts = [];
    }

    $result = $callback($attempts);

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($attempts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $result;
}

function login_lock_seconds_remaining(string $login): int
{
    $key = login_attempt_key($login);
    $now = time();

    return update_login_attempts(function (array &$attempts) use ($key, $now): int {
        cleanup_login_attempts($attempts, $now);

        $record = $attempts[$key] ?? null;

        if (!is_array($record) || empty($record['locked_until'])) {
            return 0;
        }

        $remaining = (int)$record['locked_until'] - $now;

        return max(0, $remaining);
    });
}

function register_failed_login(string $login): int
{
    $key = login_attempt_key($login);
    $now = time();

    return update_login_attempts(function (array &$attempts) use ($key, $now): int {
        cleanup_login_attempts($attempts, $now);

        $record = $attempts[$key] ?? [
            'count' => 0,
            'first_attempt_at' => $now,
            'locked_until' => 0,
        ];

        if (!is_array($record) || ($now - (int)($record['first_attempt_at'] ?? 0)) > LOGIN_ATTEMPT_WINDOW_SECONDS) {
            $record = [
                'count' => 0,
                'first_attempt_at' => $now,
                'locked_until' => 0,
            ];
        }

        $record['count'] = (int)($record['count'] ?? 0) + 1;

        if ($record['count'] >= LOGIN_MAX_ATTEMPTS) {
            $record['locked_until'] = $now + LOGIN_LOCK_SECONDS;
            $record['count'] = 0;
            $record['first_attempt_at'] = $now;
        }

        $attempts[$key] = $record;

        return max(0, (int)$record['locked_until'] - $now);
    });
}

function clear_failed_login(string $login): void
{
    $key = login_attempt_key($login);

    update_login_attempts(function (array &$attempts) use ($key): null {
        unset($attempts[$key]);

        return null;
    });
}

function cleanup_login_attempts(array &$attempts, int $now): void
{
    foreach ($attempts as $key => $record) {
        if (!is_array($record)) {
            unset($attempts[$key]);
            continue;
        }

        $lockedUntil = (int)($record['locked_until'] ?? 0);
        $firstAttemptAt = (int)($record['first_attempt_at'] ?? 0);
        $expiredLock = $lockedUntil > 0 && $lockedUntil < $now;
        $expiredWindow = $firstAttemptAt > 0 && ($now - $firstAttemptAt) > LOGIN_ATTEMPT_WINDOW_SECONDS;

        if ($expiredLock || $expiredWindow) {
            unset($attempts[$key]);
        }
    }
}
