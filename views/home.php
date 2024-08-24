<?php
if(array_key_exists("error",$_REQUEST)){
    echo "<p class='error'>";
    switch($_REQUEST["error"]){
        case "forbidden":
            echo getString("error_forbidden");
            break;
        default:
            echo getString("error_default");
            break;
    }
    echo "</p>";
}
echo "<h1>" . getString("site_name") . "</h1>";
echo "<hr>";
$predictions = arraySQL("SELECT `id`, `title` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$predictions_count = $predictions?count($predictions):0;
echo "<h2>" . getString("predictions_ongoing", [$predictions_count]) . "</h2>";
if(!$predictions){
    echo "<p>" . getString("predictions_none") . "</p>";
    die("");
}
for($i = 0; $i < count($predictions); $i++){
    $id = $predictions[$i]["id"];
    $title = $predictions[$i]["title"];
    echo "<a href='?view=prediction&id=" . $id . "'>" . $title . "</a>";
}