<?php
include_once "config.php";
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

function userConnected(){
    return array_key_exists("user", $_SESSION);
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
?>