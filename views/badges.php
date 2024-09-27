<?php
ob_start();
    include_once "badgesManager.php";
    echo "<h1>" . getString("badges_title") . "</h1>";
    echo "<p>" . getString("badges_description") . "</p>";
    echo "<hr>";
    echo "<h2>" . getString("badges_available") . "</h2>";
    if(isConnected()){
        //Streak
        $streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $streakCurrentBadgeLevel = getCurrentBadgeLevel($streak, $streak_badges, getString("streak_unit"));
        $streakNextBadgeLevel = getNextBadgeLevel($streak, $streak_badges, getString("streak_unit"));
        //Points
        $points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $pointsCurrentBadgeLevel = getCurrentBadgeLevel($points, $points_badges, getString("points_unit"));
        $pointsNextBadgeLevel = getNextBadgeLevel($points, $points_badges, getString("points_unit"));
        //Predictions created
        $predictionsCreated = intSQL("SELECT `predictionsCreated` FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `predictionsCreated` FROM `predictions` GROUP BY `user`) `predictions2` ON `users`.`username` = `predictions2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $predictionsCreatedCurrentBadgeLevel = getCurrentBadgeLevel($predictionsCreated, $predictionsCreated_badges, getString("predictions_unit"));
        $predictionsCreatedNextBadgeLevel = getNextBadgeLevel($predictionsCreated, $predictionsCreated_badges, getString("predictions_unit"));
        //Bets
        $bets = intSQL("SELECT `bets` FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `bets` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $betsCurrentBadgeLevel = getCurrentBadgeLevel($bets, $bets_badges, getString("bets_unit"));
        $betsNextBadgeLevel = getNextBadgeLevel($bets, $bets_badges, getString("bets_unit"));
        //Points spent
        $pointsSpent = intSQL("SELECT `pointsSpent` FROM `users` LEFT JOIN (SELECT `user`, SUM(`points`) AS `pointsSpent` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $pointsSpentCurrentBadgeLevel = getCurrentBadgeLevel($pointsSpent, $pointsSpent_badges, getString("points_unit"));
        $pointsSpentNextBadgeLevel = getNextBadgeLevel($pointsSpent, $pointsSpent_badges, getString("points_unit"));
        //Bets won
        $betsWon = intSQL("SELECT `correct_vote_count` FROM `users` LEFT JOIN (SELECT votes.user, COUNT(*) AS correct_vote_count FROM votes JOIN predictions ON votes.prediction = predictions.id WHERE votes.choice = predictions.answer GROUP BY votes.user) correct_votes ON users.username = correct_votes.user WHERE `username` = ?;", [$_COOKIE["username"]]);
        $betsWonCurrentBadgeLevel = getCurrentBadgeLevel($betsWon, $betsWon_badges, getString("bets_unit"));
        $betsWonNextBadgeLevel = getNextBadgeLevel($betsWon, $betsWon_badges, getString("bets_unit"));
    }
    function displayInviteShowProgress(){
        echo "<td colspan='2'>" . displayInvite(getString("invite_action_show_progress")) . "</td>";
    }
echo "<table>
    <tr>
        <th>" . getString("badges_badge") . "</th>
        <th>" . getString("badges_bronze") . "</th>
        <th>" . getString("badges_silver") . "</th>
        <th>" . getString("badges_gold") . "</th>
        <th>" . getString("badges_diamond") . "</th>
        <th>" . getString("badges_level_current") . "</th>
        <th>" . getString("badges_level_next") . "</th>
    </tr>
    <tr>
        <td>" . getString("streak") . "</td>";
        generateStaticBadgeRow("calendar", $streak_badges, getString("streak_unit"));
        if(isConnected()){
            echo "<td>" . $streakCurrentBadgeLevel . "</td>";
            echo "<td>" . $streakNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
    <tr>
        <td>" . getString("points") . "</td>";
        generateDynamicBadgeRow("points", $points_top, $points_badges, getString("points_unit"));
        if(isConnected()){
            echo "<td>" . $pointsCurrentBadgeLevel . "</td>";
            echo "<td>" . $pointsNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
    <tr>
        <td>" . getString("predictions_created") . "</td>";
        generateDynamicBadgeRow("predictionsCreated", $predictionsCreated_top, $predictionsCreated_badges, getString("predictions_unit"));
        if(isConnected()){
            echo "<td>" . $predictionsCreatedCurrentBadgeLevel . "</td>";
            echo "<td>" . $predictionsCreatedNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
    <tr>
        <td>" . getString("bets") . "</td>";
        generateDynamicBadgeRow("bets", $bets_top, $bets_badges, getString("bets_unit"));
        if(isConnected()){
            echo "<td>" . $betsCurrentBadgeLevel . "</td>";
            echo "<td>" . $betsNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
    <tr>
        <td>" . getString("points_spent") . "</td>";
        generateDynamicBadgeRow("pointsSpent", $pointsSpent_top, $pointsSpent_badges, getString("points_unit"));
        if(isConnected()){
            echo "<td>" . $pointsSpentCurrentBadgeLevel . "</td>";
            echo "<td>" . $pointsSpentNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
    <tr>
        <td>" . getString("bets_won") . "</td>";
        generateDynamicBadgeRow("betsWon", $betsWon_top, $betsWon_badges, getString("bets_unit"));
        if(isConnected()){
            echo "<td>" . $betsWonCurrentBadgeLevel . "</td>";
            echo "<td>" . $betsWonNextBadgeLevel . "</td>";
        } else {
            displayInviteShowProgress();
        }
echo "
    </tr>
</table>
<hr>
<h2>" . getString("badges_other") . "</h2>
<table>
    <tr>
        <th>" . getString("badges_badge") . "</th>
        <th>" . getString("badges_role") . "</th>
    </tr>
    <tr>
        <td><img src=\"svg/verified.svg\" alt=\"" . getString("verified") . "\" title=\"" . getString("verified") . "\"></td>
        <td>" . getString("verified") . "</td>
    </tr>
    <tr>
        <td><img src=\"svg/mod.svg\" alt=\"" . getString("mod") . "\" title=\"" . getString("mod") . "\"></td>
        <td>" . getString("mod") . "</td>
    </tr>
    <tr>
        <td><img src=\"svg/developer.svg\" alt=\"" . getString("developer") . "\" title=\"" . getString("developer") . "\"></td>
        <td>" . getString("developer") . "</td>
    </tr>
    <tr>
        <td><img src=\"svg/translator.svg\" alt=\"" . getString("translator") . "\" title=\"" . getString("translator") . "\"></td>
        <td>" . getString("translator") . "</td>
    </tr>
</table>";
ob_end_flush();