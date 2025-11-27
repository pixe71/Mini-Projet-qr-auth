CREATE DATABASE IF NOT EXISTS super_systeme;
USE super_systeme;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    a2f_token VARCHAR(20) DEFAULT NULL,
    a2f_expiration DATETIME DEFAULT NULL
);

CREATE TABLE users_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    rfid_uid VARCHAR(50) DEFAULT NULL,
    status ENUM('en_attente', 'actif') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
