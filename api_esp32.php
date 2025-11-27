<?php
require 'db.php';
header('Content-Type: application/json');

// --- MODE 1 : L'ESP32 demande s'il y a du travail (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // On cherche le plus vieux user en attente
    $stmt = $pdo->query("SELECT id, nom FROM badge_users WHERE status = 'en_attente' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // On renvoie du JSON à l'ESP32 : { "id": 12, "nom": "Thomas", "action": "write" }
        echo json_encode(["found" => true, "id" => $user['id'], "nom" => $user['nom']]);
    } else {
        echo json_encode(["found" => false]);
    }
}

// --- MODE 2 : L'ESP32 confirme l'écriture du badge (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // L'ESP32 envoie l'ID et l'UID du badge scanné
    // On récupère les données JSON envoyées par l'ESP32
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id']) && isset($data['uid'])) {
        $id = $data['id'];
        $uid = $data['uid'];

        // On met à jour la base
        $stmt = $pdo->prepare("UPDATE badge_users SET rfid_uid = ?, status = 'actif' WHERE id = ?");
        $stmt->execute([$uid, $id]);

        echo json_encode(["status" => "success", "message" => "Badge associe"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Donnees manquantes"]);
    }
}
?>