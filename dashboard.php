<?php
include('cnx.php');
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: signin.php");
    exit();
}

// Récupération des données pour les statistiques
$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM clients WHERE `type` = 'client' AND active = 1");
$stmt->execute();
$result = $stmt->get_result();
$count_clients = $result->fetch_assoc()['count'];

$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM reservations");
$stmt->execute();
$result = $stmt->get_result();
$count_reservations = $result->fetch_assoc()['count'];

$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM plats");
$stmt->execute();
$result = $stmt->get_result();
$count_plats = $result->fetch_assoc()['count'];

// Récupérer les réservations récentes pour affichage
$stmt = $cnx->prepare("SELECT * FROM reservations ORDER BY heure DESC LIMIT 5");
$stmt->execute();
$recent_reservations = $stmt->get_result();

// Traitement des notifications
$notification = '';
$notificationType = '';

// Suppression d'un plat
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Récupérer le chemin de l'image avant de supprimer
    $stmt = $cnx->prepare("SELECT image FROM plats WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $imagePath = $row['image'];
        
        // Supprimer le plat
        $stmt = $cnx->prepare("DELETE FROM plats WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            // Supprimer l'image associée si elle existe
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            $notification = "Plat supprimé avec succès!";
            $notificationType = "success";
        } else {
            $notification = "Erreur lors de la suppression: " . $cnx->error;
            $notificationType = "danger";
        }
    }
}

// Modification d'un plat
if (isset($_POST['modifier'])) {
    $id = intval($_POST['id']);
    $nom = htmlspecialchars(trim($_POST['nom']));
    $description = htmlspecialchars(trim($_POST['description']));
    $prix = floatval($_POST['prix']);
    $categorie = htmlspecialchars(trim($_POST['categorie']));
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $imagePath = $_POST['old_image'];

    // Validation des données
    if (empty($nom) || $prix <= 0) {
        $notification = "Veuillez remplir tous les champs obligatoires correctement.";
        $notificationType = "danger";
    } else {
        // Traitement de l'image si une nouvelle est fournie
        if (!empty($_FILES["image"]["name"])) {
            $targetDir = "Uploads/plats/";
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            // Générer un nom unique pour éviter les conflits
            $fileName = pathinfo($_FILES["image"]["name"], PATHINFO_FILENAME);
            $fileExtension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $uniqueName = $fileName . '_' . time() . '.' . $fileExtension;
            $targetFile = $targetDir . $uniqueName;
            
            // Vérifier le type de fichier
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            if (in_array(strtolower($fileExtension), $allowedTypes)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($_POST['old_image']) && file_exists($_POST['old_image']) && $_POST['old_image'] !== '') {
                        unlink($_POST['old_image']);
                    }
                    $imagePath = $targetFile;
                } else {
                    $notification = "Erreur lors du téléchargement de l'image.";
                    $notificationType = "danger";
                }
            } else {
                $notification = "Seuls les fichiers JPG, JPEG, PNG, GIF et WEBP sont autorisés.";
                $notificationType = "danger";
            }
        }

        // Mettre à jour le plat dans la base de données
        if (empty($notification)) {
            $stmt = $cnx->prepare("UPDATE plats SET nom = ?, description = ?, prix = ?, image = ?, categorie = ?, disponible = ? WHERE id = ?");
            $stmt->bind_param("ssdsiii", $nom, $description, $prix, $imagePath, $categorie, $disponible, $id);
            
            if ($stmt->execute()) {
                $notification = "Plat mis à jour avec succès!";
                $notificationType = "success";
            } else {
                $notification = "Erreur lors de la mise à jour: " . $cnx->error;
                $notificationType = "danger";
            }
        }
    }
}

