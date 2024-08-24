<?php 
function getLanguage(){
    return "fr";
}

$placeholder = "[TBR]";

function getString(string $key, array $args = []){
    $lang = getLanguage();
    global $placeholder;
    $lang_strings = json_decode(file_get_contents("strings/$lang.json"), true);
    $string = $lang_strings[$key];
    if(preg_match($placeholder, $string)){
        foreach($args as $key => $value){
            $string = str_replace($placeholder, $value, $string);
        }
    }
    return $string;
}
?>