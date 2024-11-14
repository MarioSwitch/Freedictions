<?php
include_once "functions.php";

/**
 * Génère le nom de la page à afficher dans l'onglet du navigateur
 * @return string Nom de la page
 */
function getTitle(): string{
	switch($_REQUEST["view"]){
		case "index.php":
		case "home":
			$title = getString("title_home");
			break;
		default:
			$title = $_REQUEST["view"];
			break;
	}
	return $title . " – " . getString("site_name");
}
?>

<html>
	<head>
		<title><?php echo getTitle() ?></title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<?php
		include "header.php";
		include "views/" . $_REQUEST["view"] . ".php";
		?>
	</body>
</html>