// Ajout d'un plat
if (isset($_POST['ajouter'])) {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $description = htmlspecialchars(trim($_POST['description']));
    $prix = floatval($_POST['prix']);
    $categorie = htmlspecialchars(trim($_POST['categorie']));
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $imagePath = "";

    // Validation des données
    if (empty($nom) || $prix <= 0) {
        $notification = "Veuillez remplir tous les champs obligatoires correctement.";
        $notificationType = "danger";
    } else {
        // Traitement de l'image
        if (!empty($_FILES["image"]["name"])) {
            $targetDir = "Uploads/plats/";
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            // Générer un nom unique pour éviter les conflits
            $fileName = pathinfo($_FILES["image"]["name"], PATHINFO_FILENAME);
            $fileExtension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $uniqueName = $fileName . '_' . time() . '.' . $fileExtension;
            $targetFile = $targetDir . $uniqueName;
            
            // Vérifier le type de fichier
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            if (in_array(strtolower($fileExtension), $allowedTypes)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $imagePath = $targetFile;
                } else {
                    $notification = "Erreur lors du téléchargement de l'image.";
                    $notificationType = "danger";
                }
            } else {
                $notification = "Seuls les fichiers JPG, JPEG, PNG, GIF et WEBP sont autorisés.";
                $notificationType = "danger";
            }
        }

        // Insérer le plat dans la base de données
        if (empty($notification)) {
            $stmt = $cnx->prepare("INSERT INTO plats (nom, description, prix, image, categorie, disponible, date_ajout) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssdsii", $nom, $description, $prix, $imagePath, $categorie, $disponible);
            
            if ($stmt->execute()) {
                $notification = "Plat ajouté avec succès!";
                $notificationType = "success";
            } else {
                $notification = "Erreur lors de l'ajout: " . $cnx->error;
                $notificationType = "danger";
            }
        }
    }
}

// Récupération des catégories de plats
$stmt = $cnx->prepare("SELECT DISTINCT categorie FROM plats ORDER BY categorie");
$stmt->execute();
$categories_result = $stmt->get_result();
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    if (!empty($row['categorie'])) {
        $categories[] = $row['categorie'];
    }
}

// Récupération des plats pour affichage
$stmt = $cnx->prepare("SELECT * FROM plats ORDER BY id DESC");
$stmt->execute();
$plats_result = $stmt->get_result();

