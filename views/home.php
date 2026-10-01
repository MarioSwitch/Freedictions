<?php
$opened = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `ended` > NOW() ORDER BY `ended` ASC;");
echo "<h1>" . getString("predictions_opened") . "</h1>";
if(!isConnected()) echo displayPaginatedTable($opened, "opened", ["title", "volume", "time"]);
if(isConnected()){
	$not_bet = executeQuery("SELECT `predictions`.* FROM `predictions` WHERE `predictions`.`approved` = 1 AND `predictions`.`ended` > NOW() AND `predictions`.`id` NOT IN (SELECT `predictions`.`id` FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `bets`.`user` = ? AND `answer` IS NULL) ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
	echo "<h2>" . getString("predictions_opened_bet_none") . "</h2>";
	echo displayPaginatedTable($not_bet, "opened", ["title", "volume", "time"]);

	echo "<br><br>";

	$already_bet = executeQuery("SELECT `predictions`.* FROM `predictions` JOIN `choices` ON `choices`.`prediction` = `predictions`.`id` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `predictions`.`approved` = 1 AND `bets`.`user` = ? AND NOW() < `ended` AND `answer` IS NULL ORDER BY `predictions`.`ended` ASC;", [$_COOKIE["username"]]);
	echo "<h2>" . getString("predictions_opened_bet_already") . "</h2>";
	echo displayPaginatedTable($already_bet, "opened", ["title", "volume", "bet", "time"]);
}