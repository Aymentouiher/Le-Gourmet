<?php
// Connexion à la base de données
include("cnx.php");
// Éviter la double connexion
if (!isset($cnx)) {
    $cnx = mysqli_connect('localhost', 'root', '', 'gourmet');
    if (!$cnx) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

session_start();

// Variables pour stocker les messages d'erreur et les valeurs précédentes
$errors = [];
$old_values = [];
$success_message = '';

// Traitement du formulaire
if (isset($_POST['signup'])) {
    // Récupération et nettoyage des données
    $nom = mysqli_real_escape_string($cnx, trim($_POST['nom_complet']));
    $email = mysqli_real_escape_string($cnx, trim($_POST['email']));
    $telephone = mysqli_real_escape_string($cnx, trim($_POST['telephone']));
    $mdp = $_POST['mdp'];
    $mdp_confirm = $_POST['mdp_confirm'];
    
    // Stockage des valeurs pour les réafficher en cas d'erreur
    $old_values['nom_complet'] = $nom;
    $old_values['email'] = $email;
    $old_values['telephone'] = $telephone;
    
    // Validation: Nom complet
    if (empty($nom)) {
        $errors['nom_complet'] = "Le nom complet est requis";
    } elseif (strlen($nom) < 3 || strlen($nom) > 50) {
        $errors['nom_complet'] = "Le nom doit contenir entre 3 et 50 caractères";
    }
    
    // Validation: Email
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    } else {
        // Vérifier si l'email existe déjà
        $email_check = mysqli_query($cnx, "SELECT * FROM clients WHERE email = '$email'");
        if (mysqli_num_rows($email_check) > 0) {
            $errors['email'] = "Cet email est déjà utilisé";
        }
    }
    
    // Validation: Téléphone
    if (empty($telephone)) {
        $errors['telephone'] = "Le numéro de téléphone est requis";
    } elseif (!preg_match("/^[0-9]{10}$/", $telephone)) {
        $errors['telephone'] = "Le numéro doit contenir 10 chiffres";
    }
    
    // Validation: Mot de passe
    if (empty($mdp)) {
        $errors['mdp'] = "Le mot de passe est requis";
    } elseif (strlen($mdp) < 8) {
        $errors['mdp'] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match("/[A-Z]/", $mdp) || !preg_match("/[a-z]/", $mdp) || !preg_match("/[0-9]/", $mdp)) {
        $errors['mdp'] = "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre";
    }
    
    // Validation: Confirmation de mot de passe
    if ($mdp != $mdp_confirm) {
        $errors['mdp_confirm'] = "Les mots de passe ne correspondent pas";
    }
    
    // Si aucune erreur, procéder à l'insertion
    if (empty($errors)) {
        // Utiliser password_hash plutôt que sha1 pour plus de sécurité
        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
        
        $sql_insert = "INSERT INTO `clients`(`nom_complet`, `email`, `telephone`, `mdp`, `type`, `active`, `date_creation`)
                       VALUES ('$nom', '$email', '$telephone', '$hashed_password', 'client', '1', NOW())";
        
        if (mysqli_query($cnx, $sql_insert)) {
            // Création d'un jeton unique pour la vérification par email (optionnel)
            $token = bin2hex(random_bytes(50));
            
            // Redirection avec un message de succès
            $success_message = "Votre compte a été créé avec succès!";
            
            // Vider les valeurs du formulaire après succès
            $old_values = [];
            
            // Optionnel: connecter automatiquement l'utilisateur
            $user_id = mysqli_insert_id($cnx);
            $_SESSION['id_client'] = $user_id;
            $_SESSION['nom_client'] = $nom;
            $_SESSION['type_client'] = 'client';
            
            // Vous pouvez rediriger immédiatement si vous préférez
            // header('Location: index.php');
            // exit();
        } else {
            $errors['general'] = "Erreur lors de l'inscription: " . mysqli_error($cnx);
        }
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Inscrivez-vous pour profiter des services exclusifs du restaurant Le Gourmet">
    <title>Inscription - Le Gourmet</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Ion Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        @font-face {
            src: url(fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf);
            font-family: "BodoniModa-Italic";
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'BodoniModa-Italic', sans-serif !important;
            background: linear-gradient(120deg, #060834, #1a2a5a, #060834);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
            position: relative;
            overflow-x: hidden;
            padding: 80px 0;
        }

        /* Background Animation */
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Subtle Texture Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
            z-index: 0;
        }

        /* Floating Elements (Wine-inspired) */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            background: rgba(128, 0, 32, 0.3); /* Deep wine red */
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

        .main-container {
            position: relative;
            max-width: 600px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 245, 224, 0.3); /* Cream accent */
            border-radius: 20px;
            backdrop-filter: blur(15px);
            padding: 40px;
            box-shadow: 0 0 30px rgba(6, 8, 52, 0.3);
            z-index: 1;
            margin: 20px;
        }

        h2 {
            font-size: 2rem;
            color: #fff5e0; /* Cream */
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 5px rgba(6, 8, 52, 0.4);
        }

        .inputbox {
            position: relative;
            margin: 25px 0;
            border-bottom: 2px solid #fff5e0; /* Cream */
        }

        .inputbox.has-error {
            border-bottom: 2px solid #ff6b6b;
        }

        .input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1rem;
            padding: 0 35px 0 5px;
            color: #fff;
            transition: all 0.3s ease;
        }

        .input:focus ~ label,
        .input:not(:placeholder-shown) ~ label {
            top: -5px;
            font-size: 0.8rem;
            color: #fff5e0; /* Cream */
        }

        .inputbox ion-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff5e0; /* Cream */
            font-size: 1.2rem;
        }

        .inputbox label {
            position: absolute;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .eye-icon {
            cursor: pointer;
        }

        .password-strength {
            height: 5px;
            margin-top: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            background-color: #555;
        }

        .weak {
            width: 30%;
            background-color: #ff6b6b;
        }

        .medium {
            width: 60%;
            background-color: #ffd166;
        }

        .strong {
            width: 100%;
            background-color: #06d6a0;
        }

        button {
            width: 100%;
            height: 48px;
            border-radius: 40px;
            background: linear-gradient(45deg, #060834, #1a2a5a); /* Navy gradient */
            border: 1px solid #fff5e0; /* Cream border */
            outline: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #fff5e0; /* Cream text */
            transition: all 0.4s ease;
            box-shadow: 0 4px 12px rgba(6, 8, 52, 0.3);
            text-transform: uppercase;
            margin-top: 20px;
        }

        button:hover {
            background: linear-gradient(45deg, #1a2a5a, #060834);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(6, 8, 52, 0.5);
        }

        .success-message {
            background-color: rgba(6, 214, 160, 0.2);
            border: 1px solid #06d6a0;
            color: #06d6a0;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #fff5e0;
        }

        .login-link a {
            color: #fff5e0;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #ffd166;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 30px 20px;
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

    <div class="main-container">
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
                <p>Redirection vers la page d'accueil...</p>
                <script>
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 3000);
                </script>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['general'])): ?>
            <div class="error" style="text-align: center; margin-bottom: 20px;">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="POST" id="signupForm">
            <h2>Créez votre compte</h2>
            
            <div class="inputbox <?php echo isset($errors['nom_complet']) ? 'has-error' : ''; ?>">
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" name="nom_complet" class="input" placeholder=" " value="<?php echo isset($old_values['nom_complet']) ? htmlspecialchars($old_values['nom_complet']) : ''; ?>" required>
                <label>Nom Complet</label>
                <?php if (isset($errors['nom_complet'])): ?>
                    <span class="error"><?php echo $errors['nom_complet']; ?></span>
                <?php endif; ?>
            </div>

            <div class="inputbox <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="email" name="email" class="input" placeholder=" " value="<?php echo isset($old_values['email']) ? htmlspecialchars($old_values['email']) : ''; ?>" required>
                <label>Email</label>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>

            <div class="inputbox <?php echo isset($errors['telephone']) ? 'has-error' : ''; ?>">
                <ion-icon name="call-outline"></ion-icon>
                <input type="tel" name="telephone" class="input" placeholder=" " value="<?php echo isset($old_values['telephone']) ? htmlspecialchars($old_values['telephone']) : ''; ?>" required>
                <label>Téléphone</label>
                <?php if (isset($errors['telephone'])): ?>
                    <span class="error"><?php echo $errors['telephone']; ?></span>
                <?php endif; ?>
            </div>

            <div class="inputbox <?php echo isset($errors['mdp']) ? 'has-error' : ''; ?>">
                <span class="eye-icon" onclick="togglePasswordVisibility('mdp')">
                    <ion-icon name="eye-outline" id="eyeIcon-mdp"></ion-icon>
                </span>
                <input type="password" name="mdp" id="mdp" class="input" placeholder=" " required>
                <label>Mot de passe</label>
                <div id="passwordStrength" class="password-strength"></div>
                <?php if (isset($errors['mdp'])): ?>
                    <span class="error"><?php echo $errors['mdp']; ?></span>
                <?php endif; ?>
            </div>

            <div class="inputbox <?php echo isset($errors['mdp_confirm']) ? 'has-error' : ''; ?>">
                <span class="eye-icon" onclick="togglePasswordVisibility('mdp_confirm')">
                    <ion-icon name="eye-outline" id="eyeIcon-mdp_confirm"></ion-icon>
                </span>
                <input type="password" name="mdp_confirm" id="mdp_confirm" class="input" placeholder=" " required>
                <label>Confirmer le mot de passe</label>
                <?php if (isset($errors['mdp_confirm'])): ?>
                    <span class="error"><?php echo $errors['mdp_confirm']; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" name="signup">S'inscrire</button>
            
            <div class="login-link">
                Déjà inscrit ? <a href="login.php">Connexion</a>
            </div>
        </form>
    </div>

    <!-- Script pour la visibilité du mot de passe et l'évaluation de sa force -->
    <script>
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById('eyeIcon-' + inputId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('name', 'eye-off-outline');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('name', 'eye-outline');
            }
        }
        
        // Évaluer la force du mot de passe
        const passwordInput = document.getElementById('mdp');
        const strengthIndicator = document.getElementById('passwordStrength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Critères de force du mot de passe
            if (password.length >= 8) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^A-Za-z0-9]/)) strength += 1;
            
            // Afficher la force
            strengthIndicator.className = 'password-strength';
            
            if (password.length === 0) {
                strengthIndicator.style.width = '0%';
                strengthIndicator.classList.remove('weak', 'medium', 'strong');
            } else if (strength <= 2) {
                strengthIndicator.classList.add('weak');
            } else if (strength <= 4) {
                strengthIndicator.classList.add('medium');
            } else {
                strengthIndicator.classList.add('strong');
            }
        });
    </script>
</body>
</html>