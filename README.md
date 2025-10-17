![SortirLogo.png](assets/imgs/SortirLogo-darkmode.png)

## Sommaire

1. [Présentation & architecture du projet](#présentation)
2. [Installation](#installation)
3. [Pièces jointes](#pièces-jointes)
4. [Contributions](#contributions)

## Présentation

### Description

"*La société ENI souhaite développer pour ses stagiaires actifs ainsi que ses anciens stagiaires
une plateforme web leur permettant d’organiser des sorties.
La plateforme est une plateforme privée dont l’inscription sera gérée par le ou les
administrateurs.
Les sorties, ainsi que les participants ont un site de rattachement, pour permettre une
organisation géographique des sorties.*"

Le projet Sortir.com, réaliser en Sympfony répond à ce problèmes en permettant aux
de créer et s'inscrires à des sorties facilement.

### Architecture du projet :
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
├── .dockerignore
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

### Deploiement local pour contribuer

Requis :
- Un IDE *(comme [PhpStorm](https://www.jetbrains.com/phpstorm/))*
- Un serveur local *(Comme [Wamp](https://wampserver.aviatechno.net/))*
- [Git](https://git-scm.com/) installer sur votre machine
- [Symfony CLI]() installer sur votre machine (avec variables d'environnement)

Étapes :
1. Cloner le projet avec : `git clone https://github.com/Corentin-cott/Sortir.com.git`
2. Configurer le `.env.local` (se référer au .env.local.exemple)
3. Installer les dépendances avec : `symfony composer install`
4. Créer la basse de données avec : `symfony console doctrine:database:create`
5. Excecuter les migrations avec : `symfony console doctrine:migration:migrate`
6. Insérer les fixtures avec : `symfony console doctrine:fixtures:load`
7. *(Optionnel, dans un autre terminal)* Lancer le scheduler : `symfony console messenger:consume scheduler_hello`

### Commandes Symfony importantes

| Commandes                                           | Action                                                                                                                           |
|:----------------------------------------------------|:---------------------------------------------------------------------------------------------------------------------------------|
| `symfony console serve`                             | Lancement du serveur en mode dev sur l'adresse                                                                                   |
| `symfony composer install`                          | Installer les dépendances                                                                                                        |
| `symfony console doctrine:database:create`          | Créer la base de donnée                                                                                                          |
| `symfony console doctrine:migration:migrate`        | Exécute l'ensemble des migrations pour définir les tables et colonnes                                                            |
| `symfony console doctrine:migration:create`         | Créer une migration, important lorsqu'on met à jour une Entity
| `symfony console doctrine:fixtures:load`            | Rempli la base de données avec les données construites dans les fixtures                                                         |
| `symfony console messenger:consume scheduler_hello` | Lance notre scheduler. Apres ca il s'appelera automatiquement à l'interval qu'on lui a donné                                     |

## Pièces jointes

Dans le dossier compressé nommé "*Pièces jointes.zip*" vous trouverez :
```yaml
Pièces jointes.zip/ # Dossier compressé
├── 01-Métier/
│   ├── ProcessusGestionSorties.pdf
├── 02-CasUtilisations/
│   ├── DescriptionProduit.ods
│   ├── SortiesDesktop.pdf
│   ├── SortiesSmartphone.pdf
├── 03-Conception/
│   ├── DiagClasse.pdf
│   └── DiagEtatSortie.pdf
├── 04-ModèlePhysique/
│   └── create_bd_sorties.sql
├── DocumentVision.pdf
└── Enonce.pdf
```

## Contributions

- [Corentin COTTEREAU](https://github.com/Corentin-cott)
- [Thomas LAMBERT](https://github.com/Nowone33)
- [Axel SCORDIA](https://github.com/ScordiaAxel-git)
