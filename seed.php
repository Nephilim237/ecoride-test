<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

use Ecoride\Ecoride\Database\Seeder;
use Ecoride\Ecoride\Services\PaginationService;
use Ecoride\Ecoride\Services\CarpoolService;

echo "🚗 === Générateur de données Ecoride === 🚗 \n";
echo "==========================================\n\n";

echo "⚠️  ATTENTION : Cette opération va :\n";
echo "   • Effacer TOUTES les données existantes\n";
echo "   • Recréer les schémas de base\n";
echo "   • Générer de nouvelles données de test\n\n";

echo "Voulez-vous continuer ? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
if ($line != 'yes') {
    echo "❌ Opération annulée.\n";
    exit;
}
fclose($handle);

echo "\n";

try {
    $startTime = microtime(true);

    $seeder = new Seeder();
    $seeder->run();

    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);

    echo "\n🎉 Base de données peuplée avec succès ! \n";
    echo "⏱️  Temps d'exécution : {$executionTime}s\n";
    echo "📊 Vous pouvez maintenant tester votre application Ecoride !\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de la génération : " . $e->getMessage() . "\n";
    echo "📁 Fichier : " . $e->getFile() . "\n";
    echo "📍 Ligne : " . $e->getLine() . "\n";

    if ($e->getPrevious()) {
        echo "🔍 Cause : " . $e->getPrevious()->getMessage() . "\n";
    }
}

//$query = "
//    SELECT c.covoiturage_id, date_depart, heure_depart, lieu_depart,
//           date_arrivee, heure_arrivee, lieu_arrivee, c.statut, c.nb_places,
//           prix_personne, c.conducteur_id, c.voiture_id, c.date_creation,
//           v.voiture_id, modele, immatriculation, energie, couleur, v.nb_places,
//           date_premiere_immatriculation, v.user_id,v.marque_id, v.date_creation,
//           m.marque_id, libelle
//    FROM covoiturage c
//    JOIN user u ON c.conducteur_id = u.user_id
//    JOIN voiture v ON c.voiture_id = v.voiture_id
//    JOIN marque m ON v.marque_id = m.marque_id
//    LEFT JOIN (
//        SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees
//        FROM reservation
//        WHERE statut IN ('confirme', 'en attente')
//        GROUP BY covoiturage_id
//    ) r ON c.covoiturage_id = r.covoiturage_id
//    WHERE c.statut = 'prevu'
//    AND c.date_depart >= CURDATE()
//    -- AND c.lieu_depart LIKE '%Marseille%'
//    -- AND c.lieu_arrivee = '%Toulouse%'
//    -- AND c.date_depart = CURDATE()
//    -- AND (c.nb_places - IFNULL(places_reservees, 0)) > 0
//    -- AND (c.nb_places - IFNULL(places_reservees, 0)) > ?
//    -- ORDER BY c.date_depart, c.heure_depart
//    -- LIMIT 10 OFFSET 0
//";


