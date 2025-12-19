<?php

namespace Ecoride\Ecoride\Core;

abstract class Model
{
    protected \PDO $connection;
    protected ?MongoManager $mongo;
    protected string $table;

    public function __construct(){
        $this->connection = Database::getInstance()->getConnection();
        $this->mongo = MongoManager::getInstance();
    }

    // Methodes communes pour MariaDB
    public function find_by_id(int $id): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function find_all(): false|array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' .implode(', :', array_keys($data));

        $stmt = $this->connection->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        return $stmt->execute($data);
    }

    // Methodes Pour MongoDB
    protected function mongoInsert($collection, $data): \MongoDB\InsertOneResult
    {
        $collection = $this->mongo->getCollection($collection);
        return $collection->insertOne($data);
    }

    protected function mongoFind($collection, $filter = []): \MongoDB\Driver\CursorInterface&\Iterator
    {
        $collection = $this->mongo->getCollection($collection);
        return $collection->find($filter);
    }

    public function get_notices(): false|array
    {
        $stmt = $this->connection->query("
            SELECT a.*, u.photo, u.nom, u.prenom
            FROM avis a
            JOIN user u ON a.passager_id = u.user_id
            WHERE statut = 'publie' AND note >= '3.0' 
            ORDER BY date_creation DESC 
            LIMIT 12
        ");
        return $stmt->fetchAll();
    }

}