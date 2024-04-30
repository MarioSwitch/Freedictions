<?php
if(isConnected()){
    rawSQL("UPDATE `users` SET `updated` = NOW() WHERE username = ?;", [$_COOKIE["username"]]);
    setcookie("username", $_COOKIE["username"], time() + 30*24*60*60); // 30 days
    setcookie("password", $_COOKIE["password"], time() + 30*24*60*60); // 30 days
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
    <a href="index.php?view=home"><img src="svg/website.svg" alt="Icône du site"></a>
    <a href="index.php?view=about"><img src="svg/info.svg"></a>
    <a href="index.php?view=leaderboard"><img src="svg/leaderboard.svg"></a>
    <a href="index.php?view=badges"><img src="svg/trophy.svg"></a>
    <a href="index.php?view=allUsers"><img src="svg/allUsers.svg"></a>
    <a href="index.php?view=allPredictions"><img src="svg/allPredictions.svg"></a>
    <form method="GET" action="controller.php" class="search-form">
        <input class="header-search" type="text" placeholder="Rechercher" name="search">
        <button type="submit" name="action" class="header-button" value="search"></button>
    </form>
    <div class="header-right">
        <?php
        // Si l'utilisateur n'est pas connecté, on affiche un lien de connexion et/ou d'inscription
        if (!isConnected()){
            echo "<a class='header-svg' href='index.php?view=signup'><img src='svg/signup.svg'></a>";
            echo "<a class='header-svg' href='index.php?view=signin'><img src='svg/login.svg'></a>";
        } //S'il est connecté, on affiche un lien "profil", "créer un prédiction"
        else {
            $username = $_COOKIE["username"];
            $points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$username]);
            echo "<p class='header-text'>" . displayUsername($username) . " <small>(" . displayInt($points, false) . " points)</small></p>";
            echo "<a href='index.php?view=profile&user=$username'><img src='svg/profile.svg'></a>";
            echo "<a href='index.php?view=createPrediction'><img src='svg/new.svg'></a>";
            echo "<a href='controller.php?action=logout'><img src='svg/logout.svg'></a>";
        }
        ?>
    </div>
</div>