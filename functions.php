<?php
/**
 * Vous devez créer le fichier et définir :
 * - CONFIG_COOKIES_EXPIRATION : Durée de vie des cookies en secondes
 * - CONFIG_DATABASE_HOST : Adresse du serveur de base de données (par exemple "localhost")
 * - CONFIG_DATABASE_NAME : Nom de la base de données
 * - CONFIG_DATABASE_USER : Nom de l'utilisateur ayant accès à la base de données
 * - CONFIG_DATABASE_PASSWORD : Mot de passe de l'utilisateur ayant accès à la base de données
 * 
 * Pour définir une constante, utilisez la fonction PHP define().
 * Par exemple, define("CONFIG_COOKIES_EXPIRATION", 30*24*60*60); définit la constante CONFIG_COOKIES_EXPIRATION à 30 jours. 
 */
include_once "config.php";

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