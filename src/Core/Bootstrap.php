<?php
namespace App\Core;

use PDO;

class Bootstrap
{
    public static PDO $db;

    public static function init(): void
    {
        self::$db = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname=vacation_portal_db;charset=utf8mb4',
            'root', 'verysecret',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
