<h1><?= getString("title_signin") ?></h1>
<form role="form" action="controller.php">
	<label for="username"><?= getString("username") ?></label>
	<input type="text" id="username" name="username" required="required" autocomplete="current-password">
	<br>
	<label for="password"><?= getString("password") ?></label>
	<input type="password" id="password" name="password" required="required" autocomplete="current-password">
	<br>
	<button type="submit" name="action" value="signin"><?= getString("signin") ?></button>
</form>