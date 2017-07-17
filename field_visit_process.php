<?php
// field_visit_process.php
// script to add a new field visit report to the db
require("database.php");
require("secure.php");
$sql = "SELECT name, type FROM fieldvisit_columns";
$que = mysql_query($sql);
checkdberror($sql);
while($res = mysql_fetch_assoc($que))
{
	$cols[] = $res['name'];
	$cols_type[$res['name']] = $res['type'];
}
foreach($_COOKIE as $k => $v)
{
	if(in_array($k, $cols))
	{
		$insert_fields[] = $k;
		if($k=='field_visit_date')
		{
			$inserts[$k] = date('Y-m-d', strtotime(stripslashes($v)));
		}
		else
		{
			$inserts[$k] = $cols_type[$k] != 'checkbox' ? stripslashes($v) : ($v=='on' ? 1 : 0);
		}
	}
	if($k=='clearance_phone')
	{
		//echo "$k => $v<br />\n";
		$clearphone = $v;
	}
	if($k=='email_number')
	{
		//echo "$k => $v<br />\n";
		$email_number = $v;
	}
}
//start to make the insert query
$sql = "INSERT INTO fieldvisit (visitor_id, dealer_id, clearance_phone, email_number, ".implode(', ',$insert_fields).") VALUES ('{$_COOKIE['uid']}', '{$_COOKIE['lastname']}', '$clearphone', '$email_number', '";
foreach($insert_fields as $fields)
{
	if($notfirst) $sql .= ", '";
	$sql .= mysql_escape_string($inserts[$fields])."'";
	$notfirst = true;	
}
$sql .= ")";
//die($sql);
// query made, run it
$res = mysql_query($sql);
$thisid = mysql_insert_id();
header('Location: field_visit_post.php?id='.$thisid);
//if($res) die("Success<br />$sql");

?>