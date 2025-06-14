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

	case "user_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$username_connected = $_COOKIE["username"];

		$username_concerned = $_REQUEST["user"];
		$password = $_REQUEST["password"];
		if(empty($username_concerned) || empty($password)) redirect("user/$username_concerned/delete", "fields");

		$perms = isMod() || $username_connected == $username_concerned;
		if(!$perms) redirect("user/$username_concerned/delete", "perms");

		$password_hash = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username_connected], "string");
		if(!password_verify($password, $password_hash)) redirect("user/$username_concerned/delete", "password");

		executeQuery("DELETE FROM `notifications` WHERE `user` = ?;", [$username_concerned]); // Supprimer les notifications de l'utilisateur
		executeQuery("DELETE FROM `bets` WHERE `user` = ?;", [$username_concerned]); // Supprimer les paris de l'utilisateur
		executeQuery("DELETE FROM `bets` WHERE `prediction` IN (SELECT `id` FROM `predictions` WHERE `user` = ?);", [$username_concerned]); // Supprimer les paris sur les prédictions de l'utilisateur
		executeQuery("UPDATE `predictions` SET `answer` = NULL WHERE `user` = ?;", [$username_concerned]); // Réinitialiser les réponses des prédictions de l'utilisateur
		executeQuery("DELETE FROM `choices` WHERE `prediction` IN (SELECT `id` FROM `predictions` WHERE `user` = ?);", [$username_concerned]); // Supprimer les choix des prédictions de l'utilisateur
		executeQuery("DELETE FROM `predictions` WHERE `user` = ?;", [$username_concerned]); // Supprimer les prédictions de l'utilisateur
		executeQuery("DELETE FROM `users` WHERE `username` = ?;", [$username_concerned]); // Supprimer l'utilisateur

		redirect("home");

	case "user_password":
		if(!isConnected()) redirect("home", "perms_connected");

		$username_connected = $_COOKIE["username"];

		$username_concerned = $_REQUEST["user"];
		$password_verification = $_REQUEST["pv"];
		$new_password = $_REQUEST["np"];
		$new_password_confirm = $_REQUEST["np_confirm"];
		if(empty($username_concerned) || empty($password_verification) || empty($new_password) || empty($new_password_confirm)) redirect("user/$username_concerned/password", "fields");
		if($new_password != $new_password_confirm) redirect("user/$username_concerned/password", "password_confirm");

		$perms = isMod() || $username_connected == $username_concerned;
		if(!$perms) redirect("user/$username_concerned/password", "perms");

		$password_verification_hash = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username_connected], "string");
		if(!password_verify($password_verification, $password_verification_hash)) redirect("user/$username_concerned/password", "password");

		$hash = password_hash($new_password, PASSWORD_DEFAULT);
		executeQuery("UPDATE `users` SET `password` = ? WHERE `username` = ?;", [$hash, $username_concerned]);

		redirect("user/$username_concerned");

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

	case "modqueue_edit":
		if(!isMod()) redirect("home", "perms_mod");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		redirect("prediction/$prediction_id/edit");

	case "prediction_edit":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("prediction/$prediction_id", "fields");

		$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
		$perms = isMod() && ($_COOKIE["username"] == $prediction_creator || !isMod($prediction_creator));
		if(!$perms) redirect("prediction/$prediction_id", "perms");

		if(
			!array_key_exists("question", $_REQUEST) ||
			!array_key_exists("details", $_REQUEST) ||
			!array_key_exists("user", $_REQUEST) ||
			!array_key_exists("created", $_REQUEST) ||
			!array_key_exists("end", $_REQUEST) ||
			!array_key_exists("choices", $_REQUEST) ||
			!array_key_exists("choices_id", $_REQUEST)
		) redirect("prediction/$prediction_id/edit", "fields");

		$question = trim(htmlspecialchars($_REQUEST["question"]));
		$details = trim(htmlspecialchars($_REQUEST["details"]));
		$user = trim(htmlspecialchars($_REQUEST["user"]));
		$created = trim(htmlspecialchars($_REQUEST["created"]));
		$end = trim(htmlspecialchars($_REQUEST["end"]));

		$choices = $_REQUEST["choices"];
		$choices_id = $_REQUEST["choices_id"];
		if(count($choices) != count($choices_id)) redirect("prediction/$prediction_id/edit", "fields");

		executeQuery("UPDATE `predictions` SET `title` = ?, `description` = ?, `user` = ?, `created` = ?, `ended` = ? WHERE `id` = ?;", [$question, $details, $user, $created, $end, $prediction_id]);

		for($i = 0; $i < count($choices); $i++){
			$choice = trim(htmlspecialchars($choices[$i]));
			$choice_id = $choices_id[$i];
			executeQuery("UPDATE `choices` SET `name` = ? WHERE `id` = ?;", [$choice, $choice_id]);
		}

		redirect("prediction/$prediction_id");

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
				$user = $bet["user"];
				$chips = $bet["chips"];
				executeQuery("UPDATE `users` SET `chips` = `chips` + ? WHERE `username` = ?;", [$chips, $user]);
				executeQuery("INSERT INTO `notifications` VALUES (?, ?, DEFAULT, DEFAULT);", [$user, "DELETED:$prediction_id,REFUNDED:$chips"]);
			}
		}else{
			executeQuery("UPDATE `predictions` SET `answer` = NULL WHERE `id` = ?;", [$prediction_id]);
		}
		executeQuery("DELETE FROM `bets` WHERE `prediction` = ?;", [$prediction_id]);
		executeQuery("DELETE FROM `choices` WHERE `prediction` = ?;", [$prediction_id]);
		executeQuery("DELETE FROM `predictions` WHERE `id` = ?;", [$prediction_id]);

		redirect("home");

	case "notifications_read":
		if(!isConnected()) redirect("home", "perms_connected");

		$user = $_COOKIE["username"];
		executeQuery("UPDATE `notifications` SET `read` = 1 WHERE `user` = ?;", [$user]);

		redirect("notifications");

	case "notifications_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$user = $_COOKIE["username"];
		executeQuery("DELETE FROM `notifications` WHERE `user` = ? AND `read` = 1;", [$user]);

		redirect("notifications");

	case "settings":
		$language = $_REQUEST["language"];
		$shorten_large_numbers = $_REQUEST["shorten_large_numbers"];
		if(empty($language) || empty($shorten_large_numbers)) redirect("settings", "fields");

		setcookie("language", $language, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("shorten_large_numbers", $shorten_large_numbers, time() + CONFIG_COOKIES_EXPIRATION);

		redirect("settings");
}