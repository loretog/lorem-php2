<?php

class Database
{
    private static $instance = null;

    public static function get()
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    'mysql:host='.DB_HOST.';dbname='.DB_NAME,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    ]
                );
            } catch (PDOException $e) {
                if (DEBUG) {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed');
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}