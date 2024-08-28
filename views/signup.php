<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "data":
            echo getString("error_data");
            break;
        case "username_invalid":
            echo getString("error_username_invalid");
            break;
        case "username_taken":
            echo getString("error_username_taken");
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
<h1>" . getString("signup_title") . "</h1>
<form role=\"form\" action=\"controller.php\">
    <label for=\"username\">" . getString("username") . "</label>
    <input type=\"text\" id=\"username\" name=\"username\" required=\"required\" pattern=\"[A-Za-z0-9]{4,20}\" title=\"" . getString("signup_username_format") . "\">
    <br/>
    <label for=\"password\">" . getString("password") . "</label>
    <input type=\"password\" id=\"password\" name=\"password\" required=\"required\">
    <br/>
    <label for=\"passwordconfirmation\">" . getString("signup_password_confirm") . "</label>
    <input type=\"password\" id=\"passwordconfirmation\" name=\"passwordconfirmation\" required=\"required\">
    <br/>
    <button type=\"submit\" name=\"action\" value=\"createAccount\">" . getString("signup_confirm") . "</button>
</form>";