<?php
// inc_queue_filters.php
// functions to enable the shipping queue filters
// each function has two modes, display (1) and verify (0)
// display spits out the formatted data as it was before
// verify runs a specific set of filters, kicked off by queueverify.php
// defaults to display

function pickOrderType()
{
	global $show_type; // get the show_type
	?>Display <select name="show_type" id="type_select" onChange="changeType();" ><option value="open"<?php
	if($show_type=="open") echo ' selected="selected"';
?>>Open</option><option value="closed"<?php
	if($show_type=="closed") echo ' selected="selected"';
?>>Closed</option><option value="all"<?php if($show_type=="all") echo ' selected="selected"'; ?>>All</option></select> Orders from <?php
}


function pickOrderDates($mode = 1)
{
	if($mode)
	{
		global $from_date;
		global $thru_date;
		// show calendar JS app
		?><input class="date" type="text" id="from_date" name="from_date" size="10" value="<?php= $from_date != '' ? $from_date : date('m/d/Y') ?>"> to <input class="date" type="text" id="thru_date" name="thru_date" size="10" value="<?php= $thru_date != '' ? $thru_date : date('m/d/Y') ?>"><?php
	}
}


function pickGroupOrders($mode = 1)
{
	if($mode)
	{
		global $groupmulti;
		global $show_type;
		global $entry;
		if(secure_is_admin() || secure_is_vendor())
		{
			?>&nbsp;&nbsp;<input type="checkbox" name="groupmulti" id="groupview" value="1"<?php if($groupmulti==1 || !$entry) echo ' checked'; ?>>&nbsp;
<span id="grouptext"><?php if($show_type=="closed") { echo 'Group by Dealer'; } else { echo 'Group Multi-PO Capable Orders'; } ?></span><?php
		}
	}
}


function pickVendor($mode = 1)
{
	if($mode)
	{
		global $chosen_vendor;
		global $vendornames;
		global $vendorinfo;
		?><select name="vendor" onChange="chooseVendor(this.options[this.selectedIndex].value);" id="vendor_select">
<option value="all"<?php if(isset($chosen_vendor) && $chosen_vendor=="all" || $chosen_vendor=='') { echo " selected"; } ?>>All <?php if(secure_is_admin()) { echo 'Vendor'; } else { echo 'Form'; } ?>s</option>
<option value="n/a">&nbsp;</option>
<?php
// get the vendors they have access to
// $vendorid = id of the current user
// if the type is vendor, we'll go a different route
$vendorinfo = getVendorInfo();
$vendornames = getVendorNames();
for($i=0; $i<count($vendornames); $i++) {
	echo "<option value=\"{$vendornames[$i]}\"";
	if(isset($chosen_vendor) && $chosen_vendor!='' && $chosen_vendor==$vendornames[$i]) echo ' selected';
	echo ">{$vendornames[$i]}</option>\n";
}
?>
</select><?php
	}
	else
	{
		// verify filter set
		global $chosen_vendor;
		global $vendor_amt;
		global $vendornames;
		//global $vendorinfo;
		if(!$vendornames)
		{
			$vendornames = getVendorNames();
			$vendor_amt = count($vendornames);
		}
		for($i=0; $i<=count($vendornames); $i++)
		{
			if(isset($chosen_vendor) && $chosen_vendor != $vendornames[$i])
			{
				$chosen_vendor = array_shift($vendornames);
				break;
			}
		}
	}
}


function pickForm($mode = 1)
{
	if($mode)
	{
		global $form_entry;
		global $chosen_form;
		global $vendorinfo;
		?><tr id="select_form" style="display: none">
<td valign="top">Select Form:</td>
<td valign="top" colspan="2" align="left">
<input type="hidden" name="form_entry" value="<?php if($form_entry) echo $form_entry; ?>">
<?php
foreach($vendorinfo as $vendor_list)
{
	$form_uniques['vendor'][] = $vendor_list['vname'];
	$form_uniques['name'][] = $vendor_list['fname'];
	$form_uniques['id'][] = $vendor_list['ID'];
}
// create the JS arrays
// first we iterate through all the forms and split things by vendor
// define the form_vendors array first because we check the vendor for existence in the array at the start,
// and the array needs to be def'd for that to work
$form_vendors = Array();
for($i=0; $i<count($form_uniques['vendor']); $i++)
{
	if(!in_array($form_uniques['vendor'][$i], $form_vendors)) $form_vendors[] = $form_uniques['vendor'][$i];
	$form_names[$form_uniques['vendor'][$i]][] = stripslashes($form_uniques['name'][$i]);
	$form_ids[$form_uniques['vendor'][$i]][] = $form_uniques['id'][$i];
}
// so form_vendors = array of the vendors
// form_names[form_vendor] = array of form names within that vendor
// form_ids[form_vendor] = array of form IDs within that vendor
// now push to a set of arrays, one for each vendor 
fire

?><script type="text/javascript">
<?php
// call the js_array function, one for each vendor...array name will be vendor code + suffix for easy referencing
echo js_array("form_names",$form_names)."\n";
echo js_array("form_ids", $form_ids)."\n";
?></script>
<?php
$form_chosen_tag = '<input type="hidden" name="chosen_form" value="';
$done = false;
if($chosen_form!='') $form_chosen_tag .= $chosen_form;
echo $form_chosen_tag.'"><select name="choose_form" id="choose_form" onChange="chooseForm(this.options[this.selectedIndex].value);"><option value="all">All Forms</option>';
echo '</select>';
?>
</td>
</tr>
		<?php
	}
	else
	{
		// verify mode
		global $chosen_form;
		global $chosen_vendor;
		$getformsquery = buildVendorQuery($chosen_vendor);
		$formsdb = mysql_query($getformsquery);
		while($formlist = mysql_fetch_assoc($formsdb))
		{
			$sql = "SELECT name FROM forms WHERE ID = ".$formlist['ID'];
			$que = mysql_query($sql);
			checkdberror($sql);
			$ret = mysql_fetch_assoc($que);
			$forms['name'][] = $ret['name'];
			$forms['ID'][] = $formlist['ID'];
		}
		return Array('data' => $forms, 'count' => count($forms['name']));
	}
}

