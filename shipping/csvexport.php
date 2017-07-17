<?php
// w00t!

require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');

if ($_POST['id_type']) {
	require_once("inc_csvexport.php");
	if ($_POST['id_type'] == 'po') {
		salesord_queue($_POST['id']);
	} elseif ($_POST['id_type'] == 'bol') {
		soi_queue($_POST['id']);
	} else {
		die("Unable to determine type");
	}
	die("SUCC");
}

// apply POST'd vars, first getting the values from the cookie
if($_COOKIE['csvq_shipping_show_type']) {
	$entry = true;
	$vendor_entry = $_COOKIE['csvq_vendor_entry'];
	$form_entry = $_COOKIE['csvq_form_entry'];
	$dealer_entry = $_COOKIE['csvq_dealer_entry'];
	$show_type = $_COOKIE['csvq_shipping_show_type'];
	$from_month = $_COOKIE['csvq_shipping_fr_mo'];
	$from_day = $_COOKIE['csvq_shipping_fr_day'];
	$from_year = $_COOKIE['csvq_shipping_fr_yr'];
	$thru_month = $_COOKIE['csvq_shipping_th_mo'];
	$thru_day = $_COOKIE['csvq_shipping_th_day'];
	$thru_year = $_COOKIE['csvq_shipping_th_yr'];
	$vendor = $_COOKIE['csvq_shipping_vendor'];
	$dealer = $_COOKIE['csvq_shipping_dealer'];
	$chosen_vendor = $_COOKIE['csvq_chosen_vendor'];
	$chosen_form = $_COOKIE['csvq_chosen_form'];
	$chosen_dealer = $_COOKIE['csvq_chosen_dealer'];
	$searchopt = $_COOKIE['csvq_searchopt'];
	$searchfor = $_COOKIE['csvq_searchfor'];
} else {
	$entry = false;
	$show_type = 'po';
	$from_month = date('n');
	if(strlen($from_month)==1) $from_month = "0".$from_month;
	$from_year = date('Y');
	$from_day = date('j');
	if(strlen($from_day)==1) $from_day = "0".$from_day;
	$thru_month = date('n');
	if(strlen($thru_month)==1) $thru_month = "0".$thru_month;
	$thru_day = date('j');
	if(strlen($thru_day)==1) $thru_day = "0".$thru_day;
	$thru_year = date('Y');
}
if($_POST['show_type']) {
	$entry = true;
	$vendor_entry = $_POST['vendor_entry'];
	setcookie('csvq_vendor_entry', $vendor_entry, 0);  
	$form_entry = $_POST['form_entry'];
	setcookie('csvq_form_entry', $form_entry, 0);
	$dealer_entry = $_POST['dealer_entry'];
	setcookie('csvq_dealer_entry', $dealer_entry, 0);
	$show_type = $_POST['show_type'];
	setcookie('csvq_shipping_show_type', $show_type, 0);
	$from_month = $_POST['from_month'];
	if(strlen($from_month)==1) $from_month = "0".$from_month;
	setcookie('csvq_shipping_fr_mo', $from_month, 0);
	$from_day = $_POST['from_day'];
	if(strlen($from_day)==1) $from_day = "0".$from_day;
	setcookie('csvq_shipping_fr_day', $from_day, 0);
	$from_year = $_POST['from_year'];
	setcookie('csvq_shipping_fr_yr', $from_year, 0);
	$thru_month = $_POST['thru_month'];
	if(strlen($thru_month)==1) $thru_month = "0".$thru_month;
	setcookie('csvq_shipping_th_mo', $thru_month, 0);
	$thru_day = $_POST['thru_day'];
	if(strlen($thru_day)==1) $thru_day = "0".$thru_day;
	setcookie('csvq_shipping_th_day', $thru_day, 0);
	$thru_year = $_POST['thru_year'];
	setcookie('csvq_shipping_th_yr', $thru_year, 0);
	$vendor = $_POST['vendor'];
	setcookie('csvq_shipping_vendor', $vendor, 0);
	$chosen_vendor = $_POST['chosen_vendor'];
	setcookie('csvq_chosen_vendor', $chosen_vendor, 0);
	$chosen_form = $_POST['chosen_form'];
	setcookie('csvq_chosen_form', $chosen_form, 0);
	$chosen_dealer = $_POST['chosen_dealer'];
	setcookie('csvq_chosen_dealer', $chosen_dealer, 0);
	$searchopt = $_POST['searchopt'];
	setcookie('csvq_searchopt', $searchopt, 0);
	$searchfor = $_POST['searchfor'] != '[Enter desired value]' ? $_POST['searchfor'] : "";
	setcookie('csvq_searchfor', $searchfor, 0);
	$dealer = $_POST['dealer'];
	setcookie('csvq_shipping_dealer', $dealer, 0);
}
// build months array
$months = array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
// if we have a message to display, pull it from the cookie collection to a string and reset
if($_COOKIE['BoL_msg']) {
	$msg = $_COOKIE['BoL_msg'];
	setcookie('BoL_msg', '', time()-2);
} else {
	$msg = "";
}
// Header output
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Shipping CSV Export Queue</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
	<link type="text/css" href="css/shipping.css" rel="stylesheet">
	<script src="bol.js" language="javascript" type="text/javascript"></script>
	<script src="shipping.js" language="javascript" type="text/javascript"></script>
	<script src="../include/common.js" language="javascript" type="text/javascript"></script>
	<script type="text/javascript">
		function queue_mas90(tag, id) {
			var post_arr = Array();
			post_arr.id = id;
			post_arr.id_type = '<?php echo $show_type; ?>';
			var action = window.location;
			gFormPostProcess = finish_queue_mas90;
			gFormAlertResult = finish_alert_queue_mas90;
			postArr( tag, action, post_arr);
		}
		var gPostProcessTag = false;
		function finish_queue_mas90(tag) {
			gPostProcessTag = tag;
		}
		
		function finish_alert_queue_mas90(text) {
			if (text == 'SUCC') {
				// Successful
				gPostProcessTag.parentNode.innerHTML = getToday();
				//rmrow(gPostProcessTag);
			} else {
				alert(text);
			}
			gPostProcessTag = false;
		}
	</script>
