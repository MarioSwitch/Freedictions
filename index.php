<?php
include_once "functions.php";

$view = array_key_exists("view",$_REQUEST)?$_REQUEST["view"]:"home";

include("header.php");

if (file_exists("views/$view.php")) include("views/$view.php");
?>