<?php
include('cnx.php');
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: signin.php");
    exit();
}

// Traitement des notifications
$notification = '';
$notificationType = '';

// Suppression d'un client
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Désactiver le client au lieu de le supprimer complètement
    $stmt = $cnx->prepare("UPDATE clients SET active = 0 WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $notification = "Client désactivé avec succès!";
        $notificationType = "success";
    } else {
        $notification = "Erreur lors de la désactivation: " . $cnx->error;
        $notificationType = "danger";
    }
}

// Réactivation d'un client
if (isset($_GET['activate_id']) && is_numeric($_GET['activate_id'])) {
    $activate_id = intval($_GET['activate_id']);
    
    $stmt = $cnx->prepare("UPDATE clients SET active = 1 WHERE id = ?");
    $stmt->bind_param("i", $activate_id);
    
    if ($stmt->execute()) {
        $notification = "Client réactivé avec succès!";
        $notificationType = "success";
    } else {
        $notification = "Erreur lors de la réactivation: " . $cnx->error;
        $notificationType = "danger";
    }
}

// Récupération des statistiques
$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM clients WHERE `type` = 'client' AND active = 1");
$stmt->execute();
$result = $stmt->get_result();
$count_active_clients = $result->fetch_assoc()['count'];

$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM clients WHERE `type` = 'client' AND active = 0");
$stmt->execute();
$result = $stmt->get_result();
$count_inactive_clients = $result->fetch_assoc()['count'];

// Récupération des clients
$show_inactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] == 1;
$filter_condition = $show_inactive ? "" : " AND active = 1";

