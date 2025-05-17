<?php
session_start();
$pageTitle = "Connexion";
require_once __DIR__ . '/../../config/config.php';

// Débogage - Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Urban Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4CAF50;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 40px 0;
        }

        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }

        .form-title {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn {
            background: var(--accent-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 1.1rem;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            color: #fff;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <a href="../../public/index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>

    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Connexion</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>controllers/AuthController.php?action=login" method="POST" id="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>

            <p class="text-center" style="margin-top: 20px;">
                Pas encore de compte ? <a href="inscription.php" class="link">Inscrivez-vous</a>
            </p>
        </div>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if (!email || !password) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs !');
        }
    });
    </script>
</body>
</html>
