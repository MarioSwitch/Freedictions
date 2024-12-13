<?php
include_once "functions.php";

define("NOW", executeQuery("SELECT NOW();", [], "string")); // Utilisation de define(), car « const NOW = … » nécessite une valeur brute (pas de fonction, ni de variable)

switch($_REQUEST["action"]){
	case "signup":
		$username = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		$password_confirm = $_REQUEST["password_confirm"];
		if(empty($username) || empty($password) || empty($password_confirm) || !preg_match("/^[A-Za-z0-9]{4,20}$/", $username)) redirect("signup", "fields");
		if($password != $password_confirm) redirect("signup", "password_confirm");

		$user_exists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$username], "int");
		if($user_exists) redirect("signup", "username_taken");

		$hash = password_hash($password, PASSWORD_DEFAULT);
		executeQuery("INSERT INTO users (`username`, `password`, `created`, `updated`, `streak`, `chips`, `mod`, `extra`) VALUES (?, ?, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT);", [$username, $hash]);
		redirect("controller.php?action=signin&username=$username&password=$password");

	case "signin":
		$username = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		if(empty($username) || empty($password)) redirect("signin", "fields");

		$user_exists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$username], "int");
		if(!$user_exists) redirect("signin", "username_unknown");

		$hash_saved = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username], "string");
		if(!password_verify($password, $hash_saved)) redirect("signin", "password");

		$username_capitalization = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?;", [$username], "string");

		setcookie("username", $username_capitalization, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("password", $password, time() + CONFIG_COOKIES_EXPIRATION);
		redirect("home");

	case "signout":
		setcookie("username", "", time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("password", "", time() + CONFIG_COOKIES_EXPIRATION);
		redirect("home");

	case "modqueue_approve":
		if(!isMod()) redirect("home", "perms_mod");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");

		executeQuery("UPDATE `predictions` SET `approved` = 1 WHERE `id` = ?;", [$prediction_id]);
		executeQuery("UPDATE `predictions` SET `created` = NOW() WHERE `id` = ?;", [$prediction_id]);
		executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$prediction_creator, "APPROVED:$prediction_id"]);

		redirect("prediction/$prediction_id");

	case "modqueue_reject":
		if(!isMod()) redirect("home", "perms_mod");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");

		executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$prediction_creator, "REJECTED:$prediction_id"]);

		redirect("controller.php?action=prediction_delete&prediction=$prediction_id");

	case "prediction_create":
		if(!isConnected()) redirect("home", "perms_connected");

		$question = trim(htmlspecialchars($_REQUEST["question"]));
		$end = $_REQUEST["end"];
		$choices = $_REQUEST["choices"];
		foreach($choices as $choice) $choice = trim(htmlspecialchars($choice));
		if(empty($question) || empty($end) || empty($choices) || !array_key_exists("details", $_REQUEST) || !array_key_exists("offset", $_REQUEST)) redirect("create", "fields");

		$details = trim(htmlspecialchars($_REQUEST["details"])); // details peut valoir "" (évalué comme false), utilisation de array_key_exists() au lieu de empty() pour vérifier
		$offset = $_REQUEST["offset"]; // offset peut valoir 0 (évalué comme false), utilisation de array_key_exists() au lieu de empty() pour vérifier

		if(count($choices) < 2) redirect("create", "fields");

		$approved = isMod() ? 1 : 0;

		date_default_timezone_set("UTC");
		$endUTC = date("Y-m-d\TH:i", strtotime($end) - $offset*60);

		executeQuery("INSERT INTO `predictions` VALUES (DEFAULT, ?, ?, ?, DEFAULT, ?, ?, DEFAULT, DEFAULT);", [$question, $details, $_COOKIE["username"], $endUTC, $approved]);
		$id = executeQuery("SELECT `id` FROM `predictions` ORDER BY `created` DESC LIMIT 1;", [], "int");
		foreach($choices as $choice) executeQuery("INSERT INTO `choices` VALUES (DEFAULT, ?, ?);", [$id, $choice]);

		redirect("prediction/$id");

	case "prediction_bet":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		$choice_id = $_REQUEST["choice"];
		$chips = $_REQUEST["chips"];
		if(empty($prediction_id) || empty($choice_id) || empty($chips)) redirect("prediction/$prediction_id", "fields");

		$chips = intval($chips);
		$chips_total = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "int");
		if($chips > $chips_total || $chips < 1) redirect("prediction/$prediction_id", "fields");

		$choice_prediction = executeQuery("SELECT `prediction` FROM `choices` WHERE `id` = ?;", [$choice_id], "int");
		if($choice_prediction != $prediction_id) redirect("prediction/$prediction_id", "fields");

		$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$approved) redirect("prediction/$prediction_id", "prediction_not_approved");

		$prediction_ended = executeQuery("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		if(NOW >= $prediction_ended) redirect("prediction/$prediction_id", "prediction_closed");

		$already_bet = executeQuery("SELECT COUNT(*) FROM `bets` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $prediction_id], "int");
		if($already_bet){
			$already_bet_choice_id = executeQuery("SELECT `choice` FROM `bets` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $prediction_id], "int");
			executeQuery("UPDATE `bets` SET `chips` = `chips` + ? WHERE `user` = ? AND `prediction` = ?;", [$chips, $_COOKIE["username"], $prediction_id]);
		}else{
			executeQuery("INSERT INTO `bets` VALUES (?, ?, ?, ?);", [$_COOKIE["username"], $prediction_id, $choice_id, $chips]);
		}
		executeQuery("UPDATE `users` SET `chips` = `chips` - ? WHERE `username` = ?;", [$chips, $_COOKIE["username"]]);

		redirect("prediction/$prediction_id");

	case "prediction_close":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("prediction/$prediction_id", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = ($_COOKIE["username"] == $prediction_creator) || (isMod() && !isMod($prediction_creator));
		if(!$perms) redirect("prediction/$prediction_id", "perms");

		$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$approved) redirect("prediction/$prediction_id", "prediction_not_approved");

		executeQuery("UPDATE `predictions` SET `ended` = NOW() WHERE `id` = ?;", [$prediction_id]);

		redirect("prediction/$prediction_id");

	case "prediction_resolve":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		$choice_id = $_REQUEST["choice"];
		if(empty($prediction_id) || empty($choice_id)) redirect("prediction/$prediction_id", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = ($_COOKIE["username"] == $prediction_creator) || (isMod() && !isMod($prediction_creator));
		if(!$perms) redirect("prediction/$prediction_id", "perms");

		$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$approved) redirect("prediction/$prediction_id", "prediction_not_approved");

		$resolved = executeQuery("SELECT `answer` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if($resolved) redirect("prediction/$prediction_id", "prediction_resolved");

		$choice_prediction = executeQuery("SELECT `prediction` FROM `choices` WHERE `id` = ?;", [$choice_id], "int");
		if($choice_prediction != $prediction_id) redirect("prediction/$prediction_id", "fields");

		$prediction_ended = executeQuery("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		if(NOW < $prediction_ended) redirect("prediction/$prediction_id", "prediction_opened");

		executeQuery("UPDATE `predictions` SET `answer` = ? WHERE `id` = ?;", [$choice_id, $prediction_id]);
		executeQuery("UPDATE `predictions` SET `answered` = NOW() WHERE `id` = ?;", [$prediction_id]);

		$chips_total = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `prediction` = ?;", [$prediction_id], "int");
		$chips_win = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int");
		if($chips_win){
			$ratio = $chips_total / $chips_win;
			$winners = executeQuery("SELECT `user`, `chips` FROM `bets` WHERE `prediction` = ? AND `choice` = ?;", [$prediction_id, $choice_id]);
			foreach($winners as $winner){
				$chips_won = floor($winner["chips"] * $ratio);
				executeQuery("UPDATE `users` SET `chips` = `chips` + ? WHERE `username` = ?;", [$chips_won, $winner["user"]]);
				executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$winner["user"], "RESOLVED:$prediction_id,ANSWER:$choice_id,WON:$chips_won"]);
			}
		}

		$losers = executeQuery("SELECT `user`, `chips`, `choice` FROM `bets` WHERE `prediction` = ? AND `choice` != ?;", [$prediction_id, $choice_id]);
		foreach($losers as $loser){
			$wrong_choice_id = $loser["choice"];
			$chips_lost = $loser["chips"];
			executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$loser["user"], "RESOLVED:$prediction_id,ANSWER:$choice_id,YOUR_ANSWER:$wrong_choice_id,LOST:$chips_lost"]);
		}

		redirect("prediction/$prediction_id");

	case "prediction_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("prediction/$prediction_id", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = ($_COOKIE["username"] == $prediction_creator) || (isMod() && !isMod($prediction_creator));
		if(!$perms) redirect("prediction/$prediction_id", "perms");

		$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$approved && !isMod()) redirect("prediction/$prediction_id", "prediction_not_approved");

		$resolved = executeQuery("SELECT `answer` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$resolved){
			$bets = executeQuery("SELECT * FROM `bets` WHERE `prediction` = ?;", [$prediction_id]);
			foreach($bets as $bet){
				executeQuery("UPDATE `users` SET `chips` = `chips` + ? WHERE `username` = ?;", [$bet["chips"], $bet["user"]]);
				executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$bet["user"], "DELETED_REFUNDED:" . $bet["chips"]]);
			}
		}else{
			executeQuery("UPDATE `predictions` SET `answer` = NULL WHERE `id` = ?;", [$prediction_id]);
		}
		executeQuery("DELETE FROM `bets` WHERE `prediction` = ?;", [$prediction_id]);
		executeQuery("DELETE FROM `choices` WHERE `prediction` = ?;", [$prediction_id]);
		executeQuery("DELETE FROM `predictions` WHERE `id` = ?;", [$prediction_id]);

		redirect("home");

	case "settings":
		$language = $_REQUEST["language"];
		$shorten_large_numbers = $_REQUEST["shorten_large_numbers"];
		if(empty($language) || empty($shorten_large_numbers)) redirect("settings", "fields");

		setcookie("language", $language, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("shorten_large_numbers", $shorten_large_numbers, time() + CONFIG_COOKIES_EXPIRATION);

		redirect("settings");
}