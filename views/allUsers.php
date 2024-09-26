<?php
$order = array_key_exists("order", $_REQUEST)?$_REQUEST["order"]:"";
$request_old = "
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
$request_pointsEarned = "
WITH prediction_yields AS (
    SELECT
        p.id AS prediction_id,
        SUM(v.points) / SUM(CASE WHEN v.choice = p.answer THEN v.points ELSE 0 END) AS yield
    FROM
        predictions p
    JOIN
        votes v ON v.prediction = p.id
    WHERE
        p.answer IS NOT NULL
    GROUP BY
        p.id
),
user_points AS (
    SELECT
        v.user AS username,
        v.prediction AS prediction_id,
        FLOOR(v.points * py.yield) AS points_gained
    FROM
        votes v
    JOIN
        predictions p ON v.prediction = p.id
    JOIN
        prediction_yields py ON v.prediction = py.prediction_id
    WHERE
        v.choice = p.answer
)
SELECT
    u.username,
    COALESCE(SUM(up.points_gained), 0) AS total_points_gained
FROM
    users u
LEFT JOIN
    user_points up ON u.username = up.username
GROUP BY
    u.username
ORDER BY
    total_points_gained DESC;
";
$request = "
WITH prediction_yields AS (
        SELECT
            p.id AS prediction_id,
            SUM(v.points) / SUM(CASE WHEN v.choice = p.answer THEN v.points ELSE 0 END) AS yield
        FROM
            predictions p
        JOIN
            votes v ON v.prediction = p.id
        WHERE
            p.answer IS NOT NULL
        GROUP BY
            p.id
    ),
    user_points AS (
        SELECT
            v.user AS username,
            v.prediction AS prediction_id,
            FLOOR(v.points * py.yield) AS points_gained
        FROM
            votes v
        JOIN
            predictions p ON v.prediction = p.id
        JOIN
            prediction_yields py ON v.prediction = py.prediction_id
        WHERE
            v.choice = p.answer
    )
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
    COALESCE(points_wins.points_spent_on_wins, 0) AS points_spent_on_wins,
    COALESCE(SUM(up.points_gained), 0) AS points_earned
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
LEFT JOIN
        user_points up ON users.username = up.username
    GROUP BY
        users.username
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
    case "winsLow":$users = arraySQL($request . " ORDER BY `correct_vote_count` ASC;");break;
    case "winsHigh":$users = arraySQL($request . " ORDER BY `correct_vote_count` DESC;");break;
    case "spentLow":$users = arraySQL($request . " ORDER BY `vote_points` ASC;");break;
    case "spentHigh":$users = arraySQL($request . " ORDER BY `vote_points` DESC;");break;
    case "psowLow":$users = arraySQL($request . " ORDER BY `points_spent_on_wins` ASC;");break;
    case "psowHigh":$users = arraySQL($request . " ORDER BY `points_spent_on_wins` DESC;");break;
    case "earnedLow":$users = arraySQL($request . " ORDER BY `points_earned` ASC;");break;
    case "earnedHigh":$users = arraySQL($request . " ORDER BY `points_earned` DESC;");break;
    default:$users = arraySQL($request . ";");break;
}
$accounts = intSQL("SELECT COUNT(*) FROM `users`");
$myUsername = isConnected()?$_COOKIE["username"]:"";
echo "<h1>" . getString("allUsers_title") . "</h1>";
echo "<p>" . getString("allUsers_description", [displayInt($accounts)]) . "</p>";
echo "<hr>";

