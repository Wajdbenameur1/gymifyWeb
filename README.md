Système de Gestion de Salle de Sport

Aperçu
Ce projet est une application web développée dans le cadre des cours de l'École d'Ingénieurs Esprit pour l'année universitaire 2024-2025. Elle est conçue pour gérer plusieurs salles de sport, permettant aux administrateurs, responsables de salles, entraîneurs et sportifs de gérer diverses opérations telles que la gestion des utilisateurs, des activités, des abonnements, des événements, des cours, des paiements, des avis et des blogs. L'application vise à rationaliser les opérations des salles de sport et à améliorer l'expérience utilisateur grâce à une interface intuitive et réactive.
Fonctionnalités

Panneau d'administration : Gérer les utilisateurs, les salles de sport, les activités, les réclamations et les produits pour toutes les succursales.
Tableau de bord du responsable de salle : Ajouter des abonnements pour chaque salle, des événements et des équipes spécifiques à la salle attribuée.
Interface entraîneur : Organiser et gérer les cours et les planning.
Portail sportif : Consulter les activités, abonnements, événements,cours et produit ,effectuer des paiements, gérer les avis et contribuer aux blogs,participer a une evenement.
Support multi-salles : Gestion centralisée pour plusieurs succursales avec des fonctionnalités spécifiques à chaque site.
Design réactif : Optimisé pour une utilisation sur ordinateurs, tablettes et appareils mobiles.

Pile Technologique
Frontend

template twig: Pour une interface utilisateur dynamique et réactive.
CSS : Pour un style moderne et efficace.
Backend

symfony: Pour un serveur robuste et évolutif.
Xampp: Pour stocker les données des utilisateurs, informations des salles, abonnements, etc.

Autres Outils

JWT (JSON Web Tokens) : Pour une authentification et une autorisation sécurisées.
Stripe : Pour le traitement des paiements en ligne.
Git : Pour le contrôle de version.
GitHub : Pour l'hébergement du dépôt du projet.
Google Auth: Pour authentification avec google
Recaptcha: Pour securité
Google Calendar: pour gerer les cours
Meteo: Pour afficher le meteo
DeepInfra: Chatbot 


Structure du Répertoire
gymifyweb/
├── public/                   
│   ├── build/                
│   ├── css/                   
│   └──fonts/  
    └──img/
    └──js/
    └──uploads/
├── src/ 
    └──command/
│   ├── controllers/  
    └──doctrin/
│   ├── entity/                 
│   ├── enum/                 
│   └── form/
    └──Notification/
    └──repository/
    └──security/
    └──service/
    └──twig/
│
├──templates
├── .env  
├── README.md                  
└── .gitignore                  

Premiers Pas
Prérequis

Symfony (>= 8.x)
Xampp (instance locale)
Git
Compte Stripe pour l'intégration des paiements
Compte google

Installation

Cloner le dépôt :git clone https://github.com/Wajdbenameur1/gymifyWeb


Naviguer dans le répertoire du projet :cd gimifyWeb


Installer les dépendances:
npm install
composer install


Configurer les variables d'environnement :
Créer un fichier .env
Ajouter les variables suivantes : DATABASE_URL="mysql://wajd@192.168.212.75:3306/projweb"


Lancement de l'Application

Démarrer le serveur:
symfony server :start


Ouvrir votre navigateur et accéder à http://localhost:8000 pour utiliser l'application.

Utilisation

Administrateur : Connectez-vous pour gérer les utilisateurs, les salles, les réclamations et les produits.
Responsable de salle : Accédez au tableau de bord pour ajouter des abonnements, événements ou équipes pour votre salle.
Entraîneur : Créez et gérez des cours et des plannings.
Sportif : Consulter les activités, abonnements, événements,cours et produit ,effectuer des paiements, gérer les avis et contribuer aux blogs,participer a une evenement.

Étiquettes (Topics)

développement-web
twig
AI
machine learning
Xampp
Symfony
intégration-paiement


Remerciements
Ce projet a été réalisé sous la direction du corps professoral de l'École d'Ingénieurs Esprit. Un grand merci à nos professeurs  Madame Sassi Soumaya et Maroua Douiri pour leur soutien et leurs retours tout au long du développement.
