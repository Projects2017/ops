<?php
/*********************************************************************
    Licensed for Jeff Hosking (PMD Furniture) by Radium Development
        (c) 2004 Radium Development with full rights granted to
                   Jeff Hosking (PMD Furniture)
 *********************************************************************/
//die("Currently Offline for MySQL repairs, try again in 10 minutes");
// This is meant to test form.inc.php
// This isn't actually meant to be used as a template or anything
// *********************************************************************************************************
//   =============== INCLUDES SECTION ====================
// Secure doc
$duallogin = 1;
include("database.php");
include("vendorsecure.php");
if (!$vendorid)
	include("secure.php");

// Include form functions
include("form.inc.php");
// *********************************************************************************************************
//   =============== INITIALIZATION SECTION ====================
// Determine form
$form = $_REQUEST['form'];
// Action Instruction (i.e. Display)
$action = $_REQUEST['action'];
// Order Display by field
if (isset($_REQUEST['order']))
	$order = $_REQUEST['order'];
// Id for editing
$updateid = $_REQUEST['updateid'];
$deleteid = $_REQUEST['deleteid'];
$viewid = $_REQUEST['viewid'];
$delfile = $_REQUEST['delfile'];
$splitclaim = $_REQUEST['splitclaim'];
if ($_REQUEST['comment']) {
	$new_comment = $_REQUEST['comment'];
}
//$f_vendor = $_REQUEST['f_vendor'];

$sendemail = $_REQUEST['sendemail'];
// Get data from Insert or Update action (data_columnname fields) only postable
foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^data_(.+)",$key, $reg))
		$data[$reg[1]] = $value;
}
$filter = array();
foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^f_(.+)",$key, $reg))// &amp;& $value
		$filter[$reg[1]] = $value;
}
// Get old data for update forms
foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^old_(.+)",$key, $reg))
		$old[$reg[1]] = $value;
}
foreach($_FILES as $key => $value) {
	if(ereg("^data_(.+)",$key, $reg)) {
		$data[$reg[1]] = $value['file'];
		$old[$reg[1]] = $value['file']."_old";
	}
}
if ($form)
{
	$forminfo = forminfo($form);
	$formprop = forminfo($form,1);
}
$formlist = formlistforms();
if (($formprop['massedit'] && (secure_is_admin() || (!secure_is_dealer()))) ||($formprop['massdelete'] && secure_is_admin())) {
	if ($_REQUEST['mumode']) {
		$mumode = 1;
	} else {
		$mumode = 0;
	}
	$mucapable = 1;
} else {
	$mucapable = 0;
}
// (secure_is_admin() || (!secure_is_dealer()))
//   == DEFAULT FILTER ==
if ($formprop['default_vendor_filter'] && !secure_is_dealer()) {
	$farray = explode(",",$formprop['default_vendor_filter']);
	foreach ($farray as $x) {
		$x = explode("=",$x);
		if (!array_key_exists($x[0],$filter)) // Add filter on default if it's not present
			$filter[$x[0]] = $x[1];
	}
	unset($x);
	unset($farray);
} elseif ($formprop['default_filter']) {
	$farray = explode(",",$formprop['default_filter']);
	foreach ($farray as $x) {
		$x = explode("=",$x);
		if (!array_key_exists($x[0],$filter)) // Add filter on default if it's not present
			$filter[$x[0]] = $x[1];
	}
	if ((!$filter['userteam'])) {
                $filter['userteam'] = $dealerteam;
	}
	unset($x);
	unset($farray);
}

// *********************************************************************************************************
//   =============== JAVASCRIPT SECTION ====================
function form_javascript() {
	?>
	<SCRIPT type='text/javascript'>
	function jump_to_claim(query_string) {
		var str = query_string.replace(/action=(view|update)/,"action=" + document.getElementById('jump_claim_view_mode').value);
		str = str.replace(/(viewid|updateid)=[\d]+/, document.getElementById('jump_claim_view_mode').value + "id=" + document.getElementById('jump_to_claim_input').value);
		
		window.location = "form.php?" + str;
	
	}
	</SCRIPT>
	<?php
}
// *********************************************************************************************************
//   =============== OUTPUT SECTION ====================

