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
				<th>" . getString("predictions_table_question") . "</th>
				<th>" . getString("predictions_table_choices") . "</th>
				<th>" . getString("predictions_table_proposed") . "</th>
				<th>" . getString("predictions_table_end_future") . "</th>
				<th>" . getString("predictions_table_actions") . "</th>
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
			$proposed = $user . "<br><span id=\"proposed_$id\">" . $timestamp_proposed . "</span><script>display(\"$timestamp_proposed\",\"proposed_$id\")</script>";
			$timestamp_ended = $prediction["ended"];
			$ended = "<span id=\"ended_$id\">" . $timestamp_ended . "</span><script>display(\"$timestamp_ended\",\"ended_$id\")</script>";
			$actions = "
				<button type=\"submit\" name=\"action\" value=\"modqueue_approve\">" . getString("predictions_table_actions_approve") . "</button>
				<button type=\"submit\" name=\"action\" value=\"modqueue_reject\">" . getString("predictions_table_actions_reject") . "</button>
				<button disabled=\"disabled\">" . getString("predictions_table_actions_edit") . "</button>";
			$html .= "
			<tr>
				<form role=\"form\" action=\"controller.php\">
					<input type=\"hidden\" name=\"id\" value=\"$id\">
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
				<th>" . getString("predictions_table_question") . "</th>
				<th>" . getString("predictions_table_created") . "</th>
				<th>" . getString("predictions_table_end_past") . "</th>
				<th>" . getString("predictions_table_result") . "</th>
			</tr>
		</thead>
		<tbody>";
		foreach($predictions as $prediction){
			$id = $prediction["id"];
			$title = $prediction["title"];
			$choices_array = executeQuery("SELECT * FROM `choices` WHERE `prediction` = ?;", [$id]);
			$result = "
			<select name=\"result\" required=\"required\">
				<option selected=\"selected\" disabled=\"disabled\" value=\"\">" . getString("prediction_result_select") . "</option>";
				foreach($choices_array as $choice){
					$choice_id = $choice["id"];
					$choice_name = $choice["name"];
					$result .= "<option value=\"$choice_id\">$choice_name</option>";
				}
			$result .= "
			</select>
			<button type=\"submit\" name=\"action\" value=\"prediction_resolve\">" . getString("prediction_resolve") . "</button>";
			$user = $prediction["user"];
			$timestamp_created = $prediction["created"];
			$created = $user . "<br><span id=\"created_$id\">" . $timestamp_created . "</span><script>display(\"$timestamp_created\",\"created_$id\")</script>";
			$timestamp_ended = $prediction["ended"];
			$ended = "<span id=\"ended_$id\">" . $timestamp_ended . "</span><script>display(\"$timestamp_ended\",\"ended_$id\")</script>";
			$html .= "
			<tr>
				<form role=\"form\" action=\"controller.php\">
					<input type=\"hidden\" name=\"id\" value=\"$id\">
					<td><a href=\"prediction/$id\">$title</a></td>
					<td>$created</td>
					<td>$ended</td>
					<td>$result</td>
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
<h2><?= getString("predictions_waiting_answer") . " (" . displayInt($waiting_answer_count) . ")" ?></h2>
<?= displayWaitingAnswer($waiting_answer) ?>