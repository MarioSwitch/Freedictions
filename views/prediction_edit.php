<?php
$id = $_REQUEST["id"];
$exists = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `id` = ?;", [$id], "int");
if(!$exists) redirect("home", "prediction_unknown");

$prediction_creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$perms = isMod() && ($_COOKIE["username"] == $prediction_creator || !isMod($prediction_creator));
if(!$perms) redirect("prediction/$id", "perms");

$question = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$details = executeQuery("SELECT `description` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$creator = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$created_UTC = executeQuery("SELECT `created` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$end_UTC = executeQuery("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$choices = executeQuery("SELECT `id`, `name` FROM `choices` WHERE `prediction` = ?;", [$id]);
?>
<h1><?= getString("prediction_manage_edit") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" name="prediction" value="<?= $id ?>">
	<label for="question"><?= getString("prediction_question") ?></label>
	<input type="text" id="question" name="question" value="<?= $question ?>" required="required" style="width:calc(var(--font-size) * 30);">
	<br>
	<label for="details"><?= getString("prediction_details") ?></label>
	<input type="text" id="details" name="details" value="<?= $details ?>" style="width:calc(var(--font-size) * 40);">
	<br>
	<label for="user"><?= getString("prediction_created") ?></label>
	<input type="text" id="user" name="user" value="<?= $creator ?>">
	<input type="text" id="created" name="created" value="<?= $created_UTC ?>">
	<br>
	<label for="end"><?= getString("create_end") ?></label>
	<input type="text" id="end" name="end" value="<?= $end_UTC ?>">
	<br>
	<label><?= getString("prediction_outcomes") ?></label>
	<?php
	foreach($choices as $choice){
		echo "
		<input type=\"hidden\" name=\"choices_id[]\" value=\"" . $choice["id"] . "\">
		<input type=\"text\" name=\"choices[]\" value=\"" . $choice["name"] . "\" required=\"required\"><br>";
	}
	?>
	<br>
	<button type="submit" name="action" value="prediction_edit"><?= getString("general_save") ?></button>
</form>