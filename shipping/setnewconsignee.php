<?php
// setnewconsignee.php
// script to add a custom consignee address to a bol
$loc = $_GET['po'] ? (strpos($_POST['po'],';') ? 'multiaddbol.php?ids' : 'addbol.php?id') : strpos($_POST['po'],';') ? 'multiaddbol.php?ids' : 'addbol.php?id';
// first, if we're GETing that means we need to remove the custom user info from the snapshot db (for a cleaner table)
if($_GET && $_GET['resetid'])
{
	require('../database.php');
	$sql = "DELETE FROM snapshot_users WHERE ID = {$_GET['resetid']}";
	checkdberror($sql);
	mysql_query($sql);
	header('Location: '.$loc.'='.$_GET['po'].'&source='.$_GET['source']);
	exit();
}
if($_POST['stop'])
{
	header('Location: '.$loc.'='.$_POST['po'].'&source='.$_POST['source']); // if we're canceling, go ahead & redirect
}
require('inc_shipping.php'); // get the shipping functions
if(!$_POST) // if vars aren't POST'd in, exit
    sendError("setting a custom consignee address for a new BOL", "BOL Entry - Set Custom Consignee (setnewconsignee.php line 5)", "Unauthorized access to setconsignee.php without POSTing variables", 'shipping.php');
// add the required scripts for db & user capabilities
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
if($_POST['last_name']=='[Addressee]')
{
	// if the addressee doesn't have a new name entered, we need to bail
	
	header('Location: '.$loc.'='.$_POST['po'].'&source='.$_POST['source']);
}
// go through the post'd vars and see if any field has been left at the default; if so, we need to stop from going on
$consignee = Array();
foreach($_POST as $key => $value)
{
	switch($key)
	{
		case 'last_name':
			$consignee['last_name'] = $value; // already verified entry
			break;
		case 'address':
			if($value=='[Address]')
			{
				die('No address entered');
			}
			$consignee['address'] = $value;
			break;
		case 'address2':
			$consignee['address2'] = stripslashes($value)=="[Address cont'd]" ? "" : $value;
			break;
		case 'city':
			if($value=='[City]') die('No city entered');
			$consignee['city'] = $value;
			break;
		case 'state':
			if($value=='[ST]') die('No state entered');
			if(strlen($value)>2) die ('Invalid state entry');
			$consignee['state'] = $value;
			break;
		case 'zip':
			if($value=='[PostalCode]') die('No postal code entered');
			$consignee['zip'] = $value;
			break;
		case 'phone':
			$consignee['phone'] = $value=='[Phone #]' ? "" : $value;
			break;
	}
}
// set up the SQL query to add to the snapshot_users db table
$sql = "INSERT INTO snapshot_users (last_name, address, address2, city, state, zip, phone) VALUES ('".mysql_escape_string(stripslashes($consignee['last_name']))."', '".mysql_escape_string(stripslashes($consignee['address']))."', '".mysql_escape_string(stripslashes($consignee['address2']))."', '".mysql_escape_string(stripslashes($consignee['city']))."', '".mysql_escape_string(strtoupper(stripslashes($consignee['state'])))."', '".mysql_escape_string(stripslashes($consignee['zip']))."', '".mysql_escape_string(stripslashes($consignee['phone']))."')";
$que = mysql_query($sql);
checkdberror($sql);
$newuserid = mysql_insert_id();
$sql = "INSERT INTO shipping_agents (snapshot_userid) VALUES ($newuserid)";
$que = mysql_query($sql);
checkdberror($sql);
header('Location: '.$loc.'='.$_POST['po'].'&source='.$_POST['source'].'&shipto='.$newuserid);
?>