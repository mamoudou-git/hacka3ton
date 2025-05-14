<?php
$pageTitle = "Carte Interactive";
require_once '../../vue/layout/header.php';
?>

<div class="container">
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px;">Navigation et État des Routes</h2>

        <!-- Barre de recherche d'itinéraire -->
        <div style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px;">
            <div>
                <label for="start" style="display: block; margin-bottom: 5px;">Point de départ</label>
                <input type="text" id="start" placeholder="Entrez le point de départ" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="end" style="display: block; margin-bottom: 5px;">Destination</label>
                <input type="text" id="end" placeholder="Entrez la destination" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="align-self: end;">
                <button onclick="calculateRoute()" class="btn" style="height: 41px;">
                    <i class="fas fa-route"></i> Calculer
                </button>
            </div>
        </div>

        <!-- Filtres pour les signalements -->
        <div style="margin-bottom: 20px;">
            <label style="margin-right: 15px;">
                <input type="checkbox" id="showRoadworks" checked> Travaux routiers
            </label>
            <label style="margin-right: 15px;">
                <input type="checkbox" id="showAccidents" checked> Accidents
            </label>
            <label>
                <input type="checkbox" id="showTraffic" checked> Embouteillages
            </label>
        </div>

        <!-- Carte -->
        <div id="map" style="height: 600px; border-radius: 4px;"></div>

        <!-- Informations sur l'itinéraire -->
        <div id="routeInfo" style="margin-top: 20px; display: none;">
            <h3>Informations sur l'itinéraire</h3>
            <div id="routeDetails"></div>
        </div>
    </div>
</div>

<!-- Inclusion des bibliothèques nécessaires -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Coordonnées du centre de la Guinée (Conakry)
    const guineeCentre = [9.6412, -13.5784];
    
    // Initialisation de la carte
    const map = L.map('map').setView(guineeCentre, 7);
    
    // Ajout du fond de carte
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Définition des limites de la Guinée
    const guineeBounds = [
        [7.1906, -15.0766],
        [12.6741, -7.6410]
    ];
    map.setMaxBounds(guineeBounds);
    map.setMinZoom(7);
    map.setMaxZoom(18);

    // Variable pour stocker l'itinéraire
    let routingControl = null;

    // Fonction pour calculer l'itinéraire
    window.calculateRoute = function() {
        const start = document.getElementById('start').value;
        const end = document.getElementById('end').value;

        if (!start || !end) {
            alert('Veuillez entrer un point de départ et une destination');
            return;
        }

        // Suppression de l'ancien itinéraire s'il existe
        if (routingControl) {
            map.removeControl(routingControl);
        }

        // Création du nouvel itinéraire
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(start + ', Guinée')}`)
            .then(response => response.json())
            .then(startData => {
                if (startData.length === 0) throw new Error('Point de départ non trouvé');
                return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(end + ', Guinée')}`)
                    .then(response => response.json())
                    .then(endData => {
                        if (endData.length === 0) throw new Error('Destination non trouvée');
                        return {
                            start: [startData[0].lat, startData[0].lon],
                            end: [endData[0].lat, endData[0].lon]
                        };
                    });
            })
            .then(coords => {
                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(coords.start[0], coords.start[1]),
                        L.latLng(coords.end[0], coords.end[1])
                    ],
                    routeWhileDragging: true,
                    lineOptions: {
                        styles: [{color: '#4CAF50', weight: 6}]
                    }
                }).addTo(map);

                // Événement quand l'itinéraire est calculé
                routingControl.on('routesfound', function(e) {
                    const routes = e.routes;
                    const route = routes[0];
                    
                    // Affichage des détails de l'itinéraire
                    document.getElementById('routeInfo').style.display = 'block';
                    document.getElementById('routeDetails').innerHTML = `
                        <p><strong>Distance :</strong> ${(route.summary.totalDistance / 1000).toFixed(1)} km</p>
                        <p><strong>Durée estimée :</strong> ${Math.round(route.summary.totalTime / 60)} minutes</p>
                    `;
                });
            })
            .catch(error => {
                alert(error.message);
            });
    };

    // Simulation de signalements en temps réel (à remplacer par des données réelles de votre base de données)
    const signalements = [
        { type: 'roadworks', position: [9.6412, -13.5784], description: 'Travaux routiers en cours' },
        { type: 'accident', position: [10.3833, -9.3000], description: 'Accident signalé' },
        { type: 'traffic', position: [11.3167, -12.2833], description: 'Embouteillage important' }
    ];

    // Création des icônes personnalisées
    const icons = {
        roadworks: L.divIcon({className: 'custom-div-icon', html: '<i class="fas fa-hard-hat" style="color: #ff9800;"></i>'}),
        accident: L.divIcon({className: 'custom-div-icon', html: '<i class="fas fa-car-crash" style="color: #f44336;"></i>'}),
        traffic: L.divIcon({className: 'custom-div-icon', html: '<i class="fas fa-traffic-light" style="color: #fdd835;"></i>'})
    };

    // Groupes de marqueurs
    const layers = {
        roadworks: L.layerGroup(),
        accident: L.layerGroup(),
        traffic: L.layerGroup()
    };

    // Ajout des signalements sur la carte
    signalements.forEach(signal => {
        const marker = L.marker(signal.position, {icon: icons[signal.type]})
            .bindPopup(signal.description);
        layers[signal.type].addLayer(marker);
    });

    // Ajout des couches à la carte
    Object.values(layers).forEach(layer => map.addLayer(layer));

    // Gestion des filtres
    document.getElementById('showRoadworks').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(layers.roadworks);
        else map.removeLayer(layers.roadworks);
    });

    document.getElementById('showAccidents').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(layers.accident);
        else map.removeLayer(layers.accident);
    });

    document.getElementById('showTraffic').addEventListener('change', function(e) {
        if (e.target.checked) map.addLayer(layers.traffic);
        else map.removeLayer(layers.traffic);
    });
});
</script>

<style>
.custom-div-icon {
    background: white;
    border: 2px solid rgba(0,0,0,0.2);
    border-radius: 50%;
    text-align: center;
    width: 30px !important;
    height: 30px !important;
    line-height: 30px !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.custom-div-icon i {
    font-size: 16px;
}
</style>

<?php require_once '../../vue/layout/footer.php'; ?>
