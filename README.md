RentCar – Application Web de Location & Vente de Voitures

Application développée par l'équipe de Créative Code.

Objectif : proposer un site complet pour la vente et la location de voitures pour différents besoins (évènements, sorties, trajets simples…).

- Technologies utilisées:

. Frontend

React (Vite)

React Router

TailwindCSS

. Backend

PHP 8+

MySQL

Composer (autoloading)

Architecture MVC light

Outils

GitHub (workflow équipe)

VS Code

Postman (tests API)

📁 Architecture du projet

rentcar-app/
│
├── frontend/
│   ├── src/
│   ├── public/
│   └── package.json
│
├── backend/
│   ├── public/        → index.php, endpoints
│   ├── src/           → controllers, models
│   ├── config/        → database.php, env.php
│   ├── vendor/
│   └── composer.json
│
├── docs/
│   ├── api/
│   ├── database-schema.png
│   └── features.md
│
├── README.md
└── .gitignore

- Fonctionnalités prévues (v1):

. Compte utilisateur

Inscription / Connexion

Profil client

. Location & Vente

Liste des voitures disponibles

Filtrage (prix, catégorie, disponibilité)

Détails d’un véhicule

Location / réservation

Achat (simulation)

. Dashboard Admin

Ajouter / modifier / supprimer un véhicule

Voir les réservations

Gérer les clients

- Installation & Setup

1️1-  Cloner le repo

git clone https://github.com/votre-repo/rentcar-app.git

2️2- Installer le frontend

cd frontend
npm install
npm run dev

3️3- Installer le backend

cd backend
composer install

Configurer la base dans config/database.php.

Lancer le serveur interne :
php -S localhost:8000 -t public


- Tests API

Postman collection disponible dans docs/api/postman.json.

- Contributeurs

Juste – Frontend / Backend

Jean-Eudes – Backend / Frontend

- Workflow Git recommandé
Branches

main → version stable

dev → développement

feature/nom → pour chaque nouvelle feature

Process

Créer une feature branch

Développer

Commit propre avec message explicite

Push

Pull Request → revue de l’autre développeur

Merge dans dev, puis dans main lors d'une release

- License

MIT
