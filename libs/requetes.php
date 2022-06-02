<?php

include_once "maLibSQL.pdo.php";

function creerCompte($username, $displayname, $hash)
{
    $SQL = "INSERT INTO users VALUES ('$username','$displayname','$hash',NULL,100,false,NOW())";
    SQLInsert($SQL);
    $_SESSION["user"] = $username;
    $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
    $_SESSION["connecte"] = true;
    $_SESSION["heureConnexion"] = date("H:i:s");
}

function seConnecter($username, $password)
{
    $SQL = "SELECT hash_pwd FROM users WHERE username='$username';";
    $hash_saved = SQLGetChamp($SQL);
    if (password_verify($password, $hash_saved)) {
        $_SESSION["user"] = $username;
        $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
        $_SESSION["connecte"] = true;
        $_SESSION["heureConnexion"] = date("H:i:s");
        $SQL = "UPDATE users SET lastConnection = NOW() WHERE username = '$username';";
        SQLUpdate($SQL);
    }
}

function supprimerCompte($username, $password)
{
    $SQL = "SELECT hash_pwd FROM users WHERE username='$username';";
    $hash_saved = SQLGetChamp($SQL);
    if (password_verify($password, $hash_saved)) {
        $SQL = "DELETE FROM users WHERE username='$username';";
        SQLDelete($SQL);
        //Ne pas oublier de supprimer toutes les prédictions créées par cet utilisateur ainsi que ses paris
        //Ne pas oublier de supprimer les données de session (le déconnecter)
    }
}

function creerPrediction($name, $user, $endDate, $choix)
{//La variable choix sera un tableau créé pour l'occasion (format : "choix1", "choix2", "choix3", etc.)
    $SQL = "INSERT INTO predictions VALUES (DEFAULT,'$name','$user',DEFAULT,'$endDate',NULL);";
    SQLInsert($SQL);
    $predictionID = SQLGetChamp("SELECT id FROM predictions WHERE title = '$name' AND author = '$user' AND endDate = '$endDate';");
    foreach ($choix as $unChoix) {
        $SQL = "INSERT INTO predictionsChoices VALUES (DEFAULT, $predictionID, '$unChoix');";
        SQLInsert($SQL);
    }
    return $predictionID;
}

function parier($user, $prediction, $choice, $points)
{
    $SQL = "UPDATE users SET points = points - $points WHERE username = '$user';";
    SQLUpdate($SQL);
    $SQL = "INSERT INTO usersChoices VALUES ('$user',$prediction,$choice,$points);";
    SQLInsert($SQL);
    return $prediction;
}

function donnerReponsePrediction($prediction, $answer)
{
    $author = SQLGetChamp("SELECT author FROM predictions WHERE id='$prediction';");
    $userConnected = $_SESSION["user"];
    $admin = SQLGetChamp("SELECT isAdmin FROM users WHERE username='$userConnected';");
    if ($author == $userConnected || $admin) {
        SQLUpdate("UPDATE predictions SET correctAnswer = $answer WHERE id = $prediction;");
        $totalPoints = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$prediction;");
        $winPoints = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$prediction AND choice=$answer;");
        if ($winPoints != 0) {
            $winRate = $totalPoints / $winPoints;
            $tableauDesGagnants = parcoursRs(SQLSelect("SELECT username,pointsSpent FROM usersChoices WHERE prediction='$prediction' AND choice=$answer;"));
            $count = 0;
            foreach ($tableauDesGagnants as $uneLigne) {
                foreach ($uneLigne as $uneColonne) {
                    $count++;
                    if ($count % 2 == 1) {
                        $utilisateurAPayer = $uneColonne;
                    }
                    if ($count % 2 == 0) {
                        $pointsMises = $uneColonne;
                        $pointsAPayer = floor($pointsMises * $winRate);
                        SQLUpdate("UPDATE users SET points = points + $pointsAPayer WHERE username='$utilisateurAPayer';");
                    }
                }
            }
        }
    }
    return $prediction;
}

function supprimerPrediction($prediction)
{
    $author = SQLGetChamp("SELECT author FROM predictions WHERE id='$prediction';");
    $userConnected = $_SESSION["user"];
    $admin = SQLGetChamp("SELECT isAdmin FROM users WHERE username='$userConnected';");
    if ($author == $userConnected || $admin) {
        $lesChoixDesUtilisateurs = parcoursRs(SQLSelect("SELECT username,pointsSpent FROM usersChoices WHERE prediction='$prediction';"));
        $count = 0;
        foreach ($lesChoixDesUtilisateurs as $uneLigne) {
            foreach ($uneLigne as $uneColonne) {
                $count++;
                if ($count % 2 == 1) {
                    $utilisateurARembourser = $uneColonne;
                }
                if ($count % 2 == 0) {
                    $pointsARembourser = $uneColonne;
                    SQLUpdate("UPDATE users SET points = points + $pointsARembourser WHERE username='$utilisateurARembourser'");
                }
            }
        }
        SQLDelete("DELETE FROM predictionsChoices WHERE prediction='$prediction';");
        SQLDelete("DELETE FROM predictions WHERE id='$prediction';");
    }
}

?>