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

    if (empty($username) || empty($password) || empty($nom_complet) || empty($email)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($password !== $password_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? UNION SELECT id FROM admins WHERE username = ?");
        $check->execute([$username, $username]);
        
        if ($check->fetch()) {
            $erreur = "Ce nom d'utilisateur est déjà pris.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            if ($pdo->prepare("INSERT INTO users (username, password, nom_complet, email) VALUES (?,?,?,?)")->execute([$username, $hashed, $nom_complet, $email])) {
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
    <title>Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Outfit',sans-serif; background:radial-gradient(circle at 10% 20%,#000 0%,#181823 90.2%); min-height:100vh; display:flex; align-items:center; justify-content:center; color:#fff; padding:20px; }
        .panel { background:rgba(255,255,255,0.08); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.15); border-radius:24px; padding:40px; max-width:420px; width:100%; box-shadow:0 20px 50px rgba(0,0,0,0.5); text-align:center; }
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
        .success { font-size:4rem; color:#00ff88; margin-bottom:20px; }
        .btn-login { display:inline-block; padding:12px 30px; background:linear-gradient(90deg,#3a7bd5,#00d2ff); color:#fff; text-decoration:none; border-radius:10px; font-weight:600; margin-top:20px; }
    </style>
</head>
<body>
    <div class="panel">
        <?php if($succes): ?>
            <div class="success"><i class="fa-solid fa-circle-check"></i></div>
            <h2 style="color:#00ff88;">Compte créé !</h2>
            <p style="color:rgba(255,255,255,0.7);">Vous pouvez maintenant vous connecter.</p>
            <a href="login.php" class="btn-login">Se connecter</a>
        <?php else: ?>
            <i class="fa-solid fa-user-plus" style="font-size:2.5rem;color:#00d2ff;margin-bottom:20px;"></i>
            <h2>Créer un compte</h2>
            <?php if($erreur): ?><div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?=$erreur?></div><?php endif; ?>
            <form method="post">
                <div class="input-group"><i class="fa-solid fa-user"></i><input type="text" name="username" placeholder="Identifiant" required></div>
                <div class="input-group"><i class="fa-solid fa-id-card"></i><input type="text" name="nom_complet" placeholder="Nom complet" required></div>
                <div class="input-group"><i class="fa-solid fa-envelope"></i><input type="email" name="email" placeholder="Email" required></div>
                <div class="input-group"><i class="fa-solid fa-key"></i><input type="password" name="password" placeholder="Mot de passe" required></div>
                <div class="input-group"><i class="fa-solid fa-lock"></i><input type="password" name="password_confirm" placeholder="Confirmer" required></div>
                <button type="submit">Créer mon compte</button>
            </form>
            <div class="links"><a href="login.php"><i class="fa-solid fa-chevron-left"></i> Déjà un compte ?</a></div>
        <?php endif; ?>
    </div>
</body>
</html>
