<?php
include_once "config.php";

/**
 * Executes a SQL query on the database
 * @param string $query SQL query (replace custom arguments with "?")
 * @param array $args Arguments array to replace "?" placeholders (if any)
 * @param string $result_type Result type ("array" (by default), "row", "string", "int" or "float")
 * @return array|string|int|float Query result
 */
function executeQuery(string $query, array $args = [], string $result_type = "array"): array|string|int|float{
	$database_handler = null;

	// Initiates database connection
	try{
		$database_handler = new PDO("mysql:host=" . CONFIG_DATABASE_HOST . ";dbname=" . CONFIG_DATABASE_NAME, CONFIG_DATABASE_USER, CONFIG_DATABASE_PASSWORD);
	}catch(PDOException $exception){
		die("<span class=\"error\">" . $exception->getMessage() . "</span>");
	}

	// Executes the query
	try{
		$statement_handler = $database_handler->prepare($query);
		for($i=0; $i<count($args); $i++){
			$statement_handler->bindParam($i+1, $args[$i]); // $i+1 as parameters are indexed from 1
		}
		$result = $statement_handler->execute();
		if($result === false){
			die("<span class=\"error\">" . $database_handler->errorInfo()[2] . "</span>");
		}
		$result = $statement_handler->fetchAll();
	}catch(PDOException $exception){
		die("<span class=\"error\">" . $query . "<br>" . print_r($args, true) . "<br>" . $exception->getMessage() . "</span>");
	}

	// Closes database connection
	$database_handler = null;

	// Returns result
	return match($result_type){
		"row"    => $result ? $result[0]              : [],
		"string" => $result ? strval($result[0][0])   : "",
		"int"    => $result ? intval($result[0][0])   : 0,
		"float"  => $result ? floatval($result[0][0]) : 0.0,
		default  => $result ? $result                 : []
	};
}

/**
 * Redirects to another site page
 * @param string $link Target page (e.g. "home" or "user/MarioSwitch")
 * @param string $error Error code (if any)
 * @return void
 */
function redirect(string $link, string $error = ""): void{
	header("Location: " . CONFIG_PATH . "/$link" . ($error ? "?error=$error" : ""));
	die("");
}

/**
 * Inserts a SVG icon in a text
 * @param string $icon SVG filename (without ".svg")
 * @param string $align Icon position relative to the text ("left" or "right")
 * @param float $scale Icon scale (factor of font size)
 * @return string <span> containing the SVG icon
 */
function insertTextIcon(string $icon, string $align, float $scale): string{
	$align = match($align){
		"left" => "margin-right:calc(var(--font-size) * 0.1);",
		"right" => "margin-left:calc(var(--font-size) * 0.1);",
		default => ""
	};
	$alt = getString("icon_" . $icon);
	$style = "
		width: calc(var(--font-size) * $scale);
		height: calc(var(--font-size) * $scale);
		vertical-align: bottom;
		$align;
		cursor: help;

		display: inline-block;
		background: currentColor;
		mask: url(svg/$icon.svg) no-repeat center / contain;
	";
	return "<span title=\"$alt\" alt=\"$alt\" style=\"$style\"></span>"; // Using <span> instead of <img> to allow recoloring
}

/**
 * Gets a string from a language file
 * @param string $key String key
 * @param array $args Arguments array to replace "[TBR]" placeholders (if any)
 * @return string Translated string (or English string/key if not found)
 */
function getString(string $key, array $args = []): string{
	// file_get_contents very slow -> caching file contents
	static $english_strings = NULL;
	if($english_strings == NULL) $english_strings = json_decode(file_get_contents("strings/en.json"), true);
	$english_string = array_key_exists($key, $english_strings) ? $english_strings[$key] : $key;

	$language = getSetting("language");
	if(!file_exists("strings/$language.json")) return $english_string; // Unknown language file

	static $language_strings = NULL;
	if($language_strings == NULL) $language_strings = json_decode(file_get_contents("strings/$language.json"), true);
	if(!array_key_exists($key, $language_strings)) return $english_string; // Unknown key in language file

	$string = $language_strings[$key];
	foreach($args as $arg){
		$string = preg_replace("/\[TBR\]/", $arg, $string, 1);
	}
	return $string;
}

