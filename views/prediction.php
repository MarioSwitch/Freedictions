<?php
include_once "time.js.php";

/**
 * Génère le code HTML pour afficher une boîte d'information prédiction
 * @param string $info Information à afficher (« created_time », « created_user », « ended » ou « participation »)
 * @return string Code HTML
 */
function displayPredictionBox(string $info): string{
	global $created_user, $created_time, $ended, $answer, $answer_name, $volume_chips, $volume_users;
	$value = match($info){
		"created" => $created_time,
		"proposed" => "<a href=\"../user/$created_user\">" . displayUser($created_user) . "</a>",
		"time_remaining" => $ended,
		"outcome" => $answer ? $answer_name : getString("prediction_waiting_outcome"),
		"volume" => displayInt($volume_chips) . insertTextIcon("chips", "right", 2) . ", " . displayInt($volume_users) . insertTextIcon("users", "right", 2),
	};
	$caption = getString("prediction_$info");
	$id = ($info == "created" || $info == "time_remaining") ? " id=\"$info\"" : "";
	$html = "
	<div style=\"display:inline-block; border:1px solid var(--color-text); border-radius: 10px; width:15%; min-width:250px; max-width:400px;\">
		<p style=\"font-size:calc(var(--font-size) * 2.0); margin:calc(var(--font-size) * 0.5);\"$id>$value</p>
		<p style=\"font-size:calc(var(--font-size) * 0.8); margin:calc(var(--font-size) * 0.5);\">$caption</p>
	</div>";
	if($info == "created" || $info == "time_remaining"){
		$html .= "<script>display(\"$value\", \"$info\");</script>";
	}
	return $html;
}

/**
 * Détermine si l'utilisateur actuel est le créateur de la prédiction
 * @return bool Vrai si l'utilisateur actuel est le créateur de la prédiction
 */
function isCreator(): bool{
	global $created_user;
	if(!isConnected()) return false;
	return $_COOKIE["username"] == $created_user;
}

