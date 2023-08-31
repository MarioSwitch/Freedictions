<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "forbidden":
            echo "Vous ne pouvez pas effectuer cette action !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "</p>";
}
?>
<p class="error">Ce projet est en développement et peut ne pas fonctionner correctement.</p>
<h1>Better Twitch Predictions</h1>
<hr>
<?php
$predictions = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$predictions_count = $predictions?count($predictions):0;
echo "<h2>Prédictions ouvertes ($predictions_count)</h2>";
if(!$predictions){
    echo "<p>Aucune prédiction ouverte</p>";
    die("");
}
for($i = 0; $i < count($predictions); $i++){
    $id = $predictions[$i]["id"];
    $title = $predictions[$i]["title"];
    echo "<a href='?view=prediction&id=" . $id . "'>" . $title . "</a>";
}
?>