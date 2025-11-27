<?php
session_start();
require 'db.php';

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 1. Vérifications de base
    if ($password !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // 2. Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $erreur = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // 3. CRÉATION DU COMPTE
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // On insère l'admin
            $insert = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            if ($insert->execute([$username, $hash])) {
                
                // Récupérer l'ID du nouvel inscrit
                $new_user_id = $pdo->lastInsertId();

                // 4. GÉNÉRATION IMMÉDIATE DU TOKEN 2FA (Comme au login)
                $code_random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
                $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Mise à jour avec le token
                $update = $pdo->prepare("UPDATE admins SET a2f_token = ?, a2f_expiration = ? WHERE id = ?");
                $update->execute([$code_random, $expiry, $new_user_id]);

                // 5. REDIRECTION VERS LA VALIDATION QR
                $_SESSION['temp_admin_id'] = $new_user_id;
                header("Location: login_2fa.php");
                exit();

            } else {
                $erreur = "Erreur lors de l'inscription.";
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-panel">
        <h2>Créer un compte</h2>
        <?php if($erreur) echo "<p class='error'>$erreur</p>"; ?>
        
        <form method="post">
            <input type="text" name="username" placeholder="Nouvel Identifiant" required autocomplete="off">
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        
        <br>
        <a href="index.php">Retour connexion</a>
    </div>
</body>
</html>