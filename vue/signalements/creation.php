<?php
$pageTitle = "Créer un signalement";
require_once '../../vue/layout/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width: 800px;">
        <h2 class="form-title">Signaler un problème</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="http://localhost/Projet_hackaton/vue/signalements/creation.php" method="POST" enctype="multipart/form-data" id="signalement-form">
            <div class="form-group">
                <label for="titre">Titre du signalement</label>
                <input type="text" id="titre" name="titre" required 
                       placeholder="Ex: Problème de route">
            </div>

            <div class="form-group">
                <label for="categorie">Catégorie</label>
                <select name="categorie" id="categorie" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Sélectionnez une catégorie</option>
                    <option value="route">Problème de route</option>
                    <option value="eclairage">Éclairage public</option>
                    <option value="proprete">Propreté</option>
                    <option value="infrastructure">Infrastructure</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description détaillée</label>
                <textarea id="description" name="description" required
                          style="width: 100%; min-height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"
                          placeholder="Décrivez le problème en détail..."></textarea>
            </div>

            <div class="form-group">
                <label for="photo">Photo du problème</label>
                <input type="file" id="photo" name="photo" accept="image/*" required
                       style="border: none; padding: 10px 0;">
                <div id="preview" style="margin-top: 10px; max-width: 300px;"></div>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Localisation</label>
                <div id="map" style="height: 400px; margin-top: 10px; border-radius: 4px;"></div>
                <p style="margin-top: 5px; color: #666; font-size: 0.9em;">
                    Cliquez sur la carte pour placer le marqueur à l'endroit exact du problème
                </p>
                <input type="hidden" id="latitude" name="latitude" required>
                <input type="hidden" id="longitude" name="longitude" required>
            </div>

            <button type="submit" class="btn">Envoyer le signalement</button>
        </form>
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
        preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; border-radius: 4px;">`;
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
</script>

<?php require_once '../../vue/layout/footer.php'; ?>
