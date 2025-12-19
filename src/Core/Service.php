<?php

namespace Ecoride\Ecoride\Core;

use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Models\VehicleModel;
use Ecoride\Ecoride\Services\AuthService;

class Service
{
    protected Session $session;
    protected UserModel $userModel;
    protected VehicleModel $vehicleModel;
    protected array $errors = [];

    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new UserModel();
        $this->vehicleModel = new VehicleModel();
    }

    public function logging_user_session($user): void
    {
        $currentUser = $this->get_connected_user() ?? [];
        $userVehicles = $this->vehicleModel->get_user_vehicles($user->user_id ?? $currentUser['id']);
        $sqlPreferences = $this->userModel->get_preferences_with_mysql($user->user_id ?? $currentUser['id']);
        $mongoPreferences = $this->userModel->get_preferences($user->user_id ?? $currentUser['id']);
        $this->session->set_session('user', [
            'id' => $user->user_id ?? $currentUser['id'],
            'nom' => $user->nom ?? $currentUser['nom'] ?? null,
            'prenom' => $user->prenom ?? $currentUser['prenom'] ?? null,
            'email' => $user->email ?? $currentUser['email'],
            'pseudo' => $user->pseudo ?? $currentUser['pseudo'],
            'telephone' => $user->telephone ?? $currentUser['telephone'] ?? null,
            'adresse' => $user->adresse ?? $currentUser['adresse'] ?? null,
            'avatar' => $user->photo ?? $currentUser['photo'] ?? null,
            'vehicules' => $userVehicles,
            'nombreVehicules' => count($userVehicles),
            'sqlPreferences' => $sqlPreferences,
            'mongoPreferences' => $mongoPreferences,
            'roles' => [
                $this->userModel->is_passenger($user->user_id ?? $currentUser['id']) ? 'passager' : null,
                $this->userModel->is_driver($user->user_id ?? $currentUser['id']) ? 'chauffeur' : null,
            ],
            'date_creation' => $user->date_creation ?? $currentUser['date_creation'] ?? null,
            'adminLevelMask' => $this->userModel->get_role_mask($user->user_id ?? $currentUser['id']) ?? '',
            'adminLevelInfo' => $this->userModel->get_role_info($user->user_id ?? $currentUser['id']) ?? ''
        ]);
    }


    public function get_connected_user_id()
    {
        $user = $this->get_connected_user();
        return $user ? $user['id'] : null;
    }

    public function get_connected_user()
    {
        return $this->session->get_session('user');
    }

    public function is_passenger(): bool
    {
        $user = $this->get_connected_user();
        return $user && in_array('passager', $user['roles']);
    }

    /**
     * Middleware pour les utilisateur deja connectes
     * @return void
     */
    public function require_guest(): void
    {
        if ($this->is_logged_in()) {
            redirect('/profile');
        }
    }

    public function is_logged_in(): bool
    {
        return $this->session->has_session('user');
    }

    /**
     * Middleware pour les chauffeurs uniquement
     * @return void
     */
    public function require_driver(): void
    {
        $this->require_auth();
        if (!$this->is_driver()) {
            $this->session->set_flash('error', 'Acces refuse');
            redirect('/');
        }
    }

    /**
     * Middleware de protection des routes
     * @return void
     */
    public function require_auth(): void
    {
        if (!$this->is_logged_in()) {
            $this->session->set_flash('error', 'Acces refuse');
            redirect('/login');
        }
    }

    public function is_driver(): bool
    {
        $user = $this->get_connected_user();
        return $user && in_array('chauffeur', $user['roles']);
    }

    // Methodes utilitaires pour la gestion du tableau des erreurs
    public function get_errors(): array
    {
        return $this->errors;
    }

    public function has_errors(): bool
    {
        return !empty($this->errors);
    }

    public function get_error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function clear_errors(): void
    {
        $this->errors = [];
    }
}