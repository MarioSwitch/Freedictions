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
<p class="error">Ce site n'est pas terminé et peut contenir des bugs.</p>
<h1>[insérer nom du site]</h1>
<h2>[insérer slogan/description]</h2>
<hr>
<h2>Prédictions ouvertes</h2>
<?php
$predictions = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
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