function pickDealer($mode = 1)
{
	if($mode)
	{
		global $dealer_entry;
		global $chosen_dealer;
		?><tr>
<td>
<input type="hidden" id="dealer_entry" name="dealer_entry" value="<?php if($dealer_entry) echo $dealer_entry; ?>">Select Dealer:</td>
<td align="left"><input type="hidden" id="chosen_dealer" name="chosen_dealer" value="<?php if($chosen_dealer) echo $chosen_dealer; ?>">
<select name="dealer" onChange="chooseDealer(this.options[this.selectedIndex].value);" id="dealer_select">
<option value="all"<?php if(isset($_POST['dealer']) && ($_POST['dealer']=="all" || $_POST['dealer']=="")) echo " selected"; ?>>All Dealers</option>
<option value="">&nbsp;</option>
<?php
// get the dealers they have access to
// $vendorid = id of the current user
// if the type is vendor, we'll go a different route
$dealers = getDealerInfo();
$dealer_selected = '';
foreach ($dealers as $dealer_record)
{
	?>
	<option value="<?php echo $dealer_record['dealername'].'"';
	if($chosen_dealer)
	{
		if($chosen_dealer == $dealer_record['dealername']) echo " selected";
	}
	?>><?php if(isset($dealer_record['dealername'])) echo $dealer_record['dealername']; ?></option>
	<?php
}
?>
</select>
<br /></td>
</tr><?php
	}
	else
	{
		global $chosen_dealer;
		global $chosen_dealer_name;
		global $dealer_amt;
		global $dealerinfo;
		if(!$dealerinfo)
		{
			$dealerinfo = getDealerInfo();
			$dealer_amt = count($dealerinfo);
		}
		//echo "Dealerinfo:<br /><br />\n\n";
		//var_dump($dealerinfo);
		for($i = 0; $i <= count($dealerinfo); $i++)
		{
			if($chosen_dealer != $dealerinfo[$i]['deal_id'] && $dealerinfo[$i]['dealername'] != '')
			{
				$chosen_dealer = $dealerinfo[$i]['deal_id'];
				$chosen_dealer_name = $dealerinfo[$i]['dealername'];
				array_shift($dealerinfo);
				break;
			}
		}
	}
}


function pickSearchBy($mode = 1)
{
	if($mode)
	{
		global $searchfor;
		global $searchopt;
		?><td align="left">Search by 
		<select name="searchopt" id="searchopt">
		<option value="order_forms.ID"<?php
		if($searchopt == "order_forms.ID") echo ' selected'; ?>>PO</option>
		<option value="BoL_forms.ID"<?php if($searchopt == "BoL_forms.ID") echo ' selected'; ?>>BOL</option>
		</select><br />
		<input type="text" name="searchfor" id="searchfor" value="<?php echo $searchfor ? $searchfor : "[Enter desired value]"; ?>"></td>
		<?php
	}
}

function getQueueMinMax()
{
	$sql = "SELECT MIN(po) AS least, MAX(po) AS most FROM BoL_queue";
	$que = mysql_query($sql);
	checkdberror($sql);
	$res = mysql_fetch_assoc($que);
	return $res;
}

function getBolMinMax()
{
	$sql = "SELECT MIN(ID) AS least, MAX(ID) AS most FROM BoL_forms";
	$que = mysql_query($sql);
	checkdberror($sql);
	$res = mysql_fetch_assoc($que);
	return $res;
}
?>