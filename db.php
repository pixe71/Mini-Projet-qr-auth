<?php
$host = 'bdd.luc-tournie.fr';
$dbname = 'rfid';
$user = 'rfid'; // Changez selon votre config
$pass = 'iv4VEvp&1C7vb5X&Pz5o';     // Changez selon votre config

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>