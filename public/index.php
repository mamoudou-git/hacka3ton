<?php
session_start();
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amélioration Urbaine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4CAF50;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        .banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
            flex: 1;
            display: flex;
            align-items: center;
        }

        .banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') center/cover;
            opacity: 0.2;
            z-index: 0;
        }

        .banner-content {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .title-box {
            background: rgba(30, 60, 114, 0.8);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .buttons-container {
            margin-top: 40px;
        }

        .btn-custom {
            padding: 15px 40px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin: 10px;
            display: inline-block;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-custom:hover::before {
            left: 100%;
        }

        .btn-join {
            background: var(--accent-color);
            color: white;
            border: none;
            margin-right: 20px;
        }

        .btn-join:hover {
            background: #45a049;
            color: white;
            transform: translateY(-2px);
        }

        .btn-login {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-login:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .features {
            padding: 60px 0;
            background: white;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .feature-box {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 30px 0;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .banner {
                padding: 60px 0;
            }
            .btn-custom {
                margin: 10px;
                width: 80%;
            }
            .title-box {
                margin: 20px;
            }
        }

        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-floating alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Bannière principale -->
    <div class="banner">
        <div class="container">
            <div class="banner-content text-center">
                <div class="title-box">
                    <h1 class="display-4 mb-4">Ensemble, Améliorons Notre Ville</h1>
                    <p class="lead mb-0">Signalez facilement les problèmes urbains et contribuez à l'amélioration de votre environnement</p>
                </div>
                <div class="buttons-container">
                    <a href="http://localhost/Projet_hackaton2/vue/user/inscription.php" class="btn btn-custom btn-join">
                        <i class="fas fa-user-plus me-2"></i>Rejoindre
                    </a>
                    <a href="http://localhost/Projet_hackaton2/vue/user/login.php" class="btn btn-custom btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Connexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Caractéristiques -->
    <section class="features">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-city feature-icon"></i>
                        <h3>Vision Urbaine</h3>
                        <p>Pour des villes guinéennes plus intelligentes et durables</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-hands-helping feature-icon"></i>
                        <h3>Participation Citoyenne</h3>
                        <p>Votre voix compte dans l'amélioration de notre communauté</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3>Suivi en Temps Réel</h3>
                        <p>Suivez l'évolution des améliorations urbaines</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer amélioré -->
    <footer class="footer">
        <div class="container text-center">
            <h4>Pour des villes guinéennes meilleures</h4>
            <p class="mb-3">Rejoignez-nous dans cette initiative citoyenne</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="mailto:balde@gmail.com"><i class="fas fa-envelope"></i></a>
            </div>
            <div class="mt-3">
                <small>Contact : balde@gmail.com | Tel: +224 627 46 49 95</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 