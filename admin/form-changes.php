<?php
require("database.php");
require("secure.php");

require("menu.php");

function getForm($header) {
	$sql = "select forms.name from forms inner join form_headers on 
	 forms.ID = form_headers.form where form_headers.ID=$header";
	$query = mysql_query($sql);
	checkDBError();
	
	if ($result = mysql_fetch_array($query)) return $result[0];
	return 0;
}

function formName($form) {
	$sql = "SELECT name FROM forms WHERE ID=$form";
	$query = mysql_query($sql);
	checkDBError();
	
	if ($result = mysql_fetch_array($query)) return $result[0];
	return 0;
}

function getUser($user_id) {
	$sql = "select first_name,last_name from users where ID=$user_id";
	$query = mysql_query($sql);
	checkDBError();
	
	if ($result = mysql_fetch_array($query)) {
		$return_string = $result[0]." ".$result[1];
		return $return_string;
	}
	return "";
}
?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
<br>
<?php
if ($change_id <> "") {

	$sql = "select form_changes.user,form_items.header,
	 form_items.description,form_changes.date,form_changes.action,form_items.partno 
	 FROM form_changes INNER JOIN form_items ON form_changes.form_item_id = form_items.ID 
	 WHERE form_changes.ID = $change_id";
	$query = mysql_query($sql);
	checkDBError();
	$result = mysql_fetch_array($query);
	$user = getUser($result[0]);
	$date = date("m/d/Y", strtotime($result[3]));
	$form = getForm($result[1]);
	$item = $result[5]." - ".$result[2];
	$details = $result[4];
?>
<h3 class="fat_black">Form Changes</h3>
<table border="0" cellspacing="3" cellpadding="2">
  <tr> 
    <td align="right" class="text_12"><b>User:</b></td>
    <td class="text_12"><?php echo $user; ?></td>
  </tr>
  <tr> 
    <td align="right" class="text_12"><b>Date:</b></td>
    <td class="text_12"><?php echo $date; ?></td>
  </tr>
  <tr> 
    <td align="right" class="text_12"><b>Form:</b></td>
    <td class="text_12"><?php echo $form; ?></td>
  </tr>
  <tr> 
    <td align="right" class="text_12"><b>Item:</b></td>
    <td class="text_12"><?php echo $item; ?></td>
  </tr>
  <tr> 
    <td align="right" valign="top" class="text_12"><b>Details:</b></td>
    <td class="text_12"><?php echo $details; ?></td>
  </tr>
</table>
	<br>
	<div><a href="form-changes.php">Back</a></div>
<?php
}
else {
	if ($form <> "") {
		$where_clause = " WHERE form_changes.form=$form";
		$title = " - ".formName($form);
	}
?>

<h3 class="fat_black">Form Changes<?php echo $title; ?></h3>

<?php
	$sql = "SELECT DISTINCT form FROM form_changes ORDER BY form";
	$query = mysql_query($sql);
	checkDBError();
	echo "<form action=\"form-changes.php\" method=\"get\">
	 <p>Select a Vendor: <select name=\"form\">\n
	 <option value=\"\">- All Vendors -</option>";
	while ($result = mysql_fetch_array($query)) {
		echo "<option value=\"".$result[0]."\"";
		if ($form == $result[0])
			echo " selected";
		echo ">".formName($result[0])."</option>\n";
	}
	echo "</select> <input type=\"submit\" value=\"Go\"></p>
	 </form>";
	
	$sql = "SELECT form_changes.ID,form_changes.form,form_items.description,form_changes.date,form_items.header 
	 FROM form_changes INNER JOIN form_items ON form_changes.form_item_id = form_items.ID 
	 $where_clause ORDER BY form_changes.date desc";
	$query = mysql_query($sql);
	checkDBError();
?>
<table border="0" cellspacing="2" cellpadding="2" width="60%">
<tr><td class="fat_black_12" bgcolor="#fcfcfc">Form</td><td class="fat_black_12" bgcolor="#fcfcfc">Form Item</td><td class="fat_black_12" bgcolor="#fcfcfc">Date</td></tr>
<?php
	while ($result = mysql_fetch_array($query)) {
		$ID = $result[0];
		$form = $result[1];
		$description = $result[2];
		$date = date("m/d/Y", strtotime($result[3]));
		$form = getForm($result[4]);
?>
	<tr>
	 <td class="text_12"><?php echo $form; ?></td>
	 <td class="text_12"><?php echo $description; ?></td>
	 <td class="text_12"><a href="form-changes.php?change_id=<?php echo $ID; ?>"><?php echo $date; ?></a></td>
	</tr>
<?php
	}
?>
</table>
<?php
} //end if...else

footer($link);
?>