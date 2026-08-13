<?php
$id = $_REQUEST["id"];
$prediction = executeQuery("SELECT * FROM `predictions` WHERE `id` = ?;", [$id], "row");
?>

<h1><?= getString("prediction_manage_delete") ?></h1>
<form role="form" action="controller.php">
	<input type="hidden" name="prediction" value="<?= $id ?>">
	<label for="question"><?= getString("prediction_question") ?></label>
	<p id="question" class="input_disabled"><?= $prediction["title"] ?></p>
	<br>
	<label for="password"><?= getString("general_password") ?></label>
	<input type="password" name="password" id="password" required="required">
	<br>
	<button type="submit" name="action" value="prediction_delete"><?= getString("prediction_manage_delete") ?></button>
	<p><?= getString("general_cant_be_undone") ?></p>
</form>