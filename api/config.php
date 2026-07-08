<?php

declare(strict_types=1);

const ADMIN_USERNAME = 'admin';
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_ATTEMPT_WINDOW_SECONDS = 900;
const LOGIN_LOCK_SECONDS = 900;

function admin_password_hash(): string
{
    $envHash = getenv('ADMIN_PASSWORD_HASH');

    if (is_string($envHash) && $envHash !== '') {
        return $envHash;
    }

    return password_hash('anna2026', PASSWORD_DEFAULT);
}

function prices_data_file(): string
{
    return dirname(__DIR__) . '/data/prices.json';
}

function login_attempts_file(): string
{
    return dirname(__DIR__) . '/data/login-attempts.json';
}
