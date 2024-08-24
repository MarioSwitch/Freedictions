<?php
if(!isConnected()){
    header("Location:index.php?view=home");
    die("");
}
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo getString("error_data");
            break;
        case "password":
            echo getString("error_password");
            break;
        case "forbidden":
            echo getString("error_forbidden_delete_account");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "<br>" . getString("error_try_again") . "</p>";
}

if($_COOKIE["username"] == $_REQUEST["user"]){
    echo "
    <h1>" . getString("deleteAccount_title", [displayUsername($_REQUEST["user"])]) . "</h1>
        <form role='form' action='controller.php'>
        <input type='hidden' name='username' value='" . $_REQUEST["user"] . "'>
        <label for='password'>" . getString("password") . "</label>
        <input type='password' name='password' required='required'>
        <br/>
        <p>" . getString("deleteAccount_info1") . "<br>" . getString("deleteAccount_info2") . "</p>
        <button type='submit' name='action' value='deleteAccount'>" . getString("deleteAccount_confirm") . "</button>
    </form>";
}else if(isMod()){
    echo "
    <h1>" . getString("deleteAccount_title_mod", [displayUsername($_REQUEST["user"])]) . "</h1>
        <form role='form' action='controller.php'>
        <input type='hidden' name='username' value='" . $_REQUEST["user"] . "'>
        <label for='password'>" . getString("password") . "</label>
        <input type='password' name='password' required='required'>
        <br/>
        <p>" . getString("deleteAccount_info_mod") . "</p>
        <button type='submit' name='action' value='deleteAccount'>" . getString("deleteAccount_confirm") . "</button>
    </form>";
}else{
    header("Location:index.php?view=home");
    die("");
}