// Vérifier si c'est une requête AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Pour les requêtes AJAX, renvoyer uniquement les notifications
if ($isAjax && !empty($notification)) {
    header('Content-Type: application/json');
    echo json_encode(['notification' => $notification, 'type' => $notificationType]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration - Le Gourmet</title>
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.2.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: "BodoniModa";
            src: url("fonts/BodoniModa-VariableFont_opsz,wght.ttf") format("truetype");
        }
        @font-face {
            font-family: "BodoniModa-Italic";
            src: url("fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf") format("truetype");
        }
        :root {
            --primary-color: #1a2a5a;
            --secondary-color: #800020;
            --accent-color: #fff5e0;
            --dark-color: #060834;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        body {
            font-family: 'BodoniModa', serif;
            background-color: #f5f5f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            width: 250px;
        }
        .sidebar-header {
            padding: 20px;
            background: var(--dark-color);
            color: var(--accent-color);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .sidebar .nav-link {
            padding: 15px 20px;
            color: rgba(255,245,224,0.8);
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover {
            color: var(--accent-color);
            background: rgba(0,0,0,0.1);
            border-left-color: var(--accent-color);
        }
        .sidebar .nav-link.active {
            color: var(--accent-color);
            background: rgba(0,0,0,0.2);
            border-left-color: var(--accent-color);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 1rem 0;
        }
        main {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 999;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            main {
                margin-left: 0;
            }
        }
        .content-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
        }
        .stat-card .card-body {
            padding: 25px;
            position: relative;
            z-index: 1;
        }
        .stat-card .card-icon {
            font-size: 50px;
            opacity: 0.8;
            position: absolute;
            right: 15px;
            top: 15px;
        }
        .stat-card .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .stat-card .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .stat-card .card-subtitle {
            font-size: 0.9rem;
            opacity: 0.7;
        }
        .stat-card.primary {
            background: linear-gradient(135deg, #1a2a5a, #2a3a6a);
            color: white;
        }
        .stat-card.danger {
            background: linear-gradient(135deg, #800020, #a01030);
            color: white;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #28a745, #38b755);
            color: white;
        }
        .stat-card.info {
            background: linear-gradient(135deg, #17a2b8, #27b2c8);
            color: white;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        .card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary-color);
        }
        .card-header-tabs {
            margin-bottom: -15px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        .plat-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
            overflow: hidden;
        }
        .plat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .plat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--light-color);
            border-bottom: 1px solid #eee;
        }
        .plat-details {
            padding: 20px;
            display: flex;
        }
        .plat-info {
            flex: 1;
            padding-right: 20px;
        }
        .plat-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .plat-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        .plat-description {
            color: #555;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .plat-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }
        .plat-meta-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .plat-meta-item i {
            margin-right: 5px;
            color: var(--primary-color);
        }
        .plat-category {
            background: var(--primary-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        .plat-status {
            background: var(--success-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-left: 10px;
        }
        .plat-status.unavailable {
            background: var(--danger-color);
        }
        .btn-actions {
            display: flex;
            gap: 10px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 42, 90, 0.25);
        }
        .form-label {
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
        }
        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #0f1a3a;
            border-color: #0f1a3a;
        }
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        .btn-danger:hover {
            background-color: #bd2130;
            border-color: #bd2130;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-dismissible .btn-close {
            padding: 15px 20px;
        }
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .recent-items-table th, .recent-items-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .recent-items-table tbody tr {
            transition: all 0.2s;
        }
        .recent-items-table tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-bottom: none;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }
        .toggle-sidebar {
            display: none;
            padding: 10px;
            background: var(--primary-color);
            color: white;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
            border-radius: 5px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .toggle-sidebar {
                display: block;
            }
        }
        .navbar-admin {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 30px;
            margin-bottom: 30px;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 5px;
            display: none;
        }
        .custom-file-input {
            cursor: pointer;
        }
        .profile-dropdown {
            min-width: 220px;
            padding: 0;
        }
        .profile-header {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .profile-header h6 {
            margin: 0;
            font-weight: 600;
        }
        .profile-dropdown .dropdown-item {
            padding: 12px 20px;
        }
        .profile-dropdown .dropdown-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        /* Tooltip styles */
        .tooltip-inner {
            max-width: 200px;
            padding: 5px 10px;
            color: white;
            text-align: center;
            background-color: var(--dark-color);
            border-radius: 5px;
            font-size: 0.8rem;
        }
        
        /* Switch toggle for disponible/non disponible */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-left: -2.5em;
            background-color: rgba(0,0,0,0.1);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='white'/%3e%3c/svg%3e");
            background-position: left center;
            border-radius: 1.5em;
            transition: background-position .15s ease-in-out;
        }
        .form-switch .form-check-input:checked {
            background-color: var(--success-color);
            background-position: right center;
        }
    </style>
</head>

<body>
    <!-- Menu toggle for mobile -->
    <div class="toggle-sidebar">
        <i class="bi bi-list"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Le Gourmet</h3>
            <p class="mb-0">Panneau d'administration</p>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reservation.php">
                    <i class="bi bi-calendar-check"></i> Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="menu.php">
                    <i class="bi bi-menu-button-wide"></i> Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="clients.php">
                    <i class="bi bi-people"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="galerie.php">
                    <i class="bi bi-images"></i> Galerie
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="messages.php">
                    <i class="bi bi-chat-left-text"></i> Messages
                    <span class="badge bg-danger rounded-pill">3</span>
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <!-- <li class="nav-item">
                <a class="nav-link" href="admin_parametres.php">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="index.php" target="_blank">
                    <i class="bi bi-house"></i> Voir le site
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Navbar -->
        <nav class="navbar-admin d-flex justify-content-between mb-4">
            <div>
                <h1 class="fs-4 m-0">Tableau de bord</h1>
            </div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://via.placeholder.com/150" alt="Admin" class="user-avatar">
                    <span class="d-none d-md-inline me-2">Admin</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow profile-dropdown" aria-labelledby="dropdownUser">
                    <li>
                        <div class="profile-header">
                            <h6>Admin</h6>
                            <small>Administrateur</small>
                        </div>
                    </li>
                    <li><a class="dropdown-item" href="admin_profil.php"><i class="bi bi-person-circle"></i> Mon profil</a></li>
                    <li><a class="dropdown-item" href="admin_parametres.php"><i class="bi bi-gear"></i> Paramètres</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                </ul>
            </div>
        </nav> 

        <!-- Notification toast -->
        <?php if (!empty($notification)): ?>
        <div class="notification-toast">
            <div class="toast align-items-center text-white bg-<?php echo $notificationType; ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $notification; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="stat-card primary">
                    <div class="card-body">
                        <i class="bi bi-people card-icon"></i>
                        <h5 class="card-title">Clients</h5>
                        <div class="card-value"><?php echo $count_clients; ?></div>
                        <p class="card-subtitle">Clients actifs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="stat-card danger">
                    <div class="card-body">
                        <i class="bi bi-calendar-check card-icon"></i>
                        <h5 class="card-title">Réservations</h5>
                        <div class="card-value"><?php echo $count_reservations; ?></div>
                        <p class="card-subtitle">Total réservations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="stat-card success">
                    <div class="card-body">
                        <i class="bi bi-menu-button card-icon"></i>
                        <h5 class="card-title">Plats</h5>
                        <div class="card-value"><?php echo $count_plats; ?></div>
                        <p class="card-subtitle">Plats au menu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities and Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Réservations récentes</h5>
                        <a href="admin_reservations.php" class="btn btn-sm btn-primary">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover recent-items-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Date & Heure</th>
                                        <th>Personnes</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_reservations->num_rows > 0): ?>
                                        <?php while ($reservation = $recent_reservations->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($reservation['nom']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($reservation['telephone']); ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $date = new DateTime($reservation['heure']);
                                                        echo $date->format('d/m/Y H:i'); 
                                                    ?>
                                                </td>
                                                <td><?php echo $reservation['personnes']; ?></td>
                                                <td>
                                                    <?php if (isset($reservation['statut'])): ?>
                                                        <?php if ($reservation['statut'] == 'confirmée'): ?>
                                                            <span class="badge bg-success">Confirmée</span>
                                                        <?php elseif ($reservation['statut'] == 'en attente'): ?>
                                                            <span class="badge bg-warning">En attente</span>
                                                        <?php elseif ($reservation['statut'] == 'annulée'): ?>
                                                            <span class="badge bg-danger">Annulée</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inconnue</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Confirmée</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <!-- <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary view-reservation-btn" data-reservation-id="<?php echo $reservation['id']; ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#sendEmailModal<?php echo $reservation['id']; ?>">
                                                            <i class="bi bi-envelope"></i>
                                                        </button>
                                                    </div> -->
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Aucune réservation récente</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlatModal">
                                <i class="bi bi-plus-circle"></i> Ajouter un plat
                            </button>
                            <!-- <a href="admin_reservations.php" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-plus"></i> Gérer les réservations
                            </a> -->
                            <a href="messages.php" class="btn btn-outline-primary">
                                <i class="bi bi-chat-left-text"></i> Voir les messages
                                <span class="badge bg-danger">3</span>
                            </a>
                            <a href="index.php" target="_blank" class="btn btn-outline-dark">
                                <i class="bi bi-house"></i> Voir le site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Management -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Gestion du menu</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlatModal">
                    <i class="bi bi-plus-circle"></i> Ajouter un plat
                </button>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs card-header-tabs" id="menuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">Tous</button>
                    </li>
                    <?php foreach ($categories as $categorie): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="<?php echo strtolower(str_replace(' ', '-', $categorie)); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo strtolower(str_replace(' ', '-', $categorie)); ?>" type="button" role="tab"><?php echo htmlspecialchars($categorie); ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="tab-content mt-4" id="menuTabsContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <?php if ($plats_result->num_rows > 0): ?>
                            <?php while ($plat = $plats_result->fetch_assoc()): ?>
                                <div class="plat-item animate__animated animate__fadeIn">
                                    <div class="plat-header">
                                        <div>
                                            <span class="plat-category"><?php echo !empty($plat['categorie']) ? htmlspecialchars($plat['categorie']) : 'Non catégorisé'; ?></span>
                                            <?php if (isset($plat['disponible'])): ?>
                                                <?php if ($plat['disponible']): ?>
                                                    <span class="plat-status">Disponible</span>
                                                <?php else: ?>
                                                    <span class="plat-status unavailable">Non disponible</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-actions">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $plat['id']; ?>">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $plat['id']; ?>">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <div class="plat-details">
                                        <div class="plat-info">
                                            <h5 class="plat-title"><?php echo htmlspecialchars($plat['nom']); ?></h5>
                                            <p class="plat-description"><?php echo htmlspecialchars($plat['description']); ?></p>
                                            <div class="plat-meta">
                                                <div class="plat-meta-item">
                                                    <i class="bi bi-currency-euro"></i>
                                                    <span><?php echo number_format($plat['prix'], 2, ',', ' '); ?> €</span>
                                                </div>
                                                <?php if (isset($plat['date_ajout'])): ?>
                                                    <div class="plat-meta-item">
                                                        <i class="bi bi-calendar"></i>
                                                        <span>Ajouté le <?php echo date('d/m/Y', strtotime($plat['date_ajout'])); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($plat['image']); ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>" class="plat-image">
                                        <?php else: ?>
                                            <img src="Uploads/plats/default-dish.jpg" alt="Image par défaut" class="plat-image">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Modal pour modifier -->
                                <div class="modal fade" id="editModal<?php echo $plat['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $plat['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $plat['id']; ?>">
                                                    Modifier le plat
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="plat-form">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $plat['id']; ?>">
                                                    <input type="hidden" name="old_image" value="<?php echo $plat['image']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="nom<?php echo $plat['id']; ?>" class="form-label">Nom du plat</label>
                                                        <input type="text" class="form-control" name="nom" id="nom<?php echo $plat['id']; ?>" value="<?php echo htmlspecialchars($plat['nom']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="description<?php echo $plat['id']; ?>" class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" id="description<?php echo $plat['id']; ?>" rows="3"><?php echo htmlspecialchars(trim($plat['description'])); ?></textarea>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="prix<?php echo $plat['id']; ?>" class="form-label">Prix (€)</label>
                                                            <input type="number" class="form-control" name="prix" id="prix<?php echo $plat['id']; ?>" step="0.01" min="0" value="<?php echo htmlspecialchars($plat['prix']); ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="categorie<?php echo $plat['id']; ?>" class="form-label">Catégorie</label>
                                                            <select class="form-select" name="categorie" id="categorie<?php echo $plat['id']; ?>">
                                                                <option value="">Sélectionner une catégorie</option>
                                                                <option value="Entrées" <?php echo ($plat['categorie'] == 'Entrées') ? 'selected' : ''; ?>>Entrées</option>
                                                                <option value="Plats" <?php echo ($plat['categorie'] == 'Plats') ? 'selected' : ''; ?>>Plats</option>
                                                                <option value="Desserts" <?php echo ($plat['categorie'] == 'Desserts') ? 'selected' : ''; ?>>Desserts</option>
                                                                <option value="Boissons" <?php echo ($plat['categorie'] == 'Boissons') ? 'selected' : ''; ?>>Boissons</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="image<?php echo $plat['id']; ?>" class="form-label">Image</label>
                                                        <input type="file" class="form-control" name="image" id="image<?php echo $plat['id']; ?>" accept="image/*">
                                                        <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                                                            <div class="mt-2">
                                                                <img src="<?php echo htmlspecialchars($plat['image']); ?>" alt="Image actuelle" class="img-thumbnail" style="max-height: 100px;">
                                                                <span class="ms-2">Image actuelle</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="mb-3 form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="disponible" id="disponible<?php echo $plat['id']; ?>" <?php echo (isset($plat['disponible']) && $plat['disponible']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="disponible<?php echo $plat['id']; ?>">Disponible</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" name="modifier" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal pour supprimer -->
                                <div class="modal fade" id="deleteModal<?php echo $plat['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $plat['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $plat['id']; ?>">Confirmer la suppression</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Êtes-vous sûr de vouloir supprimer le plat <strong><?php echo htmlspecialchars($plat['nom']); ?></strong> ?</p>
                                                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Cette action est irréversible.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="button" class="btn btn-danger delete-plat-btn" data-plat-id="<?php echo $plat['id']; ?>">Supprimer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="bi bi-emoji-frown" style="font-size: 2rem;"></i>
                                <p class="mt-3">Aucun plat trouvé. Ajoutez votre premier plat!</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addPlatModal">
                                    <i class="bi bi-plus-circle"></i> Ajouter un plat
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tab panes for each category -->
                    <?php foreach ($categories as $categorie): ?>
                        <div class="tab-pane fade" id="<?php echo strtolower(str_replace(' ', '-', $categorie)); ?>" role="tabpanel">
                            <?php
                            // Get plats for this category
                            $stmt = $cnx->prepare("SELECT * FROM plats WHERE categorie = ? ORDER BY id DESC");
                            $stmt->bind_param("s", $categorie);
                            $stmt->execute();
                            $category_plats = $stmt->get_result();
                            ?>
                            
                            <?php if ($category_plats->num_rows > 0): ?>
                                <?php while ($plat = $category_plats->fetch_assoc()): ?>
                                    <div class="plat-item animate__animated animate__fadeIn">
                                        <!-- Content similar to the "all" tab, repeated for each category -->
                                        <div class="plat-header">
                                            <div>
                                                <span class="plat-category"><?php echo htmlspecialchars($plat['categorie']); ?></span>
                                                <?php if (isset($plat['disponible'])): ?>
                                                    <?php if ($plat['disponible']): ?>
                                                        <span class="plat-status">Disponible</span>
                                                    <?php else: ?>
                                                        <span class="plat-status unavailable">Non disponible</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn-actions">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $plat['id']; ?>">
                                                    <i class="bi bi-pencil"></i> Modifier
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $plat['id']; ?>">
                                                    <i class="bi bi-trash"></i> Supprimer
                                                </button>
                                            </div>
                                        </div>
                                        <div class="plat-details">
                                            <div class="plat-info">
                                                <h5 class="plat-title"><?php echo htmlspecialchars($plat['nom']); ?></h5>
                                                <p class="plat-description"><?php echo htmlspecialchars($plat['description']); ?></p>
                                                <div class="plat-meta">
                                                    <div class="plat-meta-item">
                                                        <i class="bi bi-currency-euro"></i>
                                                        <span><?php echo number_format($plat['prix'], 2, ',', ' '); ?> €</span>
                                                    </div>
                                                    <?php if (isset($plat['date_ajout'])): ?>
                                                        <div class="plat-meta-item">
                                                            <i class="bi bi-calendar"></i>
                                                            <span>Ajouté le <?php echo date('d/m/Y', strtotime($plat['date_ajout'])); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($plat['image']); ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>" class="plat-image">
                                            <?php else: ?>
                                                <img src="Uploads/plats/default-dish.jpg" alt="Image par défaut" class="plat-image">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <p>Aucun plat dans cette catégorie.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal pour ajouter un plat -->
    <div class="modal fade" id="addPlatModal" tabindex="-1" aria-labelledby="addPlatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlatModalLabel">Ajouter un nouveau plat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="plat-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom du plat</label>
                            <input type="text" class="form-control" name="nom" id="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prix" class="form-label">Prix (€)</label>
                                <input type="number" class="form-control" name="prix" id="prix" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="categorie" class="form-label">Catégorie</label>
                                <select class="form-select" name="categorie" id="categorie">
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Entrées">Entrées</option>
                                    <option value="Plats">Plats</option>
                                    <option value="Desserts">Desserts</option>
                                    <option value="Boissons">Boissons</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="disponible" id="disponible" checked>
                            <label class="form-check-label" for="disponible">Disponible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="ajouter" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.2.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.0/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Auto-hide toast notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // SIMPLE FIX FOR ACTION BUTTONS
            // Empêcher la navigation par défaut pour les boutons de modification et de suppression
            document.addEventListener('click', function(e) {
                // Pour les boutons de modification et suppression
                if (e.target.closest('.btn-outline-primary') || e.target.closest('.btn-outline-danger')) {
                    // Trouver le bouton cliqué
                    const button = e.target.closest('.btn-outline-primary') || e.target.closest('.btn-outline-danger');
                    
                    // Ne pas traiter les boutons qui ont déjà data-bs-toggle="modal"
                    if (button.hasAttribute('data-bs-toggle')) {
                        return;
                    }
                    
                    // Empêcher la navigation par défaut
                    e.preventDefault();
                    
                    // Obtenir l'ID du modal à ouvrir depuis l'attribut data-bs-target
                    const modalId = button.getAttribute('data-bs-target');
                    
                    // Ouvrir le modal si l'attribut data-bs-target existe
                    if (modalId) {
                        const modalElement = document.querySelector(modalId);
                        if (modalElement) {
                            const modal = new bootstrap.Modal(modalElement);
                            modal.show();
                        }
                    }
                }
            }, true);
            
            // Gérer les clics sur les boutons de visualisation de réservation
            document.addEventListener('click', function(e) {
                if (e.target.closest('.view-reservation-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.view-reservation-btn');
                    const reservationId = button.getAttribute('data-reservation-id');
                    
                    // Afficher les détails de la réservation dans un popup
                    showReservationDetails(reservationId);
                }
            });
            
            // Fonction pour afficher les détails de la réservation dans un popup
            function showReservationDetails(reservationId) {
                // Créer le modal de détails s'il n'existe pas déjà
                let modalElement = document.getElementById('reservationModal');
                if (!modalElement) {
                    modalElement = document.createElement('div');
                    modalElement.className = 'modal fade';
                    modalElement.id = 'reservationModal';
                    modalElement.setAttribute('tabindex', '-1');
                    modalElement.setAttribute('aria-hidden', 'true');
                    
                    modalElement.innerHTML = `
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Détails de la réservation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="reservationDetails">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                            <p class="mt-2">Chargement des détails...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(modalElement);
                }
                
                // Afficher le modal
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                
                // Simuler le chargement des détails (normalement, vous feriez un appel AJAX ici)
                setTimeout(() => {
                    const reservationDetails = document.getElementById('reservationDetails');
                    
                    // Dans un cas réel, vous récupéreriez ces données de votre serveur
                    // Ici, nous simulons des données pour la démonstration
                    reservationDetails.innerHTML = `
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Réservation #${reservationId}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-person me-2"></i>Informations client</h6>
                                        <p class="mb-1"><strong>Nom:</strong> Jean Dupont</p>
                                        <p class="mb-1"><strong>Téléphone:</strong> 06 12 34 56 78</p>
                                        <p class="mb-1"><strong>Email:</strong> jean.dupont@example.com</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-calendar-event me-2"></i>Détails de la réservation</h6>
                                        <p class="mb-1"><strong>Date:</strong> 20/05/2025</p>
                                        <p class="mb-1"><strong>Heure:</strong> 19:30</p>
                                        <p class="mb-1"><strong>Personnes:</strong> 4</p>
                                        <p class="mb-1"><strong>Statut:</strong> <span class="badge bg-success">Confirmée</span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h6><i class="bi bi-chat-quote me-2"></i>Notes</h6>
                                        <p>Près de la fenêtre si possible. C'est pour un anniversaire.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }, 800);
            }
            
            // Initialize toasts
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 5000
                });
                toast.show();
                return toast;
            });
            
            // Mobile sidebar toggle
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('show');
            });
            
            // Image preview functionality
            const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
            imageInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const id = this.id;
                    const previewId = `${id}_preview`;
                    
                    // Check if preview element already exists, if not create it
                    let preview = document.getElementById(previewId);
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = previewId;
                        preview.className = 'image-preview img-thumbnail mt-2';
                        preview.style.maxHeight = '150px';
                        this.parentNode.appendChild(preview);
                    }
                    
                    // Show the preview if file is selected
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        preview.style.display = 'none';
                    }
                });
            });
            
            // DataTables initialization
            if (document.querySelector('.recent-items-table')) {
                new DataTable('.recent-items-table', {
                    paging: false,
                    info: false,
                    searching: false
                });
            }
            
            // Handle form submissions (edit and add forms) without page reload
            const platForms = document.querySelectorAll('.plat-form');
            platForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Create FormData to handle file uploads
                    const formData = new FormData(this);
                    
                    // Add header to indicate AJAX request
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'dashboard.php', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                // Close the modal
                                const modalElement = form.closest('.modal');
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                modal.hide();
                                
                                // Show success notification
                                const action = formData.has('ajouter') ? 'ajouté' : 'mis à jour';
                                showNotification(`Plat ${action} avec succès!`, 'success');
                                
                                // Reload the content without refreshing the page
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } catch (error) {
                                console.error('Error processing response:', error);
                            }
                        } else {
                            showNotification('Erreur lors de l\'opération.', 'danger');
                        }
                    };
                    
                    xhr.onerror = function() {
                        showNotification('Erreur de connexion au serveur.', 'danger');
                    };
                    
                    xhr.send(formData);
                });
            });
            
            // Handle delete button clicks for popup behavior
            const deleteButtons = document.querySelectorAll('.delete-plat-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const platId = this.getAttribute('data-plat-id');
                    const deleteUrl = `dashboard.php?delete_id=${platId}`;
                    
                    // Add header to indicate AJAX request
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', deleteUrl, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                // Close the modal
                                const modalElement = button.closest('.modal');
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                modal.hide();
                                
                                // Show success notification
                                showNotification('Plat supprimé avec succès!', 'success');
                                
                                // Find and remove the plat item from the DOM
                                const platItems = document.querySelectorAll('.plat-item');
                                platItems.forEach(item => {
                                    if (item.querySelector(`button[data-bs-target="#deleteModal${platId}"]`)) {
                                        item.remove();
                                    }
                                });
                                
                                // If no more plat items, show empty message
                                const remainingPlats = document.querySelectorAll('.plat-item').length;
                                if (remainingPlats === 0) {
                                    const tabContent = document.querySelector('.tab-pane.active');
                                    if (tabContent) {
                                        tabContent.innerHTML = `
                                            <div class="text-center p-4">
                                                <i class="bi bi-emoji-frown" style="font-size: 2rem;"></i>
                                                <p class="mt-3">Aucun plat trouvé. Ajoutez votre premier plat!</p>
                                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addPlatModal">
                                                    <i class="bi bi-plus-circle"></i> Ajouter un plat
                                                </button>
                                            </div>
                                        `;
                                    }
                                }
                            } catch (error) {
                                console.error('Error processing response:', error);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else {
                            showNotification('Erreur lors de la suppression.', 'danger');
                        }
                    };
                    
                    xhr.onerror = function() {
                        showNotification('Erreur de connexion au serveur.', 'danger');
                    };
                    
                    xhr.send();
                });
            });
            
            // Function to show notifications
            function showNotification(message, type) {
                // Create toast container if it doesn't exist
                let toastContainer = document.querySelector('.notification-toast');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'notification-toast';
                    document.body.appendChild(toastContainer);
                }
                
                const toastHTML = `
                    <div class="toast align-items-center text-white bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                toastContainer.innerHTML = toastHTML;
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    const toast = document.querySelector('.toast');
                    if (toast) {
                        toast.classList.remove('show');
                        setTimeout(() => {
                            if (toast && toast.parentNode) {
                                toast.parentNode.removeChild(toast);
                            }
                        }, 300);
                    }
                }, 5000);
            }