<h1><?= getString("title_signin") ?></h1>
<form role="form" action="controller.php">
	<label for="username"><?= getString("general_username") ?></label>
	<input type="text" id="username" name="username" required="required" autocomplete="current-password">
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" id="password" name="password" required="required" autocomplete="current-password">
	<br>
	<button type="submit" name="action" value="signin"><?= getString("signin_button") ?></button>
</form>