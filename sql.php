<?php
include_once "config.php";
include_once "achievementsManager.php";
/* This file contains $BDD_host, $BDD_base, $BDD_user and $BDD_password
    $BDD_host is the database server address (usually localhost)
    $BDD_base is the database name
    $BDD_user is the username of the database's user
    $BDD_password is the password of the database's user
You MUST have these 4 variables defined (either in a different php file or in replacement of the include_once line) to use the functions below.
*/

$dbh = null;

/**
 * Starts a SQL connexion with the server
 * @return void
 */
function startSQL(){
    global $BDD_host;
    global $BDD_base;
    global $BDD_user;
    global $BDD_password;
    global $dbh;

    try {
        $dbh = new PDO("mysql:host=$BDD_host;dbname=$BDD_base", $BDD_user, $BDD_password);
        $dbh->exec("SET CHARACTER SET utf8");
    } catch (PDOException $e) {
        die("<strong style='color:red'>Error connecting: " . $e->getMessage() . "</strong>");
    }
}

/**
 * Ends the SQL connexion with the server
 * @return void
 */
function endSQL(){
    global $dbh;
    $dbh = null;
}

/**
 * Executes an SQL query and return an Array
 * @param string $sql the query except php variables that are replaced by ? (question marks)
 * @param array|null $param_array Array containing all parameters (that will replace question marks)
 * @return array|boolean an array containing all found results or false if there is no result
 */
function arraySQL(string $sql, array $param_array = null){
    startSQL();
    global $dbh;
    if ($dbh == null) echo "<p style='color:red;'>Pas de connexion à la base de données !</p>";
    try {
        $sth = $dbh->prepare($sql);
        if ($param_array){ // checks if $param_array isn't null
            for ($i = 0; $i < count($param_array); $i++){
                $sth->bindParam($i + 1, $param_array[$i]);
            }
        }
        $res = $sth->execute();
        if ($res === false){
            // die();
            // for debug only:
            die("<strong style='color:red'>Erreur lors de l'exécution : " . $dbh->errorInfo()[2] . "</strong>");
        }
        $res = $sth->fetchAll();
    } catch (Exception $e){  // debug code to handle database problems - uncomment only for developping purposes
        echo "<div style='color:red;margin: 5%;margin-top: 100px;padding: 5%;font-size: 125%;border: 1px solid black;border-radius: 40px;'>";
        echo "Erreur lors de la requête !<br><br>$sql";
        rawPrint($param_array);
        for ($i = 0; $i < count($param_array); $i++){
            $sql = preg_replace("/\?/", $param_array[$i], $sql, 1);
        }
        echo $sql;
        echo "</div>";
        echo 'Caught exception: ', $e->getMessage(), "<br>";
    }
    endSQL();
    if ($res === []) return false;
    else if (count($res) == 0) return false;
    return $res;
}

/**
 * Executes a SQL query and return a string
 * @param string $sql the query except php variables that are replaced by ? (question marks)
 * @param array|null $param_array Array containing all parameters (that will replace question marks)
 * @return bool|string the first string of the result
 */
function stringSQL(string $sql, array $param_array = null){
    $res = arraySQL($sql, $param_array);
    if (!$res) return false;
    return $res[0][0];
}

/**
 * Executes a SQL query and returns an int
 * @param string $sql the query except php variables that are replaced by ? (question marks)
 * @param array|null $param_array Array containing all parameters (that will replace question marks)
 * @return int the first value of the result converted to int (or zero if it was not an int)
 */
function intSQL(string $sql, array $param_array = null){
    return intval(stringSQL($sql, $param_array));
}

/**
 * Executes all types of SQL query without return
 * @param string $sql the query except php variables that are replaced by ? (question marks)
 * @param array|null $param_array Array containing all parameters (that will replace question marks)
 * @return bool true if the query was executed, false otherwise
 */
function rawSQL(string $sql, array $param_array = null){
    return (bool) arraySQL($sql, $param_array);
}

