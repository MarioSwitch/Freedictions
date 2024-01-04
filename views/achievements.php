<h1>Succès</h1>
<p>Ci-dessous, la liste des succès disponibles. Obtenir un succès affichera une icône à côté de votre pseudo sur tout le site !</p>
<hr>
<h2>Liste des succès</h2>
<?php 
    include_once "achievementsManager.php";
    if(userConnected()){
        $streak = intSQL("SELECT `streak` FROM `users` WHERE `username` = ?;", [$_SESSION["user"]]);
        $streak_current_achievement = getCurrentAchievement($streak, $streak_achievements, "jours");
        $streak_next_achievement = getNextAchievement($streak, $streak_achievements, "jours");
    }
?>
<table>
    <tr>
        <th>Succès</th>
        <th>Bronze</th>
        <th>Argent</th>
        <th>Or</th>
        <th>Arc-en-ciel</th>
        <th>Palier actuel</th>
        <th>Palier suivant</th>
    </tr>
    <tr>
        <td>Série de connexion</td>
        <td><img src='svg/achievements/calendarBronze.svg' alt='Bronze' title='Bronze'><br><?php echo($streak_achievements[0]) ?> jours</td>
        <td><img src='svg/achievements/calendarSilver.svg' alt='Argent' title='Argent'><br><?php echo($streak_achievements[1]) ?> jours</td>
        <td><img src='svg/achievements/calendarGold.svg' alt='Or' title='Or'><br><?php echo($streak_achievements[2]) ?> jours</td>
        <td><img src='svg/achievements/calendarRainbow.svg' alt='Arc-en-ciel' title='Arc-en-ciel'><br><?php echo($streak_achievements[3]) ?> jours</td>
        <?php
            if(userConnected()){
                echo "<td>" . $streak_current_achievement . "</td>";
                echo "<td>" . $streak_next_achievement . "</td>";
            } else {
                echo "<td colspan='2'>Connectez-vous pour voir votre progression !</td>";
            }
        ?>
    </tr>
</table>