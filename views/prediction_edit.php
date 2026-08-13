<?php
$id = $_REQUEST["id"];
$prediction = executeQuery("SELECT * FROM `predictions` WHERE `id` = ?;", [$id], "row");

$question = $prediction["title"];
$details = $prediction["description"];
$creator = $prediction["user"];
$created_UTC = $prediction["created"];
$end_UTC = $prediction["ended"];
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