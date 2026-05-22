<?php
/**
 * Csrf — Génération et vérification de jetons CSRF.
 * Utilise la session PHP. Un jeton par session, régénéré après validation
 * pour éviter le replay.
 */

namespace App;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function check(?string $token): bool
    {
        if (!is_string($token) || empty($_SESSION['csrf'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf'], $token);
    }

    public static function rotate(): void
    {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}
