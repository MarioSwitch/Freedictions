<h1>Succès</h1>
<p>Ci-dessous, la liste des succès disponibles. Obtenir un succès affichera une icône à côté de votre pseudo sur tout le site !</p>
<hr>
<h2>Liste des succès</h2>
<?php 
    include_once "achievementsManager.php";
    if(isConnected()){
        //Streak
        $streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $streak_current_achievement = getCurrentAchievement($streak, $streak_achievements, "jours");
        $streak_next_achievement = getNextAchievement($streak, $streak_achievements, "jours");
        //Points
        $points = intSQL("SELECT `points` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]]);
        $points_current_achievement = getCurrentAchievement($points, $points_achievements, "points");
        $points_next_achievement = getNextAchievement($points, $points_achievements, "points");
    }
?>
<table>
    <tr>
        <th>Succès</th>
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
            generateStaticAchievementRow("calendar", $streak_achievements, "jours");
            if(isConnected()){
                echo "<td>" . $streak_current_achievement . "</td>";
                echo "<td>" . $streak_next_achievement . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
    <tr>
        <td>Points</td>
        <?php
            generateDynamicAchievementRow("points", $points_top, $points_achievements, "points");
            if(isConnected()){
                echo "<td>" . $points_current_achievement . "</td>";
                echo "<td>" . $points_next_achievement . "</td>";
            } else {
                echo "<td colspan='2'>" . displayInvite("voir votre progression") . "</td>";
            }
        ?>
    </tr>
</table>