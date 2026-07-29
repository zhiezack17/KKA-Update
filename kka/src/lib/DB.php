<?php
/**
 * Singleton PDO wrapper.
 */
class DB {
    private static ?PDO $pdo = null;

    public static function init(array $cfg): PDO {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $cfg['db_host'], $cfg['db_port'], $cfg['db_name']
        );
        self::$pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return self::$pdo;
    }

    public static function pdo(): PDO {
        if (!self::$pdo) {
            throw new RuntimeException('Database belum diinisialisasi.');
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $params = []): PDOStatement {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array {
        return self::q($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = []) {
        $row = self::q($sql, $params)->fetch(PDO::FETCH_NUM);
        return $row[0] ?? null;
    }

    public static function insert(string $table, array $data): int {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(',', array_map(fn($c) => "`$c`", $cols)),
            implode(',', array_map(fn($c) => ":$c", $cols))
        );
        self::q($sql, $data);
        return (int)self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, array $where): int {
        $set = implode(',', array_map(fn($c) => "`$c`=:set_$c", array_keys($data)));
        $wh  = implode(' AND ', array_map(fn($c) => "`$c`=:wh_$c", array_keys($where)));
        $params = [];
        foreach ($data  as $k => $v) $params["set_$k"] = $v;
        foreach ($where as $k => $v) $params["wh_$k"]  = $v;
        $stmt = self::q("UPDATE `$table` SET $set WHERE $wh", $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, array $where): int {
        $wh = implode(' AND ', array_map(fn($c) => "`$c`=:$c", array_keys($where)));
        return self::q("DELETE FROM `$table` WHERE $wh", $where)->rowCount();
    }
}