// ********** MODE SELECTION
if (!$forminfo || !$action) {
?>
<!-- Invalid Form HTML -->
<HTML><HEAD><TITLE>RSS Claims Database - Invalid Form</TITLE>
<link rel="stylesheet" href="/styles.css" type="text/css">
<?php javascript(); form_javascript(); ?>
</HEAD>
<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
<?php require('menu.php'); ?>

<?php drawmenu(0); ?>

Please choose an option from the menu above.


</BODY>
</HTML>
<?php
exit(0);
}
// ********** FORMINFO SELECTION
if ($action == "forminfo") {
	drawmenu($form); ?><br><?php if (secure_is_admin()) { echo "Your access level: ADMINISTRATOR"; } ?><br><?php
	echo "<PRE>";
	echo "forminfo('".$form."');\n";
	print_r($forminfo);
	echo "\nforminfo('".$form."',1);\n";
	print_r($formprop);
	echo "\nformlistforms();\n";
	print_r($formlist);
// ********** DISPLAY SUMMARY SELECTION
} elseif ($action == "display") {
	$bigarray = formdata($form, $order,$filter);
	?>
	<HTML><HEAD><TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?> Editor</TITLE>
		<link rel="stylesheet" href="/styles.css" type="text/css">
		<?php javascript(); form_javascript(); ?>
		</HEAD>
		<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
		<?php require('menu.php'); ?>


	<?php
	drawmenu($form);
?> <CENTER><CENTER><FONT FACE=ARIAL SIZE=3><B><?php echo $formprop["long_name"]; ?> Editor</B></FONT></CENTER><P>
<?php
//print_r($bigarray[0]);  //unrem for testing purposes to see 1st row in result
echo "<FORM method=\"POST\" enctype=\"multipart/form-data\">\n";
if (secure_is_admin()) {
	echo "\tTeam: <SELECT name=\"f_userteam\">\n";
	echo "\t\t<OPTION VALUE=\"*\">*</OPTION>\n";
	$teamlist = teams_list();
	foreach ($teamlist as $value) {
		echo "\t\t<OPTION VALUE=\"".$value."\"";
		if ($filter['userteam'] == $value)
			echo " SELECTED";
		echo ">".$value."</OPTION>\n";
	}
	echo "</SELECT>\n";
}
if (secure_is_admin() || (!secure_is_dealer())) {
	echo "\tDealer: <SELECT name=\"f_user_id\">\n";
	$userlist = db_user_getlist();
	echo "\t\t<OPTION VALUE=0>All Dealers</OPTION>\n";
	foreach ($userlist as $value) {
		echo "\t\t<OPTION VALUE=\"".$value[id]."\"";
		if ($filter['user_id'] == $value[id])
			echo " SELECTED";
		echo ">".$value[last_name]."</OPTION>\n";
	}
	echo "</SELECT>\n";
}
if (secure_is_dealer()) {
	echo "\tVendor: <SELECT name=\"f_vendor_id\">\n";
	$vendorlist = db_vendor_getlist();
	echo "\t\t<OPTION VALUE=0>All Vendors</OPTION>\n";
	foreach ($vendorlist as $value) {
		echo "\t\t<OPTION VALUE=\"".$value[id]."\"";
		if ($filter['vendor_id'] == $value[id])
			echo " SELECTED";
		echo ">".$value[name]."</OPTION>\n";
	}
	echo "</SELECT>\n";
}
/* if (secure_is_admin()) {
	echo "Vendor Type: <SELECT name=\"f_vendortype\">";
	echo "<OPTION VALUE=0>All Types</OPTION>";
	echo "<OPTION VALUE=\"furniture\"";
	if ($filter['vendortype'] == "furniture") {
		echo " SELECTED";
	}
	echo ">Furniture</OPTION>";
	echo "<OPTION VALUE=\"bedding\"";
	if ($filter['vendortype'] == "bedding") {
		echo " SELECTED";
	}
	echo ">Bedding</OPTION>";
	echo "</SELECT><BR>";
} */
echo "\t Status: <SELECT name=\"f_status\">\n";
echo "\t\t<OPTION VALUE=\"!9\">All Status Options</OPTION>\n";
// Only Open Claims
echo "\t\t<OPTION VALUE=\"-5\"";
if (strcmp($filter['status'],"-5") == 0)
	echo " SELECTED";
echo ">All Open Claims</OPTION>\n";
// Only Vendor Required Claims
echo "\t\t<OPTION VALUE=\"|2|6\"";
if (strcmp($filter['status'],"|2|6") == 0)
	echo " SELECTED";
echo ">All Vendor Claims</OPTION>\n";
// Only Closed Claims
echo "\t\t<OPTION VALUE=\"+6\"";
if (strcmp($filter['status'],"+6") == 0)
	echo " SELECTED";
echo ">All Closed Claims</OPTION>\n";
// The rest of the options
foreach ($claims_status as $key => $value) {
	echo "\t\t<OPTION VALUE=\"".$key."\"";
	if (strcmp($filter['status'],$key) == 0)
		echo " SELECTED";
	echo ">".$value."</OPTION>\n";
}
echo "\t</SELECT>\n";
if (secure_is_admin() && $formprop['sms'] == '1') {
	echo "\tSMS: <SELECT name=\"f_upsincesms\">\n";
	$vendorlist = db_vendor_getlist();
	echo "\t\t<OPTION VALUE=0>Any</OPTION>\n";
        if ($filter['upsincesms'] == '1') {
           echo "\t\t<OPTION VALUE=1 SELECTED>Updated</OPTION>\n";
        } else {
            echo "\t\t<OPTION VALUE=1>Updated</OPTION>\n";
        }
        if ($filter['upsincesms'] == '2') {
           echo "\t\t<OPTION VALUE=2 SELECTED>Sent SMS</OPTION>\n";
        } else {
            echo "\t\t<OPTION VALUE=2>Sent SMS</OPTION>\n";
        }
	echo "</SELECT>\n";
}
?><input type=submit value="Filter">
<?php
echo "</FORM>\n";
if ($mumode) {
	
	echo "<FORM method=\"POST\" enctype=\"multipart/form-data\" action=\"".$SERVER['PHP_SELF']."?".urlfilter($filter)."\">";
	?><input type="hidden" NAME="action" VALUE="massupdate"><input type="hidden" NAME="form" VALUE="<?php echo $form; ?>"><?php
	?><input type=submit value="Mass Update"><?php
} elseif ($mucapable) {
	echo "<BR><A HREF=\"".$SERVER['PHP_SELF']."?form=".$form."&action=display&mumode=1&".urlfilter($filter)."\">\n";
	echo "Switch to Mass Edit Mode";
	echo "</A>";
}
	if ($bigarray) {
		if (count($bigarray) >= 2000) echo "<center class=\"fat_black_12\">More than 2000 results, first 2000 results shown.</center>";
		echo "<table border=1 cellspacing=2 cellpadding=2>\n";
		//echo "<TR>
		echo "\t<TR><TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>ACTION</FONT></TH>";



		foreach($bigarray[0] as $value => $junk) {
			if (!$forminfo[$value]['on_summary']) {
				// Display Nothing!
			} elseif ($value == "clientip") {
				if (secure_is_admin())
					echo "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".ucwords($value)."</FONT></TH>";
      } elseif ($value == "cr_link") {
        if (secure_is_admin() || secure_is_vendor()) {
					echo "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><a href=\"?form=". urlencode($form)."&action=display&.".urlfilter($filter)."&order=".urlencode($value)."\">".
  				$forminfo[$value]['nicename']."</FONT></TH>"; 
        }
			} else {
				if ($order == "!".$value)
					echo "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><a href=\"?form=". urlencode($form)."&action=display&.".urlfilter($filter)."&order=".urlencode($value)."\">".
				$forminfo[$value]['nicename']."</FONT></TH>";
				elseif ($order == $value)
				    echo "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><a href=\"?form=". urlencode($form)."&action=display&.".urlfilter($filter)."&order=".urlencode("!".$value)."\">".
				$forminfo[$value]['nicename']."</FONT></TH>";
				else
					echo "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><a href=\"?form=". urlencode($form)."&action=display&.".urlfilter($filter)."&order=".urlencode($value)."\">".
				$forminfo[$value]['nicename']."</FONT></TH>";
			}
		}


		foreach($bigarray as $value) {
			//if ($f_vendor && ($f_vendor != $value['vendor_id']))
			//	continue;
			$bgcolor = "#FFFFFF";
			
				

			if (secure_is_admin()) {
				if($formprop['sms'] && $value['upsincesms'] >= 1) {
					$bgcolor = "#C0C0C0";
				} else {
					$bgcolor = "#FFFFFF";
				}
				echo "\t<TR id='claim".$value['id']."' bgcolor='$bgcolor'><TD class=text_12>";
				if ($formprop['sms']) {
                                    echo "<A HREF=javascript:void(0); onclick=javascript:window.open('sms.php?updateid=".$value['id']."','smsWin','height=300,width=400,addressbar=0,status=1,toolbar=0,menubar=0,location=0');><IMG id='smsAdmin".$value['id']."' BORDER=0 ALT='T'";
                                    echo "SRC=/images/cellPhone";
                                    if ($value['upsincesms'] == '2') {
                                        echo '_sent';
                                    }
                                    echo ".gif></A>&nbsp;|&nbsp;";
                                }
				echo "<A HREF=".$SERVER['PHP_SELF']."?action=update&form=".$form."&updateid=".$value['id']."><IMG BORDER=0 ALT='E' SRC=/images/button_edit.png></A>&nbsp;|&nbsp;";
				if ($mumode && $formprop['massdelete']) {
					?><input TYPE="checkbox" NAME="data_delete_<?php echo $value['id']; ?>"><?php
				} else {
					echo "<A HREF=".$SERVER['PHP_SELF']."?action=delete&form=".$form."&deleteid=". $value['id']." ";
					?>onClick="return confirm('Are you sure you wish to delete this record?');"><?php
					echo "<IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></A>";
				}
				echo "&nbsp;|&nbsp;<A HREF=".$SERVER['PHP_SELF']."?action=view&form=".$form."&viewid=". $value['id']."><IMG BORDER=0 SRC=/images/mag.gif ALT='V'></A>";
				if ($formprop['cansplit']) {
					echo "&nbsp;|&nbsp;<A HREF=".$SERVER['PHP_SELF']."?action=split&form=".$form."&updateid=". $value['id'];
					?> onClick="return confirm('Are you sure you wish to split this record?');"<?php
					echo "><IMG BORDER=0 SRC=/images/split.jpg ALT='S'></A>";
				}
				echo "</TD>";
			} elseif (secure_is_vendor()) {
				echo "\t<TR bgcolor=#FFFFFF><TD class=text_12><A HREF=".$SERVER['PHP_SELF']."?action=update&form=".$form."&updateid=".$value['id']."><IMG BORDER=0 ALT='E' SRC=/images/button_edit.png></A>&nbsp;|&nbsp;<A HREF=".$SERVER['PHP_SELF']."?action=view&form=".$form."&viewid=". $value['id']."><IMG BORDER=0 SRC=/images/mag.gif ALT='V'></A>";
				if ($formprop['cansplit']) {
					echo "&nbsp;|&nbsp;<A HREF=".$SERVER['PHP_SELF']."?action=split&form=".$form."&updateid=". $value['id'];
					?> onClick="return confirm('Are you sure you wish to split this record?');"<?php
					echo "><IMG BORDER=0 SRC=/images/split.jpg ALT='S'></A>";
				}
				echo "</TD>";
			} else {
				echo "\t<TR bgcolor=#FFFFFF><TD class=text_12><A HREF=".$SERVER['PHP_SELF']."?action=view&form=".$form."&viewid=". $value['id']."><IMG BORDER=0 SRC=/images/mag.gif ALT='V'></A></TD>";
			}

   // do a quick loop to grab a PO # now before we build the table
   foreach ($value as $k => $v) {
   	if($k=="po" || $k == "PO#") {
   		$po_id = $v;
   	}
   }
   
   // getShippingData (found in form.inc.php) grabs the shipping data from the shipping tables into an array
   // carrier[] = carrier name; tracking[] = tracking #; shipdate[] = ship date
   //$shipping = getShippingData($po_id);
   
			foreach ($value as $key => $x) {
          if($key == "cr_link" && (!secure_is_admin() && !secure_is_vendor())) continue;
					$xdispbefore = "";
					$xdispafter = "";
					if ($forminfo[$key]['url_prefix'] && $x!="$") {
						$xdispbefore = "<A HREF=\"".$forminfo[$key]['url_prefix'].urlencode($x)."\" TARGET=_new><FONT FACE=ARIAL COLOR=#000000>";
						$xdispafter = "</FONT></A>";
					} elseif ($forminfo[$key]['url_prefix']) {						
						$xdispbefore = "<A HREF=\"".$forminfo[$key]['url_prefix'].($po_id-1000)."\" STYLE=\"text-align: center\" TARGET=_new><FONT FACE=ARIAL COLOR=\"GREEN\">";
						$xdispafter = "</FONT></A>";
          }
					if (!$forminfo[$key]['on_summary']) {
						// Display Nothing!
					} elseif ($key == "timestamp") {
						// YYYYMMDDHHMMSS
						$x = timestamp2date($x);
						echo "<td class=text_12>".$xdispbefore.$x.$xdispafter."</TD>";
					} elseif ($key == "clientip") {
						if (secure_is_admin())
							echo "<td class=text_12>".$x."</TD>";
					} elseif (($forminfo[$key]['datatype'] == "checkbox")&&($mumode)&&($forminfo[$key]['massedit'])&&($forminfo[$key]['edit'])) {
					 	?>
					 	<td class=text_12><center><input TYPE="checkbox" NAME="data_<?php echo $value['id']; ?>_<?php echo $key ?>" <?php if ($x == "on") echo "CHECKED";?>></center>
					 	<input TYPE="hidden" NAME="old_<?php echo $value['id']; ?>_<?php echo $key ?>" VALUE="<?php echo $x; ?>"></td>
					 	<?php
					} elseif ($forminfo[$key]['datatype'] == "checkbox") {
						echo "<td class=text_12><center>";
						if ($x == "on")
							echo "X";
						else
							echo "&nbsp;";
						echo "</center></td>";
					} elseif ($key == "user_id") {
						echo "<td class=text_12>".$xdispbefore.db_user_getuserinfo($x, "last_name").$xdispafter."</TD>";
					} elseif ($key == "vendor_id") {
						echo "<td class=text_12>".$xdispbefore.db_vendor_getinfo($x, "name").$xdispafter."</TD>";
					} elseif ($key == "status") {
						echo "<td class=text_12>".$xdispbefore.$claims_status[$x].$xdispafter."</TD>";
	//goody added clickable po per gary 08-05-2004
					} elseif (($forminfo[$key]['datatype'] == "upload") && ($x == "1")) {
						echo "<td class=text_12>";
						show_upload_links($form, $value['id']);
						echo "</td>";
					} elseif (($forminfo[$key]['datatype'] == "upload") && ($x == "0")) {
						echo "<TD CLASS=text_12>None</TD>";
					} else {
						echo "<td class=text_12>";
						// insert shipping info if applicable
/*						if($key == "carrier")
						{
							if($shipping['carrier'])
							{
								$colors = array('yellow','silver');
								$thiscolor = 0;
								foreach($shipping['carrier'] as $car)
								{
									echo '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									$thiscolor = $thiscolor==0 ? 1 : 0;
									echo $car."</span><br />\n";
								}
							}
						}
						if($key == "tracking")
						{
							if($shipping)
							{
								$colors = array('yellow','silver');
								$thiscolor = 0;
								foreach($shipping as $car)
								{
									//echo '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									//$thiscolor = $thiscolor==0 ? 1 : 0;
									echo $car;
									//echo "</span>";
									echo "<br />\n";
								}
							}
						}
						if($key == "shipdate")
						{
							if($shipping['shipdate'])
							{
								$colors = array('yellow','silver');
								$thiscolor = 0;
								foreach($shipping['shipdate'] as $car)
								{
									echo '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									$thiscolor = $thiscolor==0 ? 1 : 0;
									echo $car."</span><br />\n";
								}
							}
						}
*/						
						if (($mumode)&&($forminfo[$key]['massedit'])&&($forminfo[$key]['edit'])) {
							?>
							<input TYPE="text" NAME="data_<?php echo $value['id']; ?>_<?php echo $key ?>"<?php if ($forminfo[$key]['limit'] != -1) { ?> MAXLENGTH="<?php echo $forminfo[$key]['limit']; ?>"<?php } ?> VALUE="<?php echo $x; ?>">
							<input TYPE="hidden" NAME="old_<?php echo $value['id']; ?>_<?php echo $key ?>" VALUE="<?php echo $x; ?>"><?php
						} else {
							echo $xdispbefore.$x.$xdispafter;
						}
						echo "</TD>";
					}
					$xdisp = "";
			}
			echo "\t</TR>\n";
		}
		echo "</TABLE>\n";
		if ($mumode) {
			?><input type=submit value="Mass Update">
			<?php
		}
		echo "<BR>\n";
			if (secure_is_admin()) {
				echo "<B>ACTION LEGEND:</B><BR><IMG BORDER=0 ALT='E' SRC=/images/cellPhone.gif> = TEXT&nbsp;|&nbsp;<IMG BORDER=0 SRC=/images/button_edit.png> = EDIT&nbsp;|&nbsp;<IMG BORDER=0 SRC=/images/button_drop.png> = DELETE&nbsp;|&nbsp;<IMG BORDER=0 SRC=/images/mag.gif ALT='V'> = VIEW<BR><I>Please note, all updates and deletions are effective immediately.</I>\n";
			} else {
				echo "<B>ACTION LEGEND:</B><BR><IMG BORDER=0 SRC=/images/mag.gif ALT='V'> = VIEW\n";
			}
	} else { ?>
	<table border=0 cellspacing=0 cellpadding=5>
		<TR><TH bgcolor=#CCCC99>No records on File</TH></TR>
	</TABLE>
	<?php }
	if ($mumode)  {
		echo "</FORM>\n";
	}
	?>
	</CENTER>
	</BODY></HTML>
	<?php
// ********** INSERT FORM DISPLAY SELECTION
} elseif (($action == "insert") && (!$data)) {
	// Remove all non-insertable items
	foreach ($forminfo as $k => $i) {
		if ($i["insert"])
			$x[$k] = $i;
	}
	$forminfo = $x;
	unset($x);
	unset($i);
	unset($k);
	?>
	<HTML><HEAD>	<TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?> Insert Form</TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); form_javascript(); ?>
	</HEAD>
	<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require('menu.php'); ?>
	<?php drawmenu($form); ?>
	<CENTER>
	<CENTER><FONT FACE=ARIAL SIZE=3><B><?php echo $formprop["long_name"]; ?> Insert Form</B></FONT></CENTER><br>
	<P>
		<CENTER><?php echo $formprop['desc']; ?></CENTER>
	</P>
    <P>
		<CENTER><?php echo $formprop['contact_info']; ?></CENTER>
	</P>
	<CENTER>
	<TABLE>
	<FORM method="POST" enctype="multipart/form-data">
	<?php
	foreach($forminfo as $value) {
		if ($value['id'] == "user_id") {
			if (secure_is_admin() || secure_is_vendor()) {
				echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:</FONT></TD><TD><FONT FACE=ARIAL><SELECT NAME="data_<?php=$value['id']; ?>">
				<?php
				$temp = db_user_getlist('*',0,"*","*","*","*","*");
				foreach ($temp as $d) {
					echo "<OPTION VALUE=\"".$d["id"]."\"";
					if ($d['id'] == $_GET['auto_'.$value['id']])
						echo "SELECTED";
					elseif ((!$_GET['auto_'.$value['id']]) && $d["id"] == $GLOBALS['userid'])
						echo "SELECTED";
					echo ">".$d["last_name"];
				}
				echo "</SELECT>";
			}
		} elseif ($value['id'] == "vendor_id") {
			if (!secure_is_vendor()) {
				echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:</FONT></TD><TD><FONT FACE=ARIAL><SELECT NAME="data_<?php=$value['id']; ?>">
				<?php
				$temp = db_vendor_getlist();
				foreach ($temp as $d) {
					echo "<OPTION VALUE=\"".$d["id"]."\"";
					if ($d['id'] == $_GET['auto_'.$value['id']])
						echo " SELECTED";
					echo ">".$d["name"];
				}
				echo "</SELECT>";
            }
		} elseif ($value['datatype'] == "upload") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>
			:<?php if ($forminfo[$value['id']]['note']) { ?>
				<BR>(<?php echo $forminfo[$value['id']]['note']; ?>)
					
				<?php } ?></TD><TD> <input TYPE="file" NAME="data_<?php echo $value['id'] ?>"><?php
			if ($value['required'])
				echo "";
			else
				echo "(optional)</TD></TR>";
		} elseif ($value['datatype'] == "select") { 
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>
			:</FONT><?php if ($forminfo[$value['id']]['note']) { ?>
				<BR><FONT FACE=ARIAL SIZE=-2 COLOR="darkred"><B>(<?php echo $forminfo[$value['id']]['note']; ?>)</B></FONT>
					
				<?php } ?></TD><TD> <SELECT NAME="data_<?php echo $value['id'] ?>"><?php
			$valuelist = array();
			$valuelist = explode('|',$value['datatype_special']);
			foreach ($valuelist as $g) {
				echo "<OPTION VALUE=\"".$g."\"";
				if ($g == $_GET['auto_'.$value['id']])
					echo " SELECTED";
				echo ">".$g;
			}
			echo "</SELECT>";
		} elseif ($value['datatype'] == "date") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:<?php if ($forminfo[$value['id']]['note']) { ?>
				<BR>(<?php echo $forminfo[$value['id']]['note']; ?>)
					
				<?php } ?></FONT></TD><TD><input TYPE="text" NAME="data_<?php echo $value['id'] ?>"<?php if ($forminfo[$value['id']]['limit'] != -1) { ?> MAXLENGTH="<?php echo $forminfo[$value['id']]['limit']; ?>"<?php }
				 if ($_GET['auto_'.$value['id']])
					echo " value=\"".$_GET['auto_'.$value['id']]."\"";
				 ?>><?php
			echo "(date:YYYY-MM-DD)";
			if ($value['required'])
				echo "(required)</FONT></TD></TR>";
			else
				echo "(optional)";
		} elseif (($value['datatype'] == "text") && ($value['multiline'] == 1)) {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:<?php if ($forminfo[$value['id']]['note']) { ?>
				<BR>(<?php echo $forminfo[$value['id']]['note']; ?>)
					
				<?php } ?></FONT></TD><TD><FONT FACE=ARIAL><textarea NAME="data_<?php echo $value['id'] ?>"><?php
				if ($_GET['auto_'.$value['id']])
					echo $_GET['auto_'.$value['id']];
				?></TEXTAREA><?php
			echo "(".$value['datatype'].")";
			if ($value['required'])
				echo "(required)</FONT></TD></TR>";
			else
				echo "(optional)</FONT></TD></TR>";
		} elseif ($value['datatype'] == "checkbox") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:<?php if ($forminfo[$value['id']]['note']) { ?>
				<BR>(<?php echo $forminfo[$value['id']]['note']; ?>)
					
				<?php } ?></FONT></TD><TD><FONT FACE=ARIAL><input TYPE="checkbox" NAME="data_<?php echo $value['id'] ?>"<?php
				if ($_GET['auto_'.$value['id']] == 'yes')
					echo ' CHECKED';
				?>><?php
		} else {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$value['id']]['nicename']; ?>:<?php if ($forminfo[$value['id']]['note']) { ?>
				<BR>(<?php echo $forminfo[$value['id']]['note']; ?>)
					
				<?php } ?></FONT></TD><TD><FONT FACE=ARIAL><input TYPE="text" NAME="data_<?php echo $value['id'] ?>"<?php if ($forminfo[$value['id']]['limit'] != -1) { ?> MAXLENGTH="<?php echo $forminfo[$value['id']]['limit']; ?>"<?php }
				 if ($_GET['auto_'.$value['id']])
					echo " value=\"".$_GET['auto_'.$value['id']]."\"";
				 ?>><?php
			echo "(".$value['datatype'].")";
			if ($value['required'])
				echo "(required)</FONT></TD></TR>";
			else
				echo "(optional)</FONT></TD></TR>";
		}
		?><?php
	}
	?><TR><TD>&nbsp;</TD><TD><FONT FACE=ARIAL><input type="hidden" NAME="action" VALUE="insert">
	<input type="hidden" NAME="form" VALUE="<?php echo $form; ?>">
	<input type=submit value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Submit Claim&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
	</FONT></FORM></TD></TR></TABLE><BR>
	</CENTER>

