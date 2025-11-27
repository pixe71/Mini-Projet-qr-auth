<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 1. Générer un code aléatoire (6 caractères Hexadécimal)
        $token = strtoupper(bin2hex(random_bytes(3))); 
        
        // 2. Définir l'expiration (Maintenant + 5 minutes)
        // Note: On utilise le fuseau horaire du serveur SQL ou PHP, attention à la synchro.
        $expiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // 3. Mettre à jour la BDD
        $update = $pdo->prepare("UPDATE users SET token_2fa = ?, token_expiration = ? WHERE id = ?");
        $update->execute([$token, $expiration, $user['id']]);

        // 4. Stocker l'ID temporairement en session (pas encore connecté officiellement)
        $_SESSION['temp_user_id'] = $user['id'];
        
        // 5. Redirection vers la vérification QR
        header("Location: verify_2fa.php");
        exit();
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><title>Connexion</title></head>
<body>
    <form method="post">
        <h2>Connexion (Étape 1)</h2>
        <?php if (isset($erreur)) echo "<p style='color:red'>$erreur</p>"; ?>
        <input type="text" name="username" placeholder="Identifiant" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <button type="submit">Suivant >></button>
    </form>
    <br><a href="register.php">Créer un compte</a>
</body>
</html>