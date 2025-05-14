<?php
require_once '../models/Signalement.php';

class CarteController {
    private $signalementModel;

    public function __construct() {
        $this->signalementModel = new Signalement();
    }

    public function getSignalements() {
        try {
            $signalements = $this->signalementModel->getAllSignalements();
            return json_encode([
                'status' => 'success',
                'data' => $signalements
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des signalements'
            ]);
        }
    }

    public function getSignalementsByType($type) {
        try {
            $signalements = $this->signalementModel->getSignalementsByCategorie($type);
            return json_encode([
                'status' => 'success',
                'data' => $signalements
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des signalements'
            ]);
        }
    }

    public function getSignalementsProximite($lat, $lng, $distance = 5) {
        try {
            $signalements = $this->signalementModel->getSignalementsProximite($lat, $lng, $distance);
            return json_encode([
                'status' => 'success',
                'data' => $signalements
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des signalements à proximité'
            ]);
        }
    }

    public function getStatistiquesZone($lat, $lng, $rayon) {
        try {
            $stats = [
                'total' => $this->signalementModel->getCountInZone($lat, $lng, $rayon),
                'resolus' => $this->signalementModel->getCountInZoneByStatus($lat, $lng, $rayon, 'resolu'),
                'en_cours' => $this->signalementModel->getCountInZoneByStatus($lat, $lng, $rayon, 'valide')
            ];
            return json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques'
            ]);
        }
    }
}

// Traitement des requêtes AJAX
if (isset($_GET['action'])) {
    $controller = new CarteController();
    
    switch ($_GET['action']) {
        case 'getAll':
            echo $controller->getSignalements();
            break;
        case 'getByType':
            $type = $_GET['type'] ?? '';
            echo $controller->getSignalementsByType($type);
            break;
        case 'getProximite':
            $lat = $_GET['lat'] ?? 0;
            $lng = $_GET['lng'] ?? 0;
            $distance = $_GET['distance'] ?? 5;
            echo $controller->getSignalementsProximite($lat, $lng, $distance);
            break;
        case 'getStats':
            $lat = $_GET['lat'] ?? 0;
            $lng = $_GET['lng'] ?? 0;
            $rayon = $_GET['rayon'] ?? 5;
            echo $controller->getStatistiquesZone($lat, $lng, $rayon);
            break;
    }
}
?>
