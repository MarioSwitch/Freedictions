<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php?view=accueil");
    die("");
}
if (!isset($_SESSION["connecte"])) {
    echo("<script type=\"text/javascript\">window.location.href=\"index.php?view=signin\"</script>");
}
?>
<div class="page-header">
    <h1 class="title">Créer une nouvelle prédiction</h1>
</div>

<form action="controleur.php">
    <div class="top-row">
        <div class="form-group">
            <label for="prediNameBox">Question</label>
            <input type="text" class="form-control" id="prediNameBox" name="name" required="required">
        </div>
        <div class="form-group">
            <label for="prediEndBox">Fin des votes</label>
            <input type="datetime-local" class="form-control" id="prediEndBox" name="end" required="required"
                   min=<?= date('Y-m-d') . "T" . date('H:i') ?> max="2038-01-18T20:14">
        </div>
    </div>
    <hr class="line">
    <div id="choices">
        <div class="row">
            <div class="fill">
                <input type="button" class="add-choice" value="Ajouter un choix" onclick="ajouterChoix();">
                <label for="prediChoicesBox">Choix</label>
                <input type="button" class="rm-choice" value="Supprimer un choix" onclick="supprimerChoix();">
            </div>
        </div>
        <input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 1"
               required="required">
        <input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 2"
               required="required">
    </div>
    <button type="submit" name="action" value="Publier" class="btn btn-default">Publier</button>
</form>