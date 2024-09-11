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

$usersOnline = [
    "name" => getString("stats_users_online"),
    "1d" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `users` WHERE `updated` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `users`;")
];

$usersCreated = [
    "name" => getString("stats_users_created"),
    "1d" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `users` WHERE `created` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `users`;")
];

$predictionsCreated = [
    "name" => getString("predictions_created_no_value"),
    "1d" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 DAY;"),
    "1w" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 WEEK;"),
    "1mo" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 MONTH;"),
    "1y" => intSQL("SELECT COUNT(*) FROM `predictions` WHERE `created` > NOW() - INTERVAL 1 YEAR;"),
    "all" => intSQL("SELECT COUNT(*) FROM `predictions`;")
];

$totalPoints = intSQL("SELECT SUM(`points`) FROM `users`;");
$averagePoints = $totalPoints / $usersOnline["all"];

$totalPointsSpent = intSQL("SELECT SUM(`points`) FROM `votes`;");
$averagePointsSpent = $totalPointsSpent / $usersOnline["all"];

$totalBets = intSQL("SELECT COUNT(*) FROM `votes`;");
$averageBets = $totalBets / $usersOnline["all"];

$totalChoices = intSQL("SELECT COUNT(*) FROM `choices`;");
$averageChoices = $totalChoices / $predictionsCreated["all"];

echo "
<h1>" . getString("stats_title") . "</h1>
<table>
    <tr>
        <th>" . getString("stats_stat") . "</th>
        <th>" . getString("stats_all") . "</th>
        <th>" . getString("stats_average") . "</th>
    </tr>
    <tr>
        <td>" . getString("points") . "</td>
        <td>" . displayInt($totalPoints) . "</td>
        <td>" . displayFloat($averagePoints) . "</td>
    </tr>
    <tr>
        <td>" . getString("points_spent") . "</td>
        <td>" . displayInt($totalPointsSpent) . "</td>
        <td>" . displayFloat($averagePointsSpent) . "</td>
    </tr>
    <tr>
        <td>" . getString("votes") . "</td>
        <td>" . displayInt($totalBets) . "</td>
        <td>" . displayFloat($averageBets) . "</td>
    </tr>
    <tr>
        <td>" . getString("createPrediction_form_choices") . "</td>
        <td>" . displayInt($totalChoices) . "</td>
        <td>" . displayFloat($averageChoices) . "</td>
    </tr>
</table>

<br>

<table>
    <tr>
        <th rowspan=\"2\">" . getString("stats_stat") . "</th>
        <th colspan=\"4\">" . getString("stats_last") . "</th>
        <th rowspan=\"2\">" . getString("stats_all") . "</th>
    </tr>
    <tr>
        <th>" . getString("stats_1d") . "</th>
        <th>" . getString("stats_1w") . "</th>
        <th>" . getString("stats_1mo") . "</th>
        <th>" . getString("stats_1y") . "</th>
    </tr>
    <tr><td colspan=\"6\">" . getString("stats_users") . "</td></tr>" . 
    displayStat($usersOnline) . 
    displayStat($usersCreated) . "
    <tr><td colspan=\"6\">" . getString("stats_predictions") . "</td></tr>" . 
    displayStat($predictionsCreated) . "
</table>";