<?php
if(!isMod()) redirect("home", "perms_mod");

include_once "time.js.php";

$waiting_approval = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 ORDER BY `created` ASC;");
$waiting_approval_count = count($waiting_approval);

$waiting_answer = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL ORDER BY `ended` ASC;");
$waiting_answer_count = count($waiting_answer);

/**
 * Génère le code HTML pour afficher les prédictions en attente d'approbation
 * @param array $predictions Liste des prédictions
 * @return string Code HTML
 */
function displayWaitingApproval(array $predictions): string{
	if(!$predictions) return "<p>" . getString("predictions_none") . "</p>";
	$html = "
	<table class=\"predictions_list\">
		<thead>
			<tr>
				<th>" . getString("prediction_question") . "</th>
				<th>" . getString("prediction_outcomes") . "</th>
				<th>" . getString("prediction_proposed") . "</th>
				<th>" . getString("prediction_time_remaining") . "</th>
				<th>" . getString("modqueue_actions") . "</th>
			</tr>
		</thead>
		<tbody>";
		foreach($predictions as $prediction){
			$id = $prediction["id"];
			$title = $prediction["title"];
			$choices_array = executeQuery("SELECT * FROM `choices` WHERE `prediction` = ?;", [$id]);
			$choices = "";
			foreach($choices_array as $choice){
				$choices .= $choice["name"];
				if($choice != end($choices_array)) $choices .= "<br>";
			}
			$user = $prediction["user"];
			$timestamp_proposed = $prediction["created"];
			$proposed = displayUser($user, true) . "<br><span id=\"proposed_$id\">$timestamp_proposed</span><script>display(\"$timestamp_proposed\",\"proposed_$id\")</script>";
			$timestamp_ended = $prediction["ended"];
			$ended = "<span id=\"ended_$id\">" . $timestamp_ended . "</span><script>display(\"$timestamp_ended\",\"ended_$id\")</script>";
			$actions = "
				<button type=\"submit\" name=\"action\" value=\"modqueue_approve\">" . getString("modqueue_actions_approve") . "</button>
				<button type=\"submit\" name=\"action\" value=\"modqueue_reject\">" . getString("modqueue_actions_reject") . "</button>
				<button type=\"submit\" name=\"action\" value=\"modqueue_edit\">" . getString("modqueue_actions_edit") . "</button>";
			$html .= "
			<tr>
				<form role=\"form\" action=\"controller.php\">
					<input type=\"hidden\" name=\"prediction\" value=\"$id\">
					<td><a href=\"prediction/$id\">$title</a></td>
					<td>$choices</td>
					<td>$proposed</td>
					<td>$ended</td>
					<td>$actions</td>
				</form>
			</tr>";
		}
	$html .= "
		</tbody>
	</table>";
	return $html;
}

/**
 * Génère le code HTML pour afficher les prédictions en attente de résultat
 * @param array $predictions Liste des prédictions
 * @return string Code HTML
 */
function displayWaitingAnswer(array $predictions): string{
	if(!$predictions) return "<p>" . getString("predictions_none") . "</p>";
	$html = "
	<table class=\"predictions_list\">
		<thead>
			<tr>
				<th>" . getString("prediction_question") . "</th>
				<th>" . getString("prediction_created") . "</th>
				<th>" . getString("prediction_time_elapsed") . "</th>
			</tr>
		</thead>
		<tbody>";
		foreach($predictions as $prediction){
			$id = $prediction["id"];
			$title = $prediction["title"];
			$user = $prediction["user"];
			$timestamp_created = $prediction["created"];
			$created = displayUser($user, true) . "<br><span id=\"created_$id\">$timestamp_created</span><script>display(\"$timestamp_created\",\"created_$id\")</script>";
			$timestamp_ended = $prediction["ended"];
			$ended = "<span id=\"ended_$id\">" . $timestamp_ended . "</span><script>display(\"$timestamp_ended\",\"ended_$id\")</script>";
			$html .= "
			<tr>
				<form role=\"form\" action=\"controller.php\">
					<input type=\"hidden\" name=\"id\" value=\"$id\">
					<td><a href=\"prediction/$id\">$title</a></td>
					<td>$created</td>
					<td>$ended</td>
				</form>
			</tr>";
		}
	$html .= "
		</tbody>
	</table>";
	return $html;
}
?>
<h1><?= getString("title_modqueue") ?></h1>
<h2><?= getString("predictions_waiting_approval") . " (" . displayInt($waiting_approval_count) . ")" ?></h2>
<?= displayWaitingApproval($waiting_approval) ?>
<br><br>
<h2><?= getString("predictions_waiting_outcome") . " (" . displayInt($waiting_answer_count) . ")" ?></h2>
<?= displayWaitingAnswer($waiting_answer) ?>