<?php
$username_connected = $_COOKIE["username"];
$username_concerned = $_REQUEST["user"];

$user_exists = count(executeQuery("SELECT * FROM `users` WHERE `username` = ?", [$username_concerned]));
if(!$user_exists) redirect("home");

$perms = isMod() || $username_connected == $username_concerned;
if(!$perms) redirect("user/$username_concerned", "perms");
?>
<h1><?= getString("user_manage_password") ?></h1>
<form role="form" action="controller.php">
	<label for="user"><?= getString("general_user") ?></label>
	<input type="text" name="user" id="user" value="<?= $username_concerned ?>" required="required" readonly="readonly">
	<br>
	<label for="pv"><?= getString("password_current") ?></label>
	<input type="password" name="pv" id="pv" required="required">
	<br>
	<label for="np"><?= getString("password_new") ?></label>
	<input type="password" name="np" id="np" required="required">
	<br>
	<label for="np_confirm"><?= getString("password_new_confirm") ?></label>
	<input type="password" name="np_confirm" id="np_confirm" required="required">
	<br>
	<button type="submit" name="action" value="user_password"><?= getString("general_save") ?></button>
</form>