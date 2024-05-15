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
?>
<script src="choices.js"></script>
<h1>Créer une nouvelle prédiction</h1>
<?php 
if(!isConnected()){
    echo "<p>La création de prédiction nécessite d'être connecté à un compte !</p>";
    die();
}
if(!eligible()){
    global $eligibleMinimumDays;
    global $eligibleMinimumPoints;
    global $eligibleMinimumPointsSpent;
    global $eligibleMinimumVotes;
    global $eligibleMinimumWins;

    $days = intSQL("SELECT DATEDIFF(NOW(), `created`) FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
    $points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
    $pointsSpent = intSQL("SELECT SUM(`points`) FROM `votes` WHERE `user` = ?;", [$_COOKIE["username"]]);
    $votes = intSQL("SELECT COUNT(*) FROM `votes` WHERE `user` = ?;", [$_COOKIE["username"]]);
    $wins = intSQL("SELECT COUNT(*) FROM `votes` JOIN `predictions` ON votes.prediction = predictions.id WHERE votes.user = ? AND choice = answer;", [$_COOKIE["username"]]);

    echo "<p>Pour éviter les abus, vous devez vérifier les conditions suivantes pour pouvoir créer une prédiction :<br>";
    if($eligibleMinimumDays){
        echo"<br><span class='" . (($days < $eligibleMinimumDays)?"not_completed":"completed") . "'>Avoir un compte depuis au moins $eligibleMinimumDays jours <small>($days / $eligibleMinimumDays)</small></span>";
    }
    if($eligibleMinimumPoints){
        echo "<br><span class='" . (($points < $eligibleMinimumPoints)?"not_completed":"completed") . "'>Avoir au moins $eligibleMinimumPoints points <small>($points / $eligibleMinimumPoints)</small></span>";
    }
    if($eligibleMinimumPointsSpent){
        echo "<br><span class='" . (($pointsSpent < $eligibleMinimumPointsSpent)?"not_completed":"completed") . "'>Avoir dépensé au moins $eligibleMinimumPointsSpent points <small>($pointsSpent / $eligibleMinimumPointsSpent)</small></span>";
    }
    if($eligibleMinimumVotes){
        echo "<br><span class='" . (($votes < $eligibleMinimumVotes)?"not_completed":"completed") . "'>Avoir voté au moins $eligibleMinimumVotes fois <small>($votes / $eligibleMinimumVotes)</small></span>";
    }
    if($eligibleMinimumWins){
        echo "<br><span class='" . (($wins < $eligibleMinimumWins)?"not_completed":"completed") . "'>Avoir gagné au moins $eligibleMinimumWins fois <small>($wins / $eligibleMinimumWins)</small></span>";
    }
    echo "<br><br>Une fois les conditions vérifiées, cette page deviendra un formulaire permettant de créer une prédiction.<br>Notez que les conditions peuvent être modifiées à tout moment, et sans avertissement préalable.</p>";

    die();
}
?>
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
<?php //Les retours à la ligne sont considérés comme des espaces en HTML et décalent donc les 2 premiers choix au niveau de l'affichage. En insérant le texte dans un bloc PHP, les retours à la ligne et les indentations ne sont plus retournés par les "echo" et ne sont donc plus présents dans la page HTML générée. Il est également possible de simplement retirer les retours à la ligne, mais cela dégrade la lisibilité du code.
        echo '<input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 1" required="required">';
        echo '<input type="text" class="prediChoicesBox" name="choices[]" placeholder="Choix 2" required="required">';
    echo '</div>';
?>
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