/**
 * Gets the list of supported languages
 * @return array List of supported languages
 */
function getSupportedLanguages(): array{
	$languages = [];
	$files = array_diff(scandir("strings"), [".", ".."]);
	foreach($files as $file){
		array_push($languages, substr($file, 0, 2));
	}
	return $languages;
}

/**
 * Gets the user's preferred language
 * @return string User's preferred language
 */
function getPreferredLanguage(): string{
	$raw = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	$array = explode(",", $raw);
	foreach($array as $language){
		$language_code = strtolower(substr($language, 0, 2));
		if(file_exists("strings/$language_code.json")) return $language_code;
	}
	return "en";
}

/**
 * Gets the value of a user setting.
 * Creates it and assigns it to its default value if it doesn't exist.
 * Resets it to its default value if it's not valid.
 * @param string $name Setting name
 * @return string Setting value
 */
function getSetting(string $name): string{
	switch($name){
		case "language":
			$default = getPreferredLanguage();
			$supported = getSupportedLanguages();
			break;
		case "theme":
			$default = "light";
			$supported = ["light", "dark", "black"];
			break;
		case "shorten_large_numbers":
			$default = "yes";
			$supported = ["yes", "no"];
			break;
		case "results_per_page":
			$default = "50";
			$supported = ["10", "25", "50", "100"];
			break;
	}
	if(array_key_exists($name, $_COOKIE)){
		return in_array($_COOKIE[$name], $supported) ? $_COOKIE[$name] : $default;
	}else{
		setcookie($name, $default, time()+CONFIG_COOKIES_EXPIRATION, "/");
		return $default;
	}
}

/**
 * Resets cookies expiration
 * @return void
 */
function resetCookiesExpiration(): void{
	foreach($_COOKIE as $key => $value){
		setcookie($key, $value, time()+CONFIG_COOKIES_EXPIRATION, "/");
	}
}

/**
 * Checks if session cookies are correct and returns connection status
 * @return bool True if the user is connected, false otherwise
 */
