<?php
session_start();
$pageTitle = "Mon Profil";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost/Projet_hackaton2/vue/user/login.php');
    exit();
}

// Récupération des données de l'utilisateur
global $db;
$stmt = $db->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: http://localhost/Projet_hackaton2/vue/user/login.php');
    exit();
}

// Récupération des statistiques des signalements
$stats = [
    'total' => 0,
    'en_cours' => 0,
    'resolus' => 0
];

// Total des signalements
$stmt = $db->prepare("SELECT COUNT(*) FROM signalement WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['total'] = $stmt->fetchColumn();

// Signalements en cours (nouveau + valide)
$stmt = $db->prepare("SELECT COUNT(*) FROM signalement WHERE id_utilisateur = ? AND statut_signalement IN ('nouveau', 'valide')");
$stmt->execute([$_SESSION['user_id']]);
$stats['en_cours'] = $stmt->fetchColumn();

// Signalements résolus
$stmt = $db->prepare("SELECT COUNT(*) FROM signalement WHERE id_utilisateur = ? AND statut_signalement = 'resolu'");
$stmt->execute([$_SESSION['user_id']]);
$stats['resolus'] = $stmt->fetchColumn();

// Récupération des derniers signalements
$stmt = $db->prepare("SELECT * FROM signalement WHERE id_utilisateur = ? ORDER BY date_signalement DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$derniers_signalements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Signalement Urbain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-avatar {
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-box {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
        }
        .signalement-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .signalement-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-nouveau { background: #ffd700; color: #000; }
        .status-valide { background: #87ceeb; color: #000; }
        .status-resolu { background: #90ee90; color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-card">
                
                <h2 style="text-align: center; margin-bottom: 20px;">
                    Profil de <?php echo htmlspecialchars($user['email']); ?>
                </h2>
                <div style="background: #f8f8f8; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>

            <div class="profile-card">
                <h3 style="margin-bottom: 20px;">Mes Signalements</h3>
                
                <div class="stats-grid">
                    <div class="stat-box">
                        <p>Total</p>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="stat-box">
                        <p>En cours</p>
                        <div class="stat-number"><?php echo $stats['en_cours']; ?></div>
                    </div>
                    <div class="stat-box">
                        <p>Résolus</p>
                        <div class="stat-number"><?php echo $stats['resolus']; ?></div>
                    </div>
                </div>

                <h3 style="margin: 30px 0 20px;">Derniers Signalements</h3>
                <?php if (empty($derniers_signalements)): ?>
                    <div style="background: #f8f8f8; padding: 20px; border-radius: 4px; text-align: center;">
                        <p style="color: #666;">Aucun signalement pour le moment</p>
                        <a href="http://localhost/Projet_hackaton2/vue/signalements/creation.php" class="btn btn-primary" style="margin-top: 15px;">
                            Créer un signalement
                        </a>
                    </div>
                <?php else: ?>
                    <div class="signalements-list">
                        <?php foreach ($derniers_signalements as $signalement): ?>
                            <div class="signalement-item">
                                <h4><?php echo htmlspecialchars($signalement['titre']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars(substr($signalement['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="signalement-status status-<?php echo $signalement['statut_signalement']; ?>">
                                        <?php echo ucfirst($signalement['statut_signalement']); ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($signalement['date_signalement'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="http://localhost/Projet_hackaton2/vue/signalements/creation.php" class="btn btn-primary">
                                Créer un nouveau signalement
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="http://localhost/Projet_hackaton2/controllers/AuthController.php?action=logout" class="btn btn-danger">
                    Se déconnecter
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
