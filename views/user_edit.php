<?php
$username = $_REQUEST["user"];
$exists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$username], "int");
if(!$exists) redirect("home", "username_unknown");
if(!isAuthorized(NULL, "user_edit", $username)) redirect("user/$username", "perms");

$data = executeQuery("SELECT * FROM `users` WHERE `username` = ?;", [$username]);

$username = $data[0]["username"];
$password = $data[0]["password"];
$created = $data[0]["created"];
$updated = $data[0]["updated"];
$streak = $data[0]["streak"];
$chips = $data[0]["chips"];
$mod = $data[0]["mod"];
$extra = $data[0]["extra"];
?>
<h1><?= getString("user_manage_edit") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" id="username_old" name="username_old" value="<?= $username ?>" required="required">
	<label for="username_new"><?= getString("general_username") ?></label>
	<input type="text" id="username_new" name="username_new" value="<?= $username ?>" required="required">
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="text" id="password" name="password" value="<?= $password ?>" required="required">
	<br>
	<label for="created"><?= getString("user_created_updated_streak") ?></label>
	<input type="text" id="created" name="created" value="<?= $created ?>" required="required"><br>
	<input type="text" id="updated" name="updated" value="<?= $updated ?>" required="required">
	<input type="text" id="streak" name="streak" value="<?= $streak ?>" required="required">
	<br>
	<label for="chips"><?= getString("general_chips") ?></label>
	<input type="text" id="chips" name="chips" value="<?= $chips ?>" required="required">
	<br>
	<label for="mod"><?= getString("tooltip_moderator") ?></label>
	<input type="text" id="mod" name="mod" value="<?= $mod ?>" required="required">
	<br>
	<label for="extra"><?= getString("user_extra") ?></label>
	<input type="text" id="extra" name="extra" value="<?= $extra ?>" required="required" style="width:calc(var(--font-size) * 30);">
	<br>
	<button type="submit" name="action" value="user_edit"><?= getString("general_save") ?></button>
</form>