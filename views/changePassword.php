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
        case "old_password":
            echo getString("error_password_old");
            break;
        case "password":
            echo getString("error_password_mismatch");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "<br>" . getString("error_try_again") . "</p>";
}
echo "
<h1>" . getString("changePassword_title", [displayUsername($_COOKIE["username"])]) . "</h1>
<form role='form' action='controller.php'>
    <input type='hidden' name='username' value='" . $_COOKIE["username"] . "'>
    <label for='password'>" . getString("changePassword_password_current") . "</label>
    <input type='password' id='password' name='password' required='required'>
    <br/>
    <label for='newpassword'>" . getString("changePassword_password_new") . "</label>
    <input type='password' name='newpassword' required='required'>
    <br/>
    <label for='newpasswordconfirmation'>" . getString("changePassword_password_confirm") . "</label>
    <input type='password' name='newpasswordconfirmation' required='required'>
    <button type='submit' name='action' value='changePassword'>" . getString("changePassword_confirm") . "</button>
</form>";