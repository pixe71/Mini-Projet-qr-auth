<?php
session_start();
require 'db.php';

$erreur = null;
$succes = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $nom_complet = trim($_POST['nom_complet']);
    $email = trim($_POST['email']);

    // Validation
    if (empty($username) || empty($password) || empty($nom_complet) || empty($email)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($password !== $password_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si username existe déjà (admins + users)
        $check_admin = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $check_admin->execute([$username]);
        
        $check_user = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check_user->execute([$username]);
        
        if ($check_admin->fetch() || $check_user->fetch()) {
            $erreur = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // Créer l'utilisateur
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, password, nom_complet, email) VALUES (?,?,?,?)");
            
            if ($ins->execute([$username, $hashed, $nom_complet, $email])) {
                $succes = true;
            } else {
                $erreur = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --accent: #00d2ff;
            --accent-hover: #3a7bd5;
            --success: #00ff88;
            --danger: #ff4b4b;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: 1px solid rgba(255, 255, 255, 0.15);
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
            box-sizing: border-box;
        }

        .glow {
            position: fixed;
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
            max-width: 420px;
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
            z-index: 2;
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
            z-index: 1;
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
            font-family: inherit;
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

        .success-panel {
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        .success-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--success);
        }
        .success-text {
            color: rgba(255,255,255,0.7);
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .btn-login {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(90deg, var(--accent-hover) 0%, var(--accent) 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
            box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }
        .btn-login:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="glass-panel">
        <?php if($succes): ?>
            <div class="success-panel">
                <div class="success-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="success-title">Compte créé avec succès !</div>
                <div class="success-text">
                    Votre compte utilisateur a été créé.<br>
                    Vous pouvez maintenant vous connecter.
                </div>
                <a href="login.php" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Se connecter
                </a>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 20px;">
                <i class="fa-solid fa-user-plus" style="font-size: 2.5rem; color: var(--accent);"></i>
            </div>
            <h2>Créer un compte</h2>

            <?php if($erreur): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $erreur; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="input-group">
                    <input type="text" name="username" required autocomplete="off" placeholder=" " value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fa-solid fa-user"></i>
                    <span class="floating-label">Identifiant</span>
                </div>

                <div class="input-group">
                    <input type="text" name="nom_complet" required placeholder=" " value="<?php echo isset($_POST['nom_complet']) ? htmlspecialchars($_POST['nom_complet']) : ''; ?>">
                    <i class="fa-solid fa-id-card"></i>
                    <span class="floating-label">Nom complet</span>
                </div>

                <div class="input-group">
                    <input type="email" name="email" required placeholder=" " value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="floating-label">Email</span>
                </div>

                <div class="input-group">
                    <input type="password" name="password" required placeholder=" ">
                    <i class="fa-solid fa-key"></i>
                    <span class="floating-label">Mot de passe</span>
                </div>

                <div class="input-group">
                    <input type="password" name="password_confirm" required placeholder=" ">
                    <i class="fa-solid fa-lock"></i>
                    <span class="floating-label">Confirmer le mot de passe</span>
                </div>
                
                <button type="submit">
                    Créer mon compte <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
                </button>
            </form>
            
            <div class="links">
                <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0;"></div>
                <a href="login.php"><i class="fa-solid fa-chevron-left"></i> Déjà un compte ? Se connecter</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>