<?php
if(isConnected()) executeQuery("UPDATE `users` SET `updated` = NOW() WHERE `username` = ?;", [$_COOKIE["username"]]);

echo "
<header>
	<div>
		<a href=\"" . CONFIG_PATH . "/home\">
			<!-- <img src=\"svg/favicon.svg\"> -->
			<div>
				<p>" . getString("site_name") . "</p>
				<p><i>" . getString("site_desc") . "</i></p>
			</div>
		</a>
		<a href=" . CONFIG_PATH . "/search><img src=\"svg/search.svg\"></a>
		<a href=" . CONFIG_PATH . "/history><img src=\"svg/history.svg\"></a>
		<a href=" . CONFIG_PATH . "/leaderboard><img src=\"svg/leaderboard.svg\"></a>
		<a href=" . CONFIG_PATH . "/settings><img src=\"svg/settings.svg\"></a>
	</div>
	<div>";
	if(!isConnected()){
		echo "
		<a href=" . CONFIG_PATH . "/signup><img src=\"svg/signup.svg\"></a>
		<a href=" . CONFIG_PATH . "/signin><img src=\"svg/signin.svg\"></a>";
	}else{
		if(isAuthorized(NULL, "modqueue_access", NULL)){
			$waiting_approval = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 0;", [], "int");

			$waiting_answer = match(executeQuery("SELECT `mod` FROM `users` WHERE `username` = ?;", [$_COOKIE["username"]], "int")){
				3 => executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL;", [], "int"),
				2 => executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL AND `user` IN (SELECT `username` FROM `users` WHERE `mod` <= ?);", [2], "int"),
				1 => executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL AND `user` IN (SELECT `username` FROM `users` WHERE `mod` <= ?);", [1], "int"),
				default => []
			};

			echo "<a href=\"" . CONFIG_PATH . "/modqueue\">";
				echo "<img src=\"svg/modqueue.svg\">";
				echo "<div>";
					echo "<p class=\"red\">" . ($waiting_approval ? displayInt($waiting_approval) : "") . "</p>";
					echo "<p>" . (($waiting_approval || $waiting_answer) ? displayInt($waiting_answer) : "") . "</p>";
				echo "</div>";
			echo "</a>";
		}
		$user = $_COOKIE["username"];
		$notifications_total = executeQuery("SELECT COUNT(*) FROM `notifications` WHERE `user` = ?;", [$user], "int");
		$notifications_unread = executeQuery("SELECT COUNT(*) FROM `notifications` WHERE `user` = ? AND `read` = 0;", [$user], "int");
		echo "<a href=\"" . CONFIG_PATH . "/notifications\">";
			echo "<img src=\"svg/notifications.svg\">";
			echo "<div>";
				echo "<p class=\"red\">" . ($notifications_unread ? displayInt($notifications_unread) : "") . "</p>";
				echo "<p>" . ($notifications_total ? displayInt($notifications_total) : "") . "</p>";
			echo "</div>";
		echo "</a>";
		$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$user], "int");
		echo "<a href=\"" . CONFIG_PATH . "/user/$user\">";
			echo "<img src=\"svg/user.svg\">";
			echo "<div>";
				echo "<p>" . displayUser($user) . "</p>";
				echo "<p>" . displayInt($chips) . insertTextIcon("chips", "right", 0.4) . "</p>";
			echo "</div>";
		echo "</a>";
		echo "<a href=\"" . CONFIG_PATH . "/create\"><img src=\"svg/create.svg\"></a>
			<a href=\"" . CONFIG_PATH . "/signout\"><img src=\"svg/signout.svg\"></a>";
	}
	echo "
	</div>
</header>";