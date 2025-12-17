<?php
/**
 * Configuration de la base de données
 */

// Configuration de la base de données
// Utilisation de getenv pour la flexibilité Docker, avec repli sur les valeurs par défaut
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'rfid');
define('DB_USER', getenv('DB_USER') ?: 'rfid');
define('DB_PASS', getenv('DB_PASS') ?: 'iv4VEvp&1C7vb5X&Pz5o');

// Démarrage de la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration PHP (Optionnel : décommentez pour le debug)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}

?>


