<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php?view=accueil");
    die("");
}
include_once "libs/maLibSQL.pdo.php";

$now = SQLGetChamp("SELECT NOW();");
$displayname = SQLGetChamp("SELECT nickname FROM users WHERE username='$_SESSION[user]';");
$points = SQLGetChamp("SELECT points FROM users WHERE username='$_SESSION[user]';");
$predictionsCreatedText = "";
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$_SESSION[user]' AND NOW()<endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>En cours</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$_SESSION[user]' AND NOW()>endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>En attente de réponse</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$_SESSION[user]' AND correctAnswer IS NOT NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3>Terminées</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsParticipatedText = "";
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$_SESSION[user]' AND NOW()<endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En cours</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . " : Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . $uneColonne . "</b> points<br>";
        }
    }
}
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$_SESSION[user]' AND NOW()>endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>En attente de réponse</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . " : Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . $uneColonne . "</b> points<br>";
        }
    }
}
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$_SESSION[user]' AND correctAnswer IS NOT NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3>Terminées</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "https://www.yoshi-web-store.com/predictions/index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . " : Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . $uneColonne . "</b> points<br>";
        }
    }
}

echo("
    <div class=\"page-header\">
      <h1 class=\"title\">Mon profil (" . $displayname . ")</h1>
    </div>

    <p class=\"lead\">
      
      <b class=\"text\">" . $points . " points</b>

    </p>
	<h2 class=\"title-h2\">Prédictions créées</h2>
	<p class=\"text\">" . $predictionsCreatedText . "</p>
	<h2 class=\"title-h2\">Prédictions auxquelles j'ai participé</h2>
	<p class=\"text\">" . $predictionsParticipatedText . "</p>
");
?>