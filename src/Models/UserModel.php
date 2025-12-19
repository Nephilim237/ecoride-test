<?php

namespace Ecoride\Ecoride\Models;

use Ecoride\Ecoride\Core\Model;
use Ecoride\Ecoride\Core\MongoManager;
use Ecoride\Ecoride\Core\RoleManager;
use InvalidArgumentException;
use MongoDB\BSON\UTCDateTime;

class UserModel extends Model
{
    protected string $table = "user";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Trouver un utilisateur grace a son email
     * @param string $email
     * @return mixed
     */
    public function find_by_email(string $email): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM $this->table WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Trouver un utilisateur grace a son email
     * @param string $identifier
     * @return mixed
     */
    public function find_by_username_or_email(string $identifier): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM $this->table WHERE pseudo = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }

    public function find_by_pseudo(string $pseudo)
    {
        $stmt = $this->connection->prepare("SELECT * FROM $this->table WHERE pseudo = ?");
        $stmt->execute([$pseudo]);
        return $stmt->fetch();

    }

    /**
     * Verifie si un email existe en base de donnee
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function email_exists(string $email, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM $this->table WHERE email = ?";
        $params = [$email];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function pseudo_exists(string $pseudo, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM $this->table WHERE pseudo = ?";
        $params = [$pseudo];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;

    }

    public function update_remember_token(int $userId, string $token): bool
    {
        $stmt = $this->connection->prepare("UPDATE $this->table SET remember_me = ? WHERE user_id = ?");
        return $stmt->execute([$token, $userId]);
    }

    public function find_by_remember_token(string $token)
    {
        $stmt = $this->connection->prepare("SELECT * FROM $this->table WHERE remember_me = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function update_profile(int $userId, $userData): bool
    {
        $allowedFields = ['nom', 'prenom', 'email', 'password', 'telephone', 'adresse', 'pseudo', 'credits', 'role_admin', 'date_naissance', 'photo'];
        $updates = [];
        $params = [];

        foreach ($userData as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = :$field";
                $params[$field] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE $this->table SET " . implode(', ', $updates) . " WHERE user_id = :user_id";
        $params['user_id'] = $userId;

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function add_driver_role(int $userId): bool
    {
        $stmt = $this->connection->prepare("
            INSERT INTO role_user (user_id, role_id)
            SELECT ?, role_id FROM role WHERE libelle = 'chauffeur'
        ");

        return $stmt->execute([$userId]);
    }

    public function add_passenger_role(int $userId): bool
    {
        $stmt = $this->connection->prepare("
            INSERT INTO role_user (user_id, role_id)
            SELECT ?, role_id FROM role WHERE libelle = 'passager'
        ");

        return $stmt->execute([$userId]);
    }

    public function save_preferences_with_mysql(int $userId, array $preferences): true
    {
        // Traitement MySQL
        $stmt = $this->connection->prepare("
            INSERT INTO user_preference (user_id, preference_id, valeur_preference) 
            VALUES (?, ?, ?)
        ");

        foreach ($preferences as $key => $value) {
            $preferenceId = $this->get_or_create_preference($key);
            $stmt->execute([$userId, $preferenceId, $value]);
        }

        return true;
    }

    public function get_or_create_preference(string $preference): int
    {
        // Si la preference existe, on recupere son identifiant et on le renvoie
        $stmt = $this->connection->prepare("SELECT preference_id FROM preference WHERE preference = ?");
        $stmt->execute([strtolower($preference)]);
        $result = $stmt->fetch();

        if ($result) return $result->preference_id;

        // Si la preference n'existe pas, on la cree
        $stmt = $this->connection->prepare("INSERT INTO preference (preference) VALUES (?)");
        $stmt->execute([strtolower($preference)]);

        return $this->connection->lastInsertId();
    }

    public function save_preferences(int $userId, array $preferences): void
    {
        // Traitements MongoDB
        $mongo = MongoManager::getInstance();
        $collection = $mongo->getCollection('preferences');

        // REcuperer les preferences actuelles
        $old = $collection->findOne(['user_id' => $userId]);
        $oldPreferences = (array)$old['preferences'] ?? [];

        // Fusionner les anciennes et les nouvelles
        $merged = array_merge($oldPreferences, $preferences);

        // Mise a jour sans ecraser
        $collection->updateOne(
            ['user_id' => $userId],
            [
                '$set' => [
                    'preferences' => $merged,
                    'update_at' => new UTCDateTime(),
                ]
            ],
            ['upsert' => true]
        );
    }

    public function update_preferences_with_mongo(int $userId, array $preference): void
    {
        // Instance de connection mongoDB et recuperation de la table preferences
        $mongo = MongoManager::getInstance();
        $collection = $mongo->getCollection('preferences');

        $property =strtolower( array_keys($preference)[0]);
        $value = strtolower(array_values($preference)[0]);
        $collection->updateOne(
            ['user_id' => $userId],
            [
                '$set' => [
                    "preferences.$property" => $value,
                    'updates_at' => new UTCDateTime(),
                ]
            ]
        );

    }

    public function get_preferences_with_mysql(int $userId): false|array
    {
        $query = "
            SELECT p.preference_id, p.preference, up.valeur_preference, up.user_id
            FROM preference p
            JOIN user_preference up on p.preference_id = up.preference_id
            WHERE up.user_id = ?
            ORDER BY up.user_id
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function get_preferences(string $userId): array
    {
        $mongo = MongoManager::getInstance();
        $collection = $mongo->getCollection('preferences');
        $preferences = $collection->findOne(['user_id' => $userId]);

        return $preferences ? (array)$preferences['preferences'] : [];
    }

    public function get_user_roles(int $userId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT r.role_id, r.libelle 
                    FROM role r
                    JOIN role_user ru ON ru.role_id = r.role_id
                    WHERE ru.user_id = ?"
        );
        $stmt->execute([$userId]);

        $roles = [];
        while ($row = $stmt->fetch()) {
            $roles[] = $row->libelle;
        }

        return $roles;
    }

    public function count_user_cars(int $userId): mixed
    {
        $sql = "
            SELECT COUNT(*)
            FROM voiture v
            JOIN user u on v.user_id = u.user_id
            WHERE u.user_id = ?
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchColumn();
    }

    public function is_driver(int $userId): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) 
                    FROM role_user ru
                    JOIN role r on ru.role_id = r.role_id
                    WHERE r.libelle = 'chauffeur' AND ru.user_id = ?"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function is_passenger(int $userId): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) 
                    FROM role_user ru
                    JOIN role r on ru.role_id = r.role_id
                    WHERE r.libelle = 'passager' AND ru.user_id = ?"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function setRole(int $userId, string $role): bool
    {
        $mask = RoleManager::get_full_mask($role);

        return $this->set_role_mask($userId, $mask);
    }

    public function set_role_mask(int $userId, int $mask): bool
    {
        if (!RoleManager::is_valid_mask($mask)) {
            throw new InvalidArgumentException("Masque de role invalide: $mask");

        }
        $stmt = $this->connection->prepare("UPDATE user SET role_admin = ? WHERE user_id = ?");
        return $stmt->execute([$mask, $userId]);
    }

    public function hasRole(int $userId, string $role): bool
    {
        $userMask = $this->get_role_mask($userId);

        return RoleManager::has_role($userMask, $role);
    }

    public function get_role_mask(int $userId): int
    {
        $stmt = $this->connection->prepare("SELECT role_admin FROM user WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result ? (int)$result->role_admin : RoleManager::VISITEUR;
    }

    public function hasMinLevel(int $userId, int $minLevel): bool
    {
        $userMask = $this->get_role_mask($userId);
        return RoleManager::has_min_level($userMask, $minLevel);
    }

    public function promote(int $userId): bool
    {
        $currentMask = $this->get_role_mask($userId);
        $nextMask = RoleManager::get_next_mask($currentMask);

        if ($nextMask === null) {
            return false; // Niveau Max
        }

        return $this->set_role_mask($userId, $nextMask);
    }

    public function demote(int $userId): bool
    {
        $currentMask = $this->get_role_mask($userId);
        $previousMask = RoleManager::get_previous_mask($currentMask);

        if ($previousMask === null) {
            return false; // Niveau Plus bas
        }

        return $this->set_role_mask($userId, $previousMask);
    }

    public function get_role_info(int $userId): array
    {
        $mask = $this->get_role_mask($userId);

        return [
            'mask' => $mask,
            'name' => RoleManager::get_admin_role_name($mask),
            'level' => RoleManager::get_level_from_role($mask),
            'all_roles' => RoleManager::get_admin_roles($mask)
        ];
    }
}