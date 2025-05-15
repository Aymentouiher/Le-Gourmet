<?php
session_start();
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Le Gourmet - Restaurant gastronomique français offrant une expérience culinaire unique avec des plats traditionnels revisités par nos chefs renommés">
    <meta name="keywords" content="restaurant français, gastronomie, cuisine française, Le Gourmet, fine dining">
    <meta name="author" content="Le Gourmet">
    <title>Le Gourmet - Restaurant Gastronomique Français</title>

    <!-- Favicon et liens CSS -->
    <link rel="icon" href="pictures/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/carousel.css" rel="stylesheet">
    <link rel="stylesheet" href="index2.css">
    <link rel="preload" href="fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf" as="font" type="font/ttf" crossorigin>

<style>
        @font-face {
            font-family: "BodoniModa-Italic";
            src: url("fonts/BodoniModa-Italic-VariableFont_opsz,wght.ttf") format("truetype");
            font-weight: normal;
            font-style: italic;
            font-display: swap;
        }

        body {
            font-family: "BodoniModa-Italic", serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        .hero-container {
    position: relative;
    overflow: hidden;
    height: 500px;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 8s ease;
}

.hero-container:hover .hero-image {
    transform: scale(1.05);
}

/* Nouveau design pour la bannière de bienvenue */
.welcome-banner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 2;
    width: 80%;
    max-width: 600px;
}

.welcome-text {
    font-family: "BodoniModa-Italic", serif;
    color: #fff;
    font-size: 42px;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin: 0;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    position: relative;
    display: inline-block;
    padding: 0 15px;
}

.welcome-text::before,
.welcome-text::after {
    content: "";
    position: absolute;
    height: 3px;
    background-color: #fff;
    width: 0;
    transition: width 0.8s ease;
}

.welcome-text::before {
    top: -10px;
    left: 0;
}

.welcome-text::after {
    bottom: -10px;
    right: 0;
}

.hero-container:hover .welcome-text::before,
.hero-container:hover .welcome-text::after {
    width: 100%;
}

.welcome-subtitle {
    font-family: "BodoniModa", serif;
    color: #fff;
    font-size: 18px;
    margin-top: 20px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.8s ease 0.3s;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
}

.hero-container:hover .welcome-subtitle {
    opacity: 1;
    transform: translateY(0);
}

.welcome-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(128,0,32,0.5));
    z-index: 1;
}

@media (max-width: 768px) {
    .welcome-text {
        font-size: 32px;
        letter-spacing: 2px;
    }
    
    .welcome-subtitle {
        font-size: 16px;
        margin-top: 15px;
    }
    
    .hero-container {
        height: 350px;
    }
}
        #ps {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #800020;
            font-size: 36px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.7);
            padding: 15px 30px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .hero-container:hover #ps {
            letter-spacing: 4px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .AA {
            font-family: "BodoniModa", serif;
            color: #333;
            line-height: 1.6;
        }

        h2.AA {
            font-family: "BodoniModa-Italic", serif;
            color: #800020;
            margin-bottom: 20px;
        }

        .col-lg-4 {
            margin-top: 20px;
            text-align: center;
            padding: 0 25px;
        }

        .chef-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .bd-placeholder-img.rounded-circle {
            width: 140px;
            height: 140px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 15px;
            border: 3px solid #800020;
        }

        .bd-placeholder-img.rounded-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(128, 0, 32, 0.3);
        }

        .featurette {
            margin: 60px 0;
            align-items: center;
        }

        .featurette-image {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            width: 500px;
            height: 500px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.5s ease;
        }
        
        .featurette-image:hover {
            transform: translateY(-10px);
        }

        /* Style pour les modals */
        .modal-content {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
            border: none;
        }

        .modal-header {
            background-color: #800020;
            color: white;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }

        .modal-body {
            padding: 30px;
            font-family: "BodoniModa", serif;
            color: #333;
        }

        .modal-body img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #800020;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .chef-specialty {
            display: inline-block;
            background-color: #f8f4f4;
            padding: 5px 15px;
            border-radius: 20px;
            margin-bottom: 15px;
            color: #800020;
            font-weight: bold;
        }

        /* Animation pour l'apparition des sections */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            #ps {
                font-size: 24px;
                padding: 10px 20px;
            }
            .hero-container {
                height: 300px;
            }
            .featurette-image {
                width: 100%;
                height: auto;
                max-height: 350px;
            }
            .bd-placeholder-img.rounded-circle {
                width: 120px;
                height: 120px;
            }
            .modal-body img {
                width: 150px;
                height: 150px;
            }
            .featurette {
                text-align: center;
            }
            .col-md-5, .col-md-7 {
                margin-bottom: 30px;
            }
        }
 </style>
  
