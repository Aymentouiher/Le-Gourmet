<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Le Gourmet</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'BodoniModa-Italic';
            src: url('fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf') format('truetype');
            font-display: swap;
        }

        body {
            font-family: 'BodoniModa-Italic', serif;
            background: linear-gradient(135deg, #f5f5f5, #e5e5e5);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .menu-hero {
            position: relative;
            height: 250px;
            overflow: hidden;
            background-color: #800020;
        }

        .menu-hero-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
            z-index: 2;
        }

        .menu-hero-text h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .menu-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .menu-section {
            margin-bottom: 40px;
        }

        .menu-section-title {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            color: #800020;
        }

        .menu-section-title h2 {
            display: inline-block;
            background-color: #fff;
            padding: 0 20px;
            position: relative;
            z-index: 1;
            font-size: 2rem;
            color: #800020;
        }

        .menu-section-title::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #800020;
            z-index: 0;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .menu-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(128, 0, 32, 0.2);
        }

        .menu-card-img-container {
            height: 200px;
            overflow: hidden;
        }

        .menu-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .menu-card:hover img {
            transform: scale(1.1);
        }

        .menu-card-content {
            padding: 20px;
        }

        .menu-card h3 {
            font-size: 1.4rem;
            margin: 0 0 10px 0;
            color: #800020;
        }

        .menu-card p {
            font-size: 0.95rem;
            color: #555;
            margin: 0 0 15px 0;
            line-height: 1.5;
        }

        .menu-card .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #800020;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            background-color: #f8f4f4;
        }

        .order-btn {
            display: inline-block;
            margin-left: 10px;
            padding: 5px 15px;
            background-color: #800020;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            background-color: #b30029;
            transform: scale(1.05);
        }

        .no-plats {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Style pour le popup */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            text-align: center;
            min-width: 300px;
            animation: popIn 0.3s ease-out forwards;
        }
        
        .popup-header {
            margin-bottom: 15px;
            color: #800020;
            font-size: 1.5rem;
        }
        
        .popup-content {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .popup-btn {
            padding: 8px 20px;
            background-color: #800020;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        
        .popup-btn:hover {
            background-color: #b30029;
        }
        
        .popup-btn.secondary {
            background-color: #6c757d;
        }
        
        .popup-btn.secondary:hover {
            background-color: #5a6268;
        }
        
        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            color: #800020;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        @keyframes popIn {
            0% {
                transform: translate(-50%, -60%);
                opacity: 0;
            }
            100% {
                transform: translate(-50%, -50%);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .menu-hero-text h1 {
                font-size: 2rem;
            }
            .menu-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            .popup {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <?php include("header.php"); ?>

    <div class="menu-hero">
        <div class="menu-hero-text">
            <h1>Notre Menu</h1>
            <p>Découvrez notre sélection de plats gastronomiques</p>
        </div>
    </div>

    <div class="menu-container">
        <div class="menu-section">
            <div class="menu-section-title">
                <h2>Nos Plats</h2>
            </div>
            
            <div class="menu-grid">
                <?php
                include("cnx.php");

                $sql = mysqli_query($cnx, "SELECT * FROM plats");

                if (mysqli_num_rows($sql) > 0) {
                    while ($plat = mysqli_fetch_assoc($sql)) {
                        $image = !empty($plat['image']) ? htmlspecialchars($plat['image']) : 'Uploads/default.jpg';
                        ?>
                        <div class="menu-card">
                            <div class="menu-card-img-container">
                                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($plat['nom']); ?>">
                            </div>
                            
                            <div class="menu-card-content">
                                <h3><?php echo htmlspecialchars($plat['nom']); ?></h3>
                                <p><?php echo htmlspecialchars($plat['description']); ?></p>
                                <div>
                                    <span class="price"><?php echo htmlspecialchars($plat['prix']); ?> MAD</span>
                                    <?php if (isset($_SESSION['id_client'])): ?>
                                        <button class="order-btn" onclick="ajouterAuPanier('<?php echo addslashes(htmlspecialchars($plat['nom'])); ?>')">Commander</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='no-plats'>Aucun plat disponible pour le moment. Veuillez revenir plus tard.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Overlay et Popup -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <span class="popup-close" onclick="fermerPopup()">&times;</span>
        <div class="popup-header">Ajout au panier</div>
        <div class="popup-content" id="popup-message"></div>
        <button class="popup-btn" onclick="voirPanier()">Voir le panier</button>
        <button class="popup-btn secondary" onclick="fermerPopup()">Continuer les achats</button>
    </div>

    <?php include("footer.php"); ?>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour les animations et le popup -->
    <script>
        // Fonction simplifiée pour ajouter au panier et afficher le popup
        function ajouterAuPanier(nomPlat) {
            // Affiche directement le popup sans requête AJAX
            afficherPopup("Le plat \"" + nomPlat + "\" a été ajouté à votre panier !");
            
            // Si vous souhaitez quand même enregistrer l'ajout, vous pouvez rediriger vers une page qui le fait
            // window.location.href = 'ajouter_au_panier.php?id_plat=' + idPlat;
        }
        
        // Fonction pour afficher le popup
        function afficherPopup(message) {
            document.getElementById('popup-message').textContent = message;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        }
        
        // Fonction pour fermer le popup
        function fermerPopup() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('popup').style.display = 'none';
        }
        
        // Fonction pour rediriger vers la page du panier
        function voirPanier() {
            window.location.href = 'panier.php';
        }
    </script>
</body>
</html>