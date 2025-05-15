<?php
include("cnx.php");
session_start();

// Utiliser PHPMailer pour les emails - Ajouter ces lignes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fonction pour envoyer un email via PHPMailer
function envoyerEmail($destinataire, $sujet, $message) {
    // Enregistrer les tentatives d'envoi dans un log pour le débogage
    error_log("Tentative d'envoi d'email à: " . $destinataire);
    
    // Si vous avez installé PHPMailer via Composer
    if (file_exists('vendor/autoload.php')) {
        require 'vendor/autoload.php';
    }
    // Si vous avez téléchargé PHPMailer manuellement
    elseif (file_exists('PHPMailer/src/Exception.php')) {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';
    }
    // Si PHPMailer n'est pas disponible, utiliser la fonction mail() native
    else {
        $headers = "From: reservations@legourmet.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $mailSent = mail($destinataire, $sujet, $message, $headers);
        
        if (!$mailSent) {
            error_log("Échec de l'envoi d'email à $destinataire via mail(): " . (error_get_last() ? error_get_last()['message'] : 'Erreur inconnue'));
            return false;
        }
        
        return true;
    }
    
    try {
        // Créer une nouvelle instance de PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuration du serveur (SMTP) - Décommentez et configurez selon votre environnement
        /*
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'user@example.com'; // SMTP username
        $mail->Password = 'password';    // SMTP password
        $mail->SMTPSecure = 'tls';       // Activer le chiffrement TLS, 'ssl' également possible
        $mail->Port = 587;               // Port TCP pour se connecter
        */
        
        // Si vous n'avez pas de serveur SMTP, vous pouvez utiliser le mode mail()
        $mail->isMail();
        
        // Destinataires
        $mail->setFrom('reservations@legourmet.com', 'Le Gourmet');
        $mail->addAddress($destinataire);
        
        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body = $message;
        $mail->CharSet = 'UTF-8';
        
        // Envoyer l'email
        $mail->send();
        error_log("Email envoyé avec succès à: " . $destinataire);
        return true;
    } catch (Exception $e) {
        error_log("Échec de l'envoi d'email à $destinataire: " . $mail->ErrorInfo);
        return false;
    }
}

