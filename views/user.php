<?php
$username = $_REQUEST["user"];

$user_exists = count(executeQuery("SELECT * FROM `users` WHERE `username` = ?", [$username]));
if(!$user_exists) redirect("home");

$username_capitalization = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username], "string");
if($username != $username_capitalization) redirect("user/$username_capitalization");

$created = executeQuery("SELECT `created` FROM `users` WHERE `username` = ?", [$username], "string");
$updated = executeQuery("SELECT `updated` FROM `users` WHERE `username` = ?", [$username], "string");
$streak = executeQuery("SELECT `streak` FROM `users` WHERE `username` = ?", [$username], "int");
$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?", [$username], "int");

include_once "time.js.php";

/**
 * Génère le code HTML pour afficher une boîte d'information utilisateur
 * @param string $info Information à afficher (« created », « updated », « streak » ou « chips »)
 * @return string Code HTML
 */
function displayUserBox(string $info): string{
	global $created, $updated, $streak, $chips;
	$value = match($info){
		"created" => $created,
		"updated" => $updated,
		"streak" => displayInt($streak),
		"chips" => displayInt($chips),
	};
	$caption = getString("profile_$info");
	$id = ($info == "created" || $info == "updated") ? "id=\"$info\"" : "";
	$html = "
	<div style=\"display:inline-block; border:1px solid var(--color-text); border-radius: 10px; width:15%; min-width:250px; max-width:400px;\">
		<p style=\"font-size:calc(var(--font-size) * 2.0); margin:calc(var(--font-size) * 0.5);\" $id>$value</p>
		<p style=\"font-size:calc(var(--font-size) * 0.8); margin:calc(var(--font-size) * 0.5);\">$caption</p>
	</div>";
	if($info == "created" || $info == "updated"){
		$html .= "<script>display(\"$value\", \"$info\");</script>";
	}
	return $html;
}

$predictions_created_approved = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);
$predictions_created_waiting_approval = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 AND `user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);
$predictions_created = array_merge($predictions_created_approved, $predictions_created_waiting_approval);
$predictions_participated = executeQuery("SELECT * FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `predictions`.`approved` = 1 AND `bets`.`user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);

/**
 * Génère le code HTML pour afficher une liste de prédictions
 * @param string $type Type de prédictions à afficher (« created » ou « participated »)
 * @param array $predictions Prédictions à afficher
 * @return string Code HTML
 */
function displayPredictionsList(string $type, array $predictions): string{
	global $username;
	$now = executeQuery("SELECT NOW();", [], "string");
	$count = count($predictions);
	$html = "<h2>" . getString("profile_predictions_$type") . " (" . displayInt($count) . ")</h2>";
	if($count == 0){
		$html .= "<p>" . getString("profile_predictions_" . $type . "_none", [$username]) . "</p>";
	}
	if($count > 0){
		$html .= "
		<table class=\"predictions_list\">
			<thead>
				<tr>
					<th>" . getString("predictions_table_question") . "</th>";
					$html .= ($type == "participated") ? "<th>" . getString("predictions_table_bet") . "</th>" : "";
					$html .= "<th>" . getString("predictions_table_end_future") . "</th>
				</tr>
			</thead>
			<tbody>";
		foreach($predictions as $prediction){
			$id = $type == "created" ? $prediction["id"] : $prediction["prediction"];
			$id_countdown = $type . "_" . $id;
			$question = $prediction["title"];
			$ended = $prediction["ended"];
			$ended_td = "<td>" . getString("predictions_table_waiting_answer") . "</td>";
			if($ended > $now) $ended_td = "<td id=\"$id_countdown\">$ended</td><script>display(\"$ended\",\"$id_countdown\")</script>";
			if(!$prediction["approved"]) $ended_td = "<td>" . getString("predictions_table_waiting_approval") . "</td>";
			$bet_td = "";
			if($type == "participated"){
				$bet_choice = $prediction["name"];
				$bet_chips = $prediction["chips"];
				$bet_td = "<td>$bet_choice<br>" . displayInt($bet_chips) . insertTextIcon("chips", "right", 1) . "</td>";
			}
			$html .= "
				<tr>
					<td><a href=\"../prediction/$id\">$question</a></td>";
					$html .= $bet_td;
					$html .= "$ended_td
				</tr>";
		}
		$html .= "
			</tbody>
		</table>";
	}
	return $html;
}
?>
<h1><?= $username ?></h1>
<div>
	<?= displayUserBox("created") ?>
	<?= displayUserBox("updated") ?>
	<?= displayUserBox("streak") ?>
	<?= displayUserBox("chips") ?>
</div>
<br>
<?= displayPredictionsList("created", $predictions_created) ?>
<br><br>
<?= displayPredictionsList("participated", $predictions_participated) ?>