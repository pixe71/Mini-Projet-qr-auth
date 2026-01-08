<?php
session_start();
require 'db.php';

// --- SÉCURIssssTÉ ---
if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

// --- DÉSACTIVATION AUTOMATtttIQUE DES BADGES EXPIRÉS ---
// Désactiver les badges dont la date de réservation est passée
$pdo->exec("
    UPDATE users_rfid u
    INNER JOIN reservations r ON u.numero_reservation = r.numero_reservation
    SET u.status = 'expiré'
    WHERE r.date_reservation < CURDATE() 
    AND u.status != 'expiré'
");

// --- LOGIQUE ACTIONS ---

// 1. Validation / Suppression
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Passe de "pending_validation" (Web) à "en_attente" (ESP32)
        $pdo->prepare("UPDATE users_rfid SET status = 'en_attente' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users_rfid WHERE id = ?")->execute([$id]);
    } elseif ($action === 'reset') {
        // Remet un badge actif en mode "en_attente" pour le reprogrammer
        $pdo->prepare("UPDATE users_rfid SET status = 'en_attente', rfid_uid = NULL WHERE id = ?")->execute([$id]);
    } elseif ($action === 'delete_reservation') {
        // Supprimer une réservation (libère le créneau)
        $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
    }
    header("Location: dashboard.php"); exit();
}

// 2. Ajout Manuel Rapide
if (isset($_POST['quick_add'])) {
    $nom = htmlspecialchars($_POST['nom']);
    if (!empty($nom)) {
        $pdo->prepare("INSERT INTO users_rfid (nom_complet, status) VALUES (?, 'en_attente')")->execute([$nom]);
        header("Location: dashboard.php"); exit();
    }
}

// 3. Export CSV
if (isset($_GET['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="badges_rfid.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Nom', 'UID', 'Statut', 'Date Création'));
    $rows = $pdo->query("SELECT id, nom_complet, rfid_uid, status, created_at FROM users_rfid");
    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) fputcsv($output, $row);
    exit();
}

// --- STATISTIQUES ---
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users_rfid")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM users_rfid WHERE status='actif'")->fetchColumn(),
    'web_pending' => $pdo->query("SELECT COUNT(*) FROM users_rfid WHERE status='pending_validation'")->fetchColumn(),
    'esp_queue' => $pdo->query("SELECT COUNT(*) FROM users_rfid WHERE status='en_attente'")->fetchColumn(),
    'reservations_total' => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
    'reservations_today' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE date_reservation = CURDATE()")->fetchColumn()
];

