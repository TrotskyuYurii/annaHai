<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

json_response([
    'authenticated' => is_admin_logged_in(),
    'csrfToken' => is_admin_logged_in() ? csrf_token() : '',
]);
