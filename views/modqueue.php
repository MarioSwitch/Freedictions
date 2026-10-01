<?php
$waiting_approval = isAuthorized(NULL, "modqueue_access_full", NULL) ?
	executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 ORDER BY `created` ASC;") :
	executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 AND `user` IN (SELECT `username` FROM `users` WHERE `mod` = ?) ORDER BY `created` ASC;", [0]);

$waiting_answer = isAuthorized(NULL, "modqueue_access_full", NULL) ?
	executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL ORDER BY `ended` ASC;") :
	executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL AND `user` IN (SELECT `username` FROM `users` WHERE `mod` = ?) ORDER BY `ended` ASC;", [0]);

echo "<h1>" . getString("title_modqueue") . "</h1>";
echo "<h2>" . getString("predictions_waiting_approval") . "</h2>";
echo displayPaginatedTable($waiting_approval, "opened", ["title", "outcomes", "proposed", "time", "actions"]);
echo "<br><br>";
echo "<h2>" . getString("predictions_waiting_outcome") . "</h2>";
echo displayPaginatedTable($waiting_answer, "opened", ["title", "created", "time"]); // Using "opened" instead of "closed" to avoid styling time column