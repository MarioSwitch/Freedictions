<?php
$leaderboard = executeQuery("SELECT * FROM `users` ORDER BY `chips` DESC;");
if(isConnected()){
	$count = count($leaderboard);
	$my_username = $_COOKIE["username"];
	$my_chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$my_username], "int");
	$my_rank = executeQuery("SELECT COUNT(*) FROM `users` WHERE `chips` > ?;", [$my_chips], "int") + 1;
	$my_top = ($my_rank / $count) * 100;
	$my_string = getString("leaderboard_desc", [displayRank($my_rank), displayInt($count), displayFloat($my_top, true)]);
}
echo "<h1>" . getString("title_leaderboard") . "</h1>";
echo "<p>" . (isConnected() ? $my_string : "") . "</p>";
echo displayPaginatedTable($leaderboard, "users");