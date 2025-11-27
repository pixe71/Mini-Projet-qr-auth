<?php
// db.php - À inclure partout
$host = 'bdd.luc-tournie.fr';
$dbname = 'rfid';
$user = 'rfid'; 
$pass = 'iv4VEvp&1C7vb5X&Pz5o'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}
?>