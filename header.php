<?php

include_once "sql.php";

if(userConnected()){
    rawSQL("UPDATE `users` SET `updated` = NOW() WHERE username = ?;", [$_SESSION["user"]]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Better Twitch Predictions</title>
    <link rel="shortcut icon" href="svg/website.svg" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="navbar">
    <a class="icon" href="index.php?view=home"><img src="svg/website.svg" alt="Icône du site" height="40px" width="40px"></a>
    <a class="header-svg" href="index.php?view=info"><img src="svg/info.svg" width="25px" height="25px"></a>
    <a class="header-svg" href="index.php?view=leaderboard"><img src="svg/leaderboard.svg" width="25px" height="25px"></a>
    <form method="GET" action="controller.php" class="search-form">
        <input class="header-search" type="text" placeholder="Rechercher" name="search">
        <button type="submit" name="action" class="header-button" value="search"></button>
    </form>
    <div class="header-right">
        <?php
        // Si l'utilisateur n'est pas connecte, on affiche un lien de connexion et/ou d'inscription
        if (!userConnected()){
            echo "<a class='header-svg' href='index.php?view=signup'><img src='svg/signup.svg' width='25px' height='25px'></a>";
            echo "<a class='header-svg' href='index.php?view=signin'><img src='svg/login.svg' width='25px' height='25px'></a>";
        } //Si il est connecté, on affiche un lien "profil", "créer un prédiction"
        else {
            $username = stringSQL("SELECT `username` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]);
            $points = number_format(intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]), 0, '', ' ');
            echo "<p class='header-text'>" . displayUsername($username) . " ($points points)</p>";
            echo "<a class='header-svg' href='index.php?view=profile&user=$username'><img src='svg/profile.svg' width='25px' height='25px'></a>";
            echo "<a class='header-svg' href='index.php?view=createPrediction'><img src='svg/new.svg' width='25px' height='25px'></a>";
            echo "<a class='header-svg' href='controller.php?action=logout'><img src='svg/logout.svg' width='25px' height='25px'></a>";
        }
        ?>
    </div>
</div>