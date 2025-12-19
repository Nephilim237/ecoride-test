<?php

namespace Ecoride\Ecoride\Core;

class RoleManager
{
    // Definition des bits pour chaque role
    const VISITEUR = 1 << 0; // 1 (0001)
    const UTILISATEUR = 1 << 1; // 2 (0010)
    const EMPLOYE = 1 << 2; // 4 (0100)
    const ADMINISTRATEUR = 1 << 3; // 8 (1000)

    // Masque combines pour la hierarchie
    const ROLE_UTILISATEUR = self::VISITEUR | self::UTILISATEUR; // 3 (0011)
    const ROLE_EMPLOYE = self::ROLE_UTILISATEUR | self::EMPLOYE; // 7 (0111)
    const ROLE_ADMIN = self::ROLE_EMPLOYE | self::ADMINISTRATEUR; // 15 (1111)

    // Mapping des roles vers leurs masques complets
    const MASQUES = [
        'visiteur' => self::VISITEUR,
        'utilisateur' => self::ROLE_UTILISATEUR,
        'employe' => self::ROLE_EMPLOYE,
        'administrateur' => self::ROLE_ADMIN
    ];

    // Niveaux pour l'interface utilisateur
    const NIVEAUX = [
        'visiteur' => 0,
        'utilisateur' => 1,
        'employe' => 2,
        'administrateur' => 3
    ];

    /**
     * Verifier si un utilisateur possede un role
     * @param int $userRole
     * @param string $role
     * @return bool
     */
    public static function has_role(int $userRole, string $role): bool
    {
        $requiredRole = self::MASQUES[$role] ?? self::VISITEUR;
        return ($userRole & $requiredRole) === $requiredRole;
    }

    /**
     * Ajouter un role a un utilisateur
     * @param $userRole
     * @param $role
     * @return int
     */
    public static function add_role($userRole, $role): int
    {
        return $userRole | $role;
    }

    /**
     * Retirer un role a un utilisateur
     * @param int $userRole
     * @param int $role
     * @return int
     */
    public static function remove_role(int $userRole, int $role): int
    {
        return $userRole & -$role;
    }

    /**
     * Obtenir un masque complet pour un role hierarchique
     * @param string $mask
     * @return int
     */
    public static function get_full_mask(string $mask): int
    {
        return self::MASQUES[$mask] ?? self::VISITEUR;
    }

    /**
     * Obtenir le niveau d'un utilisateur a partir de son role
     * @param int $mask
     * @return int
     */
    public static function get_level_from_role(int $mask): int
    {
        if (self::has_role($mask, self::ADMINISTRATEUR)) return 3;
        if (self::has_role($mask, self::EMPLOYE)) return 2;
        if (self::has_role($mask, self::UTILISATEUR)) return 1;
        return 0;
    }


    /**
     * Obtenir le nom du role principa en fonction du masque de l'utilisateur
     * @param int $mask
     * @return string
     */
    public static function get_admin_role_name(int $mask): string
    {
        if (self::has_role($mask, self::ADMINISTRATEUR)) return 'Administrateur';
        if (self::has_role($mask, self::EMPLOYE)) return 'Employé';
        if (self::has_role($mask, self::UTILISATEUR)) return 'Utilisateur';
        return 'Visiteur';
    }

    /**
     * Obtenir tous les roles alloues a un utilisateur
     * @param int $role
     * @return array
     */
    public static function get_admin_roles(int $role): array
    {
        $roles = [];

        if (self::has_role($role, self::ADMINISTRATEUR)) $roles[] = 'Administrateur';
        if (self::has_role($role, self::EMPLOYE)) $roles[] = 'Employé';
        if (self::has_role($role, self::UTILISATEUR)) $roles[] = 'Utilisateur';
        if (self::has_role($role, self::VISITEUR)) $roles[] = 'Visiteur';

        return $roles;
    }

    /**
     * Verifier les permissions basees sur le niveaux
     * @param int $userRole
     * @param int $minLevel
     * @return bool
     */
    public static function has_min_level (int $userRole, int $minLevel): bool
    {
        $userLevel = self::get_level_from_role($userRole);
        return $userLevel >= $minLevel;
    }

    public static function is_valid_mask(int $mask): bool
    {
        return in_array($mask, [1, 3, 7, 15]);
    }

    public static function get_next_mask(int $currentMask): ?int
    {
        $masks = [1, 3, 7, 15];
        $currentIndex = array_search($currentMask, $masks);

        return $masks[$currentIndex + 1] ?? null;
    }


    public static function get_previous_mask(int $currentMask): ?int
    {
        $masks = [1, 3, 7, 15];
        $currentIndex = array_search($currentMask, $masks);

        return $masks[$currentIndex - 1] ?? null;
    }

}