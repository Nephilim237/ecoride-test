CREATE DATABASE IF NOT EXISTS ecoride CHARACTER SET utf8mb4_unicode_ci;

USE ecoride;

-- Table utilisateur
CREATE TABLE user (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(64) NOT NULL,
    prenom VARCHAR(64) NOT NULL ,
    email VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL,
    telephone VARCHAR(64) NOT NULL,
    adresse VARCHAR(64) NOT NULL,
    pseudo VARCHAR(64) NOT NULL UNIQUE,
    date_naissance DATE NOT NULL,
    photo VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_pseudo (pseudo),
    INDEX idx_date_naissance (date_naissance),
    index idx_nom_prenom (nom, prenom)
);

CREATE TABLE role (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    libelle ENUM('chauffeur', 'passager') DEFAULT 'passager' NOT NULL
);

CREATE TABLE role_user (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE ,
    FOREIGN KEY (role_id) REFERENCES role(role_id) ON DELETE CASCADE
);

CREATE TABLE marque (
    marque_id INT PRIMARY KEY AUTO_INCREMENT,
    libelle VARCHAR(64) NOT NULL UNIQUE
);

CREATE TABLE voiture (
    voiture_id INT PRIMARY KEY AUTO_INCREMENT,
    modele VARCHAR(64) NOT NULL ,
    immatriculation VARCHAR(64) NOT NULL UNIQUE,
    energie ENUM('0', '1') DEFAULT '0',
    couleur VARCHAR(64),
    date_premiere_immatriculation DATE,
    user_id INT NOT NULL,
    marque_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (marque_id) REFERENCES marque(marque_id) ON DELETE CASCADE
);

CREATE TABLE covoiturage (
    covoiturage_id INT PRIMARY KEY AUTO_INCREMENT,
    date_depart DATE NOT NULL ,
    heure_depart TIME NOT NULL,
    lieu_depart VARCHAR(128) NOT NULL,
    date_arrivee DATE NOT NULL,
    heure_arrivee TIME NOT NULL,
    lieu_arrivee VARCHAR(128) NOT NULL ,
    statut ENUM('prevu', 'en cours', 'annule', 'termine') DEFAULT 'prevu',
    nb_places INT NOT NULL,
    prix_personne INT NOT NULL ,
    conducteur_id INT NOT NULL ,
    voiture_id INT NOT NULL ,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conducteur_id) REFERENCES user (user_id) ON DELETE CASCADE,
    FOREIGN KEY (voiture_id) REFERENCES voiture (voiture_id) ON DELETE CASCADE,
    INDEX idx_conducteur (conducteur_id),
    INDEX idx_dates (date_depart, date_arrivee),
    INDEX idx_lieux (lieu_depart, lieu_arrivee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservation (
    passager_id INT NOT NULL ,
    covoiturage_id INT NOT NULL ,
    statut ENUM('en attente', 'confirme', 'annule'),
    nb_place_reservee INT NOT NULL ,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (passager_id, covoiturage_id),
    FOREIGN KEY (passager_id) REFERENCES user (user_id),
    FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (covoiturage_id),
    INDEX idx_covoiturage (covoiturage_id)
);

CREATE TABLE avis (
    avis_id INT PRIMARY KEY AUTO_INCREMENT,
    commentaire TEXT NOT NULL ,
    note FLOAT NOT NULL CHECK ( note >= 1 AND note <= 5 ),
    statut ENUM('publie', 'modere') DEFAULT 'modere',
    passager_id INT NOT NULL,
    conducteur_id INT NOT NULL,
    covoiturage_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passager_id) REFERENCES user(user_id),
    FOREIGN KEY (conducteur_id) REFERENCES user(user_id),
    FOREIGN KEY (covoiturage_id) REFERENCES covoiturage(covoiturage_id),
    INDEX idx_conducteur (conducteur_id),
    INDEX idx_passager (passager_id)
);

CREATE TABLE configuration (
    configuration_id INT PRIMARY KEY AUTO_INCREMENT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE parametre (
    parametre_id INT PRIMARY KEY AUTO_INCREMENT,
    propriete VARCHAR(64) NOT NULL ,
    valeur VARCHAR(64) NOT NULL ,
    conducteur_id INT NOT NULL ,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conducteur_id) REFERENCES user (user_id),
    UNIQUE KEY unique_conducteur_proprietaire (conducteur_id, propriete)
);

CREATE TABLE preference (
    user_id INT,
    configuration_id INT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, configuration_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (configuration_id) REFERENCES configuration(configuration_id)
);

-- Insertion des role en BD

INSERT INTO role (libelle)
    VALUES
        ('passager'),
        ('chauffeur');