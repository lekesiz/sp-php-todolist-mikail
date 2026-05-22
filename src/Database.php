<?php
/**
 * Database — Singleton PDO.
 *
 * Suit l'approche M. Christoffel (RS LPDWCA PHP N°7 PDO class) :
 * une seule connexion PDO partagée pour toute la requête HTTP,
 * exceptions activées, fetch associatif par défaut, charset utf8mb4.
 */

namespace App;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['dbname'],
                $db['charset']
            );

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                if (!empty($config['app']['debug'])) {
                    die('Erreur de connexion BDD : ' . htmlspecialchars($e->getMessage()));
                }
                die('Erreur de connexion à la base de données.');
            }
        }

        return self::$instance;
    }
}
