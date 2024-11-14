<?php
if(isConnected()) executeQuery("UPDATE `users` SET `updated` = NOW() WHERE `username` = ?;", [$_COOKIE["username"]]);

echo "
    <div id=\"header\">
        <p>" . "[TODO]" . "</p>
    </div>
";