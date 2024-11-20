<?php
$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
include_once "time.js.php";
echo "<h1>" . getString("predictions_opened") . " (" . $count . ")</h1>";
if($count == 0){
	echo "<p>" . getString("predictions_none") . "</p>";
}else{
	foreach($opened as $prediction){
		echo "<a href=\"" . CONFIG_PATH . "/prediction/" . $prediction["id"] . "\">" . $prediction["title"] . "</a><br>";
		$volume = executeQuery("SELECT SUM(`points`) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");
		echo "<abbr title=\"" . getString("chips_bet") . "\"><img style=\"width: 20; height: 20; vertical-align: bottom;\" src=\"svg/chips.svg\" alt=\"" . getString("chips_bet") . "\"></abbr>";
		echo "<small>" . displayInt($volume) . "</small>";
		echo "<abbr title=\"" . getString("time_left") . "\"><img style=\"width: 20; height: 20; margin-left: 15px; vertical-align: bottom;\" src=\"svg/clock.svg\" alt=\"" . getString("time_left") . "\"></abbr>";
		echo "<small id=\"end_" . $prediction["id"] . "\">" . $prediction["ended"] . "</small>";
		echo "<br><br>";
		echo "<script>display(\"" . $prediction["ended"] . "\", \"end_" . $prediction["id"] . "\");</script>";
	}
}

