<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rfid = $_POST['rfid'];

    // Hachage du mot de passe (Indispensable !)
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion dans la BDD
    $stmt = $pdo->prepare("INSERT INTO users (username, password, rfid_uid) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$username, $hash, $rfid]);
        $message = "Utilisateur créé avec succès ! Badge RFID associé.";
    } catch (Exception $e) {
        $message = "Erreur : L'utilisateur existe déjà.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><title>Création Utilisateur RFID</title></head>
<body>
    <h2>Enrôlement Nouvel Utilisateur (Badge RFID)</h2>
    <?php if ($message) echo "<p style='color:green'>$message</p>"; ?>
    
    <form method="post">
        <label>Nom d'utilisateur :</label><br>
        <input type="text" name="username" required><br><br>

        <label>Mot de passe initial :</label><br>
        <input type="password" name="password" required><br><br>

        <label>UID du Badge RFID (Scanner le badge ici) :</label><br>
        <input type="text" name="rfid" placeholder="ex: A4-F2-90-11"><br><br>

        <button type="submit">Créer l'utilisateur</button>
    </form>
    <br><a href="login.php">Aller à la connexion</a>
</body>
</html>