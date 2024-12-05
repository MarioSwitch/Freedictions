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

		executeQuery("UPDATE `predictions` SET `approved` = 1 WHERE `id` = ?;", [$prediction_id]);
		redirect("modqueue");

	case "modqueue_reject":
		if(!isMod()) redirect("home", "perms_mod");

		$prediction_id = $_REQUEST["id"];
		if(empty($prediction_id)) redirect("modqueue", "fields");

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
}