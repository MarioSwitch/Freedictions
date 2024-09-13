<?php
$tableTop = array_key_exists("start", $_REQUEST)?$_REQUEST["start"]:1;
$tableBottom = array_key_exists("end", $_REQUEST)?$_REQUEST["end"]:50;
if(!is_numeric($tableTop) || !is_numeric($tableBottom) || $tableTop < 1 || $tableBottom < 1 || $tableTop > $tableBottom){
	header("Location: index.php?view=leaderboard");
	die();
}
$tableTop = intval($tableTop);
$tableBottom = intval($tableBottom);
$sqlLimit = $tableBottom - $tableTop + 1;
$sqlOffset = $tableTop - 1;
$leaderboard = arraySQL("SELECT `username`, `points` FROM `users` ORDER BY `points` DESC LIMIT $sqlLimit OFFSET $sqlOffset;");
$myUsername = "";
$myTableTop = NULL;
$myTableBottom = NULL;
$accounts = intSQL("SELECT COUNT(*) FROM `users`");
echo "<h1>" . getString("leaderboard_title") . "</h1>";
echo "<p>" . getString("leaderboard_description") . "</p>";
if(isConnected()){
	$myPoints = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
	$myRank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $myPoints . ";") + 1;
	$myTop = ($myRank / $accounts)*100;
	echo "<p>" . getString("leaderboard_info", [displayOrdinal($myRank), displayInt($accounts)]) . " (" . getString("percentage_top", [displayFloat($myTop)]) . ").</p>";
	$myUsername = $_COOKIE["username"];
	$myTableTop = $myRank - ($myRank % $sqlLimit) + 1;
	$myTableBottom = $myTableTop + $sqlLimit - 1;
}
echo "<hr>";
echo "<table><tr>
	<th>" . getString("rank") . "<br><small>(" . displayInt($tableTop, false) . " – " . displayInt($tableBottom, false) . ")</small></th>
	<th>" . getString("user") . "</th>
	<th>" . getString("points") . "</th>
</tr>";
if(!$leaderboard){
	echo "<tr><td colspan='3'>" . getString("users_none") . "</td></tr>";
}else{
	for($i = 0; $i < count($leaderboard); $i++){
		$user = $leaderboard[$i]["username"];
		$points = $leaderboard[$i]["points"];
		$rank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $points . ";") + 1;
		echo "<tr" . ($myUsername == $user ?" class=\"selected_answer\"":"") . "><td>" . displayOrdinal($rank) . "</td><td><p><a href='?view=profile&user=" . $user . "'>" . displayUsername($user) . "</a></p></td><td>" . displayInt($points) . "</td></tr>";
	}
}
echo "<tr><td colspan='3'>";
$previousTop = $tableTop - $sqlLimit;
$previousBottom = $tableBottom - $sqlLimit;
if($previousTop < 1) $previousTop = 1;
if($previousBottom < 1) $previousBottom = 1;
if($previousTop != $previousBottom) echo "<a href='?view=leaderboard&start=$previousTop&end=$previousBottom'>◄ " . getString("ranks") . " " . displayInt($previousTop, false) . " – " . displayInt($previousBottom, false) . "</a><br>";
$nextTop = $tableTop + $sqlLimit;
$nextBottom = $tableBottom + $sqlLimit;
echo "<a href='?view=leaderboard&start=$nextTop&end=$nextBottom'>" . getString("ranks") . " " . displayInt($nextTop, false) . " – " . displayInt($nextBottom, false) . " ►</a><br>";
if(!is_null($myTableTop)){
	echo "<a href='?view=leaderboard&start=$myTableTop&end=$myTableBottom'>" . getString("leaderboard_my_page") . " <small>(" . getString("ranks") . " " . displayInt($myTableTop, false) . " – " . displayInt($myTableBottom, false) . ")</small></a>";
}
echo "</td></tr></table>";