<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}
include_once "libs/maLibSQL.pdo.php";
echo "<p class=\"title\">Classement</p>";
echo "<p class=\"text2\">Ci-dessous le classement des utilisateurs ayant le plus de points.</p>";
echo "<hr class=\"line\">";
$classement = parcoursRs(SQLSelect("SELECT nickname, points FROM users ORDER BY points DESC;"));
$classementHeader = "<table class='table'><tr><th>Rang</th><th>Utilisateur</th><th>Points</th></tr>";
$rank = 1;
echo $classementHeader;
foreach($classement as $uneLigne){
	echo "<tr><td>" . $rank . "</td>";
	foreach($uneLigne as $uneDonnee){
		echo "<td>" . $uneDonnee . "</td>";
	}
	$rank++;
	echo "</tr>";
}
?>
