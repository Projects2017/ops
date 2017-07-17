<?php
/*

Field Visits

A small web page/app to enter field visits to PMD dealers
DB tables involved are fieldvisit and fieldvisit_columns (hereon * and *_columns, respectively)
*_columns is a table of the fields in the * table.
It lists the name, description, and type (section, text, label, option, checkbox, textarea)
In addition, you can set the field to be a required entry (*_columns.required = '1') or a number (*_columns.numeric = '1')

*/

require("database.php");
require("secure.php");
// set arrays used by the validation code so we don't have stupid errors
$bads = Array();
$missing_requireds = Array();
$numerics = Array();
if($_POST['editmode'])
{
	$editmode = true;
	$sql = "SELECT * FROM fieldvisit WHERE dealer_id = '{$_POST['dealer']}' AND visit_id = '{$_POST['visitdate']}'";
	checkdberror($sql);
	$que = mysql_query($sql);
	$editmodevars = mysql_fetch_assoc($que);
}

if($_POST && $_COOKIE['user_chosen']!=1 && !$_POST['editmode']) // if we're posting and not because a new dealer was chosen & not because we're editing a current one...
{
	// we're from a post
	// validate the data before going any further
	// first: required fields
	$sql = "SELECT name FROM fieldvisit_columns WHERE required = '1'";
	$que = mysql_query($sql);
	checkdberror($sql);
	$errorout = false; // if errorout, there's a problem somewhere and the submission won't happen
	$errorouts = Array();
	while($res = mysql_fetch_assoc($que))
	{
		if(is_null($_POST[$res['name']]) || $_POST[$res['name']]=='') // if a required field is null or blank
		{
			$errorout = true; // we'll be erroring out...
			$errorouts[] = $res['name']; //...because of this field name
		}
	}
	// next: numeric fields
	$sql = "SELECT name FROM fieldvisit_columns WHERE `numeric` = '1'"; // make sure everything that has to be a number is
	$que = mysql_query($sql);
	checkdberror($sql);
	$numberouts = Array();
	while($res = mysql_fetch_assoc($que))
	{
		if($_POST[$res['name']]!='' && !is_numeric($_POST[$res['name']])) // if a field which has to be a number is not blank and is not a number, error out
		{
			$errorout = true;
			$numberouts[] = $res['name']; // add to the list of fields with bad data
		}
	}
	// last: visit date = date of some kind
	$test = strtotime(stripslashes($_POST['field_visit_date']));
	if(date('Y',$test)==1969) // would be 1969 if the date to pull a correct date
	{
		// bad date, need to reenter
		$errorout = true;
		$errmsg = "Date of Field Visit is not a valid date (e.g. 1/12/08; Jan 12, 2008; January 12, 2008)<br />";
		$baddate = true;
	}
	if($errorout) // if we're erroring out
	{
		// first get the description of the missing required fields for the user nag
		$sql = "SELECT name, description FROM fieldvisit_columns WHERE name IN ('".implode("', '",$errorouts)."')";
		$que = mysql_query($sql);
		checkdberror($sql);
		$bads = Array();
		while($res = mysql_fetch_assoc($que)) // get the descriptions of the bad fields
		{
			$bads[] = $res['description'];
		}
		$badstr = implode(', ',$bads);
		if($badstr!='') $errmsg .= "These required fields are missing: ".$badstr."<br />";
		// now do the bad datas
		$sql = "SELECT name, description FROM fieldvisit_columns WHERE name IN ('".implode("', '",$numberouts)."')";
		$que = mysql_query($sql);
		checkdberror($sql);
		$baddatas = Array();
		while($res = mysql_fetch_assoc($que)) // get the descriptions of the bad fields
		{
			$baddatas[] = $res['description'];
		}
		if(count($baddatas)>0)
		{
			$badstr = implode(', ',$baddatas);
			$errmsg .= "These fields must be numeric: ".$badstr;
		}
		$missing_requireds = $errorouts;
		$numerics = $numberouts;
	}
	else
	{
		// we aren't erroring, now to process
		// if there's been any files uploaded...
		if($_FILES['upfile']['tmp_name'][0])
		{
			
			if(!$editmode)
			{
				// ...we calculate this new record's ID and make the proper folder in /doc/manager/
				$sql = "SELECT MAX(visit_id)+1 AS thisid FROM fieldvisit";
				$que = mysql_query($sql);
				checkdberror($sql);
				$res = mysql_fetch_assoc($que);
				$thisid = $res['thisid'];
			}
			else
			{
				$thisid = $editmodevars['visit_id'];
			}
			//echo $_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$thisid."<br />\n";
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$thisid)) mkdir($_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$thisid);
			$thispath = $_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$thisid;
			// now to move the files to their permanent location
			for($i=0; $i<count($_FILES['upfile']['name']); $i++)
			{
				if(!move_uploaded_file($_FILES['upfile']['tmp_name'][$i],$thispath.'/'.$_FILES['upfile']['name'][$i]))
				die("Error uploading file.");
				$filenames[] = $_FILES['upfile']['name'][$i];
			}
			// continue with the rest of the addition
		}
		// get the old filenames
		for($i=0; $i<count($_POST['files']); $i++)
		{
			$oldfiles[] = $_POST['files'][$i];
		}
		// get all the records listed under the *_columns table first
		$sql = "SELECT name, type FROM fieldvisit_columns";
		$que = mysql_query($sql);
		checkdberror($sql);
		while($res = mysql_fetch_assoc($que))
		{
			$cols[] = $res['name'];
			$cols_type[$res['name']] = $res['type'];
		}
		foreach($_POST as $k => $v)
		{
			if(in_array($k, $cols)) // if the POST var we're on is in the *_columns table...
			{
				$insert_fields[] = $k; // ...add the field name to the insert fields array and...
				if($k=='field_visit_date') // ...add the data to the inserts array
				{
					$inserts[$k] = date('Y-m-d', strtotime(stripslashes($v)));
				}
				else
				{
					$inserts[$k] = $cols_type[$k] != 'checkbox' ? stripslashes($v) : ($v=='on' ? 1 : 0);
				}
			}
			if($k=='clearance_phone') // if we're on the phone number for the clearance center, just push to a var; that field doesn't have a corresponding *_columns record
			{
				$clearphone = $v;
			}
			if($k=='email_number') // same with the email
			{
				$email_number = $v;
			}
			if($k=='cell_phone') // and with the cell phone
			{
				$cell_phone = $v;
			}
		}
		//start to make the insert query, but for new records only
		if(!$_POST['edited'])
		{
			$sql = "INSERT INTO fieldvisit (visitor_id, dealer_id, clearance_phone, email_number, files, ".implode(', ',$insert_fields).") VALUES ('{$_POST['uid']}', '{$_POST['lastname']}', '$clearphone', '$email_number', '";
			foreach($filenames as $file)
			{
				if($file=='') unset($filenames[$file]);
			}
			$filelist = implode('; ',$oldfiles);
			$filelist .= $filenames ? '; '.implode('; ',$filenames) : '';
			$sql .= $filelist."', '";
			foreach($insert_fields as $fields)
			{
				if($notfirst) $sql .= ", '"; // if we need to throw in a comma, do so
				$sql .= mysql_escape_string($inserts[$fields])."'";
				$notfirst = true;	
			}
			$sql .= ")"; // add the last )
			// query made, run it
			$res = mysql_query($sql);
			checkdberror($sql);
			$thisid = mysql_insert_id();

			// change cell phone records if changed
			if($_POST['lastname']!='') // if dealer chosen, make it into a local var
			{
				$dealerid = $_POST['lastname']; // dealerid = users.id
			}
			if($editmode || $_POST['dealer'])
			{
				$dealerid = $_POST['dealer'];
			}
			$sql = "SELECT cell_phone FROM users WHERE ID = $dealerid";
			$que = mysql_query($sql);
			checkdberror($sql);
			$res = mysql_fetch_assoc($que);
			if($cell_phone != $res['cell_phone'])
			{
				// do the update of the cell #
				$sql = "UPDATE users SET cell_phone = '$cell_phone' WHERE ID = $dealerid";
				mysql_query($sql);
				checkdberror($sql);
			}
			header('Location: field_visit_post.php?id='.$thisid); // see our handywork
		}
		else
		{
			// get the old data first for comparison purposes
			// set up the changes array
			$changes = array();
			$sql = "SELECT * FROM fieldvisit WHERE visit_id = {$_POST['visitid']}";
			$que = mysql_query($sql);
			checkdberror($sql);
			$olddata = mysql_fetch_assoc($que);
			// old user data
			if($_POST['lastname']!='') // if dealer chosen, make it into a local var
			{
				$dealerid = $_POST['lastname']; // dealerid = users.id
			}
			if($editmode || $_POST['dealer'])
			{
				$dealerid = $_POST['dealer'];
			}
			$sql = "SELECT cell_phone FROM users WHERE ID = $dealerid";
			$que = mysql_query($sql);
			checkdberror($sql);
			$usercell = mysql_fetch_assoc($que);
			// find the differences and log them
			if($_POST['editvisitor_id']!=$olddata['visitor_id']) $changes[] = "Visitor ID: \'{$olddata['visitor_id']}\' to \'{$_POST['editvisitor_id']}\'";
			if($clearphone != $olddata['clearance_phone']) $changes[] = "Clearance Phone #: \'{$olddata['clearance_phone']}\' to \'$clearphone\'";
			if($email_number != $olddata['email_number']) $changes[] = "Email #: \'{$olddata['email_number']}\' to \'$email_number\'";
			if($cell_phone != $usercell['cell_phone']) $changes[] = "Cell Phone #: \'{$usercell['cell_phone']}\' to \'$cell_phone\'";
			foreach($insert_fields as $fields)
			{
				if(stripslashes($inserts[$fields]) != stripslashes($olddata[$fields]))
				{
					$sql = "SELECT description, type FROM fieldvisit_columns WHERE name = '$fields'";
					checkdberror($sql);
					$que = mysql_query($sql);
					$res = mysql_fetch_assoc($que);
					if($res['type']=='checkbox')
					{
						$prior = $olddata[$fields] ? "Yes" : "No";
						$now = $inserts[$fields] ? "Yes" : "No";
					}
					else
					{
						$prior = $olddata[$fields];
						$now = $inserts[$fields];
					}
					$changes[] = "{$res['description']}: '$prior' to '$now'";
				}
			}
			if(count($changes) > 0)
			{
				$changestxt = implode('<br />',$changes);
				$sql = "INSERT INTO fieldvisit_changelog (visitid, editorid, changes) VALUES ({$_POST['visitid']}, $userid, '".mysql_escape_string($changestxt)."')";
				checkdberror($sql);
				mysql_query($sql);
			}
			// do the actual update
			$sql = "UPDATE fieldvisit SET visitor_id = '{$_POST['editvisitor_id']}', dealer_id = '{$_POST['lastname']}', clearance_phone = '$clearphone', email_number = '$email_number', ";
			$oldfiles = array();
			$oldfiles = explode('; ',$_POST['editmodefiles']);
			for($i=0; $i<count($oldfiles); $i++)
			{
				if($oldfiles[$i] == '') unset($oldfiles[$i]);
			}
			$oldfilelist = implode('; ',$oldfiles);
			$newfilelist = $filenames ? implode('; ',$filenames) : '';
			$editfiles = $oldfilelist;
			if($newfilelist != '') $editfiles .= ";$newfilelist";
			$sql .= "files = '$editfiles', ";
			foreach($insert_fields as $fields)
			{
				if($notfirst) $sql .= ", ";
				$sql .= "$fields = '".mysql_escape_string($inserts[$fields])."'";
				$notfirst = true;
			}
			$sql .= " WHERE visit_id = '{$_POST['visitid']}'";
			mysql_query($sql);
			checkdberror($sql);
			
			// change cell phone records if changed
			$sql = "SELECT cell_phone FROM users WHERE ID = $dealerid";
			$que = mysql_query($sql);
			checkdberror($sql);
			$res = mysql_fetch_assoc($que);
			if($cell_phone != $res['cell_phone'])
			{
				// do the update of the cell #
				$sql = "UPDATE users SET cell_phone = '$cell_phone' WHERE ID = $dealerid";
				mysql_query($sql);
				checkdberror($sql);
			}


			header('Location: field_visit_post.php?id='.$_POST['visitid']);
		}
	}
}
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
<?php require('menu.php');
?>
<script type="text/javascript">
function doSubmit()
{
	createCookie('user_chosen','1',3)
	document.fieldvisit.submit();
}

