<?php
include_once "functions.php";

if(!array_key_exists("view", $_REQUEST)){
    header("Location:index.php?view=home");
    die();
}

$view = $_REQUEST["view"];

if(!file_exists("views/$view.php")){
    header("Location:index.php?view=home");
    die();
}

include "header.php";

include "views/$view.php";

echo "<br>";