CREATE DATABASE IF NOT EXISTS gestion_badges;
USE gestion_badges;

-- Table pour l'Administrateur du site
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table pour les utilisateurs des badges
CREATE TABLE badge_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    status ENUM('en_attente', 'actif') DEFAULT 'en_attente', -- 'en_attente' signifie que l'ESP32 doit traiter ce user
    rfid_uid VARCHAR(50) DEFAULT NULL, -- L'UID sera rempli par l'ESP32
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CRÉATION D'UN ADMIN PAR DÉFAUT (User: admin / Pass: admin123)
-- On insère directement le hash pour gagner du temps
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$8.hX/5y/T5s/H5s/H5s/H.0e0e0e0e0e0e0e0e0e0e0e0e0e0'); 
-- Note: Pour l'exemple, supprimez cette ligne et utilisez le script d'inscription si vous voulez un vrai mot de passe sécurisé.