</head>
<body>
    <?php include "header.php"; ?>

    <main>
        <!-- Remplacer l'ancien bloc hero par ce code -->
        <div class="hero-container">
            <div class="welcome-overlay"></div>
            <img class="hero-image" src="pictures/img23.jpg" alt="Façade extérieure du restaurant Le Gourmet">
            <div class="welcome-banner">
                <h1 class="welcome-text">Le Gourmet</h1>
                <p class="welcome-subtitle">Une expérience gastronomique française d'exception</p>
            </div>
        </div>

        <div class="container marketing">
            <!-- Section des chefs -->
            <div class="row animate-on-scroll">
                <div class="col-lg-4">
                    <div class="chef-container">
                        <img src="pictures/chef1.jpg" class="bd-placeholder-img rounded-circle" width="140" height="140" alt="Chef Pierre Dubois" data-bs-toggle="modal" data-bs-target="#chef1Modal" aria-label="Profil du Chef Pierre Dubois">
                        <p class="AA">Maître des saveurs, il réinvente la tradition avec audace et passion.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chef-container">
                        <img src="pictures/chef2.jpg" class="bd-placeholder-img rounded-circle" width="140" height="140" alt="Chef Élise Moreau" data-bs-toggle="modal" data-bs-target="#chef2Modal" aria-label="Profil du Chef Élise Moreau">
                        <p class="AA">Artiste culinaire, elle sublime les produits simples en plats d'exception.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chef-container">
                        <img src="pictures/chef3.jpg" class="bd-placeholder-img rounded-circle" width="140" height="140" alt="Chef Julien Lefèvre" data-bs-toggle="modal" data-bs-target="#chef3Modal" aria-label="Profil du Chef Julien Lefèvre">
                        <p class="AA">Expert en gastronomie, il allie précision et créativité pour votre plaisir.</p>
                    </div>
                </div>
            </div>

            <!-- Modals pour les profils des chefs -->
            <!-- Chef 1 Modal -->
            <div class="modal fade" id="chef1Modal" tabindex="-1" aria-labelledby="chef1ModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="chef1ModalLabel">Chef Pierre Dubois</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="pictures/chef1.jpg" alt="Chef Pierre Dubois">
                            <p class="chef-specialty">Spécialité : Cuisine française revisitée</p>
                            <p>Avec plus de 20 ans d'expérience, Pierre Dubois est un maître dans l'art de réinventer les classiques. Passionné par les produits du terroir, il apporte une touche d'audace à chaque plat, transformant les saveurs traditionnelles en expériences modernes et mémorables.</p>
                            <p><em>« La cuisine est un art où la tradition rencontre l'innovation. Mon but est de vous faire voyager à travers la France avec chaque bouchée. »</em></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chef 2 Modal -->
            <div class="modal fade" id="chef2Modal" tabindex="-1" aria-labelledby="chef2ModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="chef2ModalLabel">Chef Élise Moreau</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="pictures/chef2.jpg" alt="Chef Élise Moreau">
                            <p class="chef-specialty">Spécialité : Pâtisserie et plats végétariens</p>
                            <p>Élise Moreau est une artiste dans l'âme. Elle excelle à transformer des ingrédients simples en créations extraordinaires, avec une prédilection pour les desserts raffinés et les plats végétariens qui surprennent par leur équilibre et leur beauté.</p>
                            <p><em>« Créer un dessert parfait, c'est comme composer une symphonie où chaque note doit être en harmonie avec les autres. »</em></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chef 3 Modal -->
            <div class="modal fade" id="chef3Modal" tabindex="-1" aria-labelledby="chef3ModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="chef3ModalLabel">Chef Julien Lefèvre</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="pictures/chef3.jpg" alt="Chef Julien Lefèvre">
                            <p class="chef-specialty">Spécialité : Cuisine fusion franco-méditerranéenne</p>
                            <p>Julien Lefèvre allie précision et créativité dans chacune de ses assiettes. Inspiré par les saveurs méditerranéennes, il fusionne les techniques françaises avec des influences du sud pour offrir des plats vibrants et uniques.</p>
                            <p><em>« La Méditerranée est ma muse, la France ma technique. Cette fusion crée des saveurs que vous ne trouverez nulle part ailleurs. »</em></p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="featurette-divider">

            <!-- Section des featurettes -->
            <div class="row featurette animate-on-scroll">
                <div class="col-md-7">
                    <h2 class="AA">Notre Ambition</h2>
                    <p class="AA">À Le Gourmet, nous voulons révolutionner la cuisine française en alliant terroir et modernité. Nos plats, simples mais raffinés, rendent la gastronomie accessible dans une ambiance chaleureuse et conviviale.</p>
                    <p class="AA">Nous sélectionnons méticuleusement nos ingrédients auprès de producteurs locaux pour garantir fraîcheur et qualité exceptionnelle. Notre objectif est de créer une expérience culinaire qui éveille vos sens et vous laisse des souvenirs impérissables.</p>
                </div>
                <div class="col-md-5">
                    <img src="pictures/rest.9.jpg" class="featurette-image img-fluid mx-auto" width="500" height="500" alt="Vue du rooftop Le Gourmet">
                </div>
            </div>

            <hr class="featurette-divider">

            <div class="row featurette animate-on-scroll">
                <div class="col-md-7 order-md-2">
                    <h2 class="AA">Que veut dire Le Gourmet ?</h2>
                    <p class="AA">Le Gourmet incarne l'élégance culinaire française : un hommage aux traditions, enrichi d'une touche créative. Chaque plat célèbre des saveurs raffinées pour un plaisir gustatif inoubliable.</p>
                    <p class="AA">Plus qu'un simple restaurant, Le Gourmet est une philosophie, une célébration de l'art de vivre à la française où chaque repas devient un moment précieux à savourer pleinement, en bonne compagnie.</p>
                </div>
                <div class="col-md-5 order-md-1">
                    <img src="pictures/rest.10.jpg" class="featurette-image img-fluid mx-auto" width="500" height="500" alt="Détail d'un plat raffiné">
                </div>
            </div>

            <hr class="featurette-divider">

            <div class="row featurette animate-on-scroll">
                <div class="col-md-7">
                    <h2 class="AA">Notre Vision</h2>
                    <p class="AA">Nous voyons la cuisine comme un art qui éveille les sens. Avec des ingrédients authentiques et un savoir-faire unique, nous créons des plats pour inspirer et rassembler autour d'un repas mémorable.</p>
                    <p class="AA">Chaque saison nous offre de nouvelles opportunités de créativité, c'est pourquoi notre carte évolue régulièrement pour mettre en valeur les meilleurs produits du moment et vous surprendre à chaque visite.</p>
                </div>
                <div class="col-md-5">
                    <img src="pictures/rest.11.jpg" class="featurette-image img-fluid mx-auto" width="500" height="500" alt="Présentation élégante d'un plat">
                </div>
            </div>

            <hr class="featurette-divider">
            
            <!-- Section de réservation rapide -->
            <div class="row text-center animate-on-scroll py-5">
                <div class="col-12">
                    <h2 class="AA mb-4">Réservez votre table</h2>
                    <p class="AA mb-4">Offrez-vous une expérience gastronomique inoubliable</p>
                    <a href="reservation.php" class="btn btn-lg" style="background-color: #800020; color: white; transition: all 0.3s ease;">Réserver maintenant</a>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </main>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/color-modes.js"></script>
    
    <!-- Script pour les animations au défilement -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.animate-on-scroll');
            
            function checkInView() {
                animateElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add('visible');
                    }
                });
            }
            
            // Vérifier au chargement initial
            checkInView();
            
            // Vérifier au défilement
            window.addEventListener('scroll', checkInView);
        });
    </script>
</body>
</html>