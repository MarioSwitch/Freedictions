<?php
if(!isConnected()) redirect("home", "perms_connected");

include_once "time.js.php";

$notifications_unread = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 0 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);
$notifications_read = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 1 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);

$i = 0;

/**
 * Affiche les notifications
 * @param mixed $notifications Notifications à afficher
 * @return string Tableau HTML des notifications
 */
function displayNotifications($notifications): string{
	if(!$notifications) return "<p>" . getString("notifications_none") . "</p>";
	$read = $notifications[0]["read"];
	$html = "<table class=\"notifications_list\">
		<thead>
			<tr>
				<th>" . getString("notifications_sent") . "</th>
				<th>" . getString("notifications_text") . "</th>
			</tr>
		</thead>
		<tbody>";
	global $i;
	foreach($notifications as $notification){
		$sent = $notification["sent"];
		$text = $notification["text"];
		$text_parts = explode(",", $text);
		$formatted_text = "";
		$verb = explode(":", $text_parts[0])[0];
		if($verb == "DAILY"){
			$info = explode(":", $text_parts[0])[1];
			if($info == "RESET"){
				$formatted_text = "<b>" . getString("notifications_daily_title") . "</b><br>" . getString("notifications_daily_reset");
			}else{
				$chips = $info + 9; // Streak à 0 : donne 10 jetons, passage du streak à 1, la notification est alors "DAILY:1" et l'utilisateur a reçu 10 jetons (1+9)
				$chips_formatted = displayInt($chips) . insertTextIcon("chips", "right", 1);
				$formatted_text = "<b>" . getString("notifications_daily_title") . "</b><br>" . getString("notifications_daily_desc", ["<b>" . $chips_formatted . "</b>"]);
			}
		}
		if($verb == "APPROVED"){
			$prediction_id = explode(":", $text_parts[0])[1];
			$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
			$formatted_text = "<b>" . getString("notifications_approved") . "</b><br><a href=\"prediction/$prediction_id\">$prediction_title</a>";
		}
		if($verb == "REJECTED"){
			$prediction_id = explode(":", $text_parts[0])[1];
			$formatted_text = "<b>" . getString("notifications_rejected_title"). "</b><br>" . getString("notifications_rejected_desc", ["<b>" . $prediction_id . "</b>"]);  
		}
		if($verb == "RESOLVED"){
			$prediction_id = explode(":", $text_parts[0])[1];
			$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
			$outcome_id = explode(":", $text_parts[1])[1];
			$outcome_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$outcome_id], "string");
			if(explode(":", $text_parts[2])[0] == "WON"){
				$chips = explode(":", $text_parts[2])[1];
				$chips_formatted = displayInt($chips) . insertTextIcon("chips", "right", 1);
				$formatted_text = "<b><a href=\"prediction/$prediction_id\">$prediction_title</a></b><br>" . getString("notifications_resolved_won", ["<b>" . $outcome_title . "</b>", "<b>" . $chips_formatted . "</b>"]);
			}else{
				$outcome_selected = explode(":", $text_parts[2])[1];
				$outcome_selected_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$outcome_selected], "string");
				$chips = explode(":", $text_parts[3])[1];
				$chips_formatted = displayInt($chips) . insertTextIcon("chips", "right", 1);
				$formatted_text = "<b><a href=\"prediction/$prediction_id\">$prediction_title</a></b><br>" . getString("notifications_resolved_lost", ["<b>" . $outcome_selected_title . "</b>", "<b>" . $outcome_title . "</b>","<b>" . $chips_formatted . "</b>"]);
			}
		}
		if($verb == "DELETED"){
			$prediction_id = explode(":", $text_parts[0])[1];
			$chips = explode(":", $text_parts[1])[1];
			$chips_formatted = displayInt($chips) . insertTextIcon("chips", "right", 1);
			$formatted_text = "<b>" . getString("notifications_deleted_title") . "</b><br>" . getString("notifications_deleted_desc", ["<b>" . $prediction_id . "</b>", "<b>" . $chips_formatted . "</b>"]);
		}
		$i++;
		$sent_td = "<span id=\"notification_$i\">" . $sent . "</span><script>display(\"$sent\",\"notification_$i\")</script>";
		$html .= "<tr>
			<td>$sent_td</td>
			<td>$formatted_text</td>
		</tr>";
	}
	$html .= "</tbody>
	</table>";
	if(!$read){
		$html .= "
		<form role=\"form\" action=\"controller.php\">
			<button type=\"submit\" name=\"action\" value=\"notifications_read\">" . getString("notifications_mark_as_read") . "</button>
		</form>";
	}else{
		$html .= "
		<form role=\"form\" action=\"controller.php\">
			<button type=\"submit\" name=\"action\" value=\"notifications_delete\">" . getString("notifications_delete") . "</button>
		</form>";
	}
	return $html;
}
?>
<h1><?= getString("title_notifications") . " (" . count($notifications_unread) + count($notifications_read) . ")" ?></h1>
<h2><?= getString("notifications_unread") . " (" . count($notifications_unread) . ")" ?></h2>
<?= displayNotifications($notifications_unread) ?>
<br>
<h2><?= getString("notifications_read") . " (" . count($notifications_read) . ")" ?></h2>
<?= displayNotifications($notifications_read) ?>