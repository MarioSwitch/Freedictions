<?php
if(isConnected()) executeQuery("UPDATE `users` SET `updated` = NOW() WHERE `username` = ?;", [$_COOKIE["username"]]);

echo "
<div id=\"header\">
	<div style=\"width:calc((100% - 10px) / 2); float:left; text-align:left; margin-left: 5px\">
		
		<a href=" . CONFIG_PATH . "/home><img src=\"svg/favicon.svg\">" . getString("site_name") . "</a>
		<a href=" . CONFIG_PATH . "/leaderboard><img src=\"svg/leaderboard.svg\"></a>
	</div>
	<div style=\"width:calc((100% - 10px) / 2); float:left; text-align:right; margin-right: 5px\">";
	if(!isConnected()){
		echo "
		<a href=" . CONFIG_PATH . "/signup><img src=\"svg/signup.svg\"></a>
		<a href=" . CONFIG_PATH . "/signin><img src=\"svg/signin.svg\"></a>";
	}else{
		$user = $_COOKIE["username"];
		$chips = executeQuery("SELECT `chips` FROM `users` WHERE `username` = ?;", [$user], "int");
		echo "
		$user
		<small>(" . displayInt($chips) . ")</small>
		<a href=" . CONFIG_PATH . "/user/$user><img src=\"svg/user.svg\"></a>
		<a href=" . CONFIG_PATH . "/signout><img src=\"svg/signout.svg\"></a>";
	}
echo "</div></div>";

if(!empty($_REQUEST["error"])){
	echo "<h1>" . getString("error_" . $_REQUEST["error"]) . "<br>" . getString("error_retry") . "</h1>";
}