$id = $_REQUEST["id"];
$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$id], "int");
$question = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$created_user = executeQuery("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$created_time = executeQuery("SELECT `created` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$ended = executeQuery("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$answer = executeQuery("SELECT `answer` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$answer_name = $answer ? executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$answer], "string") : "";
$answered = executeQuery("SELECT `answered` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$now = executeQuery("SELECT NOW();", [], "string");

$details = executeQuery("SELECT `description` FROM `predictions` WHERE `id` = ?;", [$id], "string");
$details_text = $details ? "<h2>" . getString("prediction_details") . "</h2><p>$details</p><br>" : "";

$choices = executeQuery("SELECT * FROM `choices` WHERE `prediction` = ?;", [$id]);
$choices_count = count($choices);
$choices_contains_numbers = false;
foreach($choices as $choice){
	if(preg_match("/[0-9]/", $choice["name"])) $choices_contains_numbers = true;
}
$volume_chips = executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `prediction` = ?;", [$id], "int");
$volume_users = executeQuery("SELECT COUNT(`user`) FROM `bets` WHERE `prediction` = ?;", [$id], "int");
$choices_bets = [];
foreach($choices as $choice){
	$choice_id = $choice["id"];
	$choices_bets[$choice_id] = array(
		"chips" => executeQuery("SELECT SUM(`chips`) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int"),
		"users" => executeQuery("SELECT COUNT(`user`) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int")
	);
	$choices_bets[$choice_id]["percentage"] = $volume_chips ? $choices_bets[$choice_id]["chips"] / $volume_chips * 100 : 0;
	$choices_bets[$choice_id]["ratio"] = $choices_bets[$choice_id]["chips"] ? $volume_chips / $choices_bets[$choice_id]["chips"] : 0;
	$choices_bets[$choice_id]["top_chips"] = executeQuery("SELECT MAX(`chips`) FROM `bets` WHERE `choice` = ?;", [$choice_id], "int");
	$choices_bets[$choice_id]["top_users"] = executeQuery("SELECT `user` FROM `bets` WHERE `choice` = ? AND `chips` = ?;", [$choice_id, $choices_bets[$choice_id]["top_chips"]]);
}
usort($choices, function($a, $b){
	global $choices_contains_numbers, $choices_bets;
	if($choices_contains_numbers || $choices_bets[$a["id"]]["chips"] == $choices_bets[$b["id"]]["chips"]) return $a["id"] - $b["id"]; // Si les choix contiennent des chiffres (ou si le nombre de jetons misés est égal), on trie par ID
	return $choices_bets[$b["id"]]["chips"] - $choices_bets[$a["id"]]["chips"]; // Sinon, on trie par nombre de jetons misés décroissant
});
$choices_table = "
<table class=\"choices_list\">
	<thead>
		<tr>
			<th>" . getString("prediction_outcome") . "</th>
			<th>" . getString("prediction_probability") . "</th>
			<th>" . getString("prediction_volume") . "</th>
			<th>" . getString("prediction_to_win") . "</th>
			<th>" . getString("prediction_top") . "</th>
		</tr>
	</thead>
	<tbody>";
	foreach($choices as $choice){
		$choice_id = $choice["id"];
		$choice_name = $choice["name"];
		$choice_chips = $choices_bets[$choice_id]["chips"];
		$choice_percentage = $choices_bets[$choice_id]["percentage"];
		$choice_users = $choices_bets[$choice_id]["users"];
		$choice_ratio = $choices_bets[$choice_id]["ratio"];
		$choice_top_chips = $choices_bets[$choice_id]["top_chips"];
		$choice_top_users = $choices_bets[$choice_id]["top_users"];
		$choice_top = "–";
		if($choice_top_chips){
			$choice_top = displayInt($choice_top_chips) . insertTextIcon("chips", "right", 1);
			foreach($choice_top_users as $choice_user){
				$choice_user = $choice_user[0];
				$choice_top .= "<br><a href=\"../user/$choice_user\">" . displayUser($choice_user) . "</a>";
			}
		}
		$choices_table .= "
		<tr>
			<td>$choice_name</td>
			<td>" . displayFloat($choice_percentage, true) . "</td>
			<td>
				" . displayInt($choice_chips) . insertTextIcon("chips", "right", 1) . "<br>
				" . displayInt($choice_users) . insertTextIcon("users", "right", 1) . "
			</td>
			<td>" . ($choice_ratio ? displayRatio($choice_ratio) : "–") . "</td>
			<td>$choice_top</td>
		</tr>";
	}
$choices_table .= "
	</tbody>
</table>";

$already_bet = isConnected() ? executeQuery("SELECT COUNT(*) FROM `bets` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $id], "int") : false;
if($already_bet){
	$already_bet_choice_id = executeQuery("SELECT `choice` FROM `bets` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $id], "int");
	$already_bet_choice_name = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$already_bet_choice_id], "string");
	$already_bet_chips = executeQuery("SELECT `chips` FROM `bets` WHERE `user` = ? AND `prediction` = ?;", [$_COOKIE["username"], $id], "int");
}

$choices_select = "
<select name=\"choice\" required=\"required\">
	<option value=\"\" disabled=\"disabled\" selected=\"selected\">" . getString("general_select") . "</option>";
foreach($choices as $choice){
	$choice_id = $choice["id"];
	$choice_name = $choice["name"];
	$choices_select .= "<option value=\"$choice_id\">$choice_name</option>";
}
$choices_select .= "</select>";
$choices_select_full = $choices_select;
if($already_bet) $choices_select = "
	<select name=\"choice\" required=\"required\">
		<option value=\"$already_bet_choice_id\" selected=\"selected\">$already_bet_choice_name</option>
	</select>
";

$chips_total_raw = isConnected() ? executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "int") : 0;
$chips_total = "<span style=\"zoom:1.2;\">" . displayInt($chips_total_raw) . insertTextIcon("chips", "right", 1) . "</span>";

