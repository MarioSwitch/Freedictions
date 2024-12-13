<?php
if(isConnected()) executeQuery("UPDATE `users` SET `updated` = NOW() WHERE `username` = ?;", [$_COOKIE["username"]]);

echo "
<div id=\"header\">
	<div style=\"width:calc((100% - 10px) / 2); float:left; text-align:left; margin-left:calc(var(--font-size) * 0.2);\">
		<a href=\"" . CONFIG_PATH . "/home\" style=\"float:left; margin-right:calc(var(--font-size) * 0.5);\">
			<img src=\"svg/favicon.svg\" style=\"float:left;\">
			<div style=\"display:inline-block; margin-left:calc(var(--font-size) * 0.2); text-align:center;\">
				<p style=\"font-size:calc(var(--font-size) * 0.5); margin:0;\">" . getString("site_name") . "</p>
				<p style=\"font-size:calc(var(--font-size) * 0.3); margin:0;\"><i>" . getString("site_desc") . "</i></p>
			</div>
		</a>
		<a href=" . CONFIG_PATH . "/leaderboard><img src=\"svg/leaderboard.svg\"></a>
		<a href=" . CONFIG_PATH . "/settings><img src=\"svg/settings.svg\"></a>
	</div>
	<div style=\"width:calc((100% - 10px) / 2); float:left; text-align:right; margin-right:calc(var(--font-size) * 0.2);\">";
	if(!isConnected()){
		echo "
		<a href=" . CONFIG_PATH . "/signup><img src=\"svg/signup.svg\"></a>
		<a href=" . CONFIG_PATH . "/signin><img src=\"svg/signin.svg\"></a>";
	}else{
		echo "<a href=\"" . CONFIG_PATH . "/create\" style=\"margin-right:calc(var(--font-size) * 0.5);\"><img src=\"svg/create.svg\"></a>";
		if(isMod()){
			$waiting_approval = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 0;", [], "int");
			$waiting_answer = executeQuery("SELECT COUNT(*) FROM `predictions` WHERE `approved` = 1 AND NOW() >= `ended` AND `answer` IS NULL;", [], "int");
			echo "<a href=\"" . CONFIG_PATH . "/modqueue\" style=\"margin-right:calc(var(--font-size) * 0.5);\">";
				echo "<img src=\"svg/modqueue.svg\">";
				echo "<div style=\"display:inline-block; margin-left:calc(var(--font-size) * 0.2); text-align:center;\">";
					echo "<p style=\"font-size:calc(var(--font-size) * 0.45); margin:0;color:red;\">" . ($waiting_approval ? displayInt($waiting_approval) : "") . "</p>";
					echo "<p style=\"font-size:calc(var(--font-size) * 0.35); margin:0;\">" . (($waiting_approval || $waiting_answer) ? displayInt($waiting_answer) : "") . "</p>";
				echo "</div>";
			echo "</a>";
		}
		$user = $_COOKIE["username"];
		$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$user], "int");
		echo "<a href=\"" . CONFIG_PATH . "/user/$user\" style=\"margin-right:calc(var(--font-size) * 0.5);\">";
			echo "<img src=\"svg/user.svg\">";
			echo "<div style=\"display:inline-block; margin-left:calc(var(--font-size) * 0.2); text-align:center;\">";
				echo "<p style=\"font-size:calc(var(--font-size) * 0.45); margin:0;\">" . displayUser($user) . "</p>";
				echo "<p style=\"font-size:calc(var(--font-size) * 0.35); margin:0;\">" . displayInt($chips) . insertTextIcon("chips", "right", 0.4) . "</p>";
			echo "</div>";
		echo "</a>";
		echo "<a href=" . CONFIG_PATH . "/signout><img src=\"svg/signout.svg\"></a>";
	}
echo "</div></div>";

if(!empty($_REQUEST["error"])){
	echo "<h1 style=\"color:darkred;\">" . getString("error_" . $_REQUEST["error"]) . "<br>" . getString("error_retry") . "</h1>";
}