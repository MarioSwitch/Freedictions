<h1>Badges</h1>
<p>Ci-dessous, la liste des badges disponibles. Obtenir un badge affichera une icône à côté de votre pseudo sur tout le site !</p>
<hr>
<h2>Badges à obtenir</h2>
<?php 
    include_once "badgesManager.php";
    if(isConnected()){
        //Streak
        $streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $streakCurrentBadgeLevel = getCurrentBadgeLevel($streak, $streak_badges, "jours");
        $streakNextBadgeLevel = getNextBadgeLevel($streak, $streak_badges, "jours");
        //Points
        $points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $pointsCurrentBadgeLevel = getCurrentBadgeLevel($points, $points_badges, "points");
        $pointsNextBadgeLevel = getNextBadgeLevel($points, $points_badges, "points");
        //Predictions created
        $predictionsCreated = intSQL("SELECT `predictionsCreated` FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `predictionsCreated` FROM `predictions` GROUP BY `user`) `predictions2` ON `users`.`username` = `predictions2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $predictionsCreatedCurrentBadgeLevel = getCurrentBadgeLevel($predictionsCreated, $predictionsCreated_badges, "prédictions");
        $predictionsCreatedNextBadgeLevel = getNextBadgeLevel($predictionsCreated, $predictionsCreated_badges, "prédictions");
        //Bets
        $bets = intSQL("SELECT `bets` FROM `users` LEFT JOIN (SELECT `user`, COUNT(*) AS `bets` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $betsCurrentBadgeLevel = getCurrentBadgeLevel($bets, $bets_badges, "mises");
        $betsNextBadgeLevel = getNextBadgeLevel($bets, $bets_badges, "mises");
        //Points spent
        $pointsSpent = intSQL("SELECT `pointsSpent` FROM `users` LEFT JOIN (SELECT `user`, SUM(`points`) AS `pointsSpent` FROM `votes` GROUP BY `user`) `votes2` ON `users`.`username` = `votes2`.`user` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $pointsSpentCurrentBadgeLevel = getCurrentBadgeLevel($pointsSpent, $pointsSpent_badges, "points");
        $pointsSpentNextBadgeLevel = getNextBadgeLevel($pointsSpent, $pointsSpent_badges, "points");
    }
?>
<table>
    <tr>
        <th>Badge</th>
        <th>Bronze</th>
        <th>Argent</th>
        <th>Or</th>
        <th>Diamant</th>
        <th>Palier actuel</th>
        <th>Palier suivant</th>
    </tr>
    <tr>
        <td>Série de connexion</td>
        <?php 
            generateStaticBadgeRow("calendar", $streak_badges, "jours");
            if(isConnected()){
                echo "<td>" . $streakCurrentBadgeLevel . "</td>";
                echo "<td>" . $streakNextBadgeLevel . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
    <tr>
        <td>Points</td>
        <?php
            generateDynamicBadgeRow("points", $points_top, $points_badges, "points");
            if(isConnected()){
                echo "<td>" . $pointsCurrentBadgeLevel . "</td>";
                echo "<td>" . $pointsNextBadgeLevel . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
    <tr>
        <td>Prédictions créées</td>
        <?php
            generateDynamicBadgeRow("predictionsCreated", $predictionsCreated_top, $predictionsCreated_badges, "prédictions");
            if(isConnected()){
                echo "<td>" . $predictionsCreatedCurrentBadgeLevel . "</td>";
                echo "<td>" . $predictionsCreatedNextBadgeLevel . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
    <tr>
        <td>Mises</td>
        <?php
            generateDynamicBadgeRow("bets", $bets_top, $bets_badges, "mises");
            if(isConnected()){
                echo "<td>" . $betsCurrentBadgeLevel . "</td>";
                echo "<td>" . $betsNextBadgeLevel . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
    <tr>
        <td>Points dépensés</td>
        <?php
            generateDynamicBadgeRow("pointsSpent", $pointsSpent_top, $pointsSpent_badges, "points");
            if(isConnected()){
                echo "<td>" . $pointsSpentCurrentBadgeLevel . "</td>";
                echo "<td>" . $pointsSpentNextBadgeLevel . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
</table>
<hr>
<h2>Autres badges</h2>
<table>
    <tr>
        <th>Badge</th>
        <th>Rôle</th>
    </tr>
    <tr>
        <td><img src="svg/mod.png" alt="Modérateur" title="Modérateur"></td>
        <td>Modérateur du site</td>
    </tr>
</table>