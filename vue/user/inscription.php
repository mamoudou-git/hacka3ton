<?php
$pageTitle = "Inscription";
require_once '../../vue/layout/header.php';

// Vérifier si l'utilisateur n'est pas déjà connecté
//session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Inscription</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/AuthController.php?action=register" method="POST" id="inscription-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required 
                       minlength="3" pattern="[A-Za-z0-9]+" 
                       title="Lettres et chiffres uniquement">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required 
                       minlength="8" 
                       title="8 caractères minimum">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">S'inscrire</button>
        </form>

        <p class="text-center" style="margin-top: 20px;">
            Déjà inscrit ? <a href="http://localhost/Projet_hackaton/vue/user/login.php" class="link">Connectez-vous</a>
        </p>
    </div>
</div>

<script>
document.getElementById('inscription-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas !');
    }
});
</script>

<?php require_once '../../vue/layout/footer.php'; ?>
