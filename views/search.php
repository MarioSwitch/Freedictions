<?php
$search = $_REQUEST["query"];
if ($search == ""){
    die("");
}
$predictionsOuvertes = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() AND INSTR(title, '{$search}') > 0 ORDER BY `ended` ASC;");
$predictionsFermees = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` <= NOW() AND INSTR(title, '{$search}') > 0;");
echo "<h1>Résultats de recherche pour \"" . $search . "\"</h1>";
echo "<h2>Prédictions ouvertes</h2>";
if(!$predictionsOuvertes){
    echo "<p>Aucune prédiction ouverte ne correspond à votre recherche.</p>";
}else{
    for($i = 0; $i < count($predictionsOuvertes); $i++){
        echo "<a href='?view=prediction&id=" . $predictionsOuvertes[$i]["id"] . "'>" . $predictionsOuvertes[$i]["title"] . "</a>";
    }
}
echo "<h2>Prédictions fermées</h2>";
if(!$predictionsFermees){
        echo "<p>Aucune prédiction fermée ne correspond à votre recherche.</p>";
}else{
    for($i = 0; $i < count($predictionsFermees); $i++){
        echo "<a href='?view=prediction&id=" . $predictionsFermees[$i]["id"] . "'>" . $predictionsFermees[$i]["title"] . "</a>";
    }
}
?>
