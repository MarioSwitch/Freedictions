<?php
$username = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?;", [$_REQUEST["user"]], "string");
?>
<h1><?= getString("user_manage_password") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" name="user" value="<?= $username ?>">
	<label for="user"><?= getString("general_user") ?></label>
	<p id="user" class="input_disabled"><?= $username ?></p>
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