<?php
$notifications_unread = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 0 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);
$notifications_read = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 1 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);

echo "<h1>" . getString("title_notifications") . "</h1>";
echo "<h2>" . getString("notifications_unread") . "</h2>";
echo displayPaginatedTable($notifications_unread, "notifications");
if($notifications_unread){
	echo "
		<form role=\"form\" action=\"controller.php\">
			<button type=\"submit\" name=\"action\" value=\"notifications_read\">" . getString("notifications_mark_as_read") . "</button>
		</form>";
}
echo "<br><br>";
echo "<h2>" . getString("notifications_read") . "</h2>";
echo displayPaginatedTable($notifications_read, "notifications");
if($notifications_read){
	echo "
		<form role=\"form\" action=\"controller.php\">
			<button type=\"submit\" name=\"action\" value=\"notifications_delete\">" . getString("notifications_delete") . "</button>
		</form>";
}