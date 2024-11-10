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

define("NOW", executeQuery("SELECT NOW();", [], "string")); // Utilisation de define(), car « const NOW = … » nécessite une valeur brute (pas de fonction, ni de variable)

/**
 * Réinitialise la date d'expiration des cookies
 * @return void
 */
function resetCookiesExpiration(): void{
	foreach($_COOKIE as $key => $value){
		setcookie($key, $value, time()+CONFIG_COOKIES_EXPIRATION);
	}
}

/**
 * Vérifie que les cookies de connexion sont valides et retourne le statut de connexion
 * @return bool Vrai si l'utilisateur est connecté, faux sinon
 */
function isConnected(): bool{
	if(!(array_key_exists("username", $_COOKIE) && array_key_exists("password", $_COOKIE))){
		logout();
		return false;
	}
	if(executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "int") == 0){
		logout();
		return false;
	}
	$hash_saved = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "string");
	if(!password_verify($_COOKIE["password"],$hash_saved)){
		logout();
		return false;
	}
	return true;
}

/**
 * Vérifie si un utilisateur est un modérateur
 * @param string $user Utilisateur à vérifier. Si omis, vérifie l'utilisateur actuellement connecté
 * @return bool Vrai si l'utilisateur est un modérateur, faux sinon
 */
function isMod($user = NULL): bool{
	if($user == NULL){ // Utilisateur actuellement connecté
		if(!isConnected()) return false;
		$user = $_COOKIE["username"];
	}else{ // Utilisateur spécifié
		$userExists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$user], "int");
		if(!$userExists) return false;
	}
	return executeQuery("SELECT `mod` FROM `users` WHERE `username` = ?;", [$user], "int") == 1;
}

/**
 * Vérifie si un utilisateur possède un rôle supplémentaire
 * @param string $type Rôle supplémentaire à vérifier
 * @param string $user Utilisateur à vérifier. Si omis, vérifie l'utilisateur actuellement connecté
 * @return bool Vrai si l'utilisateur possède le rôle supplémentaire, faux sinon
 */
function isExtra(string $type, string $user = NULL): bool{
	if($user == NULL){ // Utilisateur actuellement connecté
		if(!isConnected()) return false;
		$user = $_COOKIE["username"];
	}else{ // Utilisateur spécifié
		$userExists = executeQuery("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$user], "int");
		if(!$userExists) return false;
	}
	$extra = executeQuery("SELECT `extra` FROM `users` WHERE `username` = ?;", [$user], "string");
	return preg_match("/$type/", $extra) == 1;
}

/**
 * Déconnecte l'utilisateur (supprime les cookies de connexion)
 * @return void
 */
function logout(): void{
	unset($_COOKIE["username"], $_COOKIE["password"]);
}

/**
 * Affiche un utilisateur (nom d'utilisateur et rôles supplémentaires)
 * @param string $username Nom de l'utilisateur à afficher
 * @param bool $link Vrai pour inclure un lien vers la page de l'utilisateur, faux sinon
 * @return string Nom d'utilisateur et rôles supplémentaires
 */
function displayUser(string $username, bool $link = false): string{
	$roles = ""; // TODO: Ajouter les rôles supplémentaires (isMod et isExtra)
	$full_username = $roles . $username;
	if($link){
		return "<a href=\"user/$username\">$full_username</a>";
	}else{
		return $full_username;
	}
}

/**
 * Affiche un nombre entier
 * @param int $int Entier à afficher
 * @param bool $shorten Vrai pour tronquer les grands nombres (ex. 3 456 789 -> 3,45 M et non 3,46 M; -3 456 789 -> -3,45 M et non -3,46 M)
 * @param bool $force_sign Vrai pour afficher le signe « + » devant les nombres positifs, et « ± » devant 0
 * @return string Nombre entier formaté
 */
function displayInt(int $int, bool $shorten = true, bool $force_sign = false): string{
	// Détermination du signe
	$sign = "";
	if($int < 0) $sign = "-";
	if($force_sign && $int > 0) $sign = "+";
	if($force_sign && $int == 0) $sign = "±";

	// Retrait du signe pour le traitement
	// À partir d'ici, « nombre » signifie la valeur absolue.
	$int = abs($int);

	// PHP_INT_MAX vaut 9 223 372 036 854 775 807, soit environ 9,22e18.
	// Les nombres supérieurs à 1 000 000 000 000 000 000 (1e18) sont masqués.
	if($int >= 1e18) return "–";

	// Insertion des séparateurs de milliers
	$full_int = number_format($int, 0, getString("decimal_separator"), getString("thousands_separator"));

	// Le nombre est TOUJOURS affiché en entier si :
	// - L'option $shorten est désactivée (on force l'affichage complet pour un cas précis, comme l'affichage des rangs)
	// - Le nombre est inférieur à 1 million (pas besoin de raccourcir)
	// - L'utilisateur a désactivé le raccourcissement des grands nombres dans les paramètres
	if(!$shorten || $int < 1e6 || getSetting("shorten_large_numbers") == "no") return $sign . $full_int;

	// Calcul du nombre de chiffres
	$digits = strlen($int);

	// Sélection du préfixe adapté
	$suffix = match($digits){
		7, 8, 9 => getString("abbr_million"),
		10, 11, 12 => getString("abbr_billion"),
		13, 14, 15 => getString("abbr_trillion"),
		16, 17, 18 => getString("abbr_quadrillion"),
		default => ""
	};

	// Extraction des 3 premiers chiffres (d'où le fait que le résultat est tronqué et non arrondi)
	$formatted_int = intval(substr($int, 0, 3));

	// Calcul du nombre de décimales à afficher
	//   1 234 567 -> 1,23 M -> 2 décimales (7 chiffres, 7%3 = 1, 3-1 = 2, 2%3 = 2)
	//  12 345 678 -> 12,3 M -> 1 décimale (8 chiffres, 8%3 = 2, 3-2 = 1, 1%3 = 1)
	// 123 456 789 -> 123 M -> 0 décimales (9 chiffres, 9%3 = 0, 3-0 = 3, 3%3 = 0)
	$digits_decimals = (3 - $digits%3) % 3;

	// Division du nombre pour placer le séparateur de décimales au bon endroit
	$formatted_int = $formatted_int / pow(10, $digits_decimals);

	// Application du bon nombre de décimales et du séparateur de décimales
	$formatted_int = number_format($formatted_int, $digits_decimals, getString("decimal_separator"), getString("thousands_separator"));

	// Retourne le nombre raccourci avec le nombre complet en infobulle
	return "<abbr title=\"" . $sign . $full_int . "\">" . $sign . $formatted_int . $suffix . "</abbr>";
}