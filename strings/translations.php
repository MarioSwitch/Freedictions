<?php 
function getLanguage(){
    return "en";
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