<?php
session_start();

include_once("sql.php");

$addArgs = "";

//ob_start();
switch($_REQUEST["action"]){
    case 'createAccount' :
        if(!($_REQUEST["username"] && $_REQUEST["password"] && $_REQUEST["passwordconfirmation"])){
            header("Location:index.php?view=signup&error=data");
            die("");
        }
        createAccount($_REQUEST["username"],$_REQUEST["password"],$_REQUEST["passwordconfirmation"]);
    break;

    case 'login' :
        login($_REQUEST["username"],$_REQUEST["password"]);
    break;

    case 'logout' :
        session_destroy();
    break;
}

header("Location:index.php" . $addArgs);

//ob_end_flush();
?>