<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function default_prices(): array
{
    return [
        [
            'name' => 'ІНДИВІДУАЛЬНА КОНСУЛЬТАЦІЯ',
            'time' => '55 хвилин',
            'price' => '1800 UAH / 40 EUR',
        ],
        [
            'name' => 'ПЕРСОНАЛЬНА КОНСУЛЬТАЦІЯ ІЗ СУПРОВОДОМ',
            'time' => '55 хвилин + підтримка в месенджері 3 рази на тиждень',
            'price' => '2500 UAH / 50 EUR',
        ],
        [
            'name' => 'РОДИННА КОНСУЛЬТАЦІЯ',
            'time' => '80 хвилин',
            'price' => '2200 UAH / 45 EUR',
        ],
        [
            'name' => 'ПАРНА КОНСУЛЬТАЦІЯ',
            'time' => '90 хвилин',
            'price' => '2500 UAH / 50 EUR',
        ],
    ];
}

function load_prices(): array
{
    $file = prices_data_file();

    if (!is_file($file)) {
        return default_prices();
    }

    $data = json_decode((string)file_get_contents($file), true);

    if (!is_array($data) || !isset($data['prices']) || !is_array($data['prices'])) {
        return default_prices();
    }

    return $data['prices'];
}

function validate_prices(array $prices): array
{
    $cleanPrices = [];

    foreach ($prices as $item) {
        if (!is_array($item)) {
            continue;
        }

        $name = trim((string)($item['name'] ?? ''));
        $time = trim((string)($item['time'] ?? ''));
        $price = trim((string)($item['price'] ?? ''));

        if ($name === '' || $time === '' || $price === '') {
            continue;
        }

        $cleanPrices[] = [
            'name' => limit_text($name, 160),
            'time' => limit_text($time, 220),
            'price' => limit_text($price, 80),
        ];
    }

    if (!$cleanPrices) {
        json_response(['error' => 'Додайте хоча б одну позицію прайсу.'], 422);
    }

    return $cleanPrices;
}

function limit_text(string $value, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length);
    }

    return substr($value, 0, $length);
}

function save_prices(array $prices)
{
    $file = prices_data_file();
    $directory = dirname($file);

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        json_response(['error' => 'Не вдалося створити папку для даних.'], 500);
    }

    $json = json_encode(['prices' => $prices], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    if ($json === false || file_put_contents($file, $json, LOCK_EX) === false) {
        json_response(['error' => 'Не вдалося зберегти прайс.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_response(['prices' => load_prices()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_admin();
    require_csrf();

    $data = read_json_body();
    $prices = validate_prices((array)($data['prices'] ?? []));
    save_prices($prices);

    json_response(['prices' => $prices]);
}

json_response(['error' => 'Метод не дозволений.'], 405);
