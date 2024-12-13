<?php
$results_per_page = array_key_exists("results", $_REQUEST) ? intval($_REQUEST["results"]) : 50;
$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;

$table_top = ($page_number - 1) * $results_per_page + 1;
$table_bottom = $table_top + $results_per_page - 1;

if(!is_numeric($results_per_page) || !is_numeric($page_number) || $results_per_page < 1 || $page_number < 1) redirect("leaderboard");

$leaderboard = executeQuery("SELECT `username`, `chips` FROM `users` ORDER BY `chips` DESC LIMIT $results_per_page OFFSET " . ($page_number - 1) * $results_per_page . ";");
$users = executeQuery("SELECT COUNT(*) FROM `users`;", [], "int");

if(isConnected()){
	$my_username = $_COOKIE["username"];
	$my_chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$my_username], "int");
	$my_rank = executeQuery("SELECT COUNT(*) FROM `users` WHERE `chips` > ?;", [$my_chips], "int") + 1;
	$my_top = ($my_rank / $users) * 100;
	$my_page = floor(($my_rank - 1) / $results_per_page) + 1;
	$my_string = getString("leaderboard_desc", [displayRank($my_rank), displayInt($users), displayFloat($my_top, true)]);
}
?>
<h1><?= getString("title_leaderboard") ?></h1>
<p><?= isConnected() ? $my_string : "" ?></p>
<table class="users_list">
	<thead>
		<tr>
			<th><?= getString("general_rank") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_top + count($leaderboard) - 1) . "</small>" ?></th>
			<th><?= getString("general_user") ?></th>
			<th><?= getString("general_chips") ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if(!$leaderboard) echo "<tr><td colspan='3'>" . getString("general_user_none") . "</td></tr>";
		else{
			foreach($leaderboard as $user){
				$username = $user["username"];
				$chips = $user["chips"];
				$rank = executeQuery("SELECT COUNT(*) FROM `users` WHERE `chips` > ?;", [$chips], "int") + 1;
				echo "<tr>
					<td>" . displayRank($rank) . "</td>
					<td>" . displayUser($username, true) . "</td>
					<td>" . displayInt($chips) . "</td>
				</tr>";
			}
		}
		?>
		<tr>
			<td>
				<?php
				if($page_number >= 2) echo "<a href=\"leaderboard?results=$results_per_page&page=" . ($page_number - 1) . "\">◄<br><small>" . $table_top - $results_per_page . " – " . $table_top - 1 . "</small>	</a>";
				?>
			</td>
			<td>
				<?php
				if(isConnected()) echo "<a href=\"leaderboard?results=$results_per_page&page=$my_page\">" . getString("leaderboard_page", [$my_page]) . "</a>";
				?>
			</td>
			<td>
				<?php
				if($table_bottom < $users) echo "<a href=\"leaderboard?results=$results_per_page&page=" . ($page_number + 1) . "\">►<br><small>" . $table_bottom + 1 . " – " . $table_bottom + $results_per_page . "</small></a>";
				?>
			</td>
		</tr>
	</tbody>
</table>