<h1>Succès</h1>
<p>Ci-dessous, la liste des succès disponibles. Obtenir un succès affichera une icône à côté de votre pseudo sur tout le site !</p>
<hr>
<h2>Liste des succès</h2>
<?php 
    /** Get current achievement
     * @param int $stat the stat to check
     * @param array $tab the array of achievements
     * @return string the current achievement
    */
    function getCurrentAchievement(int $stat, array $tab, string $unit){
        if($stat < $tab[0]) return "-";
        if($stat < $tab[1]) return "Bronze<br>" . $tab[0] . " " . $unit;
        if($stat < $tab[2]) return "Argent<br>" . $tab[1] . " " . $unit;
        if($stat < $tab[3]) return "Or<br>" . $tab[2] . " " . $unit;
        if($stat >= $tab[3]) return "Arc-en-ciel<br>" . $stat . " / " .  $tab[3] . " " . $unit;
    }
    function getNextAchievement(int $stat, array $tab, string $unit){
        if($stat < $tab[0]) return "Bronze<br>" . $stat . " / " .  $tab[0] . " " . $unit;
        if($stat < $tab[1]) return "Argent<br>" . $stat . " / " .  $tab[1] . " " . $unit;
        if($stat < $tab[2]) return "Or<br>" . $stat . " / " .  $tab[2] . " " . $unit;
        if($stat < $tab[3]) return "Arc-en-ciel<br>" . $stat . " / " .  $tab[3] . " " . $unit;
        if($stat >= $tab[3]) return "Félicitations !";
    }
    $streak_achievements = array(7, 14, 30, 365);
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