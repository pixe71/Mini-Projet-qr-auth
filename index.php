<?php
session_start();
require 'db.php';

$success = false;
$erreur = false;
$max_attempts = 3; 
$time_window = 30; 
$cooldown_time = 120; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialiser le tableau des tentatives
    if (!isset($_SESSION['request_attempts'])) {
        $_SESSION['request_attempts'] = [];
    }
    
    // Nettoyer les anciennes tentatives
    $current_time = time();
    $_SESSION['request_attempts'] = array_filter($_SESSION['request_attempts'], function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    // Vérifier cooldown
    if (isset($_SESSION['cooldown_until']) && $current_time < $_SESSION['cooldown_until']) {
        $remaining = $_SESSION['cooldown_until'] - $current_time;
        $erreur = "Patience ! Attendez encore $remaining secondes.";
    } 
    // Vérifier tentatives
    elseif (count($_SESSION['request_attempts']) >= $max_attempts) {
        $_SESSION['cooldown_until'] = $current_time + $cooldown_time;
        $erreur = "Trop de requêtes. Pause de 2 minutes imposée.";
    }
    
    if (!$erreur) {
        // CORRECTION ICI : Utilisation de ?? '' pour éviter l'erreur "Undefined array key"
        $nom_complet = htmlspecialchars(trim($_POST['nom_complet'] ?? ''));
        
        if (!empty($nom_complet)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users_rfid (nom_complet, status) VALUES (?, ?)");
                
                if ($stmt->execute([$nom_complet, 'en_attente'])) {
                    $success = true;
                    $_SESSION['request_attempts'][] = $current_time;
                    // On vide le POST pour éviter que le champ reste rempli
                    $_POST = array(); 
                }
            } catch (PDOException $e) {
                $erreur = "Erreur technique. Réessayez plus tard.";
            }
        } else {
            $erreur = "Le nom est obligatoire.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'Accès RFID</title>
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
            max-width: 400px;
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

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.4);
            transition: 0.3s;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input[type="text"]:focus {
            border-color: var(--accent);
            background: rgba(0,0,0,0.4);
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.1);
        }

        input[type="text"]:focus + i {
            color: var(--accent);
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

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: left;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .alert-success {
            background: rgba(46, 213, 115, 0.15);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }

        .alert-error {
            background: rgba(255, 75, 75, 0.15);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff6b6b;
        }

        .link-admin {
            display: inline-block;
            margin-top: 25px;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
        }
        .link-admin:hover { color: white; }

        .check-icon {
            font-size: 3rem;
            color: var(--success);
            margin-bottom: 20px;
            display: inline-block;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }

        /* Floating Label Fix */
        input::placeholder { color: transparent; }
        .floating-label {
            position: absolute; left: 45px; top: 50%; transform: translateY(-50%);
            pointer-events: none; transition: 0.3s; color: rgba(255,255,255,0.4);
        }
        input:focus ~ .floating-label, input:not(:placeholder-shown) ~ .floating-label {
            top: -10px; left: 10px; font-size: 0.75rem; color: var(--accent); background: #1a1a24; padding: 0 5px; border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="glass-panel">
        
        <?php if($success): ?>
            <div>
                <i class="fa-solid fa-circle-check check-icon"></i>
                <h2>Demande Envoyée</h2>
                <div class="alert alert-success" style="justify-content: center; text-align:center;">
                    Votre demande est en cours d'examen par l'administrateur.
                </div>
                <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                    Vous pouvez fermer cette page.
                </p>
                <a href="index.php" style="color: var(--accent); text-decoration:none; font-size:0.9rem; margin-top:20px; display:block;">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Nouvelle demande
                </a>
            </div>

        <?php else: ?>
            <div style="margin-bottom: 30px;">
                <i class="fa-solid fa-id-card-clip" style="font-size: 2.5rem; color: var(--accent); margin-bottom: 15px;"></i>
                <h2>Accès RFID</h2>
                <p class="subtitle">
                    Entrez votre identité pour initier la création<br>de votre badge sécurisé.
                </p>
            </div>
            
            <?php if($erreur): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo $erreur; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="input-group">
                    <input type="text" name="nom_complet" required autocomplete="off" placeholder=" " 
                           value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>">
                    
                    <i class="fa-regular fa-user"></i>
                    <span class="floating-label">Nom & Prénom</span>
                </div>
                
                <button type="submit">
                    Envoyer ma demande <i class="fa-solid fa-paper-plane" style="margin-left:8px;"></i>
                </button>
            </form>
        <?php endif; ?>
        
        <a href="login.php" class="link-admin">
            <i class="fa-solid fa-lock"></i> Accès Administrateur
        </a>
    </div>

</body>
</html>
