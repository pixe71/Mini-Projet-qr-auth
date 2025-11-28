<?php
session_start();
require 'db.php';

// Si déjà connecté, on redirige
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: dashboard.php");
    exit();
}

$erreur = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Récupération de l'admin
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // --- LOGIQUE A2F (Authentification à 2 Facteurs) ---
        // Génération d'un code aléatoire de 6 caractères
        $code_random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        // On stocke le token dans la BDD pour vérification page suivante
        $upd = $pdo->prepare("UPDATE admins SET a2f_token = ?, a2f_expiration = ? WHERE id = ?");
        $upd->execute([$code_random, $expiry, $admin['id']]);

        // On garde l'ID en session temporaire pour la page login_2fa.php
        $_SESSION['temp_admin_id'] = $admin['id'];
        
        // Redirection vers la vérification du code
        header("Location: login_2fa.php");
        exit();
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- COPIE EXACTE DU STYLE GLOBAL --- */
        :root {
            --accent: #00d2ff;
            --accent-hover: #3a7bd5;
            --danger: #ff4b4b;
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
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(58, 123, 213, 0.15) 0%, rgba(0,0,0,0) 70%);
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
            max-width: 360px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            text-align: center;
            animation: floatUp 0.8s ease-out;
        }

        @keyframes floatUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin: 10px 0 30px 0;
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 1.6rem;
        }

        /* INPUTS MODERNES */
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

        input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
            font-family: inherit;
        }

        input:focus {
            border-color: var(--accent);
            background: rgba(0,0,0,0.4);
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.1);
        }
        
        input:focus + i { color: var(--accent); }

        /* Label flottant */
        input::placeholder { color: transparent; }
        .floating-label {
            position: absolute; left: 45px; top: 50%; transform: translateY(-50%);
            pointer-events: none; transition: 0.3s; color: rgba(255,255,255,0.4); font-size: 0.9rem;
        }
        input:focus ~ .floating-label, input:not(:placeholder-shown) ~ .floating-label {
            top: -10px; left: 10px; font-size: 0.75rem; color: var(--accent); background: #15151e; padding: 0 5px; border-radius: 4px;
        }

        /* BOUTON */
        button {
            width: 100%; padding: 15px; margin-top: 10px;
            border: none; border-radius: 12px;
            background: linear-gradient(90deg, var(--accent-hover) 0%, var(--accent) 100%);
            color: white; font-weight: 600; font-size: 1rem; cursor: pointer;
            transition: 0.2s; box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4); }

        /* LINKS */
        .links {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 0.85rem;
        }
        .links a { color: rgba(255,255,255,0.4); text-decoration: none; transition: 0.3s; }
        .links a:hover { color: white; }

        /* ALERT ERROR */
        .alert-error {
            background: rgba(255, 75, 75, 0.15);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff6b6b; padding: 12px; border-radius: 10px;
            font-size: 0.9rem; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="glass-panel">
        <div style="margin-bottom: 20px;">
            <i class="fa-solid fa-shield-halved" style="font-size: 2.5rem; color: var(--accent);"></i>
        </div>
        <h2>Espace Admin</h2>

        <?php if($erreur): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $erreur; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <input type="text" name="username" required autocomplete="off" placeholder=" ">
                <i class="fa-solid fa-user"></i>
                <span class="floating-label">Identifiant</span>
            </div>

            <div class="input-group">
                <input type="password" name="password" required placeholder=" ">
                <i class="fa-solid fa-key"></i>
                <span class="floating-label">Mot de passe</span>
            </div>
            
            <button type="submit">
                Connexion <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
            </button>
        </form>
        
        <div class="links">
            <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0;"></div>
            <a href="index.php"><i class="fa-solid fa-chevron-left"></i> Retour à l'accueil</a>
        </div>
    </div>

</body>
</html>
