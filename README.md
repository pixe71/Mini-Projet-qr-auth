# 🔐 Système de Gestion RFID avec Authentification 2FA

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)

Système de gestion de badges RFID avec interface web sécurisée (2FA), dashboard administrateur et intégration ESP32.

🌐 **Démo** : [miniprojet.pixe71.dev](https://miniprojet.pixe71.dev/)


## 🎯 Vue d'ensemble

Système complet de contrôle d'accès RFID combinant :
- Interface web glassmorphism moderne
- Authentification 2FA par QR code
- API REST pour ESP32
- Dashboard administrateur temps réel

## ✨ Fonctionnalités

**Interface Publique**
- Formulaire de demande RFID avec rate limiting (3 req/30s)
- Protection anti-spam avec cooldown 2 minutes

**Espace Admin**
- Connexion sécurisée 2FA (timer 2 min)
- Dashboard : stats temps réel, gestion badges, export CSV
- KPI : Badges ESP32, actifs, total utilisateurs

## 🏗️ Stack

| Composant | Techno | Version |
|-----------|--------|---------|
| Backend | PHP | 7.4+ |
| BDD | MySQL | 5.7+ |
| Frontend | HTML/CSS/JS | ES6+ |
| Design | Glassmorphism | Custom |
| Icons | Font Awesome | 6.0 |

**Sécurité** : Password hashing, PDO préparé, XSS protection, rate limiting, tokens 2FA

## 📦 Installation

```bash
version: '3.8'

services:
  web-test:
    image: ghcr.io/pixe71/mini-projet-qr-auth:main
    expose:
      - "80"
    environment:
      - DB_HOST=
      - DB_NAME=
      - DB_USER=
      - DB_PASS=

networks:
  cloudflared_cloudflare:
    external: true
---

# Config db.php
$host = 'votre_host';
$dbname = 'rfid';
$user = 'votre_user';
$pass = 'votre_password';

# Permissions
chmod 755 -R .
chmod 644 *.php
```

**Config actuelle (à changer en prod)** :
```
Host: bdd.luc-tournie.fr
User: rfid
Pass: iv4VEvp&1C7vb5X&Pz5o
DB: rfid
```


## 📁 Structure

```
Mini-Projet-qr-auth/
├── index.php         # Formulaire public demande RFID
├── login.php         # Connexion admin
├── login_2fa.php     # Validation QR 2FA
├── dashboard.php     # Interface admin
├── register.php      # Inscription admin
├── db.php            # Config MySQL
├── api.php           # API ESP32
└── BDD.sql           # Schéma tables
```
## 🗄️ Base de Données

**Table `admins`** :
- `id`, `username`, `password` (hash), `a2f_token`, `a2f_expiration`

**Table `users_rfid`** :
- `id`, `nom_complet`, `rfid_uid`, `status`, `created_at`

**Status** :
- `en_attente` : En file ESP32
- `actif` : Badge programmé

```sql
-- Stats par statut
SELECT status, COUNT(*) FROM users_rfid GROUP BY status;
```

## 🔒 Sécurité

**Implémenté** :
- Password hashing (bcrypt)
- PDO requêtes préparées
- XSS protection (htmlspecialchars)
- Rate limiting (3 req/30s)
- Tokens 2FA expirables (2 min)

## 👤 Auteur

**Pixe71** - [@pixe71](https://github.com/pixe71)
**rafael12g** - [@rafael12g](https://github.com/rafael12g)

---

<div align="center">

</div>
