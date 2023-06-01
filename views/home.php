<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "forbidden":
            echo "Vous ne pouvez pas effectuer cette action !";
            break;
        default:
            echo "Une erreur inconnue s'est produite.";
            break;
    }
    echo "</p>";
}
?>