<?php
// ********** INSERT DATA SELECTION
} elseif (($action == "insert") && ($data)) {
?>		<HTML><HEAD><TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?> Editor Error</TITLE>
			<link rel="stylesheet" href="/styles.css" type="text/css">
			<?php javascript(); form_javascript(); ?>
			</HEAD>
			<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
			<?php require('menu.php'); ?>
		<?php drawmenu($form); ?>
		<center>
<?php
	unset($success);
	$success = forminsert($form, $data);
	if (!$success) {
?>
		    The following information has been submitted in <?php echo $form ?><br>
		    <a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=view&viewid=<?php echo $global_last_insert_id; ?>">View your newly submitted claim</a><br>
		    <?php if (secure_is_admin()) { ?>
		    <a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $global_last_insert_id; ?>">Edit your newly submitted claim</a><br>
		    <?php }?>
			<p>
			<TABLE><TR BGCOLOR=#CCCC99><TD BGCOLOR=#CCCC99><B>FIELD</B></TD><TD BGCOLOR=#CCCC99><B>NEW VALUE</B></TD></TR>
			<?php
			foreach($data as $key => $value) {
				echo "<TR><TD BGCOLOR=#CCCC99>".$forminfo[$key]['nicename']."</TD><TD BGCOLOR=#FFFFFF>".$value."</TD></TR>";
			}

	?> </TABLE><P>
<?php


	} else {
		#echo "An error occurred processing this update.";
		#print_r($success);
		#echo "<P>";
?>
		<CENTER><TABLE><TR><TD> <?php

		if (count($success['missing'])) { ?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are missing:
			<UL>
			<?php
			foreach($success['missing'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename'];
				echo "</LI>";
			}
			?> </FONT></UL></P> <?php
		}
		if (count($success['wrongtype'])) { 	?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are of the wrong type:
			<UL>
			<?php
			foreach($success['wrongtype'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename']." - Should be a ".$forminfo[$value]['datatype'];
				echo "</LI>";
			}
			?> </UL></FONT></P> <?php
		}  
		if (count($success['tolong'])) { 	?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are too long:
			<UL>
			<?php
			foreach($success['tolong'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename']." - Should be under ".$forminfo[$value]['limit']." charactors long.";
				echo "</LI>";
			}
			?> </UL></FONT></P> <?php
		}  
		?>
		</TD></TR></TABLE></CENTER></CENTER>
		</BODY></HTML> <?php
	}
// ********** UPDATE DISPLAY SELECTION
} elseif ((($action == "update") && (!$data)) && isset($updateid)) {
	if (!(secure_is_vendor()||secure_is_admin())) die ("You don't have access to edit");
	$oldform = formselect($form, $updateid);
	if (!$oldform)
		die("ID not present in database");
	$comments = formcomments($form, $updateid);
	?>
	<HTML><HEAD><TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?> Update Form</TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); form_javascript(); ?>
	</HEAD>
	<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require('menu.php'); ?>
	<?php drawmenu($form, 1); ?>

<CENTER>

	<CENTER><FONT FACE=ARIAL SIZE=3><B><?php echo $formprop["long_name"]; ?> Editor</B></FONT></CENTER><br>
	<CENTER><FONT FACE=ARIAL SIZE=3>
		<A href='<?php
			$qs = str_replace(array("action=update", "updateid="), array("action=view", "viewid="), $_SERVER['QUERY_STRING']);
			echo "form.php?" . $qs;
				?>'>Switch to form view</A><BR>
				Jump to Claim: <INPUT type='input' id='jump_to_claim_input' value='<?php echo $_GET['updateid']; ?>'> <SELECT name='jump_claim_view_mode'><OPTION value='view'>View</OPTION><OPTION value='update'>Edit</OPTION></SELECT> <INPUT type='button' value='Go' onclick='jump_to_claim("<?php echo $_SERVER['QUERY_STRING']; ?>");'>
	</FONT></CENTER>
	<FORM method="POST" enctype="multipart/form-data">
	<CENTER>
	<TABLE>
	<?php
	foreach($oldform as $key => $value) {
		if (($key == "id") || ($key == "clientip")) {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</FONT></TD><TD> <?php echo $value; ?><input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"></TD></TR> <?php
		} elseif ($key == "user_id") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</FONT></TD><TD> <?php echo db_user_getuserinfo($value, "last_name"); ?><input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"></TD></TR> <?php
		} elseif ($key == "vendor_id") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</FONT></TD><TD><FONT FACE=ARIAL><SELECT NAME="data_<?php=$key; ?>">
			<?php
			$temp = db_vendor_getlist();
			foreach ($temp as $d) {
				echo "<OPTION VALUE=\"".$d["id"]."\"";
				if ($d["id"] == $value)
					echo " SELECTED";
				echo ">".$d["name"];
			}
			echo "</SELECT>";
			?><input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"></TD></TR><?php
		} elseif ($key == "timestamp") {
			unset($x);
			// YYYYMMDDHHMMSS
			$x = timestamp2datetime($value);
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</FONT></TD><TD> <?php echo $x; ?><input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"></TD></TR> <?php
		} elseif ($key == "status") {
				echo "<TD ALIGN=RIGHT bgcolor=#CCCC99>";
				?><input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"><?php
				echo "<FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</TD><TD> <?php if ($forminfo[$key]["edit"]) {
					echo "<SELECT NAME=\"data_".$key."\">";
					foreach ($claims_status as $k => $x) {
						if (secure_is_dealer()||((($k == 2)||($k == 5)||($k == $value))))
							if ($k != 0) {
								echo "<option ";
								echo "value=\"".$k."\"";
								if ($k == $value)
									echo " selected";
								echo ">".$x."</option>";
							}
					}
					echo "</SELECT>";
			   } else {
				echo $claims_status[$value];
			   }
		} elseif ($forminfo[$key]['datatype'] == "upload") {
			echo "<TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>
			:</TD><TD> <input TYPE="file" NAME="data_<?php echo $key ?>"><?php
		} elseif ($forminfo[$key]['datatype'] == "select") {
			echo "<TR><TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>
			:</TD><TD> <SELECT NAME="data_<?php echo $key ?>"><?php
			$valuelist = array();
			$valuelist = explode('|',$forminfo[$key]['datatype_special']);
			$selected = 0;
			foreach ($valuelist as $g) {
				echo "<OPTION VALUE=\"".$g."\"";
				if ($g == $value) {
					echo " SELECTED";
					$selected = 1;
				}
				echo ">".$g;
			}
			if ($selected == 0)
				echo "<OPTION VALUE=\"".$value."\" SELECTED>$value";
			echo "</SELECT>";
			?><input type="hidden" name="old_<?php echo $key; ?>" value="<?php echo $value; ?>">
			<?php echo "\n\n";
		} elseif ($forminfo[$key]['datatype'] == "checkbox") {
			echo "<TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</TD><TD>
			<?php if ($forminfo[$key]["edit"]) { ?>
					<input TYPE="checkbox" NAME="data_<?php echo $key ?>" <?php if ($value == "on") echo "CHECKED";?>>
					<?php
					if ($forminfo[$key]['required'])
						echo "<FONT FACE=ARIAL>(cannot be blank)</FONT>";
			} else {
				if ($value == "on")
					echo "X";
			} ?>
			<input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"><?php
		} elseif ($forminfo[$key]['datatype'] == "date") {
			echo "<TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</TD><TD>
			<?php if ($forminfo[$key]["edit"]) { ?>
			<input TYPE="text" NAME="data_<?php echo $key ?>" VALUE="<?php echo $value; ?>"<?php if ($forminfo[$key]['limit'] != -1) { ?> MAXLENGTH="<?php echo $forminfo[$key]['limit']; ?>"<?php } ?>>
			<?php } else { ?>
			<?php echo $value ?>
			<?php } ?>
			<input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"><?php
			echo "<FONT FACE=ARIAL>(".$forminfo[$key]['datatype'].")(YYYY-MM-DD)</FONT>";
			if ($forminfo[$key]['required'])
				echo "<FONT FACE=ARIAL>(cannot be blank)</FONT>";
		} else {
			echo "<TD ALIGN=RIGHT bgcolor=#CCCC99><FONT FACE=ARIAL>".$forminfo[$key]['nicename']; ?>:</TD><TD> <?php if ($forminfo[$key]["edit"]) { ?>
					<input TYPE="text" NAME="data_<?php echo $key ?>" VALUE="<?php echo $value; ?>"<?php if ($forminfo[$key]['limit'] != -1) { ?> maxlength="<?php echo $forminfo[$key]['limit']; ?>"<?php } ?>>
					<?php echo "<FONT FACE=ARIAL>(".$forminfo[$key]['datatype'].")</FONT>";
					if ($forminfo[$key]['required'])
						echo "<FONT FACE=ARIAL>(cannot be blank)</FONT>";
			   } else {
				echo $value;
			   } ?>
			<input TYPE="hidden" NAME="old_<?php echo $key ?>" VALUE="<?php echo $value; ?>"><?php
		}
		?></FONT></TD></TR><?php
	}
	?>
	<TR><TD COLSPAN=2><FONT FACE=ARIAL>	<CENTER>
	<TABLE width=200>
	  <TR>
	    <TH colspan=4>
		  Comments
		</TH>
	  </TR>
	<?php
	if ($comments) {
		foreach ($comments as $value) {
			if ($value['user_type'] == "D") {
				$userout = db_user_getuserinfo($value['user_id'], "last_name");
			} elseif ($value['user_type'] == "O") {
				$userout = "RSS Only: ".db_user_getuserinfo($value['user_id'], "last_name");
			} elseif ($value['user_type'] == "V") {
				$userout = db_vendor_getinfo($value['user_id'], "name");
			}
			echo "<TR>";
			if (secure_is_admin()) {
				echo "<td class=text_12 bgcolor=#CCCC99 align=\"center\">";
				?>
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=delcomment&deleteid=<?php echo $value['id']; ?>&viewid=<?php echo $updateid; ?>&form=<?php echo $form; ?>" onClick="return confirm('Are you ABSOLUTELY sure you want to delete this comment forever?');">
				<?php
				echo "<IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></TD></a>";
			}
			echo"<td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><B>".timestamp2datetime($value['timestamp'])."</B></FONT></TD></TD><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><B>".$userout."</B></FONT><TD class=text_12 nowrap>".$value['comment']."</TD></TR>";
			unset($userout);
		}
	}?>
	<TR><TD colspan=3>New Comment: <input TYPE="text" NAME="comment" SIZE=50></TD>
	</TR>
	<TR><TD colspan=3>E-Mail Copy of Claim to: <input TYPE="text" NAME="sendemail" SIZE=50></TD>
	</TR>
	<TR>
	<TD colspan=6><?php if ($formprop["cansplit"] && (secure_is_admin()||secure_is_vendor())) {
		?><input type="checkbox" value="Y" id="splitclaim" name="splitclaim">Split Claim<?php
	} ?></td>
	</TR>
	</TABLE>
	</FONT></TD></TR>
	</TD></TR></TABLE>
	<input type="hidden" NAME="action" VALUE="update">	<input type="hidden" NAME="form" VALUE="<?php echo $form; ?>">	<input type=submit value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Submit Changes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
	</FORM>