function createCookie(name,value,secs) {
	if (secs) {
		var date = new Date();
		date.setTime(date.getTime()+(secs*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function additionalFile(suffix)
{
	if(suffix)
	{
		var addhere = document.getElementById('newfile'+suffix);
		suffix++;
	}
	else
	{
		var addhere = document.getElementById('newfile');
		suffix = 1;
	}
	var thislabel = document.createElement('td');
	thislabel.setAttribute("align", "right");
	thislabel.setAttribute("class", "newrow");
	thislabel.innerHTML = "Attach File(s):";
	var thisfileform = document.createElement('td');
	var fileinput = document.createElement('input');
	fileinput.setAttribute('type', 'file');
	fileinput.setAttribute('size', '30');
	fileinput.setAttribute('name', 'upfile[]');
	var addanother = document.createElement('button');
	// addanother.setAttribute('onclick', 'additionalFile('+suffix+');');
	addanother.onclick = function(){ additionalFile(suffix); }
	addanother.setAttribute('type', 'button');
	addanother.innerHTML = 'Add Another File';
	thisfileform.appendChild(fileinput);
	thisfileform.appendChild(addanother);
	addhere.appendChild(thislabel);
	addhere.appendChild(thisfileform);
	// addhere.innerHTML = '<td align="right" class="newrow">Attach File(s):<\/td><td><input type="file" size="30" name="upfile[]"><button onclick="additionalFile(' + suffix + ');" type="button">Add Another File<\/button><\/td>';
	var newfile = document.createElement('tr');
	var newid = "newfile"+suffix;
	newfile.setAttribute("id",newid);
	addhere.parentNode.insertBefore(newfile,addhere.nextSibling);
}
</script>
<form method="post" name="fieldvisit" enctype="multipart/form-data" action="field_visit.php">
<input type="hidden" name="uid" value="<?php echo $userid; ?>">
<?php if($editmode) { ?><input type="hidden" name="edited" value="1"><input type="hidden" name="visitid" value="<?php echo $editmodevars['visit_id']; ?>"><input type="hidden" name="editvisitor_id" value="<?php echo $editmodevars['visitor_id']; ?>"><?php } ?>
<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
<tr><TD colspan="2" align="center" style="font-weight: bold">RSS MANAGER FIELD VISIT REPORT</TD></tr>
<tr><td colspan="2" align="center">* = Required Field</td></tr>
<tr><td colspan="2" align="center"><?php if($errmsg) { ?><span style="color: red; font-weight: bold"><?php echo stripslashes($errmsg); ?></span><?php } else { ?>&nbsp;<?php } ?></td></tr>
<tr><td colspan="2" align="center" class="newrow" style="font-weight: bold">Dealer Information</td></tr>
<tr><TD align="right" class="newrow">*Name of Dealer:</TD><TD><?php
if($_POST['lastname']!='') // if dealer chosen, make it into a local var
{
	$dealerid = $_POST['lastname']; // dealerid = users.id
}
if($editmode)
{
	$dealerid = $_POST['dealer'];
}
$sql = "SELECT ID, last_name FROM users WHERE disabled != 'Y' AND ID IN (SELECT relation_id FROM login WHERE type = 'D') ORDER BY last_name"; // get all dealers
$que = mysql_query($sql);
while($result = mysql_fetch_assoc($que))
{
	if(is_null($dealerid)) $dealerid = $result['ID']; // if the dealer hasn't been chosen, use the first one to return from the db for now
	$user_list[] = $result['last_name'];
	$user_ids[] = $result['ID'];
}
?><select name="lastname" onchange="doSubmit();">
<?php for($i=0; $i<count($user_ids); $i++)
{
	echo "\t<option value=\"{$user_ids[$i]}\"";
	if($dealerid==$user_ids[$i]) echo ' selected'; // if the dealer's been picked before, pick it again
	echo ">{$user_list[$i]}</option>\n";
}
	$sql = "SELECT cell_phone, email, email2, email3, level FROM users WHERE ID = $dealerid"; // get misc. dealer info into local vars for viewing and using
	$que = mysql_query($sql);
	checkdberror($sql);
	$res = mysql_fetch_assoc($que);
	$dealer_cell = $res['cell_phone'];
	$dealer_email = $res['email'];
	$dealer_email2 = $res['email2'];
	$dealer_email3 = $res['email3'];
	$dealer_level = $res['level'];
	unset($res);
?>
</select>
</TD></tr>
<tr><td align="right" class="newrow">Clearance Center Phone Number:</td><td><input type="text" name="clearance_phone" width="15"<?php
if($_POST['clearance_phone']!='') { echo " value=\"".$_POST['clearance_phone']."\""; } else if($editmode) { echo " value=\"".$editmodevars['clearance_phone']."\""; }
?>></td></tr> 
<tr><td align="right" class="newrow">Dealer Cell Phone Number:</td><td><input type="text" name="cell_phone" width="15"<?php
if($_POST['cell_phone']!='' || $dealer_cell != '') // if the dealer cell # has been filled in already, get it from the POST data
{
	echo " value=\"$dealer_cell\"";
}
else if($editmode)
{
	echo " value=\"".$editmodevars['cell_phone']."\"";
}
?>></td></tr>
<tr><td align="right" class="newrow">Dealer Email Address:</td><td><?php
if(is_null($dealer_email) || $dealer_email=='') // if no dealer chosen, make a blank text field for it
{
	?>N/a<?php
}
else
{
	if(!is_null($dealer_email2))
	{
		?><select name="email_number"><option value="1"<?php if($editmode && $dealer_email == $editmodevars['email_number']) echo ' selected'; ?>><?php echo $dealer_email; ?></option><?php
		for($i=2; $i<=3; $i++)
		{
			$var = 'dealer_email'.$i;
			if($$var!='')
			{
				?><option value="<?php echo $i; ?>"<?php if($editmode && $editmodevars['email_number']==$i) echo ' selected'; ?>><?php echo $$var; ?></option><?php
			}
		}
		?></select><?php
	}
	else
	{
		if($dealer_email=='') // if the email address is empty, place a N/a
		{
			?><!-- no email address found -->N/a<?php
		}
		else
		{
			echo $dealer_email; // if just an email address, go ahead and display it
			?><input type="hidden" name="email_number" value="1"><?php
		}
	}
}
?></td></tr>
<tr><td align="right" class="newrow">Manager:</td><td><?php
	$sql = "SELECT manager, level FROM users WHERE ID = '$dealerid'"; // get the manager
	$que = mysql_query($sql);
	$res = mysql_fetch_assoc($que);
	$managername = $res['manager'];
	$dealerlevel = $res['level'];
echo $managername; ?></td></tr>
<tr><td align="right" class="newrow">Current Dealer Level:</td><td><?php
switch($dealerlevel)
{
	case NULL:
		echo 'Unknown';
		break;
	case 'TBD':
	case '1':
	case '2':
	case '3':
	case '4/5':
		echo $dealerlevel;
		break;
}
?></td></tr>
<tr><TD align="right" class="newrow">Name of Visitor:</TD><TD><?php
if($dealerid !='' || $editmode)
{
	$sql = "SELECT ID, last_name FROM users WHERE disabled != 'Y' AND ID IN (SELECT relation_id FROM login WHERE type = 'M') ORDER BY last_name"; // get all managers
	$que = mysql_query($sql);
	while($result = mysql_fetch_assoc($que))
	{
		if(is_null($managername)) $managername = $result['ID']; // if the dealer hasn't been chosen, use the first one to return from the db for now
		$manager_list[] = $result['last_name'];
		$manager_ids[] = $result['ID'];
		if($result['ID']==$userid) $managername = $result['ID'];
	}
?><select name="managername">
<?php for($i=0; $i<count($manager_ids); $i++)
{
	echo "\t<option value=\"{$manager_ids[$i]}\"";
	if($editmode && $manager_ids[$i]==$editmodevars['visitor_id']) echo ' selected';
	if($managername==$manager_ids[$i]) echo ' selected'; // if the dealer's been picked before or is the current user, pick it again
	echo ">{$manager_list[$i]}</option>\n";
}
}
?></TD></tr>
<?php
// now we have the field generating code

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
			<tr><td colspan="2" align="center" class="newrow" style="font-weight: bold"><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?><span style="color: red"><?php } echo $res['description']; ?><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?></span><?php } ?></td></tr><?php
		}
		elseif($res['type']=="textarea")
		{
			?><tr><td class="newrow" align="right"><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?><span style="color: red"><?php } echo $res['description']; ?><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?></span><?php } ?></td><td><textarea name="<?php echo $res['name']; ?>" cols="30" rows="5"><?php if($_POST[$res['name']]) echo stripslashes($_POST[$res['name']]); if($editmode) echo stripslashes($editmodevars[$res['name']]); ?></textarea></td></tr><?php
		}
		else
		{
			?><tr><td class="newrow" align="right"><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?><span style="color: red"><?php } echo $res['description']; ?><?php if(in_array($res['name'],$missing_requireds)||in_array($res['name'], $numerics)) { ?></span><?php } ?></td><td><input type="checkbox" name="propersign"<?php if($_POST['propersign']=='on' || ($editmode && $editmodevars['propersign'])) echo " checked=\"checked\""; ?>></td></tr><?php
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
				?><tr><td align="right" class="newrow"><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)) { ?><span style="color: red"><?php } if($res2['required']==1) echo '*'; echo $res2['description']; ?><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)) { ?></span><?php } ?></td><td><textarea name="<?php echo $res2['name']; ?>" cols="30" rows="5"><?php if($_POST[$res2['name']]) echo stripslashes($_POST[$res2['name']]); if($editmode) echo stripslashes($editmodevars[$res2['name']])?></textarea><?php
				break;
			case 'text':
			case 'checkbox':
				?><tr><td align="right" class="newrow"><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)||($res2['name']=='field_visit_date'&&$baddate)) { ?><span style="color: red"><?php } if($res2['required']==1) echo '*';  echo $res2['description']; ?><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)) { ?></span><?php } ?></TD><TD><input type="<?php echo $res2['type']; ?>" name="<?php echo $res2['name']; ?>"<?php
				if($res2['type']=='checkbox')
				{
					if($_POST[$res2['name']]=='on' || ($editmode && $editmodevars[$res2['name']])) echo " checked=\"checked\">";
				}
				else
				{
					if($_POST[$res2['name']]) echo " value=\"".stripslashes($_POST[$res2['name']])."\"";
					if($editmode) echo " value=\"".stripslashes($editmodevars[$res2['name']])."\"";
					echo ">";
				}
				break;
			case 'option':
				?><tr><td align="right" class="newrow"><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)) { ?><span style="color: red"><?php } if($res2['required']==1) echo '*';  echo $res2['description']; ?><?php if(in_array($res2['name'],$missing_requireds)||in_array($res2['name'], $numerics)) { ?></span><?php } ?></TD><TD><select name="<?php echo $res2['name']; ?>"><option value=""<?php if(!$_POST[$res2['name']] || ($editmode && !$editmodevars[$res2['name']])) { ?> selected="selected"<?php } ?>>N/a</option><?php
				$opts = explode(',',$res2['options']);
				foreach($opts as $k=>$v)
				{
					?><option value="<?php echo $v; ?>"<?php if($_POST[$res2['name']]==$v || ($editmode && $editmodevars[$res2['name']]==$v)) echo " selected=\"selected\""; ?>><?php echo $v; ?></option><?php
				}
				echo "</select>\n";
				if($opts[0]==1) echo "&nbsp;1 = Worst; 5 = Best"; // add a label for 1-5 options
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

