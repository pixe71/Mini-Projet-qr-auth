<?php
session_start();
require 'db.php';

if (isset($_SESSION['admin_logged'])) { header("Location: dashboard.php"); exit(); }
if (isset($_SESSION['user_logged'])) { header("Location: calendar.php"); exit(); }

$erreur = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Admin
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
        $pdo->prepare("UPDATE admins SET a2f_token = ?, a2f_expiration = ? WHERE id = ?")->execute([$code, $expiry, $admin['id']]);
        $_SESSION['temp_admin_id'] = $admin['id'];
        $_SESSION['temp_user_type'] = 'admin'; // Indiquer qu'il s'agit d'un admin
        header("Location: login_2fa.php");
        exit();
    }

    // User
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Générer code 2FA pour utilisateur
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
        $pdo->prepare("UPDATE users SET a2f_token = ?, a2f_expiration = ? WHERE id = ?")->execute([$code, $expiry, $user['id']]);
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['temp_user_type'] = 'user'; // Indiquer qu'il s'agit d'un user
        header("Location: login_2fa.php");
        exit();
    }

    $erreur = "Identifiant ou mot de passe incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Outfit',sans-serif; background:radial-gradient(circle at 10% 20%,#000 0%,#181823 90.2%); height:100vh; display:flex; align-items:center; justify-content:center; color:#fff; }
        .panel { background:rgba(255,255,255,0.08); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.15); border-radius:24px; padding:40px; max-width:360px; width:100%; box-shadow:0 20px 50px rgba(0,0,0,0.5); text-align:center; }
        h2 { margin:10px 0 30px; font-size:1.6rem; }
        .input-group { position:relative; margin-bottom:20px; }
        .input-group i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.4); }
        input { width:100%; padding:15px 15px 15px 45px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:#fff; font-size:1rem; outline:none; box-sizing:border-box; font-family:inherit; }
        input:focus { border-color:#00d2ff; }
        button { width:100%; padding:15px; margin-top:10px; border:none; border-radius:12px; background:linear-gradient(90deg,#3a7bd5,#00d2ff); color:#fff; font-weight:600; font-size:1rem; cursor:pointer; }
        button:hover { transform:translateY(-2px); }
        .links { margin-top:25px; font-size:0.85rem; }
        .links a { color:rgba(255,255,255,0.4); text-decoration:none; }
        .links a:hover { color:#fff; }
        .error { background:rgba(255,75,75,0.15); border:1px solid rgba(255,75,75,0.3); color:#ff6b6b; padding:12px; border-radius:10px; margin-bottom:20px; }
    </style>
</head>
<body>
    <div class="panel">
        <i class="fa-solid fa-right-to-bracket" style="font-size:2.5rem;color:#00d2ff;margin-bottom:20px;"></i>
        <h2>Connexion</h2>
        <?php if($erreur): ?><div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?=$erreur?></div><?php endif; ?>
        <form method="post">
            <div class="input-group"><i class="fa-solid fa-user"></i><input type="text" name="username" placeholder="Identifiant" required></div>
            <div class="input-group"><i class="fa-solid fa-key"></i><input type="password" name="password" placeholder="Mot de passe" required></div>
            <button type="submit">Se connecter</button>
        </form>
        <div class="links"><a href="register.php"><i class="fa-solid fa-user-plus"></i> Créer un compte</a></div>
    </div>
</body>
</html>
