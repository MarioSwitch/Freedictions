<?php
$count = intSQL("SELECT COUNT(*) FROM `predictions`");
echo "<h1>" . getString("allPredictions_title") . "</h1>";
echo "<p>" . getString("allPredictions_description", [displayInt($count)]) . "</p>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_ongoing") . " (" . displayInt($count) . ")</h2>";
echo "<table><tr>";
    echo "<th>" . getString("id") . "</th>";
    echo "<th>" . getString("title") . "</th>";
    echo "<th>" . getString("creator") . "</th>";
    echo "<th>" . getString("created") . " (UTC)</th>";
    echo "<th>" . getString("bets_end") . " (UTC) ▲</th>";
echo "</tr>";
if(!$predictions){
    echo "<tr><td colspan='5'>" . getString("predictions_none") . "</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td></tr>";
    }
}
echo "</table>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended` FROM `predictions` WHERE `ended` < NOW() AND `answer` IS NULL ORDER BY `ended` ASC;");
$count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_waiting") . " (" . displayInt($count) . ")</h2>";
echo "<table><tr>";
    echo "<th>" . getString("id") . "</th>";
    echo "<th>" . getString("title") . "</th>";
    echo "<th>" . getString("creator") . "</th>";
    echo "<th>" . getString("created") . " (UTC)</th>";
    echo "<th>" . getString("bets_end") . " (UTC) ▲</th>";
echo "</tr>";
if(!$predictions){
    echo "<tr><td colspan='5'>" . getString("predictions_none") . "</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td></tr>";
    }
}
echo "</table>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title`, `user`, `created`, `ended`, `answer` FROM `predictions` WHERE `answer` IS NOT NULL ORDER BY `ended` DESC;");
$count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_ended") . " (" . displayInt($count) . ")</h2>";
echo "<table><tr>";
    echo "<th>" . getString("id") . "</th>";
    echo "<th>" . getString("title") . "</th>";
    echo "<th>" . getString("creator") . "</th>";
    echo "<th>" . getString("created") . " (UTC)</th>";
    echo "<th>" . getString("bets_end") . " (UTC) ▼</th>";
    echo "<th>" . getString("answer") . "</th>";
echo "</tr>";
if(!$predictions){
    echo "<tr><td colspan='6'>" . getString("predictions_none") . "</td></tr>";
}else{
    for($i = 0; $i < $count; $i++){
        $link_prediction = "?view=prediction&id=" . $predictions[$i]["id"];
        $link_user = "?view=profile&user=" . $predictions[$i]["user"];
        $id = $predictions[$i]["id"];
        $title = $predictions[$i]["title"];
        $user = $predictions[$i]["user"];
        $created = $predictions[$i]["created"];
        $ended = $predictions[$i]["ended"];
        $answer = stringSQL("SELECT `name` FROM `choices` WHERE `id`=?;", [$predictions[$i]["answer"]]);
        echo "<tr><td>" . $id . "</td><td><p><a href=\"" . $link_prediction . "\">". $title . "</a></p></td><td><p><a href=\"" . $link_user . "\">" . displayUsername($user) . "</a></p></td><td>" . $created . "</td><td>" . $ended . "</td><td>" . $answer . "</td></tr>";
    }
}
echo "</table>";