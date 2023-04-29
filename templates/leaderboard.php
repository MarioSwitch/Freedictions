<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}
include_once "libs/maLibSQL.pdo.php";
$classement = parcoursRs(SQLSelect("SELECT nickname, points FROM users ORDER BY points DESC;"));
$classementHeader = "<table class='table'><tr><th>Rang</th><th>Utilisateur</th><th>Points</th></tr>";
$accounts = SQLGetChamp("SELECT COUNT(*) FROM users");
echo "<p class='title'>Classement</p>";
echo "<p class='text2'>Ci-dessous, le classement des utilisateurs ayant le plus de points.</p>";
if(valider("connecte","SESSION")){
	$myPoints = SQLGetChamp("SELECT points FROM users WHERE username='$_SESSION[user]';");
	$myRank = SQLGetChamp("SELECT COUNT(*) FROM users WHERE points > " . $myPoints . ";")+1;
	$myTop = round(($myRank / $accounts)*100,2);
	echo "<p class='text2'>Vous êtes " . $myRank . "<sup>e</sup> sur " . $accounts . " (top " . $myTop . " %)</p>";
}else{
	echo "<p class='text2'>Total : " . $accounts . " utilisateurs</p>";
}
echo "<hr class='line'>";
echo $classementHeader;
foreach($classement as $uneLigne){
	$typeDonnee = 1;
	foreach($uneLigne as $uneDonnee){
		if($typeDonnee == 1){$user = $uneDonnee;}
		if($typeDonnee == 2){$points = $uneDonnee;}
		$typeDonnee++;
	}
	$rank = SQLGetChamp("SELECT COUNT(*) FROM users WHERE points > " . $points . ";")+1;
	echo "<tr><td>" . $rank . "</td><td>" . $user . "</td><td>" . $points . "</td></tr>";
}
echo "</table>";
?>
