<?php
$user = $_REQUEST["user"];
$detailed = array_key_exists("detailed", $_REQUEST)?($_REQUEST["detailed"] == "true"):false;
$userExists = intSQL("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$user]);
if(!$userExists){
    echo("<h1>Le compte \"$user\" n'existe pas !</h1>");
    die("");
}

//Timestamps
$created = stringSQL("SELECT `created` FROM `users` WHERE `username` = ?;", [$user]);
$createdDate = substr($created,0,10);
$createdTime = substr($created,11,8);

$online = stringSQL("SELECT `updated` FROM `users` WHERE `username` = ?;", [$user]);
$onlineDate = substr($online,0,10);
$onlineTime = substr($online,11,8);

//Values
$streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$user]);
$points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$user]);
$totalCreated = intSQL("SELECT COUNT(*) FROM `predictions` WHERE `user` = ?;", [$user]);
$totalBets = intSQL("SELECT COUNT(*) FROM `votes` WHERE `user` = ?;", [$user]);
$totalBetsPoints = intSQL("SELECT SUM(points) FROM `votes` WHERE `user` = ?;", [$user]);
$answerBets = intSQL("SELECT COUNT(*) FROM `votes` JOIN `predictions` ON votes.prediction = predictions.id WHERE votes.user = ? AND predictions.answer IS NOT NULL;", [$user]);
$answerBetsPoints = intSQL("SELECT SUM(points) FROM `votes` JOIN `predictions` ON votes.prediction = predictions.id WHERE votes.user = ? AND predictions.answer IS NOT NULL;", [$user]);
$correctBets = intSQL("SELECT COUNT(*) FROM `votes` JOIN `predictions` ON votes.prediction = predictions.id WHERE votes.user = ? AND choice = answer;", [$user]);
$correctBetsPoints = intSQL("SELECT SUM(points) FROM `votes` JOIN `predictions` ON votes.prediction = predictions.id WHERE votes.user = ? AND choice = answer;", [$user]);
$correctBetsPercentage = $answerBets?($correctBets/$answerBets*100):"N/A";
$correctBetsPercentagePoints = $answerBetsPoints?($correctBetsPoints/$answerBetsPoints*100):"N/A";

//Ranks
$rankStreak = intSQL("SELECT COUNT(*) FROM `users` WHERE `streak` > " . $streak) + 1;
$rankPoints = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $points) + 1;
$rankCreated = intSQL("SELECT COUNT(*) FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `totalCreated` FROM `predictions` GROUP BY `user`) `predictions2` ON `users`.`username` = `predictions2`.`user` WHERE `totalCreated` > " . $totalCreated) + 1;
$rankBets = intSQL("SELECT COUNT(*) FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `totalBets` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `totalBets` > " . $totalBets) + 1;
$rankBetsPoints = intSQL("SELECT COUNT(*) FROM `users` LEFT JOIN (SELECT `user`, SUM(`points`) AS `pointsSpent` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `pointsSpent` > " . $totalBetsPoints) + 1;

