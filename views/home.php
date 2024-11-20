<?php
$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
include_once "time.js.php";
echo "<h1>" . getString("predictions_opened") . " (" . $count . ")</h1>";
if($count == 0){
	echo "<p>" . getString("predictions_none") . "</p>";
}else{
	echo "<table class=\"predictions_list\">
		<tr>
			<th>" . getString("prediction") . "</th>
			<th>" . getString("chips_bet") . "</th>
			<th>" . getString("time_left") . "</th>
		</tr>";
	foreach($opened as $prediction){
		$id = $prediction["id"];
		$title = $prediction["title"];
		$ended = $prediction["ended"];
		$volume = executeQuery("SELECT SUM(`points`) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");

		echo "<tr>";
		echo "<td><a href=\"" . CONFIG_PATH . "/prediction/$id\">$title</a></td>";
		echo "<td>" . displayInt($volume) . "</td>";
		echo "<td><abbr id=\"end_$id\">$ended</abbr></td>";
		echo "<script>display(\"" . $prediction["ended"] . "\", \"end_" . $prediction["id"] . "\");</script>";
		echo "</tr>";
	}
	echo "</table>";
}