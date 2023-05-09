<?php

include_once "maLibSQL.pdo.php";
$now = SQLGetChamp("SELECT NOW();");

function creerCompte($username,$displayname,$hash){
    global $now;
    if(SQLGetChamp("SELECT COUNT(*) FROM users WHERE username='$username';") == 0){
        SQLInsert("INSERT INTO users VALUES ('$username','$displayname','$hash',NULL,100,false,'$now')");
        $_SESSION["user"] = $username;
        $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
        $_SESSION["connecte"] = true;
        $_SESSION["heureConnexion"] = date("H:i:s");
    }else{
        header("Location:index.php?view=signup&error=username");
        die("");
    }
}

function seConnecter($username,$password){
    $hash_saved=SQLGetChamp("SELECT hash_pwd FROM users WHERE username='$username';");
    if(password_verify($password,$hash_saved)){
        $_SESSION["user"] = $username;
        $_SESSION["nickname"] = SQLGetChamp("SELECT nickname FROM users WHERE username='$username';");
        $_SESSION["connecte"] = true;
    }else{
        header("Location:index.php?view=signin&error=password");
        die("");
    }
}

function changerMotDePasse($username,$oldpassword,$newpassword){
    $hash_saved=SQLGetChamp("SELECT hash_pwd FROM users WHERE username='$username';");
    if(password_verify($oldpassword,$hash_saved)){
        $newhash = password_hash($newpassword,PASSWORD_DEFAULT);
        SQLUpdate("UPDATE users SET hash_pwd='$newhash' WHERE username='$username';");
        session_destroy();
    }else{
        header("Location:index.php?view=changePassword&error=password");
        die("");
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
    SQLInsert("INSERT INTO predictions VALUES (DEFAULT,'$name','$user','$now','$endDateUTC',NULL);");
    $predictionID = SQLGetChamp("SELECT id FROM predictions WHERE title = '$name' AND author = '$user' AND endDate = '$endDateUTC';");
    foreach($choix as $unChoix){
        $unChoix = html_special_chars($unChoix);
        SQLInsert("INSERT INTO predictionsChoices VALUES (DEFAULT, $predictionID, '$unChoix');");
    }
    return $predictionID;
}

function parier($user,$prediction,$choice,$points){
    global $now;
    $end = SQLGetChamp("SELECT endDate FROM predictions WHERE id='$prediction';");
    if($now < $end){
        SQLUpdate("UPDATE users SET points = points - $points WHERE username = '$user';");
        SQLInsert("INSERT INTO usersChoices VALUES ('$user',$prediction,$choice,$points);");
    }else{
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=closed");
        die("");
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
    }else{
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=unauthorized");
        die("");
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
    }else{
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=unauthorized");
        die("");
    }
}

function supprimerCompte($username, $password){
    $hash_saved=SQLGetChamp("SELECT hash_pwd FROM users WHERE username='$username';");
    if(password_verify($password,$hash_saved)){
        SQLDelete("DELETE FROM usersChoices WHERE username='$username';");
        $createdPredictions = SQLSelect("SELECT id FROM predictions WHERE author='$username';");
        foreach($createdPredictions as $unePrediction){
            foreach($unePrediction as $unID){
                supprimerPrediction($unID);
            }
        }
        SQLDelete("DELETE FROM users WHERE username='$username';");
        session_destroy();
    }else{
        header("Location:index.php?view=deleteAccount&error=password");
        die("");
    }
}
?>
