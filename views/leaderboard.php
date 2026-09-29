<?php
$results_per_page = intval(getSetting("results_per_page"));
$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;
$offset = ($page_number - 1) * $results_per_page;

$leaderboard = executeQuery("SELECT `username`, `chips` FROM `users` ORDER BY `chips` DESC LIMIT $results_per_page OFFSET $offset;");
$users = executeQuery("SELECT COUNT(*) FROM `users`;", [], "int");

$table_top = ($page_number - 1) * $results_per_page + 1;
$table_bottom = $table_top + $results_per_page - 1;

$previous_page = $page_number - 1;
$previous_start = $table_top - $results_per_page;
$previous_end = $table_top - 1;

$next_page = $page_number + 1;
$next_start = $table_bottom + 1;
$next_end = min($table_bottom + $results_per_page, $users);

if(isConnected()){
	$my_username = $_COOKIE["username"];
	$my_chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$my_username], "int");
	$my_rank = executeQuery("SELECT COUNT(*) FROM `users` WHERE `chips` > ?;", [$my_chips], "int") + 1;
	$my_top = ($my_rank / $users) * 100;
	$my_page = ceil($my_rank / $results_per_page);
	$my_string = getString("leaderboard_desc", [displayRank($my_rank), displayInt($users), displayFloat($my_top, true)]);
}
?>
<h1><?= getString("title_leaderboard") ?></h1>
<p><?= isConnected() ? $my_string : "" ?></p>
<table class="users_list">
	<thead>
		<tr>
			<th><?= getString("general_rank") . "<br><small>" . displayInt($table_top, false) . " – " . displayInt($table_top + count($leaderboard) - 1, false) . "</small>" ?></th>
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
				$my_row = (isConnected() && $username == $my_username) ? "mine" : "";
				echo "<tr class=\"$my_row\">
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
				if($page_number >= 2) echo "<a href=\"leaderboard?page=$previous_page\">◄<br><small>" . displayInt($previous_start, false) . " – " . displayInt($previous_end, false) . "</small></a>";
				?>
			</td>
			<td>
				<?php
				if(isConnected()) echo "<a href=\"leaderboard?page=$my_page\">" . getString("leaderboard_page", [displayInt($my_page, false)]) . "</a>";
				?>
			</td>
			<td>
				<?php
				if($table_bottom < $users) echo "<a href=\"leaderboard?page=$next_page\">►<br><small>" . displayInt($next_start, false) . " – " . displayInt($next_end, false) . "</small></a>";
				?>
			</td>
		</tr>
	</tbody>
</table>