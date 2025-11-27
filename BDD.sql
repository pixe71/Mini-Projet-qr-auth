CREATE DATABASE IF NOT EXISTS systeme_securite;
USE systeme_securite;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rfid_uid VARCHAR(50) DEFAULT NULL, -- Pour stocker l'ID du badge RFID
    token_2fa VARCHAR(10) DEFAULT NULL, -- Le code temporaire
    token_expiration DATETIME DEFAULT NULL, -- Quand le code expire
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
