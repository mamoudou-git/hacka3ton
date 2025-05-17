<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Récupération des filtres
$statut = isset($_GET['statut']) ? $_GET['statut'] : 'all';
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'date_desc';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'all';

// Construction de la requête SQL de base
$sql = "SELECT s.*, u.email as email_utilisateur 
        FROM signalement s 
        LEFT JOIN utilisateur u ON s.id_utilisateur = u.id 
        WHERE 1=1";
$params = [];

// Ajout des filtres
if ($statut !== 'all') {
    $sql .= " AND s.statut_signalement = ?";
    $params[] = $statut;
}

if ($categorie !== 'all') {
    $sql .= " AND s.categorie = ?";
    $params[] = $categorie;
}

// Ajout du tri
switch ($tri) {
    case 'date_asc':
        $sql .= " ORDER BY s.date_signalement ASC";
        break;
    case 'urgence':
        $sql .= " ORDER BY s.niveau_urgence DESC";
        break;
    case 'votes':
        $sql .= " ORDER BY s.votes DESC";
        break;
    default:
        $sql .= " ORDER BY s.date_signalement DESC";
}

// Exécution de la requête
$stmt = $db->prepare($sql);
$stmt->execute($params);
$signalements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des catégories uniques pour le filtre
$stmt = $db->query("SELECT DISTINCT categorie FROM signalement WHERE categorie IS NOT NULL");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Préparation des données pour la carte
$markers = [];
foreach ($signalements as $s) {
    if (!empty($s['latitude']) && !empty($s['longitude'])) {
        $markers[] = [
            'lat' => $s['latitude'],
            'lng' => $s['longitude'],
            'title' => $s['titre'],
            'status' => $s['statut_signalement'],
            'urgence' => $s['niveau_urgence']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Signalements - Signalement Urbain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .signalement-card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-nouveau { background-color: #ffd700; color: #000; }
        .status-valide { background-color: #87ceeb; color: #000; }
        .status-resolu { background-color: #90ee90; color: #000; }
        .urgence-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .urgence-faible { background-color: #98FB98; }
        .urgence-moyen { background-color: #FFD700; }
        .urgence-eleve { background-color: #FF6B6B; }
        .votes-count {
            font-size: 0.9em;
            color: #666;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        #map {
            height: 400px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .location-info {
            font-size: 0.8em;
            color: #666;
            margin-top: 10px;
        }
        .view-map-btn {
            padding: 2px 8px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Liste des Signalements</h1>

        <!-- Carte des signalements -->
        <div id="map"></div>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="all" <?php echo $statut === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="nouveau" <?php echo $statut === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                        <option value="valide" <?php echo $statut === 'valide' ? 'selected' : ''; ?>>Validé</option>
                        <option value="resolu" <?php echo $statut === 'resolu' ? 'selected' : ''; ?>>Résolu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select name="categorie" id="categorie" class="form-select">
                        <option value="all">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $categorie === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tri" class="form-label">Trier par</label>
                    <select name="tri" id="tri" class="form-select">
                        <option value="date_desc" <?php echo $tri === 'date_desc' ? 'selected' : ''; ?>>Plus récents</option>
                        <option value="date_asc" <?php echo $tri === 'date_asc' ? 'selected' : ''; ?>>Plus anciens</option>
                        <option value="urgence" <?php echo $tri === 'urgence' ? 'selected' : ''; ?>>Niveau d'urgence</option>
                        <option value="votes" <?php echo $tri === 'votes' ? 'selected' : ''; ?>>Nombre de votes</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>

        <!-- Liste des signalements -->
        <?php if (empty($signalements)): ?>
            <div class="alert alert-info">Aucun signalement trouvé.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($signalements as $signalement): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card signalement-card">
                            <?php if (!empty($signalement['photo'])): ?>
                                <img src="<?php echo BASE_URL . $signalement['photo']; ?>" 
                                     class="card-img-top" alt="Photo du signalement"
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($signalement['titre']); ?></h5>
                                    <span class="status-badge status-<?php echo $signalement['statut_signalement']; ?>">
                                        <?php echo ucfirst($signalement['statut_signalement']); ?>
                                    </span>
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars(substr($signalement['description'], 0, 150)) . '...'; ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($signalement['categorie']); ?></span>
                                    <span class="urgence-badge urgence-<?php echo $signalement['niveau_urgence']; ?>">
                                        Urgence : <?php echo ucfirst($signalement['niveau_urgence']); ?>
                                    </span>
                                </div>

                                <div class="location-info">
                                    <button class="btn btn-sm btn-outline-primary view-map-btn" 
                                            onclick="centerMapOn(<?php echo $signalement['latitude']; ?>, <?php echo $signalement['longitude']; ?>)">
                                        <i class="fas fa-map-marker-alt"></i> Voir sur la carte
                                    </button>
                                    <span class="ms-2">
                                        Coordonnées: <?php echo round($signalement['latitude'], 6); ?>, <?php echo round($signalement['longitude'], 6); ?>
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        Par <?php echo htmlspecialchars($signalement['email_utilisateur']); ?><br>
                                        Le <?php echo date('d/m/Y H:i', strtotime($signalement['date_signalement'])); ?>
                                    </small>
                                    <div class="votes-count">
                                        <i class="fas fa-thumbs-up"></i> <?php echo $signalement['votes']; ?> votes
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Bouton pour créer un nouveau signalement -->
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>vue/signalements/creation.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus-circle"></i> Créer un nouveau signalement
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialisation de la carte
        const map = L.map('map').setView([9.6412, -13.5784], 7); // Centré sur la Guinée
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Ajout des marqueurs
        const markers = <?php echo json_encode($markers); ?>;
        const mapMarkers = {};

        markers.forEach(marker => {
            const icon = L.divIcon({
                className: `marker-icon status-${marker.status} urgence-${marker.urgence}`,
                html: '<i class="fas fa-map-marker-alt"></i>',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            });

            const mapMarker = L.marker([marker.lat, marker.lng], { icon })
                .addTo(map)
                .bindPopup(marker.title);

            mapMarkers[`${marker.lat}-${marker.lng}`] = mapMarker;
        });

        // Fonction pour centrer la carte sur un marqueur
        function centerMapOn(lat, lng) {
            map.setView([lat, lng], 15);
            const marker = mapMarkers[`${lat}-${lng}`];
            if (marker) {
                marker.openPopup();
            }
        }
    </script>
</body>
</html>
