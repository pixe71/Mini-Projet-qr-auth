<?php
require 'db.php';
header('Content-Type: application/json');

// --- CAS 1 : L'ESP32 demande "Qui dois-je inscrire ?" (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // On cherche le premier user qui n'a pas encore de badge
    $stmt = $pdo->query("SELECT id, nom_complet FROM users_rfid WHERE status = 'en_attente' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // On dit à l'ESP32 : "Oui, occupe-toi de l'ID 5 (Thomas)"
        echo json_encode(["job" => true, "id" => $user['id'], "nom" => $user['nom_complet']]);
    } else {
        // On dit à l'ESP32 : "Rien à faire, dors."
        echo json_encode(["job" => false]);
    }
}

// --- CAS 2 : L'ESP32 dit "J'ai fini, voici l'UID" (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On lit les données brutes envoyées par l'ESP32
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['id']) && isset($input['uid'])) {
        $stmt = $pdo->prepare("UPDATE users_rfid SET rfid_uid = ?, status = 'actif' WHERE id = ?");
        $stmt->execute([$input['uid'], $input['id']]);
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>