<?php
$placeholder = "/\[TBR\]/";

/**
 * Get a string from a language file
 * @param string $key the key of the string
 * @param array $args the arguments to replace "[TBR]"
 * @return string the string
 */
function getString(string $key, array $args = []){
    $lang = getSetting("language");
    global $placeholder;
    if(!file_exists("strings/$lang.json")) return $key;
    $lang_strings = json_decode(file_get_contents("strings/$lang.json"), true);
    if(!array_key_exists($key, $lang_strings)) return $key;
    $string = $lang_strings[$key];
    foreach ($args as $value) {
        $string = preg_replace($placeholder, $value, $string, 1);
    }
    return $string;
}