// Vérification de disponibilité des tables
function verifierDisponibilite($cnx, $date, $heure, $personnes) {
    // Format de la date et heure
    $dateTime = date('Y-m-d H:i:s', strtotime($date . ' ' . $heure));
    
    // Déterminer le nombre de tables nécessaires (assumant 4 personnes par table)
    $tablesNecessaires = ceil($personnes / 4);
    
    // Vérifier combien de tables sont déjà réservées à cette heure
    $debut = date('Y-m-d H:i:s', strtotime($dateTime . ' -1 hour'));
    $fin = date('Y-m-d H:i:s', strtotime($dateTime . ' +2 hours'));
    
    $stmt = $cnx->prepare("SELECT SUM(CEIL(personnes/4)) as tables_reservees FROM reservations WHERE heure BETWEEN ? AND ?");
    $stmt->bind_param("ss", $debut, $fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Utiliser l'opérateur de coalescence null pour éviter les erreurs si tables_reservees est NULL
    $tablesReservees = $row['tables_reservees'] ?? 0;
    
    // Supposons que le restaurant dispose de 15 tables au total
    $tablesDisponibles = 15 - $tablesReservees;
    
    return [
        'disponible' => $tablesDisponibles >= $tablesNecessaires,
        'tables_necessaires' => $tablesNecessaires,
        'tables_disponibles' => $tablesDisponibles
    ];
}

// Générer un numéro de réservation unique
function genererNumeroReservation() {
    return 'RES-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Variables pour stocker les données du formulaire en cas d'erreur
$formData = [
    'nom' => '',
    'email' => '',
    'telephone' => '',
    'date' => '',
    'heure' => '',
    'personnes' => ''
];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $formData['nom'] = trim($_POST['nom']);
    $formData['email'] = trim($_POST['email']);
    $formData['telephone'] = trim($_POST['telephone']);
    $formData['date'] = $_POST['date'];
    $formData['heure'] = $_POST['heure'];
    $formData['personnes'] = intval($_POST['personnes']);
    
    // Validation des données
    $errors = [];
    
    if (empty($formData['nom'])) {
        $errors[] = "Le nom est requis";
    }
    
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }
    
    if (empty($formData['telephone']) || !preg_match("/^[0-9+\s()-]{8,15}$/", $formData['telephone'])) {
        $errors[] = "Numéro de téléphone invalide";
    }
    
    // Vérifier que la date n'est pas dans le passé
    $dateActuelle = date('Y-m-d');
    if ($formData['date'] < $dateActuelle) {
        $errors[] = "La date ne peut pas être dans le passé";
    }
    
    // Heures d'ouverture (12h - 22h)
    $heureReservation = intval(explode(':', $formData['heure'])[0]);
    if ($heureReservation < 12 || $heureReservation > 21) {
        $errors[] = "Les réservations sont possibles entre 12h et 22h";
    }
    
    if ($formData['personnes'] < 1 || $formData['personnes'] > 20) {
        $errors[] = "Le nombre de personnes doit être entre 1 et 20";
    }
    
    // Vérifier la disponibilité
    if (empty($errors)) {
        $disponibilite = verifierDisponibilite($cnx, $formData['date'], $formData['heure'], $formData['personnes']);
        
        if (!$disponibilite['disponible']) {
            $errors[] = "Désolé, nous n'avons pas assez de tables disponibles à cette heure. Nous avons {$disponibilite['tables_disponibles']} tables disponibles, mais vous avez besoin de {$disponibilite['tables_necessaires']} tables.";
        }
    }
    
    if (empty($errors)) {
        $reservation_time = $formData['date'] . ' ' . $formData['heure'] . ':00';
        $numero_reservation = genererNumeroReservation();
        $statut = "confirmée";
        
        // Convertir le nombre de personnes en entier pour être sûr
        $personnes = intval($formData['personnes']);
        
        $stmt = $cnx->prepare("INSERT INTO reservations (`numero_reservation`, `nom`, `email`, `telephone`, `heure`, `personnes`, `statut`, `date_creation`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $numero_reservation, $formData['nom'], $formData['email'], $formData['telephone'], $reservation_time, $personnes, $statut);
        
        if ($stmt->execute()) {
            // Suppression de la partie QR code
            
            // Préparer les données de confirmation
            $_SESSION['reservation_success'] = true;
            $_SESSION['reservation_data'] = [
                'numero' => $numero_reservation,
                'nom' => $formData['nom'],
                'date' => $formData['date'],
                'heure' => $formData['heure'],
                'personnes' => $formData['personnes'],
                'qr_code' => '' // Champ vide pour le QR code
            ];
            
            // Envoyer un email de confirmation
            $to = $formData['email'];
            $subject = "Confirmation de réservation - Le Gourmet";
            
            $message = "
            <html>
            <head>
                <title>Confirmation de réservation</title>
            </head>
            <body>
                <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
                    <h2 style='color: #1a2a5a;'>Votre réservation est confirmée!</h2>
                    <p>Cher(e) <strong>{$formData['nom']}</strong>,</p>
                    <p>Merci d'avoir choisi Le Gourmet. Votre réservation a été enregistrée avec succès.</p>
                    <div style='background-color: #f8f8f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Numéro de réservation:</strong> $numero_reservation</p>
                        <p><strong>Date:</strong> {$formData['date']}</p>
                        <p><strong>Heure:</strong> {$formData['heure']}</p>
                        <p><strong>Nombre de personnes:</strong> {$formData['personnes']}</p>
                    </div>
                    <p>Veuillez présenter votre numéro de réservation à l'arrivée: <strong>$numero_reservation</strong></p>
                    <p>Nous sommes impatients de vous accueillir!</p>
                    <p>Pour toute modification ou annulation, veuillez nous contacter au +212670251030 ou répondre à cet email.</p>
                    <hr>
                    <p style='font-size: 0.8em; color: #777;'>Le Gourmet - 123 Rue de la Gastronomie, 75001 Paris</p>
                </div>
            </body>
            </html>
            ";
            
            // Utiliser la nouvelle fonction pour envoyer l'email
            $emailEnvoye = envoyerEmail($to, $subject, $message);
            
            // Enregistrer si l'email a été envoyé
            $_SESSION['email_envoye'] = $emailEnvoye;
            
            // Rediriger vers la page de confirmation
            header("Location: confirmation_reservation.php");
            exit();
        } else {
            $errors[] = "Erreur lors de la réservation: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Afficher les erreurs
    if (!empty($errors)) {
        $errorMessage = "<div class='error'><ul>";
        foreach ($errors as $error) {
            $errorMessage .= "<li>$error</li>";
        }
        $errorMessage .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Le Gourmet</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        @font-face {
            font-family: "BodoniModa-Italic";
            src: url("fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf") format("truetype");
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            font-family: 'BodoniModa-Italic', sans-serif !important;
            background: linear-gradient(120deg, #060834, #1a2a5a, #060834);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
            position: relative;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
            z-index: 0;
        }
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        .particle {
            position: absolute;
            background: rgba(128, 0, 32, 0.3);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(128, 0, 32, 0.2);
            animation: float 9s infinite ease-in-out;
        }
        .particle:nth-child(1) { width: 12px; height: 12px; top: 10%; left: 25%; animation-duration: 8s; }
        .particle:nth-child(2) { width: 18px; height: 18px; top: 65%; left: 75%; animation-duration: 10s; }
        .particle:nth-child(3) { width: 15px; height: 15px; top: 35%; left: 45%; animation-duration: 7s; }
        .particle:nth-child(4) { width: 10px; height: 10px; top: 80%; left: 15%; animation-duration: 11s; }
        .particle:nth-child(5) { width: 14px; height: 14px; top: 20%; left: 60%; animation-duration: 9s; }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0.4; }
            50% { transform: translateY(-30px) translateX(10px); opacity: 0.7; }
        }
        .reservation-container {
            position: relative;
            max-width: 550px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 245, 224, 0.3);
            border-radius: 20px;
            backdrop-filter: blur(15px);
            padding: 40px;
            box-shadow: 0 0 20px rgba(6, 8, 52, 0.3);
            z-index: 1;
            margin: 40px auto;
            transition: all 0.3s ease;
        }
        .reservation-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(6, 8, 52, 0.4);
        }
        h2 {
            font-size: 2.2rem;
            color: #fff5e0;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 5px rgba(6, 8, 52, 0.4);
            position: relative;
            padding-bottom: 15px;
        }
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, transparent, #fff5e0, transparent);
        }
        .success {
            color: #e6f7d9;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            background: rgba(40, 167, 69, 0.2);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            animation: fadeIn 0.5s ease-in-out;
        }
        .error {
            color: #f8d7da;
            text-align: left;
            font-weight: bold;
            margin-bottom: 20px;
            background: rgba(220, 53, 69, 0.2);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            animation: fadeIn 0.5s ease-in-out;
        }
        .error ul {
            margin: 10px 0 0 20px;
        }
        .error li {
            margin-bottom: 5px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        .form-col {
            flex: 1;
        }
        .inputbox {
            position: relative;
            margin: 20px 0;
            border-bottom: 2px solid #fff5e0;
        }
        input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1rem;
            padding: 0 35px 0 5px;
            color: #fff;
            font-family: 'BodoniModa-Italic', sans-serif;
            transition: border-color 0.3s ease;
        }
        input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        input:focus {
            border-bottom-color: #fff;
        }
        .inputbox ion-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff5e0;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        .inputbox:hover ion-icon {
            color: #fff;
        }
        label {
            position: absolute;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        input:focus ~ label,
        input:not(:placeholder-shown) ~ label {
            top: -5px;
            font-size: 0.8rem;
            color: #fff5e0;
        }
        .info-text {
            color: #fff5e0;
            font-size: 0.85rem;
            margin-top: 5px;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        button {
            width: 100%;
            height: 50px;
            border-radius: 40px;
            background: linear-gradient(45deg, #060834, #1a2a5a);
            border: 1px solid #fff5e0;
            outline: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff5e0;
            transition: all 0.4s ease;
            box-shadow: 0 4px 12px rgba(6, 8, 52, 0.3);
            text-transform: uppercase;
            margin-top: 20px;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.4s ease;
        }
        button:hover {
            background: linear-gradient(45deg, #1a2a5a, #060834);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(6, 8, 52, 0.5);
        }
        button:hover::before {
            left: 100%;
        }
        footer {
            width: 100%;
            text-align: center;
            padding: 20px;
            color: #fff5e0;
            position: relative;
            z-index: 1;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .hours-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #fff5e0;
            text-align: center;
        }
        .hours-info p {
            margin: 5px 0;
        }
        .hours-info strong {
            color: #fff;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .reservation-container {
                margin: 20px 15px;
                padding: 25px;
            }
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="reservation-container animate__animated animate__fadeIn">
        <h2>Réserver une Table</h2>
        
        <?php if (isset($errorMessage)) echo $errorMessage; ?>

        <div class="hours-info">
            <p><strong>Heures d'ouverture:</strong> Tous les jours de 12h à 23h</p>
            <p><strong>Dernière réservation:</strong> 22h</p>
        </div>

        <form action="" method="POST" id="reservationForm">
            <div class="form-row">
                <div class="form-col">
                    <div class="inputbox">
                        <ion-icon name="person-outline"></ion-icon>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($formData['nom']); ?>" placeholder=" " required>
                        <label>Nom complet</label>
                    </div>
                </div>
                <div class="form-col">
                    <div class="inputbox">
                        <ion-icon name="call-outline"></ion-icon>
                        <input type="tel" name="telephone" value="<?php echo htmlspecialchars($formData['telephone']); ?>" placeholder=" " required>
                        <label>Téléphone</label>
                    </div>
                </div>
            </div>
            
            <div class="inputbox">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" placeholder=" " required>
                <label>Email</label>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="inputbox">
                        <ion-icon name="calendar-outline"></ion-icon>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($formData['date']); ?>" min="<?php echo date('Y-m-d'); ?>" placeholder=" " required>
                        <label>Date</label>
                    </div>
                </div>
                <div class="form-col">
                    <div class="inputbox">
                        <ion-icon name="time-outline"></ion-icon>
                        <input type="time" name="heure" value="<?php echo htmlspecialchars($formData['heure']); ?>" min="12:00" max="22:00" placeholder=" " required>
                        <label>Heure</label>
                    </div>
                    <p class="info-text">Réservations entre 12h et 22h</p>
                </div>
            </div>
            
            <div class="inputbox">
                <ion-icon name="people-outline"></ion-icon>
                <input type="number" name="personnes" min="1" max="20" value="<?php echo $formData['personnes'] ? htmlspecialchars($formData['personnes']) : ''; ?>" placeholder=" " required>
                <label>Nombre de personnes</label>
            </div>
            <p class="info-text">Maximum 20 personnes par réservation. Pour les groupes plus importants, veuillez nous contacter.</p>
            
            <button type="submit">Réserver ma table</button>
        </form>
    </div>

    <?php include("footer.php"); ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reservationForm');
        
        form.addEventListener('submit', function(e) {
            let valid = true;
            const nom = form.querySelector('input[name="nom"]');
            const email = form.querySelector('input[name="email"]');
            const telephone = form.querySelector('input[name="telephone"]');
            const date = form.querySelector('input[name="date"]');
            const heure = form.querySelector('input[name="heure"]');
            const personnes = form.querySelector('input[name="personnes"]');
            
            // Valider que les champs ne sont pas vides
            [nom, email, telephone, date, heure, personnes].forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderBottom = '2px solid #dc3545';
                    valid = false;
                } else {
                    field.style.borderBottom = '2px solid #fff5e0';
                }
            });
            
            // Valider l'email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                email.style.borderBottom = '2px solid #dc3545';
                valid = false;
            }
            
            // Valider le téléphone (chiffres, espaces, +, -, () autorisés)
            const phonePattern = /^[0-9+\s()-]{8,15}$/;
            if (!phonePattern.test(telephone.value)) {
                telephone.style.borderBottom = '2px solid #dc3545';
                valid = false;
            }
            
            // Vérifier que la date n'est pas dans le passé
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = new Date(date.value);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                date.style.borderBottom = '2px solid #dc3545';
                valid = false;
            }
            
            // Vérifier l'heure (entre 12h et 22h)
            const hourValue = parseInt(heure.value.split(':')[0]);
            if (hourValue < 12 || hourValue > 22) {
                heure.style.borderBottom = '2px solid #dc3545';
                valid = false;
            }
            
            // Vérifier le nombre de personnes
            const personnesValue = parseInt(personnes.value);
            if (isNaN(personnesValue) || personnesValue < 1 || personnesValue > 20) {
                personnes.style.borderBottom = '2px solid #dc3545';
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Veuillez corriger les erreurs dans le formulaire.');
            }
        });
    });
    </script>
</body>
</html>