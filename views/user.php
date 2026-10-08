<?php
$user = executeQuery("SELECT * FROM `users` WHERE `username` = ?;", [$_REQUEST["user"]], "row");

$username = $user["username"];
$created = $user["created"];
$updated = $user["updated"];
$streak = $user["streak"];

$predictions_created_count = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `user` = ?;", [$username], "int");
$predictions_participated_volume = executeQuery("SELECT COALESCE(COUNT(*), 0) as `count`, COALESCE(SUM(`chips`), 0) as `chips` FROM `bets` WHERE `user` = ?;", [$username], "row");
$predictions_participated_count = $predictions_participated_volume["count"];
$predictions_participated_chips = $predictions_participated_volume["chips"];

$chips = $user["chips"];

$predictions_created_approved = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);
$predictions_created_waiting_approval = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 AND `user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);
$predictions_created = array_merge($predictions_created_approved, $predictions_created_waiting_approval);
$predictions_participated = executeQuery("SELECT `predictions`.* FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `predictions`.`approved` = 1 AND `bets`.`user` = ? AND `answer` IS NULL ORDER BY `ended` ASC;", [$username]);

$predictions_resolved_won = executeQuery("SELECT COUNT(*) FROM `bets` JOIN `predictions` ON `predictions`.`answer` = `bets`.`choice` WHERE `bets`.`user` = ?;", [$username], "int");
$predictions_resolved_total = executeQuery("SELECT COUNT(*) FROM `bets` JOIN `predictions` ON `predictions`.`id` = `bets`.`prediction` WHERE `bets`.`user` = ? AND `predictions`.`answer` IS NOT NULL;", [$username], "int");
$predictions_resolved_ratio = $predictions_resolved_total ? $predictions_resolved_won / $predictions_resolved_total * 100 : PHP_INT_MAX;
$predictions_resolved_profit = executeQuery("SELECT `UserPnL`(?);", [$username], "int");

$manage_password = "<p><button onclick=\"location.href='$username/password'\">" . getString("user_manage_password") . "</button></p>";
$manage_delete = "<p><button onclick=\"location.href='$username/delete'\">" . getString("user_manage_delete") . "</button></p>";
$manage_edit = "<p><button onclick=\"location.href='$username/edit'\">" . getString("user_manage_edit") . "</button></p>";

$manage_html = "";
$manage_html .= isAuthorized(NULL, "user_password", $username) ? $manage_password : "";
$manage_html .= isAuthorized(NULL, "user_delete", $username) ? $manage_delete : "";
$manage_html .= isAuthorized(NULL, "user_edit", $username) ? $manage_edit : "";

$summary_table = "
<table class=\"summary user_summary\">
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
			<small>(" . displayInt($predictions_participated_chips) . insertTextIcon("chips", "right", 1.5) . ")</small>
		</td>
		<td>" .
			displayInt($predictions_resolved_won) . " / " . displayInt($predictions_resolved_total) . " <small>(" . displayFloat($predictions_resolved_ratio, true) . ")</small><br>" .
			displayInt($predictions_resolved_profit, true, true) . insertTextIcon("chips", "right", 1.5) . "
		</td>
	</tr>
	<tr>
		<td>" . getString("user_created_updated_streak") . "</td>
		<td>" . getString("user_predictions") . "</td>
		<td>" . getString("user_resolved_won_total_profit") . "</td>
	</tr>
</table>
";
?>
<h1><?= displayUser($username) ?></h1>
<h2><?= displayInt($chips, false) . insertTextIcon("chips", "right", 1.5) ?></h2>
<?= $summary_table ?>
<br><br>
<h2><?= getString("predictions_created") ?> (<?= getString("user_without_outcome", [displayInt(count($predictions_created))]) ?>)</h2>
<?= displayPaginatedTable($predictions_created, "opened", ["title", "time"]) ?>
<br><br>
<h2><?= getString("predictions_participated") ?> (<?= getString("user_without_outcome", [displayInt(count($predictions_participated))]) ?>)</h2>
<?= displayPaginatedTable($predictions_participated, "opened", ["title", "bet", "time"]) ?>
<br><br>
<?php if($manage_html){
	echo "<h2>" . getString("user_manage") . "</h2>
	$manage_html";
}
?>