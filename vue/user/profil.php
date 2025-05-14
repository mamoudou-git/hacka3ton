<?php
$pageTitle = "Mon Profil";
require_once 'vue/layout/header.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    redirect('login');
}
?>

<div class="container">
    <div class="profile-container">
        <!-- Informations du profil -->
        <div class="profile-card">
            <div class="profile-avatar">
                <img src="assets/images/default-avatar.png" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
            </div>
            <h2 style="text-align: center; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </h2>
            <div style="background: #f8f8f8; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                <p><strong>Email:</strong><br><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div style="background: #f8f8f8; padding: 15px; border-radius: 4px;">
                <p><strong>Rôle:</strong><br><?php echo htmlspecialchars($_SESSION['role']); ?></p>
            </div>
        </div>

        <!-- Statistiques et activités -->
        <div class="profile-card">
            <h3 style="margin-bottom: 20px;">Mes Signalements</h3>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <p>Total</p>
                    <div class="stat-number">0</div>
                </div>
                <div class="stat-box">
                    <p>En cours</p>
                    <div class="stat-number">0</div>
                </div>
                <div class="stat-box">
                    <p>Résolus</p>
                    <div class="stat-number">0</div>
                </div>
            </div>

            <h3 style="margin: 30px 0 20px;">Derniers Signalements</h3>
            <div style="background: #f8f8f8; padding: 20px; border-radius: 4px; text-align: center;">
                <p style="color: #666;">Aucun signalement pour le moment</p>
                <a href="#" class="btn" style="margin-top: 15px; display: inline-block; padding: 8px 20px;">
                    Créer un signalement
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'vue/layout/footer.php'; ?>
