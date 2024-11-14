<?php
$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
echo "<h1>" . getString("predictions_opened") . " (" . $count . ")</h1>";
for($i = 0; $i < 100; $i++){
	echo "$i<br>";
}