</head>
<body>
<?php include_once("../menu.php"); ?>

<?php
// This is where non-superadmins get off =)
if (!secure_is_superadmin()) die("Access Denied");
?>
<p class="title">Shipping CSV Export Queue<br /><?php
// if the filter's been applied, show the right header
if($entry) {
	switch($show_type)
	{
		case "po":
			echo "POs";
			break;
		case "bol":
			echo "BoLs";
			break;
		default:
			echo "ERR";
			break;
	}
	echo " from $from_month/$from_day/$from_year through $thru_month/$thru_day/$thru_year</p>\n";
}
if($msg!="") { // display the message if necessary
	echo '<p class="alert">'.$msg."</p>\n";
}
?>
<form name="settings_form" method="post" action="csvexport.php">
	<table align="center" border="0" cellspacing="3" cellpadding="3">
		<tr>
			<td colspan="3">
				Display 
				<select name="show_type" id="type_select" onChange="this.form.submit();" >
					<option value="po"<?php
						if($show_type=="po") echo ' selected="selected"';
						?>>PO</option><option value="bol"<?php
						if($show_type=="bol") echo ' selected="selected"';
						?>>BoL</option>
				</select>
				Orders from 
				<select name="from_month">
<?php
// create the date range dropdowns
for($i=1; $i<=12; $i++) {
	echo "\t\t\t\t\t".'<option value="'.$i.'"';
	if($from_month==$i) echo ' selected="selected"';
	echo '>'.$months[$i]."</option>\n";
}
?>
				</select>
				<input type="text" name="from_day" size="3" value="<?php echo $from_day; ?>" />
				<select name="from_year">
<?php
for($i=2003; $i<=date('Y'); $i++) {
	echo "\t\t\t\t\t".'<option';
	if($from_year==$i) echo ' selected="selected"';
	echo ">$i</option>\n";
}
?>
				</select>
				to
				<select name="thru_month">
<?php
for($i=1; $i<=12; $i++) {
	echo "\t\t\t\t\t".'<option value="'.$i.'"';
	if($i==$thru_month) echo ' selected="selected"';
	echo '>'.$months[$i].'</option>'."\n";
}
?>
				</select>
				<input type="text" name="thru_day" size="3" value="<?php echo $thru_day; ?>" />
				<select name="thru_year">
