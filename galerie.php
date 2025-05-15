<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Layout</title>
    <script src="../assets/js/color-modes.js"></script>
    <meta name="description" content="A responsive image gallery layout">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/carousel/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital@1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Bodoni Moda', serif;
            font-style: italic;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        .gallery-item {
            position: relative;
            flex: 1;
            min-width: 300px;
            max-width: 45%;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .gallery-item:first-child10 {
            max-width: 65%;
        }

        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover::after {
            opacity: 1;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .gallery {
                flex-direction: column;
                gap: 15px;
            }

            .gallery-item,
            .gallery-item:first-child {
                max-width: 100%;
                margin: 0 auto;
            }

            .gallery-item img {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .gallery-item {
                min-width: 100%;
            }

            .gallery-item img {
                height: 200px;
            }

            .gallery-container {
                padding: 20px 10px;
            }
        }

        .gallery:nth-child(1) { animation-delay: 0.1s; }
        .gallery:nth-child(2) { animation-delay: 0.2s; }
        .gallery:nth-child(3) { animation-delay: 0.3s; }
        .gallery:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
<?php session_start(); ?>
    <?php include('header.php'); ?>
    <br><br>
    <div class="gallery-container">
        <div class="gallery">
            <div class="gallery-item">
                <img src="pictures/galerie.0.jpg" alt="Gallery Image 1">
            </div>
            <div class="gallery-item">
                <img src="pictures/pexels-pixabay-262978.jpg" alt="Restaurant interior with tables">
            </div>
        </div>

        <div class="gallery">
            <div class="gallery-item">
                <img src="pictures/galerie.17.jpg" alt="Gallery Image 4">
            </div>
            <div class="gallery-item">
                <img src="pictures/galerie.3.jpg" alt="Gallery Image 3">
            </div>
        </div>

        <div class="gallery">
            <div class="gallery-item">
                <img src="pictures/galerie.5.jpg" alt="Gallery Image 5">
            </div>
            <div class="gallery-item">
                <img src="pictures/pexels-reneterp-2544830.jpg" alt="Outdoor dining area">
            </div>
        </div>

        <div class="gallery">
            <div class="gallery-item">
                <img src="pictures/galerie.7.webp" alt="Gallery Image 7">
            </div>
            <div class="gallery-item">
                <img src="pictures/galerie.8.webp" alt="Gallery Image 8">
            </div>
        </div>
    </div>
</body>
</html>