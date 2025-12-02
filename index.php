<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            color: white; padding: 20px;
        }
        .container { text-align: center; max-width: 600px; }
        .logo {
            font-size: 4rem; margin-bottom: 20px;
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        h1 { font-size: 2.5rem; margin-bottom: 15px; }
        .subtitle { font-size: 1.1rem; color: rgba(255,255,255,0.6); margin-bottom: 50px; }
        .buttons { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px; padding: 18px 40px;
            border-radius: 15px; font-size: 1.1rem; font-weight: 600;
            text-decoration: none; transition: 0.3s; font-family: inherit;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
            color: white; box-shadow: 0 8px 30px rgba(0, 210, 255, 0.4);
        }
        .btn-primary:hover { transform: translateY(-3px); }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15); color: white;
        }
        .btn-secondary:hover { transform: translateY(-3px); background: rgba(255, 255, 255, 0.12); }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo"><i class="fa-solid fa-calendar-check"></i></div>
        <h1>Système de Réservation</h1>
        <p class="subtitle">Réservez facilement vos créneaux horaires</p>
        <div class="buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Se connecter
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fa-solid fa-user-plus"></i> S'inscrire
            </a>
        </div>
    </div>
</body>
</html>

