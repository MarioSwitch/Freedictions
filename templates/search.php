<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php?view=accueil");
    die("");
}
include_once "libs/maLibSQL.pdo.php";

// fonction qui recherche le title d'une prédiction, non sensible à la casse.
// exemple : rechercher "c" affichera les titres "abcd" et "combien de..." s'ils existent bien sur
function Rechercher($recherche)
{
    $all_predi = SQLSelect("SELECT title, id FROM predictions;");
    foreach ($all_predi as $ligne) {
        // echo "<p class='text2' style='color: aqua'>" . $ligne["title"] . $ligne["id"] . "</p>";
        $str = $ligne["title"];
        for ($i = 0; $i < strlen($str); $i++) {
            if ($str[$i] == strtolower($recherche[0]) || $str[$i] == strtoupper($recherche[0])) {
                $same = true;
                $k = 0;
                for ($j = $i; $j - $i < strlen($recherche); $j++,$k++) {
                    if ($str[$j] != strtolower($recherche[$k]) && $str[$j] != strtoupper($recherche[$k])) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    echo "<a class=\"a-text\" href=\"index.php?view=prediction&id=". $ligne["id"] . "\">" . $ligne["title"] . "</a>";
                }
            } // fin du if
        }
    }
}
?>
<h1 class='title'>Rechercher</h1>


<?php
echo "<h2 class='title-h2'>Résultats de recherche pour " . htmlspecialchars($_GET['q']) . "</h2>"; // prevents XSS injection
Rechercher(htmlspecialchars($_GET['q']));
?>

