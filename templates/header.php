<?php

// Si la page est appelée directement par son adresse, on redirige en passant par la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php");
    die("");
}

include_once "libs/maLibSQL.pdo.php";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Better Twitch Predictions</title>
    <link rel="shortcut icon" href="ressources/icon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./css/dark.css">
    <script src="./js/custom.js"></script>
    <script src="https://raw.githubusercontent.com/MarioSwitch/Programmes/main/JavaScript/countdown.js"></script>
    <script>countdownTo("2023-04-28T14:30:00Z", "dans %countdown", "il y a %countup", "createdCountdown");</script>
    <script>countdownTo("2023-04-28T18:00:00Z", "Se termine dans %countdown", "Terminé depuis %countup", "endCountdown");</script>
</head>
<body>
<div class="navbar">
    <a class="icon" href="index.php?view=accueil"><img src="ressources/icon.png" alt="Icône du site" height="40px"
                                                       width="40px"></a>
    <form method="GET" action="controleur.php" class="search-form">
        <input class="header-search" type="text" placeholder="Rechercher" name="recherche">
        <button type="submit" name="action" class="header-button" value="Rechercher"></button>
    </form>
    <div class="header-right">
        <?php
        // Si l'utilisateur n'est pas connecte, on affiche un lien de connexion et/ou d'inscription
        if (!valider("connecte", "SESSION")) {
            echo "<a class=\"header-signup\" href=\"index.php?view=signup\"><img src=\"./ressources/svg/createProfile.svg\" width=\"25px\" height=\"25px\"></a>";
	    echo "<a class=\"header-signin\" href=\"index.php?view=signin\"><img src=\"./ressources/svg/login.svg\" width=\"25px\" height=\"25px\"></a>";
        } //Si il est connecté, on affiche un lien "profil", "créer un prédiction"
        else {
	    $displayname = SQLGetChamp("SELECT nickname FROM users WHERE username='$_SESSION[user]';");
	    $points = SQLGetChamp("SELECT points FROM users WHERE username='$_SESSION[user]';");
	    echo "<p class=\"text\">Connecté en tant que $displayname ($points points)</p>";
            echo "<a class=\"header-profile\" href=\"index.php?view=profile\"><img src=\"./ressources/svg/profile.svg\" width=\"25px\" height=\"25px\"></a>";
            echo "<a class=\"header-createPrediction\" href=\"index.php?view=createPrediction\"><img src=\"./ressources/svg/createPrediction.svg\" width=\"25px\" height=\"25px\"></a>";
            echo "<a class=\"header-logout\" href=\"controleur.php?action=Logout\"><img src=\"./ressources/svg/logout.svg\" width=\"25px\" height=\"25px\"></a>";
        }
        ?>
    </div>
</div>








