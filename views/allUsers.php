<?php
$order = array_key_exists("order", $_REQUEST)?$_REQUEST["order"]:"";
$request = "
SELECT 
    users.`username`,
    users.`created`,
    users.`updated`,
    users.`streak`,
    users.`points`,
    users.`mod`,
    COALESCE(predictions2.pred_count, 0) AS predictions_created,
    COALESCE(votes2.vote_count, 0) AS vote_count,
    COALESCE(votes2.vote_points, 0) AS vote_points,
    COALESCE(correct_votes.correct_vote_count, 0) AS correct_vote_count,
    COALESCE(points_wins.points_spent_on_wins, 0) AS points_spent_on_wins
FROM `users`
LEFT JOIN (SELECT user, COUNT(*) AS vote_count, SUM(points) AS vote_points FROM votes GROUP BY user) votes2 ON users.username = votes2.user
LEFT JOIN (SELECT user, COUNT(*) AS pred_count FROM predictions GROUP BY user) predictions2 ON users.username = predictions2.user
LEFT JOIN (
    SELECT votes.user, COUNT(*) AS correct_vote_count
    FROM votes
    JOIN predictions ON votes.prediction = predictions.id
    WHERE votes.choice = predictions.answer
    GROUP BY votes.user
) correct_votes ON users.username = correct_votes.user
LEFT JOIN (
    SELECT u.username, COALESCE(SUM(
        CASE 
            WHEN p.answer = v.choice THEN v.points 
            ELSE 0 
        END
    ), 0) AS points_spent_on_wins
    FROM users u
    LEFT JOIN votes v ON u.username = v.user
    LEFT JOIN predictions p ON v.prediction = p.id AND p.answer IS NOT NULL
    GROUP BY u.username
) points_wins ON users.username = points_wins.username
";
switch($order){
    case "usernameA-Z":$users = arraySQL($request . " ORDER BY `username` ASC;");break;
    case "usernameZ-A":$users = arraySQL($request . " ORDER BY `username` DESC;");break;
    case "createdOld":$users = arraySQL($request . " ORDER BY `created` ASC;");break;
    case "createdNew":$users = arraySQL($request . " ORDER BY `created` DESC;");break;
    case "updatedOld":$users = arraySQL($request . " ORDER BY `updated` ASC;");break;
    case "updatedNew":$users = arraySQL($request . " ORDER BY `updated` DESC;");break;
    case "streakLow":$users = arraySQL($request . " ORDER BY `streak` ASC;");break;
    case "streakHigh":$users = arraySQL($request . " ORDER BY `streak` DESC;");break;
    case "pointsLow":$users = arraySQL($request . " ORDER BY `points` ASC;");break;
    case "pointsHigh":$users = arraySQL($request . " ORDER BY `points` DESC;");break;
    case "mod":$users = arraySQL($request . " ORDER BY `mod` DESC;");break;
    case "predictionsLow":$users = arraySQL($request . " ORDER BY `predictions_created` ASC;");break;
    case "predictionsHigh":$users = arraySQL($request . " ORDER BY `predictions_created` DESC;");break;
    case "votesLow":$users = arraySQL($request . " ORDER BY `vote_count` ASC;");break;
    case "votesHigh":$users = arraySQL($request . " ORDER BY `vote_count` DESC;");break;
    case "spentLow":$users = arraySQL($request . " ORDER BY `vote_points` ASC;");break;
    case "spentHigh":$users = arraySQL($request . " ORDER BY `vote_points` DESC;");break;
    default:$users = arraySQL($request . ";");break;
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
$sort_predictions = ($order == "predictionsHigh")?"predictionsLow":"predictionsHigh";
$sort_votes = ($order == "votesHigh")?"votesLow":"votesHigh";
$sort_spent = ($order == "spentHigh")?"spentLow":"spentHigh";
$link_username = "?view=allUsers&order=" . $sort_username;
$link_created = "?view=allUsers&order=" . $sort_created;
$link_updated = "?view=allUsers&order=" . $sort_updated;
$link_streak = "?view=allUsers&order=" . $sort_streak;
$link_points = "?view=allUsers&order=" . $sort_points;
$link_mod = "?view=allUsers&order=mod";
$link_predictions = "?view=allUsers&order=" . $sort_predictions;
$link_votes = "?view=allUsers&order=" . $sort_votes;
$link_spent = "?view=allUsers&order=" . $sort_spent;
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
        <th><p><a href=\"" . $link_predictions . "\">Prédictions créées" . isOrderedBy("predictions") . "</a></th>
        <th><p><a href=\"" . $link_votes . "\">Votes" . isOrderedBy("votes") . "<br><small>(dont corrects)</small></a></th>
        <th><p><a href=\"" . $link_spent . "\">Points dépensés" . isOrderedBy("spent") . "<br><small>(dont mises remportées)</small></a></th>
    </tr>";
if(!$users){
    echo "<tr><td colspan='9'>Aucun utilisateur</td></tr>";
}else{
    for($i = 0; $i < $accounts; $i++){
        $link_user = "?view=profile&user=" . $users[$i]["username"];
        $username = $users[$i]["username"];
        $created = $users[$i]["created"];
        $updated = $users[$i]["updated"];
        $streak = $users[$i]["streak"];
        $points = $users[$i]["points"];
        $mod = $users[$i]["mod"];
        $predictions = $users[$i]["predictions_created"];
        $votes = $users[$i]["vote_count"];
        $votes_correct = $users[$i]["correct_vote_count"];
        $spent = $users[$i]["vote_points"];
        $spent_on_wins = $users[$i]["points_spent_on_wins"];
        echo "<tr>
            <td><p><a href=\"" . $link_user . "\">" . displayUsername($username) . "</a></td>
            <td>" . $created . "</td>
            <td>" . $updated . "</td>
            <td>" . $streak . "</td>
            <td>" . displayInt($points) . "</td>
            <td>" . $mod . "</td>
            <td>" . $predictions . "</td>
            <td>" . $votes . " <small>(" . $votes_correct . ")</small></td>
            <td>" . displayInt($spent) . " <small>(" . displayInt($spent_on_wins) . ")</small></td>
        </tr>";
    }
}
echo "</table>";
?>