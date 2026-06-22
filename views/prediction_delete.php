<?php
$username_connected = $_COOKIE["username"];
$prediction_concerned = $_REQUEST["id"];

$prediction_exists = count(executeQuery("SELECT * FROM `predictions` WHERE `id` = ?", [$prediction_concerned]));
if(!$prediction_exists) redirect("home");

$prediction_question = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?", [$prediction_concerned], "string");
$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?", [$prediction_concerned], "string");

$perms = $username_connected == $prediction_creator || (isMod() && !isMod($prediction_creator)); // isCreator() || (isMod() && !isMod($created_user))
if(!$perms) redirect("prediction/$prediction_concerned", "perms");
?>

<h1><?= getString("prediction_manage_delete") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" name="prediction" value="<?= $prediction_concerned ?>">
	<label for="question"><?= getString("prediction_question") ?></label>
	<p id="question" style="font-size: calc(var(--font-size) * 1.2); font-weight: bold;"><?= $prediction_question ?></p>
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" name="password" id="password" required="required">
	<br>
	<button type="submit" name="action" value="prediction_delete"><?= getString("prediction_manage_delete") ?></button>
</form>