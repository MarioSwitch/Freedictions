<?php 
function getLanguage(){
    $default_language = "fr";
    $supported_languages = ["fr", "en"];
    if (array_key_exists("language", $_COOKIE)){
        $language = $_COOKIE["language"];
        return in_array($language, $supported_languages) ? $language : $default_language;
    }else{
        setcookie("language", $default_language, time() + 30*24*60*60); // 30 days
        return $default_language;
    }
}

$placeholder = "/\[TBR\]/";

/**
 * Get a string from a language file
 * @param string $key the key of the string
 * @param array $args the arguments to replace "[TBR]"
 * @return string the string
 */
function getString(string $key, array $args = []){
    $lang = getLanguage();
    global $placeholder;
    $lang_strings = json_decode(file_get_contents("strings/$lang.json"), true);
    $string = $lang_strings[$key];
    foreach ($args as $value) {
        $string = preg_replace($placeholder, $value, $string, 1);
    }
    return $string;
}