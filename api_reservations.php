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
        $stmt = $pdo->query("SELECT date_reservation, heure_debut FROM reservations ORDER BY date_reservation, heure_debut");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookedSlots = [];
        foreach ($reservations as $res) {
            $date = $res['date_reservation'];
            if (!isset($bookedSlots[$date])) {
                $bookedSlots[$date] = [];
            }
            $bookedSlots[$date][] = $res['heure_debut'];
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
        $date = $_POST['date'];
        $heure_debut = $_POST['heure_debut'];
        $heure_fin = $_POST['heure_fin'];
        $motif = htmlspecialchars(trim($_POST['motif']));
        
        // Vérifier que le créneau n'est pas déjà pris
        $check = $pdo->prepare("SELECT id FROM reservations WHERE date_reservation = ? AND heure_debut = ?");
        $check->execute([$date, $heure_debut]);
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ce créneau est déjà réservé']);
            exit();
        }
        
        // Créer la réservation
        try {
            $insert = $pdo->prepare("INSERT INTO reservations (user_id, nom_complet, date_reservation, heure_debut, heure_fin, motif) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$user_id, $user_nom, $date, $heure_debut, $heure_fin, $motif]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la réservation']);
        }
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Action invalide']);
?>
