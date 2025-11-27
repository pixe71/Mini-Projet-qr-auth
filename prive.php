<?php
session_start();

if (!isset($_SESSION['est_connecte']) || $_SESSION['est_connecte'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<h1>ACCÈS AUTORISÉ</h1>
<p>Vous êtes connecté avec succès via Mot de passe + QR Code temporaire.</p>
<a href="logout.php">Déconnexion</a>