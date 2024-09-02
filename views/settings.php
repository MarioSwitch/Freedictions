<?php
$language = getLanguage();

echo "
<h1>" . getString("settings_title") . "</h1>
<form role=\"form\" action=\"controller.php\">
    <p>
        <label for=\"language\">" . getString("settings_language") . "</label>
        <select name=\"language\" id=\"language\">
            <option value=\"fr\"" . ($language=="fr"?" selected":"") . ">" . getString("settings_language_fr") . "</option>
            <option value=\"en\"" . ($language=="en"?" selected":"") . ">" . getString("settings_language_en") . "</option>
        </select>
    </p>
    <br/>
    <button type=\"submit\" name=\"action\" value=\"settings\">" . getString("settings_apply") . "</button>
</form>";