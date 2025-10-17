![SortirLogo.png](assets/imgs/SortirLogo-darkmode.png)

## Description

"*La société ENI souhaite développer pour ses stagiaires actifs ainsi que ses anciens stagiaires
une plateforme web leur permettant d’organiser des sorties.
La plateforme est une plateforme privée dont l’inscription sera gérée par le ou les
administrateurs.
Les sorties, ainsi que les participants ont un site de rattachement, pour permettre une
organisation géographique des sorties.*"

Le projet Sortir.com, réaliser en Sympfony répond à ce problèmes en permettant aux
de créer et s'inscrires à des sorties facilement.

Architecture du projet :
```yaml
Sortir.com/
├── assets/
│   └── # Contient les ressources du projet (images, fichiers CSS, etc.)
├── bin/
│   └── # Console Symfony
├── config/
│   └── # Fichiers de configuration du framework
├── migrations/
│   └── # Fichiers de migration
├── nginx/
│   └── default.conf # Configuration du serveur web Nginx
├── public/
│   └── # Répertoire racine accessible publiquement (index.php, fichiers compilés, etc.)
├── src/
│   ├── Controller/
│   │   └── # Contrôleurs gérant les routes et la logique des pages
│   ├── DataFixtures/
│   │   └── # Données de test ou d’initialisation insérées en base
│   ├── Entity/
│   │   └── # Entités Doctrine représentant les tables de la base de données
│   ├── Form/
│   │   └── # Classes de formulaires Symfony pour la gestion des formulaires HTML
│   ├── Message/
│   │   └── # Message à destination du messageHandler lors de l'utilisation du scheduler
│   ├── Repository/
│   │   └── # Classes gérant les intérations avec la BDD via Doctrine
│   ├── Scheduler/
│   │   └── # Tâche planifiée gérant les états des sorties 
│   ├── Security/
│   │   └── # Gestion de la sécurité (authentification, autorisation, etc.)
│   ├── Services/
│   │   └── # Services métiers & classes utilitaires
│   └── Kernel.php
├── templates/
│   └── # Fichiers Twig pour le rendu des pages HTML
├── tests/
│   └── # Classes et scripts de tests automatisés
├── translations/
│   └── # Fichiers de traduction pour l’internationalisation (i18n)
├── .gitignore
├── Dockerfile
├── LICENSE
├── composer.json # Dépendances PHP et configuration du projet Symfony
├── composer.lock # Version verrouillée des dépendances installées
├── composer.phar
├── deploy.sh # Script de déploiement
├── docker-compose.yml
├── entrypoint.sh # Script exécuté au démarrage du conteneur Docker
├── importmap.php
├── php.ici
├── phpunit.dist.xml
└── symfony.lock # Fichier de verrouillage des versions Symfony et dépendances
```

## Installation

## Pièces jointes

Dans le dossier compresser nommé "Pièces jointes.zip" vous trouverez :

## Contributions