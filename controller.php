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

    case 'changePassword' :
        if(!(userConnected() && $_REQUEST["username"] && $_REQUEST["password"] && $_REQUEST["newpassword"] && $_REQUEST["newpasswordconfirmation"])){
            header("Location:index.php?view=changePassword&error=data");
            die("");
        }
        changePassword($_REQUEST["username"], $_REQUEST["password"], $_REQUEST["newpassword"], $_REQUEST["newpasswordconfirmation"]);
    break;

    case 'deleteAccount' :
        if(!(userConnected() && $_REQUEST["username"] && $_REQUEST["password"])){
            header("Location:index.php?view=deleteAccount&error=data");
            die("");
        }
        deleteAccount($_REQUEST["username"], $_REQUEST["password"]);
    break;

    case 'search':
        $addArgs = "?view=search&query=" . $_REQUEST["search"];
    break;
}

header("Location:index.php" . $addArgs);

//ob_end_flush();
?>