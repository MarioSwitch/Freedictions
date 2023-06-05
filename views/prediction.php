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
        $pointsPercentage = "<br>(" . number_format(($pointsChoice / $pointsTotal) * 100, 2, ',', '') . " %)";
    }else{
        $pointsPercentage = "";
    }
    if($votesTotal != 0 && $votesChoice != 0){
        $votesPercentage = "<br>(" . number_format(($votesChoice / $votesTotal) * 100, 2, ',', '') . " %)";
    }else{
        $votesPercentage = "";
    }
    if($pointsPercentage != ""){
        $winRate = number_format($pointsTotal / $pointsChoice, 2, ',', ' ');
    }else{
        $winRate = "-";
    }
    $pointsMax = intSQL("SELECT MAX(points) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$_REQUEST["id"], $choiceID]);
    $choiceName = $prediChoices[$i]["name"];
    $prediChoicesText = $prediChoicesText . "<tr><td>" . $choiceName . "</td><td>" . $votesChoice . $votesPercentage . "</td><td>" . number_format($pointsChoice, 0, '', ' ') . $pointsPercentage .  "</td><td>" . $winRate . "</td><td>" . number_format($pointsMax, 0, '', ' ') . "</td></tr>";
}
$prediChoicesText = $prediChoicesText . "</table><br><p>Au total, " . $votesTotal . " personnes ont parié sur cette prédiction pour un total de " . number_format($pointsTotal, 0, '', ' ') . " points.</p>";

//Dynamic content
if (!userConnected()){
    $mode = "disconnected";
} elseif ($prediCreator == $_SESSION["user"] && !userMod()){
    $mode = "creator";
} elseif (intSQL("SELECT COUNT(*) FROM `votes` WHERE `user` = ? AND `prediction` = ?;", [$_SESSION["user"], $_REQUEST["id"]]) == 1){
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
$pointsMax = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]);

//Display
echo("
<h1>" . $prediTitle . " </h1>
<p>Créé par <a href='?view=profile&user=" . $prediCreator . "'>" . displayUsername($prediCreator) . "</a> <abbr id='createdCountdown' title='" . $prediCreated . " UTC'></abbr></p>
<p><abbr id='endCountdown' title='" . $prediEnd . " UTC'></abbr></p>
<h2>" . $prediNumberOfAnswers . " réponses possibles</h2>
" . $prediChoicesText . "
<hr>
<h2>Parier</h2>
");
switch($mode){
    case "disconnected" :
        echo("<p>Vous devez être connecté pour pouvoir parier !</p>");
    break;

    case "creator" :
        echo("<p>Vous ne pouvez pas parier sur cette prédiction car vous en êtes le créateur.</p>");
    break;

    case "alreadyVoted" :
        $choice = stringSQL("SELECT `choices`.`name` FROM `choices` JOIN `votes` ON `choices`.`id` = `votes`.`choice` WHERE `votes`.`user` = ? AND `votes`.`prediction` = ?;", [$_SESSION["user"], $_REQUEST["id"]]);
        $pointsSpent = intSQL("SELECT `points` FROM `votes` WHERE `user` = ? AND `prediction` = ?;", [$_SESSION["user"], $_REQUEST["id"]]);
        echo("<p>Vous avez parié sur " . $choice . " avec " . number_format($pointsSpent, 0, '', ' ') . " points.</p>");
    break;

    case "waitingAnswer" :
        echo("<p>Les votes sont terminés.</p>");
    break;

    case "normal" :
    default :
        echo("<form role='form' action='controller.php'><input type='hidden' name='prediction' value='" . $_REQUEST["id"] . "'><p>Parier sur " . $dropdownMenu . " avec <input type='number' name='points' min='1' max='" . $pointsMax . "' required='required'> points</p><button type='submit' name='action' value='vote'>Parier</button></form>");
    break;
}
if ($mode == "creator" || userMod())
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
    echo("<hr><h3>" . $prediAnswerTitle . " était la bonne réponse. Les points ont été redistribués !</h3>");
}
?>