<?php

class Database
{
    private static $instance = null;

    public static function get()
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    DB_DRIVER . ':host='.DB_HOST.';dbname='.DB_NAME,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
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

    public static function fetchAll(string $table, array $conditions = []): array
    {
        try {
            $sql = 'SELECT * FROM ' . $table;
            $params = [];

            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $stmt = self::get()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            if (DEBUG) {
                die('Fetch error: ' . $e->getMessage());
            }
            die('Error retrieving records');
        }
    }

    public static function fetch(string $table, array $conditions = []): ?array
    {
        try {
            $sql = 'SELECT * FROM ' . $table;
            $params = [];

            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $stmt = self::get()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            if (DEBUG) {
                die('Fetch error: ' . $e->getMessage());
            }
            die('Error retrieving record');
        }
    }

    public static function insert(string $table, array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $values = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO $table ($columns) VALUES ($values)";

            $stmt = self::get()->prepare($sql);
            $stmt->execute($data);
            return self::get()->lastInsertId();
        } catch (PDOException $e) {
            if (DEBUG) {
                die('Insert error: ' . $e->getMessage());
            }
            die('Error creating record');
        }
    }

    public static function update(string $table, array $data, array $conditions): int
    {
        try {
            $sql = "UPDATE $table SET ";
            $set = [];
            foreach (array_keys($data) as $key) {
                $set[] = "$key = :$key";
            }
            $sql .= implode(', ', $set);

            $where = [];
            foreach (array_keys($conditions) as $key) {
                $where[] = "$key = :cond_$key";
                $data[":cond_$key"] = $conditions[$key];
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);

            $stmt = self::get()->prepare($sql);
            $stmt->execute($data);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (DEBUG) {
                die('Update error: ' . $e->getMessage());
            }
            die('Error updating records');
        }
    }

    public static function delete(string $table, array $conditions): int
    {
        try {
            $sql = "DELETE FROM $table";
            $params = [];

            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }            

            $stmt = self::get()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (DEBUG) {
                die('Delete error: ' . $e->getMessage());
            }
            die('Error deleting records');
        }
    }
}