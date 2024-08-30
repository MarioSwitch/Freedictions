<?php
include_once "functions.php";

$points = isConnected()?intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]):0;

$args = "";

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
        logout();
    break;

    case 'changePassword' :
        if(!(isConnected() && $_REQUEST["username"] && $_REQUEST["password"] && $_REQUEST["newpassword"] && $_REQUEST["newpasswordconfirmation"])){
            header("Location:index.php?view=changePassword&error=data");
            die("");
        }
        changePassword($_REQUEST["username"], $_REQUEST["password"], $_REQUEST["newpassword"], $_REQUEST["newpasswordconfirmation"]);
    break;

    case 'deleteAccount' :
        if(!(isConnected() && $_REQUEST["username"] && $_REQUEST["password"])){
            header("Location:index.php?view=deleteAccount&error=data");
            die("");
        }
        deleteAccount($_REQUEST["username"], $_REQUEST["password"]);
    break;

    case 'createPrediction' :
        if(!(isConnected() && $_REQUEST["name"] && $_REQUEST["end"] && $_REQUEST["offset"] && $_REQUEST["choices"])){
            header("Location:index.php?view=createPrediction&error=data");
            die("");
        }
        $correctedName = $_REQUEST["name"];
        $correctedName = preg_replace("/</", "&lt;", $correctedName);
        $correctedName = preg_replace("/>/", "&gt;", $correctedName);
        $correctedChoices = $_REQUEST["choices"];
        $correctedChoices = preg_replace("/</", "&lt;", $correctedChoices);
        $correctedChoices = preg_replace("/>/", "&gt;", $correctedChoices);
        $args = "?view=prediction&id=" . createPrediction($correctedName,$_COOKIE["username"],$_REQUEST["end"],$_REQUEST["offset"],$correctedChoices);
    break;

    case 'vote' :
        if(!(isConnected() && $_REQUEST["prediction"] && $_REQUEST["choice"] && $_REQUEST["points"] > 0)){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=vote");
            die("");
        }
        if($points < $_REQUEST["points"]){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=points");
            die("");
        }
        $args = "?view=prediction&id=" . vote($_COOKIE["username"],$_REQUEST["prediction"],$_REQUEST["choice"],$_REQUEST["points"]);
    break;

    case 'addPoints' :
        if(!(isConnected() && $_REQUEST["prediction"] && $_REQUEST["points"] > 0)){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=vote");
            die("");
        }
        if($points < $_REQUEST["points"]){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=points");
            die("");
        }
        $args = "?view=prediction&id=" . addPoints($_COOKIE["username"],$_REQUEST["prediction"],$_REQUEST["points"]);
    break;

    case 'answer' :
        if(!(isConnected() && $_REQUEST["prediction"] && $_REQUEST["choice"])){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=answer");
            die("");
        }
        $args = "?view=prediction&id=" . answer($_REQUEST["prediction"],$_REQUEST["choice"]);
    break;

    case 'deletePrediction' :
        if(!(isConnected() && $_REQUEST["prediction"])){
            header("Location:index.php?view=prediction&id=" . $_REQUEST["prediction"] . "&error=delete");
            die("");
        }
        deletePrediction($_REQUEST["prediction"]);
    break;

    case 'search':
        $args = "?view=search&query=" . $_REQUEST["search"];
    break;
}

header("Location:index.php" . $args);