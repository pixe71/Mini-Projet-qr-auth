<?php
session_start();
require 'db.php';

$success = false;
$erreur = false;
$max_attempts = 3; // Nombre max de tentatives
$time_window = 30; // Fenêtre de temps en secondes
$cooldown_time = 120; // Cooldown si trop de tentatives

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialiser le tableau des tentatives si nécessaire
    if (!isset($_SESSION['request_attempts'])) {
        $_SESSION['request_attempts'] = [];
    }
    
    // Nettoyer les anciennes tentatives (> 30 secondes)
    $current_time = time();
    $_SESSION['request_attempts'] = array_filter($_SESSION['request_attempts'], function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    // Vérifier si en cooldown
    if (isset($_SESSION['cooldown_until']) && $current_time < $_SESSION['cooldown_until']) {
        $remaining = $_SESSION['cooldown_until'] - $current_time;
        $erreur = "Trop de tentatives. Veuillez attendre encore $remaining secondes.";
    } 
    // Vérifier le nombre de tentatives dans la fenêtre de temps
    elseif (count($_SESSION['request_attempts']) >= $max_attempts) {
        $_SESSION['cooldown_until'] = $current_time + $cooldown_time;
        $erreur = "Trop de tentatives en peu de temps. Veuillez attendre $cooldown_time secondes.";
    }
    
    if (!$erreur) {
        $nom_complet = htmlspecialchars(trim($_POST['nom_complet']));
        
        if (!empty($nom_complet)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users_rfid (nom_complet, status) VALUES (?, 'en_attente')");
                if ($stmt->execute([$nom_complet])) {
                    $success = true;
                    // Enregistrer cette tentative
                    $_SESSION['request_attempts'][] = $current_time;
                }
            } catch (PDOException $e) {
                $erreur = "Erreur lors de l'enregistrement. Veuillez réessayer.";
            }
        } else {
            $erreur = "Veuillez entrer votre nom complet";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Badge RFID</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-panel">
        <h2>Demande de Badge RFID</h2>
        <p style="color: #aaa; font-size: 0.9em; margin-bottom: 25px;">
            Remplissez le formulaire ci-dessous pour faire une demande de badge d'accès. 
            Un administrateur validera votre demande.
        </p>
        
        <?php if($success): ?>
            <div class="success">
                ✓ Votre demande a été envoyée avec succès !<br>
                Un administrateur va traiter votre demande prochainement.<br>
                <small style="color: #aaa; margin-top: 10px; display: block;">Vous serez contacté une fois votre badge activé.</small>
            </div>
            <br>
        <?php else: ?>
        
        <?php if($erreur): ?>
            <p class="error"><?php echo $erreur; ?></p>
        <?php endif; ?>
        
        <form method="post">
            <input type="text" 
                   name="nom_complet" 
                   placeholder="Nom et Prénom" 
                   required 
                   autocomplete="off"
                   value="<?php echo isset($_POST['nom_complet']) && !$success ? htmlspecialchars($_POST['nom_complet']) : ''; ?>">
            
            <button type="submit">Envoyer ma demande</button>
        </form>
        <?php endif; ?>
        
        <br>
        <a href="login.php">Connexion Administrateur →</a>
    </div>
</body>
</html>
