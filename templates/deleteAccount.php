<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php" || !$_SESSION) {
    header("Location:?view=accueil");
    die("");
}
?>
<h1 class='title'>Supprimer votre compte (<?php echo($_SESSION["user"]) ?>)</h1>
<form class='row' role='form' action='controleur.php'>
    <input type='hidden' name='username' value='<?php echo($_SESSION["user"]) ?>'>
    <input type='password' name='password' class='signup-input' placeholder='Confirmez votre mot de passe' required='required'>
    <button class='button' type='submit' name='action' value='SupprimerCompte'>Confirmer la suppression du compte</button>
</form>
