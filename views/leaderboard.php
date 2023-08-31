<?php
$classement = arraySQL("SELECT `username`, `points` FROM `users` ORDER BY `points` DESC;");
$accounts = intSQL("SELECT COUNT(*) FROM `users`");
echo "<h1>Classement</h1>";
echo "<p>Ci-dessous, le classement des utilisateurs ayant le plus de points.</p>";
if(userConnected()){
	$myPoints = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]);
	$myRank = intSQL("SELECT COUNT(*) FROM `users` WHERE `points` > " . $myPoints . ";") + 1;
	$myTop = ($myRank / $accounts)*100;
	echo "<p>Vous êtes " . displayInt($myRank, false) . "<sup>e</sup> sur " . displayInt($accounts) . " (top " . displayFloat($myTop) . " %)</p>";
}else{
	echo "<p>Total : " . displayInt($accounts) . " utilisateurs</p>";
}
echo "<hr>";
echo "<table><tr><th>Rang</th><th>Utilisateur</th><th>Points</th></tr>";
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
echo "</table>";
?>