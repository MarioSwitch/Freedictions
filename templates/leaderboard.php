<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}
include_once "libs/maLibSQL.pdo.php";
echo "<p class=\"title\">Classement</p>";
echo "<p class=\"text2\">Ci-dessous, le classement des utilisateurs ayant le plus de points.</p>";
echo "<hr class=\"line\">";
$classement = parcoursRs(SQLSelect("SELECT nickname, points FROM users ORDER BY points DESC;"));
$classementHeader = "<table class='table'><tr><th>Rang</th><th>Utilisateur</th><th>Points</th></tr>";
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
