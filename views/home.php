<?php
/**
 * Génère le code HTML pour afficher une liste de prédictions
 * @param array $predictions Prédictions à afficher
 * @return string Code HTML
 */
function displayPredictionsList(array $predictions): string{
	$already_bet = array_key_exists("name", $predictions[0]) && array_key_exists("chips", $predictions[0]);
	$html = "
	<table class=\"predictions_list\">
		<thead>
			<tr>
				<th>" . getString("prediction_question") . "</th>
				<th>" . getString("prediction_volume") . "</th>";
				$html .= $already_bet ? "<th>" . getString("prediction_bet_noun") . "</th>" : "";
				$html .= "<th>" . getString("prediction_time_remaining") . "</th>
			</tr>
		</thead>
		<tbody>";
		foreach($predictions as $prediction){
			$id = $prediction["id"];
			$title = $prediction["title"];
			$ended = $prediction["ended"];
			$users = executeQuery("SELECT COUNT(*) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");
			$volume = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");
			$bet_name = $already_bet ? $prediction["name"] : "";
			$bet_chips = $already_bet ? $prediction["chips"] : "";

			$html .= "
			<tr>
				<td><a href=\"prediction/$id\">$title</a></td>
				<td>" . displayInt($volume) . insertTextIcon("chips", "right", 1) . "<br>" . displayInt($users) . insertTextIcon("users", "right", 1) . "</td>";
				$html .= $already_bet ? "<td>" . displayInt($bet_chips) . insertTextIcon("chips", "right", 1) . "<br>$bet_name</td>" : "";
				$html .= "<td id=\"ended_$id\">$ended</td>
				<script>display(\"$ended\",\"ended_$id\")</script>
			</tr>
			";
		}
		$html .= "
		</tbody>
	</table>";
	return $html;
}

$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `approved` = 1 AND `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
include_once "time.js.php";
echo "<h1>" . getString("predictions_opened") . " (" . displayInt($count) . ")</h1>";
if($count == 0) echo "<p>" . getString("predictions_none") . "</p>";
if($count > 0){
	if(!isConnected()) echo displayPredictionsList($opened);
	if(isConnected()){
		$not_bet = executeQuery("SELECT `predictions`.`id`, `predictions`.`title`, `predictions`.`ended` FROM `predictions` WHERE `predictions`.`approved` = 1 AND `predictions`.`ended` > NOW() AND `predictions`.`id` NOT IN (SELECT `predictions`.`id` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `bets`.`user` = ? AND `answer` IS NULL) ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
		$not_bet_count = count($not_bet);
		echo "<h2>" . getString("predictions_opened_bet_none") . " (" . displayInt($not_bet_count) . ")</h2>";
		if($not_bet_count == 0) echo "<p>" . getString("predictions_none") . "</p>";
		if($not_bet_count > 0) echo displayPredictionsList($not_bet);

		echo "<br><br>";

		$already_bet = executeQuery("SELECT `predictions`.`id`, `predictions`.`title`, `predictions`.`ended`, `choices`.`name`, `bets`.`chips` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `predictions`.`approved` = 1 AND `bets`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
		$already_bet_count = count($already_bet);
		echo "<h2>" . getString("predictions_opened_bet_already") . " (" . displayInt($already_bet_count) . ")</h2>";
		if($already_bet_count == 0) echo "<p>" . getString("predictions_none") . "</p>";
		if($already_bet_count > 0) echo displayPredictionsList($already_bet);
	}
}