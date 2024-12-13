<h1><?= getString("title_create") ?></h1>
<form role="form" action="controller.php">
	<label for="question"><?= getString("prediction_question") ?></label>
	<input type="text" id="question" name="question" required="required" style="width:calc(var(--font-size) * 30);">
	<br>
	<label for="details"><?= getString("prediction_details") ?></label>
	<input type="text" id="details" name="details" style="width:calc(var(--font-size) * 40);">
	<br>
	<p><?= getString("create_details_desc") ?></p>
	<br><br>
	<label for="end"><?= getString("create_end") ?></label>
	<span id="end"></span>
	<br>
	<p><?= getString("create_end_desc") ?></p>
	<br><br>
	<label><?= getString("prediction_outcomes") ?></label>
	<span id="choices"></span>
	<span id="choices_add"></span>
	<br><hr class="mini"><br>
	<?= "<b>" . getString("general_note") . "</b> " . (isMod() ? getString("create_note_mod") : getString("create_note_user")) ?>
	<br><br>
	<button type="submit" name="action" value="prediction_create"><?= isMod() ? getString("create_create") : getString("create_submit") ?></button>
</form>
<script>
	// Gère la date de fin de la prédiction
	var date = new Date();
	var year = date.getYear()+1900;
	var month = ("0" + (date.getMonth()+1)).slice(-2);
	var day = ("0" + date.getDate()).slice(-2);
	var hours = ("0" + date.getHours()).slice(-2);
	var minutes = ("0" + date.getMinutes()).slice(-2);
	var local = year + "-" + month + "-" + day + "T" + hours + ":" + minutes;
	document.getElementById("end").innerHTML += "<input type=\"datetime-local\" id=\"end\" name=\"end\" required=\"required\" min=\"" + local + "\" max=\"2037-12-31T23:59\">";
	document.getElementById("end").innerHTML += "<input type=\"hidden\" name=\"offset\" value=\"" + -(date.getTimezoneOffset()) + "\">";

	//Gère les choix
	var choices = document.getElementById("choices");
	var choices_count = choices.childElementCount / 3; // 3 éléments par choix : <input>, <img>, et <br>
	let onclick_delete = "if(choices_count > 2){this.previousElementSibling.remove(); this.nextElementSibling.remove(); this.remove(); choices_count--;}";
	let choiceInput = "<input type=\"text\" name=\"choices[]\" required=\"required\"><img src=\"svg/delete.svg\" title=\"<?= getString("icon_outcome_delete") ?>\" alt=\"<?= getString("icon_outcome_delete") ?>\" class=\"delete_choice\" onclick=\"" + onclick_delete + "\"><br>";
	while(choices_count < 2){
		choices.innerHTML += choiceInput;
		choices_count++;
	}

	// Gère l'ajout de choix
	let onclick_add = "choices.insertAdjacentHTML('beforeend', choiceInput); choices_count++;";
	document.getElementById("choices_add").innerHTML = "<img src=\"svg/add.svg\" title=\"<?= getString("icon_outcome_add") ?>\" alt=\"<?= getString("icon_outcome_add") ?>\" class=\"add_choice\" onclick=\"" + onclick_add + "\">";
</script>