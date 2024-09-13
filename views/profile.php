<?php
$user = $_REQUEST["user"];
$detailed = array_key_exists("detailed", $_REQUEST)?($_REQUEST["detailed"] == "true"):false;
$userExists = intSQL("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$user]);
if(!$userExists){
    echo "<h1>" . getString("profile_not_found", [$user]) . "</h1>";
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
$correctBetsPercentage = $answerBets?($correctBets/$answerBets*100):getString("n_a");
$correctBetsPercentagePoints = $answerBetsPoints?($correctBetsPoints/$answerBetsPoints*100):getString("n_a");
$accounts = intSQL("SELECT COUNT(*) FROM `users`");

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
$predictionsCreatedText = $predictionsCreatedText . "<h3>" . getString("predictions_ongoing") . " (" . displayInt($predictionsCreatedCount) . ")</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>" . getString("predictions_none") . "</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}
$predictionsCreatedText = $predictionsCreatedText . "<hr class='mini'>";

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsCreatedCount = $predictionsCreated?count($predictionsCreated):0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>" . getString("predictions_waiting") . " (" . displayInt($predictionsCreatedCount) . ")</h3>";
if(!$predictionsCreated){
    $predictionsCreatedText = $predictionsCreatedText . "<p>" . getString("predictions_none") . "</p>";
}else{
    for ($i=0; $i < count($predictionsCreated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
        $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
    }
}
$predictionsCreatedText = $predictionsCreatedText . "<hr class='mini'>";

$predictionsCreated = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsCreatedCount = $predictionsCreated?count($predictionsCreated):0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>" . getString("predictions_ended") . " (" . displayInt($predictionsCreatedCount) . ")</h3>";
if($detailed){
    if(!$predictionsCreated){
        $predictionsCreatedText = $predictionsCreatedText . "<p>" . getString("predictions_none") . "</p>";
    }else{
        for ($i=0; $i < count($predictionsCreated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsCreated[$i]["id"];
            $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$link\">" . $predictionsCreated[$i]["title"] . "</a><br/>";
        }
    }
}else{
    $predictionsCreatedText = $predictionsCreatedText . "<p>" . getString("profile_detailed", ["<a href=\"" . $_SERVER['REQUEST_URI'] . "&detailed=true\">" . getString("profile_detailed_here") . "</a>"]) . "</p>";
}

//Predictions participated
$predictionsParticipatedText = "";
$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>" . getString("predictions_ongoing") . " (" . displayInt($predictionsParticipatedCount) . ")</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>" . getString("predictions_none") . "</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>" . getString("profile_prediction_bet", [$predictionsParticipated[$i]["name"], displayInt($predictionsParticipated[$i]["points"])]) . "</p><br/>";
    }
}
$predictionsParticipatedText = $predictionsParticipatedText . "<hr class='mini'>";

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND NOW() > `ended` AND `answer` IS NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>" . getString("predictions_waiting") . " (" . displayInt($predictionsParticipatedCount) . ")</h3>";
if(!$predictionsParticipated){
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>" . getString("predictions_none") . "</p>";
}else{
    for ($i=0; $i < count($predictionsParticipated); $i++){
        $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
        $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>" . getString("profile_prediction_bet", [$predictionsParticipated[$i]["name"], displayInt($predictionsParticipated[$i]["points"])]) . "</p><br/>";
    }
}
$predictionsParticipatedText = $predictionsParticipatedText . "<hr class='mini'>";

$predictionsParticipated = arraySQL("SELECT `predictions`.`id`, `predictions`.`title`, `choices`.`name`, `votes`.`points` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `votes` ON `votes`.`choice` = `choices`.`id` WHERE `votes`.`user` = ? AND `answer` IS NOT NULL;", [$user]);
$predictionsParticipatedCount = $predictionsParticipated?count($predictionsParticipated):0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>" . getString("predictions_ended") . " (" . displayInt($predictionsParticipatedCount) . ")</h3>";
if($detailed){
    if(!$predictionsParticipated){
        $predictionsParticipatedText = $predictionsParticipatedText . "<p>" . getString("predictions_none") . "</p>";
    }else{
        for ($i=0; $i < count($predictionsParticipated); $i++){
            $link = "index.php?view=prediction&id=" . $predictionsParticipated[$i]["id"];
            $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$link\">" . $predictionsParticipated[$i]["title"] . "</a><p>" . getString("profile_prediction_bet", [$predictionsParticipated[$i]["name"], displayInt($predictionsParticipated[$i]["points"])]) . "</p><br/>";
        }
    }
}else{
    $predictionsParticipatedText = $predictionsParticipatedText . "<p>" . getString("profile_detailed", ["<a href=\"" . $_SERVER['REQUEST_URI'] . "&detailed=true\">" . getString("profile_detailed_here") . "</a>"]) . "</p>";
}

//Display
echo "
    <h1>" . displayUsername($user) . "</h1>
    <p>" . getString("profile_created") . " <abbr title='" . $created . " UTC' id='createdCountdown'></abbr></p>
    <p>" . getString("online") . " <abbr title='" . $online . " UTC' id='onlineCountdown'></abbr></p>
    <hr>
    <h2>" . getString("stats") . "</h2>
    <table>
        <tr>
            <th>" . getString("stat") . "</th>
            <th>" . getString("value") . "</th>
            <th>" . getString("rank") . "</th>
        </tr>
        <tr>
            <td>" . getString("streak") . "</td>
            <td>" . displayInt($streak) . "</td>
            <td><p><a href=\"?view=allUsers&order=streakHigh\">" . displayOrdinal($rankStreak) . "</a></p></td>
        </tr>
        <tr>
            <td>" . getString("points") . "</td>
            <td>" . displayInt($points) . "</td>
            <td><p><a href=\"?view=allUsers&order=pointsHigh\">" . displayOrdinal($rankPoints) . "</a></p></td>
        </tr>
        <tr>
            <td>" . getString("predictions_created") . "</td>
            <td>" . displayInt($totalCreated) . "</td>
            <td><p><a href=\"?view=allUsers&order=predictionsHigh\">" . displayOrdinal($rankCreated) . "</a></p></td>
        </tr>
        <tr>
            <td>" . getString("predictions_participated") . "</td>
            <td>" . displayInt($totalBets) . " " . getString("bets_unit") . "<br>" . displayInt($totalBetsPoints) . " " . getString("points_unit") . "</td>
            <td><p><a href=\"?view=allUsers&order=votesHigh\">" . displayOrdinal($rankBets) . "</a><br><a href=\"?view=allUsers&order=spentHigh\">" . displayOrdinal($rankBetsPoints) . "</p></td>
        </tr>
        <tr>
            <td>" . getString("bets_won") . "</td>
            <td>" . getString("of", [displayInt($correctBets), displayInt($answerBets)]) . ($answerBets?("<br><small>" . getString("percentage", [displayFloat($correctBetsPercentage)]) . "</small>"):"") . "</td>
            <td>" . getString("coming_soon") . "</td>
        </tr>
        <tr>
            <td>" . getString("bets_won_points") . "</td>
            <td>" . getString("of", [displayInt($correctBetsPoints), displayInt($answerBetsPoints)]) . ($answerBetsPoints?("<br><small>" . getString("percentage", [displayFloat($correctBetsPercentagePoints)]) . "</small>"):"") . "</td>
            <td>" . getString("coming_soon") . "</td>
        </tr>
    </table>
    <hr>
	<h2>" . getString("predictions_created") . " (" . displayInt($totalCreated) . ")</h2>
	<p>" . $predictionsCreatedText . "</p>
    <hr>
	<h2>" . getString("predictions_participated") . " (" . displayInt($totalBets) . ")</h2>
	<p>" . $predictionsParticipatedText . "</p>
";
if(isConnected() && $user == $_COOKIE["username"]){
    echo "
        <hr>
        <h2>" . getString("profile_manage") . "</h2>
        <p>" . getString("profile_manage_info", ["<a href='?view=changePassword'>" . getString("profile_manage_info_change_password") . "</a>", "<a href='?view=deleteAccount&user=$user'>" . getString("profile_manage_info_delete_account") . "</a>"]) . "</p>
    ";
} else if (isMod()){
    echo "
        <hr>
        <h2>" . getString("profile_manage_mod") . "</h2>
        <p>" . getString("profile_manage_info_mod", ["<a href='?view=deleteAccount&user=$user'>" . getString("profile_manage_info_delete_account_mod") . "</a>"]) . "</p>
    ";
}

//JavaScript
include_once "countdown.js.php";
echo "<script>countdownTo(\"" . $createdDate . "T" . $createdTime . "Z\", '" . getString("javascript_countdown_in", ["%countdown"]) . "', '" . getString("javascript_countdown_ago", ["%countup"]) . "', 'createdCountdown');</script>";
echo "<script>countdownTo(\"" . $onlineDate . "T" . $onlineTime . "Z\", '" . getString("javascript_countdown_in", ["%countdown"]) . "', '" . getString("javascript_countdown_ago", ["%countup"]) . "', 'onlineCountdown');</script>";

/* Weird behaviour... To be fixed
include_once "UTC_Local_Converter.js.php";
echo "<script>UTCtoLocal(\"$created\",document.querySelectorAll('p')[1]);</script>";
echo "<script>UTCtoLocal(\"$online\",document.querySelectorAll('p')[2]);</script>";
*/