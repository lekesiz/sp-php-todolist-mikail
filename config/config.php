<?php
/**
 * Configuration centrale de l'application.
 * Adapter les valeurs DB selon votre environnement local (MAMP, XAMPP, WAMP, Docker).
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
        'base_url'  => '/todoList-app/public',
        'timezone'  => 'Europe/Paris',
        'debug'     => true, // Désactiver en production
    ],
];
