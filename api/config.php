<?php

declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_ATTEMPT_WINDOW_SECONDS = 900;
const LOGIN_LOCK_SECONDS = 900;

function load_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $loaded = true;
    $envFile = dirname(__DIR__) . '/.env';

    if (!is_file($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function env_value(string $key): string
{
    load_env();

    $value = getenv($key);

    return is_string($value) ? trim($value) : '';
}

function admin_username(): string
{
    return env_value('ADMIN_USERNAME');
}

function admin_password_hash(): string
{
    return env_value('ADMIN_PASSWORD_HASH');
}

function prices_data_file(): string
{
    return dirname(__DIR__) . '/data/prices.json';
}

function login_attempts_file(): string
{
    return dirname(__DIR__) . '/data/login-attempts.json';
}
