<?php
session_start();
require 'db.php';

// Protection : Si pas admin, dehors !
if (!isset($_SESSION['admin_logged'])) { header("Location: index.php"); exit(); }

// Ajout d'un utilisateur pour badge
if (isset($_POST['add_user'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $stmt = $pdo->prepare("INSERT INTO users_rfid (nom_complet, status) VALUES (?, 'en_attente')");
    $stmt->execute([$nom]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard RFID</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="glass-panel wide">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1>Gestion Badges RFID</h1>
            <a href="logout.php" style="color:#ff6b6b; border:1px solid #ff6b6b; padding:5px 15px; border-radius:20px;">Déconnexion</a>
        </div>
        
        <div style="background: rgba(255,255,255,0.05); padding:20px; border-radius:15px; margin-bottom:30px; border:1px solid rgba(255,255,255,0.1);">
            <h3 style="margin-top:0;">Enrôler un utilisateur</h3>
            <p style="color:#ccc; font-size:0.9em; margin-bottom:15px;">Entrez le nom, l'ESP32 flashera le badge automatiquement.</p>
            
            <form method="post" style="display:flex; gap:10px;">
                <input type="text" name="nom" placeholder="Nom Prénom" required style="margin:0;">
                <button type="submit" name="add_user" style="width:auto; white-space:nowrap;">Ajouter à la file</button>
            </form>
        </div>

        <h3>Utilisateurs & Badges</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>UID Badge</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $users = $pdo->query("SELECT * FROM users_rfid ORDER BY id DESC");
                while($u = $users->fetch()) {
                    $status_class = ($u['status'] == 'actif') ? "status-actif" : "status-attente";
                    $status_text = ($u['status'] == 'actif') ? "ACTIF" : "EN ATTENTE";
                    $uid_display = $u['rfid_uid'] ? "<span style='font-family:monospace; background:rgba(0,0,0,0.3); padding:2px 6px; border-radius:4px;'>".$u['rfid_uid']."</span>" : "<span style='color:#666'>...</span>";
                    
                    echo "<tr>
                        <td>#{$u['id']}</td>
                        <td style='font-weight:bold;'>{$u['nom_complet']}</td>
                        <td>$uid_display</td>
                        <td class='$status_class'>$status_text</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>