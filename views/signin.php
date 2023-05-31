<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "credentials":
            echo "Les informations saisies sont incorrectes !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "<br>Veuillez réessayer.</p>";
}
?>

<h1 class="title">Connexion</h1>
<form role="form" action="controller.php">
    <label for="username">Nom d'utilisateur</label>
    <input type="text" id="username" name="username" required="required">
    <br/>
    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" required="required">
    <br/>
    <button type="submit" name="action" value="login">Se connecter</button>
</form>
