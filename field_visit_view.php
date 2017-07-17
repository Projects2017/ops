<?php
// field_visit_view.php
// script to display field visit(s) by visitor, manager, or dealer
require("database.php");
require("secure.php");
ob_end_flush();
ob_start();
if($_POST)
{
	$dealer_selected = $_POST['dealer'];
	$visitid = $_POST['visitdate'];
	$editmode = $_POST['editmode'];
}
if(!(secure_is_manager()||secure_is_admin()||secure_is_superadmin())) die("Unauthorized access");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
<style type="text/css">
.newrow { background-color: #CCCC99; vertical-align: top }
</style>
<script type="text/javascript">
function doSubmit()
{
	document.viewfilter.submit();
}

function resetVisitDate()
{
	document.getElementById('resetform').setAttribute('value',1);
}

function removeDuplicate(visit_id)
{
	if(confirm('Are you sure you want to permanently delete this field visit record?'))	window.location = 'field_visit_remove.php?id='+visit_id;
}
</script>
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<form name="viewfilter" method="post" action="field_visit_view.php">
<input type="hidden" name="resetme" id="resetform" value="">
<table border="0" cellspacing="0" cellpadding="5" width="100%"><TR><TD colspan="2" align="center">RSS Manager Field Visit Viewer</TD></TR>
<tr><td class="newrow" align="right" width="50%">Dealer:</td><td><input type="hidden" name="dealer_select" value="" id="dealer_select"><select name="dealer" onchange="javascript:resetVisitDate(); doSubmit();" id="dealer"><option value=""<?php if(!$dealer_selected) { ?> selected="selected"<?php } ?>>Select Dealer...</option><?php
$sql = "SELECT DISTINCT dealer_id, last_name FROM fieldvisit INNER JOIN users ON dealer_id = users.ID";
$sql .= " ORDER BY last_name ASC";
$que = mysql_query($sql);
if(mysql_num_rows($que)==0) { echo "There are no Field Visits to view"; die(); }
$qu = mysql_query($sql);
while($res = mysql_fetch_assoc($que))
{
	$temp_deal_select = $res['dealer_id'];
	echo "<option value=\"";
	$sql2 = "SELECT last_name FROM users WHERE ID = '{$res['dealer_id']}'";
	//echo $sql2;
	$que2 = mysql_query($sql2);
	$return = mysql_fetch_assoc($que2);
	$this_name = $return['last_name'];
	$this_id = $res['dealer_id'];
	echo $this_id."\"";
	if($dealer_selected == $this_id) echo ' selected';
	echo ">$this_name</option>\n";
}
?></select></td></tr>
<?php
if($dealer_selected!="")
{
	?><tr><td class="newrow" align="right">Visit Date:</td><td><select name="visitdate" onchange="doSubmit()" id="visitdate"><option value=""<?php if(!$visitdate || $_POST['resetme']) { ?> selected="selected"<?php } ?>>Select Date...</option><?php
	$sql = "SELECT visit_id, field_visit_date FROM fieldvisit WHERE dealer_id = '$dealer_selected'";
	$que = mysql_query($sql);
	$validdates = array();
	while($res = mysql_fetch_assoc($que))
	{
		$validdates[] = $res['field_visit_date'];
		echo "<option value=\"{$res['visit_id']}\"";
		if($visitid == $res['visit_id']) echo ' selected';
		echo ">".date('F j, Y', strtotime($res['field_visit_date']));
		$visits[$res['field_visit_date']][] = $visitid;
		if(count($visits[$res['field_visit_date']])>1)
		{
			// there's been more than one visit this date, so append a #2 to the description
			echo " #2";
		}
		echo "</option>\n";	
	}
?></select><input type="submit" value="View"></td></tr>
</table>
</form>
<?php
}
if($visitid!="")
{
	$sql = "SELECT * FROM fieldvisit WHERE dealer_id = '$dealer_selected' AND visit_id = '$visitdate'";
	$que = mysql_query($sql);
	checkDBerror($sql);
	$main_result = mysql_fetch_assoc($que);
	?><table border="0" cellspacing="0" cellpadding="5" width="100%"><tr><TD colspan="2" align="center"><a href="field_visit.php">Enter Another Field Visit</a> | <a href="field_visit_view.php">View Field Visits</a><?php
	// allow edits only for admins or superadmins
	// removed limitation 6/11/08
	if(date('Y-m-d')-date('Y-m-d',strtotime($main_result['field_visit_date']))<=90)
	{
		?><form name="editmode1" method="post" action="field_visit.php"><?php
		foreach($_POST as $pname => $pvalue)
		{
			?><input type="hidden" name="<?php echo $pname; ?>" value="<?php echo $pvalue; ?>"><?php
		}
		?><a href="javascript:document.editmode1.submit();">Edit This Field Visit</a><?php if(!$_POST['editmode']) { ?><input type="hidden" name="editmode" value="1"><?php } ?></form><?php
	}
	// see if this is a duplicate entry for the same dealer & date, and if so offer to remove it
	// first, check if a duplicate
	$sql = "SELECT COUNT(visit_id) as datecount FROM fieldvisit WHERE dealer_id = '$dealer_selected' AND field_visit_date = '".$main_result['field_visit_date']."'";
	//echo $sql;
	$que = mysql_query($sql);
	checkDBerror($sql);
	$res = mysql_fetch_assoc($que);
	$datecount = $res['datecount'];
	if($datecount > 1 && secure_is_superadmin())
	{
		// we're a duplicate entry, a superadmin user, so offer to remove
		?><a href="javascript:removeDuplicate(<?php= $visitid ?>)">Delete This Duplicate Entry</a><?php
	}
	
	?></TD></tr></table><?php
	unset($validdates);
	// grab visitor name
	$sql = "SELECT last_name FROM users WHERE ID = {$main_result['visitor_id']}";
	$que = mysql_query($sql);
	checkdberror($sql);
	$visitorname = mysql_fetch_assoc($que);
	$namepos = strpos($visitorname['last_name'], ',');
	$name2 = substr($visitorname['last_name'], 0, $namepos);
	$visitname = substr(stristr($visitorname['last_name'], ','), 2).' '.$name2;
	?><table border="0" cellspacing="0" cellpadding="5" width="100%">
	<tr><td class="newrow" align="right">Visitor Name:</td><td><?php= $visitname ?></td></tr>
	<tr><td colspan="2" align="center" class="newrow" style="font-weight: bold">Dealer Information</td></tr>
	<tr><TD align="right" class="newrow" width="50%">Name of Dealer:</TD><TD><?php
	$sql = "SELECT last_name, cell_phone, email, email2, email3, manager, level FROM users WHERE ID = {$main_result['dealer_id']}";
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
				case 'textarea':
				case 'text':
				case 'checkbox':
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
	?></table><?php
	if($main_result['files']!='')
	{
		?></table><form name="dlfiles" action="dl_fieldvisit_attach.php" method="post"><table border="0" cellspacing="0" cellpadding="5" width="100%"><tr><td align="right" class="newrow" width="50%"><input type="hidden" name="id" value="<?php echo $main_result['visit_id']; ?>">Download Attached File(s):</td><td align="left"><?php
		$filenames = explode('; ', $main_result['files']);
		if(count($filenames)>1) { ?><input type="radio" name="files" value="allfiles">Download All File(s)<br /><?php }
		foreach($filenames as $file)
		{
			if($file != '')
			{
			?><input type="radio" name="files" value="<?php echo $file; ?>"><?php echo $file; ?><br /><?php
			}
		}
		?><input type="submit" name="go" value="Download File(s)"></td></tr></table></form><?php
	}
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
			?><table><tr><td class="newrow" colspan="2" align="center">Change Log</td></tr><?php
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
			?></tr></table><?php
		}
	}
}
?>
<table border="0" cellspacing="0" cellpadding="5" width="100%"><tr><TD colspan="2" align="center"><a href="field_visit.php">Enter Another Field Visit</a> | <a href="field_visit_view.php">View Field Visits</a>
<?php
if(secure_is_admin() || secure_is_superadmin())
{
	?><form name="editmode" method="post" action="field_visit.php"><?php
	foreach($_POST as $pname => $pvalue)
	{
		?><input type="hidden" name="<?php echo $pname; ?>" value="<?php echo $pvalue; ?>"><?php
	}
	?> | <a href="javascript:document.editmode.submit();">Edit This Field Visit</a><?php if(!$_POST['editmode']) { ?><input type="hidden" name="editmode" value="1"><?php } ?></form><?php
} ?>
</TD></tr>
</table>
</body>
</html>
<?php
$html = ob_get_clean();
$config = array(
	'indent'         => true,
	'output-xhtml'   => true,
	'wrap'           => 200);

// Tidy
$tidy = new tidy;
$tidy->parseString($html, $config, 'utf8');
$tidy->cleanRepair();

// Output
echo $tidy;

?>