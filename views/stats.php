<?php
function displayStat($array){
    $percentage1d = $array["all"] ? ($array["1d"] / $array["all"] * 100) : PHP_INT_MAX;
    $percentage1w = $array["all"] ? ($array["1w"] / $array["all"] * 100) : PHP_INT_MAX;
    $percentage1mo = $array["all"] ? ($array["1mo"] / $array["all"] * 100) : PHP_INT_MAX;
    $percentage1y = $array["all"] ? ($array["1y"] / $array["all"] * 100) : PHP_INT_MAX;
    return "<tr>
        <td>" . $array["name"] . "</td>
        <td>" . displayInt($array["1d"]) . "<br><small>" . getString("percentage", [displayFloat($percentage1d)]) . "</small></td>
        <td>" . displayInt($array["1w"]) . "<br><small>" . getString("percentage", [displayFloat($percentage1w)]) . "</small></td>
        <td>" . displayInt($array["1mo"]) . "<br><small>" . getString("percentage", [displayFloat($percentage1mo)]) . "</small></td>
        <td>" . displayInt($array["1y"]) . "<br><small>" . getString("percentage", [displayFloat($percentage1y)]) . "</small></td>
        <td>" . displayInt($array["all"]) . "</td>
    </tr>";
}

function getMedian(string $key){
    switch($key){
        case "points":
            $request = "SELECT `points` FROM `users` ORDER BY `points` DESC;";
            $count = intSQL("SELECT COUNT(*) FROM `users`;");
            break;
        case "created":
            $request = "SELECT COUNT(*) FROM `predictions` GROUP BY `user` ORDER BY COUNT(*) DESC;";
            $count = intSQL("SELECT COUNT(*) FROM `users`;");
            break;
        case "bets":
            $request = "SELECT COUNT(*) FROM `votes` GROUP BY `user` ORDER BY COUNT(*) DESC;";
            $count = intSQL("SELECT COUNT(*) FROM `users`;");
            break;
        case "pointsSpent":
            $request = "SELECT SUM(`points`) FROM `votes` GROUP BY `user` ORDER BY SUM(`points`) DESC;";
            $count = intSQL("SELECT COUNT(*) FROM `users`;");
            break;
        case "choices":
            $request = "SELECT COUNT(*) FROM `choices` GROUP BY `prediction` ORDER BY COUNT(*) DESC;";
            $count = intSQL("SELECT COUNT(*) FROM `predictions`;");
            break;
    }
    $array = arraySQL($request);
    $middle = ceil($count / 2) - 1; // -1 because arrays start at 0
    if(!$array) return PHP_INT_MAX;
    if(count($array) < $count) return 0;
    if($count % 2 == 1){
        return $array[$middle][0];
    }else{
        return ($array[$middle][0] + $array[$middle + 1][0]) / 2;
    }
}

$usersOnline = [
    "name" => getString("users_online"),
    "1d" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `users`;")
];

$usersCreated = [
    "name" => getString("users_created"),
    "1d" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `users`;")
];

$predictionsCreated = [
    "name" => getString("predictions_created"),
    "1d" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `predictions`;")
];

$totalPoints = intSQL("SELECT SUM(`points`) FROM `users`;");
$averagePoints = $usersOnline["all"] ? ($totalPoints / $usersOnline["all"]) : PHP_INT_MAX;
$medianPoints = getMedian("points");

$totalCreated = intSQL("SELECT COUNT(*) FROM `predictions`;");
$averageCreated = $usersOnline["all"] ? ($totalCreated / $usersOnline["all"]) : PHP_INT_MAX;
$medianCreated = getMedian("created");

$totalBets = intSQL("SELECT COUNT(*) FROM `votes`;");
$averageBets = $usersOnline["all"] ? ($totalBets / $usersOnline["all"]) : PHP_INT_MAX;
$medianBets = getMedian("bets");

$totalPointsSpent = intSQL("SELECT SUM(`points`) FROM `votes`;");
$averagePointsSpent = $usersOnline["all"] ? ($totalPointsSpent / $usersOnline["all"]) : PHP_INT_MAX;
$medianPointsSpent = getMedian("pointsSpent");

$totalChoices = intSQL("SELECT COUNT(*) FROM `choices`;");
$averageChoices = $predictionsCreated["all"] ? ($totalChoices / $predictionsCreated["all"]) : PHP_INT_MAX;
$medianChoices = getMedian("choices");

echo "
<h1>" . getString("stats") . "</h1>
<table>
    <tr>
        <th>" . getString("stat") . "</th>
        <th>" . getString("total") . "</th>
        <th>" . getString("average") . "</th>
        <th>" . getString("median") . "</th>
    </tr>
    <tr>
        <td>" . getString("points") . "<br><small>" . getString("per_user") . "</small></td>
        <td>" . displayInt($totalPoints) . "</td>
        <td>" . displayFloat($averagePoints) . "</td>
        <td>" . displayInt($medianPoints) . "</td>
    </tr>
    <tr>
        <td>" . getString("predictions_created") . "<br><small>" . getString("per_user") . "</small></td>
        <td>" . displayInt($totalCreated) . "</td>
        <td>" . displayFloat($averageCreated) . "</td>
        <td>" . displayInt($medianCreated) . "</td>
    </tr>
    <tr>
        <td>" . getString("bets") . "<br><small>" . getString("per_user") . "</small></td>
        <td>" . displayInt($totalBets) . "</td>
        <td>" . displayFloat($averageBets) . "</td>
        <td>" . displayInt($medianBets) . "</td>
    </tr>
    <tr>
        <td>" . getString("points_spent") . "<br><small>" . getString("per_user") . "</small></td>
        <td>" . displayInt($totalPointsSpent) . "</td>
        <td>" . displayFloat($averagePointsSpent) . "</td>
        <td>" . displayInt($medianPointsSpent) . "</td>
    </tr>
    <tr>
        <td>" . getString("choices") . "<br><small>" . getString("per_prediction") . "</small></td>
        <td>" . displayInt($totalChoices) . "</td>
        <td>" . displayFloat($averageChoices) . "</td>
        <td>" . displayInt($medianChoices) . "</td>
    </tr>
</table>

<br>

<table>
    <tr>
        <th rowspan=\"2\">" . getString("stat") . "</th>
        <th colspan=\"4\">" . getString("last") . "</th>
        <th rowspan=\"2\">" . getString("total") . "</th>
    </tr>
    <tr>
        <th>24 " . getString("time_hours") . "</th>
        <th>7 " . getString("time_days") . "</th>
        <th>30 " . getString("time_days") . "</th>
        <th>365 " . getString("time_days") . "</th>
    </tr>
    <tr><td colspan=\"6\">" . getString("users") . "</td></tr>" . 
    displayStat($usersOnline) . 
    displayStat($usersCreated) . "
    <tr><td colspan=\"6\">" . getString("predictions") . "</td></tr>" . 
    displayStat($predictionsCreated) . "
</table>";