<?php
include('cnx.php');
session_start();

// Vérification de l'authentification et du type d'utilisateur
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: signin.php");
    exit();
}

// Gérer la suppression de message
$notification = '';
$notificationType = '';

if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Préparer et exécuter la requête de suppression
    $stmt = $cnx->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $notification = "Message supprimé avec succès";
        $notificationType = "success";
    } else {
        $notification = "Erreur lors de la suppression: " . $cnx->error;
        $notificationType = "danger";
    }
}

// Récupération des messages
$stmt = $cnx->prepare("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
$stmt->execute();
$messages = $stmt->get_result();

// Compter le nombre total de messages
$stmt = $cnx->prepare("SELECT COUNT(*) as count FROM contact_messages");
$stmt->execute();
$result = $stmt->get_result();
$count_messages = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages - Le Gourmet</title>
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.2.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Reprendre le même style que votre dashboard */
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
        .navbar-admin {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 30px;
            margin-bottom: 30px;
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        .message-card {
            transition: all 0.3s;
        }
        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .message-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eee;
            border-radius: 10px 10px 0 0;
        }
        .message-content {
            padding: 20px;
        }
        .message-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .message-actions {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
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
                <a class="nav-link active" href="messages.php">
                    <i class="bi bi-chat-left-text"></i> Messages
                    <span class="badge bg-danger rounded-pill"><?php echo $count_messages; ?></span>
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link" href="admin_parametres.php">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li>
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
                <h1 class="fs-4 m-0">Messages des clients</h1>
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

        <!-- Stats Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total des messages</h5>
                                <h2 class="display-4 fw-bold mb-0"><?php echo $count_messages; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-chat-left-text-fill" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Tous les messages</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="messagesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($messages->num_rows > 0): ?>
                                <?php while ($message = $messages->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $message['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($message['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td>
                                            <?php 
                                                // Afficher un aperçu du message limité à 50 caractères
                                                $preview = htmlspecialchars(substr($message['message'], 0, 50));
                                                echo $preview . (strlen($message['message']) > 50 ? '...' : '');
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $date = new DateTime($message['submitted_at']);
                                                echo $date->format('d/m/Y H:i'); 
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $message['id']; ?>">
                                                <i class="bi bi-eye"></i> Voir
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $message['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal pour voir le message complet -->
                                    <div class="modal fade" id="viewModal<?php echo $message['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $message['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel<?php echo $message['id']; ?>">
                                                        Message de <?php echo htmlspecialchars($message['name']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <p><strong>De:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</p>
                                                        <p><strong>Date:</strong> 
                                                            <?php 
                                                                $date = new DateTime($message['submitted_at']);
                                                                echo $date->format('d/m/Y H:i'); 
                                                            ?>
                                                        </p>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-header">
                                                            Message
                                                        </div>
                                                        <div class="card-body">
                                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                                        <i class="bi bi-reply"></i> Répondre par email
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal pour supprimer -->
                                    <div class="modal fade" id="deleteModal<?php echo $message['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $message['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $message['id']; ?>">Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Êtes-vous sûr de vouloir supprimer ce message de <strong><?php echo htmlspecialchars($message['name']); ?></strong> ?</p>
                                                    <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Cette action est irréversible.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <a href="messages.php?delete_id=<?php echo $message['id']; ?>" class="btn btn-danger">Supprimer</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun message reçu</td>
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
            if (document.getElementById('messagesTable')) {
                new DataTable('#messagesTable', {
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
                    order: [[4, 'desc']], // Trier par date (colonne 4) par défaut, du plus récent au plus ancien
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100]
                });
            }
        });
    </script>
</body>
</html>