<?php

// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php" || isset($_SESSION["connecte"])) {
    header("Location:?view=accueil");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='text error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur. Assurez-vous d'avoir correctement rempli tous les champs et réessayez.";
            break;
        case "username":
            echo "Le nom d'utilisateur est déjà utilisé ! Veuillez réessayer.";
            break;
        default:
            echo "Une erreur inconnue s'est produite, veuillez réessayer.";
            break;
    }
    echo "</p>";
}
?>

<h1 class="title">Création de compte</h1>
<form role="form" action="controleur.php">
    <div class="top-row">
        <div class="form-group">
            <label for="usernameBox">Nom d'utilisateur</label>
            <input type="text" class="signup-input" id="usernameBox" name="username" required="required" pattern="[A-Za-z0-9]{4,20}" title="Le nom d'utilisateur doit comporter 4 à 20 caractères et n'être composé que de lettres ou de chiffres.">
        </div>
        <div class="form-group">
            <label for="displaynameBox">Nom public (affiché sur le site)</label>
            <input type="text" class="signup-input" id="displaynameBox" name="displayname" required="required">
        </div>
    </div>
    <div class="bottom-row">
        <div class="form-group">
            <label for="passwordBox">Mot de passe</label>
            <input type="password" class="signup-input" id="passwordBox" name="password" required="required">
        </div>
        <div class="form-group">
            <label for="passwordconfirmationBox">Confirmer le mot de passe</label>
            <input type="password" class="signup-input" id="passwordconfirmationBox" name="passwordconfirmation" required="required">
        </div>
    </div>
    <button type="submit" name="action" value="Inscription" class="btn btn-default">Créer</button>
</form>




