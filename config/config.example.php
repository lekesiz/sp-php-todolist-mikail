<?php
/**
 * Exemple de config.local.php pour override local.
 *
 * Si vous préférez ne pas dépendre de variables d'environnement,
 * copiez ce fichier en `config.local.php` (gitignored) et modifiez vos valeurs.
 *
 *   cp config/config.example.php config/config.local.php
 *
 * Sinon, laissez `config.php` en place et définissez DB_HOST, DB_NAME, etc.
 * dans .htaccess (SetEnv) ou dans votre shell.
 */

return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'todoList',
        'user'     => 'root',
        'password' => 'root', // MAMP par défaut ; sinon adapter
        'charset'  => 'utf8mb4',
    ],

    'app' => [
        'name'      => 'To Do List — SP PHP',
        // Préfixe d'URL : '/todoList-app/public' en MAMP, '' en prod (docroot = public/).
        'base_url'  => '/todoList-app/public',
        'timezone'  => 'Europe/Paris',
        'debug'     => true,
    ],
];