/**
 * Displays anything on the webpage. It is used for debugging purposes.
 * @param mixed $a anything
 * @return void
 */
function rawPrint(mixed $a){
    echo "<pre>\n";
    print_r($a);
    echo "</pre>\n";
}

// ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ GENERAL FUNCTIONS ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

// ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼ SPECIFIC FUNCTIONS ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼

$now = stringSQL("SELECT NOW();");

/**
 * Checks if the user is connected
 * @return bool true if the user is connected, false otherwise
 */
function userConnected(){
    return array_key_exists("user", $_SESSION) && intSQL("SELECT COUNT(*) FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]);
}

/**
 * Checks if the user is a moderator
 * @return bool true if the user is a moderator, false otherwise
 */
function userMod(){
    if(!userConnected()) return false;
    return intSQL("SELECT `mod` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]) == 1;
}

/**
 * Displays the username with an emoji if necessary
 * @param string $username the username
 * @return string the modified username
 */
function displayUsername($username){
    //Variables
    $mod = intSQL("SELECT `mod` FROM `users` WHERE `username` = ?;", [$username]);
    $streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$username]);
    global $streak_achievements;
    //Code
    $icons = "";
    if($mod){$icons .= "<abbr title='Modérateur'><img class='user-icon' src='svg/mod.png'></abbr>";}
    $icons .= checkStaticAchievement($streak, $streak_achievements, "calendar", "Jours de connexion consécutifs");
    return $icons . $username;
}

/**
 * Displays an int value
 * @param int $int int to be displayed
 * @param bool $short true will short display using SI prefixes (M (mega), G (giga), T (tera), P (peta) and E (exa))
 * @return string
 */
function displayInt($int, $short = true){
    $full_number = number_format($int, 0, ',', ' ');
    if(!$short || $int<1000000){
        return  $full_number;
    }
    $string = (string) $int;
    $digits = strlen($string);
    switch($digits){
        case 7:
        case 8:
        case 9:
            $prefix = 'M';break;
        case 10:
        case 11:
        case 12:
            $prefix = 'G';break;
        case 13:
        case 14:
        case 15:
            $prefix = 'T';break;
        case 16:
        case 17:
        case 18:
            $prefix = 'P';break;
        case 19: //Max points have 19 digits
        default:
            $prefix = 'E';break;
    }
    $cropped_result = (int) substr($string,0, 3);
    $divisor = pow(10,(3-($digits%3))%3);
    $result = ((float)$cropped_result) / $divisor;
    $formatted_result = number_format($result, (3-($digits%3))%3, ',', ' ');
    return "<abbr title='". $full_number . "'>" . $formatted_result . " " . $prefix . "</abbr>";
}

/**
 * Displays a float value
 * @param float $float float to be displayed
 * @param int $decimals number of decimals (2 by default)
 * @return string formatted float
 */
function displayFloat($float, $decimals = 2){
    return number_format($float, $decimals, ',', ' ');
}

/**
 * Creates a new account
 * @param string $username the username
 * @param string $password1 the password
 * @param string $password2 the password confirmation
 * @return void
 */
function createAccount($username, $password1, $password2){
    if(!preg_match("/^[A-Za-z0-9]{4,20}$/", $username)){
        header("Location: index.php?view=signup&error=username_invalid");
        die("");
    }
    if($password1 != $password2){
        header("Location: index.php?view=signup&error=password");
        die("");
    }
    if(intSQL("SELECT COUNT(*) FROM users WHERE username = ?;", [$username]) > 0){
        header("Location: index.php?view=signup&error=username_taken");
        die("");
    }
    $hash = password_hash($password1, PASSWORD_DEFAULT);
    rawSQL("INSERT INTO users (`username`, `password`, `created`, `updated`, `streak`, `points`, `mod`) VALUES (?, ?, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT);", [$username, $hash]);
    $_SESSION["user"] = $username;
}

/**
 * Logs in the user
 * @param string $username the username
 * @param string $password the password
 * @return void
 */
