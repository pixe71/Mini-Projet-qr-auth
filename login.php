<?php
session_start();
require 'db.php';

// Si déjà connecté, rediriger selon le type
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: dashboard.php");
    exit();
}
if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true) {
    header("Location: calendar.php");
    exit();
}

$erreur = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // D'abord vérifier si c'est un admin
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // C'est un admin - Processus 2FA
        $code_random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $upd = $pdo->prepare("UPDATE admins SET a2f_token = ?, a2f_expiration = ? WHERE id = ?");
        $upd->execute([$code_random, $expiry, $admin['id']]);

        $_SESSION['temp_admin_id'] = $admin['id'];
        header("Location: login_2fa.php");
        exit();
    }

    // Sinon vérifier si c'est un utilisateur normal
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // C'est un utilisateur normal - Connexion directe
        $_SESSION['user_logged'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_nom_complet'] = $user['nom_complet'];
        
        header("Location: calendar.php");
        exit();
    }

    // Aucun match
    $erreur = "Identifiant ou mot de passe incorrect.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
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

        input::placeholder { color: transparent; }
        .floating-label {
            position: absolute; left: 45px; top: 50%; transform: translateY(-50%);
            pointer-events: none; transition: 0.3s; color: rgba(255,255,255,0.4); font-size: 0.9rem;
        }
        input:focus ~ .floating-label, input:not(:placeholder-shown) ~ .floating-label {
            top: -10px; left: 10px; font-size: 0.75rem; color: var(--accent); background: #15151e; padding: 0 5px; border-radius: 4px;
        }

        button {
            width: 100%; padding: 15px; margin-top: 10px;
            border: none; border-radius: 12px;
            background: linear-gradient(90deg, var(--accent-hover) 0%, var(--accent) 100%);
            color: white; font-weight: 600; font-size: 1rem; cursor: pointer;
            transition: 0.2s; box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4); }

        .links {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 0.85rem;
        }
        .links a { color: rgba(255,255,255,0.4); text-decoration: none; transition: 0.3s; }
        .links a:hover { color: white; }

        .alert-error {
            background: rgba(255, 75, 75, 0.15);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff6b6b; padding: 12px; border-radius: 10px;
            font-size: 0.9rem; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }

        .info-box {
            background: rgba(0, 210, 255, 0.1);
            border: 1px solid rgba(0, 210, 255, 0.3);
            color: rgba(255,255,255,0.7);
            padding: 15px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            text-align: left;
        }

        .info-box strong {
            color: var(--accent);
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="glass-panel">
        <div style="margin-bottom: 20px;">
            <i class="fa-solid fa-right-to-bracket" style="font-size: 2.5rem; color: var(--accent);"></i>
        </div>
        <h2>Connexion</h2>

        <div class="info-box">
            <i class="fa-solid fa-info-circle"></i>
            <strong>Admins</strong> : Accès dashboard avec 2FA<br>
            <strong>Utilisateurs</strong> : Accès calendrier direct
        </div>

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
                Se connecter <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
            </button>
        </form>
        
        <div class="links">
            <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0;"></div>
            <a href="register.php"><i class="fa-solid fa-user-plus"></i> Créer un compte</a>
        </div>
    </div>

</body>
</html>
