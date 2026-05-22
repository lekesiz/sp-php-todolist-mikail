<?php
/**
 * Front controller — Point d'entrée unique de l'application.
 *
 * Tous les requêtes HTTP passent ici, sont routées par App\Router
 * et déléguées à un Controller, qui rend la vue dans le layout commun.
 */

declare(strict_types=1);

// --- bootstrap ---
session_start();
require __DIR__ . '/../src/autoload.php';

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Paris');

if (!empty($config['app']['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Headers de sécurité basiques (OWASP)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

// --- dispatch ---
$router = new \App\Router();
$router->dispatch();
