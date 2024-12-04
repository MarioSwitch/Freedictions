<?php
$id = $_REQUEST["id"];
$question = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$id], "string");
?>
<h1><?= $question ?></h1>