<?php
session_start();

include_once "sql.php";

$view = array_key_exists("view",$_REQUEST)?$_REQUEST["view"]:"home";

include("views/header.php");

if (file_exists("views/$view.php")) include("views/$view.php");
?>