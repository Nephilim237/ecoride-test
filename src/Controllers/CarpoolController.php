<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\CarpoolModel;
use Ecoride\Ecoride\Services\AddressAutocompleteService;

class CarpoolController extends Controller
{

    private CarpoolModel $carpoolModel;
    private AddressAutocompleteService $addressService;

    public function __construct()
    {
        parent::__construct();
        $this->carpoolModel = new CarpoolModel();
        $this->addressService = new AddressAutocompleteService();
    }

    public function search(): void
    {
        $searchParams = [
            'lieu_depart' => sanitize($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => sanitize($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $_GET['date_depart'] ?? '',
            'nb_passagers' => $_GET['nb_passagers'] ?? 1,

            // Nouveaux Filtres Pour L'US4
             'is_ecologic' => $_GET['is_ecologic'] ?? '',
             'prix_max' => $_GET['prix_max'] ?? '',
             'duree_max' => $_GET['duree_max'] ?? '',
             'note_min' => $_GET['note_min'] ?? '',
        ];

        $carpools = [];
        $nextAvailableDate = null;

        // Verifier si au moins un champ de formulaire a ete rempli
        $hasCriteria = !empty($searchParams['lieu_depart']) ||
            !empty($searchParams['lieu_arrivee']) ||
            !empty($searchParams['date_depart']);
        if ($hasCriteria) {
            $carpools = $this->carpoolModel->get_carpools($searchParams);

            // Si aucun resultat, trouver la prochaine date disponible
            if (empty($carpools) && !empty($searchParams['lieu_depart']) && !empty($searchParams['lieu_arrivee'])) {
                $nextAvailableDate = $this->carpoolModel->get_next_available_date(
                    $searchParams['lieu_depart'],
                    $searchParams['lieu_arrivee']
                );
            }
        }

        $data = [
            'title' => "Resultats pour {$searchParams['lieu_depart']} {$searchParams['lieu_arrivee']} | " . APP_NAME,
            'searchParams' => $searchParams,
            'searchPerformed' => $hasCriteria,
            'nextAvailableDate' => $nextAvailableDate,
            'carpools' => $carpools,
            'activeFilters' => $this->get_active_filters($searchParams),
        ];

        $this->renderView('/carpool/index', $data);
    }

    public function index(): void
    {
        $data = [
            'searchPerformed' => false,
            'carpools' => [],
            'nextAvailableDate' => null,
            'searchParams' => [],
            'title' => "Trouver Un Covoiturage | " . APP_NAME,
        ];
        $this->renderView('carpool/index', $data);
    }

    public function autocomplete(): void
    {
        $query = $_GET['query'] ?? '';

        // Verifier que c'est une requete AJAX et qu4il y a une querie
        if (empty($query) || strlen($query) < 2) {
            $this->json([]);
            return;
        }

        $addresses = $this->addressService->search_address($query);

        $this->json($addresses);
    }

    private function get_active_filters(array $searchParams): array
    {
        $activeFilters = [];

        if (!empty($searchParams['is_ecologic']) && $searchParams['is_ecologic'] === 'on') {
            $activeFilters[] = ['name' => 'is_ecologic', 'label' => 'Ecologique : Oui'];
        }

        if (!empty($searchParams['prix_max'])) {
            $activeFilters[] = ['name' => 'prix_max', 'label' => "Prix max: {$searchParams['prix_max']} Credits"];
        }

        if (!empty($searchParams['duree_max'])) {
            $activeFilters[] = ['name' => 'duree_max', 'label' => "Duree max: {$searchParams['duree_max']} minutes"];
        }

        if (!empty($searchParams['note_min'])) {
            $activeFilters[] = ['name' => 'note_min', 'label' =>  "Note min: {$searchParams['note_min']}/5"];
        }

        return $activeFilters;
    }

}