function isConnected(): bool{
	// password_verify very slow -> caching connection status
	static $isConnected = false;
	if($isConnected) return true;

	if(!(array_key_exists("username", $_COOKIE) && array_key_exists("password", $_COOKIE))){
		unset($_COOKIE["username"], $_COOKIE["password"]);
		return false;
	}
	$hash_saved = executeQuery("SELECT `password` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "string");
	if(!$hash_saved){
		unset($_COOKIE["username"], $_COOKIE["password"]);
		return false;
	}
	if(!password_verify($_COOKIE["password"],$hash_saved)){
		unset($_COOKIE["username"], $_COOKIE["password"]);
		return false;
	}
	$isConnected = true;
	return true;
}

/**
 * Checks if a user is authorized to perform an action
 * @param string|null $user User to check. If NULL, checks the current user
 * @param string $action Action to perform (see controller.php)
 * @param string|int|null $id Target ID for the action (username or prediction number). Can be NULL for some generic actions.
 * @return bool True if the user is authorized to perform the action, false otherwise
 */
function isAuthorized(string|null $user, string $action, string|int|null $id): bool{
	if(!isConnected()) return false;
	if($user == NULL) $user = $_COOKIE["username"];

	$perms = executeQuery("SELECT `mod` FROM `users` WHERE `username` = ?;", [$user], "int");
	$isAdministrator = $perms >= 3;
	$isModerator = $perms >= 2;
	$isVerifier = $perms >= 1;

	$targetPerms = match($action){
		"user_password", "user_delete", "user_edit" => executeQuery("SELECT `mod` FROM `users` WHERE `username` = ?;", [$id], "int"),
		"prediction_close", "prediction_resolve", "prediction_edit", "prediction_delete" => executeQuery("SELECT `mod` FROM `users` WHERE `username` = (SELECT `user` FROM `predictions` WHERE `id` = ?);", [$id], "int"),
		default => 0
	};

	switch($action){
		case "user_password":
		case "user_delete":
			if($isAdministrator) return true;
			if($isModerator) return $user == $id || $targetPerms < 2; // Only yourself, default and verifiers
			return $user == $id;

		case "user_edit":
			return $isAdministrator;

		case "modqueue_access":
		case "modqueue_approve":
		case "modqueue_reject":
		case "prediction_create_approved":
			return $isVerifier;

		case "prediction_close":
		case "prediction_resolve":
			if($isAdministrator) return true;
			if($isModerator) return $targetPerms <= 2; // Only default, verifiers and moderators
			if($isVerifier) return $targetPerms <= 1; // Only default and verifiers
			return false;

		case "modqueue_edit":
		case "prediction_edit":
		case "prediction_delete":
			if($isAdministrator) return true;
			$approved = executeQuery("SELECT `approved` FROM `predictions` WHERE `id` = ?;", [$id], "int");
			if($isModerator) return !$approved || $targetPerms < 2; // All non-approved, and approved from default and verifiers
			if($isVerifier) return !$approved; // Only non-approved
			return false;

		default:
			return false;
	}
}

/**
 * Displays a user (username and extra roles)
 * @param string $username Username to display
 * @param bool $link True to include a link to the user's page, false otherwise
 * @return string Username and extra roles
 */
function displayUser(string $username, bool $link = false): string{
	$icons = [
		"verified" => "✔️",

		"alpha" => "💛",
		"beta" => "🩶",

		"developer" => "💻",
		"translator" => "🌍",
	];

	$user = executeQuery("SELECT `mod`, `extra` FROM `users` WHERE `username` = ?;", [$username], "row");

	$extras = "";
	$extras_array = $user["extra"] ? explode(",", $user["extra"]) : [];
	foreach($icons as $icon_title => $icon_icon){
		if(!in_array($icon_title, $extras_array)) continue;
		$tooltip = getString("tooltip_" . $icon_title);
		$extras .= "<span title=\"$tooltip\">$icon_icon</span>";
	}

	$perms = $user["mod"];
	$isAdministrator = $perms >= 3;
	$isModerator = $perms >= 2;
	$isVerifier = $perms >= 1;

	if($isAdministrator){
		$tooltip = getString("tooltip_administrator");
		$full_username = $extras . "<span title=\"$tooltip\"class=\"administrator\">" . $username ."</span>";
	}else if($isModerator){
		$tooltip = getString("tooltip_moderator");
		$full_username = $extras . "<span title=\"$tooltip\"class=\"moderator\">" . $username ."</span>";
	}else if($isVerifier){
		$tooltip = getString("tooltip_verifier");
		$full_username = $extras . "<span title=\"$tooltip\"class=\"verifier\">" . $username ."</span>";
	}else{
		$full_username = $extras . $username;
	}

	if($link){
		return "<a href=\"user/$username\">$full_username</a>";
	}else{
		return $full_username;
	}
}

/**
 * Displays an integer
 * @param int $int Integer to display
 * @param bool $shorten True to shorten large numbers (e.g. 3,456,789 -> 3.45M and not 3.46M; -3,456,789 -> -3.45M and not -3.46M)
 * @param bool $force_sign True to display "+" before positive numbers, and "±" before 0
 * @return string Formatted integer
 */
function displayInt(int $int, bool $shorten = true, bool $force_sign = false): string{
	// Manages the sign
	$sign = "";
	if($int < 0) $sign = "-";
	if($force_sign && $int > 0) $sign = "+";
	if($force_sign && $int == 0) $sign = "±";

	// Discards the sign for processing
	// From now on, "number" means the absolute value
	$int = abs($int);

	// PHP_INT_MAX equals 9,223,372,036,854,775,807; i.e. 9.22e18
	// Numbers greater than 1,000,000,000,000,000,000 (1e18) are hidden
	if($int >= 1e18) return "–";

	// Inserts thousands separators
	$full_int = number_format($int, 0, getString("decimal_separator"), getString("thousands_separator"));

	// Number is ALWAYS fully displayed if:
	// - $shorten is false: we need the full number in specific cases like displaying ranks
	// - Number is lower than 1 million: no need to shorten
	// - "Shorten large numbers" setting is set to "No": user preference
	if(!$shorten || $int < 1e6 || getSetting("shorten_large_numbers") == "no") return $sign . $full_int;

	// Computes the number of digits
	$digits = strlen($int);

	// Selects the suffix
	$suffix = match($digits){
		7, 8, 9 => getString("abbr_million"),
		10, 11, 12 => getString("abbr_billion"),
		13, 14, 15 => getString("abbr_trillion"),
		16, 17, 18 => getString("abbr_quadrillion"),
		default => ""
	};

	// Extracts the first 3 digits (that's why the result is truncated and not rounded)
	$formatted_int = intval(substr($int, 0, 3));

	// Computes the number of decimals to display
	//   1,234,567 -> 1.23M -> 2 decimals (7 digits, 7%3 = 1, 3-1 = 2, 2%3 = 2)
	//  12,345,678 -> 12.3M -> 1 decimal (8 digits, 8%3 = 2, 3-2 = 1, 1%3 = 1)
	// 123,456,789 -> 123M -> 0 decimals (9 digits, 9%3 = 0, 3-0 = 3, 3%3 = 0)
	$digits_decimals = (3 - $digits%3) % 3;

	// Divides the number to correctly place the decimal separator
	$formatted_int = $formatted_int / pow(10, $digits_decimals);

	// Applies the correct number of decimals and the decimal separator
	$formatted_int = number_format($formatted_int, $digits_decimals, getString("decimal_separator"), getString("thousands_separator"));

	// Returns the shortened number with the full number in a tooltip
	return "<abbr title=\"" . $sign . $full_int . "\">" . $sign . $formatted_int . $suffix . "</abbr>";
}

/**
 * Displays a rank
 * @param int $rank Rank to display
 * @return string Formatted rank
 */
function displayRank(int $rank): string{
	if($rank <= 0) return "–"; // Impossible ranks
	$suffix = "";
	switch(getSetting("language")){
		case "en":
			if($rank % 10 == 1 && !in_array($rank % 100, [11, 12, 13])) $suffix = "<sup>st</sup>";
			if($rank % 10 == 2 && !in_array($rank % 100, [11, 12, 13])) $suffix = "<sup>nd</sup>";
			if($rank % 10 == 3 && !in_array($rank % 100, [11, 12, 13])) $suffix = "<sup>rd</sup>";
			if(!in_array($rank % 10, [1, 2, 3]) || in_array($rank % 100, [11, 12, 13])) $suffix = "<sup>th</sup>";
			break;
		case "de":
			$suffix = ".";
			break;
		case "fr":
			if($rank == 1) $suffix = "<sup>er</sup>";
			if($rank >= 2) $suffix = "<sup>e</sup>";
			break;
	}
	return displayInt($rank, false) . $suffix;
}

/**
 * Displays a float
 * @param float $float Float to display
 * @param bool $percentage True to display a percentage
 * @return string Formatted float
 */
function displayFloat(float $float, bool $percentage = false): string{
	// If the float is outside the percentage range (0-100), uses displayInt() instead
	if($float > 100) return displayInt(intval(floor($float)));
	if($float < 0) return displayInt(intval(ceil($float)));

	// Formats with 2 decimals
	$number = number_format($float, 2, getString("decimal_separator"), getString("thousands_separator"));

	// If the float is a percentage, formats accordingly
	return $percentage ? getString("percentage", [$number]) : $number;
}

/**
 * Displays a ratio (e.g. 1.23 -> "+23.00%", 4.56 -> "×4.56")
 * @param float $ratio Ratio to display
 * @return string Formatted ratio
 */
function displayRatio(float $ratio): string{
	if($ratio < 2) return "+" . displayFloat(($ratio - 1) * 100, true);
	return "×" . displayFloat($ratio);
}

/**
 * Displays a paginated table
 * @param array $data Data to display (use "SELECT * FROM ...")
 * @param string $type Type of data ("opened", "closed", "users" or "notifications")
 * @param array $columns Columns to display for types "opened" or "closed" (e.g. ["title", "created", "volume"]). Supported: "title", "outcomes", "proposed", "created", "volume", "bet", "answer", "time" (ended/answered with checks), "time_modqueue" (ended/answered without checks, for modqueue), "actions" (approve/reject/edit, for modqueue)
 * @return string HTML table
 */
function displayPaginatedTable(array $data, string $type, array $columns = ["title"]): string{
	$url = $_SERVER["REQUEST_URI"];
	$url_parts = parse_url($url);
	parse_str($url_parts["query"] ?? "", $url_params);

	$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;
	$results_per_page = intval(getSetting("results_per_page"));
	$offset = ($page_number - 1) * $results_per_page;
	$count = count($data);

	$table_top = ($page_number - 1) * $results_per_page + 1;
	$table_bottom = min($table_top + $results_per_page - 1, $count);

	$previous_page = $page_number - 1;
	$previous_page_params = array_merge($url_params, ["page" => $previous_page]);
	$previous_page_url = $url_parts["path"] . "?" . http_build_query($previous_page_params);
	$previous_start = $table_top - $results_per_page;
	$previous_end = $table_top - 1;

	$next_page = $page_number + 1;
	$next_page_params = array_merge($url_params, ["page" => $next_page]);
	$next_page_url = $url_parts["path"] . "?" . http_build_query($next_page_params);
	$next_start = $table_bottom + 1;
	$next_end = min($table_bottom + $results_per_page, $count);

	if($type == "opened" || $type == "closed"){
		$predictions = array_slice($data, $offset, $results_per_page, true);
		if(!$predictions) return getString("predictions_none");
		$html = "
		<table class=\"list predictions_list\">
			<thead>
				<tr>";
					foreach($columns as $column){
						$th = match($column){
							"title" => getString("prediction_question") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_bottom, false) . " / " . displayInt($count, false) . "</small>",
							"outcomes" => getString("prediction_outcomes"),
							"proposed" => getString("prediction_proposed"),
							"created" => getString("prediction_created"),
							"volume" => getString("prediction_volume"),
							"bet" => getString("prediction_bet_noun"),
							"answer" => getString("prediction_outcome"),
							"time", "time_modqueue" => $type == "opened" ? getString("general_time_remaining") : getString("general_time_elapsed"),
							"actions" => getString("modqueue_actions"),
							default => $column
						};
						$th = "<th>" . $th . "</th>";
						$html .= $th;
					}
					$html .= "
				</tr>
			</thead>
			<tbody>";
				foreach($predictions as $prediction){
					$html .= "<tr>";
					$id = $prediction["id"];
					$answer = $prediction["answer"];
					foreach($columns as $column){
						switch($column){
							case "title":
								$title = $prediction["title"];
								$td = "<td><a href=\"" . CONFIG_PATH . "/prediction/$id\">$title</a></td>";
								break;
							case "outcomes":
								$choices_array = executeQuery("SELECT * FROM `choices` WHERE `prediction` = ?;", [$id]);
								$choices = "";
								foreach($choices_array as $choice){
									$choices .= $choice["name"];
									if($choice != end($choices_array)) $choices .= "<br>";
								}
								$td = "<td>$choices</td>";
								break;
							case "proposed":
							case "created":
								$created_user = $prediction["user"];
								$created_time = $prediction["created"];
								$abbr_id = bin2hex(random_bytes(8));
								$td = "<td>" . displayUser($created_user, true) . "<br><abbr id=\"$abbr_id\">$created_time</abbr></td><script>display(\"$created_time\",\"$abbr_id\")</script>";
								break;
							case "volume":
								$volume = executeQuery("SELECT COALESCE(COUNT(*), 0) as `users`, COALESCE(SUM(`chips`), 0) as `chips` FROM `bets` WHERE `prediction` = ?;", [$id], "row");
								$users = $volume["users"];
								$chips = $volume["chips"];
								$td = "<td>" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "<br>" . displayInt($users) . insertTextIcon("users", "right", 1) . "</td>";
								break;
							case "bet":
								if(array_key_exists("user", $_REQUEST)){$user = $_REQUEST["user"];}
								else if(isConnected())                 {$user = $_COOKIE["username"];}
								else                                   {$td = "<td></td>"; break;}
								$bet = executeQuery("SELECT `choices`.`name`, `bets`.`chips` FROM `choices` JOIN `bets` ON `bets`.`choice` = `choices`.`id` WHERE `choices`.`prediction` = ? AND `bets`.`user` = ?;", [$id, $user], "row");
								if(!$bet){$td = "<td></td>"; break;}
								$bet_name = $bet["name"];
								$bet_chips = $bet["chips"];
								$td = "<td>" . displayInt($bet_chips) . insertTextIcon("chips", "right", 1) . "<br>$bet_name</td>";
								break;
							case "answer":
								$answer_name = $answer ? executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$answer], "string") : getString("prediction_waiting_outcome");
								$unanswered = ($type == "closed" && !$answer) ? "class=\"unanswered\"" : "";
								$td = "<td $unanswered>$answer_name</td>";
								break;
							case "time":
								$now = executeQuery("SELECT NOW();", [], "string");
								$closed = $now >= $prediction["ended"];
								if(!$prediction["approved"]){$td = "<td>" . getString("prediction_waiting_approval") . "</td>"; break;}
								if($type == "opened" && $closed){$td = "<td>" . getString("prediction_waiting_outcome") . "</td>"; break;}
								// No break here on purpose: once checks are done, "time_modqueue" is the same as "time", excepted formatting ($unanswered) that we want ONLY for "time" (not for modqueue)
							case "time_modqueue":
								$time = $type == "opened" ? $prediction["ended"] : ($answer ? $prediction["answered"] : $prediction["ended"]);
								$unanswered = ($column == "time" && $type == "closed" && !$answer) ? "class=\"unanswered\"" : "";
								$abbr_id = bin2hex(random_bytes(8));
								$td = "<td $unanswered><abbr id=\"$abbr_id\">$time</abbr></td><script>display(\"$time\",\"$abbr_id\")</script>";
								break;
							case "actions":
								$actions = "
									<form class=\"actions\" role=\"form\" action=\"controller.php\">
										<input type=\"hidden\" name=\"prediction\" value=\"$id\">
										<button type=\"submit\" name=\"action\" value=\"modqueue_approve\">" . getString("modqueue_actions_approve") . "</button>
										<button type=\"submit\" name=\"action\" value=\"modqueue_reject\">" . getString("modqueue_actions_reject") . "</button>
										<button type=\"submit\" name=\"action\" value=\"modqueue_edit\">" . getString("modqueue_actions_edit") . "</button>
									</form>";
								$td = "<td>$actions</td>";
								break;
							default:
								$td = "<td></td>";
						}
						$html .= $td;
					}
					$html .= "</tr>";
				}
				if($page_number >= 2 || $next_start <= $count){
					$html .= "
					<tr>
						<td class=\"previous\">";
							if($page_number >= 2) $html .= "<a href=\"$previous_page_url\">◄<br><small>" . displayInt($previous_start, false) . " – " . displayInt($previous_end, false) . "</small></a>";
						$html .= "
						</td>";
						for($i = 0; $i < count($columns) - 2; $i++){
							$html .= "<td></td>";
						}
						$html .= "
						<td class=\"next\">";
							if($next_start <= $count) $html .= "<a href=\"$next_page_url\">►<br><small>" . displayInt($next_start, false) . " – " . displayInt($next_end, false) . "</small></a>";
						$html .= "
						</td>
					</tr>";
				}
				$html .= "
			</tbody>
		</table>";

		return $html;
	}

	if($type == "users"){
		$users = array_slice($data, $offset, $results_per_page, true);
		if(!$users) return getString("general_user_none");
		if(isConnected()){
			$my_username = $_COOKIE["username"];
			$my_position = NULL;
			foreach($data as $i => $row){
				if($row["username"] == $my_username){
					$my_position = $i + 1;
					break;
				}
			}

			$my_page = ceil($my_position / $results_per_page);
			$my_page_params = array_merge($url_params, ["page" => $my_page]);
			$my_page_url = $url_parts["path"] . "?" . http_build_query($my_page_params);
		}
		$html = "
		<table class=\"list users_list\">
			<thead>
				<tr>
					<th>" . getString("general_rank") . "</th>
					<th>" . getString("general_user") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_bottom, false) . " / " . displayInt($count, false) . "</small></th>
					<th>" . getString("general_chips") . "</th>
				</tr>
			</thead>
			<tbody>";
				foreach($users as $user){
					$username = $user["username"];
					$chips = $user["chips"];
					$rank = executeQuery("SELECT COUNT(*) FROM `users` WHERE `chips` > ?;", [$chips], "int") + 1;
					$my_row = (isConnected() && $username == $my_username) ? "mine" : "";

					$html .= "
					<tr class=\"$my_row\">
						<td>" . displayRank($rank) . "</td>
						<td>" . displayUser($username, true) . "</td>
						<td>" . displayInt($chips) . "</td>
					</tr>";
				}
				if($page_number >= 2 || isConnected() && $my_position || $next_start <= $count){
					$html .= "
					<tr>
						<td class=\"previous\">";
							if($page_number >= 2) $html .= "<a href=\"$previous_page_url\">◄<br><small>" . displayInt($previous_start, false) . " – " . displayInt($previous_end, false) . "</small></a>";
						$html .= "
						</td>
						<td>";
							if(isConnected() && $my_position) $html .= "<a href=\"$my_page_url\">" . getString("leaderboard_page", [displayInt($my_page, false)]) . "</a>";
						$html .= "
						</td>
						<td class=\"next\">";
							if($next_start <= $count) $html .= "<a href=\"$next_page_url\">►<br><small>" . displayInt($next_start, false) . " – " . displayInt($next_end, false) . "</small></a>";
						$html .= "
						</td>
					</tr>";
				}
				$html .= "
			</tbody>
		</table>";

		return $html;
	}

	if($type == "notifications"){
		$notifications = array_slice($data, $offset, $results_per_page, true);
		if(!$notifications) return getString("notifications_none");
		$html = "
		<table class=\"list notifications_list\">
			<thead>
				<tr>
					<th>" . getString("general_time_elapsed") . "</th>
					<th>" . getString("notifications_text") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_bottom, false) . " / " . displayInt($count, false) . "</small></th>
				</tr>
			</thead>
			<tbody>";
				foreach($notifications as $notification){
					$sent = $notification["sent"];
					$text = $notification["text"];

					// Example: DELETED:152,REFUNDED:50 -> [["DELETED", "152"], ["REFUNDED", "50"]]
					$text_parts = explode(",", $text);
					for($i = 0; $i < count($text_parts); $i++) $notification[$i] = explode(":", $text_parts[$i]);

					switch($notification[0][0]){
						case "DAILY": // "DAILY:123" or "DAILY:RESET"
							$notification_title = getString("notifications_daily");
							if($notification[0][1] == "RESET"){
								$notification_desc = getString("notifications_daily_reset");
							}else{
								$chips = $notification[0][1] + 9; // User receives 10 chips only when streak increments from 0 to 1. As the increment is done before the notification is sent, the difference is 9 and not 10.
								$notification_desc = getString("notifications_chips_won", ["<b>" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "</b>"]);
							}
							break;
						case "APPROVED": // "APPROVED:123"
							$prediction_id = $notification[0][1];
							$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
							$notification_title = getString("notifications_approved");
							$notification_desc = "<a href=\"prediction/$prediction_id\">$prediction_title</a>";
							break;
						case "REJECTED": // "REJECTED:123"
							$notification_title = getString("notifications_rejected");
							$notification_desc = getString("notifications_rejected_desc", ["<b>" . $notification[0][1] . "</b>"]);
							break;
						case "RESOLVED": // "RESOLVED:123,ANSWER:456,WON:789" or "RESOLVED:123,ANSWER:45,YOUR_ANSWER:67,LOST:89"
							$prediction_id = $notification[0][1];
							$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
							$outcome_id = $notification[1][1];
							$outcome_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$outcome_id], "string");
							if($notification[2][0] == "WON"){
								$selected_id = $outcome_id;
								$selected_title = $outcome_title;
								$chips = $notification[2][1];
								$chips_bet = executeQuery("SELECT `chips` FROM `bets` WHERE `user` = ? AND `choice` = ?;", [$_COOKIE["username"], $selected_id], "int");
								$chips_profit = $chips - $chips_bet;
								$chips_sentence = getString("notifications_chips_won", ["<b>" . displayInt($chips) . " (" . displayInt($chips_profit, true, true) . ")" . insertTextIcon("chips", "right", 1) . "</b>"]);
							}else{
								$selected_id = $notification[2][1];
								$selected_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$selected_id], "string");
								$chips = $notification[3][1];
								$chips_sentence = getString("notifications_chips_lost", ["<b>" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "</b>"]);
							}
							$notification_title = "<a href=\"prediction/$prediction_id\">$prediction_title</a>";
							$notification_desc =
								getString("notifications_resolved_selected") . " <b>$selected_title</b><br>" .
								getString("notifications_resolved_outcome") . " <b>$outcome_title</b><br>" .
								$chips_sentence;
							break;
						case "DELETED": // "DELETED:123,REFUNDED:456"
							$prediction_id = $notification[0][1];
							$chips = $notification[1][1];
							$notification_title = getString("notifications_deleted");
							$notification_desc =
								getString("notifications_deleted_desc", ["<b>" . $prediction_id . "</b>"]) . "<br>" .
								getString("notifications_chips_refunded", ["<b>" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "</b>"]);
							break;
						default:
							$notification_title = $text;
							$notification_desc = "";
							break;
					}
					$abbr_id = bin2hex(random_bytes(8));
					$sent_td = "<td><abbr id=\"$abbr_id\">" . $sent . "</abbr></td><script>display(\"$sent\",\"$abbr_id\")</script>";
					$html .= "
					<tr>
						$sent_td
						<td><b>$notification_title</b><br>$notification_desc</td>
					</tr>";
				}
				if($page_number >= 2 || $next_start <= $count){
					$html .= "
					<tr>
						<td class=\"previous\">";
							if($page_number >= 2) $html .= "<a href=\"$previous_page_url\">◄<br><small>" . displayInt($previous_start, false) . " – " . displayInt($previous_end, false) . "</small></a>";
						$html .= "
						</td>";
						for($i = 0; $i < count($columns) - 2; $i++){
							$html .= "<td></td>";
						}
						$html .= "
						<td class=\"next\">";
							if($next_start <= $count) $html .= "<a href=\"$next_page_url\">►<br><small>" . displayInt($next_start, false) . " – " . displayInt($next_end, false) . "</small></a>";
						$html .= "
						</td>
					</tr>";
				}
				$html .= "
			</tbody>
		</table>";

		return $html;
	}

	return "";
}
