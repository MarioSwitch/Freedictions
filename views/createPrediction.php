<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo getString("error_data");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "<br>" . getString("error_try_again") . "</p>";
}
include_once "choices.js.php";
echo "
<h1>" . getString("createPrediction_title") . "</h1>";
if(!isConnected()){
    echo "<p>" . getString("createPrediction_not_connected") . "</p>";
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

    echo "<p>" . getString("createPrediction_eligible_intro") . "<br>";
    if($eligibleMinimumDays){
        echo"<br><span class='" . (($days < $eligibleMinimumDays)?"not_completed":"completed") . "'>" . getString("createPrediction_eligible_days", [$eligibleMinimumDays]) . " <small>($days / $eligibleMinimumDays)</small></span>";
    }
    if($eligibleMinimumPoints){
        echo "<br><span class='" . (($points < $eligibleMinimumPoints)?"not_completed":"completed") . "'>" . getString("createPrediction_eligible_points", [$eligibleMinimumPoints]) . " <small>($points / $eligibleMinimumPoints)</small></span>";
    }
    if($eligibleMinimumPointsSpent){
        echo "<br><span class='" . (($pointsSpent < $eligibleMinimumPointsSpent)?"not_completed":"completed") . "'>" . getString("createPrediction_eligible_points_spent", [$eligibleMinimumPointsSpent]) . " <small>($pointsSpent / $eligibleMinimumPointsSpent)</small></span>";
    }
    if($eligibleMinimumVotes){
        echo "<br><span class='" . (($votes < $eligibleMinimumVotes)?"not_completed":"completed") . "'>" . getString("createPrediction_eligible_bets", [$eligibleMinimumVotes]) . " <small>($votes / $eligibleMinimumVotes)</small></span>";
    }
    if($eligibleMinimumWins){
        echo "<br><span class='" . (($wins < $eligibleMinimumWins)?"not_completed":"completed") . "'>" . getString("createPrediction_eligible_wins", [$eligibleMinimumWins]) . " <small>($wins / $eligibleMinimumWins)</small></span>";
    }
    echo "<br><br>" . getString("createPrediction_eligible_outro1") . "<br>" . getString("createPrediction_eligible_outro2") . "</p>";

    die();
}
echo "
<form action=\"controller.php\">
    <div class=\"top-row\">
        <div class=\"form-group\">
            <label for=\"prediNameBox\">" . getString("createPrediction_form_name") . "</label>
            <input type=\"text\" class=\"form-control\" id=\"prediNameBox\" name=\"name\" required=\"required\">
        </div>
        <div id=\"end\" class=\"form-group\">
            <label for=\"prediEndBox\"><abbr title=\"" . getString("createPrediction_form_end_tooltip") . "\">" . getString("bets_end") . "*</abbr></label>
        </div>
    </div>
    <div class=\"top-row\">
        <div class=\"form-group\">
                <label for=\"prediDesc\">" . getString("createPrediction_form_desc") . "</label>
                <input type=\"text\" class=\"form-control\" id=\"prediDesc\" name=\"desc\" placeholder=\"" . getString("createPrediction_form_desc_placeholder") . "\">
        </div>
    </div>
    <hr>
    <div id=\"choices\">
        <div class=\"row\">
            <div class=\"fill\">
                <input type=\"button\" class=\"add-choice\" value=\"" . getString("createPrediction_form_choices_add") . "\" onclick=\"addChoice();\">
                <label for=\"prediChoicesBox\">" . getString("choices") . "</label>
                <input type=\"button\" class=\"rm-choice\" value=\"" . getString("createPrediction_form_choices_remove") . "\" onclick=\"removeChoice();\">
            </div>
        </div>";
//Les retours à la ligne sont considérés comme des espaces en HTML et décalent donc les 2 premiers choix au niveau de l'affichage. En insérant le texte dans un bloc PHP, les retours à la ligne et les indentations ne sont plus retournés par les "echo" et ne sont donc plus présents dans la page HTML générée. Il est également possible de simplement retirer les retours à la ligne, mais cela dégrade la lisibilité du code.
        echo "<input type=\"text\" class=\"prediChoicesBox\" name=\"choices[]\" placeholder=\"" . getString("choices_number") . "1\" required=\"required\">";
        echo "<input type=\"text\" class=\"prediChoicesBox\" name=\"choices[]\" placeholder=\"" . getString("choices_number") . "2\" required=\"required\">";
    echo '</div>';
echo "
    <button type=\"submit\" name=\"action\" value=\"createPrediction\">" . getString("createPrediction_form_submit") . "</button>
</form>
<script>
	var date = new Date();
	var year = date.getYear()+1900;
	var month = (\"0\" + (date.getMonth()+1)).slice(-2);
	var day = (\"0\" + date.getDate()).slice(-2);
	var hours = (\"0\" + date.getHours()).slice(-2);
	var minutes = (\"0\" + date.getMinutes()).slice(-2);
	var local = year + \"-\" + month + \"-\" + day + \"T\" + hours + \":\" + minutes;
	document.getElementById(\"end\").innerHTML += \"<input type='datetime-local' class='form-control' id='prediEndBox' name='end' required='required' min='\" + local + \"' max='2037-12-31T23:59'>\";
	document.getElementById(\"end\").innerHTML += \"<input type='hidden' name='offset' value=\" + -(date.getTimezoneOffset()) + \">\";
</script>";