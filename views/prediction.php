<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "vote":
        case "answer":
        case "delete":
            echo "La requête contient une erreur. Assurez-vous d'avoir correctement rempli tous les champs et réessayez.";
            break;
        case "closed":
            echo "La prédiction est terminée, vous ne pouvez plus parier !";
            break;
        case "unauthorized":
            echo "Vous n'avez pas la permission de gérer cette prédiction.";
            break;
        case "too_early":
            echo "Vous ne pouvez pas donner la bonne réponse avant la fin des votes !";
            break;
        case "points":
            echo "Vous n'avez pas assez de points !";
            break;
        default:
            echo "Une erreur inconnue s'est produite, veuillez réessayer.";
            break;
    }
    echo "</p>";
}
$prediExists = intSQL("SELECT COUNT(*) FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]]);
if (!$prediExists){
    echo("<h1>Cette prédiction n'existe pas ou a été supprimée !</h1><br><p>Si vous avez parié sur cette prédiction, vous avez récupéré les points misés !</p>");
    die("");
}
$prediction =  arraySQL("SELECT * FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]]);
$prediTitle = $prediction[0]["title"];
$prediCreator = $prediction[0]["user"];
$prediCreated = $prediction[0]["created"];
$prediCreatedDate = substr($prediCreated,0,10);
$prediCreatedTime = substr($prediCreated,11,8);
$prediEnd = $prediction[0]["ended"];
$prediEndDate = substr($prediEnd,0,10);
$prediEndTime = substr($prediEnd,11,8);
echo "<script src='countdown.js'></script>";
echo "<script>countdownTo('" . $prediCreatedDate . "T" . $prediCreatedTime . "Z', 'dans %countdown', 'il y a %countup', 'createdCountdown');</script>";
echo "<script>countdownTo('" . $prediEndDate . "T" . $prediEndTime . "Z', 'Se termine dans %countdown', 'Terminé depuis %countup', 'endCountdown');</script>";
$prediAnswered = $prediction[0]["answered"];
if($prediAnswered != NULL){
    $prediAnsweredDate = substr($prediAnswered,0,10);
    $prediAnsweredTime = substr($prediAnswered,11,8);
    echo "<script>countdownTo('" . $prediAnsweredDate . "T" . $prediAnsweredTime . "Z', 'dans %countdown', 'il y a %countup', 'answerCountdown');</script>";
}
$prediAnswer = $prediction[0]["answer"];
if ($prediAnswer != NULL){
    $prediAnswerTitle = stringSQL("SELECT `name` FROM `choices` WHERE `id` = ?;", [$prediAnswer]);
}
$prediNumberOfAnswers = intSQL("SELECT COUNT(*) FROM `choices` WHERE `prediction` = ?;", [$_REQUEST["id"]]);
$prediChoices = arraySQL("SELECT * FROM `choices` WHERE `prediction` = ?;", [$_REQUEST["id"]]);
$svgVotes = "<abbr title='Nombre de votes'><img src='svg/people.svg'></abbr>";
$svgPoints = "<abbr title='Points dépensés'><img src='svg/points.svg'></abbr>";
$svgRatio = "<abbr title='Rendement (si vous gagnez, votre mise sera multipliée par ce coefficient)'><img src='svg/cup.svg'>";
$svgMax = "<abbr title='Record de mise'><img src='svg/podium.svg'></abbr>";
$prediChoicesText = "<table><tr><th>Choix</th><th>" . $svgVotes . "</th><th>" . $svgPoints . "</th><th>" . $svgRatio . "</th><th>" . $svgMax . "</th></tr>";
for($i = 0; $i < count($prediChoices); $i++){
    $choiceID = $prediChoices[$i]["id"];
    $votesChoice = intSQL("SELECT COUNT(*) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$_REQUEST["id"], $choiceID]);
    $votesTotal = intSQL("SELECT COUNT(*) FROM `votes` WHERE `prediction` = ?;", [$_REQUEST["id"]]);
    $pointsChoice = intSQL("SELECT SUM(points) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$_REQUEST["id"], $choiceID]);
    $pointsTotal = intSQL("SELECT SUM(points) FROM `votes` WHERE `prediction` = ?;", [$_REQUEST["id"]]);
    if($pointsTotal != 0 && $pointsChoice != 0){
        $pointsPercentage = "<br><small>" . displayFloat(($pointsChoice / $pointsTotal) * 100) . " %</small>";
    }else{
        $pointsPercentage = "";
    }
    if($votesTotal != 0 && $votesChoice != 0){
        $votesPercentage = "<br><small>" . displayFloat(($votesChoice / $votesTotal) * 100) . " %</small>";
    }else{
        $votesPercentage = "";
    }
    if($pointsPercentage != ""){
        $winRate = displayFloat($pointsTotal / $pointsChoice);
    }else{
        $winRate = "-";
    }
    $pointsMaxChoice = intSQL("SELECT MAX(points) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$_REQUEST["id"], $choiceID]);
    $pointsMaxTotal = intSQL("SELECT MAX(points) FROM `votes` WHERE `prediction` = ?;", [$_REQUEST["id"]]);
    $choiceName = $prediChoices[$i]["name"];
    $prediChoicesText = $prediChoicesText . "<tr><td>" . $choiceName . "</td><td>" . displayInt($votesChoice) . $votesPercentage . "</td><td>" . displayInt($pointsChoice) . $pointsPercentage .  "</td><td>" . $winRate . "</td><td>" . displayInt($pointsMaxChoice) . "</td></tr>";
}
$prediChoicesText = $prediChoicesText . "<tr><th>Total</th><th>" . displayInt($votesTotal) . "</th><th>" . displayInt($pointsTotal) . "</th><th>N/A</th><th>" . displayInt($pointsMaxTotal) . "</th></tr></table>";

//Dynamic content
if(array_key_exists("username",$_COOKIE)){
    $creator = ($prediCreator == $_COOKIE["username"]);
}else{
    $creator = false;
}

if (!isConnected()){
    $mode = "disconnected";
} elseif (intSQL("SELECT COUNT(*) FROM `votes` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $_REQUEST["id"]]) == 1){
    $mode = "alreadyVoted";
} elseif ($prediEnd < stringSQL("SELECT NOW();")){
    $mode = "waitingAnswer";
} else{
    $mode = "normal";
}
$dropdownMenu = "<select name='choice'>";
for($i = 0; $i < count($prediChoices); $i++){
    $dropdownMenu = $dropdownMenu . "<option value=" . $prediChoices[$i]["id"] . ">" . $prediChoices[$i]["name"] . "</option>";
}
$dropdownMenu = $dropdownMenu . "</select>";

//Display
echo("
<h1>" . $prediTitle . " </h1>
<p>Créé par <a href='?view=profile&user=" . $prediCreator . "'>" . displayUsername($prediCreator) . "</a> <abbr id='createdCountdown' title='" . $prediCreated . " UTC'></abbr></p>
<p><abbr id='endCountdown' title='" . $prediEnd . " UTC'></abbr></p>");
if($prediAnswered != NULL){
    echo("<p>Réponse donnée <abbr id='answerCountdown' title='" . $prediAnswered . " UTC'></abbr></p>");
}
echo("
<h2>" . $prediNumberOfAnswers . " réponses possibles</h2>
" . $prediChoicesText . "
<hr>
<h2>Parier</h2>
");
switch($mode){
    case "disconnected" :
        echo displayInvite("pouvoir parier");
    break;

    case "alreadyVoted" :
        $choice = stringSQL("SELECT `choices`.`name` FROM `choices` JOIN `votes` ON `choices`.`id` = `votes`.`choice` WHERE `votes`.`user` = ? AND `votes`.`prediction` = ?;", [$_COOKIE["username"], $_REQUEST["id"]]);
        $pointsSpent = intSQL("SELECT `points` FROM `votes` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $_REQUEST["id"]]);
        $pointsMax = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        echo("<p>Vous avez parié sur " . $choice . " avec " . displayInt($pointsSpent) . " points.</p>");
        if($prediEnd >= stringSQL("SELECT NOW();")){
            echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><p>Ajouter <input type='number' name='points' min='1' max='" . $pointsMax . "' required='required'> points à votre mise</p><button type='submit' name='action' value='addPoints'>Ajouter à la mise</button></form>");
        }
    break;

    case "waitingAnswer" :
        echo("<p>Les votes sont terminés.</p>");
    break;

    case "normal" :
    default :
        $pointsMax = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><p>Parier sur " . $dropdownMenu . " avec <input type='number' name='points' min='1' max='" . $pointsMax . "' required='required'> points</p><button type='submit' name='action' value='vote'>Parier</button></form>");
    break;
}
if ($creator || isMod())
{
    echo("<hr><h2>Gérer la prédiction</h2>");
    if ($prediAnswer == NULL){
        if ($prediEnd < stringSQL("SELECT NOW();")){
            echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><p>Définir " . $dropdownMenu . " comme étant la bonne réponse</p><button type='submit' name='action' value='answer'>Terminer la prédiction et redistribuer les points</button></form>");
        } else {
            echo("<p>Vous devez attendre la fin des votes pour donner la bonne réponse !</p>");
        }
        echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><button type='submit' name='action' value='deletePrediction'>Supprimer la prédiction et rendre les points</button></form>");
    } else {
        echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><button type='submit' name='action' value='deletePrediction'>Supprimer la prédiction</button></form>");
    }
}
if ($prediAnswer != NULL){
    echo("<hr><h3>" . $prediAnswerTitle . " était la bonne réponse.</h3>");
    if(isConnected()){
        $choiceID = intSQL("SELECT `choice` FROM `votes` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $_REQUEST["id"]]);
        if($choiceID){
            if($choiceID == $prediAnswer){
                $pointsChoiceVictory = intSQL("SELECT SUM(points) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$_REQUEST["id"], $prediAnswer]);
                $winRateVictory = $pointsTotal / $pointsChoiceVictory;
                $earnedPoints = floor($pointsSpent * $winRateVictory);
                $balance = $earnedPoints - $pointsSpent;
                echo("<p>Vous avez gagné " . displayInt($earnedPoints) . " points (+" . displayInt($balance) . ").</p>");
            }else{
                echo("<p>Vous avez perdu les " . displayInt($pointsSpent) . " points misés.</p>");
            }
        }
    }
}
?>