$stmt = $cnx->prepare("SELECT * FROM clients WHERE `type` = 'client'" . $filter_condition . " ORDER BY id DESC");
$stmt->execute();
$clients_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clients - Le Gourmet</title>
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.2.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        .btn-success:hover {
            background-color: #1e7e34;
            border-color: #1e7e34;
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
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
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
        .profile-dropdown .dropdown-item {
            padding: 12px 20px;
        }
        .profile-dropdown .dropdown-item i {
            margin-right: 10px;
            color: var(--primary-color);
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
        .stat-card.success {
            background: linear-gradient(135deg, #28a745, #38b755);
            color: white;
        }
        .stat-card.danger {
            background: linear-gradient(135deg, #dc3545, #ec4555);
            color: white;
        }
        .client-status-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .client-status-badge.active {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        .client-status-badge.inactive {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
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
                <a class="nav-link" href="dashboard.php">
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
                <a class="nav-link active" href="clients.php">
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
                <h1 class="fs-4 m-0">Gestion des Clients</h1>
            </div>
            <div class="dropdown">
                <!-- <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false"> -->
                    <!-- <img src="https://via.placeholder.com/150" alt="Admin" class="user-avatar"> -->
                    <!-- <span class="d-none d-md-inline me-2">Admin</span> -->
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow profile-dropdown" aria-labelledby="dropdownUser">
                    <li>
                        <!-- <div class="profile-header">
                            <h6>Admin</h6>
                            <small>Administrateur</small>
                        </div> -->
                    </li>
                    <li><a class="dropdown-item" href="admin_profil.php"><i class="bi bi-person-circle"></i> Mon profil</a></li>
                    <!-- <li><a class="dropdown-item" href="admin_parametres.php"><i class="bi bi-gear"></i> Paramètres</a></li> -->
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
            <div class="col-md-6">
                <div class="stat-card primary">
                    <div class="card-body">
                        <i class="bi bi-people card-icon"></i>
                        <h5 class="card-title">Clients actifs</h5>
                        <div class="card-value"><?php echo $count_active_clients; ?></div>
                        <p class="card-subtitle">Utilisateurs enregistrés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card danger">
                    <div class="card-body">
                        <i class="bi bi-person-slash card-icon"></i>
                        <h5 class="card-title">Clients désactivés</h5>
                        <div class="card-value"><?php echo $count_inactive_clients; ?></div>
                        <p class="card-subtitle">Comptes inactifs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Liste des clients</h5>
                <div>
                    <?php if ($show_inactive): ?>
                        <a href="clients.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-check"></i> Afficher les clients actifs
                        </a>
                    <?php else: ?>
                        <a href="clients.php?show_inactive=1" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-person-x"></i> Afficher les clients désactivés
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="clientsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Date d'inscription</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($clients_result->num_rows > 0): ?>
                                <?php while ($client = $clients_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $client['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($client['nom_complet']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($client['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($client['telephone'] ?? 'Non renseigné'); ?></td>
                                        <td>
                                            <?php 
                                                if (isset($client['date_inscription'])) {
                                                    $date = new DateTime($client['date_inscription']);
                                                    echo $date->format('d/m/Y'); 
                                                } else {
                                                    echo 'Non disponible';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($client['active']): ?>
                                                <span class="client-status-badge active">Actif</span>
                                            <?php else: ?>
                                                <span class="client-status-badge inactive">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $client['id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <?php if ($client['active']): ?>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal<?php echo $client['id']; ?>">
                                                    <i class="bi bi-person-dash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="clients_admin.php?activate_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-person-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal pour voir les détails du client -->
                                    <div class="modal fade" id="viewModal<?php echo $client['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $client['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel<?php echo $client['id']; ?>">
                                                        Détails du client
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p><strong>Nom :</strong> <?php echo htmlspecialchars($client['nom_complet']); ?></p>
                                                            <p><strong>Email :</strong> <?php echo htmlspecialchars($client['Email']); ?></p>
                                                            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($client['telephone'] ?? 'Non renseigné'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Statut :</strong> 
                                                                <?php if ($client['active']): ?>
                                                                    <span class="client-status-badge active">Actif</span>
                                                                <?php else: ?>
                                                                    <span class="client-status-badge inactive">Inactif</span>
                                                                <?php endif; ?>
                                                            </p>
                                                            <p>
                                                                <strong>Date d'inscription :</strong> 
                                                                <?php 
                                                                    if (isset($client['date_inscription'])) {
                                                                        $date = new DateTime($client['date_inscription']);
                                                                        echo $date->format('d/m/Y'); 
                                                                    } else {
                                                                        echo 'Non disponible';
                                                                    }
                                                                ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php
                                                    // Récupérer les réservations du client
                                                    $stmt = $cnx->prepare("SELECT * FROM reservations WHERE email = ? ORDER BY heure DESC LIMIT 5");
                                                    $stmt->bind_param("s", $client['Email']);
                                                    $stmt->execute();
                                                    $reservations = $stmt->get_result();
                                                    ?>
                                                    
                                                    <div class="mt-4">
                                                        <h6>Dernières réservations</h6>
                                                        <?php if ($reservations->num_rows > 0): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Date</th>
                                                                            <th>Personnes</th>
                                                                            <th>Statut</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php while($reservation = $reservations->fetch_assoc()): ?>
                                                                            <tr>
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
                                                                            </tr>
                                                                        <?php endwhile; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-muted">Aucune réservation trouvée pour ce client.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    <a href="mailto:<?php echo htmlspecialchars($client['Email']); ?>" class="btn btn-primary">
                                                        <i class="bi bi-envelope"></i> Contacter
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal pour désactiver un client -->
                                    <?php if ($client['active']): ?>
                                    <div class="modal fade" id="deactivateModal<?php echo $client['id']; ?>" tabindex="-1" aria-labelledby="deactivateModalLabel<?php echo $client['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deactivateModalLabel<?php echo $client['id']; ?>">Confirmer la désactivation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Êtes-vous sûr de vouloir désactiver le compte de <strong><?php echo htmlspecialchars($client['nom_complet']); ?></strong> ?</p>
                                                    <p>Cet utilisateur ne pourra plus se connecter à son compte tant qu'il n'aura pas été réactivé.</p>
                                                    <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Cette action peut être annulée ultérieurement.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <a href="clients.php?delete_id=<?php echo $client['id']; ?>" class="btn btn-danger">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <?php if ($show_inactive): ?>
                                            Aucun client désactivé trouvé.
                                        <?php else: ?>
                                            Aucun client actif trouvé.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.2.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.0/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide toast notifications after 5 seconds
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
            
            // Initialize DataTable
            if (document.getElementById('clientsTable')) {
                new DataTable('#clientsTable', {
                    language: {
                        processing:     "Traitement en cours...",
                        search:         "Rechercher&nbsp;:",
                        lengthMenu:     "Afficher _MENU_ éléments",
                        info:           "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
                        infoEmpty:      "Affichage de l'élément 0 à 0 sur 0 élément",
                        infoFiltered:   "(filtré de _MAX_ éléments au total)",
                        infoPostFix:    "",
                        loadingRecords: "Chargement en cours...",
                        zeroRecords:    "Aucun élément à afficher",
                        emptyTable:     "Aucune donnée disponible dans le tableau",
                        paginate: {
                            first:      "Premier",
                            previous:   "Précédent",
                            next:       "Suivant",
                            last:       "Dernier"
                        },
                        aria: {
                            sortAscending:  ": activer pour trier la colonne par ordre croissant",
                            sortDescending: ": activer pour trier la colonne par ordre décroissant"
                        }
                    },
                    order: [[0, 'desc']], // Trier par ID (colonne 0) par défaut, du plus récent au plus ancien
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100]
                });
            }
        });
    </script>
</body>
</html>