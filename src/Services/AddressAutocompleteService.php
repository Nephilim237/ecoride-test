<?php

namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database;

class AddressAutocompleteService
{
    private \PDO $connexion;

    public function __construct()
    {
        $this->connexion = Database::getInstance()->getConnection();
    }

    public function search_address(string $query): array
    {
        // Nettoyer la query
        $query = trim($query);

        if (mb_strlen($query) < 2) return [];

        // Recuperation des resultats en BDD
        $dbResults = $this->get_database_suggestions($query);

        // Recuperation des resultats de l'API
        $apiResults = $this->get_api_suggestions($query);

        // Combiner  les deux
        return $this->merge_and_deduplicate($dbResults, $apiResults);
    }

    private function get_database_suggestions(string $query): array
    {
        try {
            // Rechercher les lieux de depart et d'arriver des covoiturages dans notre base de donnees
            $sql = "
                SELECT DISTINCT lieu_depart as label, 'database' as source
                FROM covoiturage
                WHERE lieu_depart LIKE ? AND statut = 'prevu'
                UNION
                SELECT DISTINCT lieu_arrivee as label, 'database' as source
                FROM covoiturage
                WHERE lieu_arrivee LIKE ? AND statut = 'prevu'
                ORDER BY label
                LIMIT 10
            ";

            $stmt = $this->connexion->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);

            $results = $stmt->fetchAll();

            return array_map(function($result) {
                return [
                    'label' => $result->label,
                    'source' => $result->source,
                    'type' => 'covoiturage', // Pour indiquer aue ca provient de la BDD
                ];
            }, $results);

        } catch(\PDOException $e) {
            error_log("Erreur recherche base de donnees: {$e->getMessage()}");
            return [];
        }
    }

    public function get_api_suggestions(string $query): array
    {
        $url = "https://api-adresse.data.gouv.fr/search/?q=". urlencode($query) . "&limit=8&type=street&autocomplte=1";

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => APP_NAME . '/1.0'
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

//            if (!$response) {
//                return [];
//            }

            $data =json_decode($response, true); // Data contiendra une cle 'features' donc on aura $data['features']

//            if (!$data || !isset($data['features'])) {
//                return [];
//            }

            $results = [];
            $addedLabels = [];

            foreach ($data['features'] as $feature) {
                if (!isset($feature['properties'])) {
                    continue;
                }

                $properties = $feature['properties'];
                $label = $properties['label'] ?? '';
                $city = $properties['city'] ?? '';
                $postcode = $properties['postcode'] ?? '';
                $context = $properties['context'] ?? '';
                if ($label && !in_array($label, $addedLabels)) {
                    $results[] = [
                        'label' => $label,
                        'city' => $city,
                        'postcode' => $postcode,
                        'context' => $context,
                        'source' => 'api',
                        'type' => $properties['type'] ?? 'street'
                    ];

                    $addedLabels[] = $label;
                }

                if (count($results) > 6) {
                    break;
                }
            }

            return $results ?? [];

        } catch(\Exception $e) {
            error_log("Erreur API adresse: {$e->getMessage()}");
            return [];
        }
    }

    private function merge_and_deduplicate(array $dbResults, array $apiResults): array
    {
        $merged = [];
        $usedLabels = [];

        // D'abord les resultats de la base de donnees
        foreach ($dbResults as $dbResult) {

            $label = trim(strtolower($dbResult['label']));

            if (!in_array($label, $usedLabels)) {
                $merged[] = $dbResult;
                $usedLabels[] = $label;
            }
        }

        // Ensuite les resultats de l'API
        foreach ($apiResults as $apiResult) {
            $label = trim(strtolower($apiResult['label']));

            if (!in_array($label, $usedLabels)) {
                $merged[] = $apiResult;
                $usedLabels[] = $label;
            }
        }

        // On va trier le tableau pour une meilleure presentation
        usort($merged, function($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // Limiter au 10 premiers resultats
        return array_slice($merged, 0, 10);
    }

}