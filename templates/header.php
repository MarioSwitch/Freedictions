<?php

// Si la page est appelée directement par son adresse, on redirige en passant par la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php");
    die("");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Better Twitch Predictions</title>
    <link rel="shortcut icon" href="ressources/icon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./css/dark.css">
    <script src="./js/custom.js"></script>

</head>
<body>
<div class="navbar">
    <a class="icon" href="index.php?view=accueil"><img src="ressources/icon.png" alt="Icône du site" height="40px"
                                                       width="40px"></a>
    <a class="header-accueil" href="index.php?view=accueil">Accueil</a>
    <form method="GET" action="controleur.php" class="search-form">
        <input class="header-search" type="text" placeholder="Rechercher" name="recherche">
        <button type="submit" name="action" class="header-button" value="Rechercher"></button>
    </form>
    <div class="header-right">
        <?php
        // Si l'utilisateur n'est pas connecte, on affiche un lien de connexion et/ou d'inscription
        if (!valider("connecte", "SESSION")) {
            echo "<a class=\"header-signin\" href=\"index.php?view=signin\">Connexion</a>";
            echo "<a class=\"header-signup\" href=\"index.php?view=signup\">Inscription</a>";
        } //Si il est connecté, on affiche un lien "profil", "créer un prédiction"
        else {
            echo "<a class=\"header-profile\" href=\"index.php?view=profile\">Mon Profil</a>";
            echo "<a class=\"header-createPrediction\" href=\"index.php?view=createPrediction\">Créer une prédiction</a>";
            echo "<a class=\"header-logout\" href=\"controleur.php?action=Logout\">Déconnexion</a>";
        }
        ?>
    </div>
</div>








