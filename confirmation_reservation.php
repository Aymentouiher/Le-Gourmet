<?php
session_start();

// Vérifier si les données de réservation existent en session
if (!isset($_SESSION['reservation_success']) || !isset($_SESSION['reservation_data'])) {
    // Rediriger vers la page de réservation si aucune donnée n'est disponible
    header("Location: Reservation.php");
    exit();
}

// Récupérer les données de réservation
$reservation = $_SESSION['reservation_data'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Réservation - Le Gourmet</title>
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
        .confirmation-container {
            position: relative;
            max-width: 600px;
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
            text-align: center;
        }
        .confirmation-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(6, 8, 52, 0.4);
        }
        h1 {
            font-size: 2.5rem;
            color: #fff5e0;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 2px 5px rgba(6, 8, 52, 0.4);
        }
        h2 {
            font-size: 1.8rem;
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
        .confirmation-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
            animation: bounceIn 1s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }
        .reservation-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border: 1px solid rgba(255, 245, 224, 0.2);
        }
        .reservation-details p {
            color: #fff5e0;
            font-size: 1.1rem;
            margin: 10px 0;
        }
        .reservation-details strong {
            color: #fff;
            font-weight: bold;
        }
        .reservation-number {
            font-size: 1.3rem;
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 15px;
            border-radius: 10px;
            display: inline-block;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #060834, #1a2a5a);
            color: #fff5e0;
            border: 1px solid #fff5e0;
            border-radius: 40px;
            font-size: 1.1rem;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(6, 8, 52, 0.3);
        }
        .button:hover {
            background: linear-gradient(45deg, #1a2a5a, #060834);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(6, 8, 52, 0.5);
        }
        .tips {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: left;
        }
        .tips h3 {
            color: #fff5e0;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        .tips ul {
            color: #fff5e0;
            margin-left: 20px;
        }
        .tips li {
            margin-bottom: 8px;
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
        
        /* Responsive design */
        @media (max-width: 768px) {
            .confirmation-container {
                margin: 20px 15px;
                padding: 25px;
            }
            h1 {
                font-size: 2rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .confirmation-icon {
                font-size: 60px;
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

    <div class="confirmation-container animate__animated animate__fadeIn">
        <div class="confirmation-icon">
            <ion-icon name="checkmark-circle"></ion-icon>
        </div>
        
        <h1>Réservation Confirmée</h1>
        <h2>Merci pour votre réservation!</h2>
        
        <p style="color: #fff5e0; font-size: 1.1rem;">
            Cher(e) <strong><?php echo htmlspecialchars($reservation['nom']); ?></strong>, votre table au restaurant Le Gourmet a été réservée avec succès.
        </p>
        
        <div class="reservation-details">
            <p><strong>Numéro de réservation:</strong></p>
            <div class="reservation-number"><?php echo htmlspecialchars($reservation['numero']); ?></div>
            
            <p><strong>Date:</strong> <?php echo htmlspecialchars($reservation['date']); ?></p>
            <p><strong>Heure:</strong> <?php echo htmlspecialchars($reservation['heure']); ?></p>
            <p><strong>Nombre de personnes:</strong> <?php echo htmlspecialchars($reservation['personnes']); ?></p>
        </div>
        
        <p style="color: #fff5e0; font-size: 1.1rem;">
            Un e-mail de confirmation a été envoyé à votre adresse. Veuillez présenter votre numéro de réservation lors de votre arrivée.
        </p>
        
        <div class="tips">
            <h3>Conseils pour votre visite</h3>
            <ul>
                <li>Arrivez environ 5-10 minutes avant l'heure de votre réservation.</li>
                <li>Informez-nous à l'avance si vous avez des exigences alimentaires particulières.</li>
                <li>Si vous ne pouvez pas honorer votre réservation, veuillez nous en informer au moins 2 heures à l'avance.</li>
                <li>Pour toute modification, contactez-nous par téléphone au +212670251030</li>
            </ul>
        </div>
        
        <a href="index.php" class="button">Retour à l'accueil</a>
    </div>

    <?php include("footer.php"); ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Effacer les données de réservation après un certain temps
        // pour éviter que l'utilisateur ne revienne à cette page plus tard
        setTimeout(function() {
            <?php
            // Commenter cette ligne pour les tests si nécessaire
            echo "if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }";
            ?>
        }, 30000); // 30 secondes
    });
    </script>
</body>
</html>

<?php
// Optionnel: Nettoyer les données de session après affichage
// Décommentez si vous voulez que l'utilisateur ne puisse pas voir à nouveau cette page
// en actualisant son navigateur
// unset($_SESSION['reservation_success']);
// unset($_SESSION['reservation_data']);
?>