<P></CENTER>
	</BODY></HTML>
	<?php
// ********** UPDATE DATA SELECTION
} elseif (($action == "update") && ($data)) {
	foreach($old as $key => $value)
		if ($value != $data[$key])
			$new[$key] = $data[$key];
	unset($new[id]);
	unset($new[clientip]);
	unset($new[timestamp]);
	unset($new[user_id]);
	$success = formupdate($form, $old['id'], $new);
	if ($new_comment) {
		formaddcomment($form, $old['id'], $new_comment);
		if (secure_is_vendor()&&$formprop['changeemail']) formemail($form,$old['id'],$formprop['changeemail']);
	}
	$newid = 0;
	if ($splitclaim)
		$newid = formsplit($form, $old['id']);
	if ($sendemail)
		formemail($form,$old['id'],$sendemail);
	?>

	<HTML><HEAD><TITLE>Updated Record <?php echo $old['id']; ?> in form <?php echo $form; ?></TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); form_javascript(); ?>
	</HEAD>
	<body bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require("menu.php"); ?>
	<center>

	<?php drawmenu($form);
	if (!$success) {		
	?>
	Updated the following fields in record <?php echo $old['id']; ?> in form <?php echo $form; ?><BR>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $old['id']; ?>">Return to Editing your Claim</a><br>
	<?php if ($newid) { ?>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $newid; ?>">Edit Split Claim</a><br>
	<?php } ?>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=display">Return to Claims Summary</a><br>

	<TABLE><TR BGCOLOR=#C0C0C0><TD BGCOLOR=#C0C0C0><B>FIELD</B></TD><TD BGCOLOR=#C0C0C0><B>NEW VALUE</B></TD></TR>
	<?php
	foreach($new as $key => $value) {
		echo "<TR><TD BGCOLOR=#C0C0C0>".$forminfo[$key]['nicename']."</TD><TD BGCOLOR=#C0C0C0>".$value."</TD></TR>";
	}
	if ($new_comment) {
		echo "<TR><TD BGCOLOR=#C0C0C0>New Comment</TD><TD BGCOLOR=#C0C0C0>".$new_comment."</TD></TR>";

	}
	if ($sendemail) {
		echo "<TR><TD COLSPAN=\"2\" BGCOLOR=#CCCC99>Copy of record sent to ".$sendemail ."</TD></TR>";
	}

	?> </TABLE><P> <?php
	} else {
		#echo "An error occurred processing this update.";
		#print_r($success);
		#echo "<P>";
?>
		<CENTER><TABLE><TR><TD>
		<?php if ($new_comment) { ?> Your comment has been added successfully.<br><?php } ?>
		<?php if ($sendmail) { ?> The claim has been e-mailed.<br><?php } ?>
		<?php if ($newid) { ?>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $newid; ?>">Edit Split Claim</a><br>
	<?php } ?>
		<font color="red">An error occurred while processing this update, below are the reasons.</font>
		<?php

		if (count($success['missing'])) { ?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are missing:
			<UL>
			<?php
			foreach($success['missing'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename'];
				echo "</LI>";
			}
			?> </FONT></UL></P> <?php
		}
		if (count($success['wrongtype'])) { 	?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are of the wrong type:
			<UL>
			<?php
			foreach($success['wrongtype'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename']." - Should be a ".$forminfo[$value]['datatype'];
				echo "</LI>";
			}
			?> </UL></FONT></P> <?php
		}  
		if (count($success['tolong'])) { 	?>
			<P><FONT FACE=ARIAL SIZE=3>The following fields are too long:
			<UL>
			<?php
			foreach($success['tolong'] as $key => $value) {
				echo "<LI>".$forminfo[$value]['nicename']." - Should be under ".$forminfo[$value]['limit']." charactors long.";
				echo "</LI>";
			}
			?> </UL></FONT></P> <?php
		}  
		?>
		</TD></TR></TABLE></CENTER></center>
		</BODY></HTML> <?php
	}
// ********** DELETE DATA SELECTION
} elseif (($action == "delete") && ($deleteid)) {
	if (formdelete($form, $deleteid)) {
	?>
	<HTML><HEAD><TITLE>Deleted Record <?php echo $old['id']; ?> in form <?php echo $form; ?></TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); form_javascript(); ?>
	</HEAD>
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require('menu.php'); ?>
	<?php drawmenu($form); ?><center>
	Record # <?php echo $deleteid; ?> deleted from <?php echo $form; ?>.</center>
	</BODY></HTML>
	<?php

	} else {
	?>
	<?php require('menu.php'); drawmenu($form); ?><center>
	No such record to delete from <?php echo $form; ?>.
	</center>
	<?php
	}
// ********** SPLIT DATA SELECTION
} elseif (($action == "split") && ($updateid)) {
	if ($newid = formsplit($form, $updateid)) {
	?>
	<HTML><HEAD><TITLE>Split Record <?php echo $updateid; ?> into Record <?php echo $newid; ?> in form <?php echo $form; ?></TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); form_javascript(); ?>
	</HEAD>
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require('menu.php'); ?>

	<?php drawmenu($form); ?><center>
	Split Record <?php echo $updateid; ?> into Record <?php echo $newid; ?> in form <?php echo $form; ?>.<br>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $updateid; ?>">Return to Editing your Record</a><br>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=update&updateid=<?php echo $newid; ?>">Edit Split Record</a><br>
	<a href="<?php echo $SERVER['PHP_SELF']; ?>?form=<?php echo $form; ?>&action=display">Return to Record Summary</a><br></center>
	</BODY></HTML>
	<?php

	} else {
	?>
	<?php drawmenu($form); ?>
	No such record to split in <?php echo $form; ?>.

	<?php
	}
