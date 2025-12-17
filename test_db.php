<?php
// Test de connexion base de données
require_once 'db.php';

try {
    // La connexion est déjà établie dans db.php, on vérifie juste si $pdo existe
    if (isset($pdo)) {
        echo "✅ Connexion réussie à la base de données !<br>";
    } else {
        throw new Exception("L'objet PDO n'a pas été créé.");
    }
    
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
