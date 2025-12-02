<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Système de Réservation</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --accent: #00d2ff;
            --accent-hover: #3a7bd5;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: 1px solid rgba(255, 255, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
            overflow: hidden;
        }

        .glow {
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(58, 123, 213, 0.2) 0%, rgba(0,0,0,0) 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 600px;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 50px;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: inherit;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-hover) 0%, var(--accent) 100%);
            color: white;
            box-shadow: 0 8px 30px rgba(0, 210, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0, 210, 255, 0.5);
        }

        .btn-secondary {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: var(--glass-border);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 12px 35px rgba(255,255,255,0.1);
        }

        .features {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .feature {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: var(--glass-border);
            border-radius: 15px;
            padding: 25px;
            transition: 0.3s;
        }

        .feature:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 210, 255, 0.4);
        }

        .feature i {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .feature h3 {
            font-size: 1rem;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .feature p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.5;
        }

        @media (max-width: 600px) {
            h1 { font-size: 2rem; }
            .subtitle { font-size: 1rem; }
            .buttons { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="container">
        <div class="logo">
            <i class="fa-solid fa-calendar-check"></i>
        </div>

        <h1>Système de Réservation</h1>
        <p class="subtitle">
            Réservez facilement vos créneaux horaires avec notre système de gestion intelligent
        </p>

        <div class="buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fa-solid fa-user-plus"></i>
                S'inscrire
            </a>
        </div>

        <div class="features">
            <div class="feature">
                <i class="fa-solid fa-calendar-days"></i>
                <h3>Calendrier interactif</h3>
                <p>Visualisez et réservez vos créneaux en temps réel</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-clock"></i>
                <h3>Disponibilités</h3>
                <p>8 créneaux horaires par jour disponibles</p>
            </div>
            <div class="feature">
                <i class="fa-solid fa-shield-halved"></i>
                <h3>Sécurisé</h3>
                <p>Authentification 2FA pour les administrateurs</p>
            </div>
        </div>
    </div>

</body>
</html>

