<?php
session_start();

include_once "sql.php";

$view = $_GET["view"];
if (!$view) $view = "home";

include("views/header.php");

if (file_exists("views/$view.php")) include("views/$view.php");
?>