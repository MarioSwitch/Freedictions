<h1><?= getString("title_signup") ?></h1>
<form role="form" action="controller.php">
	<label for="username"><?= getString("general_username") ?></label>
	<p><?= getString("signup_username_format") ?></p>
	<input type="text" id="username" name="username" required="required" autocomplete="current-password" pattern="[A-Za-z0-9]{4,20}">
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" id="password" name="password" required="required" autocomplete="current-password">
	<br>
	<label for="password"><?= getString("signup_password_confirm") ?></label>
	<input type="password" id="password_confirm" name="password_confirm" required="required" autocomplete="current-password">
	<br>
	<button type="submit" name="action" value="signup"><?= getString("signup_button") ?></button>
</form>