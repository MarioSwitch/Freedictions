<?php
$history = executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `ended` <= NOW() ORDER BY (`answer` IS NULL) DESC, COALESCE(`answered`, `ended`) DESC;");
echo "<h1>" . getString("title_history") . "</h1>";
echo displayPaginatedTable($history, "closed", ["title", "answer", "time"]);