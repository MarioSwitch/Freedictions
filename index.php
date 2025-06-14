<?php
include_once "functions.php";

/**
 * Génère le nom de la page à afficher dans l'onglet du navigateur
 * @return string Nom de la page
 */
function getTitle(): string{
	switch($_REQUEST["view"]){
		case "prediction":
			if(empty($_REQUEST["id"])) redirect("home", "fields");

			$exists = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]], "int");
			if(!$exists) redirect("home", "prediction_unknown");

			$title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$_REQUEST["id"]], "string");
			break;
		case "prediction_edit":
			$title = getString("prediction_manage_edit");
			break;
		case "user":
			$title = $_REQUEST["user"];
			break;
		case "user_password":
			$title = getString("user_manage_password");
			break;
		case "user_delete":
			$title = getString("user_manage_delete");
			break;
		default:
			$title = getString("title_" . $_REQUEST["view"]);
			break;
	}
	return $title . " – " . getString("site_name");
}
?>

<html>
	<head>
		<title><?= getTitle() ?></title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<?php
		include "header.php";

		$view = "views/" . $_REQUEST["view"] . ".php";
		if(!file_exists($view)) redirect("home");
		include $view;
		echo "<br>";

		include "footer.php";
		?>
	</body>
</html>