<?php
for($i=2003; $i<=date('Y'); $i++) {
	echo "\t\t\t\t\t".'<option value="'.$i.'"';
	if($i==$thru_year) echo ' selected="selected"';
	echo '>'.$i.'</option>'."\n";
}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="white-space:nowrap;">
				<input type="hidden" name="vendor_entry" value="<?php if($vendor_entry) { echo 'true'; } else { echo 'false'; } ?>">
				Vendor:
				<input type="hidden" name="chosen_vendor" value="<?php if($chosen_vendor) echo $chosen_vendor; ?>">
				<select name="vendor" onChange="chooseVendor(this.options[this.selectedIndex].value);">
					<option value="all"<?php if($chosen_vendor=="all" || $chosen_vendor=='') { echo " selected"; } ?>>All Vendors</option>
					<?php
						// get the vendors they have access to
						// $vendorid = id of the current user
						// if the type is vendor, we'll go a different route
						$vendorinfo = getVendorInfo();
						$vendornames = getVendorNameIds();
						echo "\n";
						foreach ($vendornames as $id => $name) {
							// for($i=0; $i<count($vendornames); $i++) {
							echo "\t\t\t\t\t";
							?><option value="<?php echo $id; ?>"<?php if ($chosen_vendor == $id) echo " selected"; ?>><?php echo $name; ?></option><?php
							echo "\n";
						}
					?>
				</select>
			</td>
			<td  style="white-space:nowrap;">
				<input type="hidden" name="form_entry" value="<?php if($form_entry) echo $form_entry; ?>">
				<input type="hidden" name="chosen_form" value="<?php if($chosen_form) echo $chosen_form; ?>">
				<?php
				if (is_numeric($chosen_vendor)&&($chosen_vendor >= 1)) {
					?>
					Form: 
					<select name="choose_form" onChange="chooseForm(this.options[this.selectedIndex].value);">
						<option value="all">All Forms</option>
						<?php
							foreach (db_forms_getlist($chosen_vendor) as $form) {
								echo "\n\t\t\t\t\t\t";
								?><option value="<?php=$form['ID'] ?>"<?php if ($chosen_form == $form['ID']) echo " selected"; ?>><?php=$form['name'] ?></option><?php
							}
						?>
					</select>
					<?php
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<input type="hidden" name="dealer_entry" value="<?php if($dealer_entry) echo $dealer_entry; ?>">
				Select Dealer:
				<input type="hidden" name="chosen_dealer" value="<?php if($chosen_dealer) echo $chosen_dealer; ?>">
				<select name="dealer" onChange="chooseDealer(this.options[this.selectedIndex].value);">
					<option value="all"<?php if($_POST['dealer']=="all" || $_POST['dealer']=="") { echo " selected"; } ?>>All Dealers</option>
					<option value="">&nbsp;</option>
<?php
// get the dealers they have access to
// $vendorid = id of the current user
// if the type is vendor, we'll go a different route
$dealers = db_user_filterlist();
$dealer_selected = '';
foreach ($dealers as $dealer_record) {
	echo "\t\t\t\t\t".'<option value="'.$dealer_record['ID'].'"';
	if($chosen_dealer) {
		if($chosen_dealer == $dealer_record['ID']) echo " selected";
	}
	echo '>'.$dealer_record['last_name']."</option>\n";
}
?>
				</select>
				<br />
			</td>
			<td align="right" style="white-space:nowrap;">
				Search by 
				<select name="searchopt">
					<option value="po"<?php if($searchopt == "po") echo ' selected'; ?>>PO</option>
					<option value="bol"<?php if($searchopt == "bol") echo ' selected'; ?>>BOL</option>
				</select>
				<input type="text" name="searchfor" onfocus="if (this.value == '[Enter desired value]') this.value = '';" onblur="if (this.value == '') this.value='[Enter desired value]';" value="<?php echo $searchfor ? $searchfor : "[Enter desired value]"; ?>">
				<input type="submit" value="Apply Filter">
			</td>
		</tr>
		<tr>
			<td align="center"><a href="csvfinalize.php?type=pending&mode=<?php if ($show_type == 'po') echo 'salesord'; else echo 'soi'; ?>">Preview Queued CSV</a></td>
			<td align="center"><a href="csvfinalize.php?mode=<?php if ($show_type == 'po') echo 'salesord'; else echo 'soi'; ?>">Download Final CSV</a></td>
		</tr>
	</table>
</form>
<?php
if($entry) {
// Start to build the query, first being the forms we can work with based on vendor filter choice
// If there's a specific vendor chosen, create an SQL query filter for it
// otherwise, let everything through by leaving the query the way it is
$arg = array();
$arg['show_type'] = $show_type;
$arg['chosen_dealer'] = $chosen_dealer;
$arg['chosen_form'] = $chosen_form;
$arg['chosen_vendor'] = $chosen_vendor;
$arg['from_year'] = $from_year;
$arg['from_month'] = $from_month;
$arg['from_day'] = $from_day;
$arg['thru_year'] = $thru_year;
$arg['thru_month'] = $thru_month;
$arg['thru_day'] = $thru_day;
$arg['searchopt'] = $searchopt;
$arg['searchfor'] = $searchfor;
$queue = getCSVQueue($arg);
unset($arg);


if (count($queue)) {
	?>
	<table align="center" class="queue" rules="none" cellspacing="2" cellpadding="10">
		<tr class="queueheader">
			<th scope="col"><?php if ($show_type == 'po') { ?>PO #<?php } elseif ($show_type == 'bol') { ?>BoL #<?php } else { ?>ERR<?php } ?></th>
			<th scope="col"><?php if ($show_type == 'po') { ?>MAS90 S/O<?php } elseif ($show_type == 'bol') { ?>MAS90 SOI<?php } else { ?>ERR<?php } ?></th>
		</tr>
		<?php
		foreach ($queue as $id => $date) {
			?>
			<tr>
				<td align="center">
					<?php
						if ($show_type == 'po') { ?><a href="/admin/viewpo.php?po=<?php= $id ?>" target="_blank"><?php= $id ?></a><?php }
						elseif ($show_type == 'bol') { ?><a href="/shipping/viewbol.php?id=<?php= $id - 1000 ?>" target="_blank"><?php= $id ?></a><?php }
						else echo $id; 
					?>
				</td>
				<td align="center">
					<?php if ($date) {
						echo date("m/d/Y",$date);
					} else { ?>
					<a onClick="queue_mas90(this,<?php echo $id; ?>)"><img src="/images/export_icon.gif" border="0"></a>
					<?php } ?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
<?php } else { ?>
<table align="center" border="0" cellspacing="3" cellpadding="3">
<tr><td><p class="text_16">There are no orders meeting your criteria.</p></td></tr></table>
<?php }
}
?>
</body>
</html>