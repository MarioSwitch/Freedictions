<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "credentials":
            echo getString("error_credentials");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "<br>" . getString("error_try_again") . "</p>";
}
echo "
<h1 class=\"title\">" . getString("signin_title") . "</h1>
<form role=\"form\" action=\"controller.php\">
    <label for=\"username\">" . getString("username") . "</label>
    <input type=\"text\" id=\"username\" name=\"username\" required=\"required\">
    <br/>
    <label for=\"password\">" . getString("password") . "</label>
    <input type=\"password\" id=\"password\" name=\"password\" required=\"required\">
    <br/>
    <button type=\"submit\" name=\"action\" value=\"login\">" . getString("signin") . "</button>
</form>";