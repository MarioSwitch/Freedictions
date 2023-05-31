<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur.";
            break;
        case "username_invalid":
            echo "Le nom d'utilisateur ne correspond pas au format requis.";
            break;
        case "username_taken":
            echo "Le nom d'utilisateur est déjà utilisé !";
            break;
        case "password":
            echo "Les mots de passe ne correspondent pas !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "<br>Veuillez réessayer</p>";
}
?>

<h1>Création de compte</h1>
<form role="form" action="controller.php">
    <label for="username">Nom d'utilisateur</label>
    <input type="text" id="username" name="username" required="required" pattern="[A-Za-z0-9]{4,20}" title="Le nom d'utilisateur doit comporter 4 à 20 caractères et n'être composé que de lettres ou de chiffres.">
    <br/>
    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" required="required">
    <br/>
    <label for="passwordconfirmation">Confirmer le mot de passe</label>
    <input type="password" id="passwordconfirmation" name="passwordconfirmation" required="required">
    <br/>
    <button type="submit" name="action" value="createAccount">Créer</button>
</form>