// ********** COMMENT DELETE SECTION
} elseif (($action == "delcomment")&&($deleteid)&&($viewid)) {
	//echo ("Delete Comment - ".$deleteid." - ".$viewid);
	formdelcomment($form,$deleteid);
	header("Location: ".$_SERVER['PHP_SELF']."?form=".$form."&action=update&updateid=".$viewid);
	exit;
// ********** VIEW DISPLAY SELECTION
} elseif (($action == "view") && ($viewid)) {
	if ($data['comment']) {
		formaddcomment($form, $viewid, $data['comment'], 1);
		formupdate($form,$viewid,array("status" => "5"));
	}
	$comments = formcomments($form, $viewid);
	?>
	<HTML><HEAD><TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?> Viewer</TITLE>
		<link rel="stylesheet" href="/styles.css" type="text/css">
		<?php javascript(); form_javascript(); ?>
		</HEAD>
		<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
		<?php require('menu.php'); ?>


	<?php
	drawmenu($form,1);
?><CENTER> <CENTER><FONT FACE=ARIAL SIZE=3><B><?php echo $formprop["long_name"]; ?> Viewer</B></FONT></CENTER><BR>
	<CENTER><FONT FACE=ARIAL SIZE=3>
		<?php if (secure_is_admin()) { ?>
		<A href='<?php
			$qs = str_replace(array("action=view", "viewid="), array("action=update", "updateid="), $_SERVER['QUERY_STRING']);
			echo "form.php?" . $qs;
				?>'>Switch to form edit</A><BR>
		<?php } ?>
		Jump to Claim: <INPUT type='input' id='jump_to_claim_input' value='<?php echo $_GET['viewid']; ?>'> <SELECT name='jump_claim_view_mode'><OPTION value='view'>View</OPTION><OPTION value='update'>Edit</OPTION></SELECT> <INPUT type='button' value='Go' onclick='jump_to_claim("<?php echo $_SERVER['QUERY_STRING']; ?>");'>
	</FONT></CENTER>
<P>

<?php

	$value = formselect($form, $viewid);
	echo "<table border=1 cellspacing=2 cellpadding=2>";
	unset($upload);
	foreach ($value as $key => $x) {
		if ($key == "timestamp") {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			// YYYYMMDDHHMMSS
			$x = timestamp2datetime($x);
			echo "<td class=text_12><FONT FACE=ARIAL SIZE=-1>".$x."</TD>";
		} elseif ($key == "clientip") {
			if (secure_is_admin()) {
				echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
				echo "<td class=text_12><FONT FACE=ARIAL SIZE=-1>".$x."</TD>";
			}
		} elseif ($key == "status") {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12>".$claims_status[$x]."</TD>";
		} elseif ($key == "user_id") {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12>";
                        if ($formprop['sms'] && secure_is_admin()) echo "<A HREF=javascript:void(0); onclick=javascript:window.open('sms.php?updateid=".$value['id']."','smsWin','height=300,width=400,addressbar=0,status=1,toolbar=0,menubar=0,location=0');><IMG id='smsAdmin".$value['id']."' BORDER=0 ALT='T' SRC=/images/cellPhone.gif></A>";
                        echo db_user_getuserinfo($x, "last_name");
                        echo "</TD>";

		} elseif ($key == "vendor_id") {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12>".db_vendor_getinfo($x, "name")."</TD>";
		//goody added clickable po per gary 08-05-2004
		} elseif (($key == "po") || ($key == "PO#")) {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12><A HREF=/admin/report-orders-details.php?po=".$x." TARGET=_new><FONT FACE=ARIAL SIZE=-1 COLOR=#000000>".$x."</FONT></A></TD>\r\n";
		} elseif ($forminfo[$key]['datatype'] == "upload") {
			$upload = 1;
		} elseif ($forminfo[$key]['datatype'] == "checkbox") {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12>";
			if ($x == "on")
				echo "X";
			else
				echo "&nbsp;";
			echo "</td>";
		} elseif ($forminfo[$key]['multiline']) {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12><FONT FACE=ARIAL SIZE=-1>".wordwrap($x, 100, "<br />\n")."</TD>";
		} else {
			echo "<tr><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$key]['nicename']."</td>";
			echo "<td class=text_12><FONT FACE=ARIAL SIZE=-1>".$x."</TD>";
		}
		echo "</tr>";
	}
	//if stuff commented out making it ignore the db upload toggle field and just show files whether there are any or not per gary, not elegant but it works for now - goody 20040813
		echo "<tr><td class=text_12 COLSPAN=3><FONT FACE=ARIAL SIZE=-1>";
		show_upload_links($form, $value['id']);
		echo "</td></tr>";
	if ($comments) {
    ?><TR><TD COLSPAN="2">
	<TABLE>
	  <TR>
	    <TH colspan=4>
		  Comments
		</TH>
	  </TR>
	<?php

		foreach ($comments as $value) {
			if ($value['user_type'] == "D") {
				$userout = db_user_getuserinfo($value['user_id'], "last_name");
			} elseif ($value['user_type'] == "O") {
				$userout = "RSS Only: ".db_user_getuserinfo($value['user_id'], "last_name");
			} elseif ($value['user_type'] == "V") {
				$userout = db_vendor_getinfo($value['user_id'], "name");
			}
			echo "<TR><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><B>".timestamp2datetime($value['timestamp'])."</B></FONT></TD></TD><td class=text_12 bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1><B>".$userout."</B></FONT><TD class=text_12>".$value['comment']."</TD></TR>";
			unset($userout);
	}
	if (secure_is_dealer()) {
	?>
	   <TR><FORM METHOD=POST ACTION="form.php?action=view&viewid=<?php echo $viewid; ?>&form=<?php echo $form; ?>">
			<TD><font size="-1">RSS/Dealer Only Comment:</font></TD>
			<TD COLSPAN=3>
				<INPUT TYPE="text" NAME="data_comment"><INPUT TYPE="Submit" VALUE="Assign to RSS">
			</TD>
	   </FORM></TR>
	<?php
	} // End of Add comment
	?>
	</TABLE>
	</TD></TR><?php
	}
	echo "</table></center>";
// ********** DELETE FILE SELECTION
} elseif ($action == "delfile") {
	$result = formfiledel($form, $deleteid, $delfile);
	?>	<HTML><HEAD><TITLE>RSS Dealer Utilities - <?php echo $formprop["long_name"]; ?></TITLE>
		<link rel="stylesheet" href="/styles.css" type="text/css">
		<?php javascript(); form_javascript(); ?>
		</HEAD>
		<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
		<?php require('menu.php'); ?>
<center>
	<?php
	drawmenu($form);
	if ($result)
		echo "File Successfully Deleted";
	else
		echo "We were unable to delete file selected";
	echo "<BR><A HREF=\"".$SERVER['PHP_SELF']."?action=view&viewid=".$deleteid."&form=".$form."\">Return to Claim</a></center>";
// ********** MASS SELECTION
} elseif ($action == "massupdate") {
	$delete = array();
	foreach($data as $key => $value) {
		$reg = array();
		if(ereg("^delete_(.+)",$key, $reg))
			if ($value == 'on')
				$delete[] = $reg[1];
	}
	
	// Make sure blank updates are not missed.
	// REQUIRES 1 to 1 ratio of old's to new's!
	foreach($old as $key => $value) {
		$reg = array();
		if(ereg("^([0-9]+)_(.+)",$key, $reg)) {
			if (!$data[$key])
				$data[$key] = "";
		}
	}
	
	$update = array();
	foreach($data as $key => $value) {
		$reg = array();
		if(ereg("^([0-9]+)_(.+)",$key, $reg)) {
			if (!in_array($reg[1], $delete)) {
                                // Test for date value being cleared from 0000-00-00. This isn't actually an update.
                                if ($old[$key] != '0000-00-00' || $value != '') {
                                    if ($old[$key] != $value) {
                                            $update[$reg[1]][$reg[2]] = $value;
                                    }
                                }
                        }
		}
	}
	
	formmassupdate($form, $update);
	if (secure_is_admin()) {
		foreach ($delete as $value) {
			formdelete($form, $value);
		}
	}
	
		?>

	<HTML><HEAD><TITLE>Mass Update in form <?php echo $form; ?></TITLE>
	<link rel="stylesheet" href="/styles.css" type="text/css">
	<?php javascript(); ?>
	</HEAD>
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA" onload="<?php bodyonload(); ?>">
	<?php require('menu.php'); ?>


	<?php drawmenu($form); form_javascript(); ?><center>
	Mass update in form <?php echo $form; ?><BR>
	<?php echo "<A HREF=\"".$SERVER['PHP_SELF']."?form=".$form."&action=display&mumode=1&".urlfilter($filter)."\">Return to Display View</A>"; ?>

	<P> 
	<?php if (secure_is_admin()) {
		?>Deleted: <?php echo count($delete); ?> Claim(s)<BR>
		<?php
	} ?>
	Updated: <?php echo count($update); ?> Claim(s)<P></center>
	<?php
	/*
	echo "<PRE>\n";
	echo "Delete:\n";
	print_r($delete);
	echo "Update:\n";
	print_r($update);
	echo "Data:\n";
	print_r($data);
	echo "Old:\n";
	print_r($old);
	echo "</PRE><BR>";
	*/
} else {
	echo "Unknown Action<br>";
	drawmenu($form);
}

