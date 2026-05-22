<?php
/**
 * Router — Front controller minimaliste.
 *
 * Convention d'URL : /index.php?r=ressource/action&id=X
 *   - r = ressource/action (ex: "tache/index", "collaborateur/edit")
 *   - id = paramètre optionnel
 */

namespace App;

final class Router
{
    public function dispatch(): void
    {
        $route  = $_GET['r']  ?? 'home/index';
        $parts  = explode('/', trim($route, '/'));
        $resource = $parts[0] ?? 'home';
        $action   = $parts[1] ?? 'index';

        $controllerClass = '\\App\\Controllers\\' . ucfirst($resource) . 'Controller';
        if (!class_exists($controllerClass)) {
            $this->notFound("Controller introuvable : $controllerClass");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound("Action introuvable : $action");
            return;
        }

        try {
            $controller->$action();
        } catch (\Throwable $e) {
            $this->serverError($e);
        }
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/error/404.php';
    }

    private function serverError(\Throwable $e): void
    {
        http_response_code(500);
        $config = require __DIR__ . '/../config/config.php';
        $debug = !empty($config['app']['debug']);
        require __DIR__ . '/../views/error/500.php';
    }
}
