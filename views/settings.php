<?php
$language = getSetting("language");
$sln = getSetting("sln");
$td = getSetting("td");

/* ATTENTION
    - Lors de l'ajout/modification de paramètres, penser à ajuster la fonction getSetting() du fichier functions.php
    - Lors de l'ajout/modification de paramètres, penser à ajuster le case 'settings' du fichier controller.php
    - Lors de l'ajout/modification d'options de paramètres, penser à ajuster le tableau $supported de la fonction getSetting() du fichier functions.php
    - Lors de l'ajout de langues, penser à ajouter le cas correspondant dans la fonction displayOrdinal() du fichier functions.php
    - Lors de la mise à jour d'un fichier de langue, préciser à quelle version le fichier a été mis à jour (ex. "Italien (2.1.34) / Espagnol (2.1.31)") et/ou relancer les principaux traducteurs de ces langues pour compléter les strings modifiées/ajoutées entre temps
*/

echo "
<h1>" . getString("settings_title") . "</h1>
<form role=\"form\" action=\"controller.php\">
    <table id=\"settings\">
        <tr>
            <td>
                <label for=\"language\"><abbr title=\"" . getString("settings_language_description") . "\">" . getString("settings_language") . "*</abbr></label>
            </td>
            <td>
                <select name=\"language\" id=\"language\">
                    <option value=\"fr\"" . ($language=="fr"?" selected":"") . ">" . getString("settings_language_fr") . " " . getString("settings_default") . "</option>
                    <option value=\"en\"" . ($language=="en"?" selected":"") . ">" . getString("settings_language_en") . " " . getString("settings_beta") . "</option>
                    <option value=\"de\"" . ($language=="de"?" selected":"") . ">" . getString("settings_language_de") . " " . getString("settings_beta") . "</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for=\"sln\"><abbr title=\"" . getString("settings_sln_description") . "\">" . getString("settings_sln") . "*</abbr></label>
            </td>
            <td>
                <select name=\"sln\" id=\"sln\">
                    <option value=\"yes\"" . ($sln=="yes"?" selected":"") . ">" . getString("settings_yes") . " " . getString("settings_default") . "</option>
                    <option value=\"no\"" . ($sln=="no"?" selected":"") . ">" . getString("settings_no") . "</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for=\"td\"><abbr title=\"" . getString("settings_td_description") . "\">" . getString("settings_td") . "*</abbr></label>
            </td>
            <td>
                <select name=\"td\" id=\"td\">
                    <option value=\"relative\"" . ($td=="relative"?" selected":"") . ">" . getString("settings_td_relative") . " " . getString("settings_default") . "</option>
                    <option value=\"local\"" . ($td=="local"?" selected":"") . ">" . getString("settings_td_local") . "</option>
                    <option value=\"utc\"" . ($td=="utc"?" selected":"") . ">" . getString("settings_td_utc") . "</option>
                </select>
            </td>
        </tr>
    </table>
    <br/>
    <button type=\"submit\" name=\"action\" value=\"settings\">" . getString("settings_apply") . "</button>
</form>";