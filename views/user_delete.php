<?php
$username = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?;", [$_REQUEST["user"]], "string");
?>
<h1><?= getString("user_manage_delete") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" name="user" value="<?= $username ?>">
	<label for="user"><?= getString("general_user") ?></label>
	<p id="user" class="input_disabled"><?= $username ?></p>
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" name="password" id="password" required="required">
	<br>
	<button type="submit" name="action" value="user_delete"><?= getString("user_manage_delete") ?></button>
	<p><?= getString("general_cant_be_undone") ?></p>
</form>