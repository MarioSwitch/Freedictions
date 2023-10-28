<?php
$order = array_key_exists("order", $_REQUEST)?$_REQUEST["order"]:"";
switch($order){
    case "usernameA-Z":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `username` ASC;");break;
    case "usernameZ-A":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `username` DESC;");break;
    case "createdOld":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `created` ASC;");break;
    case "createdNew":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `created` DESC;");break;
    case "updatedOld":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `updated` ASC;");break;
    case "updatedNew":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `updated` DESC;");break;
    case "streakLow":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `streak` ASC;");break;
    case "streakHigh":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `streak` DESC;");break;
    case "pointsLow":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `points` ASC;");break;
    case "pointsHigh":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `points` DESC;");break;
    case "mod":$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users` ORDER BY `mod` DESC;");break;
    default:$users = arraySQL("SELECT `username`, `created`, `updated`, `streak`, `points`, `mod` FROM `users`;");break;
}
$accounts = intSQL("SELECT COUNT(*) FROM `users`");
echo "<h1>Liste des utilisateurs</h1>";
echo "<p>Ci-dessous, la liste des " . displayInt($accounts) . " utilisateurs.</p>";
echo "<hr>";
$sort_username = ($order == "usernameA-Z")?"usernameZ-A":"usernameA-Z";
$sort_created = ($order == "createdOld")?"createdNew":"createdOld";
$sort_updated = ($order == "updatedNew")?"updatedOld":"updatedNew";
$sort_streak = ($order == "streakHigh")?"streakLow":"streakHigh";
$sort_points = ($order == "pointsHigh")?"pointsLow":"pointsHigh";
$link_username = "?view=allUsers&order=" . $sort_username;
$link_created = "?view=allUsers&order=" . $sort_created;
$link_updated = "?view=allUsers&order=" . $sort_updated;
$link_streak = "?view=allUsers&order=" . $sort_streak;
$link_points = "?view=allUsers&order=" . $sort_points;
$link_mod = "?view=allUsers&order=mod";
function isOrderedBy(string $tag){
    global $order;
    if (str_contains($order, $tag)){
        if(str_contains($order, "A-Z") || str_contains($order, "Old") || str_contains($order, "Low")){
            return " ▲";
        }else{
            return " ▼";
        }
    }
    return "";
}
echo "<table>
    <tr>
        <th><p><a href=\"" . $link_username . "\">Utilisateur" . isOrderedBy("username") . "</a></th>
        <th><p><a href=\"" . $link_created . "\">Création (UTC)" . isOrderedBy("created") . "</a></th>
        <th><p><a href=\"" . $link_updated . "\">Dernière connexion (UTC)" . isOrderedBy("updated") . "</a></th>
        <th><p><a href=\"" . $link_streak . "\">Série de connexion" . isOrderedBy("streak") . "</a></th>
        <th><p><a href=\"" . $link_points . "\">Points" . isOrderedBy("points") . "</a></th>
        <th><p><a href=\"" . $link_mod . "\">Modérateur" . isOrderedBy("mod") . "</a></th>
    </tr>";
if(!$users){
    echo "<tr><td colspan='6'>Aucun utilisateur</td></tr>";
}else{
    for($i = 0; $i < $accounts; $i++){
        $link_user = "?view=profile&user=" . $users[$i]["username"];
        $username = $users[$i]["username"];
        $created = $users[$i]["created"];
        $updated = $users[$i]["updated"];
        $streak = $users[$i]["streak"];
        $points = $users[$i]["points"];
        $mod = $users[$i]["mod"];
        echo "<tr><td><p><a href=\"" . $link_user . "\">" . $username . "</a></td><td>" . $created . "</td><td>" . $updated . "</td><td>" . $streak . "</td><td>" . displayInt($points) . "</td><td>" . $mod . "</td></tr>";
    }
}
echo "</table>";
?>