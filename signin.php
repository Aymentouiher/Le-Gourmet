<?php
include("cnx.php");
// Éviter la double connexion
if (!isset($cnx)) {
    $cnx = mysqli_connect('localhost', 'root', '', 'gourmet');
    if (!$cnx) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['id'])) {
    $redirect = strtolower($_SESSION['type']) === 'admin' ? "dashboard.php" : "index.php";
    header("Location: " . $redirect);
    exit();
}

// Variables pour les erreurs et les valeurs précédentes
$errors = [];
$old_values = [];

// Traitement du formulaire de connexion
if (isset($_POST['signin'])) {
    // Récupération et nettoyage des données
    $email = mysqli_real_escape_string($cnx, trim($_POST['email']));
    $mdp = $_POST['mdp'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Stockage de l'email pour le réafficher en cas d'erreur
    $old_values['email'] = $email;
    
    // Validation de base
    if (empty($email)) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    }
    
    if (empty($mdp)) {
        $errors['mdp'] = "Le mot de passe est requis";
    }
    
    // Si pas d'erreur de validation, vérifier les identifiants
    if (empty($errors)) {
        // Utilisation de requête préparée pour la sécurité
        $stmt = $cnx->prepare("SELECT * FROM `clients` WHERE email = ? AND active = '1'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            // Vérification du mot de passe
            // Note: Comme nous avons utilisé sha1 dans l'ancienne version, nous continuons à l'utiliser ici
            // Mais nous recommandons de migrer vers password_hash/password_verify pour les nouveaux utilisateurs
            $mdp_hashed = sha1($mdp);
            
            if ($data['mdp'] === $mdp_hashed) {
                // Connexion réussie - Configuration des variables de session
                $_SESSION['id'] = $data['id'];
                $_SESSION['nom'] = $data['nom_complet'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['telephone'] = $data['telephone'];
                $_SESSION['type'] = $data['type'];
                
                // Si "Se souvenir de moi" est coché, créer un cookie (valable 30 jours)
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); // Générer un token unique
                    
                    // Stocker le token dans la base de données
                    $user_id = $data['id'];
                    $expires = date('Y-m-d H:i:s', time() + 60*60*24*30); // 30 jours
                    
                    $delete_old = $cnx->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                    $delete_old->bind_param("i", $user_id);
                    $delete_old->execute();
                    
                    $token_stmt = $cnx->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                    $token_stmt->bind_param("iss", $user_id, $token, $expires);
                    $token_stmt->execute();
                    
                    // Créer le cookie
                    setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', false, true);
                }
                
                // Redirection basée sur le type d'utilisateur
                $redirect = strtolower($_SESSION['type']) === 'admin' ? "dashboard.php" : "index.php";
                header("Location: " . $redirect);
                exit();
            } else {
                $errors['mdp'] = "Mot de passe incorrect";
            }
        } else {
            $errors['email'] = "Aucun compte actif trouvé avec cet email";
        }
        $stmt->close();
    }
}

// Vérifier si un cookie "remember_token" existe
if (!isset($_SESSION['id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Rechercher le token dans la base de données
    $stmt = $cnx->prepare("SELECT u.* FROM clients u 
                         JOIN remember_tokens r ON u.id = r.user_id 
                         WHERE r.token = ? AND r.expires > NOW() AND u.active = '1'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Connexion automatique
        $_SESSION['id'] = $user['id'];
        $_SESSION['nom'] = $user['nom_complet'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['telephone'] = $user['telephone'];
        $_SESSION['type'] = $user['type'];
        
        // Redirection basée sur le type d'utilisateur
        $redirect = strtolower($_SESSION['type']) === 'admin' ? "dashboard.php" : "index.php";
        header("Location: " . $redirect);
        exit();
    } else {
        // Token invalide ou expiré, supprimer le cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Le Gourmet</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        @font-face {
            font-family: "BodoniModa-Italic";
            src: url("fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf") format("truetype");
            font-display: swap;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
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
        .main-container {
            position: relative;
            max-width: 450px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 245, 224, 0.3);
            border-radius: 20px;
            backdrop-filter: blur(15px);
            padding: 40px;
            box-shadow: 0 0 30px rgba(6, 8, 52, 0.3);
            z-index: 1;
            margin: 20px;
        }
        h2 {
            font-size: 2.2rem;
            color: #fff5e0;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 5px rgba(6, 8, 52, 0.4);
        }
        .inputbox {
            position: relative;
            margin: 25px 0;
            border-bottom: 2px solid #fff5e0;
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
            color: #fff5e0;
        }
        .inputbox ion-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff5e0;
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
        .forget {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0 35px;
        }
        .forget label {
            display: flex;
            align-items: center;
            color: #fff;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .forget label input {
            margin-right: 8px;
            accent-color: #fff5e0;
            width: 16px;
            height: 16px;
        }
        .forget a {
            color: #fff5e0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .forget a:hover {
            color: #ffd166;
            text-decoration: underline;
        }
        .eye-icon {
            cursor: pointer;
        }
        button {
            width: 100%;
            height: 48px;
            border-radius: 40px;
            background: linear-gradient(45deg, #060834, #1a2a5a);
            border: 1px solid #fff5e0;
            outline: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #fff5e0;
            transition: all 0.4s ease;
            box-shadow: 0 4px 12px rgba(6, 8, 52, 0.3);
            text-transform: uppercase;
        }
        button:hover {
            background: linear-gradient(45deg, #1a2a5a, #060834);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(6, 8, 52, 0.5);
        }
        .register {
            text-align: center;
            margin-top: 25px;
            color: #fff;
            font-size: 0.9rem;
        }
        .register a {
            color: #fff5e0;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .register a:hover {
            color: #ffd166;
            text-decoration: underline;
        }
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .main-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .forget {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="main-container">
        <form action="signin.php" method="POST">
            <h2>Connexion</h2>
            
            <div class="inputbox <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="email" name="email" class="input" placeholder=" " value="<?php echo isset($old_values['email']) ? htmlspecialchars($old_values['email']) : ''; ?>" required>
                <label>Email</label>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="inputbox <?php echo isset($errors['mdp']) ? 'has-error' : ''; ?>">
                <span class="eye-icon" onclick="togglePasswordVisibility()">
                    <ion-icon name="eye-outline" id="eyeIcon"></ion-icon>
                </span>
                <input type="password" name="mdp" id="mdp" class="input" placeholder=" " required>
                <label>Mot de passe</label>
                <?php if (isset($errors['mdp'])): ?>
                    <span class="error"><?php echo $errors['mdp']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="forget">
                <label>
                    <input type="checkbox" name="remember">
                    Se souvenir de moi
                </label>
                <a href="reset-password.php">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" name="signin">Se connecter</button>
            
            <div class="register">
                <p>Pas encore de compte ? <a href="signup.php">Inscrivez-vous</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('mdp');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('name', 'eye-off-outline');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('name', 'eye-outline');
            }
        }
    </script>
</body>
</html>