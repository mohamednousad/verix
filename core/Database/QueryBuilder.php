<?php
namespace Verix\Core\Database;
final class QueryBuilder {
    private \PDO $pdo;
    public function __construct(){ $this->pdo=Database::connection(); }
    public function select(string $sql,array $params=[]): array { $s=$this->pdo->prepare($sql);$s->execute($params);return $s->fetchAll(); }
    public function first(string $sql,array $params=[]): ?array { $s=$this->pdo->prepare($sql);$s->execute($params);$r=$s->fetch();return $r===false?null:$r; }
    public function execute(string $sql,array $params=[]): int { $s=$this->pdo->prepare($sql);$s->execute($params);return $s->rowCount(); }
    public function insert(string $sql,array $params=[]): string { $s=$this->pdo->prepare($sql);$s->execute($params);return $this->pdo->lastInsertId(); }
}
