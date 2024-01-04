<?php
//Arrays
$streak_achievements = array(7, 14, 30, 365);

//Functions
/** Get current achievement
 * @param int $stat the stat to check
 * @param array $tab the array of achievements
 * @param string $unit the unit of the stat
 * @return string the current achievement
*/
function getCurrentAchievement(int $stat, array $tab, string $unit){
    if($stat < $tab[0]) return "-";
    if($stat < $tab[1]) return "Bronze<br><small>" . displayInt($stat) . " / " . displayInt($tab[0]) . " " . $unit . "</small>";
    if($stat < $tab[2]) return "Argent<br><small>" . displayInt($stat) . " / " . displayInt($tab[1]) . " " . $unit . "</small>";
    if($stat < $tab[3]) return "Or<br><small>" . displayInt($stat) . " / " . displayInt($tab[2]) . " " . $unit . "</small>";
    if($stat >= $tab[3]) return "Arc-en-ciel<br><small>" . displayInt($stat) . " / " .  displayInt($tab[3]) . " " . $unit . "</small>";
    return "-";
}

/**
 * Get next achievement
 * @param int $stat the stat to check
 * @param array $tab the array of achievements
 * @param string $unit the unit of the stat
 * @return string the next achievement
 */
function getNextAchievement(int $stat, array $tab, string $unit){
    if($stat < $tab[0]) return "Bronze<br>Encore " . displayInt($tab[0] - $stat) . " " . $unit . "<br><small>(" . displayInt($stat) . " / " .  displayInt($tab[0]) . ")</small>";
    if($stat < $tab[1]) return "Argent<br>Encore " . displayInt($tab[1] - $stat) . " " . $unit . "<br><small>(" . displayInt($stat) . " / " .  displayInt($tab[1]) . ")</small>";
    if($stat < $tab[2]) return "Or<br>Encore " . displayInt($tab[2] - $stat) . " " . $unit . "<br><small>(" . displayInt($stat) . " / " .  displayInt($tab[2]) . ")</small>";
    if($stat < $tab[3]) return "Arc-en-ciel<br>Encore " . displayInt($tab[3] - $stat) . " " . $unit . "<br><small>(" . displayInt($stat) . " / " .  displayInt($tab[3]) . ")</small>";
    if($stat >= $tab[3]) return "Félicitations !";
    return "-";
}

/**
 * Generate achievement icon
 * @param int $value the current value
 * @param array $goals the array of goals
 * @param string $svg the SVG keyword (ex. "calendar" for "svg/achievements/calendarBronze.svg")
 * @param string $name the name in the tooltip (ex. "Jours" for "Jours : 7+" )
 * @return string the achievement icon
 */
function checkStaticAchievement(int $value, array $goals, string $svg, string $name){
    if($value < $goals[0]) return "";
    if($value < $goals[1]) return "<abbr title='" . $name . " : " . $goals[0] . "+'><img class='user-icon' src='svg/achievements/" . $svg . "Bronze.svg'></abbr>";
    if($value < $goals[2]) return "<abbr title='" . $name . " : " . $goals[1] . "+'><img class='user-icon' src='svg/achievements/" . $svg . "Silver.svg'></abbr>";
    if($value < $goals[3]) return "<abbr title='" . $name . " : " . $goals[2] . "+'><img class='user-icon' src='svg/achievements/" . $svg . "Gold.svg'></abbr>";
    if($value >= $goals[3]) return "<abbr title='" . $name . " : " . $goals[3] . "+'><img class='user-icon' src='svg/achievements/" . $svg . "Rainbow.svg'></abbr>";
    return "";
}
?>