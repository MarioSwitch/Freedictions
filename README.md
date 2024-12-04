# Créer sa propre installation
## Étape ?/? : Configuration de votre installation
### Fichier ``config.php``
Pour que votre installation soit fonctionnelle, vous devez déclarer un certain nombre de constantes de configuration dans un fichier nommé ``config.php`` situé à la racine du projet.

- Toutes les constantes commençant par ``CONFIG_DATABASE`` servent à la connexion à la base de données. Modifiez ces paramètres afin de les faire correspondre à votre installation.

Voici un exemple type de fichier :
```php
<?php
// Configuration de la connexion à la base de données
const CONFIG_DATABASE_HOST = "localhost"; // Adresse du serveur de la base de données
const CONFIG_DATABASE_NAME = "predictions"; // Nom de la base de données
const CONFIG_DATABASE_USER = "root"; // Nom d'un utilisateur MariaDB/MySQL ayant accès à la base de données
const CONFIG_DATABASE_PASSWORD = ""; // Mot de passe de cet utilisateur

// Autres configurations
const CONFIG_COOKIES_EXPIRATION = 30*24*60*60; // Durée de validité des cookies (connexion et paramètres) sans navigation sur le site de la part de l'utilisateur (en secondes).
const CONFIG_PATH = "/v3"; // Répertoire où se situe le site sur le serveur web (par rapport à « /var/www/html » ou à « C:\xampp\htdocs » par exemple), SANS « / » ou « \ » final ! Il peut être vide.
```

### Autres configurations
N'oubliez pas de mettre à jour la ligne 2 du fichier ``.htaccess`` (« ``RewriteBase /v3/`` »), en remplaçant « ``/v3/`` » par le répertoire où se situe votre installation. Il s'agit normalement du même chemin que la constante ``CONFIG_PATH``, à l'exception de l'ajout du « / » ou « \ » final.

# Crédits
Les icônes proviennent de [Streamline HQ](https://www.streamlinehq.com/).

## Historique des versions
| Version | Statut |      Commits       |          Dates           |        Contributeurs         |
| :-----: | :----: | :----------------: | :----------------------: | :--------------------------: |
|   3.0   | Alpha  | *En développement* |      Novembre 2024       |        *MarioSwitch*         |
|   2.1   | Stable |  2.1.28 – 2.1.61   | Septembre – Octobre 2024 |        *MarioSwitch*         |
|   2.1   |  Bêta  |  2.1.17 – 2.1.27   |      Septembre 2024      |        *MarioSwitch*         |
|   2.1   | Alpha  |   2.1.1 – 2.1.16   |  Août – Septembre 2024   |        *MarioSwitch*         |
|   2.0   | Stable |  2.0.33* – 2.0.57  |    Janvier – Mai 2024    |        *MarioSwitch*         |
|   2.0   | Stable |  Commits 42 – 60   |   Juin – Décembre 2023   |        *MarioSwitch*         |
|   2.0   |  Bêta  |  Commits 29 – 41   |     Mai – Juin 2023      |        *MarioSwitch*         |
|   1.1   | Stable |   Commits 8 – 28   |     Avril – Mai 2023     |        *MarioSwitch*         |
|   1.0   | Stable |   Commits 1 – 7    |     Mai – Juin 2022      | *MarioSwitch* et *yoshakami* |

\* Il y a eu 32 commits précédant celui-ci (29 à 60)