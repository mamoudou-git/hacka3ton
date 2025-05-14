# Système de Signalement des Problèmes Urbains

## Description
Amelioration Urbaine est une application web permettant aux citoyens de signaler et suivre les problèmes urbains en temps réel. Les utilisateurs peuvent signaler des incidents, suivre leur résolution et interagir avec les autorités locales.

## Technologies Utilisées
- **Backend**: PHP 7.4+
- **Base de données**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Frameworks/Bibliothèques**:
  - Bootstrap 5.3.0 (Interface responsive)
  - Font Awesome 6.0.0 (Icônes)
  - Leaflet.js (Cartographie interactive)
  - Leaflet Routing Machine (Calcul d'itinéraires)

## Prérequis
- Serveur Web (WAMP, XAMPP, etc.)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Navigateur web moderne


## Structure du Projet
```
Projet_hackaton/
├── api/                  # API endpoints
├── config/              # Fichiers de configuration
├── controllers/         # Contrôleurs
├── models/             # Modèles
├── public/             # Fichiers publics (CSS, JS, images)
├── vue/                # Vues de l'application
│   ├── dashboard/      # Interface tableau de bord
│   ├── layout/         # Templates communs
│   ├── signalements/   # Gestion des signalements
│   └── user/           # Gestion des utilisateurs
└── uploads/            # Dossier pour les fichiers uploadés
```

## Fonctionnalités Principales

### 1. Authentification
- Inscription utilisateur
- Connexion
- Gestion de profil

### 2. Signalements
- Création de signalements avec:
  - Titre et description
  - Photo du problème
  - Géolocalisation sur la carte
  - Catégorisation
- Suivi des signalements
- Système de votes
- Commentaires

### 3. Carte Interactive
- Visualisation des signalements
- Calcul d'itinéraires
- Filtres par type de problème
- Affichage en temps réel des incidents

### 4. Tableau de Bord Autorités
- Vue d'ensemble des signalements
- Statistiques en temps réel
- Gestion des interventions
- Suivi des résolutions

## Navigation dans l'Application

### Utilisateurs Non Connectés
1. Page d'accueil (`/public/index.php`)
   - Présentation du service
   - Accès à l'inscription/connexion
   - Carte des signalements publics

2. Inscription (`/vue/user/inscription.php`)
   - Création de compte avec:
     - Nom d'utilisateur
     - Email
     - Mot de passe

3. Connexion (`/vue/user/login.php`)
   - Authentification par email/mot de passe

### Utilisateurs Connectés
1. Création de Signalement (`/vue/signalements/creation.php`)
   - Formulaire de signalement
   - Upload de photo
   - Sélection sur la carte

2. Carte Interactive (`/vue/dashboard/carte.php`)
   - Navigation
   - Filtres des signalements
   - Calcul d'itinéraires

3. Profil Utilisateur
   - Historique des signalements
   - Modification des informations
   - Suivi des interventions

### Interface Autorités
1. Tableau de Bord (`/vue/dashboard/autorite.php`)
   - Statistiques globales
   - Liste des signalements
   - Gestion des priorités

## Sécurité
- Hashage des mots de passe
- Protection contre les injections SQL
- Validation des données
- Gestion des sessions sécurisée

## Support
Pour toute question ou assistance:
- Email: balde@gmail.com
- Téléphone: +224 627 46 49 95

## Licence
Tous droits réservés - Urban Report 