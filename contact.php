<?php
$host = "localhost";
$dbname = "gourmet"; 
$username = "root";
$password = "";
session_start();

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    // Log error instead of showing it to users
    error_log("Database connection failed: " . $e->getMessage());
    $connectionError = true;
}

$formSubmitted = false;
$success = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    
    // Validation with better error handling
    $name = trim(filter_input(INPUT_POST, "name", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $message = trim(filter_input(INPUT_POST, "message", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    // Enhanced validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez fournir une adresse email valide.";
    } elseif (strlen($message) < 10) {
        $error = "Votre message doit contenir au moins 10 caractères.";
    } else {
        if (!isset($connectionError)) {
            try {
                $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (:name, :email, :message, NOW())");
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":message", $message);
                $stmt->execute();
                
                // Set success message and clean form data after successful submission
                $success = "Votre message a été envoyé avec succès! Nous vous répondrons dans les plus brefs délais.";
                $name = $email = $message = "";
                
                // Send email notification (optional - would need to configure mail server)
                // mail("your@email.com", "Nouveau message de contact", "Nom: $name\nEmail: $email\nMessage: $message");
                
            } catch (PDOException $e) {
                error_log("Error saving contact message: " . $e->getMessage());
                $error = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer plus tard.";
            }
        } else {
            $error = "Service temporairement indisponible. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contactez Le Gourmet, votre restaurant gastronomique. Réservations, commentaires et suggestions.">
    <title>Le Gourmet - Contact</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/dashboard/">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'BodoniModa-Italic';
            src: url('fonts/BodoniModa-Italic.woff2') format('woff2'),
                 url('fonts/BodoniModa-Italic.woff') format('woff');
            font-weight: normal;
            font-style: italic;
            font-display: swap;
        }
        
        body {
            font-family: 'BodoniModa-Italic', serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        
        .contact-section {
            padding: 100px 0 80px;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        .contact-heading {
            color: #060834;
            margin-bottom: 30px;
            font-size: 3rem;
            font-weight: 500;
            position: relative;
            display: inline-block;
        }
        
        .contact-heading::after {
            content: "";
            position: absolute;
            width: 80px;
            height: 3px;
            background-color: #060834;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .contact-info {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .contact-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 15px;
            width: 250px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .contact-card i {
            font-size: 2rem;
            color: #060834;
            margin-bottom: 15px;
        }
        
        .contact-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #060834;
        }
        
        .contact-card p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .contact-form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #060834;
            box-shadow: 0 0 0 0.25rem rgba(6, 8, 52, 0.25);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .contact-btn {
            position: relative;
            border: none;
            background: transparent;
            padding: 0;
            cursor: pointer;
            outline-offset: 4px;
            transition: filter 250ms;
            user-select: none;
            touch-action: manipulation;
            margin-top: 10px;
        }
        
        .shadow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: rgba(6, 8, 52, 0.25);
            will-change: transform;
            transform: translateY(2px);
            transition: transform 600ms cubic-bezier(.3, .7, .4, 1);
        }
        
        .edge {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: #060834;
        }
        
        .front {
            display: block;
            position: relative;
            padding: 12px 42px;
            border-radius: 12px;
            font-size: 1.1rem;
            color: white;
            background: #060834;
            will-change: transform;
            transform: translateY(-4px);
            transition: transform 600ms cubic-bezier(.3, .7, .4, 1);
        }
        
        .contact-btn:hover {
            filter: brightness(110%);
        }
        
        .contact-btn:hover .front {
            transform: translateY(-6px);
            transition: transform 250ms cubic-bezier(.3, .7, .4, 1.5);
        }
        
        .contact-btn:active .front {
            transform: translateY(-2px);
            transition: transform 34ms;
        }
        
        .contact-btn:hover .shadow {
            transform: translateY(4px);
            transition: transform 250ms cubic-bezier(.3, .7, .4, 1.5);
        }
        
        .contact-btn:active .shadow {
            transform: translateY(1px);
            transition: transform 34ms;
        }
        
        .success-message, .error-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease;
        }
        
        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        
        .map-container {
            margin-top: 60px;
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .contact-info {
                flex-direction: column;
                align-items: center;
            }
            
            .contact-card {
                width: 90%;
                max-width: 300px;
            }
            
            .contact-heading {
                font-size: 2.5rem;
            }
            
            .map-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <section class="contact-section">
        <h2 class="contact-heading">Contactez-Nous</h2>
        
        <div class="contact-info">
            <div class="contact-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Adresse</h3>
                <p>123 Avenue Gastronomique<br>75001 Paris, France</p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-phone-alt"></i>
                <h3>Téléphone</h3>
                <p>+212670251030<br>+212661683004</p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>thraymen031@gmail.com</p>
            </div>
        </div>
        
        <div class="contact-form-container">
            <?php if (isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form class="contact-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <div class="form-group">
                    <input type="text" class="form-control" name="name" placeholder="Votre Nom" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Votre Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <textarea class="form-control" name="message" placeholder="Votre Message" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="contact-btn">
                    <span class="shadow"></span>
                    <span class="edge"></span>
                    <span class="front"><i class="fas fa-paper-plane"></i> Envoyer Message</span>
                </button>
            </form>
        </div>
        
        <!-- <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9916256937595!2d2.2922926156743347!3d48.85837007928746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sTour%20Eiffel!5m2!1s1fr!2sfr" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div> -->
    </section>

    <?php include "footer.php"; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Form validation on client side
            $('.contact-form').on('submit', function(e) {
                let isValid = true;
                const name = $('input[name="name"]').val().trim();
                const email = $('input[name="email"]').val().trim();
                const message = $('textarea[name="message"]').val().trim();
                
                // Remove any existing error messages
                $('.form-error').remove();
                $('.form-control').removeClass('error-border');
                
                if (!name) {
                    $('input[name="name"]').addClass('error-border').after('<div class="form-error" style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">Veuillez saisir votre nom</div>');
                    isValid = false;
                }
                
                if (!email) {
                    $('input[name="email"]').addClass('error-border').after('<div class="form-error" style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">Veuillez saisir votre email</div>');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('input[name="email"]').addClass('error-border').after('<div class="form-error" style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">Veuillez saisir une adresse email valide</div>');
                    isValid = false;
                }
                
                if (!message) {
                    $('textarea[name="message"]').addClass('error-border').after('<div class="form-error" style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">Veuillez saisir votre message</div>');
                    isValid = false;
                } else if (message.length < 10) {
                    $('textarea[name="message"]').addClass('error-border').after('<div class="form-error" style="color: #dc3545; font-size: 0.875rem; margin-top: 5px;">Votre message doit contenir au moins 10 caractères</div>');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Style error borders
            $('<style>.error-border { border-color: #dc3545 !important; }</style>').appendTo('head');
            
            // Smooth scroll for internal links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                
                $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top
                }, 500, 'linear');
            });
        });
    </script>
</body>
</html>