<?php
$user = $_REQUEST["user"];
$userExists = intSQL("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$user]);
if(!$userExists){
    echo("<h1>Le compte \"$user\" n'existe pas !</h1>");
    die("");
}
$now = stringSQL("SELECT NOW();");
$online = stringSQL("SELECT `updated` FROM `users` WHERE `username` = ?;", [$user]);
$onlineDate = substr($online,0,10);
$onlineTime = substr($online,11,8);
echo "<script src=\"countdown.js\"></script>";
echo "<script>countdownTo(\"" . $onlineDate . "T" . $onlineTime . "Z\", \"à l'instant\", \"il y a %countup\", \"onlineCountdown\");</script>";

//Stats
$points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$user]);
$rank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $points) + 1;
$statsPointsSpent = intSQL("SELECT SUM(points) FROM `votes` WHERE `user` = ?;", [$user]);
$statsTotalBets = intSQL("SELECT COUNT(*) FROM `votes` WHERE `user` = ?;", [$user]);
$statsTotalCreated = intSQL("SELECT COUNT(*) FROM `predictions` WHERE `user` = ?;", [$user]);

//Predictions created
$predictionsCreatedText = "";
$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND NOW() < `ended` AND `answer` IS NULL;", [$user]);
$predictionsCreatedText = $predictionsCreatedText . "<h3>En cours</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsCreatedText = $predictionsCreatedText . "<h3>En attente de réponse</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsCreatedText = $predictionsCreatedText . "<h3>Terminées</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}

//Predictions participated
$predictionsParticipatedText = "";
$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En cours</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . $predictionsParticipated[$i]["points"] . " points</p><br/>";
    }
}

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En attente de réponse</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . $predictionsParticipated[$i]["points"] . " points</p><br/>";
    }
}

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>Terminées</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . $predictionsParticipated[$i]["points"] . " points</p><br/>";
    }
}

//Display
echo("
    <h1>" . $user . "</h1>
    <p>Dernière connexion <abbr title='" . $online . " UTC' id='onlineCountdown'></abbr></p>
    <hr>
	<h2>Statistiques</h2>
	<p>" . number_format($points, 0, '', ' ') . " points (" . $rank . "<sup>e</sup>)</p>
	<p>A misé <b>" . $statsTotalBets . " </b> fois pour un total de <b>" . number_format($statsPointsSpent, 0, '', ' ') . "</b> points</p>
	<p>A créé <b>" . $statsTotalCreated . "</b> prédictions</p>
    <hr>
	<h2>Prédictions créées</h2>
	<p>" . $predictionsCreatedText . "</p>
    <hr>
	<h2>Participations à des prédictions</h2>
	<p>" . $predictionsParticipatedText . "</p>
");
if($user == $_SESSION["user"]){
    echo("
        <hr>
        <h2>Gérer le compte</h2>
        <p>Vous pouvez <a href='?view=changePassword'>changer votre mot de passe</a> ou <a href='?view=deleteAccount'>supprimer votre compte</a>.</p>
    ");
}
?>