<?php
session_start();
require 'db.php';

// Si personne n'est en cours de processus (ni login, ni inscription), on dégage
if (!isset($_SESSION['temp_admin_id'])) { header("Location: login.php"); exit(); }

$id = $_SESSION['temp_admin_id'];
$stmt = $pdo->prepare("SELECT username, a2f_token, a2f_expiration FROM admins WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_user = trim($_POST['code']);
    $now = date('Y-m-d H:i:s');

    if ($data['a2f_token'] && $code_user === $data['a2f_token']) {
        if ($now <= $data['a2f_expiration']) {
            // SUCCÈS
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_username'] = $data['username'];
            unset($_SESSION['temp_admin_id']);
            
            // Nettoyage du token
            $clean = $pdo->prepare("UPDATE admins SET a2f_token = NULL WHERE id = ?");
            $clean->execute([$id]);

            header("Location: dashboard.php");
            exit();
        } else {
            $erreur = "Le code a expiré (Délai de 2 min dépassé).";
        }
    } else {
        $erreur = "Code incorrect.";
    }
}

// URL API QR Code
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($data['a2f_token']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sécurité 2FA</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Calculer le temps restant en secondes
        const expirationTime = new Date("<?php echo $data['a2f_expiration']; ?>").getTime();
        
        function updateTimer() {
            const now = new Date().getTime();
            const distance = expirationTime - now;
            
            if (distance < 0) {
                document.getElementById("timer").innerHTML = "EXPIRÉ";
                document.getElementById("timer").style.color = "#ff6b6b";
                return;
            }
            
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById("timer").innerHTML = 
                (minutes < 10 ? "0" : "") + minutes + ":" + 
                (seconds < 10 ? "0" : "") + seconds;
        }
        
        // Mettre à jour toutes les secondes
        setInterval(updateTimer, 1000);
        updateTimer(); // Appel initial
    </script>
</head>
<body>
    <div class="glass-panel">
        <h2>Authentification Double</h2>
        <p>Scannez pour valider l'accès.</p>
        
        <div class="qr-box">
            <img src="<?php echo $qr_url; ?>" width="150">
        </div>

        <h3 style="letter-spacing: 5px; margin: 10px 0;"><?php echo $data['a2f_token']; ?></h3>
        <small style="color: #aaa;">Temps restant : <span id="timer" style="font-weight: bold; color: #51cf66;">--:--</span></small>

        <form method="post" style="margin-top:20px;">
            <?php if(isset($erreur)) echo "<p class='error'>$erreur</p>"; ?>
            <input type="text" name="code" placeholder="Code QR" required autocomplete="off" style="text-align:center; font-size:1.2em; letter-spacing:2px;">
            <button type="submit">Valider</button>
        </form>
    </div>
</body>
</html>
