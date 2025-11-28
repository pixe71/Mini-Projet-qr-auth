<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // GÉNÉRATION DU TOKEN
        $code_random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $upd = $pdo->prepare("UPDATE admins SET a2f_token = ?, a2f_expiration = ? WHERE id = ?");
        $upd->execute([$code_random, $expiry, $admin['id']]);

        $_SESSION['temp_admin_id'] = $admin['id'];
        header("Location: login_2fa.php");
        exit();
    } else {
        $erreur = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-panel">
        <h2>Connexion Admin</h2>
        <?php if(isset($erreur)) echo "<p class='error'>$erreur</p>"; ?>
        
        <form method="post">
            <input type="text" name="username" placeholder="Identifiant" required autocomplete="off">
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Connexion</button>
        </form>
        
        <br>
        <a href="register.php">Créer un compte</a>
        <br><br>
        <a href="index.php">← Retour à l'accueil</a>
    </div>
</body>
</html>
