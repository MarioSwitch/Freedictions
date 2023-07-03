<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo "La requête contient une erreur.";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "<br>Veuillez réessayer.</p>";
}
if(!eligible()){
    echo "<h1>Créer une nouvelle prédiction</h1>";
    echo "<p>Pour éviter les abus, vous devez respecter les conditions suivantes pour pouvoir créer une prédiction :<br>
        <br>Avoir un compte et y être connecté
        <br><br>Une fois les conditions vérifiées, cette page deviendra un formulaire permettant de créer une prédiction.</p>";
    die();
}
?>
<script src="choices.js"></script>
<h1>Créer une nouvelle prédiction</h1>
<form action="controller.php">
    <div class="top-row">
        <div class="form-group">
            <label for="prediNameBox">Question</label>
            <input type="text" class="form-control" id="prediNameBox" name="name" required="required">
        </div>
        <div id="end" class="form-group">
            <label for="prediEndBox"><abbr title="Le champ est à remplir suivant l'heure locale (c'est-à-dire l'heure qu'il est actuellement chez vous). Toutes les conversions sont automatiquement effectuées pour que le temps restant soit correct peu importe le fuseau horaire.">Fin des votes*</abbr></label>
        </div>
    </div>
    <hr>
    <div id="choices">
        <div class="row">
            <div class="fill">
                <input type="button" class="add-choice" value="Ajouter" onclick="addChoice();">
                <label for="prediChoicesBox">Choix</label>
                <input type="button" class="rm-choice" value="Supprimer" onclick="removeChoice();">
            </div>
        </div>
        <input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 1" required="required">
        <input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 2" required="required">
    </div>
    <button type="submit" name="action" value="createPrediction">Publier</button>
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