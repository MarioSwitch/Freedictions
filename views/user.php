<?php
$username = $_REQUEST["user"];

$user_exists = count(executeQuery("SELECT * FROM `users` WHERE `username` = ?", [$username]));
if(!$user_exists) redirect("home");

$username_capitalization = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username], "string");
if($username != $username_capitalization) redirect("user/$username_capitalization");

$created = executeQuery("SELECT `created` FROM `users` WHERE `username` = ?", [$username], "string");
$updated = executeQuery("SELECT `updated` FROM `users` WHERE `username` = ?", [$username], "string");
$streak = executeQuery("SELECT `streak` FROM `users` WHERE `username` = ?", [$username], "int");

$predictions_created_count = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `user` = ?;", [$username], "int");
$predictions_participated_count = executeQuery("SELECT COUNT(*) FROM `bets` WHERE `user` = ?;", [$username], "int");
$predictions_participated_volume = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `user` = ?;", [$username], "int");

$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?", [$username], "int");

include_once "time.js.php";

/**
 * Génère le code HTML pour afficher une boîte d'information utilisateur
 * @param string $info Information à afficher (« created », « updated », « streak » ou « chips »)
 * @return string Code HTML
 */
function displayUserBox(string $info): string{
	global $created, $updated, $streak, $predictions_created_count, $predictions_participated_count, $predictions_participated_volume, $chips;
	$value = match($info){
		"created_updated_streak" => 
			"<span id=\"created\">$created</span>
			<script>display(\"$created\",\"created\")</script>
			<br>
			<span id=\"updated\">$updated</span>
			<script>display(\"$updated\",\"updated\")</script>
			<small>(" . displayInt($streak) . ")</small>",
		"predictions" =>
			displayInt($predictions_created_count) . "<br>" .
			displayInt($predictions_participated_count) . "
			<small>(" . displayInt($predictions_participated_volume) . insertTextIcon("chips", "right", 1.5) . ")</small>",
	};
	$caption = getString("user_$info");
	$html = "
	<div style=\"display:inline-block; border:1px solid var(--color-text); border-radius: 10px; width:15%; min-width:250px; max-width:400px;\">
		<p style=\"font-size:calc(var(--font-size) * 1.5); margin:calc(var(--font-size) * 0.5);\">$value</p>
		<p style=\"font-size:calc(var(--font-size) * 0.8); margin:calc(var(--font-size) * 0.5);\">$caption</p>
	</div>";
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
	$html = "<h2>" . getString("predictions_$type") . " (" . displayInt($count) . ")</h2>";
	if($count == 0){
		$html .= "<p>" . getString("predictions_none", [$username]) . "</p>";
	}
	if($count > 0){
		$html .= "
		<table class=\"predictions_list\">
			<thead>
				<tr>
					<th>" . getString("prediction_question") . "</th>";
					$html .= ($type == "participated") ? "<th>" . getString("prediction_bet_noun") . "</th>" : "";
					$html .= "<th>" . getString("general_time_remaining") . "</th>
				</tr>
			</thead>
			<tbody>";
		foreach($predictions as $prediction){
			$id = $type == "created" ? $prediction["id"] : $prediction["prediction"];
			$id_countdown = $type . "_" . $id;
			$question = $prediction["title"];
			$ended = $prediction["ended"];
			$ended_td = "<td>" . getString("prediction_waiting_outcome") . "</td>";
			if($ended > $now) $ended_td = "<td id=\"$id_countdown\">$ended</td><script>display(\"$ended\",\"$id_countdown\")</script>";
			if(!$prediction["approved"]) $ended_td = "<td>" . getString("prediction_waiting_approval") . "</td>";
			$bet_td = "";
			if($type == "participated"){
				$bet_choice = $prediction["name"];
				$bet_chips = $prediction["chips"];
				$bet_td = "<td>" . displayInt($bet_chips) . insertTextIcon("chips", "right", 1) . "<br>$bet_choice</td>";
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
<h1><?= displayUser($username) ?></h1>
<h2><?= displayInt($chips) . insertTextIcon("chips", "right", 1.5) ?></h2>
<div>
	<?= displayUserBox("created_updated_streak") ?>
	<?= displayUserBox("predictions") ?>
</div>
<br>
<?= displayPredictionsList("created", $predictions_created) ?>
<br><br>
<?= displayPredictionsList("participated", $predictions_participated) ?>
<?php if(isMod() || $username == $_COOKIE["username"]){
	echo "<br><br>
	<h2>" . getString("user_manage") . "</h2>
	<p><button onclick=\"location.href='$username/password'\">" . getString("title_password") . "</button></p>";
}
?>