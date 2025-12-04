<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_logged'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom_complet'];

// GET - Récupérer les créneaux réservés
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    
    if ($_GET['action'] === 'get_booked') {
        // Retourner tous les créneaux réservés groupés par date
        $stmt = $pdo->query("SELECT date_reservation, heure_debut, heure_fin FROM reservations ORDER BY date_reservation, heure_debut");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookedSlots = [];
        foreach ($reservations as $res) {
            $date = $res['date_reservation'];
            if (!isset($bookedSlots[$date])) {
                $bookedSlots[$date] = [];
            }
            
            // Ajouter tous les créneaux horaires entre heure_debut et heure_fin
            $debut = strtotime($res['heure_debut']);
            $fin = strtotime($res['heure_fin']);
            
            while ($debut < $fin) {
                $bookedSlots[$date][] = date('H:i', $debut);
                $debut = strtotime('+1 hour', $debut);
            }
        }
        
        echo json_encode($bookedSlots);
        exit();
    }
    
    if ($_GET['action'] === 'my_reservations') {
        // Retourner les réservations de l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY date_reservation DESC, heure_debut DESC");
        $stmt->execute([$user_id]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($reservations);
        exit();
    }
}

// POST - Créer une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'create') {
        $date = $_POST['date'] ?? null;
        $heure_debut = $_POST['heure_debut'] ?? null;
        $heure_fin = $_POST['heure_fin'] ?? null;
        $motif = isset($_POST['motif']) ? trim($_POST['motif']) : null;

        if (!$date || !$heure_debut || !$heure_fin || !$motif) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs manquants dans la requête.']);
            exit();
        }
        
        // Vérifier que le créneau n'est pas déjà pris
        $check = $pdo->prepare("SELECT id FROM reservations WHERE date_reservation = ? AND heure_debut = ?");
        $check->execute([$date, $heure_debut]);
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ce créneau est déjà réservé']);
            exit();
        }
        
        // Créer la réservation
        try {
            // Générer un numéro de réservation unique (format: RES-YYYYMMDD-XXXXX)
            $numero = 'RES-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            
            $insert = $pdo->prepare("INSERT INTO reservations (numero_reservation, user_id, nom_complet, date_reservation, heure_debut, heure_fin, motif) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$numero, $user_id, $user_nom, $date, $heure_debut, $heure_fin, htmlspecialchars($motif, ENT_QUOTES, 'UTF-8')]);
            
            // Créer une entrée dans users_rfid pour CHAQUE réservation (un badge par réservation)
            $insertBadge = $pdo->prepare("INSERT INTO users_rfid (nom_complet, numero_reservation, rfid_uid, status) VALUES (?, ?, NULL, 'en_attente')");
            $insertBadge->execute([$user_nom, $numero]);
            error_log('[reservations] Badge créé pour réservation: ' . $numero);
            
            echo json_encode(['success' => true, 'numero_reservation' => $numero]);
        } catch (PDOException $e) {
            error_log('[reservations] '.$e->getMessage());
            http_response_code(500);
            $message = 'Erreur lors de la réservation : ' . $e->getMessage();

            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                $message = "La table 'reservations' est introuvable. Importez le fichier BDD.sql.";
            } elseif (strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
                $message = "Utilisateur introuvable. Reconnectez-vous ou recréez votre compte.";
            } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
                $message = "Table manquante dans la base de données. Importez BDD.sql.";
            }

            echo json_encode(['success' => false, 'message' => $message, 'debug' => $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Action invalide']);
?>
