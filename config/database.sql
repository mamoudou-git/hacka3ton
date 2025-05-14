create database gestion_signalement;--creation de la base de données

use gestion_signalement;--ouverture de la base de données

--creation de la table utilisateur qui contient les informations des utilisateurs qui peuvent signaler des problèmes
create table utilisateur (
    id int auto_increment primary key,
    nom varchar(50) not null,
    prenom varchar(50) not null,
    email varchar(75) not null unique,
    mot_de_passe varchar(50) not null,
    telephone varchar(20) ,
    role enum('admin', 'utilisateur','autorite','entreprise'),
    date_inscription datetime default current_timestamp
);

--creation de la table signalement qui contient les informations des signalements
create table signalement (
    id int auto_increment primary key,
    titre varchar(100) not null,
    description text not null,
    photo varchar(255),
    date_signalement datetime default current_timestamp,
    statut_signalement enum('nouveau', 'valide', 'resolu') ,
    votes int default 0,
    niveau_urgence enum('faible', 'moyen', 'eleve') ,
    id_utilisateur int,
    foreign key (id_utilisateur) references utilisateur(id)
);

--creation de la table votes qui contient les informations des votes permettant de savoir combien de votes a eu un signalement 
--pour prioritiser les signalements
create table votes (
    id int auto_increment primary key,
    id_signalement int,
    id_utilisateur int,
    date_vote datetime default current_timestamp,
    foreign key (id_signalement) references signalement(id),
    foreign key (id_utilisateur) references utilisateur(id)
);

create table commentaire (
    id int auto_increment primary key,
    id_signalement int,
    id_utilisateur int,
    date_commentaire datetime default current_timestamp,
    commentaire text not null,
    foreign key (id_signalement) references signalement(id),
    foreign key (id_utilisateur) references utilisateur(id)
);