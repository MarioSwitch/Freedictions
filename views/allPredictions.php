<?php
$count = intSQL("SELECT COUNT(*) FROM `predictions`");
echo "<h1>Liste des prédictions</h1>";
echo "<p>Ci-dessous, la liste des " . displayInt($count) . " prédictions.</p>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended`;");
$count = $predictions?count($predictions):0;
echo "<h2>Prédictions en cours (" . $count . ")</h2>";
echo "<table><tr><th>ID</th><th>Titre</th><th>Créateur</th><th>Création (UTC)</th><th>Fin des votes (UTC) ▲</th></tr>";
if(!$predictions){
    echo "<tr><td colspan='5'>Aucune prédiction</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td></tr>";
    }
}
echo "</table>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended` FROM `predictions` WHERE `ended` < NOW() AND `answer` IS NULL ORDER BY `ended`;");
$count = $predictions?count($predictions):0;
echo "<h2>Prédictions en attente de réponse (" . displayInt($count) . ")</h2>";
echo "<table><tr><th>ID</th><th>Titre</th><th>Créateur</th><th>Création (UTC)</th><th>Fin des votes (UTC) ▲</th></tr>";
if(!$predictions){
    echo "<tr><td colspan='5'>Aucune prédiction</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td></tr>";
    }
}
echo "</table>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended`, `answer` FROM `predictions` WHERE `answer` IS NOT NULL ORDER BY `id` DESC;");
$count = $predictions?count($predictions):0;
echo "<h2>Prédictions terminées (" . displayInt($count) . ")</h2>";
echo "<table><tr><th>ID ▼</th><th>Titre</th><th>Créateur</th><th>Création (UTC)</th><th>Fin des votes (UTC)</th><th>Réponse</th></tr>";
if(!$predictions){
    echo "<tr><td colspan='6'>Aucune prédiction</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        $answer = stringSQL("SELECT `name` FROM `choices` WHERE `id`=?;", [$predictions[$i]["answer"]]);
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td><td>" . $answer . "</td></tr>";
    }
}
echo "</table>";
?>