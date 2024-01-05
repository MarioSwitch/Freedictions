<?php
if(!isConnected()){
    header("Location:index.php?view=home");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur.";
            break;
        case "password":
            echo "Le mot de passe est incorrect !";
            break;
        case "forbidden":
            echo "Vous n'avez pas le droit de supprimer ce compte !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "<br>Veuillez réessayer.</p>";
}

if($_COOKIE["username"] == $_REQUEST["user"]){
    echo "
    <h1>Supprimer votre compte (" . displayUsername($_REQUEST["user"]) . ")</h1>
        <form role='form' action='controller.php'>
        <input type='hidden' name='username' value='" . $_REQUEST["user"] . "'>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' required='required'>
        <br/>
        <p>La suppression de votre compte est irréversible.<br>En supprimant votre compte, toutes les prédictions que vous avez créées ainsi que vos mises seront supprimées.</p>
        <button type='submit' name='action' value='deleteAccount'>Confirmer la suppression du compte</button>
    </form>";
}else if(isMod()){
    echo "
    <h1>Supprimer le compte : " . displayUsername($_REQUEST["user"]) . "</h1>
        <form role='form' action='controller.php'>
        <input type='hidden' name='username' value='" . $_REQUEST["user"] . "'>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' required='required'>
        <br/>
        <p>Toutes les données associées à cet utilisateur seront supprimées.</p>
        <button type='submit' name='action' value='deleteAccount'>Confirmer la suppression du compte</button>
    </form>";
}else{
    header("Location:index.php?view=home");
    die("");
}