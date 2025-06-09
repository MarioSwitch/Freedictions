<?php
include_once "time.js.php";

$results_per_page = array_key_exists("results", $_REQUEST) ? intval($_REQUEST["results"]) : 50;
$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;

$table_top = ($page_number - 1) * $results_per_page + 1;
$table_bottom = $table_top + $results_per_page - 1;

if(!is_numeric($results_per_page) || !is_numeric($page_number) || $results_per_page < 1 || $page_number < 1) redirect("history");

$history = executeQuery("SELECT * FROM `predictions` WHERE `answered` IS NOT NULL ORDER BY `answered` DESC LIMIT $results_per_page OFFSET " . ($page_number - 1) * $results_per_page . ";");
$results = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `answered` IS NOT NULL;", [], "int");
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
				$answer = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$prediction["answer"]], "string");
				$answered = $prediction["answered"];
				$rank = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `answered` IS NOT NULL AND `answered` > ?;", [$answered], "int") + 1;
				$answered_td = "<td id=\"$id\">$answered</td><script>display(\"$answered\",\"$id\")</script>";
				echo "<tr>
					<td>" . displayRank($rank) . "</td>
					<td><a href=\"prediction/$id\">$question</a></td>
					<td>" . $answer . "</td>
					" . $answered_td . "
				</tr>";
			}
		}
		?>
		<tr>
			<td>
				<?php
				if($page_number >= 2) echo "<a href=\"history?results=$results_per_page&page=" . ($page_number - 1) . "\">◄<br><small>" . $table_top - $results_per_page . " – " . $table_top - 1 . "</small>	</a>";
				?>
			</td>
			<td></td>
			<td></td>
			<td>
				<?php
				if($table_bottom < $results) echo "<a href=\"history?results=$results_per_page&page=" . ($page_number + 1) . "\">►<br><small>" . $table_bottom + 1 . " – " . $table_bottom + $results_per_page . "</small></a>";
				?>
			</td>
		</tr>
	</tbody>
</table>
<br><br>