//
//public function get_covoiturages_with_pagination_and_search_params(
//    array             $searchParams,
//    CarpoolService       $rideService,
//    PaginationService $pagination = null
//): array
//{
//    $countQuery = "
//                SELECT COUNT(DISTINCT c.covoiturage_id) as covoiturages_total
//            FROM covoiturage c
//            JOIN user u ON c.conducteur_id = u.user_id
//            JOIN voiture v ON c.voiture_id = v.voiture_id
//            JOIN marque m ON v.marque_id = m.marque_id
//            LEFT JOIN (
//                SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees
//                FROM reservation
//                WHERE statut IN ('confirme', 'en attente')
//                GROUP BY covoiturage_id
//            ) r ON c.covoiturage_id = r.covoiturage_id
//            WHERE c.statut = 'prevu'
//            AND c.date_depart >= CURDATE()
//        ";
//
//    $query = "
//            SELECT c.covoiturage_id, date_depart, heure_depart, lieu_depart,
//                   date_arrivee, heure_arrivee, lieu_arrivee, c.statut, c.nb_places as capacite_covoiturage,
//                   prix_personne, c.conducteur_id, c.voiture_id, c.date_creation,
//                   v.voiture_id, modele, immatriculation, energie, couleur, v.nb_places as capacite_vehicule,
//                   date_premiere_immatriculation, v.user_id,v.marque_id, v.date_creation,
//                   m.marque_id, libelle as marque, u.user_id, u.nom, u.prenom, u.email,u.telephone,
//                   adresse, pseudo, credits, role_admin, date_naissance, photo, u.date_creation, remember_me,
//                   (c.nb_places - SUM(r2.nb_place_reservee)) as places_restantes
//            FROM covoiturage c
//            JOIN user u ON c.conducteur_id = u.user_id
//            JOIN voiture v ON c.voiture_id = v.voiture_id
//            JOIN marque m ON v.marque_id = m.marque_id
//            JOIN reservation r2 on c.covoiturage_id = r2.covoiturage_id
//            LEFT JOIN (
//                SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees
//                FROM reservation
//                WHERE statut IN ('confirme', 'en attente')
//                GROUP BY covoiturage_id
//            ) r ON c.covoiturage_id = r.covoiturage_id
//            WHERE c.statut = 'prevu'
//            AND c.date_depart >= CURDATE()
//        ";
//
//    $conditions = [];
//    $params = [];
//
//    // Construction des filtres
//
//    // 1. Filtre sur le lieu de depart
//    if (!empty($searchParams['lieu_depart'])) {
//        $conditions[] = "c.lieu_depart LIKE ?";
//        $params[] = "%{$searchParams['lieu_depart']}%";
//    }
//
//    // 2. Filtre sur le lieu d'arrivee
//    if (!empty($searchParams['lieu_arrivee'])) {
//        $conditions[] = "c.lieu_arrivee LIKE ?";
//        $params[] = "%{$searchParams['lieu_arrivee']}%";
//    }
//
//    // 3. Filtre sur la date de depart
//    if (!empty($searchParams['date_depart'])) {
//        $conditions[] = "c.date_depart = ?";
//        $params[] = $searchParams['date_depart'];
//    }
//
//    if (!empty($conditions)) {
//        $query .= " AND " . implode(" AND ", $conditions);
//        $countQuery .= " AND " . implode(" AND ", $conditions);
//    }
//
//    // Filre par places restantes - Avec colonne claculee
//    $query .= " AND (c.nb_places - IFNULL(places_reservees, 0)) > 0";
//    $countQuery .= " AND (c.nb_places - IFNULL(places_reservees, 0)) > 0";
//
//    if (!empty($searchParams['nb_passagers'])) {
//        $query .= " AND (c.nb_places - IFNULL(places_reservees, 0)) > ?";
//        $countQuery .= " AND (c.nb_places - IFNULL(places_reservees, 0)) > ?";
//        $params[] = $searchParams['nb_passagers'];
//    }
//
//    $query .= " ORDER BY c.date_depart, c.heure_depart";
//
//    try {
//        error_log("Requete finale: $countQuery");
//        error_log("params: " . print_r($params, true));
//
//        $stmtCount = $this->connection->prepare($countQuery);
//        $stmtCount->execute($params);
//        $totalResults = $stmtCount->fetch();
//        $totalItems = $totalResults->covoiturages_total ?? 0;
//
//        // Gestion de la pahination
//        dump($pagination);
//        if ($pagination) {
//            $query .= " LIMIT ? OFFSET ?";
//            $params[] = $pagination->get_limit();
//            $params[] = $pagination->get_offest();
//        }
//
//        error_log("Requete finale: $query");
//        $stmtData = $this->connection->prepare($query);
//
//        // Liaison des parametres
//        foreach ($params as $key => $param) {
//            $stmtData->bindValue($key + 1, $param);
//        }
//
//        $stmtData->execute();
//        $results = $stmtData->fetchAll();
//
//        return [
////                'rides' => $rideService->format_rides_results($results),
//            'rides' => $results,
//            'totalItems' => $totalItems
//        ];
//
//    } catch (\PDOException $e) {
//        error_log("Erreur recherche covoiturage:  {$e->getMessage()} : {$e->getLine()}");
//        error_log("Trace: " . $e->getTraceAsString());
//        return [
//            'rides' => [],
//            'totalItems' => 0
//        ];
//    }
//}
