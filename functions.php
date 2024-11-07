<?php
include_once "config.php"; // Vous devez inclure VOTRE fichier de configuration. Lisez le fichier README.md pour connaître les constantes à définir.

/**
 * Exécute une requête sur la base de données
 * @param string $query Requête SQL (remplacer les arguments par des « ? »)
 * @param array $args Tableau des arguments
 * @param string $result_type Type de résultat (« array » (par défaut), « string », « int » ou « float »)
 * @return array|string|int|float Résultat de la requête
 */
function executeQuery(string $query, array $args = [], string $result_type = "array"): array|string|int|float{
	$database_handler = null;

	// Démarre la connexion à la base de données
	try{
		$database_handler = new PDO("mysql:host=" . CONFIG_DATABASE_HOST . ";dbname=" . CONFIG_DATABASE_NAME, CONFIG_DATABASE_USER, CONFIG_DATABASE_PASSWORD);
	}catch(PDOException $exception){
		die("<span style=\"color:red\">" . $exception->getMessage() . "</span>");
	}

	// Exécute la requête
	try{
		$statement_handler = $database_handler->prepare($query);
		for($i=0; $i<count($args); $i++){
			$statement_handler->bindParam($i+1, $args[$i]); // $i+1 car les paramètres sont indexés à partir de 1
		}
		$result = $statement_handler->execute();
		if($result === false){
			die("<span style=\"color:red\">" . $database_handler->errorInfo()[2] . "</span>");
		}
		$result = $statement_handler->fetchAll();
	}catch(PDOException $exception){
		die("<span style=\"color:red\">" . $query . "<br>" . print_r($args, true) . "<br>" . $exception->getMessage() . "</span>");
	}

	// Ferme la connexion à la base de données
	$database_handler = null;

	// Retourne le résultat
	return match($result_type){
		"string" => $result[0][0],
		"int" => intval($result[0][0]),
		"float" => floatval($result[0][0]),
		default => $result
	};
}

/**
 * Récupère une chaîne de caractères dans le fichier de langue
 * @param string $key Clé (identifiant) de la chaîne
 * @param array $args Tableau d'arguments à remplacer dans la chaîne
 * @return string Chaîne de caractères extraite du fichier de langue, ou la clé si la chaîne n'existe pas
 */
function getString(string $key, array $args = []): string{
	$language = getSetting("language");
	if(!file_exists("strings/$language.json")) return $key; // Fichier de langue inexistant
	$language_strings = json_decode(file_get_contents("strings/$language.json"), true);
	if(!array_key_exists($key, $language_strings)) return $key; // Clé inexistante dans le fichier de langue
	$string = $language_strings[$key];
	foreach($args as $arg){
		$string = preg_replace("/\[TBR\]/", $arg, $string, 1);
	}
	return $string;
}

/**
 * Retourne la valeur d'un paramètre utilisateur.
 * Le crée et l'assigne à sa valeur par défaut s'il n'existe pas.
 * Le réinitialise à sa valeur par défaut s'il n'est pas valide.
 * @param string $name Nom (identifiant) du paramètre
 * @return string Valeur du paramètre
 */
function getSetting($name): string{
	switch($name){
		case "language":
			$default = "fr";
			$supported = ["fr", "en", "de"];
			break;
		case "shorten_large_numbers":
			$default = "yes";
			$supported = ["yes", "no"];
			break;
		case "time_display":
			$default = "relative";
			$supported = ["relative", "local", "utc"];
			break;
	}
	if(array_key_exists($name, $_COOKIE)){
		return in_array($_COOKIE[$name], $supported) ? $_COOKIE[$name] : $default;
	}else{
		setcookie($name, $default, time()+CONFIG_COOKIES_EXPIRATION);
		return $default;
	}
}