//Predictions created
$predictionsCreatedText = "";
$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND NOW() < `ended` AND `answer` IS NULL;", [$user]);
$predictionsCreatedCount = $predictionsCreated?count($predictionsCreated):0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>En cours (" . $predictionsCreatedCount . ")</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}
$predictionsCreatedText = $predictionsCreatedText . "<hr class='mini'>";

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsCreatedCount = $predictionsCreated?count($predictionsCreated):0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>En attente de réponse (" . $predictionsCreatedCount . ")</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}
$predictionsCreatedText = $predictionsCreatedText . "<hr class='mini'>";

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsCreatedCount = $predictionsCreated?count($predictionsCreated):0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>Terminées (" . $predictionsCreatedCount . ")</h3>";
if($detailed){
    if(!$predictionsCreated){
        $predictionsCreatedText = $predictionsCreatedText . "<p>Aucune</p>";
    }else{
        for ($i=0; $i < count($predictionsCreated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
            $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
        }
    }
}else{
    $predictionsCreatedText = $predictionsCreatedText . "<p>Pour éviter la surcharge de la page, les prédictions terminées sont masquées par défaut. Cliquez <a href=\"" . $_SERVER['REQUEST_URI'] . "&detailed=true\">ici</a> pour les afficher.</p>";
}

//Predictions participated
$predictionsParticipatedText = "";
$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En cours (" . $predictionsParticipatedCount . ")</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . displayInt($predictionsParticipated[$i]["points"]) . " points</p><br/>";
    }
}
$predictionsParticipatedText = $predictionsParticipatedText . "<hr class='mini'>";

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En attente de réponse (" . $predictionsParticipatedCount . ")</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . displayInt($predictionsParticipated[$i]["points"]) . " points</p><br/>";
    }
}
$predictionsParticipatedText = $predictionsParticipatedText . "<hr class='mini'>";

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
if($predictionsParticipatedCount > 0){
    $predictionsParticipatedText = $predictionsParticipatedText . "<h3>Terminées (" . $predictionsParticipatedCount . ")</h3>";
    if($detailed){
        for ($i=0; $i < count($predictionsParticipated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
            $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>Parié sur " . $predictionsParticipated[$i]["name"] . " avec " . displayInt($predictionsParticipated[$i]["points"]) . " points</p><br/>";
        }
    }else{
        $predictionsParticipatedText = $predictionsParticipatedText . "<p>Pour éviter la surcharge de la page, les prédictions terminées sont masquées par défaut. Cliquez <a href=\"" . $_SERVER['REQUEST_URI'] . "&detailed=true\">ici</a> pour les afficher.</p>";
    }
}else{
    $predictionsParticipatedText = $predictionsParticipatedText . "<h3>Terminées (0)</h3>";
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>Aucune</p>";
}

//Display
echo("
    <h1>" . displayUsername($user) . "</h1>
    <p>Compte créé <abbr title='" . $created . " UTC' id='createdCountdown'></abbr></p>
    <p>Dernière connexion <abbr title='" . $online . " UTC' id='onlineCountdown'></abbr></p>
    <hr>
    <h2>Statistiques</h2>
    <table>
        <tr>
            <th>Statistique</th>
            <th>Valeur</th>
            <th>Rang</th>
        </tr>
        <tr>
            <td>Jours de connexion consécutifs</td>
            <td>" . displayInt($streak, false) . "</td>
            <td>" . displayOrdinal($rankStreak) . "</td>
        </tr>
        <tr>
            <td>Points</td>
            <td>" . displayInt($points, false) . "</td>
            <td>" . displayOrdinal($rankPoints) . "</td>
        </tr>
        <tr>
            <td>Prédictions créées</td>
            <td>" . displayInt($totalCreated, false) . "</td>
            <td>" . displayOrdinal($rankCreated) . "</td>
        </tr>
        <tr>
            <td>Participations à des prédictions</td>
            <td>" . displayInt($totalBets, false) . " mises<br>" . displayInt($totalBetsPoints, false) . " points</td>
            <td>" . displayOrdinal($rankBets) . "<br>" . displayOrdinal($rankBetsPoints) . "</td>
        </tr>
        <tr>
            <td>Bons paris (mises)</td>
            <td>" . displayInt($correctBets, false) . " sur " . $answerBets . "<br><small>" . ($answerBets?displayFloat($correctBetsPercentage):"N/A") . " %</small></td>
            <td>À venir</td>
        </tr>
        <tr>
            <td>Bons paris (points)</td>
            <td>" . displayInt($correctBetsPoints, false) . " sur " . $answerBetsPoints . "<br><small>" . ($answerBetsPoints?displayFloat($correctBetsPercentagePoints):"N/A") . " %</small></td>
            <td>À venir</td>
        </tr>
    </table>
    <hr>
	<h2>Prédictions créées (" . $totalCreated . ")</h2>
	<p>" . $predictionsCreatedText . "</p>
    <hr>
	<h2>Participations à des prédictions (" . $totalBets . ")</h2>
	<p>" . $predictionsParticipatedText . "</p>
");
if(isConnected() && $user == $_COOKIE["username"]){
    echo("
        <hr>
        <h2>Gérer le compte</h2>
        <p>Vous pouvez <a href='?view=changePassword'>changer votre mot de passe</a> ou <a href='?view=deleteAccount&user=$user'>supprimer votre compte</a>.</p>
    ");
} else if (isMod()){
    echo("
        <hr>
        <h2>Modération</h2>
        <p>Vous pouvez <a href='?view=deleteAccount&user=$user'>supprimer le compte</a>.</p>
    ");
}

//JavaScript
echo "<script src=\"countdown.js\"></script>";
echo "<script>countdownTo(\"" . $createdDate . "T" . $createdTime . "Z\", \"dans %countdown\", \"il y a %countup\", \"createdCountdown\");</script>";
echo "<script>countdownTo(\"" . $onlineDate . "T" . $onlineTime . "Z\", \"dans %countdown\", \"il y a %countup\", \"onlineCountdown\");</script>";
?>