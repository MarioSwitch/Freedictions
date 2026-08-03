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
		executeQuery("CALL `UserCreate`(?, ?);", [$username, $hash]);
		redirect("controller.php?action=signin&username=$username&password=$password");

	case "signin":
		$username = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		if(empty($username) || empty($password)) redirect("signin", "fields");

		$user = executeQuery("SELECT `username`, `password` FROM `users` WHERE `username` = ?;", [$username], "row");
		if(!$user) redirect("signin", "username_unknown");
		if(!password_verify($password, $user["password"])) redirect("signin", "password");

		setcookie("username", $user["username"], time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("password", $password, time() + CONFIG_COOKIES_EXPIRATION);
		redirect("home");

	case "signout":
		setcookie("username", "", time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("password", "", time() + CONFIG_COOKIES_EXPIRATION);
		redirect("home");

	case "user_edit":
		if(!isConnected()) redirect("home", "perms_connected");

		$username_old = $_REQUEST["username_old"];
		if(empty($username_old)) redirect("home", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $username_old)) redirect("user/$username_old", "perms");

		if(
			!array_key_exists("username_old", $_REQUEST) ||
			!array_key_exists("username_new", $_REQUEST) ||
			!array_key_exists("password", $_REQUEST) ||
			!array_key_exists("created", $_REQUEST) ||
			!array_key_exists("updated", $_REQUEST) ||
			!array_key_exists("streak", $_REQUEST) ||
			!array_key_exists("chips", $_REQUEST) ||
			!array_key_exists("mod", $_REQUEST) ||
			!array_key_exists("extra", $_REQUEST)
		) redirect("user/$username_old/edit", "fields");

		$username_old = trim(htmlspecialchars($_REQUEST["username_old"]));
		$username_new = trim(htmlspecialchars($_REQUEST["username_new"]));
		$password = trim(htmlspecialchars($_REQUEST["password"]));
		$created = trim(htmlspecialchars($_REQUEST["created"]));
		$updated = trim(htmlspecialchars($_REQUEST["updated"]));
		$streak = trim(htmlspecialchars($_REQUEST["streak"]));
		$chips = trim(htmlspecialchars($_REQUEST["chips"]));
		$mod = trim(htmlspecialchars($_REQUEST["mod"]));
		$extra = trim(htmlspecialchars($_REQUEST["extra"]));

		executeQuery("CALL `UserEdit`(?, ?, ?, ?, ?, ?, ?, ?, ?);", [$username_new, $password, $created, $updated, $streak, $chips, $mod, $extra, $username_old]);

		redirect("user/$username_new");

	case "user_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$username_connected = $_COOKIE["username"];

		$username_concerned = $_REQUEST["user"];
		$password = $_REQUEST["password"];
		if(empty($username_concerned) || empty($password)) redirect("user/$username_concerned/delete", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $username_concerned)) redirect("user/$username_concerned/delete", "perms");

		$password_hash = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username_connected], "string");
		if(!password_verify($password, $password_hash)) redirect("user/$username_concerned/delete", "password");

		executeQuery("CALL `UserDelete`(?);", [$username_concerned]);

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

		if(!isAuthorized(NULL, $_REQUEST["action"], $username_concerned)) redirect("user/$username_concerned/password", "perms");

		$password_verification_hash = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username_connected], "string");
		if(!password_verify($password_verification, $password_verification_hash)) redirect("user/$username_concerned/password", "password");

		$hash = password_hash($new_password, PASSWORD_DEFAULT);
		executeQuery("CALL `UserPassword`(?, ?);", [$username_concerned, $hash]);

		redirect("user/$username_concerned");

	case "modqueue_approve":
		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("home", "perms");

		executeQuery("CALL `ModqueueApprove`(?);", [$prediction_id]);

		redirect("prediction/$prediction_id");

	case "modqueue_reject":
		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("home", "perms");

		executeQuery("CALL `ModqueueReject`(?);", [$prediction_id]);

		redirect("modqueue");

	case "modqueue_edit":
		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("home", "perms");

		redirect("prediction/$prediction_id/edit");

	case "prediction_edit":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("home", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("prediction/$prediction_id", "perms");

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

		executeQuery("CALL `PredictionEdit`(?, ?, ?, ?, ?, ?, ?, ?);", [$prediction_id, $question, $details, $user, $created, $end, json_encode($choices, JSON_UNESCAPED_UNICODE), json_encode($choices_id, JSON_UNESCAPED_UNICODE)]);

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

		$approved = isAuthorized(NULL, "prediction_create_approved", NULL);

		date_default_timezone_set("UTC");
		$endUTC = date("Y-m-d\TH:i", strtotime($end) - $offset*60);

		$id = executeQuery("CALL `PredictionCreate`(?, ?, ?, ?, ?, ?);", [$question, $details, $_COOKIE["username"], $endUTC, $approved, json_encode($choices, JSON_UNESCAPED_UNICODE)], "int");

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

		$prediction = executeQuery("SELECT `ended`, `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "row");
		if(!$prediction["approved"]) redirect("prediction/$prediction_id", "prediction_not_approved");
		if(NOW >= $prediction["ended"]) redirect("prediction/$prediction_id", "prediction_closed");

		executeQuery("CALL `PredictionBet`(?, ?, ?, ?);", [$_COOKIE["username"], $prediction_id, $choice_id, $chips]);

		redirect("prediction/$prediction_id");

	case "prediction_close":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		if(empty($prediction_id)) redirect("prediction/$prediction_id", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("prediction/$prediction_id", "perms");

		$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "int");
		if(!$approved) redirect("prediction/$prediction_id", "prediction_not_approved");

		executeQuery("CALL `PredictionClose`(?);", [$prediction_id]);

		redirect("prediction/$prediction_id");

	case "prediction_resolve":
		if(!isConnected()) redirect("home", "perms_connected");

		$prediction_id = $_REQUEST["prediction"];
		$choice_id = $_REQUEST["choice"];
		if(empty($prediction_id) || empty($choice_id)) redirect("prediction/$prediction_id", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("prediction/$prediction_id", "perms");

		$prediction = executeQuery("SELECT `approved`, `answer`, `ended` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "row");
		if(!$prediction["approved"]) redirect("prediction/$prediction_id", "prediction_not_approved");
		if($prediction["answer"]) redirect("prediction/$prediction_id", "prediction_resolved");
		if(NOW < $prediction["ended"]) redirect("prediction/$prediction_id", "prediction_opened");

		$choice_prediction = executeQuery("SELECT `prediction` FROM `choices` WHERE `id` = ?;", [$choice_id], "int");
		if($choice_prediction != $prediction_id) redirect("prediction/$prediction_id", "fields");

		executeQuery("CALL `PredictionResolve`(?, ?);", [$prediction_id, $choice_id]);

		redirect("prediction/$prediction_id");

	case "prediction_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$username_connected = $_COOKIE["username"];
		
		$prediction_id = $_REQUEST["prediction"];
		$password = $_REQUEST["password"];
		if(empty($username_connected) || empty($password)) redirect("prediction/$prediction_id/delete", "fields");

		if(!isAuthorized(NULL, $_REQUEST["action"], $prediction_id)) redirect("prediction/$prediction_id/delete", "perms");

		$password_hash = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username_connected], "string");
		if(!password_verify($password, $password_hash)) redirect("prediction/$prediction_id/delete", "password");

		executeQuery("CALL `PredictionDelete`(?);", [$prediction_id]);

		redirect("home");

	case "notifications_read":
		if(!isConnected()) redirect("home", "perms_connected");

		$user = $_COOKIE["username"];
		executeQuery("CALL `NotificationsRead`(?);", [$user]);

		redirect("notifications");

	case "notifications_delete":
		if(!isConnected()) redirect("home", "perms_connected");

		$user = $_COOKIE["username"];
		executeQuery("CALL `NotificationsDelete`(?);", [$user]);

		redirect("notifications");

	case "settings":
		$language = $_REQUEST["language"];
		$theme = $_REQUEST["theme"];
		$shorten_large_numbers = $_REQUEST["shorten_large_numbers"];
		if(empty($language) || empty($theme) || empty($shorten_large_numbers)) redirect("settings", "fields");

		setcookie("language", $language, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("theme", $theme, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("shorten_large_numbers", $shorten_large_numbers, time() + CONFIG_COOKIES_EXPIRATION);

		redirect("settings");
}