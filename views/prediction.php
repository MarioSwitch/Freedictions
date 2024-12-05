<?php
include_once "time.js.php";

$id = $_REQUEST["id"];
$question = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$created_user = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$created_time = executeQuery("SELECT `created` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$ended = executeQuery("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$id], "string");

$details = executeQuery("SELECT `description` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$details_text = $details ? "<h2>" . getString("prediction_details") . "</h2><p>$details</p><br>" : "";

$choices = executeQuery("SELECT * FROM `choices` WHERE `prediction` = ?;", [$id]);
$choices_count = count($choices);
$choices_contains_numbers = false;
foreach($choices as $choice){
	if(preg_match("/[0-9]/", $choice["name"])) $choices_contains_numbers = true;
}
$volume_chips = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `prediction` = ?;", [$id], "int");
$volume_users = executeQuery("SELECT COUNT(`user`) FROM `bets` WHERE `prediction` = ?;", [$id], "int");
$choices_bets = [];
foreach($choices as $choice){
	$choice_id = $choice["id"];
	// Format : [ID du choix => [nombre de jetons misés]]
	$choices_bets[$choice_id] = array(
		"chips" => executeQuery("SELECT COUNT(*) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int"),
		"users" => executeQuery("SELECT COUNT(`user`) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int")
	);
	$choices_bets[$choice_id]["percentage"] = $volume_chips ? $choices_bets[$choice_id]["chips"] / $volume_chips * 100 : 0;
	$choices_bets[$choice_id]["ratio"] = $choices_bets[$choice_id]["chips"] ? $volume_chips / $choices_bets[$choice_id]["chips"] : 0;
}
usort($choices, function($a, $b){
	global $choices_contains_numbers, $choices_bets;
	if($choices_contains_numbers || $choices_bets[$a["id"]]["chips"] == $choices_bets[$b["id"]]["chips"]) return $a["id"] - $b["id"]; // Si les choix contiennent des chiffres (ou si le nombre de jetons misés est égal), on trie par ID
	return $choices_bets[$b["id"]]["chips"] - $choices_bets[$a["id"]]["chips"]; // Sinon, on trie par nombre de jetons misés décroissant
});
$choices_table = "
<table class=\"choices_list\">
	<thead>
		<tr>
			<th>" . getString("prediction_choice") . "</th>
			<th>" . getString("prediction_percentage") . "</th>
			<th>" . getString("prediction_participation") . "</th>
			<th>" . getString("prediction_ratio") . "</th>
			<th>" . getString("prediction_top") . "</th>
		</tr>
	</thead>
	<tbody>";
	foreach($choices as $choice){
		$id = $choice["id"];
		$name = $choice["name"];
		$chips = $choices_bets[$id]["chips"];
		$percentage = $choices_bets[$id]["percentage"];
		$users = $choices_bets[$id]["users"];
		$ratio = $choices_bets[$id]["ratio"];
		$choices_table .= "
		<tr>
			<td>$name</td>
			<td>" . displayFloat($percentage, true) . "</td>
			<td>
				" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "<br>
				" . displayInt($users) . insertTextIcon("users", "right", 1) . "
			</td>
			<td>" . ($ratio ? displayRatio($ratio) : "–") . "</td>
			<td></td>
		</tr>";
	}
$choices_table .= "
	</tbody>
</table>";

/**
 * Génère le code HTML pour afficher une boîte d'information prédiction
 * @param string $info Information à afficher (« created_time », « created_user », « ended » ou « participation »)
 * @return string Code HTML
 */
function displayPredictionBox(string $info): string{
	global $created_user, $created_time, $ended, $volume_chips, $volume_users;
	$value = match($info){
		"created_time" => $created_time,
		"created_user" => "<a href=\"../user/$created_user\">$created_user</a>",
		"ended" => $ended,
		"participation" => displayInt($volume_chips) . insertTextIcon("chips", "right", 2) . ", " . displayInt($volume_users) . insertTextIcon("users", "right", 2),
	};
	$caption = getString("prediction_$info");
	$id = ($info == "created_time" || $info == "ended") ? "id=\"$info\"" : "";
	$html = "
	<div style=\"display:inline-block; border:1px solid var(--color-text); border-radius: 10px; width:15%; min-width:250px; max-width:400px;\">
		<p style=\"font-size:calc(var(--font-size) * 2.0); margin:calc(var(--font-size) * 0.5);\" $id>$value</p>
		<p style=\"font-size:calc(var(--font-size) * 0.8); margin:calc(var(--font-size) * 0.5);\">$caption</p>
	</div>";
	if($info == "created_time" || $info == "ended"){
		$html .= "<script>display(\"$value\", \"$info\");</script>";
	}
	return $html;
}
?>
<h1><?= $question ?></h1>
<?= displayPredictionBox("created_time") ?>
<?= displayPredictionBox("created_user") ?>
<?= displayPredictionBox("ended") ?>
<?= displayPredictionBox("participation") ?>
<?= $details_text ?>
<h2><?= getString("prediction_choices") . " ($choices_count)" ?></h2>
<?= $choices_table ?>