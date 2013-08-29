<?php
# create the db object
$db = new SQLite3('minutes.sqlite');

?>

<!DOCTYPE html>
<html><head>

<title>Filtering Minutes Data Entry</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" media="screen" href="style.css">
</head>
<body>
<h1>Filtering Minutes Data Entry for importance</h1>

<?php
/* get the meeting id tags ready */
$results = $db->query('select max(id) from meetings');
$row = $results->fetchArray();
$meeting_id_max = $row[0];

# select the meeting to display
if(isset($_POST['update_form'])){
	if(isset($_POST['update'])) {
		if(isset($_POST['importance'])){
			$arr = $_POST['importance'];
			foreach ($arr as $x) {
				$results = $db->exec('update items set importance = 1 where id = ' . $x);
			}
		}
	}
	$meeting_id = $_POST['meeting_id'];
	if(isset($_POST['skip'])) {
		if(isset($_POST['category'])){
			$arr = $_POST['category'];
			foreach ($arr as $x) {
				$results = $db->exec('update meetings set category = 1 where id = ' . $meeting_id);
			}
		} else {
			$results = $db->exec('update meetings set category = 0 where id = ' . $meeting_id);
		}
	}

	if(isset($_POST['next_meeting'])){
		$meeting_id_next = $meeting_id;
		do {
			$meeting_id_next = $meeting_id_next + 1;
			$results = $db->query('select * from meetings where id = ' . $meeting_id_next);
			$row = $results->fetchArray();
		} while (($row == FALSE) and ( $meeting_id_next < $meeting_id_max) );
		if ($row == FALSE) {
			$meeting_id_next = $meeting_id;
		}
	
		$meeting_id = $meeting_id_next;
	}
	if(isset($_POST['prev_meeting'])){
		$meeting_id_prev = $meeting_id;
		do {
			$meeting_id_prev = $meeting_id_prev - 1;
			$results = $db->query('select * from meetings where id = ' . $meeting_id_prev);
			$row = $results->fetchArray();
		} while (($row == FALSE) and ( $meeting_id_prev > 1) );
		if ($row == FALSE) {
			$meeting_id_prev = $meeting_id;
		}
		$meeting_id = $meeting_id_prev;		
	}

} else {
	$meeting_id = 1;
}
?>

<form method="post">
<input type="hidden" name="update_form" value="update"/>
<input type="hidden" name="meeting_id" value="<?php echo($meeting_id);?>"/>

<?php
# print the meeting info
$results = $db->query('select * from meetings where id = ' . $meeting_id);
$row = $results->fetchArray();
echo("<p>Meeting ID: " . $meeting_id . "<br/>");
echo("Group: " . $row['group_name'] . "<br/>");
echo("Meeting Title: " . $row['title'] . "<br/>");
echo("Date: " . $row['date'] . "<br/>");
echo("Called By: " . $row['caller'] . "<br/>");
echo("Participants: " . $row['participants'] . "<br/>");
echo("Skipping?: ");
if($row['category']>0){
	echo("<td><input name=\"category[]\" value=\"" . $row['id'] . "\" type=\"checkbox\" checked></td>\n");
} else {
	echo("<td><input name=\"category[]\" value=\"" . $row['id'] . "\" type=\"checkbox\"></td>\n");
}
echo("</p>");
?>

<input type="submit" name="prev_meeting" value="<"/><input type="submit" name="skip" value="Update Skip option"/><input type="submit" name="next_meeting" value=">"/>

<?php
$results = $db->query('select * from items where meeting_id = ' . $meeting_id
		      . ' order by id asc');

# setting up the list of items
$line = 0;
echo("<table><tr><th>Importance</th><th>ID</th><th>Topic</th><th>Item</th><th>Action</th><th>Person</th></tr>");
while ($row = $results->fetchArray()) {
	echo("<tr>\n");
	if($row['importance']>0){
		echo("<td><input name=\"importance[]\" value=\"" . $row['id'] . "\" type=\"checkbox\" checked></td>\n");
	} else {
		echo("<td><input name=\"importance[]\" value=\"" . $row['id'] . "\" type=\"checkbox\"></td>\n");
	}
	echo("<td>" . $row['id'] . "</td>\n");
	echo("<td>" . $row['topic'] . "</td>\n");
	echo("<td>" . $row['item'] . "</td>\n");
	echo("<td>" . $row['action'] . "</td>\n");
	echo("<td>" . $row['person'] . "</td>\n");
	echo("</tr>\n");
}
echo("</table>");

?>
<input type="submit" name="update" value="Update"/>
</form>

</body>
</html>
