<?php

namespace Ecoride\Ecoride\Core;

abstract class Model
{
    protected $connection;
    protected $mongo;
    protected $table;

    public function __construct(){
        $this->connection = Database::getInstance()->getConnection();
        $this->mongo = MongoManager::getInstance();
    }

    // Methodes communes pour MariaDB
    public function find_by_id(int $id): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = ?");
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

    protected function mongoFind($collection, $filter = [])
    {
        $collection = $this->mongo->getCollection($collection);
        return $collection->find($filter);
    }

}