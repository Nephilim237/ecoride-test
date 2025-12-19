<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Models\VehicleModel;
use Ecoride\Ecoride\Services\UserService;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    public function profile(): void
    {
        // Si l'utilisateur n'est pas authentifie, rediriger vers la page de connexion
        $this->auth->require_auth();

        $user = $this->auth->get_connected_user() ?? null;
        if (!isset($_GET['p']) || $_GET['p'] !== $user['pseudo']) {
            $this->redirect('/profile', ['n' => $user['nom'], 'p' => $user['pseudo']]);
        }

        $this->renderView('profile/profile', [
            'title' => "Profil de {$user['pseudo']} | " . APP_NAME,
            'errors' => $this->session->get_errors('formErrors') ?? [],
            'oldData' => $this->session->get_errors('oldFormData') ?? []
        ]);
    }

    public function add_preference(): void
    {
        // Si l'utilisateur n'est pas authentifie, rediriger vers la page de connexion
        $this->auth->require_auth();

        $sessionUser = $this->auth->get_connected_user();

        $property = sanitize($_POST['property'] ?? '');
        $value = isset($_POST['value']) ? 'oui' : 'non';

        $propertyData = [$property => $value];

        if ($this->userService->add_preference($sessionUser['id'], $propertyData)) {
            $this->session->set_flash('success', "Preference ajoute avec success");
        } else {
            $this->session->set_errors('formErrors', $this->userService->get_errors());
            $this->session->set_errors('oldFormData', $propertyData);

        }
        $this->redirect('profile', ['pseudo' => $sessionUser['pseudo']]);
    }


}