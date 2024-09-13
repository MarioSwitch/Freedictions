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
        Le site est déjà disponible en anglais (bêta). N'hésitez pas à améliorer les traductions et/ou à traduire le site dans d'autres langues en vous rendant sur la page <a href=\"https://crowdin.com/project/better-twitch-predictions\">Crowdin</a> du projet !<br>
        Si vous souhaitez traduire le site dans une langue autre que celles présentes pour l'instant, contactez le développeur (voir page \"À propos\")<br>
    </p>
    <hr>";
$predictions = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$predictions_count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_ongoing") . " (" . displayInt($predictions_count) . ")</h2>";
if(!$predictions){
    echo "<p>" . getString("predictions_none") . "</p>";
    die("");
}
for($i = 0; $i < count($predictions); $i++){
    $id = $predictions[$i]["id"];
    $title = $predictions[$i]["title"];
    echo "<a href='?view=prediction&id=" . $id . "'>" . $title . "</a><br>";
}