if($editmode && $editmodevars['files'])
{
	$filenames = explode('; ', $editmodevars['files']);
}
else
{
	$filenames = $_FILES;
}
if($editmode)
{

	?><tr><td align="right" class="newrow" width="50%"><?php var_dump($filenames); ?><input type="hidden" name="editmodefiles" value="<?php echo $editmodevars['files']; ?>">Download Attached File(s):</td><td align="left"><?php
	foreach($filenames as $file)
	{
		if($file != '')
		{
		?><input type="radio" name="files" value="<?php echo $file; ?>"><?php echo $file; ?><br /><?php
		}
	}
	?></td></tr><?php
}
?>
<tr><td align="right" class="newrow">Attach File(s):</td><td><input type="file" name="upfile[]" size="30"<?php if($filenames) echo " value=\"".$filenames[0]."\""; ?>><button onclick="additionalFile();" type="button">Add Another File</button></td></tr>
<tr id="newfile"><?php
if(count($filenames)>1)
{
	for($i=1; $i<count($filenames); $i++)
	{
?><td align="right" class="newrow">Attach File(s):</td><td><input type="file" name="upfile[]" size="30" value="<?php echo $filenames[$i]; ?>"><button onclick="additionalFile(<?php echo $i; ?>);" type="button">Add Another File</button></td></tr>
<tr id="newfile<?php echo $i; ?>"><?php }
} ?>
</tr>
<tr><TD colspan="2" align="center"><input type="submit" value="Submit Report"></TD></tr>
</table>
</form>
</body>
</html>