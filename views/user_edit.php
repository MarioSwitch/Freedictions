<?php
$username = $_REQUEST["user"];
$user = executeQuery("SELECT * FROM `users` WHERE `username` = ?;", [$username], "row");
if(!$user) redirect("home", "username_unknown");
if(!isAuthorized(NULL, "user_edit", $username)) redirect("user/$username", "perms");

$username = $user["username"];
$password = $user["password"];
$created = $user["created"];
$updated = $user["updated"];
$streak = $user["streak"];
$chips = $user["chips"];
$mod = $user["mod"];
$extra = $user["extra"];
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
	<input type="text" id="extra" name="extra" value="<?= $extra ?>" style="width:calc(var(--font-size) * 30);">
	<br>
	<button type="submit" name="action" value="user_edit"><?= getString("general_save") ?></button>
</form>