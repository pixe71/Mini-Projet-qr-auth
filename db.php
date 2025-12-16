<?php
// db.php - À inclure partout
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'rfid';
$user = getenv('DB_USER') ?: 'rfid'; 
$pass = getenv('DB_PASS') ?: 'iv4VEvp&1C7vb5X&Pz5o'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}

?>


