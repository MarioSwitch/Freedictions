<?php
$waiting_approval = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 0 ORDER BY `created` ASC;");

$waiting_answer = match(executeQuery("SELECT `mod` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "int")){
	3 => executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL ORDER BY `ended` ASC;"),
	2 => executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL AND `user` IN (SELECT `username` FROM `users` WHERE `mod` <= ?) ORDER BY `ended` ASC;", [2]),
	1 => executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL AND `user` IN (SELECT `username` FROM `users` WHERE `mod` <= ?) ORDER BY `ended` ASC;", [1]),
	default => []
};

echo "<h1>" . getString("title_modqueue") . "</h1>";
echo "<h2>" . getString("predictions_waiting_approval") . "</h2>";
echo displayPaginatedTable($waiting_approval, "opened", ["title", "outcomes", "proposed", "time_modqueue", "actions"]);
echo "<br><br>";
echo "<h2>" . getString("predictions_waiting_outcome") . "</h2>";
echo displayPaginatedTable($waiting_answer, "closed", ["title", "created", "time_modqueue"]);