<?php
session_start();
require 'db.php';

// Vérifier authentification
if (!isset($_SESSION['user_logged'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom_complet'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Calendrier</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: 1px solid rgba(255, 255, 255, 0.15);
            --accent: #00d2ff;
            --danger: #ff4b4b;
            --success: #2ecc71;
        }

        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(0, 0, 0) 0%, rgb(24, 24, 35) 90.2%);
            color: white; min-height: 100vh;
            padding: 30px;
        }

        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        h1 {
            margin: 0;
            font-weight: 600;
            background: -webkit-linear-gradient(0deg, #00d2ff, #3a7bd5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-logout {
            padding: 10px 20px;
            border-radius: 12px;
            background: rgba(255, 75, 75, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255, 75, 75, 0.3);
            text-decoration: none;
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-logout:hover {
            background: rgba(255, 75, 75, 0.4);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        @media (max-width: 900px) {
            .calendar-grid {
                grid-template-columns: 1fr;
            }
        }

        .calendar-container {
            padding: 30px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .calendar-header button {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .calendar-header button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }

        .calendar-day.header {
            background: transparent;
            cursor: default;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .calendar-day.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .calendar-day:not(.header):not(.disabled):hover {
            background: rgba(0, 210, 255, 0.2);
            transform: scale(1.05);
        }

        .calendar-day.selected {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            box-shadow: 0 5px 15px rgba(0, 210, 255, 0.3);
        }

        .booking-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            background: rgba(0, 0, 0, 0.4);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .time-slot {
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: 0.3s;
        }

        .time-slot:hover {
            background: rgba(0, 210, 255, 0.1);
        }

        .time-slot.selected {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            border-color: var(--accent);
            position: relative;
        }

        .time-slot.selected::after {
            content: '✓';
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .time-slot.booked {
            opacity: 0.3;
            cursor: not-allowed;
            background: rgba(255, 75, 75, 0.1);
            border: 1px solid rgba(255, 75, 75, 0.2);
        }

        .time-slot.booked:hover {
            background: rgba(255, 75, 75, 0.1);
            transform: none;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #3a7bd5, #00d2ff);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 210, 255, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.15);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }

        .alert-error {
            background: rgba(255, 75, 75, 0.15);
            border: 1px solid rgba(255, 75, 75, 0.3);
            color: #ff6b6b;
        }

        .mes-reservations {
            padding: 30px;
            margin-top: 30px;
        }

        .reservation-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .reservation-card h4 {
            margin: 0 0 10px 0;
            color: var(--accent);
        }

        .reservation-card p {
            margin: 5px 0;
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body>

    <header class="glass">
        <div>
            <h1><i class="fa-solid fa-calendar-days"></i> Réservation de Créneaux</h1>
            <span style="color: rgba(255,255,255,0.5);">Bienvenue, <?php echo htmlspecialchars($user_nom); ?></span>
        </div>
        <div class="user-info">
            <a href="logout.php" class="btn-logout">
                <i class="fa-solid fa-power-off"></i> Déconnexion
            </a>
        </div>
    </header>

    <div class="calendar-grid">
        <div class="glass calendar-container">
            <h3><i class="fa-solid fa-calendar"></i> Sélectionner une Date</h3>
            <div class="calendar-header">
                <button id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
                <h3 id="currentMonth">Décembre 2025</h3>
                <button id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="calendar" id="calendar"></div>
        </div>

        <div class="glass booking-form">
            <h3><i class="fa-solid fa-clock"></i> Détails de la Réservation</h3>
            <div id="alert-container"></div>
            
            <form id="bookingForm">
                <div class="form-group">
                    <label>Date sélectionnée</label>
                    <input type="text" id="selectedDate" readonly placeholder="Sélectionnez une date">
                </div>

                <div class="form-group">
                    <label>Créneaux horaires <small style="opacity: 0.7;">(Vous pouvez sélectionner plusieurs créneaux)</small></label>
                    <div class="time-slots" id="timeSlots"></div>
                    <input type="hidden" id="selectedTime" name="selected_time">
                </div>

                <div class="form-group">
                    <label>Motif de la réservation</label>
                    <textarea id="motif" name="motif" placeholder="Décrivez le motif de votre réservation..." required></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    <i class="fa-solid fa-check"></i> Confirmer la Réservation
                </button>
            </form>
        </div>
    </div>

    <div class="glass mes-reservations">
        <h3><i class="fa-solid fa-list"></i> Mes Réservations</h3>
        <div id="mesReservations"></div>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;
        let selectedTimes = []; // Tableau pour stocker plusieurs créneaux
        let bookedSlots = {};

        const timeSlots = [
            { start: '08:00', end: '09:00' },
            { start: '09:00', end: '10:00' },
            { start: '10:00', end: '11:00' },
            { start: '11:00', end: '12:00' },
            { start: '14:00', end: '15:00' },
            { start: '15:00', end: '16:00' },
            { start: '16:00', end: '17:00' },
            { start: '17:00', end: '18:00' },
        ];

        // Charger les créneaux réservés
        async function loadBookedSlots() {
            const response = await fetch('api_reservations.php?action=get_booked');
            bookedSlots = await response.json();
            renderCalendar();
            renderTimeSlots();
        }

        // Charger mes réservations
        async function loadMyReservations() {
            const response = await fetch('api_reservations.php?action=my_reservations');
            const reservations = await response.json();
            
            const container = document.getElementById('mesReservations');
            if (reservations.length === 0) {
                container.innerHTML = '<p style="color: rgba(255,255,255,0.5);">Aucune réservation pour le moment.</p>';
            } else {
                container.innerHTML = reservations.map(r => `
                    <div class="reservation-card">
                        <h4>${r.date_reservation} • ${r.heure_debut} - ${r.heure_fin}</h4>
                        <p><strong>N° ${r.numero_reservation}</strong></p>
                        <p><i class="fa-solid fa-file-lines"></i> ${r.motif}</p>
                        <p style="font-size: 0.85rem; opacity: 0.6;">Créée le ${new Date(r.created_at).toLocaleDateString('fr-FR')}</p>
                    </div>
                `).join('');
            }
        }

        function renderCalendar() {
            const calendar = document.getElementById('calendar');
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                              'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let html = '';
            ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'].forEach(day => {
                html += `<div class="calendar-day header">${day}</div>`;
            });

            for (let i = 0; i < firstDay; i++) {
                html += `<div class="calendar-day disabled"></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const isPast = date < today;
                const isSelected = selectedDate && 
                    selectedDate.getDate() === day && 
                    selectedDate.getMonth() === month &&
                    selectedDate.getFullYear() === year;

                const classes = ['calendar-day'];
                if (isPast) classes.push('disabled');
                if (isSelected) classes.push('selected');

                html += `<div class="${classes.join(' ')}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}" onclick="selectDate(this, ${year}, ${month}, ${day})">${day}</div>`;
            }

            calendar.innerHTML = html;
        }

        function selectDate(element, year, month, day) {
            if (element.classList.contains('disabled')) return;
            
            selectedDate = new Date(year, month, day);
            document.getElementById('selectedDate').value = selectedDate.toLocaleDateString('fr-FR');
            
            renderCalendar();
            renderTimeSlots();
            selectedTimes = []; // Réinitialiser les créneaux sélectionnés
            checkFormValidity();
        }

        function renderTimeSlots() {
            const container = document.getElementById('timeSlots');
            if (!selectedDate) {
                container.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: rgba(255,255,255,0.5);">Sélectionnez une date</p>';
                return;
            }

            const dateKey = selectedDate.toISOString().split('T')[0];
            const booked = bookedSlots[dateKey] || [];

            container.innerHTML = timeSlots.map(slot => {
                const isBooked = booked.includes(slot.start);
                const slotKey = `${slot.start}|${slot.end}`;
                const isSelected = selectedTimes.includes(slotKey);
                
                return `
                    <div class="time-slot ${isBooked ? 'booked' : ''} ${isSelected ? 'selected' : ''}" 
                         onclick="selectTime('${slot.start}', '${slot.end}', ${isBooked})">
                        ${slot.start} - ${slot.end}
                        ${isBooked ? '<br><small>Réservé</small>' : ''}
                    </div>
                `;
            }).join('');
        }

        function selectTime(start, end, isBooked) {
            if (isBooked) return; // Bloquer la sélection des créneaux réservés
            
            const slotKey = `${start}|${end}`;
            
            // Toggle: ajouter ou retirer le créneau
            const index = selectedTimes.indexOf(slotKey);
            if (index > -1) {
                selectedTimes.splice(index, 1); // Retirer
            } else {
                selectedTimes.push(slotKey); // Ajouter
            }
            
            // Trier les créneaux par heure de début
            selectedTimes.sort();
            
            // Mettre à jour le champ caché avec tous les créneaux séparés par des virgules
            document.getElementById('selectedTime').value = selectedTimes.join(',');
            
            renderTimeSlots();
            checkFormValidity();
        }

        function checkFormValidity() {
            const btn = document.getElementById('submitBtn');
            const motif = document.getElementById('motif').value.trim();
            btn.disabled = !(selectedDate && selectedTimes.length > 0 && motif.length > 0);
        }

        document.getElementById('motif').addEventListener('input', checkFormValidity);

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        document.getElementById('bookingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const alertContainer = document.getElementById('alert-container');
            const motif = document.getElementById('motif').value;
            const dateStr = selectedDate.toISOString().split('T')[0];
            
            if (selectedTimes.length === 0) {
                alertContainer.innerHTML = `
                    <div class="alert alert-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span>Veuillez sélectionner au moins un créneau</span>
                    </div>
                `;
                return;
            }
            
            // Trier les créneaux par heure de début
            selectedTimes.sort();
            
            // Extraire la première heure de début et la dernière heure de fin
            const firstSlot = selectedTimes[0].split('|');
            const lastSlot = selectedTimes[selectedTimes.length - 1].split('|');
            const heureDebut = firstSlot[0];
            const heureFin = lastSlot[1];
            
            // Créer UNE SEULE réservation pour tous les créneaux sélectionnés
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('date', dateStr);
            formData.append('heure_debut', heureDebut);
            formData.append('heure_fin', heureFin);
            formData.append('motif', motif);

            try {
                const response = await fetch('api_reservations.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Réservation confirmée de ${heureDebut} à ${heureFin} ! N° ${result.numero_reservation}</span>
                        </div>
                    `;
                    document.getElementById('motif').value = '';
                    selectedDate = null;
                    selectedTimes = [];
                    document.getElementById('selectedDate').value = '';
                    document.getElementById('selectedTime').value = '';
                    loadBookedSlots();
                    loadMyReservations();
                    checkFormValidity();
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-error">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span>${result.message || 'Erreur lors de la réservation'}</span>
                        </div>
                    `;
                }
            } catch (error) {
                alertContainer.innerHTML = `
                    <div class="alert alert-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span>Erreur de connexion au serveur</span>
                    </div>
                `;
            }

            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 8000);
        });

        // Initialisation
        renderCalendar();
        loadBookedSlots();
        loadMyReservations();
    </script>

</body>
</html>
