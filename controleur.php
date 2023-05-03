<?php
session_start();

	include_once "libs/maLibUtils.php";
	include_once "libs/maLibSQL.pdo.php";
	include_once "libs/maLibSecurisation.php";
	include_once "libs/requetes.php";

	$addArgs = "";

	if ($action = valider("action"))
	{
		ob_start ();
		echo "Action = '$action' <br />";
		switch($action)
		{
			case 'Connexion' : //Obligé d'utiliser password_verification : on met dans le modèle
				seConnecter($_REQUEST["username"],$_REQUEST["password"]);
			break;

			case 'Inscription' :
				if((!isset($_SESSION["connecte"])) && $_REQUEST["username"] && $_REQUEST["displayname"] && $_REQUEST["password"] && $_REQUEST["passwordconfirmation"] && ($_REQUEST["password"] == $_REQUEST["passwordconfirmation"])){
					$hash = password_hash($_REQUEST["password"],PASSWORD_DEFAULT);
					creerCompte($_REQUEST["username"],$_REQUEST["displayname"],$hash);
				}
			break;

			case 'SupprimerCompte' :
				if(isset($_SESSION["connecte"]) && $_REQUEST["username"] && $_REQUEST["password"]){
					supprimerCompte($_REQUEST["username"],$_REQUEST["password"]);
				}

			case 'Publier' :
				if(isset($_SESSION["connecte"]) && $_REQUEST["name"] && $_REQUEST["end"] && $_REQUEST["offset"]){
					$addArgs = "?view=prediction&id=" . creerPrediction($_REQUEST["name"],$_SESSION["user"],$_REQUEST["end"],$_REQUEST["offset"],$_REQUEST["choices"]);
				}
			break;

			case 'Parier' :
				if(isset($_SESSION["connecte"]) && $_REQUEST["prediction"] && $_REQUEST["choice"] && $_REQUEST["points"]){
					$addArgs = "?view=prediction&id=" . parier($_SESSION["user"],$_REQUEST["prediction"],$_REQUEST["choice"],$_REQUEST["points"]);
				}
			break;
			case 'Rechercher':
				$addArgs = "?view=search&q=" . $_REQUEST['recherche'];
				break;

			case 'ValiderPrediction' :
				if(isset($_SESSION["connecte"]) && $_REQUEST["prediction"] && $_REQUEST["choice"]){
					$addArgs = "?view=prediction&id=" . donnerReponsePrediction($_REQUEST["prediction"],$_REQUEST["choice"]);
				}
			break;

			case 'SupprimerPrediction' :
				if(isset($_SESSION["connecte"]) && $_REQUEST["prediction"]){
					supprimerPrediction($_REQUEST["prediction"]);
				}
			break;

			case 'Logout' :
				session_destroy();
			break;

		}

	}

	// On redirige toujours vers la page index, mais on ne connait pas le répertoire de base
	// On l'extrait donc du chemin du script courant : $_SERVER["PHP_SELF"]
	// Par exemple, si $_SERVER["PHP_SELF"] vaut /chat/data.php, dirname($_SERVER["PHP_SELF"]) contient /chat

$urlBase = "index.php";
	// On redirige vers la page index avec les bons arguments

	header("Location:" . $urlBase . $addArgs);

	// On écrit seulement après cette entête
	ob_end_flush();
	
?>










