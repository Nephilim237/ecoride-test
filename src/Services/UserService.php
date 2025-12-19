<?php

namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\Service;
use Exception;

class UserService extends Service
{
    private ValidationService $validationService;
    private \PDO $connection;
    private array $profileErrors = [];
    private array $vehicleErrors = [];
    private array $preferenceErrors = [];

    public function __construct()
    {
        parent::__construct();
        $this->validationService = new ValidationService();
        $this->connection = Database::getInstance()->getConnection();
    }

    /**
     * @throws Exception
     */
    public function validate_profile_data(array $data): array
    {
        $this->validationService->clear_errors();

        // Validation explicite et directe
        $this->validationService->validate_required('nom', $data['nom'] ?? '');
        $this->validationService->validate_max('nom', $data['nom'] ?? '', 64);

        $this->validationService->validate_required('prenom', $data['prenom'] ?? '');
        $this->validationService->validate_max('prenom', $data['prenom'] ?? '', 64);

        $this->validationService->validate_required('telephone', $data['telephone'] ?? '');
        $this->validationService->validate_max('telephone', $data['telephone'] ?? '', 20);
        $this->validationService->validate_phone('telephone', $data['telephone'] ?? '');

        $this->validationService->validate_required('adresse', $data['adresse'] ?? '');
        $this->validationService->validate_max('adresse', $data['adresse'] ?? '', 255);

        $this->validationService->validate_required('date_naissance', $data['date_naissance'] ?? '');
        $this->validationService->validate_date('date_naissance', $data['date_naissance'] ?? '');
        $this->validationService->validate_adult('date_naissance', $data['date_naissance'] ?? '');

        $this->profileErrors = $this->validationService->get_errors();
        $isValid = !$this->validationService->has_errors();

        return [
            'errors' => $this->profileErrors,
            'isValid' => $isValid
        ];
    }


    public function validate_vehicle_data(array $data): array
    {
        $this->validationService->clear_errors();

        $this->validationService->validate_required('immatriculation', $data['immatriculation'] ?? '');
        $this->validationService->validate_max('immatriculation', $data['immatriculation'] ?? '', 20);
        $this->validationService->validate_license_plate('immatriculation', $data['immatriculation'] ?? '');
        $this->validationService->validate_unique('immatriculation', $data['immatriculation'] ?? '', 'voiture', 'immatriculation');

        $this->validationService->validate_required('date_premiere_immatriculation', $data['date_premiere_immatriculation'] ?? '');
        $this->validationService->validate_date('date_premiere_immatriculation', $data['date_premiere_immatriculation'] ?? '');
        // On pourrait egalement verifier que la date d'immatriculation n'est pas posterieu a la date actuelle

        // Validation explicite et directe
        $this->validationService->validate_required('marque', $data['marque'] ?? '');
        $this->validationService->validate_max('marque', $data['marque'] ?? '', 64);

        $this->validationService->validate_required('modele', $data['modele'] ?? '');
        $this->validationService->validate_max('modele', $data['modele'] ?? '', 64);

        $this->validationService->validate_required('couleur', $data['couleur'] ?? '');
        $this->validationService->validate_max('couleur', $data['couleur'] ?? '', 64);

        $this->validationService->validate_required('nb_places', $data['nb_places'] ?? '');
        $this->validationService->validate_numeric('nb_places', $data['nb_places'] ?? '');

        $this->vehicleErrors = $this->validationService->get_errors();
        $isValid = !$this->validationService->has_errors();

        return [
            'errors' => $this->vehicleErrors,
            'isValid' => $isValid
        ];
    }

    /**
     * @throws Exception
     */
    public function complete_partner_profile(int $userId, array $profileData, array $vehicleData, array $preferencesData): bool
    {
        $validateProfile = $this->validate_profile_data($profileData);
        $validateVehicle = $this->validate_vehicle_data($vehicleData);
        // Validate en cascade
        if (!$validateProfile['isValid'] || !$validateVehicle['isValid'])
            return false;

        unset($vehicleData['marque']);
        $this->connection->beginTransaction();
        try {
            // Completer profil
            $this->userModel->update_profile($userId, $profileData);

            // Ajouter le role chauffeur
            $this->userModel->add_driver_role($userId);

            // Creer le vehicule
            $this->vehicleModel->add_vehicle($userId, $vehicleData);

            // Sauvegarder les preference mysql
            $this->userModel->save_preferences_with_mysql($userId, $preferencesData);

            // Sauvegarder les preferences MongoDB
            $preferences = [
                'animaux' => $preferencesData['animaux'] ?? false,
                'fumeur' => $preferencesData['fumeurs'] ?? false
            ];

            $this->userModel->save_preferences($userId, $preferences);
            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            error_log("Erreur completion profil partenaire: {$e->getMessage()}");
            $this->connection->rollBack();
            return false;
        }
    }

    public function add_user_vehicle(int $userId, array $vehicleData): bool
    {
        if (!$this->validate_vehicle_data($vehicleData)['isValid']) {
            return false;
        }
        unset($vehicleData['marque']);

        try {
            $vehicleData['user_id'] = $userId;
            $this->vehicleModel->add_vehicle($userId, $vehicleData);

            return true;
        } catch (\PDOException $e) {
            error_log("Erreur ajout vehicule: {$e->getMessage()}");
            return false;
        }
    }

    public function add_preference(int $userId, array $preference): bool
    {
        $this->validationService->clear_errors();

        $this->validationService->validate_required('preference', array_keys($preference)[0]);
        $this->validationService->validate_max('preference', array_keys($preference)[0], 64);

        if ($this->validationService->has_errors()) {
            $this->preferenceErrors = $this->validationService->get_errors();
            return false;
        }

        $this->userModel->save_preferences($userId, $preference);
        $this->userModel->save_preferences_with_mysql($userId, $preference);

        return true;
    }

    public function get_errors(): array
    {
        return array_merge($this->profileErrors, $this->vehicleErrors, $this->preferenceErrors);
    }
}
