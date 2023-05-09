<?php
if (basename($_SERVER["PHP_SELF"]) != "index.php") {
    header("Location:../index.php?view=accueil");
    die("");
}
include_once "libs/maLibSQL.pdo.php";

$now = SQLGetChamp("SELECT NOW();");
$user = $_REQUEST["user"];
$displayname = SQLGetChamp("SELECT nickname FROM users WHERE username='$user';");
$points = SQLGetChamp("SELECT points FROM users WHERE username='$user';");
$rank = SQLGetChamp("SELECT COUNT(*) FROM users WHERE points > " . $points) + 1;
$accounts = SQLGetChamp("SELECT COUNT(*) FROM users");
$top = number_format(($rank / $accounts)*100, 2, ',', '');
$statsPointsSpent = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE username='$user';");
$statsTotalBets = SQLGetChamp("SELECT COUNT(*) FROM usersChoices WHERE username='$user';");
$statsTotalCreated = SQLGetChamp("SELECT COUNT(*) FROM predictions WHERE author='$user';");
$predictionsCreatedText = "";
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$user' AND '$now'<endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3 class='title-h3'>En cours</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$user' AND '$now'>endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3 class='title-h3'>En attente de réponse</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsCreated = parcoursRs(SQLSelect("SELECT id,title FROM predictions WHERE author='$user' AND correctAnswer IS NOT NULL;"));
$count = 0;
$predictionsCreatedText = $predictionsCreatedText . "<h3 class='title-h3'>Terminées</h3>";
foreach ($predictionsCreated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 2 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 2 == 0) {
            $predictionsCreatedText = $predictionsCreatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a><br>";
        }
    }
}
$predictionsParticipatedText = "";
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$user' AND '$now'<endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3 class='title-h3'>En cours</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<p class='text2'>Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . number_format($uneColonne, 0, '', ' ') . "</b> points</p><br>";
        }

    }
}
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$user' AND '$now'>endDate AND correctAnswer IS NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3 class='title-h3'>En attente de réponse</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<p class='text2'>Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . $uneColonne . "</b> points</p><br>";
        }
    }
}
$predictionsParticipated = parcoursRs(SQLSelect("SELECT predictions.id,predictions.title,predictionsChoices.choice,usersChoices.pointsSpent FROM predictions JOIN predictionsChoices ON predictionsChoices.prediction = predictions.id JOIN usersChoices ON usersChoices.choice = predictionsChoices.id WHERE usersChoices.username='$user' AND correctAnswer IS NOT NULL;"));
$count = 0;
$predictionsParticipatedText = $predictionsParticipatedText . "<h3 class='title-h3'>Terminées</h3>";
foreach ($predictionsParticipated as $uneLigne) {
    foreach ($uneLigne as $uneColonne) {
        $count++;
        if ($count % 4 == 1) {
            $lien = "index.php?view=prediction&id=" . $uneColonne;
        }
        if ($count % 4 == 2) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<a class='a-text' href=\"$lien\">" . $uneColonne . "</a>";
        }
        if ($count % 4 == 3) {
            $predictionsParticipatedText = $predictionsParticipatedText . "<p class='text2'>Parié sur <b>" . $uneColonne;
        }
        if ($count % 4 == 0) {
            $predictionsParticipatedText = $predictionsParticipatedText . "</b> avec <b>" . $uneColonne . "</b> points</p><br>";
        }
    }
}

echo("
    <h1 class='title'>" . $displayname . "</h1>
    <p class='text'>" . number_format($points, 0, '', ' ') . " points</p>
    <p class='text'>Classé " . $rank . "<sup>e</sup> sur " . $accounts . " (dans le top " . $top . " %)</p>
    <hr class='line'>
	<h2 class='category-h2'>Statistiques</h2>
	<p class='text'>Misé <b>" . $statsTotalBets . " </b> fois pour un total de <b>" . number_format($statsPointsSpent, 0, '', ' ') . "</b> points.</p>
	<p class='text'><b>" . $statsTotalCreated . "</b> prédictions créées</p>
    <hr class='line'>
	<h2 class='category-h2'>Prédictions créées</h2>
	<p class='text'>" . $predictionsCreatedText . "</p>
    <hr class='line'>
	<h2 class='category-h2'>Participations à des prédictions</h2>
	<p class='text'>" . $predictionsParticipatedText . "</p>
");
if($user == $_SESSION["user"]){
    echo("
        <hr class='line'>
        <h2 class='category-h2'>Gérer le compte</h2>
	<form class='row' role='form' action='controleur.php'>
            <input type='hidden' name='username' value='" . $_SESSION["user"] . "'>
            <input type='password' name='password' class='signup-input' placeholder='Confirmez votre mot de passe'>
            <button class='button' type='submit' name='action' value='SupprimerCompte'>Confirmer la suppression du compte</button>
        </form>
    ");
}
?>
