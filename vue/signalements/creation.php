<?php
session_start();
$pageTitle = "Créer un signalement";
require_once '../../config/config.php';
require_once '../../vue/layout/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour créer un signalement";
    header('Location: ' . LOGIN_URL);
    exit();
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Signaler un problème</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
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

                    <form action="<?php echo BASE_URL; ?>controllers/signalementscontroler.php?action=create" method="POST" enctype="multipart/form-data" id="signalement-form">
                        <div class="form-group mb-3">
                            <label for="titre" class="form-label">Titre du signalement</label>
                            <input type="text" class="form-control" id="titre" name="titre" required 
                                placeholder="Ex: Problème de route">
                        </div>

                        <div class="form-group mb-3">
                            <label for="categorie" class="form-label">Catégorie</label>
                            <select name="categorie" id="categorie" class="form-select" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <option value="route">Problème de route</option>
                                <option value="eclairage">Éclairage public</option>
                                <option value="proprete">Propreté</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description détaillée</label>
                            <textarea class="form-control" id="description" name="description" required
                                rows="5" placeholder="Décrivez le problème en détail..."></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="photo" class="form-label">Photo du problème</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                            <div id="preview" class="mt-2"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Localisation</label>
                            <div id="map" style="height: 400px; border-radius: 4px;" class="mb-2"></div>
                            <p class="text-muted small">
                                Cliquez sur la carte pour placer le marqueur à l'endroit exact du problème
                            </p>
                            <input type="hidden" id="latitude" name="latitude" required>
                            <input type="hidden" id="longitude" name="longitude" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Envoyer le signalement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclusion de Leaflet pour la carte -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Prévisualisation de l'image
document.getElementById('photo').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">`;
    }

    if (file) {
        reader.readAsDataURL(file);
    }
});

// Initialisation de la carte
document.addEventListener('DOMContentLoaded', function() {
    // Coordonnées du centre de la Guinée (Conakry)
    const guineeCentre = [9.6412, -13.5784];
    
    // Création de la carte centrée sur la Guinée
    const map = L.map('map').setView(guineeCentre, 7);
    
    // Ajout du fond de carte OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Définition des limites de la carte pour la Guinée
    const guineeBounds = [
        [7.1906, -15.0766], // Sud-Ouest
        [12.6741, -7.6410]  // Nord-Est
    ];
    map.setMaxBounds(guineeBounds);
    map.setMinZoom(7);      // Zoom minimum pour voir toute la Guinée
    map.setMaxZoom(18);     // Zoom maximum pour les détails

    let marker;

    // Géolocalisation de l'utilisateur (si dans les limites de la Guinée)
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Vérifier si la position est dans les limites de la Guinée
            if (lat >= 7.1906 && lat <= 12.6741 && lng >= -15.0766 && lng <= -7.6410) {
                map.setView([lat, lng], 13);
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }
        });
    }

    // Clic sur la carte pour placer le marqueur
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });

    // Ajout des principales villes de Guinée
    const villes = [
        { nom: "Conakry", coords: [9.6412, -13.5784] },
        { nom: "Kankan", coords: [10.3833, -9.3000] },
        { nom: "Kindia", coords: [10.0500, -12.8667] },
        { nom: "Labé", coords: [11.3167, -12.2833] },
        { nom: "N'Zérékoré", coords: [7.7500, -8.8167] }
    ];

    villes.forEach(ville => {
        L.marker(ville.coords)
            .bindPopup(ville.nom)
            .addTo(map);
    });
});

// Validation du formulaire
document.getElementById('signalement-form').addEventListener('submit', function(e) {
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    
    if (!latitude || !longitude) {
        e.preventDefault();
        alert('Veuillez sélectionner un emplacement sur la carte');
    }
});
</script>

<?php require_once '../../vue/layout/footer.php'; ?>
