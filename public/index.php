<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Amélioration Urbaine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 80px 0;
        }
        .feature-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .feature-box img {
            width: 60px;
            margin-bottom: 15px;
        }
        .stats {
            background: #f8f9fa;
            padding: 40px 0;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
        }
        .stat-box h3 {
            color: #1e3c72;
            font-size: 2em;
        }
    </style>
</head>
<body>
    <!-- Bannière principale -->
    <div class="banner">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Ensemble, Améliorons Notre Ville</h1>
            <p class="lead">Signalez facilement les problèmes urbains et suivez leur résolution</p>
            <div class="mt-4">
                <a href="../vue/user/inscription.php" class="btn btn-light btn-lg mx-2">Rejoindre</a>
                <a href="../vue/user/login.php" class="btn btn-outline-light btn-lg mx-2">Connexion</a>
            </div>
        </div>
    </div>

    <!-- Fonctionnalités -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box text-center">
                    <h3>Photographiez</h3>
                    <p>Capturez le problème en photo</p>
                    <button id="takePhotoBtn" class="btn btn-primary">Prendre une photo</button>
                    <video id="video" style="display: none;"></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <img id="photo" style="max-width: 100%; margin-top: 10px; display: none;">
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box text-center">
                    <img src="../public/assets/images/télécharger.webp" alt="Map">
                    <a href="../vue/dashboard/carte.php" class="btn btn-light btn-lg mx-2">localisez</a>
                    <p>Indiquez l'emplacement exact</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box text-center">
                    
                    <h3>Suivez</h3>
                    <p>Surveillez la résolution</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appel à l'action -->
    <div class="container my-5 text-center">
        <h2>Vous avez repéré un problème ?</h2>
        <p class="mb-4">Contribuez à l'amélioration de votre quartier en quelques clics</p>
        <a href="../vue/signalements/creation.php" class="btn btn-primary btn-lg">Signaler un problème</a>
    </div>

    <!-- Footer simple -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>Pour des  villes guinéennes meilleures</p>
            <small>Contact : balde@gmail.com | Tel: +224 627 46 49 95</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const photo = document.getElementById('photo');
            const takePhotoBtn = document.getElementById('takePhotoBtn');
            let stream = null;

            takePhotoBtn.addEventListener('click', async function() {
                try {
                    if (!stream) {
                        stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { 
                                facingMode: 'environment' 
                            }, 
                            audio: false 
                        });
                        video.srcObject = stream;
                        video.style.display = 'block';
                        video.play();
                        takePhotoBtn.textContent = 'Capturer';
                    } else {
                        // Prendre la photo
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0);
                        
                        // Afficher la photo
                        photo.src = canvas.toDataURL('../public/assets/images/télécharger.webp');
                        photo.style.display = 'block';
                        
                        // Arrêter la caméra
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                        video.style.display = 'none';
                        takePhotoBtn.textContent = 'Prendre une photo';
                    }
                } catch (err) {
                    console.error("Erreur:", err);
                    alert("Impossible d'accéder à la caméra. Veuillez vérifier les permissions.");
                }
            });
        });
    </script>
</body>
</html> 