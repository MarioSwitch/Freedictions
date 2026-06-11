<?php
/* REMINDER
	- When adding/editing settings, don't forget to update the getSetting() function in functions.php ($default and $supported)
	- When adding/editing settings, don't forget to update the "settings" case in controller.php ($_REQUEST and setcookie())
	- Whan adding a new language, don't forget to add the corresponding case in the displayRank() function in functions.php (switch(getSetting("language")))
*/

/**
 * Displays a language option
 * @param string $language_code Language code (e.g. "en" or "fr")
 * @return string Option inner text
 */
function displayLanguage(string $language_code): string{
	$language_names = [
		"en" => "English",
		"de" => "Deutsch",
		"fr" => "Français"
	];
	$language_flags = [
		"en" => "🌐",
		"de" => "🇩🇪",
		"fr" => "🇫🇷"
	];
	if(!array_key_exists($language_code, $language_names) || !array_key_exists($language_code, $language_flags)) return strtoupper($language_code);
	return $language_flags[$language_code] . " " . strtoupper($language_code) . " – " . $language_names[$language_code];
}

/**
 * Displays a setting select
 * @param string $setting Setting to display
 * @return void
 */
function displaySetting(string $setting){
	echo "<tr>
		<td>" . getString("settings_$setting") . "</td>
		<td><select name=\"$setting\">
	";
	$options = [];
	switch($setting){
		case "language":
			$options["en"] = displayLanguage("en");
			foreach(getSupportedLanguages() as $language){
				if($language != "en"){
					$options[$language] = displayLanguage($language);
				}
			}
			break;
		case "theme":
			$options = [
				"light" => getString("settings_theme_light"),
				"dark" => getString("settings_theme_dark"),
				"black" => getString("settings_theme_black")
			];
			break;
		case "shorten_large_numbers":
			$options = [
				"yes" => getString("general_yes"),
				"no" => getString("general_no")
			];
			break;
		default:
			$options = ["" => ""];
			break;
	}
	foreach($options as $value => $name){
		echo "<option value=\"$value\"" . (getSetting($setting) == $value ? " selected=\"selected\"" : "") . ">$name</option>";
	}
	echo "</select></td></tr>";
}
?>
<h1><?= getString("title_settings") ?></h1>
<form role="form" action="controller.php">
	<table class="hidden">
		<?php
		foreach(["language", "theme", "shorten_large_numbers"] as $setting){
			displaySetting($setting);
		}
		?>
	</table>
	<br><br>
	<button type="submit" name="action" value="settings"><?= getString("general_save") ?></button>
</form>