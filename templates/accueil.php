<?php
// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
// Pas de soucis de bufferisation, puisque c'est dans le cas où on appelle directement la page sans son contexte
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}
include_once("templates/search.php");
include_once("lis/maLibSQL.pdo.php");
?>
<h1 class='title'><del>Better Twitch Predictions</del><br>[insérer nom du site]</h1>
<h2 class='category-h2'>Le principe du site</h2>
<p class="text">Ce site web permet de miser des points virtuels sur des prédictions posées par les utilisateurs.</p>
<p class="text2">Tous les utilisateurs peuvent créer des prédictions, du moment qu'ils sont connectés.</p>
<p class="text2">Les prédictions possèdent une date limite de mise (à partir de laquelle les paris sont bloqués) et un nombre variable de réponses.</p>
<p class="text2">Les prédictions peuvent posséder autant de choix que le créateur le souhaite. Cependant, 2 choix sont nécessaires pour créer une prédiction.</p>
<p class="text2">Une fois la date limite dépassée, le créateur peut alors valider la bonne réponse. Une fois fait, les utilisateurs ayant misés sur cette réponse se partagent tous les points (suivant leur mise initiale).</p>
<hr class="line">
<h2 class="category-h2">Prédictions ouvertes</h2>
<?php
$now = SQLGetChamp("SELECT NOW();");
$predictions = parcoursRs(SQLSelect("SELECT id, title FROM predictions WHERE endDate > '$now' ORDER BY endDate ASC;"));
if(!$predictions){
	echo "<p class='text2'>Aucune prédiction ouverte</p>";
}else{
	foreach($predictions as $uneLigne){
		$typeDonnee = 1;
		foreach($uneLigne as $uneDonnee){
			if($typeDonnee == 1){$id = $uneDonnee;}
			if($typeDonnee == 2){$title = $uneDonnee;}
			$typeDonnee++;
		}
		echo "<p class='text2'><a class='a-text' href='?view=prediction&id=" . $id . "'>" . $title . "</a></p>";
	}
}
?>
