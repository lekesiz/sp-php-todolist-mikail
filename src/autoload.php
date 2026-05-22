<?php
/**
 * Autoload PSR-4-light.
 * Mappe le namespace "App\..." aux fichiers sous src/.
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relative = str_replace('\\', '/', $relative);

    // Models -> src/models/, Controllers -> src/controllers/, autres -> src/
    if (str_starts_with($relative, 'Models/')) {
        $file = __DIR__ . '/models/' . substr($relative, strlen('Models/')) . '.php';
    } elseif (str_starts_with($relative, 'Controllers/')) {
        $file = __DIR__ . '/controllers/' . substr($relative, strlen('Controllers/')) . '.php';
    } else {
        $file = __DIR__ . '/' . $relative . '.php';
    }

    if (is_file($file)) {
        require $file;
    }
});
