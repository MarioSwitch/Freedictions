<?php
$query = array_key_exists("query", $_REQUEST) ? $_REQUEST["query"] : "";
$scope = array_key_exists("scope", $_REQUEST) ? $_REQUEST["scope"] : "opened";
?>
<h1><?= getString("title_search") ?></h1>
<form>
	<select name="scope" required="required">
		<option value="opened" <?= $scope == "opened" ? "selected=\"selected\"" : "" ?>><?= getString("predictions_opened") ?></option>
		<option value="closed" <?= $scope == "closed" ? "selected=\"selected\"" : "" ?>><?= getString("predictions_closed") ?></option>
		<option value="users" <?= $scope == "users" ? "selected=\"selected\"" : "" ?>><?= getString("general_users") ?></option>
	</select>
	<input type="text" name="query" id="query" required="required" value="<?= $query ?>" style="width:calc(var(--font-size) * 30);">
	<br>
	<button type="submit"><?= getString("search_search") ?></button>
</form>
<?php if($query != ""){
$results_per_page = intval(getSetting("results_per_page"));
$page_number = array_key_exists("page", $_REQUEST) ? intval($_REQUEST["page"]) : 1;
$offset = ($page_number - 1) * $results_per_page;

$results = match($scope){
	"opened" => executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `ended` > NOW() AND `title` LIKE ? ORDER BY `ended` ASC;", ["%$query%"]),
	"closed" => executeQuery("SELECT * FROM `predictions` WHERE `approved` = 1 AND `ended` <= NOW() AND `title` LIKE ? ORDER BY (`answer` IS NULL) DESC, COALESCE(`answered`, `ended`) DESC;", ["%$query%"]),
	"users" => executeQuery("SELECT * FROM `users` WHERE `username` LIKE ? ORDER BY `chips` DESC;", ["%$query%"]),
	default => []
};

$columns = match($scope){
	"opened" => ["title", "volume", "time"],
	"closed" => ["title", "answer", "time"],
	default => []
};

echo displayPaginatedTable($results, $scope, $columns);
} ?>