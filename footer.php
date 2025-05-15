<!DOCTYPE html>
<html lang="en">
<head>
<!-- <?php session_start();  ?> -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Footer Example</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .footer {
            background-color: #060834; 
            color: #fff;
            padding: 20px 0;
        }

        .footer-container {
            display: flex;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .footer-section {
            flex: 1;
            padding: 10px;
        }

        .footer-section h4 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .footer-section p, .footer-section ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .footer-section ul li {
            margin: 5px 0;
        }

        .footer-section a {
            color: #fff;
            text-decoration: none;
        }

        .footer-section a:hover {
            text-decoration: underline;
        }

        .social-media {
            display: flex;
        }

        .social-media li {
            margin-right: 10px;
        }

        .footer-bottom {
            text-align: center;
            padding: 10px 0;
        }

        .footer-bottom p {
            margin: 0;
            font-size: 14px;
        }
            .social-media {
            display: flex;
            gap: 15px;
            list-style: none;
            padding: 0;
        }
        .social-media li {
            display: inline-block;
        }
        
    </style>
</head>
<body>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="ss">À Propos de Nous.</h4>
                <p class="ss">Chez Le Gourmet, on cuisine avec amour pour vous offrir un bon repas.</p>
            </div>
            <div class="footer-section">
                <h4 class="ss">Liens rapides</h4>
                <ul>
                    <li><a href="#services" class="ss">Services</a></li>
                    <li><a href="#about" class="ss">A propos de nous</a></li>
                    <li><a href="#contact" class="ss">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4 class="ss">Contacter nous</h4>
                <p class="ss">Email: thraymen031@gmail.com</p>
                <p class="ss">contact: +212 670251030</p>
            </div>
            <div class="footer-section">
                <h4 class="AA" style="color: white;">Suivre nous</h4>
                <ul class="social-media">
                    <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                    <li><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li> 
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="ss">&copy; 2025 Le Gourmet. Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>
