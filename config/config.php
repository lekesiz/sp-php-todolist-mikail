<?php
/**
 * Configuration centrale de l'application.
 *
 * Stratégie de chargement (1er trouvé gagne) :
 *   1. Si `config/config.local.php` existe → ses valeurs priment (override dev local).
 *   2. Sinon, variables d'environnement (idéal en prod : SetEnv .htaccess).
 *   3. Sinon, valeurs par défaut MAMP (root/root @ localhost).
 *
 * Aucun secret n'est commité : les valeurs par défaut sont publiques (root/root MAMP).
 */

// 1) Override local (gitignored) si présent.
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    return require $localFile;
}

/** Helper : lit une variable d'env avec fallback. */
if (!function_exists('cfg_env')) {
    function cfg_env(string $key, $default = null) {
        // On distingue "non défini" (false) de "défini à vide" ('') :
        // une chaîne vide est une valeur valide (ex. APP_BASE_URL="" en prod).
        if (array_key_exists($key, $_ENV)) return $_ENV[$key];
        $val = getenv($key);
        return ($val === false) ? $default : $val;
    }
}

return [
    'db' => [
        'host'     => cfg_env('DB_HOST',     'localhost'),
        'port'     => (int) cfg_env('DB_PORT', 3306),
        'dbname'   => cfg_env('DB_NAME',     'todoList'),
        'user'     => cfg_env('DB_USER',     'root'),
        'password' => cfg_env('DB_PASSWORD', 'root'),
        'charset'  => 'utf8mb4',
    ],

    'app' => [
        'name'      => cfg_env('APP_NAME', 'To Do List — SP PHP'),
        // base_url : préfixe d'URL ajouté à tous les liens internes.
        // - MAMP local : '/todoList-app/public'
        // - AlwaysData (docroot pointant sur public/) : '' (vide)
        'base_url'  => cfg_env('APP_BASE_URL', '/todoList-app/public'),
        'timezone'  => cfg_env('APP_TIMEZONE', 'Europe/Paris'),
        'debug'     => filter_var(cfg_env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    ],
];
