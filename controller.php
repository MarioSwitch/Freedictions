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

		$prediction_id = $_REQUEST["id"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");

		executeQuery("UPDATE `predictions` SET `approved` = 1 WHERE `id` = ?;", [$prediction_id]);
		executeQuery("UPDATE `predictions` SET `created` = NOW() WHERE `id` = ?;", [$prediction_id]);
		executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$prediction_creator, "APPROVED:$prediction_id"]);

		redirect("prediction/$prediction_id");

	case "modqueue_reject":
		if(!isMod()) redirect("home", "perms_mod");

		$prediction_id = $_REQUEST["id"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");

		executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$prediction_creator, "REJECTED:$prediction_id"]);

		redirect("controller.php?action=prediction_delete&id=$prediction_id");

	case "prediction_create":
		if(!isConnected()) redirect("home", "perms_connected");

		$question = $_REQUEST["question"];
		$end = $_REQUEST["end"];
		$choices = $_REQUEST["choices"];
		if(empty($question) || empty($end) || empty($choices) || !array_key_exists("details", $_REQUEST) || !array_key_exists("offset", $_REQUEST)) redirect("create", "fields");

		$details = $_REQUEST["details"]; // details peut valoir "" (évalué comme false), utilisation de array_key_exists() au lieu de empty() pour vérifier
		$offset = $_REQUEST["offset"]; // offset peut valoir 0 (évalué comme false), utilisation de array_key_exists() au lieu de empty() pour vérifier

		if(count($choices) < 2) redirect("create", "fields");

		$question = htmlspecialchars($question);
		$details = htmlspecialchars($details);

		$approved = isMod() ? 1 : 0;

		date_default_timezone_set("UTC");
		$endUTC = date("Y-m-d\TH:i", strtotime($end) - $offset*60);

		executeQuery("INSERT INTO `predictions` VALUES (DEFAULT, ?, ?, ?, DEFAULT, ?, ?, DEFAULT, DEFAULT);", [$question, $details, $_COOKIE["username"], $endUTC, $approved]);
		$id = executeQuery("SELECT `id` FROM `predictions` ORDER BY `created` DESC LIMIT 1;", [], "int");
		foreach($choices as $choice){
			$choice = htmlspecialchars($choice);
			executeQuery("INSERT INTO `choices` VALUES (DEFAULT, ?, ?);", [$id, $choice]);
		}

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
		if(empty($prediction_id)) redirect("home", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = ($_COOKIE["username"] == $prediction_creator) || (isMod() && !isMod($prediction_creator));
		if(!$perms) redirect("home", "perms");

		executeQuery("UPDATE `predictions` SET `ended` = NOW() WHERE `id` = ?;", [$prediction_id]);

		redirect("prediction/$prediction_id");

	case "prediction_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("home", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = ($_COOKIE["username"] == $prediction_creator) || (isMod() && !isMod($prediction_creator));
		if(!$perms) redirect("home", "perms");

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
}