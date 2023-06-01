<?php
if(!userConnected()){
    header("Location:index.php?view=home");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur.";
            break;
        case "old_password":
            echo "Le mot de passe actuel est incorrect !";
            break;
        case "password":
            echo "Les mots de passe ne correspondent pas !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "<br>Veuillez réessayer.</p>";
}
?>
<h1>Changer le mot de passe de votre compte (<?php echo displayUsername($_SESSION["user"]) ?>)</h1>
<form role='form' action='controller.php'>
    <input type='hidden' name='username' value='<?php echo($_SESSION["user"]) ?>'>
    <label for='password'>Mot de passe actuel</label>
    <input type='password' id='password' name='password' required='required'>
    <br/>
    <label for='newpassword'>Nouveau mot de passe</label>
    <input type='password' name='newpassword' required='required'>
    <br/>
    <label for='newpasswordconfirmation'>Confirmer le nouveau mot de passe</label>
    <input type='password' name='newpasswordconfirmation' required='required'>
    <button type='submit' name='action' value='changePassword'>Confirmer le changement de mot de passe</button>
</form>