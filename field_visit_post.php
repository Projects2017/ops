<?php
require("database.php");
require("secure.php");
if(!secure_is_manager()) die("Unauthorized access");
if(!$_GET)
{
	$sql = "SELECT MAX(visit_id) AS lastid FROM fieldvisit";
	$que = mysql_query($sql);
	checkdberror($sql);
	$res = mysql_fetch_assoc($que);
	$thisid = $res['lastid'];
}
else
{
	$thisid = $_GET['id'];
}
$sql = "SELECT * FROM fieldvisit WHERE visit_id = '$thisid'";
$que = mysql_query($sql);
checkdberror($sql);
$main_result = mysql_fetch_assoc($que);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
<style type="text/css">
.newrow { background-color: #CCCC99 }
.subhead { font-weight: bold; text-align: center }
</style>
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%"><TR><TD colspan="2" align="center">RSS Manager Field Visit Submission</TD></TR>
<tr><TD colspan="2" align="center"><a href="field_visit.php">Enter Another Field Visit</a> | <a href="field_visit_view.php">View Field Visits</a>
<?php	// allow edits only for admins or superadmins
	// removed limitation 6/11/08
	if(secure_is_admin() || secure_is_superadmin() || ($userid == $dealer_selected && date('Y-m-d')-date('Y-m-d',strtotime($main_result['field_visit_date']))<=90))
	{
		?><form name="editmode" method="post" action="field_visit.php" id="editmode">
		<input type="hidden" name="dealer_select" value="1" />
		<input type="hidden" name="date_select" value="1" />
		<input type="hidden" name="dealer" value="<?php= $main_result['dealer_id'] ?>" />
		<input type="hidden" name="visitdate" value="<?php= $main_result['visit_id'] ?>" />
		<input type="hidden" name="editmode" value="1" /> | <a href="javascript:document.editmode.submit();">Edit This Field Visit</a>
		</form><?php
	}
	
?></TD></tr>
<tr><td colspan="2" align="center" class="newrow" style="font-weight: bold">Dealer Information</td></tr>
<tr><TD align="right" class="newrow" width="50%">Name of Dealer:</TD><TD><?php
$sql = "SELECT last_name, cell_phone, email, email2, email3, manager, level FROM users WHERE ID = '{$main_result['dealer_id']}'";
$que = mysql_query($sql);
checkdberror($sql);
$userinfo = mysql_fetch_assoc($que);
echo $userinfo['last_name'];
?></TD></tr>
<tr><td align="right" class="newrow">Clearence Center Phone Number:</td><td><?php echo $main_result['clearance_phone']; ?></td></tr>
<tr><td align="right" class="newrow">Dealer Cell Phone Number:</td><td><?php echo $userinfo['cell_phone']; ?></td></tr>
<tr><td align="right" class="newrow">Dealer Email Address:</td><td><?php
switch($main_result['email_number'])
{
	case '1':
		echo $userinfo['email'];
		break;
	case '2':
		echo $userinfo['email2'];
		break;
	case '3':
		echo $userinfo['email3'];
		break;
	default:
		echo "N/a";
		break;
}
?></td></tr>
<tr><TD align="right" class="newrow">Manager:</TD><TD><?php echo $userinfo['manager']; ?></TD></tr>
<tr><td align="right" class="newrow">Current Dealer Level:</td><td><?php echo $userinfo['level']; ?></td></tr>
<tr><td align="right" class="newrow">Name of Visitor:</td><td>
<?php
// now we try the field generating code

$sql = "SELECT sort, name, description, type FROM fieldvisit_columns WHERE section = '0' ORDER BY sort"; // get section headings first
$que = mysql_query($sql);
checkdberror($sql);
while($res = mysql_fetch_assoc($que))
{
	if($res['sort']!=1)
	{
		if($res['type']=="section")
		{
			
			?><tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" align="center" class="newrow" style="font-weight: bold"><?php echo $res['description']; ?></td></tr><?php
		}
		elseif($res['type']=="textarea")
		{
			?><tr><td class="newrow" align="right"><?php echo $res['description']; ?></td><td><?php echo $main_result[$res['name']]; ?></td></tr><?php
		}
		else
		{
			?><tr><td class="newrow" align="right"><?php echo $res['description']; ?></td><td><?php if($main_result['propersign']==1) { echo "Yes"; } else { echo 'No'; } ?></td></tr><?php
		}
	}
	$sql2 = "SELECT name, description, type, options, required FROM fieldvisit_columns WHERE section = '".$res['sort']."' ORDER BY sort"; // get all those items in the section
	$que2 = mysql_query($sql2);
	checkdberror($sql2);
	while($res2 = mysql_fetch_assoc($que2))
	{
		switch($res2['type'])
		{
			case 'text':
			case 'checkbox':
			case 'textarea':
				?><tr><td align="right" class="newrow"><?php echo $res2['description']; ?></TD><TD><?php
				if($res2['type']=='checkbox') 
				{
					if($main_result[$res2['name']]) { echo "Yes"; } else { echo "No"; }
				}
				else
				{
					// text type
					if($res2['name']=='field_visit_date')
					{
						echo date('m/d/Y', strtotime($main_result[$res2['name']]));
					}
					else
					{
						echo $main_result[$res2['name']];
					}
				}
				break;
			case 'option':
				?><tr><td align="right" class="newrow"><?php echo $res2['description']; ?></TD><TD><?php
				if(!($main_result[$res2['name']]==0 || is_null($main_result[$res2['name']])))
				{
					// display the data
					echo $main_result[$res2['name']]."  (1 = Worst; 5 = Best)";
				}
				break;
			case 'label':
				?><tr><td align="right"><?php echo $res2['description']; ?></td><td>&nbsp;<?php
				break;
			default:
				break;
		}
		?></td></tr><?php
	}
}
if(!is_null($main_result['files']))
{
	?><form name="dlfiles" action="dl_fieldvisit_attach.php" method="post"><input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"><tr><td align="right" class="newrow">Download Attached File(s):</td><td align="left"><?php
	$filenames = explode('; ', $main_result['files']);
	if(count($filenames)>1) { ?><input type="radio" name="files" value="allfiles">Download All File(s) in Zip File<br /><?php };
	if(count($filenames)>=1)
	{
		foreach($filenames as $file)
		{
			?><input type="radio" name="files" value="<?php echo $file; ?>"><?php echo $file; ?><br /><?php
		}
	}
	?><input type="submit" name="go" value="Download File(s)"></td></tr></form><?php
}

// change log display section

if(secure_is_admin() || secure_is_superadmin())
{
	// display the change log if there are entries for this field visit
	// first, check the log
	$sql = "SELECT * FROM fieldvisit_changelog WHERE visitid = ".$main_result['visit_id'];
	checkdberror($sql);
	$que = mysql_query($sql);
	if(mysql_num_rows($que)!=0)
	{
		// we have changelog entries, now we need to display them
		?><tr><td colspan="2">&nbsp;</td></tr>
		<tr><td class="newrow" colspan="2" align="center" style="font-weight: bold">Change Log</td></tr><?php
		while($res = mysql_fetch_assoc($que))
		{
			?><tr><td align="right" class="newrow" width="50%"><?php
			echo $res['timestamp'];
			$sql2 = "SELECT last_name FROM users WHERE ID = {$res['editorid']}";
			checkdberror($sql2);
			$que2 = mysql_query($sql2);
			$res2 = mysql_fetch_assoc($que2);
			echo " by {$res2['last_name']}";
			?></td><td><?php= stripslashes($res['changes']) ?></td></tr><?php
		}
		?></tr><?php
	}
}

?><tr><TD colspan="2" align="center"><a href="field_visit.php">Enter Another Field Visit</a> | <a href="field_visit_view.php">View Field Visits</a>
<?php	// allow edits only for admins or superadmins
	// removed limitation 6/11/08
	if(secure_is_admin() || secure_is_superadmin() || ($userid == $dealer_selected && date('Y-m-d')-date('Y-m-d',strtotime($main_result['field_visit_date']))<=90))
	{
		?><form name="editmode1" method="post" action="field_visit.php">
		<input type="hidden" name="dealer_select" value="1">
		<input type="hidden" name="date_select" value="1">
		<input type="hidden" name="dealer" value="<?php= $main_result['dealer_id'] ?>">
		<input type="hidden" name="visitdate" value="<?php= $main_result['visit_id'] ?>">
		<input type="hidden" name="editmode" value="1"><?php
		?> | <a href="javascript:document.editmode1.submit();">Edit This Field Visit</a></form><?php
	}
	
?></TD></tr>

</table>
</body>
