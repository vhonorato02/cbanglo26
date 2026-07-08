<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => Env::bool('APP_DEBUG', false),
    'url'      => rtrim(Env::get('APP_URL', ''), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'America/Sao_Paulo'),
    'key'      => Env::get('APP_KEY', ''),
    'setup_token' => Env::get('APP_SETUP_TOKEN', ''),

    'db' => [
        'driver'   => Env::get('DB_DRIVER', 'mysql'),
        'host'     => Env::get('DB_HOST', 'agenciamd.com.br'),
        'port'     => Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'agenciam_cbanglo26'),
        'username' => Env::get('DB_USERNAME', 'agenciam_agenciamdcom'),
        'password' => Env::get('DB_PASSWORD', '$2y$10$KMP3NW4RSrGSALYHaUcDAeEEteWYJSyHTfq8KvQ1hDgVGdSjnnWBe'),
        // Usado apenas em testes (driver sqlite)
        'sqlite_path' => Env::get('DB_SQLITE_PATH', ':memory:'),
    ],

    'mail' => [
        'from_address' => Env::get('MAIL_FROM_ADDRESS', ''),
        'from_name'    => Env::get('MAIL_FROM_NAME', 'Concurso de Bolsas'),
        'smtp_host'    => Env::get('SMTP_HOST', ''),
        'smtp_port'    => (int) Env::get('SMTP_PORT', '587'),
        'smtp_user'    => Env::get('SMTP_USERNAME', ''),
        'smtp_pass'    => Env::get('SMTP_PASSWORD', ''),
        'encryption'   => Env::get('SMTP_ENCRYPTION', 'tls'),
    ],
];