// ********** DISPLAY FUNCTIONS

function show_upload_links($form, $id) {
	$i = 0;
	if (file_exists($GLOBALS['claims_uploadstoragedir'].$form."/".$id)) {
		if ($handle = opendir($GLOBALS['claims_uploadstoragedir']."$form/$id")) {
			while (false  !== ($file = readdir($handle))) {
				if ($file  != "." && $file  != "..") {
					$i++;
					echo "<A HREF=\"/".$GLOBALS['claims_uploadstorageurl']."$form/$id/".rawurlencode($file)."\">".$file."</A>";
					if (secure_is_admin()) {
						echo " <A HREF=\"".$SERVER['PHP_SELF']."?action=delfile&form=".$form."&deleteid=". $id."&delfile=".rawurlencode($file)."\" ";
						?>onClick="return confirm('Are you sure you wish to delete <?php echo $file; ?>?');"><IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></a><?php
					}
					echo "<BR>";
					// str_replace("+", "%20", urlencode($file))
				}
			}
			closedir($handle);

		}
	}

	if ($i == 0) { echo "No additional files attached to this claim."; }
}

function bodyonload() {
	echo "MM_preloadImages(";
	$formlist = formlistforms();
	$i = 0;
	foreach($formlist as $form) {
		$i++;
		$forminfo = forminfo($form, 1);
		if ($i != 1) echo ",";
		echo "'/header/".$forminfo['icon_hover']."'";
	}
	echo ")";
}

