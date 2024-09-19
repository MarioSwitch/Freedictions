<?php
function displayStat($array){
    return "<tr>
        <td>" . $array["name"] . "</td>
        <td>" . displayInt($array["1d"]) . "<br><small>" . getString("percentage", [displayFloat($array["1d"] / $array["all"] * 100)]) . "</small></td>
        <td>" . displayInt($array["1w"]) . "<br><small>" . getString("percentage", [displayFloat($array["1w"] / $array["all"] * 100)]) . "</small></td>
        <td>" . displayInt($array["1mo"]) . "<br><small>" . getString("percentage", [displayFloat($array["1mo"] / $array["all"] * 100)]) . "</small></td>
        <td>" . displayInt($array["1y"]) . "<br><small>" . getString("percentage", [displayFloat($array["1y"] / $array["all"] * 100)]) . "</small></td>
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
$averagePoints = $totalPoints / $usersOnline["all"];
$medianPoints = getMedian("points");

$totalCreated = intSQL("SELECT COUNT(*) FROM `predictions`;");
$averageCreated = $totalCreated / $usersOnline["all"];
$medianCreated = getMedian("created");

$totalBets = intSQL("SELECT COUNT(*) FROM `votes`;");
$averageBets = $totalBets / $usersOnline["all"];
$medianBets = getMedian("bets");

$totalPointsSpent = intSQL("SELECT SUM(`points`) FROM `votes`;");
$averagePointsSpent = $totalPointsSpent / $usersOnline["all"];
$medianPointsSpent = getMedian("pointsSpent");

$totalChoices = intSQL("SELECT COUNT(*) FROM `choices`;");
$averageChoices = $totalChoices / $predictionsCreated["all"];
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
        <td>" . getString("points") . "</td>
        <td>" . displayInt($totalPoints) . "</td>
        <td>" . displayFloat($averagePoints) . "</td>
        <td>" . displayInt($medianPoints) . "</td>
    </tr>
    <tr>
        <td>" . getString("predictions_created") . "</td>
        <td>" . displayInt($totalCreated) . "</td>
        <td>" . displayFloat($averageCreated) . "</td>
        <td>" . displayInt($medianCreated) . "</td>
    </tr>
    <tr>
        <td>" . getString("bets") . "</td>
        <td>" . displayInt($totalBets) . "</td>
        <td>" . displayFloat($averageBets) . "</td>
        <td>" . displayInt($medianBets) . "</td>
    </tr>
    <tr>
        <td>" . getString("points_spent") . "</td>
        <td>" . displayInt($totalPointsSpent) . "</td>
        <td>" . displayFloat($averagePointsSpent) . "</td>
        <td>" . displayInt($medianPointsSpent) . "</td>
    </tr>
    <tr>
        <td>" . getString("choices") . "</td>
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