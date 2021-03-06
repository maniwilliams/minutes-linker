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


# get the largest meeting id

$results = $db->query('select max(id) from meetings');
$row = $results->fetchArray();
$meeting_id_max = $row[0];



/* When the HTML form is submitted, the $_POST array is set */
if(isset($_POST['update_form'])){
	/* Get the meeting_ids ready for database query */
	$meeting_id = $_POST['meeting_id'];
	$meeting_id_next = $meeting_id;
	do {
		$meeting_id_next = $meeting_id_next + 1;
		$results = $db->query('select * from meetings where id = ' . $meeting_id_next);
		$row = $results->fetchArray();
	} while (($row == FALSE || $row['category'] > 0) and ( $meeting_id_next < $meeting_id_max) );
	if ($row == FALSE  || $row['category'] > 0) {
		$meeting_id_next = $meeting_id;
	}
	
	$meeting_id_prev = $meeting_id;
	do {
		$meeting_id_prev = $meeting_id_prev - 1;
		$results = $db->query('select * from meetings where id = ' . $meeting_id_prev);
		$row = $results->fetchArray();
	} while (($row == FALSE || $row['category'] > 0) and ( $meeting_id_prev > 1) );
	if ($row == FALSE || $row['category'] > 0) {
		$meeting_id_prev= $meeting_id;
	}
	
	/* If the Update (item) button was pressed */
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
	/* If the Next (item) button was pressed or the Update button was pressed */
	if(isset($_POST['next']) || isset($_POST['update'])){
		$results = $db->query('select * from items where ' .
				      'id > ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id_next) .
				      ' and importance > 0' .
				      ' order by id asc');
		$row = $results->fetchArray();
		if($row == False){
			$item_id = $_POST['item_id'];
		} else {
			$item_id = $row['id'];
		}
	/* If the Previous (item) button was pressed */
	} else if(isset($_POST['prev'])){
		$item_id = $_POST['item_id'] - 1;
		$results = $db->query('select * from items where ' .
				      'id < ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id_next).
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
		#$item_id = 1; #not sure about this
		$results = $db->query('select * from items where ' .
				      ' meeting_id = ' . ($meeting_id).
				      ' and importance > 0' .
				      ' order by id desc');
		$row = $results->fetchArray();
		if($row == False){
			$item_id = 1;
			echo("Failed to find important item");
		} else {
			$item_id = $row['id'];
		}
	}
	/* If the next_meeting button was pressed */
	if(isset($_POST['next_meeting'])){
		$meeting_id = $meeting_id_next;
		# generate the updated next meeting id
		do {
			$meeting_id_next = $meeting_id_next + 1;
			$results = $db->query('select * from meetings where id = ' . $meeting_id_next);
			$row = $results->fetchArray();
		} while (($row == FALSE || $row['category'] > 0) and ( $meeting_id_next < $meeting_id_max) );
		if ($row == FALSE || $row['category'] > 0) {
			$meeting_id_next = $meeting_id;
		}
		if ($meeting_id < $meeting_id_next) {
			$results = $db->query('select * from items where ' .
				      'id > ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id_next).
				      ' and importance > 0' .
				      ' order by id asc');
			$row = $results->fetchArray();
			if ($row == FALSE) {
				$item_id = $_POST['item_id'];
			} else {
				$item_id = $row['id'];
			}
		} else {
			$item_id = $_POST['item_id'];
		}
	

	}
	/* If the prev_meeting button was pressed */
	if(isset($_POST['prev_meeting'])){
		if($meeting_id_prev < $meeting_id) {
			$results = $db->query('select * from items where ' .
				      'id < ' . $_POST['item_id'] .
				      ' and meeting_id = ' . ($meeting_id).
				      ' and importance > 0' .
				      ' order by id desc');
			$row = $results->fetchArray();
			if ($row == FALSE) {
				$item_id = $_POST['item_id'];
			} else {
				$item_id = $row['id'];
			}
		} else {
			$item_id = $_POST['item_id'];
		}
		$meeting_id = $meeting_id_prev;
	}
/* Default values when we haven't loaded the page because of a form action */
} else {
	/* Find the first meeting */
	$meeting_id = 0;
	do {
		$meeting_id = $meeting_id + 1;
		$results = $db->query('select * from meetings where id = ' . $meeting_id);
		$row = $results->fetchArray();
	} while (($row == FALSE || $row['category'] > 0) and ( $meeting_id < $meeting_id_max) );

	$meeting_id_next = $meeting_id;
	do {
		$meeting_id_next = $meeting_id_next + 1;
		$results = $db->query('select * from meetings where id = ' . $meeting_id_next);
		$row = $results->fetchArray();
	} while (($row == FALSE || $row['category'] > 0) and ( $meeting_id_next < $meeting_id_max) );
	if ($row == FALSE) {
		$meeting_id_next = $meeting_id;
	}

	$results = $db->query('select * from items where ' .
			      ' meeting_id = ' . ($meeting_id_next).
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

echo("DEBUGGING - meeting id: $meeting_id item_id: $item_id");

?>

<form method="post">
<input type="hidden" name="update_form" value="update"/>
<input type="hidden" name="meeting_id" value="<?php echo($meeting_id);?>"/>
<input type="hidden" name="item_id" value="<?php echo($item_id);?>"/

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

<input type="submit" name="prev_meeting" value="< meeting "/><input type="submit" name="next_meeting" value=" meeting >"/>

<?php

/* Query database for items in current meeting */
$results = $db->query('select * from items where meeting_id = ' . $meeting_id
		      . ' and importance > 0 order by id asc');

/* Fetch data on subsequent meeting */
#$meeting_id = $meeting_id_next;
$meetingresults = $db->query('select * from meetings where id = ' . $meeting_id_next);
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
echo("<p>Meeting ID: " . $meeting_id_next . "<br/>");
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
<input type="submit" name="prev" value="<"/><input type="submit" name="update" value="Update"/><input type="submit" name="next" value=">"/>
</form>

</body>
</html>
