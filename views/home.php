<?php
$opened = executeQuery("SELECT `id`, `title`, `ended` FROM `predictions` WHERE `ended` > NOW() ORDER BY `ended` ASC;");
$count = count($opened);
echo "<h1>" . getString("predictions_opened") . " (" . $count . ")</h1>";
if($count == 0){
	echo "<p>" . getString("predictions_none") . "</p>";
}else{
	foreach($opened as $prediction){
		echo "<a href=\"" . CONFIG_PATH . "/prediction/" . $prediction["id"] . "\">";
		echo "<div class=\"prediction\">";
			echo "<div class=\"prediction_title\">";
				echo $prediction["title"];
			echo "</div>"; // prediction_title
			$volume = executeQuery("SELECT SUM(`points`) FROM `bets` WHERE `prediction` = ?;", [$prediction["id"]], "int");
			echo "<div class=\"prediction_choices\">";
				$choices_raw = executeQuery("SELECT `id`, `name` FROM `choices` WHERE `prediction` = ?;", [$prediction["id"]]);
				$choices_sorted = [];
				foreach($choices_raw as $choice){
					$points = executeQuery("SELECT SUM(`points`) FROM `bets` WHERE `prediction` = ? AND `choice` = ?;", [$prediction["id"], $choice["id"]], "int");
					$percentage = $volume==0 ? "–" : $points/$volume*100;
					array_push($choices_sorted, ["name" => $choice["name"], "percentage" => $percentage]);
				}
				usort($choices_sorted, function($a, $b){
					return $b["percentage"] - $a["percentage"];
				});
				$choices_count = 0;
				foreach($choices_sorted as $choice){
					echo "<div class=\"prediction_choice\">";
						if($choices_count++ >= 3){
							echo "<small>" . getString("view_all_choices", [count($choices_raw)]) . "</small></div>";
							break;
						}
						echo "<div class=\"prediction_choice_name\">";
							echo "<small>" . $choice["name"] . "</small>";
						echo "</div>"; // prediction_choice_name
						echo "<div class=\"prediction_choice_percentage\">";
							echo "<small>" . displayFloat($choice["percentage"], true) . "</small>";
						echo "</div>"; // prediction_choice_percentage
					echo "</div>"; // prediction_choice
				}
			echo "</div>"; // prediction_choices
			echo "<div class=\"prediction_stats\">";
				echo "<div class=\"prediction_volume\">";
					echo "<abbr title=\"" . getString("chips_bet") . "\"><img style=\"width: 20; height: 20; margin-right: 3px; vertical-align: bottom;\" src=\"svg/chips.svg\" alt=\"" . getString("chips_bet") . "\"></abbr>";
					echo "<small>" . displayInt($volume) . "</small>";
				echo "</div>"; // prediction_volume
				echo "<div class=\"prediction_time\">";
					echo "<small>" . $prediction["ended"] . "</small>";
					echo "<abbr title=\"" . getString("time_left") . "\"><img style=\"width: 20; height: 20; margin-left: 3px; vertical-align: bottom;\" src=\"svg/clock.svg\" alt=\"" . getString("time_left") . "\"></abbr>";
				echo "</div>"; // prediction_time
			echo "</div>"; // prediction_stats
		echo "</div>"; // prediction
		echo "</a>";
	}
}