function login($username,$password){
    $hash_saved=stringSQL("SELECT `password` FROM `users` WHERE `username` = ?;", [$username]);
    if(!password_verify($password,$hash_saved)){
        header("Location:index.php?view=signin&error=credentials");
        die("");
    }
    $_SESSION["user"] = $username;
}

/**
 * Changes the password of the user
 * @param string $username the username
 * @param string $oldpassword the old password
 * @param string $newpassword the new password
 * @param string $newpasswordconfirmation the new password confirmation
 * @return void
 */
function changePassword($username,$oldpassword,$newpassword, $newpasswordconfirmation){
    if($newpassword != $newpasswordconfirmation){
        header("Location:index.php?view=changePassword&error=password");
        die("");
    }
    $hash_saved = stringSQL("SELECT `password` FROM `users` WHERE `username` = ?;", [$username]);
    if(!password_verify($oldpassword,$hash_saved)){
        header("Location:index.php?view=changePassword&error=old_password");
        die("");
    }
    $newhash = password_hash($newpassword,PASSWORD_DEFAULT);
    rawSQL("UPDATE `users` SET `password` = '$newhash' WHERE `username` = ?;", [$username]);
    session_destroy();
}

/**
 * Deletes an account
 * @param string $username the username of the account to delete
 * @param string $password the password of the logged in user
 * @return void
 */
function deleteAccount($username, $password){
    if(!(userMod() || $username == $_SESSION["user"])){
        header("Location:index.php?view=home&error=forbidden");
        die("");
    }
    $hash_saved = stringSQL("SELECT `password` FROM `users` WHERE `username`= ?;", [$_SESSION["user"]]);
    if(!password_verify($password,$hash_saved)){
        header("Location:index.php?view=deleteAccount&user=$username&error=password");
        die("");
    }
    rawSQL("DELETE FROM `votes` WHERE `user` = ?;", [$username]);
    $createdPredictions = arraySQL("SELECT `id` FROM `predictions` WHERE `user` = ?;", [$username]);
    if($createdPredictions){
        for($i = 0; $i < count($createdPredictions); $i++){
            deletePrediction($createdPredictions[$i]["id"]);
        }
    }
    rawSQL("DELETE FROM `users` WHERE `username` = ?;", [$username]);
    if(!userMod()){
        session_destroy();
    }
}

/**
 * Checks if the user is eligible to create a prediction
 * @return bool true if the user is eligible, false otherwise
 */
function eligible(){
    return userConnected();
}

/**
 * Creates a new prediction
 * @param string $name the name of the prediction
 * @param string $user the username of the creator
 * @param string $end the end timestamp of the prediction
 * @param int $offset the offset of the timezone in minutes
 * @param array $choices the choices of the prediction
 * @return int the id of the prediction
 */
function createPrediction($name,$user,$end,$offset,$choices){//Variable $choices is an array of strings containing titles of choices
    if(!eligible()){
        header("Location:index.php?view=home&error=forbidden");
        die("");
    }
    date_default_timezone_set('UTC');
    $endUTC = date('Y-m-d\TH:i',strtotime($end)-($offset*60));
    rawSQL("INSERT INTO `predictions` VALUES (DEFAULT, ?, DEFAULT, ?, DEFAULT, ?, DEFAULT, DEFAULT);", [$name, $user, $endUTC]);
    $predictionID = intSQL("SELECT `id` FROM `predictions` ORDER BY `created` DESC LIMIT 1;");
    foreach($choices as $choice){
        rawSQL("INSERT INTO `choices` VALUES (DEFAULT, ?, ?);", [$predictionID, $choice]);
    }
    return $predictionID;
}

/**
 * Votes for a prediction
 * @param string $user the username of the voter
 * @param int $prediction the id of the prediction
 * @param int $choice the id of the choice
 * @param int $points the number of points spent
 * @return int the id of the prediction
 */