$sort_username = ($order == "usernameA-Z")?"usernameZ-A":"usernameA-Z";
$sort_created = ($order == "createdNew")?"createdOld":"createdNew";
$sort_updated = ($order == "updatedNew")?"updatedOld":"updatedNew";
$sort_streak = ($order == "streakHigh")?"streakLow":"streakHigh";
$sort_points = ($order == "pointsHigh")?"pointsLow":"pointsHigh";
$sort_predictions = ($order == "predictionsHigh")?"predictionsLow":"predictionsHigh";
$sort_votes = ($order == "votesHigh")?"votesLow":"votesHigh";
$sort_wins = ($order == "winsHigh")?"winsLow":"winsHigh";
$sort_spent = ($order == "spentHigh")?"spentLow":"spentHigh";
$sort_psow = ($order == "psowHigh")?"psowLow":"psowHigh";
$sort_earned = ($order == "earnedHigh")?"earnedLow":"earnedHigh";

$link_username = "?view=allUsers&order=" . $sort_username;
$link_created = "?view=allUsers&order=" . $sort_created;
$link_updated = "?view=allUsers&order=" . $sort_updated;
$link_streak = "?view=allUsers&order=" . $sort_streak;
$link_points = "?view=allUsers&order=" . $sort_points;
$link_mod = "?view=allUsers&order=mod";
$link_predictions = "?view=allUsers&order=" . $sort_predictions;
$link_votes = "?view=allUsers&order=" . $sort_votes;
$link_wins = "?view=allUsers&order=" . $sort_wins;
$link_spent = "?view=allUsers&order=" . $sort_spent;
$link_psow = "?view=allUsers&order=" . $sort_psow;
$link_earned = "?view=allUsers&order=" . $sort_earned;

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
        <th><p><a href=\"" . $link_username . "\">" . getString("user") . isOrderedBy("username") . "</a></th>
        <th><p><a href=\"" . $link_created . "\">" . getString("created") . " (UTC)" . isOrderedBy("created") . "</a></th>
        <th><p><a href=\"" . $link_updated . "\">" . getString("online") . " (UTC)" . isOrderedBy("updated") . "</a></th>
        <th><p><a href=\"" . $link_streak . "\">" . getString("streak") . isOrderedBy("streak") . "</a></th>
        <th><p><a href=\"" . $link_points . "\"> " . getString("points") . isOrderedBy("points") . "</a></th>
        <th><p><a href=\"" . $link_mod . "\">" . getString("mod") . isOrderedBy("mod") . "</a></th>
        <th><p><a href=\"" . $link_predictions . "\">" . getString("predictions_created") . isOrderedBy("predictions") . "</a></th>
        <th><p><a href=\"" . $link_votes . "\">" . getString("bets") . isOrderedBy("votes") . "</a></th>
        <th><p><a href=\"" . $link_wins . "\">" . getString("bets_won") . isOrderedBy("wins") . "</a></th>
        <th><p><a href=\"" . $link_spent . "\">" . getString("points_spent") . isOrderedBy("spent") . "</a></th>
        <th><p><a href=\"" . $link_psow . "\">" . getString("points_spent_on_wins") . isOrderedBy("psow") . "</a></th>
        <th><p><a href=\"" . $link_earned . "\">" . getString("points_earned") . isOrderedBy("earned") . "</a></th>
    </tr>";
if(!$users){
    echo "<tr><td colspan='9'>" . getString("users_none") . "</td></tr>";
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
        $earned = $users[$i]["points_earned"];
        echo "<tr" . ($myUsername == $username ? " class=\"blue\"":"") . ">
            <td><p><a href=\"" . $link_user . "\">" . displayUsername($username) . "</a></td>
            <td>" . $created . "</td>
            <td>" . $updated . "</td>
            <td>" . displayInt($streak) . "</td>
            <td>" . displayInt($points) . "</td>
            <td>" . $mod . "</td>
            <td>" . displayInt($predictions) . "</td>
            <td>" . displayInt($votes) . "</td>
            <td>" . displayInt($votes_correct) . "</td>
            <td>" . displayInt($spent) . "</td>
            <td>" . displayInt($spent_on_wins) . "</td>
            <td>" . displayInt($earned) . "</td>
        </tr>";
    }
}
echo "</table>";