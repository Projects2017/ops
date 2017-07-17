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
include("../database.php");
include("../vendorsecure.php");
#if (!$vendorid)
#	include("../secure.php");

function secure_is_admin() { return true; }
function secure_is_dealer() { return true; }
function secure_is_vendor() { return false; }


// Include form functions
include("../form.inc.php");
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
//   =============== OUTPUT SECTION ====================



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

	if ($bigarray) {
		if (count($bigarray) >= 2000) $strBody .= "<center class=\"fat_black_12\">More than 2000 results, first 2000 results shown.</center>";
		$strBody .= "<table border=1 cellspacing=2 cellpadding=2>\n";
		//$strBody .= "<TR>
		$strBody .= "\t<TR>";



		foreach($bigarray[0] as $value => $junk) {
			if (!$forminfo[$value]['on_summary']) {
				// Display Nothing!
			} elseif ($value == "clientip") {
				if (secure_is_admin())
					$strBody .= "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".ucwords($value)."</FONT></TH>";
      } elseif ($value == "cr_link") {
        if (secure_is_admin() || secure_is_vendor()) {
					$strBody .= "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".$forminfo[$value]['nicename']."</FONT></TH>"; 
        }
			} else {
				if ($order == "!".$value)
					$strBody .= "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".
				$forminfo[$value]['nicename']."</FONT></TH>";
				elseif ($order == $value)
				    $strBody .= "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".
				$forminfo[$value]['nicename']."</FONT></TH>";
				else
					$strBody .= "<TH bgcolor=#CCCC99><FONT FACE=ARIAL SIZE=-1>".
				$forminfo[$value]['nicename']."</FONT></TH>";
			}
		}


		foreach($bigarray as $value) {
			//if ($f_vendor && ($f_vendor != $value['vendor_id']))
			//	continue;
			$bgcolor = "#FFFFFF";

				$strBody .= "\t<TR id='claim".$value['id']."' bgcolor='$bgcolor'>";


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
			
			# update to indicate email sent
			$sql = "UPDATE claim_".$_REQUEST['form']." SET last_claim_email_sent = '".date("Y-m-d 09:00:00")."' WHERE id = ".$value['id'];
			mysql_query($sql);
          if($key == "cr_link" && (!secure_is_admin() && !secure_is_vendor())) continue;
					$xdispbefore = "";
					$xdispafter = "";

					if (!$forminfo[$key]['on_summary']) {
						// Display Nothing!
					} elseif ($key == "timestamp") {
						// YYYYMMDDHHMMSS
						$x = timestamp2date($x);
						$strBody .= "<td class=text_12>".$xdispbefore.$x.$xdispafter."</TD>";
					} elseif ($key == "clientip") {
						if (secure_is_admin())
							$strBody .= "<td class=text_12>".$x."</TD>";
					} elseif (($forminfo[$key]['datatype'] == "checkbox")&&($mumode)&&($forminfo[$key]['massedit'])&&($forminfo[$key]['edit'])) {
					 	?>
					 	<td class=text_12><center><input TYPE="checkbox" NAME="data_<?php echo $value['id']; ?>_<?php echo $key ?>" <?php if ($x == "on") echo "CHECKED";?>></center>
					 	<input TYPE="hidden" NAME="old_<?php echo $value['id']; ?>_<?php echo $key ?>" VALUE="<?php echo $x; ?>"></td>
					 	<?php
					} elseif ($forminfo[$key]['datatype'] == "checkbox") {
						$strBody .= "<td class=text_12><center>";
						if ($x == "on")
							$strBody .= "X";
						else
							$strBody .= "&nbsp;";
						$strBody .= "</center></td>";
					} elseif ($key == "user_id") {
						$strBody .= "<td class=text_12>".$xdispbefore.db_user_getuserinfo($x, "last_name").$xdispafter."</TD>";
					} elseif ($key == "vendor_id") {
						$strBody .= "<td class=text_12>".$xdispbefore.db_vendor_getinfo($x, "name").$xdispafter."</TD>";
					} elseif ($key == "status") {
						$strBody .= "<td class=text_12>".$xdispbefore.$claims_status[$x].$xdispafter."</TD>";
	//goody added clickable po per gary 08-05-2004
					} elseif (($forminfo[$key]['datatype'] == "upload") && ($x == "1")) {
						$strBody .= "<td class=text_12>";
						show_upload_links($form, $value['id']);
						$strBody .= "</td>";
					} elseif (($forminfo[$key]['datatype'] == "upload") && ($x == "0")) {
						$strBody .= "<TD CLASS=text_12>None</TD>";
					} else {
						$strBody .= "<td class=text_12>";
						// insert shipping info if applicable
/*						if($key == "carrier")
						{
							if($shipping['carrier'])
							{
								$colors = array('yellow','silver');
								$thiscolor = 0;
								foreach($shipping['carrier'] as $car)
								{
									$strBody .= '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									$thiscolor = $thiscolor==0 ? 1 : 0;
									$strBody .= $car."</span><br />\n";
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
									//$strBody .= '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									//$thiscolor = $thiscolor==0 ? 1 : 0;
									$strBody .= $car;
									//$strBody .= "</span>";
									$strBody .= "<br />\n";
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
									$strBody .= '<span style="background-color: '.($thiscolor==0?$colors[0]:$colors[1]).'">';
									$thiscolor = $thiscolor==0 ? 1 : 0;
									$strBody .= $car."</span><br />\n";
								}
							}
						}
*/						
						if (($mumode)&&($forminfo[$key]['massedit'])&&($forminfo[$key]['edit'])) {
							?>
							<input TYPE="text" NAME="data_<?php echo $value['id']; ?>_<?php echo $key ?>"<?php if ($forminfo[$key]['limit'] != -1) { ?> MAXLENGTH="<?php echo $forminfo[$key]['limit']; ?>"<?php } ?> VALUE="<?php echo $x; ?>">
							<input TYPE="hidden" NAME="old_<?php echo $value['id']; ?>_<?php echo $key ?>" VALUE="<?php echo $x; ?>"><?php
						} else {
							$strBody .= $xdispbefore.$x.$xdispafter;
						}
						$strBody .= "</TD>";
					}
					$xdisp = "";
			}
			$strBody .= "\t</TR>\n";
		}
		$strBody .= "</TABLE>\n";
		if ($mumode) {
			?><input type=submit value="Mass Update">
			<?php
		}


	} else {
	$strBody .= '<table border=0 cellspacing=0 cellpadding=5><TR><TH bgcolor=#CCCC99>No records on File beyond '.$_REQUEST['daysback'].' days</TH></TR></TABLE>';
	} 
	if ($mumode)  {
		$strBody .= "</FORM>\n";
	}
}

$strBody = '<h2>Open Claims ('.date('m/Y').') for '.ucfirst($_REQUEST['form']) .'</h2><br>Please see the claim report below. Thank you.<br><br>' . $strBody;

$to = 'claims@retailservicesystems.com';

$subject = 'Monthly Claims ('.date('m/Y').') for '.ucfirst($_REQUEST['form']);

$headers = "From: Retail Admin <noreply@retailservicesystems.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

// echo $strBody;

sendmail($to,$subject, $strBody, $headers);

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
	if ($form) {


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



?>
