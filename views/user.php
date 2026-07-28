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
	$html = "<h2>" . getString("predictions_$type") . " (" . getString("user_without_outcome", [displayInt($count)]) . ")</h2>";
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
			if($ended > $now) $ended_td = "<td><abbr id=\"$id_countdown\">$ended</abbr></td><script>display(\"$ended\",\"$id_countdown\")</script>";
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

$manage_password = "<p><button onclick=\"location.href='$username/password'\">" . getString("user_manage_password") . "</button></p>";
$manage_delete = "<p><button onclick=\"location.href='$username/delete'\">" . getString("user_manage_delete") . "</button></p>";
$manage_edit = "<p><button onclick=\"location.href='$username/edit'\">" . getString("user_manage_edit") . "</button></p>";

$manage_html = "";
$manage_html .= isAuthorized(NULL, "user_password", $username) ? $manage_password : "";
$manage_html .= isAuthorized(NULL, "user_delete", $username) ? $manage_delete : "";
$manage_html .= isAuthorized(NULL, "user_edit", $username) ? $manage_edit : "";

$summary_table = "
<table class=\"summary\">
	<tr>
		<td>
			<abbr id=\"created\">$created</abbr>
			<script>display(\"$created\",\"created\")</script>
			<br>
			<abbr id=\"updated\">$updated</abbr>
			<script>display(\"$updated\",\"updated\")</script>
			<small>(" . displayInt($streak) . ")</small>
		</td>
		<td>" .
			displayInt($predictions_created_count) . "<br>" .
			displayInt($predictions_participated_count) . "
			<small>(" . displayInt($predictions_participated_volume) . insertTextIcon("chips", "right", 1.5) . ")</small>
		</td>
	</tr>
	<tr>
		<td>" . getString("user_created_updated_streak") . "</td>
		<td>" . getString("user_predictions") . "</td>
	</tr>
</table>
";
?>
<h1><?= displayUser($username) ?></h1>
<h2><?= displayInt($chips, false) . insertTextIcon("chips", "right", 1.5) ?></h2>
<?= $summary_table ?>
<br><br>
<?= displayPredictionsList("created", $predictions_created) ?>
<br><br>
<?= displayPredictionsList("participated", $predictions_participated) ?>
<br><br>
<?php if($manage_html){
	echo "<h2>" . getString("user_manage") . "</h2>
	$manage_html";
}
?>