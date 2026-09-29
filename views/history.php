<?php
$results_per_page = intval(getSetting("results_per_page"));
$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;
$offset = ($page_number - 1) * $results_per_page;

$history = executeQuery("SELECT * FROM `predictions` WHERE `ended` <= NOW() ORDER BY (`answer` IS NULL) DESC, COALESCE(`answered`, `ended`) DESC LIMIT $results_per_page OFFSET $offset;");
$results = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `ended` <= NOW();", [], "int");

$table_top = ($page_number - 1) * $results_per_page + 1;
$table_bottom = $table_top + $results_per_page - 1;

$previous_page = $page_number - 1;
$previous_start = $table_top - $results_per_page;
$previous_end = $table_top - 1;

$next_page = $page_number + 1;
$next_start = $table_bottom + 1;
$next_end = min($table_bottom + $results_per_page, $results);
?>
<h1><?= getString("title_history") ?></h1>
<table class="predictions_list">
	<thead>
		<tr>
			<th><?= getString("general_rank") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_top + count($history) - 1, false) . "</small>" ?></th>
			<th><?= getString("prediction_question") ?></th>
			<th><?= getString("prediction_outcome") ?></th>
			<th><?= getString("general_time_elapsed") ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if(!$history) echo "<tr><td colspan='4'>" . getString("predictions_none") . "</td></tr>";
		else{
			foreach($history as $prediction){
				$id = $prediction["id"];
				$question = $prediction["title"];

				if($prediction["answer"]){
					$answer = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$prediction["answer"]], "string");
					$answered = $prediction["answered"];
					$unanswered_count = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `ended` <= NOW() AND `answer` IS NULL;", [], "int");
					$rank = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `ended` <= NOW() AND `answer` IS NOT NULL AND `answered` > ?;", [$answered], "int") + $unanswered_count + 1;
				}else{
					$answer = getString("prediction_waiting_outcome");
					$answered = $prediction["ended"];
					$rank = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `ended` <= NOW() AND `answer` IS NULL AND `ended` > ?;", [$answered], "int") + 1;
				}

				echo "<tr" . ($prediction["answer"] ? "" : " class=\"unanswered\"") . ">
					<td>" . displayRank($rank) . "</td>
					<td><a href=\"prediction/$id\">$question</a></td>
					<td>" . $answer . "</td>
					<td><abbr id=\"$id\">$answered</abbr></td><script>display(\"$answered\",\"$id\")</script>
				</tr>";
			}
		}
		?>
		<tr>
			<td>
				<?php
				if($page_number >= 2) echo "<a href=\"history?page=$previous_page\">◄<br><small>" . displayInt($previous_start, false) . " – " . displayInt($previous_end, false) . "</small></a>";
				?>
			</td>
			<td></td>
			<td></td>
			<td>
				<?php
				if($table_bottom < $results) echo "<a href=\"history?page=$next_page\">►<br><small>" . displayInt($next_start, false) . " – " . displayInt($next_end, false) . "</small></a>";
				?>
			</td>
		</tr>
	</tbody>
</table>