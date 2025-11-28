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

// Calculer le temps restant en secondes côté serveur
$now_timestamp = time();
$expiration_timestamp = strtotime($data['a2f_expiration']);
$seconds_remaining = $expiration_timestamp - $now_timestamp;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sécurité 2FA</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --accent: #00d2ff;
            --accent-hover: #3a7bd5;
            --danger: #ff4b4b;
            --success: #2ecc71;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: 1px solid rgba(255, 255, 255, 0.15);
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .glow {
            position: absolute;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(0, 210, 255, 0.2) 0%, rgba(0,0,0,0) 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            animation: floatUp 0.8s ease-out;
        }

        @keyframes floatUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin: 0 0 10px 0;
            font-weight: 600;
            font-size: 1.8rem;
            background: -webkit-linear-gradient(0deg, #fff, #aaa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .qr-box {
            background: white;
            padding: 20px;
            border-radius: 16px;
            display: inline-block;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0, 210, 255, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 30px rgba(0, 210, 255, 0.2); }
            50% { box-shadow: 0 10px 40px rgba(0, 210, 255, 0.4); }
        }

        .qr-box img {
            display: block;
            border-radius: 8px;
        }

        .code-display {
            letter-spacing: 5px;
            margin: 10px 0 20px 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent);
            text-shadow: 0 0 20px rgba(0, 210, 255, 0.5);
        }

        .timer-box {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        #timer {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--accent);
            font-variant-numeric: tabular-nums;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 3px;
            font-weight: 600;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
            text-transform: uppercase;
        }

        input[type="text"]:focus {
            border-color: var(--accent);
            background: rgba(0,0,0,0.4);
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.1);
        }

        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--accent-hover) 0%, var(--accent) 100%);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4);
        }

        .alert-error {
            background: rgba(255, 75, 75, 0.15);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
    
    <script>
        // Temps restant en secondes (calculé côté serveur)
        let secondsRemaining = <?php echo max(0, $seconds_remaining); ?>;
        
        function updateTimer() {
            if (secondsRemaining <= 0) {
                document.getElementById("timer").innerHTML = "EXPIRÉ";
                document.getElementById("timer").style.color = "#ff6b6b";
                return;
            }
            
            const minutes = Math.floor(secondsRemaining / 60);
            const seconds = secondsRemaining % 60;
            
            document.getElementById("timer").innerHTML = 
                (minutes < 10 ? "0" : "") + minutes + ":" + 
                (seconds < 10 ? "0" : "") + seconds;
            
            secondsRemaining--;
        }
        
        // Mettre à jour toutes les secondes
        setInterval(updateTimer, 1000);
        updateTimer(); // Appel initial
    </script>
</head>
<body>

    <div class="glow"></div>

    <div class="glass-panel">
        <div style="margin-bottom: 20px;">
            <i class="fa-solid fa-shield-halved" style="font-size: 2.5rem; color: var(--accent); margin-bottom: 15px;"></i>
            <h2>Authentification 2FA</h2>
            <p class="subtitle">
                Scannez le QR code ou saisissez le code<br>pour valider votre accès sécurisé.
            </p>
        </div>

        <div class="timer-box">
            <i class="fa-regular fa-clock" style="color: var(--accent);"></i>
            <span style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">Temps restant :</span>
            <span id="timer">02:00</span>
        </div>
        
        <div class="qr-box">
            <img src="<?php echo $qr_url; ?>" width="150" alt="QR Code 2FA">
        </div>

        <div class="code-display"><?php echo $data['a2f_token']; ?></div>

        <form method="post">
            <?php if(isset($erreur)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo $erreur; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <input type="text" name="code" placeholder="CODE" required autocomplete="off" maxlength="6">
            </div>
            
            <button type="submit">
                Valider l'accès <i class="fa-solid fa-check" style="margin-left:8px;"></i>
            </button>
        </form>
    </div>

</body>
</html>

