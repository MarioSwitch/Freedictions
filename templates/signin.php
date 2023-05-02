<?php

// Si la page est appelée directement par son adresse, on redirige en passant par la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
    header("Location:../index.php?view=login");
    die("");
}
if(isset($_SESSION["connecte"])){
    echo("<script type=\"text/javascript\">window.location.href=\"index.php\"</script>");
}
?>

<h1 class="title">Connexion</h1>
<form role="form" action="controleur.php">
    <div class="signin-form-group">
        <label for="usernameBox">Nom d'utilisateur</label>
        <input type="text" class="signin-input" id="usernameBox" name="username" required="required">
    </div>
    <div class="signin-form-group">
        <label for="passwordBox">Mot de passe</label>
        <input type="password" class="signin-input" id="passwordBox" name="password" required="required">
    </div>
    <button type="submit" name="action" value="Connexion" class="btn btn-default">Se connecter</button>
</form>




