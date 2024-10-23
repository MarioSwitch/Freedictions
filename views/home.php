<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "forbidden":
            echo getString("error_forbidden");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "</p>";
}
echo "<h1>" . getString("site_name") . "</h1>";
echo "<hr>";
echo "<h2>Nouveautés de la version 2.1</h2>
    <p>
        Le 2 septembre 2024, le site a reçu une mise à jour majeure : la version 2.1.<br><br>
        Cette version apporte, outre les multiples corrections mineures, une grande nouveauté : le support multilingue !<br>
        Le site est déjà disponible en anglais et en allemand (bêta).<br>
        Si vous souhaitez contribuer à la traduction du site, des informations complémentaires sont disponibles dans la partie « Notes pour les traducteurs » de la page « À propos du site ».
    </p>
    <br>
    <hr class=\"mini\">
    <h3>Serveur Discord</h3>
    <p>
        Échangez avec la communauté, discutez des prédictions, proposez des idées d'amélioration et bien plus encore, en rejoignant le serveur Discord officiel du site !<br>
        Lien d'invitation : <a href=\"https://discord.gg/PCKx4qf9XZ\">https://discord.gg/PCKx4qf9XZ</a>
    <hr>";
$predictions = arraySQL("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$predictionsCount = $predictions?count($predictions):0;
$predictionsEnded = [];
if($predictions){
    foreach($predictions as $prediction){
        $id = $prediction["id"];
        $ended = $prediction["ended"];
        $predictionsEnded[$id] = $ended;
    }
}
echo "<h2>" . getString("predictions_ongoing") . " (" . displayInt($predictionsCount) . ")</h2>";
if(isConnected()){
    // Predictions not participated
    $predictionsNotParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title` FROM `predictions` WHERE `predictions`.`ended` > NOW() AND `predictions`.`id` NOT IN (SELECT `predictions`.`id` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND `answer` IS NULL) ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
    $predictionsNotParticipatedCount = $predictionsNotParticipated?count($predictionsNotParticipated):0;
    echo "<h3>" . getString("home_bet_waiting") . " (" . displayInt($predictionsNotParticipatedCount) . ")</h3>";
    if(!$predictionsNotParticipated){
        echo "<p>" . getString("predictions_none") . "</p>";
    }else{
        foreach($predictionsNotParticipated as $prediction){
            $id = $prediction["id"];
            $title = $prediction["title"];
            $link = "index.php?view=prediction&id=" . $id;
            echo "<a href=\"$link\">" . $title . "</a>";
            echo "<p>" . getString("bets_end") . " <abbr id='ended_$id'>" . $predictionsEnded[$id] . "</abbr></p><br>";
        }
    }

    // Separator
    echo "<hr class=\"mini\">";

    // Predictions participated
    $predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
    $predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
    echo "<h3>" . getString("home_bet_already") . " (" . displayInt($predictionsParticipatedCount) . ")</h3>";
    if(!$predictionsParticipated){
        echo "<p>" . getString("predictions_none") . "</p>";
    }else{
        foreach($predictionsParticipated as $prediction){
            $id = $prediction["id"];
            $title = $prediction["title"];
            $choice = $prediction["name"];
            $points = $prediction["points"];
            $link = "index.php?view=prediction&id=" . $id;
            echo "<a href=\"$link\">" . $title . "</a>";
            echo "<p>" . getString("prediction_bet_info", [$choice, displayInt($points)]) . "</p>";
            echo "<p>" . getString("bets_end") . " <abbr id='ended_$id'>" . $predictionsEnded[$id] . "</abbr></p><br>";
        }
    }
}else{
    // All opened predictions
    if(!$predictions){
        echo "<p>" . getString("predictions_none") . "</p>";
        die("");
    }
    foreach($predictions as $prediction){
        $id = $prediction["id"];
        $title = $prediction["title"];
        echo "<a href='?view=prediction&id=" . $id . "'>" . $title . "</a>";
        echo "<p>" . getString("bets_end") . " <abbr id='ended_$id'>" . $predictionsEnded[$id] . "</abbr></p><br>";
    }
}

include_once "time.js.php";
foreach($predictionsEnded as $id => $ended){
    echo "<script>displayDateTime(\"" . $ended . "\",\"ended_$id\");</script>";
}