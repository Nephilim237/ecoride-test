<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\VehicleModel;
use Ecoride\Ecoride\Services\UserService;
use Ecoride\Ecoride\Services\VehicleService;

class VehicleController extends Controller
{
    private VehicleModel $vehicleModel;
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleModel = new VehicleModel();
        $this->userService = new UserService();
    }

    public function index(): void
    {
        $this->auth->require_auth();
        $user = $this->auth->get_connected_user();

        if (!$this->auth->is_driver()) {
            $this->redirect('profile', [
                'n' => $user['nom'],
                'p' => $user['pseudo']
            ]);
        }
        $this->renderView('vehicle/index', [
            'user' => $user,
            'title' => "Ajouter Un vehicule | " . APP_NAME,
            'errors' => $this->session->get_errors('formErrors') ?? [],
            'oldData' => $this->session->get_errors('oldFormData') ?? []
        ]);
    }

    public function handle_add_car(): void
    {
        $this->auth->require_auth();
        $user = $this->auth->get_connected_user();

        if (!$this->auth->is_driver()) {
            $this->redirect('profile', [
                'n' => $user['nom'],
                'p' => $user['pseudo']
            ]);
        }

        $brand = sanitize($_POST['brand'] ?? null);
        $brandId = null;
        if (!empty($brand)) {
            $brandId = $this->vehicleModel->get_or_create_brand($brand);
        }
        $vehicleData = [
          'marque' => $brand,
          'marque_id' => $brandId,
          'modele' => sanitize($_POST['model'] ?? ''),
          'couleur' => sanitize($_POST['color'] ?? ''),
          'immatriculation' => sanitize($_POST['license_plate'] ?? ''),
          'date_premiere_immatriculation' => sanitize($_POST['license_plate_date'] ?? ''),
          'nb_places' => sanitize($_POST['seats'] ?? ''),
          'energie' => sanitize($_POST['energie'] ?? ''),
        ];

        if ($this->userService->add_user_vehicle($user['id'], $vehicleData)) {
            $this->userService->logging_user_session($user);
            $this->session->set_flash('success', 'Votre nouveau vehicule a ete pris en compte.');
            $this->redirect('profile', ['n' => $user['nom'], 'p' => $user['pseudo']]);
        } else {
            $this->session->set_flash('error', 'Oups, Erreur lors de l\'ajout du vehicule');
            // Sauvegarde des donnees du formulaire pour le pre-remplir et des eventuelles erreurs pour les afficher
            $this->session->set_errors('formErrors', $this->userService->get_errors());
            $this->session->set_errors('oldFormData', array_merge($vehicleData, [$brand]));
            $this->redirect('add-car');
        }
    }

}