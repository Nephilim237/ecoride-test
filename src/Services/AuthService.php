<?php

namespace Ecoride\Ecoride\Services;


use Ecoride\Ecoride\Core\Service;
use Ecoride\Ecoride\Core\Session;
use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Models\VehicleModel;

class AuthService extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register(array $userData): bool
    {
        // Hasher le mot de passe
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Validation des donnees
        if (!$this->validate_registration($userData)) {
            return false;
        }

        return $this->userModel->create($userData);
    }

    public function attempt_to_connect(string $identifier, string $password, bool $remember = false): bool
    {
        $user = $this->userModel->find_by_username_or_email($identifier);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->password)) {
            // Si le mot de passe fourni dans le formulaire ne match pas avec le mot de passe en BDD
            return false;
        }

        // Gerer le "Remember me"
        if ($remember) {
            $this->set_remember_token($user->user_id);
        }

        $this->login($user);
        return true;
    }

    /**
     * Connecter un utilisateur
     * Sauvegarde ses infos en Session
     * @param $user
     * @return void
     */
    public function login($user): void
    {
        $this->logging_user_session($user);
    }

    public function login_with_remember_token(): bool
    {
        if (isset($_COOKIE['remember_me'])) {
            $user = $this->userModel->find_by_remember_token($_COOKIE['remember_me']);

            if ($user) {
                $this->logging_user_session($user);
                return true;
            }
        }
        return false;
    }

    public function logout(): void
    {
        $this->session->remove_session('user');
        setcookie('remember_me', '', time() - 3600, '/');
        $this->session->destroy_session();
        redirect('/login');
    }


    private function validate_registration(array $userData): bool
    {
        // Verifier que l'email n'est pas encore utilise
        if ($this->userModel->email_exists($userData['email'])) {
            $this->session->set_flash('error', 'Cet email est deja utilise.');
            return false;
        }


        // Verifier que le pseudo n'est pas encore utilise
        if ($this->userModel->pseudo_exists($userData['pseudo'])) {
            $this->session->set_flash('error', 'Ce pseudo est deja utilise');
            return false;
        }

        // Validation de l'email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flash('error', 'Email invalide');
            return false;
        }

        return true;
    }

    private function set_remember_token(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $this->userModel->update_remember_token($userId, $token);

        // Cookie valable 30 jours
        setcookie('remember_me', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
    }
}