<?php

/* Look for the item (needle) in the string of related items (haystack).
 * This function is used to generate the checked status of the checkboxes.
 */
function check_checkbox($haystack, $needle){
	if(strpos($haystack, " " . $needle . " ") === FALSE){
		return;
	} else {
		return("checked ");
	}
}

/* The SQLite3 database object */
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

<?php

/* When the HTML form is submitted, the $_POST array is set */
if(isset($_POST['update_form'])){
	$meeting_id = $_POST['meeting_id'];
	/* If the Update button was pressed */
	if(isset($_POST['update'])) {
		/* All the related items are surrounded by spaces */
		$related = ' ';
		if(isset($_POST['related'])){
			/* $_POST['related'] is an array. loop through each,
			 * adding to the $related string.
			 */
			$arr = $_POST['related'];
			foreach ($arr as $x) {
				$related = $related . $x . ' ';
			}
		}
		$results = $db->exec('update items set related = \'' . $related . '\' where id = ' . $_POST['item_id']);
	}
	/* If the Next button was pressed or the Update button was pressed */
	if(isset($_POST['next']) || isset($_POST['update'])){
		$results = $db->query('select * from items where ' .
				      'id > ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id + 1) .
				      ' and importance > 0' .
				      ' order by id asc');
		$row = $results->fetchArray();
		if($row == False){
			$item_id = $_POST['item_id'];
		} else {
			$item_id = $row['id'];
		}
	/* If the Previous button was pressed */
	} else if(isset($_POST['prev'])){
		$item_id = $_POST['item_id'] - 1;
		$results = $db->query('select * from items where ' .
				      'id < ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id + 1).
				      ' and importance > 0' .
				      ' order by id desc');
		$row = $results->fetchArray();
		if($row == False){
			$item_id = $_POST['item_id'];
		} else {
			$item_id = $row['id'];
		}
	/* By default we start with item 1 */
	} else {
		$item_id = 1;
	}
	if(isset($_POST['next_meeting'])){
		$meeting_id = $meeting_id + 1;
		$results = $db->query('select * from items where ' .
				      ' meeting_id = ' . ($meeting_id + 1).
				      ' and importance > 0' .
				      ' order by id asc');
		$row = $results->fetchArray();
		if ($row == FALSE) {
			$meeting_id = $meeting_id - 1;
			$item_id = $_POST['item_id'];
		} else {
			$item_id = $row['id'];
		}
	}
	if(isset($_POST['prev_meeting'])){
		if($meeting_id > 1){
			$meeting_id = $meeting_id - 1;
		}
		$results = $db->query('select * from items where ' .
				      ' meeting_id = ' . ($meeting_id + 1).
				      ' and importance > 0' .
				      ' order by id asc');
		$row = $results->fetchArray();
		if ($row == FALSE) {
			$item_id = $_POST['item_id'];
		} else {
			$item_id = $row['id'];
		}
	}
/* Default values when we haven't loaded the page because of a form action */
} else {
	$meeting_id = 1;
	$results = $db->query('select * from items where ' .
			      ' meeting_id = ' . ($meeting_id + 1).
			      ' and importance > 0' .
			      ' order by id asc');
	$row = $results->fetchArray();
	if($row == False){
		$item_id = 1;
		echo("Failed to find important item");
	} else {
		$item_id = $row['id'];
	}
}

echo("meeting id: $meeting_id item_id: $item_id");

?>

<form method="post">
<input type="hidden" name="update_form" value="update"/>
<input type="hidden" name="meeting_id" value="<?php echo($meeting_id);?>"/>

<?php

/* Display the meeting details */
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

<?php

/* Query database for items in current meeting */
$results = $db->query('select * from items where meeting_id = ' . $meeting_id
		      . ' and importance > 0 order by id asc');

/* Fetch data on subsequent meeting */
$meeting_id = $meeting_id + 1;
$meetingresults = $db->query('select * from meetings where id = ' . $meeting_id);
$meetingrow = $meetingresults->fetchArray();

/* Determine what item in the subsequent meeting we are linking to */
$itemresults = $db->query('select * from items where id = ' . $item_id);

/* Fetch the next item for subsequent meeting */
$itemrow = $itemresults->fetchArray();

/* Display each item in meeting and check any checkbox that is related to the
 * item we just looked up.
 */
echo("<table><tr><th>Related</th><th>ID</th><th>Topic</th><th>Item</th><th>Action</th><th>Person</th></tr>");
while ($row = $results->fetchArray()) {
	echo("<tr>\n");
	echo("<td><input name=\"related[]\" value=\"" . $row['id'] . "\" " . check_checkbox($itemrow['related'], $row['id']) . "type=\"checkbox\"></td>\n");
	echo("<td>" . $row['id'] . "</td>\n");
	echo("<td>" . $row['topic'] . "</td>\n");
	echo("<td>" . $row['item'] . "</td>\n");
	echo("<td>" . $row['action'] . "</td>\n");
	echo("<td>" . $row['person'] . "</td>\n");
	echo("</tr>\n");
}
echo("</table>");

/*Display the subsequent meeting details */
echo("<p>Meeting ID: " . $meeting_id . "<br/>");
echo("Group: " . $meetingrow['group_name'] . "<br/>");
echo("Meeting Title: " . $meetingrow['title'] . "<br/>");
echo("Date: " . $meetingrow['date'] . "<br/>");
echo("Called By: " . $meetingrow['caller'] . "<br/>");
echo("Participants: " . $meetingrow['participants'] . "</p>");

/*Display the meeting item */
echo("<table><tr><th>ID</th><th>Topic</th><th>Item</th><th>Action</th><th>Person</th></tr>");
	echo("<tr>\n");
	echo("<td>" . $itemrow['id'] . "</td>\n");
	echo("<td>" . $itemrow['topic'] . "</td>\n");
	echo("<td>" . $itemrow['item'] . "</td>\n");
	echo("<td>" . $itemrow['action'] . "</td>\n");
	echo("<td>" . $itemrow['person'] . "</td>\n");
	echo("</tr>\n");
echo("</table>");
?>
<input type="hidden" name="item_id" value="<?php echo($itemrow['id']);?>"/>
<input type="submit" name="prev" value="<"/><input type="submit" name="update" value="Update"/><input type="submit" name="next" value=">"/>
</form>

</body>
</html>
