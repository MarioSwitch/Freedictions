<?php
$search = $_REQUEST["query"];
if ($search == ""){
    die("");
}
$opened = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() AND INSTR(title, '{$search}') > 0 ORDER BY `ended` ASC;");
$closed = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` <= NOW() AND INSTR(title, '{$search}') > 0;");
$opened_count = $opened?count($opened):0;
$closed_count = $closed?count($closed):0;
echo "<h1>Résultats de recherche pour \"" . $search . "\"</h1>";
echo "<h2>Prédictions ouvertes ($opened_count)</h2>";
if(!$opened){
    echo "<p>Aucune prédiction ouverte ne correspond à votre recherche.</p>";
}else{
    for($i = 0; $i < count($opened); $i++){
        echo "<a href='?view=prediction&id=" . $opened[$i]["id"] . "'>" . $opened[$i]["title"] . "</a>";
    }
}
echo "<h2>Prédictions fermées ($closed_count)</h2>";
if(!$closed){
        echo "<p>Aucune prédiction fermée ne correspond à votre recherche.</p>";
}else{
    for($i = 0; $i < count($closed); $i++){
        echo "<a href='?view=prediction&id=" . $closed[$i]["id"] . "'>" . $closed[$i]["title"] . "</a>";
    }
}
?>
