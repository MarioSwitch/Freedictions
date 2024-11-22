<?php
$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
include_once "time.js.php";
echo "<h1>" . getString("predictions_opened") . " (" . $count . ")</h1>";
if($count == 0){
	echo "<p>" . getString("predictions_none") . "</p>";
}else{
	foreach($opened as $prediction){
		$id = $prediction["id"];
		$title = $prediction["title"];
		$ended = $prediction["ended"];
		$volume = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");

		echo "<a href=\"" . CONFIG_PATH . "/prediction/$id\">";
			echo "<p style=\"font-size:calc(var(--font-size) * 1.5); margin:0;\">$title</p>";
			echo "<div style=\"width:calc(var(--font-size) * 12); display:inline-block;\">";
				echo "<p style=\"margin:0; text-align:left; float:left;\">" . insertTextIcon("chips", "left", 1.2) . displayInt($volume) . "</p>";
				echo "<p style=\"margin:0; text-align:right;\"><span id=\"end_$id\">$ended</span>" . insertTextIcon("sandglass", "right", 1.2) . "</p>";
			echo "</div>";
		echo "<script>display(\"" . $prediction["ended"] . "\", \"end_" . $prediction["id"] . "\");</script>";
		echo "</a>";
		echo "<br>";
		echo "<hr class=\"mini\">";
	}
}