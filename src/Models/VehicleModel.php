<?php

namespace Ecoride\Ecoride\Models;

use Ecoride\Ecoride\Core\Model;

class VehicleModel extends Model
{

    protected string $table = "voiture";
    public function __construct()
    {
        parent::__construct();
    }

    public function add_vehicle(int $userId, array $vehicleData): bool
    {
        $vehicleData['user_id'] = $userId;
        return $this->create($vehicleData);
    }

    public function get_user_vehicles(int $userId): false|array
    {
        $stmt = $this->connection->prepare("
            SELECT v.*, m.libelle as marque
            FROM voiture v
            JOIN marque m on v.marque_id = m.marque_id
            WHERE v.user_id = ?
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function get_or_create_brand(string $brandName) {
        // Si la marque existe, on la recupere
        $sql = "SELECT  marque_id FROM marque WHERE libelle = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$brandName]);
        $result = $stmt->fetch();

        if ($result) return $result->marque_id;

        // Si marque inexistante, creer marque
        $stmt = $this->connection->prepare("INSERT INTO marque (libelle) VALUES (?)");
        $stmt->execute([$brandName]);

        return $this->connection->lastInsertId();
    }
}