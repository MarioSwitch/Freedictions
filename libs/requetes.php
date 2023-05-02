<?php

include_once "maLibSQL.pdo.php";
$now = SQLGetChamp("SELECT NOW();");

function creerCompte($username,$displayname,$hash){
    global $now;
    $SQL = "INSERT INTO users VALUES ('$username','$displayname','$hash',NULL,100,false,'$now')";
    SQLInsert($SQL);
    $_SESSION["user"] = $username;
    $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
	$_SESSION["connecte"] = true;
	$_SESSION["heureConnexion"] = date("H:i:s");
}

function seConnecter($username,$password){
    global $now;
    $SQL="SELECT hash_pwd FROM users WHERE username='$username';";
    $hash_saved=SQLGetChamp($SQL);
    if(password_verify($password,$hash_saved)){
        $_SESSION["user"] = $username;
        $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
	    $_SESSION["connecte"] = true;
	    $_SESSION["heureConnexion"] = date("H:i:s");
        $SQL="UPDATE users SET lastConnection = '$now' WHERE username = '$username';";
        SQLUpdate($SQL);
    }
}

function supprimerCompte($username, $password){
    global $now;
    $SQL="SELECT hash_pwd FROM users WHERE username='$username';";
    $hash_saved=SQLGetChamp($SQL);
    if(password_verify($password,$hash_saved)){
        $SQL="DELETE FROM users WHERE username='$username';";
        SQLDelete($SQL);
        //Ne pas oublier de supprimer toutes les prédictions créées par cet utilisateur ainsi que ses paris
        //Ne pas oublier de supprimer les données de session (le déconnecter)
    }
}
// petite fonction pour autoriser les caractères spéciaux à être dans une string en les rendant normalisés en rajoutant des backslashes devant (marche pas pour < et >, il faut les remplacer par &lt et &gt)
function html_special_chars($str) {
    $invalid_characters = array("'", '"', '/', '&', '\\'); // "$", "%", "#", "|", '\'', "\"", "\\");
    $str2 = "";
    for ($i = 0; $i < strlen($str); $i++)
    {
        $done = false;
        for($j = 0; $j < count($invalid_characters); $j++)
        {
            if($str[$i] == "<")
            {
                $done = true;
                $str2 .= "&lt";
                break;
            }
            if($str[$i] == ">")
            {
                $done = true;
                $str2 .= "&gt";
                break;
            }

            if($str[$i] == $invalid_characters[$j])
            {
                $done = true;
                $str2 .= "\\$str[$i]";
                break;
            }
        }
        if(!$done)
        {
            $str2 .= $str[$i];
        }
    }
    return $str2;
}

function creerPrediction($name,$user,$endDate,$offset,$choix){//La variable choix sera un tableau créé pour l'occasion (format : "choix1", "choix2", "choix3", etc.)
    global $now;
    $name = html_special_chars($name);
    date_default_timezone_set('UTC');
    $endDateUTC = date('Y-m-d\TH:i',strtotime($endDate)-($offset*60));
    $SQL = "INSERT INTO predictions VALUES (DEFAULT,'$name','$user','$now','$endDateUTC',NULL);";
    SQLInsert($SQL);
    $predictionID = SQLGetChamp("SELECT id FROM predictions WHERE title = '$name' AND author = '$user' AND endDate = '$endDateUTC';");
    foreach($choix as $unChoix){
        $unChoix = html_special_chars($unChoix);
        $SQL = "INSERT INTO predictionsChoices VALUES (DEFAULT, $predictionID, '$unChoix');";
        SQLInsert($SQL);
    }
    return $predictionID;
}

function parier($user,$prediction,$choice,$points){
    global $now;
    $end = SQLGetChamp("SELECT endDate FROM predictions WHERE id='$prediction';");
    if($now < $end){
        $SQL = "UPDATE users SET points = points - $points WHERE username = '$user';";
        SQLUpdate($SQL);
        $SQL = "INSERT INTO usersChoices VALUES ('$user',$prediction,$choice,$points);";
        SQLInsert($SQL);
    }
    return $prediction;
}

function donnerReponsePrediction($prediction,$answer){
    global $now;
    $author = SQLGetChamp("SELECT author FROM predictions WHERE id='$prediction';");
    $userConnected = $_SESSION["user"];
    $admin = SQLGetChamp("SELECT isAdmin FROM users WHERE username='$userConnected';");
    if($author == $userConnected || $admin){
        SQLUpdate("UPDATE predictions SET correctAnswer = $answer WHERE id = $prediction;");
        $totalPoints = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$prediction;");
        $winPoints = SQLGetChamp("SELECT SUM(pointsSpent) FROM usersChoices WHERE prediction=$prediction AND choice=$answer;");
        if($winPoints!=0){
            $winRate = $totalPoints / $winPoints;
            $tableauDesGagnants = parcoursRs(SQLSelect("SELECT username,pointsSpent FROM usersChoices WHERE prediction='$prediction' AND choice=$answer;"));
            $count=0;
            foreach($tableauDesGagnants as $uneLigne){
                foreach($uneLigne as $uneColonne){
                    $count++;
                    if($count%2==1){
                        $utilisateurAPayer = $uneColonne;
                    }
                    if($count%2==0){
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

function supprimerPrediction($prediction){
    global $now;
    $author = SQLGetChamp("SELECT author FROM predictions WHERE id='$prediction';");
    $userConnected = $_SESSION["user"];
    $admin = SQLGetChamp("SELECT isAdmin FROM users WHERE username='$userConnected';");
    if($author == $userConnected || $admin){
        $correctAnswer = SQLGetChamp("SELECT correctAnswer FROM predictions WHERE id = '$prediction';");
        if(!$correctAnswer){
            $lesChoixDesUtilisateurs = parcoursRs(SQLSelect("SELECT username,pointsSpent FROM usersChoices WHERE prediction='$prediction';"));
            $count=0;
            foreach($lesChoixDesUtilisateurs as $uneLigne){
                foreach($uneLigne as $uneColonne){
                    $count++;
                    if($count%2==1){
                        $utilisateurARembourser = $uneColonne;
                    }
                    if($count%2==0){
                        $pointsARembourser = $uneColonne;
                        SQLUpdate("UPDATE users SET points = points + $pointsARembourser WHERE username='$utilisateurARembourser'");
                    }
                }
            }
        }
        SQLDelete("DELETE FROM usersChoices WHERE prediction='$prediction';");
        SQLDelete("DELETE FROM predictionsChoices WHERE prediction='$prediction';");
        SQLDelete("DELETE FROM predictions WHERE id='$prediction';");
    }
}

?>
