<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;
    public static function connection(): PDO
    {
        if (self::$pdo !== null) return self::$pdo;
        $file = dirname(__DIR__) . '/config/database.local.php';
        if (!is_file($file)) $file = dirname(__DIR__) . '/config/database.example.php';
        $c = require $file;
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'], $c['database'], $c['charset']);
        self::$pdo = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }
}
