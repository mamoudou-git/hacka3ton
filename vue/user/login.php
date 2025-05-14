<?php
$pageTitle = "Connexion";
require_once '../../vue/layout/header.php';

// Vérifier si l'utilisateur n'est pas déjà connecté
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}
?>

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

        <form action="../../controllers/AuthController.php?action=login" method="POST" id="login-form">
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

<?php require_once '../../vue/layout/footer.php'; ?>
