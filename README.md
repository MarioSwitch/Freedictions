# Better Twitch Predictions
## Description
Better Twitch Predictions est, à l'origine, un projet informatique à sujet libre demandé en première année de notre école d'ingénieur. Développé par [MarioSwitch](https://github.com/MarioSwitch) et [Yosh](https://github.com/yoshakami) début 2022, il a par la suite été mis en ligne publiquement sur [TC Râches](https://tcraches.fr/predictions).

En mai 2023, MarioSwitch décide de reprendre le projet en améliorant grandement la qualité du code et des librairies. Cette nouvelle version (2.0) est disponible depuis le 1ᵉʳ juin 2023 et remplace l'ancienne.

Depuis cette date, des corrections de bugs et des nouvelles fonctionnalités sont parfois déployées.

## Disclaimer
Better Twitch Predictions n'est pas continuellement en développement ! Il n'y a AUCUNE garantie concernant la correction de bugs et/ou l'amélioration du site.

Il est tout de même possible de signaler des bugs par mail à predictions@marioswitch.fr, tout comme toute autre information que vous souhaiteriez nous faire parvenir.

## Guide d'installation
Si vous souhaitez installer Better Twitch Predictions localement ou votre site web, suivez les étapes ci-dessous :

1. Vous devez posséder un gestionnaire de bases de données MySQL/MariaDB et pouvoir exécuter des fichiers PHP.
2. Copiez le code source à l'emplacement désiré. Vous pouvez utiliser ``git clone https://github.com/MarioSwitch/BetterTwitchPredictions`` en vous plaçant dans le dossier parent de là où vous souhaitez l'installer (car cette commande crée elle-même le dossier BetterTwitchPredictions).
3. Dans votre gestionnaire de bases de données, créez une nouvelle base de données (en ligne de commande ou graphiquement (avec phpMyAdmin par exemple)) et initialisez-la avec le fichier ``database.sql``
4. Configurez votre base de données. Pour cela, 2 méthodes sont possibles et sont équivalentes :
    * Créez config.php à la racine du dossier BetterTwitchPredictions (là où sont les dossiers ``svg`` et ``views``) et déclarez les 4 variables nécessaires (détaillées dans ``sql.php``)
    * Dans sql.php, remplacez la ligne ``include_once config.php`` par la déclaration de ces 4 variables.
Il est tout de même préférable de créer un fichier séparé (c'est-à-dire la première solution) car elle permet de bien séparer le code des informations de connexion.
5. Testez votre installation en vous rendant sur le dossier BetterTwitchPredictions avec votre navigateur.
6. Créez le premier compte à l'aide des boutons de la barre de menu et en remplissant les champs. Il est recommandé d'avoir un compte "modérateur", notamment pour les installations publiques. Pour ce faire, une fois le compte créé, allez dans votre gestionnaire de bases de données et remplacez manuellement le "0" présent dans la table "users", colonne "mod" par un "1". En rafraîchissant la page, vous devriez voir l'icône de modérateur à côté de votre pseudo. Votre compte pourra alors gérer toutes les prédictions (y compris celles des autres utilisateurs) et supprimer n'importe quel compte.