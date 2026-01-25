<?php
namespace SiParte\Quiz\Database;

use PDO;
use PDOException;
use Exception;

class Connection {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                // Controllo Railway (MYSQL_URL) oppure Docker/local
                $url = getenv('MYSQL_URL');

                if ($url) {
                    // Railway
                    $dbparts = parse_url($url);
                    $host = $dbparts['host'] ?? 'localhost';
                    $port = $dbparts['port'] ?? '3306';
                    $user = $dbparts['user'] ?? 'root';
                    $pass = $dbparts['pass'] ?? '';
                    $db   = ltrim($dbparts['path'] ?? 'si_parte', '/');
                } else {
                    // Docker / locale
                    $host = getenv('MYSQLHOST') ?: 'localhost';
                    $port = getenv('MYSQLPORT') ?: '3306';
                    $db   = getenv('MYSQLDATABASE') ?: 'si_parte';
                    $user = getenv('MYSQLUSER') ?: 'root';
                    $pass = getenv('MYSQLPASSWORD') ?: '';
                }

                $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);

            } catch (PDOException $e) {
                throw new Exception("Connessione DB fallita: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
