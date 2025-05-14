<?php
// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si aucun titre n'est défini, on met un titre par défaut
if (!isset($pageTitle)) {
    $pageTitle = "Urban Report";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Urban Report</title>
    
    <!-- Lien vers notre fichier CSS personnalisé -->
    <link rel="stylesheet" href="http://localhost/Projet_hackaton/public/assets/style.css">
    
    <!-- Icônes Font Awesome (version gratuite) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Style pour le header et la navigation */
        .header {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #666;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            background: #f0f0f0;
            color: #333;
        }

        /* Style pour le menu mobile */
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
                display: none;
                width: 100%;
                position: absolute;
                top: 70px;
                left: 0;
                background: white;
                padding: 20px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .nav-links.active {
                display: flex;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête du site -->
    <header class="header">
        <nav class="nav container">
            

            <!-- Bouton menu mobile -->
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>

            <!-- Liens de navigation -->
            <div class="nav-links">
                <a href="http://localhost/Projet_hackaton/public/index.php">Accueil</a>
                <a href="http://localhost/Projet_hackaton/vue/signalements/creation.php">Signaler</a>
                <a href="http://localhost/Projet_hackaton/vue/dashboard/carte.php">Carte</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu utilisateur connecté -->
                    <a href="<?php echo getRoute('profil'); ?>">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                    <a href="<?php echo getRoute('logout'); ?>" style="color: #dc3545;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                <?php else: ?>
                    <!-- Menu visiteur -->
                    <a href="http://localhost/Projet_hackaton/vue/user/login.php">Connexion</a>
                    <a href="http://localhost/Projet_hackaton/vue/user/inscription.php">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Script pour le menu mobile -->
   <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.nav-links').classList.toggle('active');
            });
        });
    </script>

    <!-- Début du contenu principal -->
    <main>
