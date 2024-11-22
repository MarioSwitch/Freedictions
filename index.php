<?php
include_once "functions.php";

/**
 * Génère le nom de la page à afficher dans l'onglet du navigateur
 * @return string Nom de la page
 */
function getTitle(): string{
	switch($_REQUEST["view"]){
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

		include "footer.php";
		?>
	</body>
</html>