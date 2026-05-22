<?php
/**
 * Controller — Classe de base.
 * Fournit `render()` pour rendre une vue dans le layout commun
 * et `redirect()` pour rediriger via Location header.
 */

namespace App;

abstract class Controller
{
    /**
     * Rend une vue, encapsulée dans le layout par défaut.
     *
     * @param string               $view  Chemin relatif depuis views/, ex. "tache/index"
     * @param array<string,mixed>  $data  Variables exposées à la vue
     * @param string|null          $title Titre de page
     */
    protected function render(string $view, array $data = [], ?string $title = null): void
    {
        extract($data, EXTR_SKIP);
        $title = $title ?? 'To Do List';
        $flashes = Flash::pull();

        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout/header.php';
        echo $content;
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function redirect(string $path): void
    {
        $config = require __DIR__ . '/../config/config.php';
        header('Location: ' . $config['app']['base_url'] . $path);
        exit;
    }
}
