<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php" || !isset($_SESSION["connecte"])) {
    header("Location:?view=accueil");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='text error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur. Assurez-vous d'avoir correctement rempli tous les champs et réessayez.";
            break;
        default:
            echo "Une erreur inconnue s'est produite, veuillez réessayer.";
            break;
    }
    echo "</p>";
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
        <div id="end" class="form-group">
            <label for="prediEndBox">Fin des votes (heure locale)</label>
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
<script>
	var date = new Date();
	var year = date.getYear()+1900;
	var month = ("0" + (date.getMonth()+1)).slice(-2);
	var day = ("0" + date.getDate()).slice(-2);
	var hours = ("0" + date.getHours()).slice(-2);
	var minutes = ("0" + date.getMinutes()).slice(-2);
	var local = year + "-" + month + "-" + day + "T" + hours + ":" + minutes;
	document.getElementById("end").innerHTML += "<input type='datetime-local' class='form-control' id='prediEndBox' name='end' required='required' min='" + local + "' max='2037-12-31T23:59'>";
	document.getElementById("end").innerHTML += "<input type='hidden' name='offset' value=" + -(date.getTimezoneOffset()) + ">";
</script>
