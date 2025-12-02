# 🔐 Système de Gestion RFID avec Authentification 2FA

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

Système complet de gestion de badges RFID avec interface web sécurisée par authentification à deux facteurs (2FA) et intégration ESP32.

🌐 **Démo en ligne** : [https://miniprojet.pixe71.dev/](https://miniprojet.pixe71.dev/)

---

## 📋 Table des Matières

- [Vue d'ensemble](#-vue-densemble)
- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Structure du Projet](#-structure-du-projet)
- [API Documentation](#-api-documentation)
- [Base de Données](#-base-de-données)
- [Sécurité](#-sécurité)
- [Dépannage](#-dépannage)
- [Contribution](#-contribution)

---

## 🎯 Vue d'ensemble

Ce projet est un système complet de gestion de contrôle d'accès RFID qui combine :
- Une **interface web moderne** avec design glassmorphism
- Une **authentification sécurisée 2FA** par QR code
- Une **intégration ESP32** pour la programmation physique des badges
- Une **API RESTful** pour la communication avec le matériel
- Un **tableau de bord administrateur** pour la gestion centralisée

### Workflow du Système

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│  Utilisateur │─────▶│  Interface   │─────▶│     BDD     │
│   (Public)   │      │     Web      │      │   MySQL     │
└─────────────┘      └──────────────┘      └─────────────┘
                            │                      ▲
                            │                      │
                            ▼                      │
                     ┌──────────────┐              │
                     │  Admin 2FA   │──────────────┘
                     │  Dashboard   │
                     └──────────────┘
                            │
                            ▼
                     ┌──────────────┐
                     │   API REST   │
                     └──────────────┘
                            │
                            ▼
                     ┌──────────────┐
                     │    ESP32     │
                     │  RFID Reader │
                     └──────────────┘
```

---

## ✨ Fonctionnalités

### 🔓 Interface Publique
- ✅ Formulaire de demande d'accès RFID
- ✅ Protection anti-spam avec rate limiting (3 requêtes/30s)
- ✅ Cooldown de 2 minutes après dépassement
- ✅ Design responsive et moderne (glassmorphism)
- ✅ Validation en temps réel

### 🔒 Espace Administrateur
- ✅ Authentification sécurisée avec 2FA
- ✅ QR code dynamique pour la validation
- ✅ Timer de session (2 minutes)
- ✅ Dashboard avec statistiques en temps réel
- ✅ Gestion complète des utilisateurs
- ✅ Export CSV des données
- ✅ Ajout rapide d'utilisateurs

### 📊 Dashboard
- ✅ **4 KPI en temps réel** :
  - Demandes web en attente
  - Badges en file ESP32
  - Badges actifs
  - Total utilisateurs
- ✅ Validation/refus des demandes
- ✅ Reprogrammation de badges
- ✅ Suppression d'utilisateurs
- ✅ État système en direct

### 🤖 API ESP32
- ✅ Endpoints GET/POST pour synchronisation
- ✅ Attribution automatique des badges
- ✅ Mise à jour du statut en temps réel
- ✅ Gestion des UID RFID

---

## 🏗️ Architecture

### Stack Technique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| Backend | PHP | 7.4+ |
| Base de données | MySQL | 5.7+ |
| Frontend | HTML5 + CSS3 + JavaScript | ES6+ |
| Framework CSS | Custom Glassmorphism | - |
| Fonts | Google Fonts (Outfit) | - |
| Icons | Font Awesome | 6.0.0 |
| API | RESTful JSON | - |

### Sécurité Implémentée

```php
✓ Hachage de mots de passe (password_hash)
✓ Tokens 2FA avec expiration
✓ Protection XSS (htmlspecialchars)
✓ Requêtes préparées PDO (anti-SQL injection)
✓ Sessions PHP sécurisées
✓ Rate limiting personnalisé
✓ Validation côté serveur
```

---

## 🔧 Prérequis

- **Serveur web** : Apache 2.4+ ou Nginx
- **PHP** : Version 7.4 ou supérieure
  - Extension PDO_MySQL activée
  - Extension JSON activée
- **MySQL** : Version 5.7 ou supérieure
- **Accès distant** à la base de données (si ESP32)
- **Certificat SSL** (recommandé pour la production)

### Matériel (optionnel)
- ESP32 (pour intégration RFID physique)
- Lecteur RFID RC522
- Cartes/tags RFID compatibles

---

## 📦 Installation

### Étape 1 : Cloner le Projet

```bash
git clone https://github.com/pixe71/Mini-Projet-qr-auth.git
cd Mini-Projet-qr-auth
```

### Étape 2 : Configuration Base de Données

1. Importer la structure SQL :

```bash
mysql -u root -p < BDD.sql
```

2. Modifier `db.php` avec vos identifiants :

```php
$host = 'votre_host';      // Exemple: localhost
$dbname = 'rfid';
$user = 'votre_user';
$pass = 'votre_password';
```

### Étape 3 : Configuration Serveur Web

#### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/html/Mini-Projet-qr-auth;
    index index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }
}
```

### Étape 4 : Permissions

```bash
chmod 755 -R .
chmod 644 *.php
```

---

## ⚙️ Configuration

### Variables d'Environnement

Créer un fichier `.env` (recommandé pour la production) :

```env
DB_HOST=bdd.luc-tournie.fr
DB_USER=rfid
DB_PASSWORD=iv4VEvp&1C7vb5X&Pz5o
DB_NAME=rfid
DB_PORT=3306

# Sécurité
SESSION_LIFETIME=3600
MAX_REQUESTS=3
TIME_WINDOW=30
COOLDOWN_TIME=120
```

### Configuration BDD Actuelle

```
Hôte : bdd.luc-tournie.fr
Utilisateur : rfid
Mot de passe : iv4VEvp&1C7vb5X&Pz5o
Base de données : rfid
Port : 3306
```

⚠️ **Important** : Changez ces identifiants en production !

---

## 🚀 Utilisation

### 1. Interface Utilisateur (Public)

**URL** : `https://votre-domaine.com/index.php`

**Actions disponibles** :
1. Saisir nom et prénom
2. Soumettre la demande
3. Attendre validation administrateur

**Limitations** :
- 3 requêtes maximum par 30 secondes
- Cooldown de 2 minutes si dépassement

### 2. Connexion Administrateur

**URL** : `https://votre-domaine.com/login.php`

**Processus** :
1. Saisir identifiant et mot de passe
2. Scanner le QR code 2FA généré
3. Saisir le code à 6 caractères
4. Valider dans les 2 minutes

### 3. Dashboard Administrateur

**URL** : `https://votre-domaine.com/dashboard.php`

**Fonctionnalités** :

#### Validation de Demandes
```
1. Section "Demandes à valider"
2. Cliquer sur ✓ pour approuver
3. Cliquer sur ✗ pour refuser
→ Statut passe de "pending_validation" à "en_attente"
```

#### Gestion des Badges
```
- ↻ Reprogrammer : Remet un badge actif en attente
- 🗑️ Supprimer : Efface définitivement l'utilisateur
```

#### Ajout Rapide
```
1. Section "Ajout Rapide"
2. Saisir un nom
3. Envoyer → Ajout direct en file ESP32
```

#### Export CSV
```
Bouton "CSV" en haut à droite
→ Télécharge tous les utilisateurs
```

### 4. Inscription (Nouvel Admin)

**URL** : `https://votre-domaine.com/register.php`

**Processus** :
1. Créer identifiant/mot de passe
2. Validation 2FA immédiate
3. Redirection dashboard

---

## 📁 Structure du Projet

```
Mini-Projet-qr-auth/
│
├── 📄 index.php              # Page d'accueil (demande publique)
├── 📄 login.php              # Connexion administrateur
├── 📄 login_2fa.php          # Validation QR code 2FA
├── 📄 register.php           # Inscription nouvel admin
├── 📄 dashboard.php          # Tableau de bord admin
├── 📄 logout.php             # Déconnexion
│
├── 🔌 db.php                 # Configuration PDO MySQL
├── 🔌 api.php                # API REST pour ESP32 (v1)
├── 🔌 api_esp32.php          # API REST pour ESP32 (v2)
│
├── 🎨 style.css              # Styles globaux
├── 🗄️ BDD.sql                # Structure base de données
└── 📖 README.md              # Documentation (ce fichier)
```

### Description des Fichiers

| Fichier | Rôle | Accès |
|---------|------|-------|
| `index.php` | Formulaire de demande RFID | Public |
| `login.php` | Authentification admin (étape 1) | Public |
| `login_2fa.php` | Validation 2FA (étape 2) | Session |
| `register.php` | Création compte admin | Public |
| `dashboard.php` | Interface de gestion | Admin |
| `logout.php` | Destruction session | Admin |
| `db.php` | Connexion BDD | Include |
| `api.php` | Endpoint ESP32 (jobs/sync) | ESP32 |
| `api_esp32.php` | Endpoint ESP32 alternatif | ESP32 |
| `style.css` | Design glassmorphism | Include |
| `BDD.sql` | Schéma tables | Setup |

---

## 🔌 API Documentation

### Endpoint : `/api.php`

#### GET - Récupérer le Prochain Job

**Requête** :
```http
GET /api.php HTTP/1.1
Host: miniprojet.pixe71.dev
```

**Réponse (Job disponible)** :
```json
{
  "job": true,
  "id": 5,
  "nom": "Thomas Dupont"
}
```

**Réponse (Pas de job)** :
```json
{
  "job": false
}
```

#### POST - Confirmer Badge Programmé

**Requête** :
```http
POST /api.php HTTP/1.1
Host: miniprojet.pixe71.dev
Content-Type: application/json

{
  "id": 5,
  "uid": "A3B2C1D4"
}
```

**Réponse (Succès)** :
```json
{
  "status": "ok"
}
```

**Réponse (Erreur)** :
```json
{
  "status": "error"
}
```

### Endpoint : `/api_esp32.php`

#### GET - Vérifier File d'Attente

**Requête** :
```http
GET /api_esp32.php HTTP/1.1
```

**Réponse** :
```json
{
  "found": true,
  "id": 12,
  "nom": "Jean Martin"
}
```

#### POST - Associer UID

**Requête** :
```json
{
  "id": 12,
  "uid": "F5E4D3C2"
}
```

**Réponse** :
```json
{
  "status": "success",
  "message": "Badge associe"
}
```

### Code Exemple ESP32 (Arduino)

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <MFRC522.h>

const char* ssid = "VotreWiFi";
const char* password = "VotreMotDePasse";
const char* apiUrl = "https://miniprojet.pixe71.dev/api.php";

void loop() {
  HTTPClient http;
  
  // 1. Demander s'il y a du travail
  http.begin(apiUrl);
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String payload = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, payload);
    
    if (doc["job"] == true) {
      int userId = doc["id"];
      String userName = doc["nom"];
      
      Serial.println("Nouveau badge à programmer pour : " + userName);
      
      // 2. Attendre scan du badge
      String uid = scanRFID();
      
      // 3. Envoyer l'UID à l'API
      http.begin(apiUrl);
      http.addHeader("Content-Type", "application/json");
      
      String postData = "{\"id\":" + String(userId) + ",\"uid\":\"" + uid + "\"}";
      int postCode = http.POST(postData);
      
      if (postCode == 200) {
        Serial.println("Badge programmé avec succès !");
      }
    }
  }
  
  http.end();
  delay(5000);
}
```

---

## 🗄️ Base de Données

### Table : `admins`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | Clé primaire auto-incrémentée |
| `username` | VARCHAR(50) | Identifiant unique |
| `password` | VARCHAR(255) | Hash bcrypt du mot de passe |
| `a2f_token` | VARCHAR(20) | Token 2FA temporaire |
| `a2f_expiration` | DATETIME | Date d'expiration du token |

**Index** :
- PRIMARY KEY (`id`)
- UNIQUE KEY (`username`)

### Table : `users_rfid`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | Clé primaire auto-incrémentée |
| `nom_complet` | VARCHAR(100) | Nom de l'utilisateur |
| `rfid_uid` | VARCHAR(50) | UID du badge RFID |
| `status` | ENUM | État du badge |
| `created_at` | TIMESTAMP | Date de création |

**Valeurs ENUM status** :
- `pending_validation` : Demande web en attente de validation admin
- `en_attente` : Validé par admin, en attente de programmation ESP32
- `actif` : Badge programmé et opérationnel

**Index** :
- PRIMARY KEY (`id`)
- INDEX (`status`)

### Relations

```sql
┌─────────────────┐
│     admins      │
│─────────────────│
│ id              │──┐
│ username        │  │
│ password        │  │ Gère
│ a2f_token       │  │
│ a2f_expiration  │  │
└─────────────────┘  │
                     │
                     ▼
              ┌─────────────────┐
              │   users_rfid    │
              │─────────────────│
              │ id              │
              │ nom_complet     │
              │ rfid_uid        │
              │ status          │
              │ created_at      │
              └─────────────────┘
```

### Requêtes Utiles

```sql
-- Compter les demandes en attente
SELECT COUNT(*) FROM users_rfid WHERE status = 'pending_validation';

-- Lister tous les badges actifs
SELECT nom_complet, rfid_uid, created_at 
FROM users_rfid 
WHERE status = 'actif' 
ORDER BY created_at DESC;

-- Trouver les badges sans UID
SELECT * FROM users_rfid WHERE rfid_uid IS NULL;

-- Statistiques par statut
SELECT status, COUNT(*) as total 
FROM users_rfid 
GROUP BY status;
```

---

## 🔒 Sécurité

### Mécanismes Implémentés

#### 1. Authentification
```php
// Hachage sécurisé (bcrypt)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Vérification
password_verify($input, $hash);
```

#### 2. Protection SQL Injection
```php
// Requêtes préparées PDO
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
```

#### 3. Protection XSS
```php
// Échappement HTML
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

#### 4. Rate Limiting
```php
// Anti-spam personnalisé
if (count($_SESSION['request_attempts']) >= 3) {
    $_SESSION['cooldown_until'] = time() + 120;
}
```

#### 5. Tokens 2FA
```php
// Génération aléatoire
$token = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

// Expiration 2 minutes
$expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
```

### Recommandations Production

✅ **À faire** :
- [ ] Changer les identifiants BDD par défaut
- [ ] Activer HTTPS (Let's Encrypt)
- [ ] Configurer les en-têtes de sécurité
- [ ] Limiter les tentatives de connexion
- [ ] Implémenter des logs d'audit
- [ ] Sauvegardes BDD automatiques
- [ ] Activer le mode strict MySQL

❌ **À ne pas faire** :
- Exposer `db.php` publiquement
- Utiliser `root` en production
- Désactiver les erreurs PHP
- Stocker des mots de passe en clair
- Partager les tokens 2FA

### En-têtes de Sécurité

Ajouter dans `.htaccess` :

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'"
```

---

## 🐛 Dépannage

### Problème : Erreur de connexion BDD

**Symptôme** :
```
Erreur de connexion BDD : SQLSTATE[HY000] [2002]
```

**Solutions** :
```bash
# Vérifier le service MySQL
sudo systemctl status mysql

# Tester la connexion
mysql -h bdd.luc-tournie.fr -u rfid -p

# Vérifier le fichier db.php
cat db.php
```

### Problème : QR Code ne se génère pas

**Symptôme** : Image cassée dans `login_2fa.php`

**Solution** :
```php
// Vérifier l'URL générée
echo $qr_url;

// Tester manuellement
https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=TEST123
```

### Problème : Session expirée immédiatement

**Symptôme** : Redirection en boucle vers `login.php`

**Solution** :
```php
// Vérifier la configuration PHP
ini_set('session.gc_maxlifetime', 3600);

// Augmenter le temps d'expiration
session_set_cookie_params(3600);
```

### Problème : API ne répond pas à l'ESP32

**Symptôme** : Timeout ou erreur 500

**Solutions** :
```php
// Activer les erreurs dans api.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier les logs Apache
tail -f /var/log/apache2/error.log

// Tester l'API manuellement
curl https://miniprojet.pixe71.dev/api.php
```

### Problème : Rate limiting bloque tout

**Symptôme** : Message "Trop de requêtes" permanent

**Solution** :
```php
// Réinitialiser manuellement (à ajouter temporairement)
unset($_SESSION['request_attempts']);
unset($_SESSION['cooldown_until']);
```

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Suivez ces étapes :

### 1. Fork & Clone
```bash
git clone https://github.com/VOTRE-USERNAME/Mini-Projet-qr-auth.git
cd Mini-Projet-qr-auth
```

### 2. Créer une Branche
```bash
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### 3. Commit
```bash
git add .
git commit -m "✨ Ajout fonctionnalité X"
```

### 4. Push & Pull Request
```bash
git push origin feature/ma-nouvelle-fonctionnalite
```

### Conventions de Commit

```
✨ feat: Nouvelle fonctionnalité
🐛 fix: Correction de bug
📚 docs: Documentation
🎨 style: Formatage, style
♻️ refactor: Refactorisation
⚡ perf: Performance
✅ test: Tests
🔧 chore: Maintenance
```

---

## 📝 Changelog

### Version 1.0.0 (2025-01-XX)
- ✨ Interface publique de demande RFID
- ✨ Authentification 2FA par QR code
- ✨ Dashboard administrateur complet
- ✨ API REST pour ESP32
- ✨ Rate limiting anti-spam
- ✨ Export CSV des données
- ✨ Design glassmorphism responsive

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

```
MIT License

Copyright (c) 2025 Pixe71

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files...
```

---

## 👤 Auteur

**Pixe71**
- GitHub: [@pixe71](https://github.com/pixe71)
- Website: [https://miniprojet.pixe71.dev/](https://miniprojet.pixe71.dev/)

---

## 🙏 Remerciements

- [Font Awesome](https://fontawesome.com/) - Icônes
- [Google Fonts](https://fonts.google.com/) - Police Outfit
- [QR Server API](https://goqr.me/api/) - Génération QR codes
- [Glassmorphism](https://hype4.academy/tools/glassmorphism-generator) - Inspiration design

---

## 📞 Support

Pour toute question ou problème :

1. 🐛 [Ouvrir une Issue](https://github.com/pixe71/Mini-Projet-qr-auth/issues)
2. 💬 [Discussion GitHub](https://github.com/pixe71/Mini-Projet-qr-auth/discussions)
3. 📧 Email: support@pixe71.dev

---

<div align="center">

**⭐ Si ce projet vous a été utile, n'hésitez pas à lui donner une étoile ! ⭐**

Made with ❤️ by Pixe71

</div>
