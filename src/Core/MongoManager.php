<?php

namespace Ecoride\Ecoride\Core;

use MongoDB\Client;
use MongoDB\Collection;

class MongoManager
{
    private static ?MongoManager $instance = null;
    private $client;
    private \MongoDB\Database $database;

    private function __construct() {
        try {
            if (!extension_loaded('mongodb')) {
                throw new \Exception('Extension MongoDB non disponible.');
            }
            $this->client = new Client(MONGO_DB_URI);
            $this->client->listDatabases();
            $this->database = $this->client->selectDatabase(MONGO_DB_NAME);
        } catch (\Exception $e) {
            error_log("MongoDB non disponible: {$e->getMessage()}");
            $this->client = null;
        }
    }

    public static function getInstance(): ?MongoManager
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getCollection($name): Collection
    {
        return $this->database->selectCollection($name);
    }

    public function getConnection(): Client
    {
        return $this->client;
    }
}