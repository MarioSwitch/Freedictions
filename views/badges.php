<?php
ob_start();
include_once "badgesManager.php";
echo "<h1>" . getString("badges_title") . "</h1>";
echo "<p>" . getString("badges_description") . "</p>";
echo "<hr>";
echo "<h2>" . getString("badges_available") . "</h2>";
echo "<table>
    <tr>
        <th>" . getString("badges_badge") . "</th>
        <th>" . getString("badges_bronze") . "</th>
        <th>" . getString("badges_silver") . "</th>
        <th>" . getString("badges_gold") . "</th>
        <th>" . getString("badges_diamond") . "</th>
        <th>" . getString("badges_level_current") . "</th>
        <th>" . getString("badges_level_next") . "</th>
    </tr>";
fullStaticBadgeRow("streak", "calendar", $streak_badges, "streak_unit", $streakCurrentBadgeLevel, $streakNextBadgeLevel);
fullDynamicBadgeRow("points", "points", $points_top, $points_badges, "points_unit", $pointsCurrentBadgeLevel, $pointsNextBadgeLevel);
fullDynamicBadgeRow("predictions_created", "predictionsCreated", $predictionsCreated_top, $predictionsCreated_badges, "predictions_unit", $predictionsCreatedCurrentBadgeLevel, $predictionsCreatedNextBadgeLevel);
fullDynamicBadgeRow("bets", "bets", $bets_top, $bets_badges, "bets_unit", $betsCurrentBadgeLevel, $betsNextBadgeLevel);
fullDynamicBadgeRow("points_spent", "pointsSpent", $pointsSpent_top, $pointsSpent_badges, "points_unit", $pointsSpentCurrentBadgeLevel, $pointsSpentNextBadgeLevel);
fullDynamicBadgeRow("bets_won", "betsWon", $betsWon_top, $betsWon_badges, "bets_unit", $betsWonCurrentBadgeLevel, $betsWonNextBadgeLevel);
echo "
    </tr>
</table>
<hr>
<h2>" . getString("badges_other") . "</h2>
<table>
    <tr>
        <th>" . getString("badges_badge") . "</th>
        <th>" . getString("badges_role") . "</th>
    </tr>";
displayExtraBadge("verified");
displayExtraBadge("mod");
displayExtraBadge("developer");
displayExtraBadge("translator");
echo "</table>";
ob_end_flush();