<?php
$username_connected = $_COOKIE["username"];
$username_concerned = $_REQUEST["user"];

$user_exists = count(executeQuery("SELECT * FROM `users` WHERE `username` = ?", [$username_concerned]));
if(!$user_exists) redirect("home");

$perms = isMod() || $username_connected == $username_concerned;
if(!$perms) redirect("user/$username_concerned", "perms");
?>
<h1><?= getString("user_manage_delete") ?></h1>
<form role="form" action="controller.php">
	<label for="user"><?= getString("general_user") ?></label>
	<input type="text" name="user" id="user" value="<?= $username_concerned ?>" required="required" readonly="readonly">
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" name="password" id="password" required="required">
	<br>
	<button type="submit" name="action" value="user_delete"><?= getString("user_manage_delete") ?></button>
</form>