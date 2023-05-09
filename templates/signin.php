<?php

// Si la page est appelée directement par son adresse, on redirige en passant par la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php" || isset($_SESSION["connecte"]))
{
    header("Location:?view=accueil");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='text error'>";
    switch($_REQUEST["error"]){
        case "password":
            echo "Le nom d'utilisateur ou le mot de passe est incorrect ! Vérifiez les informations saisies et réessayez.";
            break;
        default:
            echo "Une erreur inconnue s'est produite, veuillez réessayer.";
            break;
    }
    echo "</p>";
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




