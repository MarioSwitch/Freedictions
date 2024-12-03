<?php
$username = $_REQUEST["user"];

$user_exists = count(executeQuery("SELECT * FROM `users` WHERE `username` = ?", [$username]));
if(!$user_exists) redirect("home");

$username_capitalization = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?", [$username], "string");
if($username != $username_capitalization) redirect("user/$username_capitalization");

$created = executeQuery("SELECT `created` FROM `users` WHERE `username` = ?", [$username], "string");
$updated = executeQuery("SELECT `updated` FROM `users` WHERE `username` = ?", [$username], "string");
$streak = executeQuery("SELECT `streak` FROM `users` WHERE `username` = ?", [$username], "int");
$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?", [$username], "int");

include_once "time.js.php";
/**
 * Génère le code HTML pour afficher une boîte d'information utilisateur
 * @param string $info Information à afficher (« created », « updated », « streak » ou « chips »)
 * @return string Code HTML
 */
function displayUserBox(string $info): string{
	global $created, $updated, $streak, $chips;
	$value = match($info){
		"created" => $created,
		"updated" => $updated,
		"streak" => displayInt($streak),
		"chips" => displayInt($chips),
	};
	$caption = getString("profile_$info");
	$id = ($info == "created" || $info == "updated") ? "id=\"$info\"" : "";
	$html = "
	<div style=\"display:inline-block; border:1px solid var(--color-text); border-radius: 10px; width:15%; min-width:250px; max-width:400px;\">
		<p style=\"font-size:calc(var(--font-size) * 2.0); margin:calc(var(--font-size) * 0.5);\" $id>$value</p>
		<p style=\"font-size:calc(var(--font-size) * 0.8); margin:calc(var(--font-size) * 0.5);\">$caption</p>
	</div>";
	if($info == "created" || $info == "updated"){
		$html .= "<script>display(\"$value\", \"$info\");</script>";
	}
	return $html;
}
?>
<h1><?= $username ?></h1>
<div>
	<?= displayUserBox("created") ?>
	<?= displayUserBox("updated") ?>
	<?= displayUserBox("streak") ?>
	<?= displayUserBox("chips") ?>
</div>