function vote($user,$prediction,$choice,$points){
    global $now;
    $end = stringSQL("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    if($now > $end){
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=closed");
        die("");
    }
    rawSQL("UPDATE `users` SET `points` = points - ? WHERE `username` = ?;", [$points, $user]);
    rawSQL("INSERT INTO `votes` VALUES (?, ?, ?, ?);", [$user, $prediction, $choice, $points]);
    return $prediction;
}

/**
 * Adds points to a vote
 * @param string $user the username of the voter
 * @param int $prediction the id of the prediction
 * @param int $points the number of points added
 * @return int the id of the prediction
 */
function addPoints($user,$prediction,$points){
    global $now;
    $end = stringSQL("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    if($now > $end){
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=closed");
        die("");
    }
    rawSQL("UPDATE `users` SET `points` = points - ? WHERE `username` = ?;", [$points, $user]);
    rawSQL("UPDATE `votes` SET `points` = points + ? WHERE `user` = ? AND `prediction` = ?;", [$points, $user, $prediction]);
    return $prediction;
}

/**
 * Answers a prediction
 * @param int $prediction the id of the prediction
 * @param int $answer the id of the answer
 * @return int the id of the prediction
 */
function answer($prediction,$answer){
    global $now;
    $author = stringSQL("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    $userConnected = $_SESSION["user"];
    if(!($author == $userConnected || userMod())){
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=unauthorized");
        die("");
    }
    $end = stringSQL("SELECT `ended` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    if($now < $end){
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=too_early");
        die("");
    }
    rawSQL("UPDATE `predictions` SET `answered` = ? WHERE `id` = ?;", [$now, $prediction]);
    rawSQL("UPDATE `predictions` SET `answer` = ? WHERE id = ?;", [$answer, $prediction]);
    $totalPoints = intSQL("SELECT SUM(points) FROM `votes` WHERE `prediction` = ?;", [$prediction]);
    $winPoints = intSQL("SELECT SUM(points) FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$prediction, $answer]);
    if($winPoints!=0){
        $winRate = $totalPoints / $winPoints;
        $winTable = arraySQL("SELECT `user`, `points` FROM `votes` WHERE `prediction` = ? AND `choice` = ?;", [$prediction, $answer]);
        for($i = 0; $i < count($winTable); $i++){
            $winUser = $winTable[$i]["user"];
            $votePoints = $winTable[$i]["points"];
            $earnedPoints = floor($votePoints * $winRate);
            rawSQL("UPDATE `users` SET `points` = points + ? WHERE `username` = ?;", [$earnedPoints, $winUser]);
        }
    }
    return $prediction;
}

/**
 * Deletes a prediction
 * @param int $prediction the id of the prediction
 * @return void
 */
function deletePrediction($prediction){
    global $now;
    $author = stringSQL("SELECT `user` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    $userConnected = $_SESSION["user"];
    if(!($author == $userConnected || userMod())){
        header("Location:index.php?view=prediction&id=" . $prediction . "&error=unauthorized");
        die("");
    }
    $correctAnswer = intSQL("SELECT `answer` FROM `predictions` WHERE `id` = ?;", [$prediction]);
    if(!$correctAnswer){
        $usersVotes = arraySQL("SELECT `user`, `points` FROM `votes` WHERE `prediction` = ?;", [$prediction]);
        if($usersVotes){
            for($i = 0; $i < count($usersVotes); $i++){
                $user = $usersVotes[$i]["user"];
                $points = $usersVotes[$i]["points"];
                rawSQL("UPDATE `users` SET `points` = points + ? WHERE `username` = ?;", [$points, $user]);
            }
        }
    }
    rawSQL("DELETE FROM `votes` WHERE `prediction` = ?;", [$prediction]);
    rawSQL("UPDATE `predictions` SET `answer` = NULL WHERE `id` = ?;", [$prediction]);
    rawSQL("DELETE FROM `choices` WHERE `prediction` = ?;", [$prediction]);
    rawSQL("DELETE FROM `predictions` WHERE `id` = ?;", [$prediction]);
}
?>