function drawmenu($form, $textversion = 0) {
	$filter = urlfilter($GLOBALS['filter']);
	if ($filter)
		$filter = "&".$filter;
	//displays dynamic top pmd style menu appropriate to form requested by user
	$forminfo = forminfo($form,1);
	echo "<DIV ALIGN=CENTER>";

	$Form = ucwords($form);  //make an first letter capitalized version of the form name
	$FORM = strtoupper($form);  //make all uppercase version of the form name

	$formlist = formlistforms();
	echo "<BR>";
	echo "<font FACE=ARIAL><b>GO TO A DATABASE</b><br>";
	if ($textversion) {
		echo "| ";
		foreach($formlist as $eachform) {
			$Eachform = ucwords($eachform);  //make an first letter capitalized version of the form name
			$forminfotemp = forminfo($eachform,1);
			echo "<a href=".$SERVER['PHP_SELF']."?form=".$eachform.$filter."&action=display>".$forminfotemp['nicename']."</a> | ";
		}
		echo "<br>\n";
	} else {
	?><table width="500" border="0" align="center">
  <tr><?php
    $i = 0;
	foreach ($formlist as $eachform) {
		$i++;
		$forminfotemp = forminfo($eachform,1);
		?><td><div align="center"><a href="form.php?form=<?php echo $eachform; ?>&action=display" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image<?php echo $i; ?>','','/header/<?php echo $forminfotemp['icon_hover']; ?>?>',1)"><img src="/header/<?php echo $forminfotemp['icon']; ?>" name="Image1" width="100" height="75" border="3"></a></div></td><?php
	}
	?>  </tr>
  <tr> <?php
    $i = 0;
	foreach ($formlist as $eachform) {
		$i++;
		$forminfotemp = forminfo($eachform,1);
		?><td><div align="center"><font size="2" face="Arial, Helvetica, sans-serif"><a href="form.php?form=<?php echo $eachform; ?>&action=display"><?php echo $forminfotemp['nicename']; ?></a></font></div></td><?php
	}
  ?></tr>
  </table>
  <?php  }
	echo "<BR>";
	if ($form) {
		echo "<font FACE=ARIAL><b>$FORM MENU OPTIONS</b><br>";
		echo "| <a href=".$SERVER['PHP_SELF']."?form=".$form.$filter."&action=display>Display ".$forminfo["medium_name"]."</a> ";
		if (secure_is_admin() || (secure_is_dealer() && $forminfo['dealer_insertable']) || (secure_is_vendor() && $forminfo['vendor_insertable']))
			echo " | <a href=".$SERVER['PHP_SELF']."?form=".$form."&action=insert>".$forminfo["insert_name"]."</a> ";
		if (secure_is_admin()&&$form == 'order')
			echo " | <a href='/admin/csvoor.php'>CSV Update</a> ";
		echo "|<br>&nbsp;<BR></DIV>";
	}
}

function urlfilter($filter) {
	$filterstring = "";
	$i = 0;
	foreach ($filter as $k => $v) {
		$i++;
		if ($i != 1)
			$filterstring .= "&";
		$filterstring .= "f_".urlencode($k)."=".urlencode($v);
	}
	return $filterstring;
}

function javascript() {
	?>
	<script language="JavaScript" type="text/JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
	<?php
}
?>
