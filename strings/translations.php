<?php 
function getLanguage(){
    return "fr";
}

$placeholder = "/\[TBR\]/";

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