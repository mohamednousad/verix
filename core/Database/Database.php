<?php
namespace Verix\Core\Database;
use Verix\Core\Support\Env;
final class Database {
    private static ?\PDO $pdo=null;
    public static function connection(): \PDO {
        if (self::$pdo instanceof \PDO) return self::$pdo;
        $host=(string)Env::get('DB_HOST','localhost'); $port=Env::int('DB_PORT',3306);
        $db=(string)Env::get('DB_DATABASE',''); $charset=(string)Env::get('DB_CHARSET','utf8mb4');
        $dsn="mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $opts=[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC,\PDO::ATTR_EMULATE_PREPARES=>false];
        if (Env::bool('DB_PERSISTENT',false)) $opts[\PDO::ATTR_PERSISTENT]=true;
        self::$pdo=new \PDO($dsn,(string)Env::get('DB_USERNAME',''),(string)Env::get('DB_PASSWORD',''),$opts);
        return self::$pdo;
    }
    public static function transaction(callable $fn): mixed {
        $pdo=self::connection(); $pdo->beginTransaction();
        try { $r=$fn($pdo); $pdo->commit(); return $r; } catch(\Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); throw $e; }
    }
}