$chips_input = "<input style=\"margin-bottom:0px;\" type=\"number\" name=\"chips\" min=\"1\" max=\"$chips_total_raw\" required=\"required\">" . insertTextIcon("chips", "right", 1.2);
if($already_bet) $chips_input = "<span style=\"zoom:1.2;\">" . displayInt($already_bet_chips) . " + </span>" . $chips_input;

$bet_html = "
<form role=\"form\" action=\"controller.php\">
	<table class=\"hidden\">
		<tbody>
			<tr>
				<td>" . getString("prediction_outcome") . "</td>
				<td>$choices_select</td>
			</tr>
			<tr>
				<td>" . getString("user_chips") . "</td>
				<td>$chips_total</td>
			</tr>
			<tr>
				<td>" . getString("prediction_bet_noun") . "</td>
				<td>$chips_input</td>
			</tr>
		</tbody>
	</table>
	<br>
	<input type=\"hidden\" name=\"prediction\" value=\"$id\">
	<button type=\"submit\" name=\"action\" value=\"prediction_bet\">" . getString("prediction_bet_verb") . "</button>
</form>";
if(!isConnected()){
	$bet_html = "
	<p>" . getString("error_perms_connected") . "</p>
	<button onclick=\"location.href='../signin'\">" . getString("signin_button") . "</button>
	<button onclick=\"location.href='../signup'\">" . getString("signup_button") . "</button>
	";
}
if($now >= $ended){
	$bet_html = "<p>" . getString("prediction_sentence_closed", ["<span id=\"ended\">$ended</span>"]) . "<script>display(\"$ended\", \"ended\");</script></p>";
	if($answer) $bet_html .= "<p>" . getString("prediction_sentence_resolved", [$answer_name, "<span id=\"answered\">$answered</span>"]) . "<script>display(\"$answered\", \"answered\");</script></p>";
}

$manage_close = "
<form role=\"form\" action=\"controller.php\">
	<input type=\"hidden\" name=\"prediction\" value=\"$id\">
	<button type=\"submit\" name=\"action\" value=\"prediction_close\">" . getString("prediction_manage_close") . "</button>
</form>";

$manage_resolve = "
<form role=\"form\" action=\"controller.php\">
	<input type=\"hidden\" name=\"prediction\" value=\"$id\">
	$choices_select_full
	<button type=\"submit\" name=\"action\" value=\"prediction_resolve\">" . getString("prediction_manage_resolve") . "</button>
	<p>" . getString("prediction_manage_resolve_desc") . "<br>" . getString("prediction_manage_cant_be_undone") . "</p>
</form>";

$manage_delete = "
<form role=\"form\" action=\"controller.php\">
	<input type=\"hidden\" name=\"prediction\" value=\"$id\">
	<button type=\"submit\" name=\"action\" value=\"prediction_delete\">" . getString("prediction_manage_delete") . "</button>
	<p>" . ($answer ? "" : (getString("prediction_manage_delete_desc") . "<br>")) . getString("prediction_manage_cant_be_undone") . "</p>
</form>";

if($now < $ended){
	$manage_html = $manage_close . "<br>" . $manage_delete;
}else if(!$answer){
	$manage_html = $manage_resolve . "<br>" . $manage_delete;
}else{
	$manage_html = $manage_delete;
}

// Affichage
echo "<h1>$question</h1>";
if(!$approved){
	echo "<p>" . getString("prediction_waiting_approval") . "</p>";
}
if($approved || isMod()){
	echo 
	"<div>" .
		displayPredictionBox("created") .
		displayPredictionBox("proposed") .
		displayPredictionBox(($now >= $ended) ? "outcome" : "time_remaining") .
		displayPredictionBox("volume") .
	"</div>";
	echo "
	<br>
	$details_text
	<h2>" . getString("prediction_outcomes") . " ($choices_count)" . "</h2>
	$choices_table
	<br><br>
	<h2>" . getString("prediction_bet_verb") . "</h2>
	$bet_html";
	if(isCreator() || (isMod() && !isMod($created_user))){
		echo "
		<br>
		<h2>" . getString("prediction_manage") . "</h2>
		$manage_html";
	}
}