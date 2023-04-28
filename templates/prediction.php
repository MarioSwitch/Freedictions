<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
    header("Location:../index.php?view=accueil");
    die("");
}
include_once "libs/maLibSQL.pdo.php";
$prediExists = SQLGetChamp("SELECT COUNT(*) FROM predictions WHERE id=$_REQUEST[id];");
if ($prediExists)
{
    $now = date('Y-m-d')." ".date('H:i:s');
    $prediTitle = SQLGetChamp("SELECT title FROM predictions WHERE id=$_REQUEST[id];");
    $prediCreator = SQLGetChamp("SELECT author FROM predictions WHERE id=$_REQUEST[id];");
    $prediPseudo = SQLGetChamp("SELECT nickname FROM users JOIN predictions ON users.username = predictions.author WHERE predictions.id = $_REQUEST[id];");
    $prediCreated = SQLGetChamp("SELECT created FROM predictions WHERE id=$_REQUEST[id];");
    $prediCreatedDate = substr($prediCreated,0,10);
    $prediCreatedTime = substr($prediCreated,11,8);
    $prediEnd = SQLGetChamp("SELECT endDate FROM predictions WHERE id=$_REQUEST[id];");
    $prediEndDate = substr($prediEnd,0,10);
    $prediEndTime = substr($prediEnd,11,8);
    echo "<script src=\"./js/countdown.js\"></script>";
    echo "<script>countdownTo(\"" . $prediCreatedDate . "T" . $prediCreatedTime . "+02:00\", \"dans %countdown\", \"il y a %countup\", \"createdCountdown\");</script>";
    echo "<script>countdownTo(\"" . $prediEndDate . "T" . $prediEndTime . "+02:00\", \"Se termine dans %countdown\", \"Terminé depuis %countup\", \"endCountdown\");</script>";
    $prediAnswer = SQLGetChamp("SELECT correctAnswer FROM predictions WHERE id=$_REQUEST[id];");
    if ($prediAnswer != NULL)
    {
        $prediAnswerTitle = SQLGetChamp("SELECT choice FROM predictionsChoices WHERE id=$prediAnswer;");
    }
    $prediNumberOfAnswers = SQLGetChamp("SELECT COUNT(*) FROM predictionsChoices WHERE prediction=$_REQUEST[id];");
    $prediChoices = parcoursRs(SQLSelect("SELECT id,choice FROM predictionsChoices WHERE prediction=$_REQUEST[id];"));
    $svgVotants = "<abbr title=\"Nombre de votes\"><img width=\"32px\" height=\"32px\" src=\"./ressources/svg/persons.svg\" style=\"filter: invert(1);\"></abbr>";
    $svgPoints = "<abbr title=\"Points dépensés\"><img width=\"32px\" style=\"filter: invert(1);\" height=\"32px\" src=\"./ressources/svg/points.svg\"></abbr>";
    $svgWin = "<abbr title=\"Rendement (si vous gagnez, vous gagnerez votre mise multipliée par ce nombre)\"><img width=\"32px\" style=\"filter: invert(1);\" height=\"32px\" src=\"./ressources/svg/win.svg\">";
    $svgRecord = "<abbr title=\"Record de mise\"><img width=\"32px\" style=\"filter: invert(1);\" height=\"32px\" src=\"./ressources/svg/podium.svg\"></abbr>";
    $prediChoicesText = "<table class='table'><tr><th>Choix</th><th>Répartition</th><th>" . $svgVotants . "</th><th>" . $svgPoints . "</th><th>" . $svgWin . "</th><th>" . $svgRecord . "</th></tr>";
    $count = 0;
    foreach ($prediChoices as $uneReponsePossible)
    {
        $prediChoicesText = $prediChoicesText . "<tr>";
        foreach ($uneReponsePossible as $uneColonne)
        {
            $count++;
            if ($count % 2 == 1)
            {
                $numeroChoix = $uneColonne;
                if (SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id];") != 0)
                {
                    $pourcentage = round(SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id] AND choice=$numeroChoix;") / SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id];") * 100, 1);
                } else
                {
                    $pourcentage = "-";
                }
                $nombreVotants = SQLGetChamp("SELECT COUNT(*) FROM usersChoices WHERE prediction=$_REQUEST[id] AND choice=$numeroChoix;");
                $pointsDepenses = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id] AND choice=$numeroChoix;");
                $pointsTotal = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id];");
                if ($pourcentage != 0 && $pourcentage != "-")
                {
                    $tauxVictoire = round($pointsTotal / $pointsDepenses, 2);
                } else
                {
                    $tauxVictoire = "";
                }
                $recordMise = SQLGetChamp("SELECT MAX(pointsSpent) FROM usersChoices WHERE prediction=$_REQUEST[id] AND choice=$numeroChoix;");
            }
            if ($count % 2 == 0)
            {
                $intituleChoix = $uneColonne;
                $prediChoicesText = $prediChoicesText . "<td>" . $intituleChoix . "</td><td>" . $pourcentage . " %</td><td>" . $nombreVotants . "</td><td>" . $pointsDepenses . "</td><td>" . $tauxVictoire . "</td><td>" . $recordMise . "</td>";
            }
        }
        $prediChoicesText = $prediChoicesText . "</tr>";
    }
    $prediChoicesText = $prediChoicesText . "</table><p class='text2'>Au total, <b>" . SQLGetChamp("SELECT COUNT(*) FROM usersChoices WHERE prediction=$_REQUEST[id];") . "</b> personnes ont parié sur cette prédiction pour un total de <b>" . $pointsTotal . "</b> points.</p>";
    if (!isset($_SESSION["connecte"]))
    {
        $mode = "disconnected";
    } elseif (SQLGetChamp("SELECT isAdmin FROM users WHERE username='$_SESSION[user]';") == 1)
    {
        $mode = "admin";
    } elseif ($prediCreator == $_SESSION["user"])
    {
        $mode = "creator";
    } elseif (SQLGetChamp("SELECT COUNT(*) FROM usersChoices WHERE username='$_SESSION[user]' AND prediction=$_REQUEST[id];") == 1)
    {
        $mode = "alreadyVoted";
    } elseif ($prediEnd < $now)
    {
        $mode = "waitingAnswer";
    } else
    {
        $mode = "normal";
    }
    $prediChoicesID = parcoursRs(SQLSelect("SELECT id,choice FROM predictionsChoices WHERE prediction=$_REQUEST[id];"));
    $menuDeroulant = "<select class='wrap-menu' name=\"choice\">";
    $count = 0;
    foreach ($prediChoicesID as $uneLigne)
    {
        foreach ($uneLigne as $uneColonne)
        {
            $count++;
            if ($count % 2 == 1)
            {
                $choiceID = $uneColonne;
            }
            if ($count % 2 == 0)
            {
                $menuDeroulant = $menuDeroulant . "<option class='wrap-option' value=" . $choiceID . ">" . $uneColonne . "</option>";
            }
        }
    }
    $menuDeroulant = $menuDeroulant . "</select>";
    $pointsMax = SQLGetChamp("SELECT points FROM users WHERE username='$_SESSION[user]';");
    echo("
    <h1 class='title'>" . $prediTitle . " </h1>
    <p class=\"text2\">
        Créé par " . $prediPseudo . " <span id=\"createdCountdown\"></span>
    </p>
    <p class=\"text2\" id=\"endCountdown\"></p>
	<h2 class='title-h2'>" . $prediNumberOfAnswers . " réponses possibles</h2>
	" . $prediChoicesText . "
	<hr class=\"line\">
	<h3 class='title-h3'>Parier</h3>
	");
    if($mode == "disconnected"){
        echo("<p class='text2'>Vous devez être connecté pour pouvoir parier !</p>");
    }elseif ($mode == "admin")
    {
        echo("<p class='text2'>Vous ne pouvez parier sur aucune prédiction car vous êtes un administrateur du site.</p>");
    } elseif ($mode == "creator")
    {
        echo("<p class='text2'>Vous ne pouvez pas parier sur cette prédiction car vous en êtes le créateur.</p>");
    } elseif ($mode == "alreadyVoted")
    {
        $choice = SQLGetChamp("SELECT predictionsChoices.choice FROM predictionsChoices JOIN usersChoices ON predictionsChoices.id = usersChoices.choice WHERE usersChoices.username='$_SESSION[user]' AND usersChoices.prediction=$_REQUEST[id];");
        $pointsSpent = SQLGetChamp("SELECT pointsSpent FROM usersChoices WHERE username='$_SESSION[user]' AND prediction=$_REQUEST[id];");
        echo("<p class='text2'>Vous avez parié sur <b>" . $choice . "</b> avec <b>" . $pointsSpent . "</b> points.</p>");
    } elseif ($mode == "waitingAnswer") {
        echo("<p class='text2'>Les votes sont terminés.</p>");
    } elseif ($mode == "normal") {
        echo("<form role=\"form\" action=\"controleur.php\"><input type=\"hidden\" name=\"prediction\" value=\"" . $_REQUEST["id"] . "\"><p class='text2'>Parier sur " . $menuDeroulant . " avec <input class='points-input' type=\"number\" name=\"points\" min=\"1\" max=\"" . $pointsMax . "\"> points </p><button class='button' type=\"submit\" name=\"action\" value=\"Parier\">Parier</button></form>");
    }
    if ($mode == "admin" || $mode == "creator")
    {
        if ($prediAnswer == NULL)
        {
            echo("<hr class=\"line\"><h3 class='title-h3'>Gérer la prédiction</h3>");
            if ($prediEnd < $now) 
			{
                echo("<form class='row' role=\"form\" action=\"controleur.php\"><input type=\"hidden\" name=\"prediction\" value=\"" . $_REQUEST["id"] . "\"><p class='text2'>Définir " . $menuDeroulant . " comme étant la bonne réponse </p><button class='button' type=\"submit\" name=\"action\" value=\"ValiderPrediction\">Terminer la prédiction et redistribuer les points</button></form>");
			} else {
				echo("<p class='text2'>Vous devez attendre la fin des votes pour donner la bonne réponse !</p>");
			}
        echo("<form role=\"form\" action=\"controleur.php\"><input type=\"hidden\" name=\"prediction\" value=\"" . $_REQUEST["id"] . "\"><button class='button' type=\"submit\" name=\"action\" value=\"SupprimerPrediction\">Supprimer la prédiction et rendre les points</button></form>");
		}
	}
    if ($prediAnswer != NULL)
    {
        echo("<h3 class='title-h3'><b>" . $prediAnswerTitle . "</b> était la bonne réponse. Les points ont été redistribués !</h3>");
    }
} else
{
    echo("<h1 class='title'>Cette prédiction n'existe pas !</h1><p class=\"text\">Si vous avez parié sur cette prédiction auparavant, elle a été supprimée par son créateur (ou par un administrateur) et vous avez récupéré les points misés !</p>");
}
?>
