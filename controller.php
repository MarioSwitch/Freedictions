<?php
include_once "functions.php";

switch($_REQUEST["action"]){
	case "logout":
		unset($_COOKIE["username"], $_COOKIE["password"]);
		redirect("index.php?view=home");
}