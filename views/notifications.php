<?php
if(!isConnected()) redirect("home", "perms_connected");

include_once "time.js.php";

$notifications_unread = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 0 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);
$notifications_read = executeQuery("SELECT * FROM `notifications` WHERE `user` = ? AND `read` = 1 ORDER BY `sent` DESC;", [$_COOKIE["username"]]);

$i = 0;

/**
 * Retourne le nombre de jetons formaté (en utilisant la fonction displayInt et insertTextIcon)
 * @param int $chips Nombre de jetons
 * @return string Chaîne de caractères HTML
 */
function formatChips(int $chips): string{
	return "<b>" . displayInt($chips) . insertTextIcon("chips", "right", 1) . "</b>";
}

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
				<th>" . getString("general_time_elapsed") . "</th>
				<th>" . getString("notifications_text") . "</th>
			</tr>
		</thead>
		<tbody>";
	global $i;
	foreach($notifications as $notification){
		$sent = $notification["sent"];
		$text = $notification["text"];

		$notification_title = $text;
		$notification_desc = "";

		// Exemple : DELETED:152,REFUNDED:50 -> [["DELETED", "152"], ["REFUNDED", "50"]]
		$text_parts = explode(",", $text);
		for($j = 0; $j < count($text_parts); $j++) $notification[$j] = explode(":", $text_parts[$j]);

		switch($notification[0][0]){
			case "DAILY":
				$notification_title = getString("notifications_daily");
				if($notification[0][1] == "RESET"){
					$notification_desc = getString("notifications_daily_reset");
				}else{
					$chips = $notification[0][1] + 9; // Streak à 0 : donne 10 jetons, passage du streak à 1, la notification est alors "DAILY:1" et l'utilisateur a reçu 10 jetons (1+9)
					$notification_desc = getString("notifications_chips_won", [formatChips($chips)]);
				}
				break;

			case "APPROVED":
				$prediction_id = $notification[0][1];
				$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
				$notification_title = getString("notifications_approved");
				$notification_desc = "<a href=\"prediction/$prediction_id\">$prediction_title</a>";
				break;

			case "REJECTED":
				$notification_title = getString("notifications_rejected");
				$notification_desc = getString("notifications_rejected_desc", ["<b>" . $notification[0][1] . "</b>"]);
				break;

			case "RESOLVED":
				$prediction_id = $notification[0][1];
				$prediction_title = executeQuery("SELECT `title` FROM `predictions` WHERE `id` = ?;", [$prediction_id], "string");
				$outcome_id = $notification[1][1];
				$outcome_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$outcome_id], "string");
				if($notification[2][0] == "WON"){
					$selected_id = $outcome_id;
					$selected_title = $outcome_title;
					$chips = $notification[2][1];
					$chips_sentence = getString("notifications_chips_won", [formatChips($chips)]);
				}else{
					$selected_id = $notification[2][1];
					$selected_title = executeQuery("SELECT `name` FROM `choices` WHERE `id` = ?;", [$selected_id], "string");
					$chips = $notification[3][1];
					$chips_sentence = getString("notifications_chips_lost", [formatChips($chips)]);
				}
				$notification_title = "<a href=\"prediction/$prediction_id\">$prediction_title</a>";
				$notification_desc = 
					getString("notifications_resolved_selected") . " <b>$selected_title</b><br>" .
					getString("notifications_resolved_outcome") . " <b>$outcome_title</b><br>" .
					$chips_sentence;
				break;

			case "DELETED":
				$prediction_id = $notification[0][1];
				$chips = $notification[1][1];
				$notification_title = getString("notifications_deleted");
				$notification_desc = 
					getString("notifications_deleted_desc", ["<b>" . $prediction_id . "</b>"]) . "<br>" .
					getString("notifications_chips_refunded", [formatChips($chips)]);
				break;
		}
		$i++;
		$sent_td = "<span id=\"notification_$i\">" . $sent . "</span><script>display(\"$sent\",\"notification_$i\")</script>";
		$html .= "<tr>
			<td>$sent_td</td>
			<td><b>$notification_title</b><br>$notification_desc</td>
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