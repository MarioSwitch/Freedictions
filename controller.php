<?php
include_once "functions.php";

define("NOW", executeQuery("SELECT NOW();", [], "string")); // Utilisation de define(), car « const NOW = … » nécessite une valeur brute (pas de fonction, ni de variable)

switch($_REQUEST["action"]){
	case "signin":
		$username = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		if(empty($username) || empty($password)){
			redirect("signin", "fields");
			die("");
		}
		$user_exists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$username], "int");
		if(!$user_exists){
			redirect("signin", "unknown");
			die("");
		}
		$hash_saved = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$username], "string");
		if(!password_verify($password, $hash_saved)){
			redirect("signin", "password");
			die("");
		}
		setcookie("username", $username, time() + CONFIG_COOKIES_EXPIRATION);
		setcookie("password", $password, time() + CONFIG_COOKIES_EXPIRATION);
		redirect("home");

	case "signout":
		unset($_COOKIE["username"], $_COOKIE["password"]);
		redirect("home");
}