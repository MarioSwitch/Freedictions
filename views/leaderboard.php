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
$classement = arraySQL("SELECT `username`, `points` FROM `users` ORDER BY `points` DESC LIMIT $sqlLimit OFFSET $sqlOffset;");
$accounts = intSQL("SELECT COUNT(*) FROM `users`");
echo "<h1>Classement</h1>";
echo "<p>Ci-dessous, le classement des utilisateurs ayant le plus de points.</p>";
if(isConnected()){
	$myPoints = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
	$myRank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $myPoints . ";") + 1;
	$myTop = ($myRank / $accounts)*100;
	echo "<p>Vous êtes " . displayOrdinal($myRank) . " sur " . displayInt($accounts) . " (top " . displayFloat($myTop) . " %).</p>";
}else{
	echo "<p>Total : " . displayInt($accounts) . " utilisateurs</p>";
}
echo "<hr>";
echo "<table><tr><th>Rang<br><small>(" . displayInt($tableTop, false) . " - " . displayInt($tableBottom, false) . ")</small></th><th>Utilisateur</th><th>Points</th></tr>";
if(!$classement){
	echo "<tr><td colspan='3'>Aucun utilisateur</td></tr>";
}else{
	for($i = 0; $i < count($classement); $i++){
		$user = $classement[$i]["username"];
		$points = $classement[$i]["points"];
		$rank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $points . ";") + 1;
		echo "<tr><td>" . displayInt($rank, false) . "</td><td><p><a href='?view=profile&user=" . $user . "'>" . displayUsername($user) . "</a></p></td><td>" . displayInt($points) . "</td></tr>";
	}
}
echo "<tr><td colspan='3'>";
$previousTop = $tableTop - $sqlLimit;
$previousBottom = $tableBottom - $sqlLimit;
if($previousTop < 1) $previousTop = 1;
if($previousBottom < 1) $previousBottom = 1;
if($previousTop != $previousBottom) echo "<a href='?view=leaderboard&start=$previousTop&end=$previousBottom'>◄ Rangs " . displayInt($previousTop, false) . " - " . displayInt($previousBottom, false) . "</a><br>";
$nextTop = $tableTop + $sqlLimit;
$nextBottom = $tableBottom + $sqlLimit;
echo "<a href='?view=leaderboard&start=$nextTop&end=$nextBottom'>Rangs " . displayInt($nextTop, false) . " - " . displayInt($nextBottom, false) . " ►</a>";
echo "</td></tr></table>";
?>