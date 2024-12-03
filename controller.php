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
}