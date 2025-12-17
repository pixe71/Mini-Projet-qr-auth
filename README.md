# Système de Gestion RFID avec Authentification 2FA

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=flat&logo=docker&logoColor=white)

## **Page de garde**

* **Titre du projet :** Système d'Authentification Sécurisée par QR Code (QR-Auth)
* **Nom(s) de(s) étudiant(s) :** **Luc Tournie** - [@pixe71](https://github.com/pixe71), **Rafael Galaup** - [@rafael12g](https://github.com/rafael12g)
* **Classe :** CIEL2
* **Année scolaire :** 2025-2026
* **Enseignant référent :** Mr Boudjelaba

---

# **1. Notice d’utilisation**

### **1.1. Objectif du produit ou de l’application**
La page web **QR-Auth** permet à un utilisateur de s'authentifier sur un site web de manière sécurisée avec mot de passe plus QR-Code en double authentification. Le système génère un QR Code unique sur l'écran de l'ordinateur, que l'utilisateur doit scanner via une application mobile dédiée (ou l'appareil photo) pour valider sa connexion.

### **1.2. Prérequis**
Pour mettre en place la solution, les éléments suivants sont requis :

- Docker
- BDD

**Matériel :**
* Un ordinateur (Serveur/Client Web)
* Un smartphone avec une caméra fonctionnelle.
* Une connexion réseau.

**Logiciels :**
* Un navigateur web récent (Chrome, Firefox...).

### **1.3. Procédure d’utilisation**

1.  **Démarrage du système :**
    * Lancer le navigateur et se connecter au site.
    * Le site est installé sur un raspberry

2.  **Connexion (Côté PC) :**
    * Ouvrez votre navigateur à l'adresse indiquée.
    * Sur la page d'acceuil cliquer sur Connexion ou Inscription selon le besoin.

3.  **Validation d'Authentification (Côté Smartphone) :**
    * Scannez le QR Code avec votre téléphone.
    * Un code apparait

4.  **Accès validé :**
    * La page sur l'ordinateur se rafraîchit automatiquement et vous donne accès à l'espace utilisateur.

### **1.4. Conseils et remarques**
* **Expiration :** Le QR Code est valable 120 secondes. Passé ce délai, veuillez rafraîchir la page pour en générer un nouveau.

---

# **2. Fiche(s) de recette**

### **Fiche recette n° 1 : Connexion standard**

* **Objectif du test :** Vérifier le processus complet d'authentification par scan.
* **Préconditions :** Serveur lancé, smartphone a porté de main.
* **Étapes du test :**
    1. Lancer l'application web sur le PC.
    2. Vérifier l'affichage du QR Code.
    3. Scanner le code avec le smartphone.
    4. Valider l'URL sur le smartphone.
    5. Observer la réaction de la page web sur le PC.

* **Résultat attendu :** La page web nous redirige sur une page de formulaire pour les utilisateurs et un dashboard pour les compte admins (au préalables mis en admin dans la BDD)
* **Résultat obtenu :** Redirection effectuée avec succès après le scan.
* **Validation (OK / KO) :** **OK**

---

### **Fiche recette n° 2 : Test de sécurité (Token expiré)**

* **Objectif du test :** Vérifier qu'un QR code n'est plus utilisable après utilisation ou expiration.
* **Préconditions :** Avoir déjà effectué une connexion valide.
* **Étapes du test :**
    1. Utiliser l'historique du téléphone pour rouvrir le lien du QR code précédent.
    2. Tenter de valider l'authentification à nouveau.
* **Validation (OK / KO) :** **OK**

---

# **3. Rapport du projet**

## **3.1. Introduction**
Ce projet s'inscrit dans le cadre de la sécurisation des accès informatiques. L'objectif était de développer une alternative aux mots de passe classiques en utilisant un objet physique (le smartphone) comme clé d'authentification. Nous avons réalisé une application web en Python capable de générer des codes uniques et de synchroniser deux appareils en temps réel.
en plus de cela une page web pour faire des demandes de création de badge rfid, communiquant avec un dashboard admin via la BDD.

## **3.2. Cahier des charges / Expression du besoin**
* **Fonctionnalités :**
    * Génération de QR Codes dynamiques.
    * Serveur web léger pour gérer les requêtes.
    * Détection de scan en temps réel (Polling ou WebSockets).
    * Interface utilisateur simple et responsive.
    * dashboard bien liée au responsive de l'utilisateur.
* **Contraintes :**
    * Utilisation du langage PHP/SQL.
    * Fonctionnement en réseau local.


## **3.3. Réalisation**

### **a) Description du travail effectué**
Nous avons structuré le projet selon une architecture web modulaire adaptée au PHP :
* **Scripts PHP (Contrôleur) :** Gèrent la logique métier, les routes et les interactions avec la base de données (`index.php`, `login.php`, `dashboard.php`, `api_*.php`).
* **Vues :** Les interfaces HTML sont générées dynamiquement au sein des fichiers PHP (`index.php`, `dashboard.php`), intégrant les données en temps réel.
* **Assets (Static) :** Le fichier `style.css` centralise le design (Glassmorphism), tandis que les icônes et polices sont chargées via des CDN.
---

## **3.4. Développement & Tests**

### **a) Code développé (extraits pertinents)**

```php
<?php
session_start();
require 'db.php';

// Si personne n'est en cours de processus (ni login, ni inscription), on dégage
if (!isset($_SESSION['temp_admin_id'])) { header("Location: index.php"); exit(); }

$id = $_SESSION['temp_admin_id'];
$stmt = $pdo->prepare("SELECT username, a2f_token, a2f_expiration FROM admins WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_user = trim($_POST['code']);
    $now = date('Y-m-d H:i:s');

    if ($data['a2f_token'] && $code_user === $data['a2f_token']) {
        if ($now <= $data['a2f_expiration']) {
            // SUCCÈS
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_username'] = $data['username'];
            unset($_SESSION['temp_admin_id']);
            
            // Nettoyage du token
            $clean = $pdo->prepare("UPDATE admins SET a2f_token = NULL WHERE id = ?");
            $clean->execute([$id]);

            header("Location: dashboard.php");
            exit();
        } else {
            $erreur = "Le code a expiré (Délai de 5 min dépassé).";
        }
    } else {
        $erreur = "Code incorrect.";
    }
}

// URL API QR Code
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($data['a2f_token']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sécurité 2FA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-panel">
        <h2>Authentification Double</h2>
        <p>Scannez pour valider l'accès.</p>
        
        <div class="qr-box">
            <img src="<?php echo $qr_url; ?>" width="150">
        </div>

        <h3 style="letter-spacing: 5px; margin: 10px 0;"><?php echo $data['a2f_token']; ?></h3>
        <small style="color: #aaa;">Expire à : <?php echo date('H:i:s', strtotime($data['a2f_expiration'])); ?></small>

        <form method="post" style="margin-top:20px;">
            <?php if(isset($erreur)) echo "<p class='error'>$erreur</p>"; ?>
            <input type="text" name="code" placeholder="Code QR" required autocomplete="off" style="text-align:center; font-size:1.2em; letter-spacing:2px;">
            <button type="submit">Valider</button>
        </form>
    </div>
</body>
</html>
```

---

<div align="center">

</div>


### Informations Utiles

Utilisateur de test : 

ID : test1

MDP : testtest

Utilisateur Admin :

ID : test

MDP : test
