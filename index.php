<?

$db = new SQLite3('minutes.sqlite');

?>

<!DOCTYPE html>
<html><head>

<title>Minutes Data Entry</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" media="screen" href="style.css">
</head>
<body>
<h1>Minutes Data Entry</h1>

<?

if(isset($_POST['update_form'])){
	if(isset($_POST['update'])) {
		$related = ' ';
		if(isset($_POST['related'])){
			$arr = $_POST['related'];
			foreach ($arr as $x) {
				$related = $related . $x . ' ';
			}
		}
		$results = $db->exec('update items set related = \'' . $related . '\' where id = ' . $_POST['item_id']);
	}
	if(isset($_POST['next']) || isset($_POST['update'])){
		$item_id = $_POST['item_id'] + 1;
	} else if(isset($_POST['prev'])){
		$item_id = $_POST['item_id'] - 1;
	} else {
		$item_id = 1;
	}
	$meeting_id = $_POST['meeting_id'];
	if(isset($_POST['next_meeting'])){
		$meeting_id = $meeting_id + 1;
		$results = $db->query('select * from items where meeting_id = ' . ($meeting_id + 1));
		$row = $results->fetchArray();
		if ($row == FALSE) {
			$meeting_id = $meeting_id - 1;
			$item_id = $_POST['item_id'];
		}
	}
	if(isset($_POST['prev_meeting'])){
		if($meeting_id > 1){
			$meeting_id = $meeting_id - 1;
		}
	}
} else {
	$meeting_id = 1;
	$item_id = 1;
}
?>

<form method="post">
<input type="hidden" name="update_form" value="update"/>
<input type="hidden" name="meeting_id" value="<? echo($meeting_id);?>"/>

<?
$results = $db->query('select * from meetings where id = ' . $meeting_id);
$row = $results->fetchArray();
echo("<p>Meeting ID: " . $meeting_id . "<br/>");
echo("Group: " . $row['group_name'] . "<br/>");
echo("Meeting Title: " . $row['title'] . "<br/>");
echo("Date: " . $row['date'] . "<br/>");
echo("Called By: " . $row['caller'] . "<br/>");
echo("Participants: " . $row['participants'] . "</p>");
?>

<input type="submit" name="prev_meeting" value="<"/><input type="submit" name="next_meeting" value=">"/>

<?
$results = $db->query('select * from items where meeting_id = ' . $meeting_id
		      . ' order by id asc');

$line = 0;
echo("<table><tr><th>Related</th><th>ID</th><th>Topic</th><th>Item</th><th>Action</th><th>Person</th></tr>");
while ($row = $results->fetchArray()) {
	echo("<tr>\n");
	echo("<td><input name=\"related[]\" value=\"" . $row['id'] . "\" type=\"checkbox\"></td>\n");
	echo("<td>" . $row['id'] . "</td>\n");
	echo("<td>" . $row['topic'] . "</td>\n");
	echo("<td>" . $row['item'] . "</td>\n");
	echo("<td>" . $row['action'] . "</td>\n");
	echo("<td>" . $row['person'] . "</td>\n");
	echo("</tr>\n");
}
echo("</table>");

$meeting_id = $meeting_id + 1;

$results = $db->query('select * from meetings where id = ' . $meeting_id);
$row = $results->fetchArray();
echo("<p>Meeting ID: " . $meeting_id . "<br/>");
echo("Group: " . $row['group_name'] . "<br/>");
echo("Meeting Title: " . $row['title'] . "<br/>");
echo("Date: " . $row['date'] . "<br/>");
echo("Called By: " . $row['caller'] . "<br/>");
echo("Participants: " . $row['participants'] . "</p>");

$results = $db->query('select * from items where meeting_id = ' . $meeting_id
		      . ' and id >= ' . $item_id . ' order by id asc');
echo("<table><tr><th>ID</th><th>Topic</th><th>Item</th><th>Action</th><th>Person</th></tr>");

$row = $results->fetchArray();
if($row == FALSE) {
	$item_id = $item_id - 1;
	$results = $db->query('select * from items where meeting_id = ' . $meeting_id
			      . ' and id >= ' . $item_id . ' order by id asc');
	$row = $results->fetchArray();
}
	echo("<tr>\n");
	echo("<td>" . $row['id'] . "</td>\n");
	echo("<td>" . $row['topic'] . "</td>\n");
	echo("<td>" . $row['item'] . "</td>\n");
	echo("<td>" . $row['action'] . "</td>\n");
	echo("<td>" . $row['person'] . "</td>\n");
	echo("</tr>\n");
echo("</table>");
?>
<input type="hidden" name="item_id" value="<?echo($row['id']);?>"/>
<input type="submit" name="prev" value="<"/><input type="submit" name="update" value="Update"/><input type="submit" name="next" value=">"/>
</form>

</body>
</html>
