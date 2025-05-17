<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_signalement", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Requêtes pour obtenir les statistiques
$total_query = "SELECT COUNT(*) as total FROM signalement";
$en_attente_query = "SELECT COUNT(*) as en_attente FROM signalement WHERE statut_signalement = 'nouveau'";
$en_cours_query = "SELECT COUNT(*) as en_cours FROM signalement WHERE statut_signalement = 'valide'";
$resolus_query = "SELECT COUNT(*) as resolus FROM signalement WHERE statut_signalement = 'resolu'";

try {
    // Exécution des requêtes
    $total = $pdo->query($total_query)->fetch()['total'];
    $en_attente = $pdo->query($en_attente_query)->fetch()['en_attente'];
    $en_cours = $pdo->query($en_cours_query)->fetch()['en_cours'];
    $resolus = $pdo->query($resolus_query)->fetch()['resolus'];
} catch(PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
    exit;
}

// Requête pour récupérer les derniers signalements
$signalements_query = "SELECT id, date_signalement, titre, description, niveau_urgence, statut_signalement 
                      FROM signalement 
                      ORDER BY date_signalement DESC 
                      LIMIT 10";

try {
    $signalements = $pdo->query($signalements_query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur lors de la récupération des signalements : " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Autorités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .dashboard-stat {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #1e3c72;
        }
        .priority-high { background-color: #ffe6e6; }
        .priority-medium { background-color: #fff3e6; }
        .priority-low { background-color: #e6ffe6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Tableau de Bord Autorités</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../signalements/liste_signalements.php">Signalements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/Projet_hackaton2/vue/user/deconnexion.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="dashboard-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fs-5">Total Signalements</h3>
                            <h2 class="mb-0"><?php echo $total; ?></h2>
                        </div>
                        <i class='bx bx-list-ul stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fs-5">En attente</h3>
                            <h2 class="mb-0"><?php echo $en_attente; ?></h2>
                        </div>
                        <i class='bx bx-time stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fs-5">En cours</h3>
                            <h2 class="mb-0"><?php echo $en_cours; ?></h2>
                        </div>
                        <i class='bx bx-loader stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fs-5">Résolus</h3>
                            <h2 class="mb-0"><?php echo $resolus; ?></h2>
                        </div>
                        <i class='bx bx-check-circle stat-icon'></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des derniers signalements -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Derniers Signalements</h5>
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-sm">Tous</button>
                    <button class="btn btn-outline-primary btn-sm">Priorité Haute</button>
                    <button class="btn btn-outline-primary btn-sm">Non Assignés</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Localisation</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($signalements as $signalement): 
                                $priority_class = '';
                                switch(strtolower($signalement['niveau_urgence'])) {
                                    case 'eleve':
                                        $priority_class = 'priority-high';
                                        $badge_class = 'bg-danger';
                                        break;
                                    case 'moyen':
                                        $priority_class = 'priority-medium';
                                        $badge_class = 'bg-warning';
                                        break;
                                    case 'faible':
                                        $priority_class = 'priority-low';
                                        $badge_class = 'bg-success';
                                        break;
                                }
                                
                                $status_class = '';
                                switch(strtolower($signalement['statut_signalement'])) {
                                    case 'nouveau':
                                        $status_class = 'bg-warning';
                                        break;
                                    case 'valide':
                                        $status_class = 'bg-info';
                                        break;
                                    case 'resolu':
                                        $status_class = 'bg-success';
                                        break;
                                }
                            ?>
                            <tr class="<?php echo $priority_class; ?>">
                                <td>#<?php echo $signalement['id']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($signalement['date_signalement'])); ?></td>
                                <td><?php echo htmlspecialchars($signalement['titre']); ?></td>
                                <td><?php echo htmlspecialchars(substr($signalement['description'], 0, 50)) . '...'; ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($signalement['niveau_urgence']); ?></span></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($signalement['statut_signalement']); ?></span></td>
                                <td>
                                    <a href="details_signalement.php?id=<?php echo $signalement['id']; ?>" class="btn btn-sm btn-primary">Détails</a>
                                    <?php if($signalement['statut_signalement'] == 'nouveau'): ?>
                                        <button class="btn btn-sm btn-success" onclick="assignerSignalement(<?php echo $signalement['id']; ?>)">Assigner</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
