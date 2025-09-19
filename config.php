<?php

return [
    'app' => [
        'name' => 'Správa záznamů',
        'version' => '1.0.0',
        'debug' => false,
    ],

    'database' => [
        'host' => 'localhost',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_czech_ci',
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'dir' => __DIR__ . '/zeta/cache/',
    ],

    'import' => [
        'source_url' => 'https://test.3it.cz/data/json',
        'timeout' => 10,
        'cache_ttl' => 300,
        'batch_size' => 100,
    ],

    'security' => [
        'session_name' => 'app_session',
        'session_lifetime' => 86400,
        'csrf_protection' => true,
    ],
];