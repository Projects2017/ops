<?php
require("MoS_database.php");
require("MoS_admin_secure.php");

if ($_GET['sync'] != '') {
	include_once("../include/json.php");
	$ch = curl_init($MoS_MasterPath."MoS_sync_form.php?form=" . $_GET['sync']);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$entries = curl_exec($ch);
	curl_close($ch);
	$entries = explode("\n",$entries);
	if ($entries[0] == "SUCCESS") {
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$entries = $json->decode($entries[1]);
		// echo "<pre>"; print_r($entries); echo "</pre>";
		save_to_db($entries);
		$sync_message = "Form '".$entries['forms'][0]['name']."' Synchronized";
		//echo "Form Synchronized";
	} else {
		$sync_message = "Error while syncing form.";
		//echo "Error while syncing form";
		//echo "<pre>".print_r($entries[0])."<pre>";
	}
	
	//echo $entries;
	//$entries = explode("##########", $entries);
	//foreach($entries as $entry) {
	//	mysql_query($entry) or die ($entry . "<BR><BR>" . mysql_error());
	//}
} elseif ($_POST['orderable'] != '') {
	if ($_POST['orderable'] == 'Y') {
		$sql = "UPDATE `MoS_form_access` SET `enabled` = 'Y' WHERE `form_id` = '".$_POST['form_id']."'";
		mysql_query($sql);
		checkDBerror($sql);
		if (mysql_affected_rows())
			die("Yes");
		else
			die("ERROR");
	} else {
		$sql = "UPDATE `MoS_form_access` SET `enabled` = 'N' WHERE `form_id` = '".$_POST['form_id']."'";
		mysql_query($sql);
		checkDBerror($sql);
		if (mysql_affected_rows())
			die("No");
		else
			die("ERROR");
	}
}

if ($_GET['showhidden'] == 't') {
	setPref('fv_hidehidden',true);
	$fv_hidden = true;
} elseif ($_GET['showhidden'] == 'f') {
	setPref('fv_hidehidden',false);
	$fv_hidden = false;
} else {
	$fv_hidden = getPref('fv_hidehidden');
}

require("MoS_menu.php");


?>
<script type="text/javascript">
function switch_orderable(tag, id) {
	var post_arr = Array();
	post_arr.form_id = id;
	var way = 'ERROR';
	if (getInnerTextNode(tag).nodeValue == 'Yes') {
		way = 'N';
	} else if (getInnerTextNode(tag).nodeValue == 'No') {
		way = 'Y';
	}
	if (way == 'ERROR') {
		return false;
	}
	post_arr.orderable = way;
	var action = window.location;
	gFormPostProcess = finish_switch_orderable;
	gFormAlertResult = finish_alert_orderable;
	postArr( tag, action, post_arr);
}
var gPostProcessTag = false;
function finish_switch_orderable(tag) {
	gPostProcessTag = tag;
}

function finish_alert_orderable(text) {
	getInnerTextNode(gPostProcessTag).nodeValue = text;
	gPostProcessTag = false;
}
</script>
<h3>Note: Changes made affect MoS only</h3>
<?php
if ($sync_message) { ?><h3 class="fat_red"><?php echo $sync_message; ?></h3><?php }
if (!$fv_hidden) {
	?><a href="<?php= $_SERVER['PHP_SELF']."?showhidden=t" ?>">Show Disabled Forms</a><?php
} else {
	?><a href="<?php= $_SERVER['PHP_SELF']."?showhidden=f" ?>">Hide Disabled Forms</a><?php
}
?>
<table border="0" cellspacing="0" cellpadding="5" align="left">
  <tr> 
  	<td class="fat_black_12" bgcolor="#fcfcfc"><b>Orderable</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Name</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Forms</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Discount</b></td>
	<td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Revert?</b></td>
	<td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Sync from RSS?</b></td>
  </tr>
  <?php
	$sql = "SELECT * FROM MoS_director";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$director = array();
	while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$director[$line['form_id']] = $line['MoS_form_id'];
	}

	$sql = "SELECT vendors.name as vname, forms.name as fname, forms.ID as fid, MoS_form_access.enabled as access_enabled FROM vendors, forms, MoS_form_access WHERE vendors.ID = forms.vendor AND forms.ID = MoS_form_access.form_id ORDER BY vendors.name";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$resulta = array();
	while ($results = mysql_fetch_Array($query, MYSQL_ASSOC))
	{
		$resulta[$results['fid']] = $results;
                $resulta[$results['fid']]['discount'] = loadDiscount('discount', array('form_id'=>$results['fid']), 'form');
	}
	if (count($resulta)) {
		$sql = "SELECT MoS_forms.ID, MoS_forms.name as fname, vendors.name as vname FROM MoS_forms, vendors WHERE vendors.ID = MoS_forms.vendor AND MoS_forms.ID in (" . implode(', ', array_keys($resulta)) . ")";
		$query = mysql_query($sql);
		checkDBerror($sql);
		while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$resulta[$line['ID']]['fname'] = $line['fname'];
			$resulta[$line['ID']]['vname'] = $line['vname'];
			$resulta[$line['ID']]['discount'] = loadDiscount('discount', array('form_id'=>$line['ID']), 'MoS_form');
		}
	}
	//-- Resort in case a vendor changed
	function cmp($a, $b) {
	   return strcmp($a["vname"].$a['fname'], $b["vname"].$b['fname']);
	}
	usort($resulta, "cmp");

	foreach($resulta as $result) {
		if (!$fv_hidden) {
			if ($result['access_enabled'] != 'Y') continue;
		}
	?>
  <tr>
	<?php
		if ($result['vname'] != $prev_vendor) {
			$style = "border-top: 1px solid #BBBBBB;";
		} else {
			$style = "";
		}
	?>
	<td valign="top" align="left" class="text_12" style='<?php echo $style;?>'nowrap>
		<A href='#' onclick="switch_orderable(this, <?php echo $result['fid'];?>); return false;"><?php if ($result['access_enabled'] == 'Y') { ?>Yes<?php } else { ?>No<?php } ?></A><BR>
	</td>
	<?php
		if ($result['vname'] != $prev_vendor) {
	?>
	<td valign="top" class="text_12" style='border-top: 1px solid #BBBBBB;'><?php echo $result['vname']; ?></td>
	<?php
		} else {
			echo "<TD></TD>";
		}
		$prev_vendor = $result['vname'];
	?>
    <td valign="top" class="text_12" style='<?php echo $style;?>'nowrap><A href='MoS_edit-forms-edit.php?ID=<?php echo $result['fid']; ?>'><?php echo $result['fname']; ?></A></td>
    <td valign="top" align="right" class="text_12" style='<?php echo $style;?>'nowrap>
    	<?php
    		if (!is_null($result['discount'])) {
    			echo $result['discount'];
    		}
    	?>&nbsp;
    </td>
	<td valign="top" class="text_12" style='<?php echo $style;?>'nowrap>
	<?php
		if (array_key_exists($result['fid'], $director)) {
	?>
			<A href='MoS_forms.php?action=revert&ID=<?php echo $director[$result['fid']]; ?>' onclick='return confirm("Are you sure you want to revert to the original RSS site form? All changes for the MoS will be lost.")'>Revert to Original</A>
	<?php
		}
		else {
			echo "<BR>";
		}
	?>
	</td>
	<td valign="top" align="right" class="text_12" style='<?php echo $style;?>'nowrap>
		<A href='MoS_edit-forms-view.php?sync=<?php echo $result['fid'];?>'>Sync</A><BR>
	</td>
  </tr>
  <?php
	}
	?>
</table>

