<?php
include_once "functions.php";
include_once "forms.js.php";
include_once "time.js.php";

$view_name = $_REQUEST["view"];
$view_path = "views/" . $view_name . ".php";
$view_exploded = explode("_", $view_name);

/**
 * Génère le nom de la page à afficher dans l'onglet du navigateur ou redirige le cas échéant
 * @return string Nom de la page
 */
function getTitle(): string{
	global $view_name, $view_path, $view_exploded;
	if(!file_exists($view_path)) redirect("home", "view_unknown");

	// Default title
	$title = getString("title_" . $view_name);

	switch($view_name){
		// Prediction-related cases
		case "prediction":
			if(empty($_REQUEST["id"])) redirect("home", "fields");

			$title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]], "string");
			if(!$title) redirect("home", "prediction_unknown");
			break;

		case "prediction_delete":
		case "prediction_edit":
			if(empty($_REQUEST["id"])) redirect("home", "fields");

			$prediction_id = executeQuery("SELECT `id` FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]], "int");
			if(!$prediction_id) redirect("home", "prediction_unknown");
			if(!isAuthorized(NULL, $view_name, $prediction_id)) redirect("prediction/$prediction_id", "perms");

			$title = getString("prediction_manage_$view_exploded[1]");
			break;

		// User-related cases
		case "user":
			if(empty($_REQUEST["user"])) redirect("home", "fields");

			$title = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?;", [$_REQUEST["user"]], "string");
			if(!$title) redirect("home", "username_unknown");
			break;

		case "user_delete":
		case "user_edit":
		case "user_password":
			if(empty($_REQUEST["user"])) redirect("home", "fields");

			$username = executeQuery("SELECT `username` FROM `users` WHERE `username` = ?;", [$_REQUEST["user"]], "string");
			if(!$username) redirect("home", "username_unknown");
			if(!isAuthorized(NULL, $view_name, $username)) redirect("user/$username", "perms");

			$title = getString("user_manage_$view_exploded[1]");
			break;

		// Generic cases: no $title overwrite needed
		case "history":
		case "leaderboard":
			$results_per_page = array_key_exists("results", $_REQUEST) ? intval($_REQUEST["results"]) : 50;
			$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;
			if($results_per_page < 1 || $page_number < 1) redirect($view_name);
			break;

		case "modqueue":
			if(!isAuthorized(NULL, "modqueue_access", NULL)) redirect("home", "perms");
			break;

		case "notifications":
			if(!isConnected()) redirect("home", "perms_connected");
			break;
	}
	return $title . " – " . getString("site_name");
}
?>

<html>
	<head>
		<title><?= getTitle() ?></title>
		<?php
		switch(getSetting("theme")){
			case "light": echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style_light.css\">"; break;
			case "dark": echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style_dark.css\">"; break;
			case "black": echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style_black.css\">"; break;
		}
		?>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<?php
		include "header.php";
		include $view_path;
		echo "<br>";
		include "footer.php";
		?>
	</body>
</html>