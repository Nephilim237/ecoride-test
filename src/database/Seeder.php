<?php

namespace Ecoride\Ecoride\database;

use Faker\Factory;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\core\MongoManager;
use MongoDB\BSON\UTCDateTime;

class Seeder
{
    private \Faker\Generator $faker;
    private \PDO $db;
    private ?MongoManager $mongo;
    private array $userIds = [];
    private array $voitureIds = [];
    private array $covoiturageIds = [];

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
        $this->seedRoles();
        $this->seedPreferences();
        $this->seedMarques();
        $this->seedUsers();
        $this->seedVoitures();
        $this->seedUserRoles();
        $this->seedCovoiturages();
        $this->seedReservations();
        $this->seedAvis();
        $this->seedUserPreferences();
        $this->seedMongoData();

        echo "✅ generation des donnees termiee avec succes ! \n";
    }

    private function clearExistingData(): void
    {
        echo "🧹 Nettoyage des donnees existantes ...\n";

        // Desactiver les contraintes des cles etrangeres
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

        $tables = [
            'avis', 'covoiturage', 'marque', 'preference', 'reservation',
            'role', 'role_user', 'user', 'user_preference', 'voiture'
        ];

        foreach ($tables as $table) {
            $this->db->exec("TRUNCATE TABLE $table");
        }

        // Reactiver les contraintes
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Nettoyer MongoDB
        $this->mongo->getCollection('preferences')->deleteMany([]);

        echo "✅ Donnees nettoyees.";
    }

    private function seedRoles(): void
    {
        $this->db->query("INSERT INTO role (libelle) VALUES ('chauffeur'), ('passager')");
    }

    private function seedMarques(): void
    {
        echo "🚘 Creation des marques de voiture...\n";

        $marques = [
            'Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'Ford', 'BMW',
            'Mercedes', 'Audi', 'Toyota', 'Nissan', 'Hyundai', 'Kia',
            'Fiat', 'Opel', 'Volvo', 'Seat', 'Skoda', 'Mazda', 'Honda', 'Suzuki'
        ];

        $stmt = $this->db->prepare("INSERT INTO marque (libelle) VALUES (?)");
        foreach ($marques as $marque) {
            $stmt->execute([$marque]);
        }

        echo "✅ " . count($marques) . " marques creees ...\n";
    }

    private function seedUsers(): void
    {
        echo "👥 Creation des utilisateurs...\n";

        $stmt = $this->db->prepare(
            "INSERT INTO user (nom, prenom, email, role_admin, password, telephone, adresse, pseudo, credits, date_naissance, photo, date_creation)
                    VALUES (:nom, :prenom, :email, :role_admin, :password, :telephone, :adresse, :pseudo, :credits, :date_naissance, :photo, :date_creation)"
        );

        // Creation de l'administrateur
        $adminData = [
            'nom' => 'Coding',
            'prenom' => 'City',
            'email' => 'codingcity237@gmail.com',
            'role_admin' => 15, // roleMask 15 Pour Admin
            'password' => password_hash('1234567890', PASSWORD_DEFAULT),
            'telephone' => $this->faker->phoneNumber,
            'adresse' => 'Kribi, Dombe-Elecam, Cameroun',
            'pseudo' => 'codingcity237',
            'credits' => 20,
            'date_naissance' => $this->faker->dateTime('-35 years')->format('Y-m-d'),
            'photo' => '',
            'date_creation' => $this->faker->dateTimeThisYear()->format('Y-m-d')
        ];
        $stmt->execute($adminData);
        $this->userIds[] = $this->db->lastInsertId();

        // Creation des autres utilisateurs
        for ($i = 0; $i < 50; $i++) {
            $name = $this->faker->lastName;
            $firstname = $this->faker->firstName;

            $userData = [
                'nom' => $name,
                'prenom' => $firstname,
                'email' => $this->faker->unique()->email,
                'role_admin' => $this->faker->randomElement([1, 3, 7]), // Limiter le roleMask a 7 de facon a ce qu'aucun de ces utilisateurs ne soit admin. On creera l'admin en amont manuellement
                'password' => password_hash('1234567890', PASSWORD_DEFAULT),
                'telephone' => $this->faker->phoneNumber,
                'adresse' => $this->faker->address,
                'pseudo' => $this->generateUniquePseudo($firstname, $name), // A creer
                'credits' => 20,
                'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'photo' => 'https://randomuser.me/api/portraits/' . $this->faker->randomElement(['men', 'women']) . '/' . $this->faker->numberBetween(1, 99) . '.jpg',
                'date_creation' => $this->faker->dateTimeBetween('-3 years')->format('Y-m-d')
            ];
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
        while (true) {
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
                    'nb_places' => $this->faker->numberBetween(3, 7),
                    'date_premiere_immatriculation' => $this->faker
                        ->dateTimeBetween('-8 years')
                        ->format('Y-m-d'),
                    'user_id' => $userId,
                    'marque_id' => $marque->marque_id
                ];
                $stmt = $this->db->prepare(
                    "INSERT INTO voiture (modele, immatriculation, energie, couleur, nb_places, date_premiere_immatriculation, user_id, marque_id) 
                    VALUES (:modele, :immatriculation, :energie, :couleur, :nb_places, :date_premiere_immatriculation, :user_id, :marque_id)"
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
                echo "⚠️ Role deja assigne: Utilisateur $userId - Role $roleId \n";
                return;
            } else {
                throw $e;
            }
        }
    }

    private function displayStatistics(array $stats): void
    {
        echo "✅ Repartition des roles...\n";
        echo "👤 Passagers: {$stats['role_passager']} \n";
        echo "🚗 Chauffeur: {$stats['role_chauffeur']} \n";
        echo "🔗 Chauffeur-Passagers: {$stats['role_passager_chauffeur']} \n";
    }

    private function seedCovoiturages(): void
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
            $nbCovoiturage = $this->faker->numberBetween(1, 5);

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
                    'nb_places' => $this->faker->numberBetween(1, 80),
                    'prix_personne' => $this->faker->numberBetween(5, 120),
                    'conducteur_id' => $conducteurId,
                    'voiture_id' => $voitureId,
                    'date_creation' => $this->faker->dateTimeBetween('-3 days')->format('Y-m-d')
                ];

                $stmt->execute($infoCovoiturage);
                $this->covoiturageIds[] = $this->db->lastInsertId();
                $countCovoiturage++;
            }
        }

        echo "✅ " . $countCovoiturage . " Covoiturages crees \n";
    }

    /**
     * Calcul la date et l'heure d'arrivee d'un covoiturage
     * @param $startdate mixed date de depart
     * @param $dureeTrajet mixed duree du trajet en minutes
     * @return mixed
     */
    private function generateArrivalTime(mixed $startdate, mixed $dureeTrajet): mixed
    {
        $arrival = clone $startdate;
        $arrival->modify("+{$dureeTrajet} minutes");

        return $arrival->format('H:i:s');
    }

    private function seedReservations(): void
    {
        echo "🎫 Création des réservations...\n";

        $countReservation = 0;

        // Une reservation a besoin du nombre de place et du conducteur
        // On recupere ces informations dans le covoiturage
        $stmt = $this->db->prepare("SELECT nb_places, conducteur_id FROM covoiturage WHERE covoiturage_id = ?");
        foreach ($this->covoiturageIds as $covoiturageId) {
            $stmt->execute([$covoiturageId]);
            $covoiturage = $stmt->fetch();

            $placeDispo = $covoiturage->nb_places;
            $conducteurId = $covoiturage->conducteur_id;

            // Generer aleatoirement un nombre de place reserve en fonction du nombre disponible
            $nbReservations = $this->faker->numberBetween(1, 5);
            $passagerAyantReserve = [];

            $resaStmt = $this->db->prepare("
                INSERT INTO reservation (passager_id, covoiturage_id, statut, nb_place_reservee, date_creation) 
                VALUES (:passager_id, :covoiturage_id, :statut, :nb_place_reservee, :date_creation)
            ");
            for ($i = 0; $i < $nbReservations; $i++) {
                // Une reservation est reservee uniquement aux utilisateurs autres que le chauffeur
                // Ou aux utilisateurs n'ayant pas encore fait une reservation.
                $passagersDispo = array_diff($this->userIds, [$conducteurId], $passagerAyantReserve);
                if (empty($passagersDispo)) break;

                $passagerId = $this->faker->randomElement($passagersDispo);
                $passagerAyantReserve[] = $passagerId;

                $infoReservation = [
                    'passager_id' => $passagerId,
                    'covoiturage_id' => $covoiturageId,
                    'statut' => $this->faker->randomElement(['en attente', 'confirme', 'confirme', 'confirme']),
                    'nb_place_reservee' => $this->faker->numberBetween(1, min(2, $placeDispo)),
                    'date_creation' => $this->faker->dateTimeBetween('-4 days')->format('Y-m-d'),
                ];

                try {
                    $resaStmt->execute($infoReservation);
                    $countReservation++;
                } catch (\PDOException $e) {
                    // Ignoerer les doublons
                    continue;
                }
            }
        }

        echo "✅ " . $countReservation . " réservations créées\n";
    }

    private function seedAvis(): void
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

        $avisStmt = $this->db->prepare("
            INSERT INTO avis (commentaire, note, statut, passager_id, conducteur_id, covoiturage_id, date_creation) 
            VALUES (:commentaire, :note, :statut, :passager_id, :conducteur_id, :covoiturage_id, :date_creation)
        ");
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
                $avisStmt->execute($infoAvis);
                $countAvis++;
            }
        }

        echo "✅ " . $countAvis . " avis créés\n";
    }

    private function seedMongoData(): void
    {
        echo "🗺️ Création des preferences dans MongoDB...\n";

        $collection = $this->mongo->getCollection('preferences');
        $countMongoData = 0;

        foreach ($this->userIds as $userId) {
            $hasCar = $this->userHasCar($userId);

            if ($hasCar) {
                $document = [
                    'user_id' => $userId,
                    'preferences' => [
                        'Animaux' => $this->faker->randomElement(['oui', 'non']),
                        'Fumeur' => $this->faker->randomElement(['oui', 'non']),
                    ],
                    'updates_at' => new UTCDateTime()
                ];

                // Ajout des preferences
                $collection->updateOne(
                    ['user_id' => $userId],
                    ['$set' => $document],
                    ['upsert' => true]
                );
            }

            $countMongoData++;
        }

        echo "✅ " . $countMongoData . " set de preferences MongoDB créés \n";
    }

    public function seedPreferences(): void
    {
        echo "⚙️ Création des paramètres de base...\n";

        $this->db->query("INSERT INTO preference (preference) VALUES ('fumeurs'), ('animaux')");

        echo "✅ 02 paramètres de base créés (Animaux & Fumeurs) \n";
    }

    private function seedUserPreferences(): void
    {
        echo "⚙️ Création des paramètres utilisateur...\n";

        $countParametre = 0;
        // 1. Verifier qu'un utilisateur possede un vehicule
        // 2. Si vehicule, alors preference par defaut
        foreach ($this->userIds as $userId) {
            $hasCar = $this->userHasCar($userId);

            if ($hasCar) {
                $stmt = $this->db->prepare("
                    INSERT INTO user_preference (user_id, preference_id, valeur_preference) VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    1,
                    $this->faker->randomElement(['oui', 'non'])
                ]);
                $stmt->execute([
                    $userId,
                    2,
                    $this->faker->randomElement(['oui', 'non'])
                ]);
            }
            $countParametre++;
        }

        echo "✅ " . $countParametre . " paramètres créés\n";
    }
}