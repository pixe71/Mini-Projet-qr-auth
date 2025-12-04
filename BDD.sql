-- Base de données pour le système de réservation RFID
-- Utiliser la base existante 'rfid'
USE rfid;

-- Supprimer les tables si elles existent (pour réinitialisation propre)
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS users;

-- Table admins (probablement déjà existante, on ne la recrée pas)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    a2f_token VARCHAR(20) DEFAULT NULL,
    a2f_expiration DATETIME DEFAULT NULL
);

-- Table users_rfid (probablement déjà existante)
CREATE TABLE IF NOT EXISTS users_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    numero_reservation VARCHAR(20) DEFAULT NULL,
    rfid_uid VARCHAR(50) DEFAULT NULL,
    status ENUM('en_attente', 'actif', 'expiré') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table users (nouveaux utilisateurs du système de réservation)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    a2f_token VARCHAR(20) DEFAULT NULL,
    a2f_expiration DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table reservations (système de réservation de créneaux)
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_reservation VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    nom_complet VARCHAR(100) NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    motif VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (date_reservation, heure_debut)
);
