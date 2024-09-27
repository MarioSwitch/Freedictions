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
    <hr>";
$predictions = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$predictions_count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_ongoing") . " (" . displayInt($predictions_count) . ")</h2>";
if(isConnected()){
    // Predictions not participated
    $predictionsNotParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `predictions`.`ended` FROM `predictions` WHERE `predictions`.`ended` > NOW() AND `predictions`.`id` NOT IN (SELECT `predictions`.`id` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND `answer` IS NULL) ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
    $predictionsNotParticipatedCount = $predictionsNotParticipated?count($predictionsNotParticipated):0;
    if($predictionsNotParticipatedCount){
        $predictionsNotParticipatedEndDates = [];
        foreach($predictionsNotParticipated as $prediction){
            foreach($prediction as $key => $value){
                if($key == "ended"){
                    array_push($predictionsNotParticipatedEndDates, $value);
                }
            }
        }
    }
    echo "<h3>" . getString("home_bet_waiting") . " (" . displayInt($predictionsNotParticipatedCount) . ")</h3>";
    if(!$predictionsNotParticipated){
        echo "<p>" . getString("predictions_none") . "</p>";
    }else{
        for ($i=0; $i < count($predictionsNotParticipated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsNotParticipated[$i]["id"];
            echo "<a href=\"$link\">" . $predictionsNotParticipated[$i]["title"] . "</a>";
            echo "<p>" . getString("bets_end") . " <abbr id='ended_$i'>" . $predictionsNotParticipatedEndDates[$i] . "</abbr></p><br>";
        }
    }

    // Separator
    echo "<hr class=\"mini\">";

    // Predictions participated
    $predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `predictions`.`ended`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
    $predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
    echo "<h3>" . getString("home_bet_already") . " (" . displayInt($predictionsParticipatedCount) . ")</h3>";
    if(!$predictionsParticipated){
        echo "<p>" . getString("predictions_none") . "</p>";
    }else{
        for ($i=0; $i < count($predictionsParticipated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
            echo "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>" . getString("prediction_bet_info", [$predictionsParticipated[$i]["name"], displayInt($predictionsParticipated[$i]["points"])]) . "</p><br/>";
        }
    }
}else{
    // All opened predictions
    if(!$predictions){
        echo "<p>" . getString("predictions_none") . "</p>";
        die("");
    }
    for($i = 0; $i < count($predictions); $i++){
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        echo "<a href='?view=prediction&id=" . $id . "'>" . $title . "</a><br>";
    }
}

include_once "time.js.php";
if($predictionsNotParticipatedCount){
    for ($i=0; $i < count($predictionsNotParticipated); $i++){
        echo "<script>displayDateTime(\"" . $predictionsNotParticipatedEndDates[$i] . "\",\"ended_$i\");</script>";
    }
}