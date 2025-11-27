<?php
session_start();
require 'db.php';

// Si on n'a pas passé l'étape 1, on dégage
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['temp_user_id'];
$erreur = "";

// Récupérer le token depuis la BDD pour l'affichage QR
$stmt = $pdo->prepare("SELECT token_2fa, token_expiration FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_soumis = $_POST['code'];
    $now = date('Y-m-d H:i:s');

    // VÉRIFICATION STRICTE
    if ($userData['token_2fa'] === $code_soumis) {
        if ($now <= $userData['token_expiration']) {
            // C'est GAGNÉ !
            
            // 1. Nettoyer le token en BDD (sécurité pour qu'il ne soit pas réutilisé)
            $clean = $pdo->prepare("UPDATE users SET token_2fa = NULL, token_expiration = NULL WHERE id = ?");
            $clean->execute([$user_id]);

            // 2. Valider la session
            $_SESSION['est_connecte'] = true;
            $_SESSION['user_id'] = $user_id;
            unset($_SESSION['temp_user_id']); // On n'en a plus besoin

            header("Location: prive.php");
            exit();
        } else {
            $erreur = "Le code a expiré (Dépassement des 5 minutes). Reconnectez-vous.";
        }
    } else {
        $erreur = "Code incorrect.";
    }
}

// URL API pour générer l'image QR Code (Service gratuit goqr.me ou qrserver)
$qr_content = $userData['token_2fa'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_content);
?>

<!DOCTYPE html>
<html lang="fr">
<head><title>Double Authentification</title></head>
<body style="text-align:center; font-family:sans-serif;">
    <h2>Double Authentification requise</h2>
    
    <p>Scannez ce QR Code ou recopiez le code ci-dessous :</p>
    
    <img src="<?php echo $qr_url; ?>" alt="QR Code" border="1" />
    
    <h3>Code : <?php echo $userData['token_2fa']; ?></h3>
    <p><em>Expire le : <?php echo $userData['token_expiration']; ?></em></p>

    <form method="post">
        <?php if ($erreur) echo "<p style='color:red'>$erreur</p>"; ?>
        <label>Entrez le code lu :</label><br>
        <input type="text" name="code" required autocomplete="off"><br><br>
        <button type="submit">Valider et Entrer</button>
    </form>
</body>
</html>