// --- RÉCUPÉRATION DES LISTES ---
$web_requests = $pdo->query("SELECT * FROM users_rfid WHERE status = 'pending_validation' ORDER BY created_at DESC");
$active_users = $pdo->query("SELECT * FROM users_rfid WHERE status != 'pending_validation' ORDER BY status DESC, id DESC LIMIT 50");
$reservations = $pdo->query("SELECT * FROM reservations ORDER BY date_reservation DESC, heure_debut DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supervision RFID</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: 1px solid rgba(255, 255, 255, 0.15);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            --accent: #00d2ff;
            --danger: #ff4b4b;
            --success: #2ecc71;
            --warning: #f1c40f;
        }

        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            color: white; min-height: 100vh;
            padding: 30px;
        }

        /* UTILITIES */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
        }

        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .text-dim { color: rgba(255,255,255,0.5); font-size: 0.9em; }
        
        /* HEADER */
        header { margin-bottom: 40px; display: flex; align-items: center; gap: 20px; }
        header h1 { margin: 0; font-weight: 600; background: -webkit-linear-gradient(0deg, #00d2ff, #3a7bd5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .btn-action {
            padding: 10px 20px; border-radius: 12px; border: none; cursor: pointer;
            font-weight: 600; text-decoration: none; color: white; transition: 0.3s;
            display: inline-flex; align-items: center; gap: 8px; font-size: 0.9em;
        }
        .btn-primary { background: linear-gradient(90deg, #3a7bd5 0%, #00d2ff 100%); box-shadow: 0 4px 15px rgba(0, 210, 255, 0.3); }
        .btn-logout { background: rgba(255, 75, 75, 0.2); color: #ff6b6b; border: 1px solid rgba(255, 75, 75, 0.3); }
        .btn-logout:hover { background: rgba(255, 75, 75, 0.4); }

        /* KPI CARDS GRID */
        .kpi-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;
        }
        .kpi-card { padding: 25px; display: flex; align-items: center; gap: 20px; transition: transform 0.2s; }
        .kpi-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.12); }
        .kpi-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .kpi-val { font-size: 2rem; font-weight: 700; margin: 0; line-height: 1; }
        .kpi-label { margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.7; }

        /* DASHBOARD CONTENT GRID */
        .main-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 25px;
        }
        @media (max-width: 900px) { .main-grid { grid-template-columns: 1fr; } }

        .panel-header {
            padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; justify-content: space-between; align-items: center;
        }
        .panel-header h3 { margin: 0; font-weight: 500; display: flex; align-items: center; gap: 10px; }

        /* TABLES */
        .table-container { padding: 10px; max-height: 400px; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: rgba(255,255,255,0.4); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        
        /* STATUS PILLS */
        .status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .st-active { background: rgba(46, 213, 115, 0.2); color: #2ed573; box-shadow: 0 0 10px rgba(46, 213, 115, 0.2); }
        .st-wait { background: rgba(255, 165, 2, 0.2); color: #ffa502; animation: pulse 2s infinite; }
        .st-web { background: rgba(0, 210, 255, 0.2); color: #00d2ff; }
        
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        /* ACTIONS BUTTONS IN TABLE */
        .icon-btn { 
            width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; 
            display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; color: white;
            text-decoration: none; margin-left: 5px;
        }
        .btn-ok { background: rgba(46, 213, 115, 0.2); color: #2ed573; } .btn-ok:hover { background: #2ed573; color: white; }
        .btn-del { background: rgba(255, 75, 75, 0.2); color: #ff6b6b; } .btn-del:hover { background: #ff6b6b; color: white; }
        .btn-reload { background: rgba(255, 255, 255, 0.1); color: #fff; } .btn-reload:hover { background: rgba(255, 255, 255, 0.3); }

        /* QUICK ADD FORM */
        .quick-add { padding: 20px; }
        .input-glass {
            width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: white; outline: none; margin-bottom: 10px; box-sizing: border-box;
            font-family: inherit; transition: 0.3s;
        }
        .input-glass:focus { border-color: var(--accent); background: rgba(0,0,0,0.4); }

    </style>
</head>
<body>

    <header class="glass" style="padding: 15px 30px; border-radius: 15px;">
        <div style="flex-grow:1;">
            <h1><i class="fa-solid fa-layer-group"></i>Gestion RFID</h1>
            <span class="text-dim">Panneau Administrateur Sécurisé</span>
        </div>
        <div style="display:flex; gap:15px;">
            <a href="?export_csv=1" class="btn-action" style="background: rgba(255,255,255,0.1);"><i class="fa-solid fa-download"></i> CSV</a>
            <a href="logout.php" class="btn-action btn-logout"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </header>

    <div class="kpi-grid">
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(0, 210, 255, 0.2); color:#00d2ff;"><i class="fa-solid fa-globe"></i></div>
            <div><p class="kpi-val"><?php echo $stats['web_pending']; ?></p><p class="kpi-label">Demandes Web</p></div>
        </div>
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(241, 196, 15, 0.2); color:#f1c40f;"><i class="fa-solid fa-microchip"></i></div>
            <div><p class="kpi-val"><?php echo $stats['esp_queue']; ?></p><p class="kpi-label">En attente ESP32</p></div>
        </div>
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(46, 213, 115, 0.2); color:#2ed573;"><i class="fa-solid fa-id-badge"></i></div>
            <div><p class="kpi-val"><?php echo $stats['active']; ?></p><p class="kpi-label">Badges Actifs</p></div>
        </div>
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(255, 255, 255, 0.1); color:#fff;"><i class="fa-solid fa-users"></i></div>
            <div><p class="kpi-val"><?php echo $stats['total']; ?></p><p class="kpi-label">Total Utilisateurs</p></div>
        </div>
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(155, 89, 182, 0.2); color:#9b59b6;"><i class="fa-solid fa-calendar-check"></i></div>
            <div><p class="kpi-val"><?php echo $stats['reservations_total']; ?></p><p class="kpi-label">Réservations Totales</p></div>
        </div>
        <div class="glass kpi-card">
            <div class="kpi-icon" style="background: rgba(52, 152, 219, 0.2); color:#3498db;"><i class="fa-solid fa-calendar-day"></i></div>
            <div><p class="kpi-val"><?php echo $stats['reservations_today']; ?></p><p class="kpi-label">Aujourd'hui</p></div>
        </div>
    </div>

    <div class="main-grid">
        
        <div style="display:flex; flex-direction:column; gap:25px;">
            
            <?php if($stats['web_pending'] > 0): ?>
            <div class="glass" style="border: 1px solid rgba(0, 210, 255, 0.5); box-shadow: 0 0 20px rgba(0, 210, 255, 0.1);">
                <div class="panel-header">
                    <h3 style="color:#00d2ff;"><i class="fa-solid fa-bell"></i> Demandes à valider</h3>
                    <span class="status st-web"><?php echo $stats['web_pending']; ?> Requêtes</span>
                </div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Demandeur</th><th>Date</th><th style="text-align:right;">Décision</th></tr></thead>
                        <tbody>
                            <?php while($req = $web_requests->fetch()): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($req['nom_complet']); ?></td>
                                <td class="text-dim">
                                    <?php echo date('d/m H:i', strtotime($req['created_at'])); ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="?action=approve&id=<?php echo $req['id']; ?>" class="icon-btn btn-ok" title="Valider"><i class="fa-solid fa-check"></i></a>
                                    <a href="?action=delete&id=<?php echo $req['id']; ?>" class="icon-btn btn-del" onclick="return confirm('Refuser ?')" title="Refuser"><i class="fa-solid fa-xmark"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="glass">
                <div class="panel-header">
                    <h3><i class="fa-solid fa-list"></i> Gestion des Badges</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>N° Réservation</th><th>Nom</th><th>UID Badge</th><th>Statut</th><th style="text-align:right;">Actions</th></tr></thead>
                        <tbody>
                            <?php while($u = $active_users->fetch()): ?>
                            <tr>
                                <td>
                                    <?php if($u['numero_reservation']): ?>
                                        <code style="background: rgba(0, 210, 255, 0.2); color: #00d2ff; padding: 5px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">
                                            <?php echo htmlspecialchars($u['numero_reservation']); ?>
                                        </code>
                                    <?php else: ?>
                                        <span class="text-dim">---</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
                                <td>
                                    <?php if($u['rfid_uid']): ?>
                                        <code style="background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px; font-family:'Courier New'"><?php echo $u['rfid_uid']; ?></code>
                                    <?php else: ?>
                                        <span class="text-dim">---</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['status']=='en_attente'): ?>
                                        <span class="status st-wait"><i class="fa-solid fa-spinner fa-spin"></i> Sync ESP32...</span>
                                    <?php elseif($u['status']=='actif'): ?>
                                        <span class="status st-active">ACTIF</span>
                                    <?php else: ?>
                                        <span class="status" style="background: rgba(128, 128, 128, 0.2); color: #888;">EXPIRÉ</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <?php if($u['status']=='actif'): ?>
                                        <a href="?action=reset&id=<?php echo $u['id']; ?>" class="icon-btn btn-reload" title="Reprogrammer"><i class="fa-solid fa-rotate-right"></i></a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $u['id']; ?>" class="icon-btn btn-del" onclick="return confirm('Supprimer définitivement ?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:25px;">
            
            <!--<div class="glass">
                <div class="panel-header">
                    <h3><i class="fa-solid fa-bolt"></i> Ajout Rapide</h3>
                </div>
                <div class="quick-add">
                    <form method="post">
                        <p class="text-dim" style="margin-top:0;">Force l'ajout d'un utilisateur dans la file d'attente de l'ESP32 sans passer par la validation.</p>
                        <input type="text" name="nom" class="input-glass" placeholder="Nom Prénom" required autocomplete="off">
                        <button type="submit" name="quick_add" class="btn-action btn-primary" style="width:100%; justify-content:center;">
                            Ajouter à la file
                        </button>
                    </form>
                </div>
            </div>-->

            <div class="glass" style="padding:20px;">
                <h4 style="margin-top:0; color:rgba(255,255,255,0.5);">ÉTAT SYSTÈME</h4>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <div style="width:10px; height:10px; background:#2ed573; border-radius:50%; box-shadow:0 0 10px #2ed573;"></div>
                    <span>Base de données: <strong>Connectée</strong></span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <?php if($stats['esp_queue'] > 0): ?>
                        <div style="width:10px; height:10px; background:#f1c40f; border-radius:50%; animation:pulse 1s infinite;"></div>
                        <span>ESP32: <strong>En attente de scan...</strong></span>
                    <?php else: ?>
                        <div style="width:10px; height:10px; background:#2ed573; border-radius:50%;"></div>
                        <span>ESP32: <strong>Veille</strong></span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- SECTION RÉSERVATIONS -->
    <div class="glass" style="margin-top: 30px; padding: 30px;">
        <div class="panel-header" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px;">
            <h3><i class="fa-solid fa-calendar-days"></i> Gestion des Réservations</h3>
            <span class="text-dim"><?php echo $stats['reservations_total']; ?> réservation(s) au total</span>
        </div>
        
        <div style="padding: 20px 10px 10px 10px;">
            <input type="text" 
                   id="searchReservation" 
                   class="input-glass" 
                   placeholder="Rechercher par n° réservation, nom, date ou motif..." 
                   style="margin-bottom: 0;"
                   oninput="filterReservations()">
        </div>
        
        <div class="table-container" style="max-height: 500px;">
            <table>
                <thead>
                    <tr>
                        <th>N° Réservation</th>
                        <th>Utilisateur</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Motif</th>
                        <th>Créée le</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <?php while($res = $reservations->fetch()): ?>
                    <tr class="reservation-row"
                        data-numero="<?php echo htmlspecialchars($res['numero_reservation']); ?>"
                        data-nom="<?php echo htmlspecialchars($res['nom_complet']); ?>"
                        data-date="<?php echo date('d/m/Y', strtotime($res['date_reservation'])); ?>"
                        data-motif="<?php echo htmlspecialchars($res['motif']); ?>">
                        <td>
                            <code style="background: rgba(0, 210, 255, 0.2); color: #00d2ff; padding: 5px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($res['numero_reservation']); ?>
                            </code>
                        </td>
                        <td style="font-weight:600;">
                            <i class="fa-solid fa-user" style="margin-right: 5px; opacity: 0.5;"></i>
                            <?php echo htmlspecialchars($res['nom_complet']); ?>
                        </td>
                        <td>
                            <span style="background: rgba(155, 89, 182, 0.2); color: #9b59b6; padding: 5px 10px; border-radius: 8px; font-weight: 600;">
                                <?php echo date('d/m/Y', strtotime($res['date_reservation'])); ?>
                            </span>
                        </td>
                        <td>
                            <i class="fa-solid fa-clock" style="margin-right: 5px; opacity: 0.5;"></i>
                            <?php echo substr($res['heure_debut'], 0, 5); ?> - <?php echo substr($res['heure_fin'], 0, 5); ?>
                        </td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($res['motif']); ?>
                        </td>
                        <td class="text-dim">
                            <?php echo date('d/m H:i', strtotime($res['created_at'])); ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="?action=delete_reservation&id=<?php echo $res['id']; ?>" 
                               class="icon-btn btn-del" 
                               onclick="return confirm('Supprimer cette réservation ? Le créneau redeviendra disponible.')" 
                               title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterReservations() {
            const searchValue = document.getElementById('searchReservation').value.toLowerCase();
            const rows = document.querySelectorAll('.reservation-row');
            
            rows.forEach(row => {
                const numero = row.getAttribute('data-numero').toLowerCase();
                const nom = row.getAttribute('data-nom').toLowerCase();
                const date = row.getAttribute('data-date').toLowerCase();
                const motif = row.getAttribute('data-motif').toLowerCase();
                
                const match = numero.includes(searchValue) || 
                             nom.includes(searchValue) || 
                             date.includes(searchValue) || 
                             motif.includes(searchValue);
                
                row.style.display = match ? '' : 'none';
            });
        }
    </script>

</body>
</html>
