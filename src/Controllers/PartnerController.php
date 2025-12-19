<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Models\VehicleModel;
use Ecoride\Ecoride\Services\UserService;

class PartnerController extends Controller
{
    protected UserModel $userModel;
    private VehicleModel $vehicleModel;
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->vehicleModel = new VehicleModel();
        $this->userService = new UserService();
    }

    public function become_partner(): void
    {
        // Si l'utilisateur n'est pas authentifie, rediriger vers la page de connexion
        $this->auth->require_auth();
        $sessionUser = $this->auth->get_connected_user();
        $user = $this->userModel->find_by_id($sessionUser['id']);

        // Rediriger vers profil si utilisateur deja chauffeur
        if ($this->userModel->is_driver($user->user_id)) {
            $this->session->set_flash('info', "Vous etes deja un partenaire");
            $this->redirect('/profile', [
                'n' => $user->nom,
                'p' => $user->pseudo
            ]);
        }

        $this->renderView('profile/become-partner', [
            'title' => "Devenir Partenaire | " . APP_NAME,
            'user' => $user,
            'errors' => $this->session->get_errors('formErrors') ?? [],
            'oldData' => $this->session->get_errors('oldFormData') ?? []
        ]);
    }

    /**
     * @throws \Exception
     */
    public function handle_become_partner(): void
    {
        $this->auth->require_auth();
        $sessionUser = $this->auth->get_connected_user();
        $user = $this->userModel->find_by_id($sessionUser['id']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profile', ['n' => $user->nom, 'p' => $user->pseudo]);
            return;
        }

        // Info sur le postulant
        $profileData = [
            'nom' => sanitize($_POST['name'] ?? ''),
            'prenom' => sanitize($_POST['firstname']) ?? '',
            'telephone' => sanitize($_POST['phone'] ?? ''),
            'adresse' => sanitize($_POST['address'] ?? ''),
            'date_naissance' => sanitize($_POST['birthdate']) ?? '',
        ];

        // Info sur le vehicule du postulant
        // Recuperation de la marque
        $brand = sanitize($_POST['brand'] ?? null);
        $brandId = null;
        if (!empty($brand)) {
            $brandId = $this->vehicleModel->get_or_create_brand($brand);
        }
        $vehicleData = [
            'marque_id' => $brandId,
            'marque' => $brand,
            'modele' => sanitize($_POST['model'] ?? ''),
            'immatriculation' => sanitize($_POST['license_plate'] ?? ''),
            'energie' => isset($_POST['energie']) ? 1 : 0,
            'couleur' => sanitize($_POST['color'] ?? ''),
            'nb_places' => sanitize($_POST['seats'] ?? ''),
            'date_premiere_immatriculation' => sanitize($_POST['license_plate_date'] ?? ''),
        ];

        // Preferences
        $preferenceData = [
            'animaux' => isset($_POST['animal']) ? 'oui' : 'non',
            'fumeurs' => isset($_POST['smoker']) ? 'oui' : 'non'
        ];


        if ($this->userService->complete_partner_profile($user->user_id, $profileData, $vehicleData, $preferenceData)) {
            $this->userService->logging_user_session($user);
            $this->session->set_flash('success', 'Felicitations, Vous etes desormais partenaire chauffeur.');
            $this->redirect('profile', ['n' => $user->nom, 'p' => $user->pseudo]);
        } else {
            $this->session->set_flash('error', 'Oups, Une erreur est survenue');
            // Sauvegarde des donnees du formulaire pour le pre-remplir et des eventuelles erreurs pour les afficher
            $this->session->set_errors('formErrors', $this->userService->get_errors());
            $this->session->set_errors('oldFormData', array_merge($profileData, $vehicleData, $preferenceData));
            $this->redirect('become-partner');
        }
    }

    public function become_passenger(): void
    {
        $this->auth->require_auth();

        $sessionUser = $this->auth->get_connected_user();
        $user = $this->userModel->find_by_id($sessionUser['id']);
        try {
            $this->userModel->add_passenger_role($user->user_id);
            $this->session->set_flash('success', 'Vous etes maintenant passager');
            $this->redirect('rides');
        } catch (\Exception $e) {
            $this->session->set_flash('error', "Erreur {$e->getMessage()}.");
            $this->redirect('/profile', ['n' => $user->nom, 'p' => $user->pseudo]);
        }
    }
}
