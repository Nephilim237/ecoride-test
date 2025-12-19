<?php

namespace Ecoride\Ecoride\database;

use Faker\Factory;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\core\MongoManager;

class Seeder
{
    private \Faker\Generator $faker;
    private \PDO $db;
    private ?MongoManager $mongo;
    private array $userIds = [];
    private array $voitureIds = [];
    private array $covoiturageIds = [];
    private array $marqueIds = [];

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
        $this->db = Database::getInstance()->getConnection();
        $this->mongo = MongoManager::getInstance();
    }

    public function run(): void
    {
        echo "🚗 Debut de la generation des donnees Ecoride ... \n\n";

        $this->clearExistingData();
        $this->seedMarques();
        $this->seedUsers();
        $this->seedVoitures();
        $this->seedUserRoles();
        $this->seedCovoiturages();
        $this->seedReservations();
        $this->seedAvis();
        $this->seedParametres();
        $this->seedMongoData();

        echo "✅ generation des donnees termiee avec succes ! \n";
    }

    private function clearExistingData(): void
    {
        echo "🧹 Nettoyage des donnees existantes ...\n";

        // Desactiver les contraintes des cles etrangeres
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

        $tables = [
            'preference', 'parametre', 'configuration', 'avis', 'reservation',
            'covoiturage', 'voiture', 'role_user','user','marque',
        ];

        foreach ($tables as $table) {
            $this->db->exec("TRUNCATE TABLE $table");
        }

        // Reactiver les contraintes
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Nettoyer MongoDB
        $this->mongo->getCollection('trajet_geolocalisation')->deleteMany([]);

        echo "✅ Donnees nettoyees.";
    }

    private function seedMarques(): void
    {
        echo "🚘 Creation des marques de voiture...\n";

        $marques = [
            'Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'Ford', 'BMW',
            'Mercedes', 'Audi', 'Toyota', 'Nissan', 'Hyundai', 'Kia',
            'Fiat', 'Opel', 'Volvo', 'Seat', 'Skoda', 'Mazda', 'Honda', 'Suzuki'
        ];

        foreach ($marques as $marque) {
            $stmt = $this->db->prepare("INSERT INTO marque (libelle) VALUES (?)");
            $stmt->execute([$marque]);
            $this->marqueIds[] = $this->db->lastInsertId();
        }

        echo "✅ " . count($marques) . " marques creees ...\n";
    }

    private function seedUsers(): void
    {
        echo "👥 Creation des utilisateurs...\n";

        for ($i = 0; $i < 50; $i++) {
            $name = $this->faker->lastName;
            $firstname = $this->faker->firstName;

            $userData = [
                'nom' => $name,
                'prenom' => $firstname,
                'email' => $this->faker->unique()->email,
                'password' => password_hash('1234567890', PASSWORD_DEFAULT),
                'telephone' => $this->faker->phoneNumber,
                'adresse' => $this->faker->address,
                'pseudo' => $this->generateUniquePseudo($firstname, $name), // A creer
                'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'photo' => $this->faker->optional(0.3)->imageUrl(200, 200, 'people', true, $firstname),
                'date_creation' => $this->faker->dateTimeBetween('-1 years')->format('Y-m-d')
            ];
            $stmt = $this->db->prepare(
                "INSERT INTO user (nom, prenom, email, password, telephone, adresse, pseudo, date_naissance, photo, date_creation)
                    VALUES (:nom, :prenom, :email, :password, :telephone, :adresse, :pseudo, :date_naissance, :photo, :date_creation)"
            );
            $stmt->execute($userData);
            $this->userIds[] = $this->db->lastInsertId();
        }

        echo "✅ " . count($this->userIds) . " utilisateurs crees ...\n";
    }

    private function generateUniquePseudo(string $firstname, string $name): string
    {
        $basePseudo = strtolower($firstname . '.' . $name);
        $pseudo = $basePseudo;
        $counter = 1;

        // Checker que le pseudo choisi ne se trouve pas deja en base de donnees
        while(true) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM user WHERE  pseudo = ?");
            $stmt->execute([$pseudo]);
            $result = $stmt->fetch();

            if ($result->count == 0) {
                break;
            }

            $pseudo = $basePseudo . $counter;
            $counter++;
        }

        return $pseudo;
    }

    private function seedVoitures(): void
    {
        echo "🏎 Creation des Voitures... \n";

        $modelesParMarque = [
            'Renault' => ['Clio', 'Mégane', 'Scénic', 'Captur', 'Kadjar', 'Twingo', 'Zoe'],
            'Peugeot' => ['208', '308', '3008', '5008', '2008', '508', 'Partner'],
            'Citroën' => ['C3', 'C4', 'C5', 'Berlingo', 'Cactus', 'DS3', 'Jumpy'],
            'Volkswagen' => ['Golf', 'Polo', 'Passat', 'T-Roc', 'Tiguan', 'Touran', 'Caddy'],
            'Ford' => ['Fiesta', 'Focus', 'Kuga', 'Puma', 'S-Max', 'Mondeo', 'Tourneo'],
            'BMW' => ['Série 1', 'Série 3', 'Série 5', 'X1', 'X3', 'X5', 'Série 7'],
            'Mercedes' => ['Classe A', 'Classe C', 'Classe E', 'GLA', 'GLC', 'GLE', 'Classe S'],
            'Audi' => ['A1', 'A3', 'A4', 'A6', 'Q2', 'Q3', 'Q5'],
            'Toyota' => ['Yaris', 'Corolla', 'RAV4', 'C-HR', 'Prius', 'Auris', 'Aygo'],
            'Nissan' => ['Micra', 'Qashqai', 'Juke', 'X-Trail', 'Leaf', 'Note']
        ];

        // Recuperer toutes les marques pour faire correspondre les modeles
        $stmt = $this->db->query("SELECT marque_id, libelle FROM marque");
        $marques = $stmt->fetchAll();

        $countVoiture = 0;
        foreach ($this->userIds as $userId) {
            // Verifier si l'utilisateur possede au moins le role chauffeur
//            $stmt = $this->db->prepare("SELECT role_id FROM role_user WHERE user_id = ? AND role_id = '1'");
//            $stmt->execute([$userId]);

            if ($this->faker->boolean(60)) {
                // Si un utilisateur possede un vehicule, on remplis les info de ce vehcule
                $marque = $this->faker->randomElement($marques);
                $modeles = $modelesParMarque[$marque->libelle] ?? [$this->faker->word . ' ' . $this->faker->numberBetween(1, 9)];
                $modele = $this->faker->randomElement($modeles);

                $infoVoiture = [
                    'modele' => $modele,
                    'immatriculation' => $this->generateFrenchLicensePlate(), // A creer
                    'energie' => $this->faker->randomElement(['0', '1']),
                    'couleur' => $this->faker->colorName,
                    'date_premiere_immatriculation' => $this->faker
                        ->dateTimeBetween('-8 years', '-1 years')
                        ->format('Y-m-d'),
                    'user_id' => $userId,
                    'marque_id' => $marque->marque_id
                ];
                $stmt = $this->db->prepare(
                    "INSERT INTO voiture (modele, immatriculation, energie, couleur, date_premiere_immatriculation, user_id, marque_id) 
                    VALUES (:modele, :immatriculation, :energie, :couleur, :date_premiere_immatriculation, :user_id, :marque_id)"
                );
                $stmt->execute($infoVoiture);

                $this->voitureIds[] = $this->db->lastInsertId();
                $countVoiture++;
            }
        }

        echo "✅ " . $countVoiture . " voitures creees \n";
    }

    /**
     * Genere un numero d'immatriculation au format francais
     * @return string
     */
    private function generateFrenchLicensePlate(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '1234567890';

        return
            substr(str_shuffle($letters), 0, 2) . '-' .
            substr(str_shuffle($numbers), 0, 3) . '-' .
            substr(str_shuffle($letters), 0, 2);
    }

    private function seedUserRoles(): void
    {
        echo "Attribution des roles Utilisateurs...\n";

        // $stats est le tableau qui nous indiquera le nombre d'utilisateur avec le role passager,
        // le nombre d'utilisateur avec le role conducteur
        // et le nombre d'utilisateur avec les deux roles
        $stats = [
            'role_passager' => 0,
            'role_chauffeur' => 0,
            'role_passager_chauffeur' => 0
        ];

        /**
         * Si un utilisateur possede un vehicule, alors il est un chauffeur,
         * Nous voulons que 70% de nos chauffeurs soit egalement des passagers
         * Nous allons donc verifier tous les utilisateurs qui possedent un vehicule
         */
        foreach ($this->userIds as $userId) {
            $hasCar = $this->userHasCar($userId); // A Creer

            if ($hasCar) {
                // Si vehicule alors role chauffeur
                $this->assignRole($userId, 1); // A creer

                // 70% des chauffeurs sont aussi passagers
                if ($this->faker->boolean(70)) {
                    $this->assignRole($userId, 2);
                    $stats['role_passager_chauffeur']++;
                } else {
                    $stats['role_chauffeur']++;
                }
            } else {
                // Utilisateurs sans vehicule = passager
                $this->assignRole($userId, 2);
                $stats['role_passager']++;
            }
        }

        // Affichage de la repartition Chauffeur | Passager | Chauffeur-Passager
        $this->displayStatistics($stats); // A creer
    }

    /**
     * Verifie si un utilisateur possede un vehicule en BDD
     * @param $userId l'identifiant de l'utilisateur dont on souhaite verifier la possession d'un vehicule
     * @return bool
     */
    private function userHasCar(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM voiture WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);

            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            echo "⚠️ Erreur verification voiture pour utilisateur $userId: {$e->getMessage()}";
            return false;
        }
    }

    /**
     * Associe un role a un utilisateur dans la table role_user
     * @param $userId l'identifiant de l'utilisateur auquel on doit associer un role
     * @param $roleId l'identifiant du role aui doit etre assigne a l'utilisateur correspondant
     * @return void
     */
    private function assignRole($userId, $roleId): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO role_user (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $roleId]);
        } catch (\PDOException $e) {
            // Ignorer les doublons
            if ($e->getCode() === '23000') { // Violation de contrainte d'unicite
                echo "⚠️⚠ Role deja assigne: Utilisateur $userId - Role $roleId \n";
                return;
            } else {
                throw $e;
            }
        }
    }

    private function displayStatistics(array $stats): void
    {
        $totalUsers = count($this->userIds);

        echo "✅ Repartition des roles...\n";
        echo "👤 Passagers: {$stats['role_passager']} \n";
        echo "🚗 Chauffeur: {$stats['role_chauffeur']} \n";
        echo "🔗 Chauffeur-Passagers: {$stats['role_passager_chauffeur']} \n";
    }

    private function seedCovoiturages()
    {
        echo "🛣️ Création des covoiturages...\n";

        $villesFrance = [
            'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg',
            'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims', 'Le Havre', 'Saint-Étienne',
            'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Villeurbanne', 'Le Mans'
        ];

        $countCovoiturage = 0;
        foreach ($this->voitureIds as $voitureId) {
            // On recupere le proprietaire de la voiture concernee pour le covoitirage
            $stmt = $this->db->prepare("SELECT user_id FROM voiture WHERE voiture_id = ?");
            $stmt->execute([$voitureId]);
            $voiture = $stmt->fetch();
            $conducteurId = $voiture->user_id;

            // Creer entre un et 3 covoiturages par vehicule
            $nbCovoiturage = $this->faker->numberBetween(1, 3);

            for ($i = 0; $i < $nbCovoiturage; $i++) {
                $dateDepart = $this->faker->dateTimeBetween('+1 days', '+3 months');
                $dureeTrajet = $this->faker->numberBetween(30, 360);
                $lieuDepart = $this->faker->randomElement($villesFrance);
                $lieuArrivee = $this->faker->randomElement(array_diff($villesFrance, [$lieuDepart]));

                $infoCovoiturage = [
                    'date_depart' => $dateDepart->format('Y-m-d'),
                    'heure_depart' => $this->faker->time('H:i:s'),
                    'lieu_depart' => $lieuDepart,
                    'date_arrivee' => $dateDepart->format('Y-m-d'),
                    'heure_arrivee' => $this->generateArrivalTime($dateDepart, $dureeTrajet), // A creer
                    'lieu_arrivee' => $lieuArrivee,
                    'statut' => $this->faker->randomElement(['prevu', 'prevu', 'prevu', 'en cours', 'termine']),
                    'nb_places' => $this->faker->numberBetween(1, 4),
                    'prix_personne' => $this->faker->numberBetween(5, 50),
                    'conducteur_id' => $conducteurId,
                    'voiture_id' => $voitureId,
                    'date_creation' => $this->faker->dateTimeBetween('-3 days')->format('Y-m-d')
                ];

                $stmt = $this->db->prepare(
                    "INSERT INTO covoiturage (
                         date_depart, 
                         heure_depart, lieu_depart, 
                         date_arrivee, heure_arrivee, 
                         lieu_arrivee, statut, nb_places, 
                         prix_personne, conducteur_id, 
                         voiture_id, date_creation ) VALUES (
                           :date_depart, 
                           :heure_depart, :lieu_depart, 
                           :date_arrivee, :heure_arrivee, 
                           :lieu_arrivee, :statut, :nb_places, 
                           :prix_personne, :conducteur_id, 
                           :voiture_id, :date_creation
                       )"
                );

                $stmt->execute($infoCovoiturage);
                $this->covoiturageIds[] = $this->db->lastInsertId();
                $countCovoiturage++;
            }
        }

        echo "✅ " . $countCovoiturage . " Covoiturages crees \n";
    }

    /**
     * Calcul la date et l'heure d'arrivee d'un covoiturage
     * @param $startdate La date de depart
     * @param $dureeTrajet duree du trajet en minutes
     * @return mixed
     */
    private function generateArrivalTime($startdate, $dureeTrajet): mixed
    {
        $arrival = clone $startdate;
        $arrival->modify("+{$dureeTrajet} minutes");

        return $arrival->format('H:i:s');
    }

    private function seedReservations()
    {
        echo "🎫 Création des réservations...\n";

        $countReservation = 0;

        // Une reservation a besoin du nombre de place et du conducteur
        // On recupere ces informations dans le covoiturage
        foreach ($this->covoiturageIds as $covoiturageId) {
            $stmt = $this->db->prepare("SELECT nb_places, conducteur_id FROM covoiturage WHERE covoiturage_id = ?");
            $stmt->execute([$covoiturageId]);
            $covoiturage = $stmt->fetch();

            $placeDispo = $covoiturage->nb_places;
            $conducteurId = $covoiturage->conducteur_id;

            // Generer aleatoirement un nombre de place reserve en fonction du combre disponible
            $nbReservations = $this->faker->numberBetween(0, $placeDispo);
            $passagerAyantReserve = [];
            for($i=0; $i < $nbReservations; $i++) {
                // Une reservation est reservee uniquement aux utilisateurs autres que le chauffeur
                // Ou aux utilisateurs n'ayant pas encore fait une reservation.
                $passagersDispo = array_diff($this->userIds, [$conducteurId], $passagerAyantReserve);
                if (empty($passagersDispo)) break;

                $passagerId = $this->faker->randomElement($passagersDispo);
                $passagerAyantReserve[] = $passagerId;

                $infoReservation = [
                    'passager_id' => $passagerId,
                    'covoiturage_id' => $covoiturageId,
                    'statut' => $this->faker->randomElement(['en attente','confirme', 'confirme', 'confirme']),
                    'nb_place_reservee' => $this->faker->numberBetween(1, min(2, $placeDispo)),
                    'date_creation' => $this->faker->dateTimeBetween('-4 days', '-1 days')->format('Y-m-d'),
                ];

                try {
                    $stmt = $this->db->prepare(
                        "INSERT INTO reservation (
                         passager_id, covoiturage_id, statut, nb_place_reservee, date_creation
                         ) VALUES (:passager_id, :covoiturage_id, :statut, :nb_place_reservee, :date_creation)");
                    $stmt->execute($infoReservation);
                    $countReservation++;
                } catch (\PDOException $e) {
                    // Ignoerer les doublons
                    continue;
                }
            }
        }

        echo "✅ " . $countReservation . " réservations créées\n";
    }

    private function seedAvis()
    {
        echo "⭐ Création des avis...\n";

        // Un avis ne peut etre donne que sur un covoiturage termine,
        // Donc On a besoin d'une reservation confirmee et d'un covoiturage termine
        $stmt = $this->db->prepare("
            SELECT r.passager_id, r.covoiturage_id, c.conducteur_id 
            FROM reservation r 
            JOIN covoiturage c on r.covoiturage_id = c.covoiturage_id
            WHERE r.statut = 'confirme' AND c.statut = 'termine'
        ");
        $stmt->execute();
        $reservations = $stmt->fetchAll();

        $countAvis = 0;
        foreach ($reservations as $reservation) {
            // On suppose qu'il y a 60% de chance qu'utilisateur laisse un avis
            if ($this->faker->boolean(60)) {
                $infoAvis = [
                    'commentaire' => $this->faker->optional(0.8)->text($this->faker->numberBetween(50, 300)),
                    'note' => $this->faker->numberBetween(1, 5),
                    'statut' => $this->faker->randomElement(['publie', 'publie', 'publie', 'modere']),
                    'passager_id' => $reservation->passager_id,
                    'conducteur_id' => $reservation->conducteur_id,
                    'covoiturage_id' => $reservation->covoiturage_id,
                    'date_creation' => $this->faker->dateTimeBetween('- 1 weeks')->format('Y-m-d')
                ];
                $stmt = $this->db->prepare("INSERT INTO avis (
                  commentaire, note, statut, passager_id, conducteur_id, covoiturage_id, date_creation) VALUES (
                  :commentaire, :note, :statut, :passager_id, :conducteur_id, :covoiturage_id, :date_creation                              
                  )"
                );
                $stmt->execute($infoAvis);
                $countAvis++;
            }
        }

        echo "✅ " . $countAvis . " avis créés\n";
    }

    private function seedParametres()
    {
        echo "⚙️ Création des paramètres utilisateur...\n";

        $countParametre = 0;
        foreach ($this->userIds as $userId) {
            // Vérifier si l'utilisateur est conducteur
            $stmt = $this->db->prepare(
                "SELECT role_id FROM role_user WHERE user_id= ? AND role_id = 1"
            );
            $stmt->execute([$userId]);

            if ($stmt->fetch()) {
                $parametres = [
                    ['propriete' => 'musique_autorisee', 'valeur' => $this->faker->randomElement(['oui', 'non'])],
                    ['propriete' => 'climatisation', 'valeur' => $this->faker->randomElement(['oui', 'non'])],
                    ['propriete' => 'animaux_autorises', 'valeur' => $this->faker->randomElement(['oui', 'non'])],
                    ['propriete' => 'fumeur_autorise', 'valeur' => $this->faker->randomElement(['oui', 'non'])],
                    ['propriete' => 'bagages_max', 'valeur' => $this->faker->randomElement(['petit', 'moyen', 'grand'])]
                ];

                foreach ($parametres as $param) {
                    $stmt = $this->db->prepare(
                        "INSERT INTO parametre (propriete, valeur, conducteur_id) VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$param['propriete'], $param['valeur'], $userId]);
                    $countParametre++;
                }
            }
        }

        echo "✅ " . $countParametre . " paramètres créés\n";
    }

    private function seedMongoData()
    {
        echo "🗺️ Création des données géographiques MongoDB...\n";

        $collection = $this->mongo->getCollection('trajets_geolocalisation');
        $countMongoData = 0;
        $errors = 0;

        foreach ($this->covoiturageIds as $covoiturageId) {
            // Coordonnees geographiques contexte France
            $longitudeDepart = $this->faker->randomFloat(6, -5.0, 9.0);
            $latitudeDepart = $this->faker->randomFloat(6, 41.0, 51.0);

            // 1. Point d'arrivee a une distance raisonnable
            $distanceKm = $this->faker->numberBetween(50, 400);
            $angle = deg2rad($this->faker->numberBetween(0, 3601));
            $distanceDegrees = $distanceKm/ 111.0;

            // 2. calcul de l'arrivee dans un rayon entre 50 et 500 KM
            $longitudeArrivee = $longitudeDepart + ($distanceDegrees * cos($angle));
            $latitudeArrivee = $latitudeDepart + ($distanceDegrees * sin($angle));

            // 3. S'assurer qu'on ne sorte pas de la France
            $longitudeArrivee = max(-5.0, min(9.0, $longitudeArrivee));
            $latitudeArrivee = max(41.0, min(51.0, $latitudeArrivee));

            $infoGeolocalisation = [
                'covoiturage_id' => (int)$covoiturageId,
                'point_depart' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$longitudeDepart, (float)$latitudeDepart]
                ],
                'point_arrivee' => [
                    'type' => 'Point',
                    'coodinates' => [(float)$longitudeArrivee, (float)$latitudeArrivee]
                ],
                'itineraire_complet' => $this->generateEncodedPolyline(), // A creer
                'distance_km' => (float)$distanceKm,
                'duree_min' => (int)$this->faker->numberBetween(25, 320),
                'date_creation' => new \MongoDB\BSON\UTCDateTime()
            ];

            $result = $collection->insertOne($infoGeolocalisation);

            if ($result->getInsertedCount() === 1) {
                $countMongoData++;
            }
        }

        echo "✅ " . $countMongoData . " documents MongoDB créés\n";
    }

    private function generateEncodedPolyline(): string
    {
        $chars = 'QWERTYUIOPASDFGHJKLMNBVCXZabcdefghijklmnopqrstuvwxyz-_';
        return substr(str_shuffle($chars), 0, $this->faker->numberBetween(40, 120));
    }
}