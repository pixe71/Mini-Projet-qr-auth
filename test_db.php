<?php
// Test de connexion base de données
$host = 'db';
$dbname = 'rfid';
$user = 'rfid'; 
$pass = 'iv4VEvp&1C7vb5X&Pz5o'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion réussie à la base de données !<br>";
    
    // Tester les tables
    $tables = ['admins', 'users', 'users_rfid', 'reservations'];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "✅ Table '$table' : $count ligne(s)<br>";
        } catch (PDOException $e) {
            echo "❌ Table '$table' : " . $e->getMessage() . "<br>";
        }
    }
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage();
}
?>
