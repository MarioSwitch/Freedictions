<?php
include_once "functions.php";
$allUsers = executeQuery("SELECT * FROM users");
print_r($allUsers);