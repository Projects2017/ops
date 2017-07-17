<?php
if(!$_GET) 
{
	die('This page requires data to be sent via GET.');
}
$pos_str = str_replace(';',',',$_GET['ids']);
$pos_array = explode(";",$_GET['ids']);
foreach($pos_array as $ponum)
{
	$disp_pos_array[] = $ponum+1000;
}
$disp_pos_str = implode(', ',$disp_pos_array);
$viewonly = isset($_GET['viewonly']) ? true : false;
if(count($pos_array)==1) header("Location: addbol.php?id=".$pos_str.($viewonly ? '&viewonly' : ''));
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid) include("../secure.php");
require('inc_shipping.php');
$sql = "SELECT po FROM BoL_queue WHERE po IN ($pos_str)";
$query = mysql_query($sql);
while($res = mysql_fetch_assoc($query))
{
	$order_id_array[] = $res['po'];
	$order_id_source[] = "po";
}
$order_ids = implode(',',$order_id_array);
$sql = "SELECT snapshot_form, user FROM order_forms WHERE ID IN (".$order_ids.")";
$query = mysql_query($sql);
while ($result = mysql_fetch_assoc($query))
{
	$formids_array[] = $result['snapshot_form'];
	$user_check[] = $result['user'];
}
$users_in_bol = array_unique($user_check);
if(count($users_in_bol)>1)
{
	setcookie('BoL_msg', 'Multiple PO Bills of Lading must be delivered to the same address.', time()+5);
	header("Location: shipping.php");
	exit();
}
$formids_str = implode(',',$formids_array);

$origid_sql = "SELECT orig_id FROM snapshot_forms WHERE id IN ($formids_str)";
$vendor_sql = "SELECT vendor FROM forms WHERE ID IN ($origid_sql)";
$vendorname_sql = "SELECT name AS vendorname FROM vendors WHERE ID in ($vendor_sql)";
$query = mysql_query($vendorname_sql);
checkdberror($vendorname_sql);
$result = mysql_fetch_assoc($query);
$vendorname = $result['vendorname'];

$sql = "SELECT DISTINCT address, city, state, zip FROM snapshot_forms WHERE id IN (".$formids_str.")";
$query = mysql_query($sql);
checkdberror($sql);
if(mysql_num_rows($query)>1)
{
	setcookie('BoL_msg', 'To select multiple POs in one Bill of Lading, the orders must have the same dealer and vendor addresses', time() + 5);
	header('Location: shipping.php');
	exit();
}
foreach($order_id_array as $po_id)
{
	$sql = "SELECT snapshot_user, form, user, freight_percentage, total, snapshot_form FROM order_forms WHERE ID = ".$po_id;
	$query = mysql_query($sql);
	$result = mysql_fetch_assoc($query);
	$user_id[] = $result['snapshot_user'];
	$form_id[] = $result['snapshot_form'];
	if ($result['freight_percentage'] == "")
	{
		$freightperc[] = 0;
	}
	else
	{
		$freightperc[] = $result['freight_percentage'];
	}
	$ordertotal[] = $result['total'];
}

$sql = "SELECT * from snapshot_users WHERE id = ".($_GET['shipto'] ? $_GET['shipto'] : $user_id[0]);
$query = mysql_query($sql);
checkdberror($sql);
$snapshot_result = mysql_fetch_array($query);


$sql = "SELECT DISTINCT address, city, state, zip from snapshot_users WHERE id IN (".implode(',',$user_id).")";
$query = mysql_query($sql);
checkdberror($sql);
if(mysql_num_rows($query)>1)
{
	setcookie('BoL_msg', 'To select multiple POs in one Bill of Lading, the orders must have the same dealer and vendor addresses', time() + 5);
	header('Location: shipping.php');
	exit();
}
$queue_id = "";
$queue_po = "";
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>Retail Service Systems - Bill of Lading</title>

  <link rel="stylesheet" href="/css/styles.css" type="text/css">
  <script src="bol.js" language="javascript" type="text/javascript"></script>
  </head><body onLoad="setVars();">
<?php include("../menu.php"); ?>
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="2">
      <h1><?php echo $vendorname; ?></h1>
 	  <?php
if($viewonly) 
{ ?>
	  <span style="font-weight: bold" align="center">Open Items for PO#s <?php= $disp_pos_str ?></span>
<?php } else { ?>
      <small><span style="font-weight: bold;">STRAIGHT BILL OF LADING</span><br />
      <span style="font-weight: bold;">ORIGINAL - NOT NEGOTIABLE</span></small><br >
<?php } ?>
      </td>
    </tr>
  </tbody>
</table>
<form name="add_bol" action="do_multiaddbol.php" method="post">
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" border="1" cellpadding="5" cellspacing="0">
  <tbody><?php

if(!$viewonly)
{ // don't need to see this info if we're just viewing the leftover items
  ?>
    <tr>
      <td style="text-align: center;" colspan="2">
<?php
	$sql = "SELECT MAX(ID) as last_id FROM BoL_forms";
	$query = mysql_query($sql);
	$result = mysql_fetch_assoc($query);
	$nextBoL = $result['last_id'] + 1001;
?>
      <hr style="width: 100%; height: 2px;" /><big style="font-weight: bold;">BOL#:&nbsp; <?php echo $nextBoL; ?>&nbsp;&nbsp;
Date:&nbsp; <?php echo date('m/d/Y'); ?>&nbsp;&nbsp; Time:&nbsp; <?php echo date('h:iA'); ?><br />
      </big>
      <hr style="width: 100%; height: 2px;" /></td>
    </tr>
    <tr valign="top">
      <td width="50%">
      <p class="text_16"><br />
      <br /></p>
      <p class="text_16"><small><span style="font-weight: bold;">Name of Carrier:
            <select name="carrier_name">
      <?php
		$sql = "SELECT name FROM shipping_carriers ORDER BY name";
		$que = mysql_query($sql);
		checkdberror($sql);
		while($res = mysql_fetch_assoc($que))
		{
			?><option value="<?php= $res['name'] ?>"<?php if($carrier_name == $res['name']) echo ' selected="selected"'; ?>><?php= $res['name'] ?></option>
		<?php
		} ?></span></small>
      </select>
      </p>
      </td>
      <td width="50%">
      <p class="text_16"><small><span style="font-weight: bold;">Tracking No:</span><input type="text" length="15" name="tracking_num" tabindex="2"><br />
      <span style="font-weight: bold;">&nbsp;<br />
      <span style="font-weight: bold;">Date:</span><input type="text" length="15" name="ship_date" value="<?php echo date('n/d/Y'); ?>" tabindex="3"></small></p>
      </td>
    </tr>
<?php
} // end of viewonly suppressed section
?>
    <tr>
      <td style="vertical-align: top;">
      <?php if(!$viewonly) { ?><span id="editconsignee" title="Click to enter custom address" onClick="editConsignee('<?php= $_GET['ids'] ?>',<?php= $user_id[0] ?>,'<?php= $po_source ?>'<?php if($_GET['shipto']) echo ',1,'.$_GET['shipto']; ?>)"><?php } ?><b>Consignee
<?php
if(!$viewonly)
{ // only need the option to select custom address if not viewonly ?> (click <?php
	  if($_GET['shipto']) { echo "to cancel "; } else { echo 'for '; } ?>custom address):<?php } ?></b><br />
<?php
	$j = 0;
	$tabindex = 4;
	foreach($pos_array as $po_id)
	{
		$sql = "SELECT {$order_id_source[$j]} FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
		$query = mysql_query($sql);
		$res = mysql_fetch_assoc($query);
		$working_po_id = $res[$order_id_source[$j]];
		$sql = "SELECT snapshot_user, form, user, freight_percentage, total, snapshot_form 	FROM order_forms WHERE ID = ".$working_po_id;
		$query = mysql_query($sql);
		$result = mysql_fetch_assoc($query);
		$user_id[] = $result['snapshot_user'];
		$form_id[] = $result['snapshot_form'];
		if ($result['freight_percentage'] == "")
		{
			$freightperc[] = 0;
		}
		else
		{
			$freightperc[] = $result['freight_percentage'];
		}
		$ordertotal[] = $result['total'];
		$j++;
	}
	$sql = "SELECT * from snapshot_users WHERE id = ".($_GET['shipto'] ? $_GET['shipto'] : $user_id[0]);
	$query = mysql_query($sql);
	checkdberror($sql);
	$result = mysql_fetch_array($query);
	?>
	<small><?php echo $result['last_name'];
	if(!$_GET['shipto']) echo ', '.$result['first_name']." <strong>(".$result['orig_id'].")</strong>";
	echo "<br />".$result['address']."<br />".$result['city'].", ".$result['state'].". ".$result['zip']."<br />PH:".$result['phone']."<br />"; ?></small></span></td>
	<td style="vertical-align: top;"><b>Shipper:</b><br />
	<small><?php
	$sql = "SELECT address, city, state, zip, phone, fax FROM snapshot_forms WHERE id = ".$form_id[0];
	$query = mysql_query($sql);
	checkdberror($sql);
	$result = mysql_fetch_assoc($query);
	echo $result['address']."<br />\n".$result['city'].", ".$result['state'].". 	".$result['zip']."<br />\n";
	echo "PH:".$result['phone']."<br />\nFAX:".$result['fax']."</small></td>\n";
	
	$j = 0;
foreach($pos_array as $po_id) {
  $sql = "SELECT {$order_id_source[$j]} FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
  $query = mysql_query($sql);
  $res = mysql_fetch_assoc($query);
  $working_po_id = $res[$order_id_source[$j]];
  $sql = "SELECT po_suffix FROM BoL_forms WHERE po = $working_po_id";          // get all suffixes used so far
  $que = mysql_query($sql);
  checkdberror($sql);
  while($result = mysql_fetch_assoc($que)) {
  if (!is_null($result['po_suffix'])) $suffixes[] = chr($result['po_suffix']); } // get all the current suffixes for this base PO # and throw them into an array as ints
  // if there's currently suffixes, display the last entered + 1; otherwise, a null field
  if($suffixes) {
    $suff = chr(ord(array_pop($suffixes))+1);
  } else if(mysql_num_rows($que)) {
    $suff = "0";
  } else { $suff = ""; }
  $print_po_ids[] = ($po_id+1000).$suff;
  $po_ids[] = $working_po_id+1000;
  $po_suffs[] = $suff;                  // push the display PO# to an array
  if($suff!="") { $print_po_suff[] = substr($suff, 1); } else { $print_po_suff[] = ""; }
  $j++;
}
$j = 0;
foreach($pos_array as $po_id) {
  $sql = "SELECT {$order_id_source[$j]} FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
  $query = mysql_query($sql);
  $res = mysql_fetch_row($query);
  $working_po_id = $res[0];
  $sql = "SELECT ordered, snapshot_form FROM order_forms WHERE ID = ".$working_po_id;
  $query = mysql_query($sql);
  checkdberror($sql);
  $result = mysql_fetch_row($query);
  $orderdatetime[] = $result[0];
  $snapshot_form[] = $result[1];
$j++;
}

	?></tr>
  </tbody>
</table>
<table style="width: 80%; margin-right: auto; margin-left: auto; text-align: left;" border="1" cellpadding="5" cellspacing="0">
  <tbody><?php if(!$viewonly) { // only need to see PO header if we're adding a new one
  ?>
    <tr>
      <td colspan="8" rowspan="1" style="vertical-align: top;">
      <hr style="width: 100%; height: 2px;">
      <div style="text-align: center;"><big><b>PO#: <?php             // create the PO # display array
echo implode(', ', $print_po_ids);
?>&nbsp;&nbsp;&nbsp;Dates: <?php
foreach($orderdatetime as $ordertime) {  // can't use implode to display order date/times
  static $addcomma = false;
  if($addcomma) echo ', ';
  echo date('m/d/Y', strtotime($ordertime));
  $addcomma = true;
}
?>&nbsp;&nbsp;&nbsp;Times: <?php
foreach($orderdatetime as $ordertime) {
  static $addcomma2 = false;
  if($addcomma2) echo ', ';
  echo date('h:iA', strtotime($ordertime));
  $addcomma2 = true;
}
?></b></big></div>
      </td>
    </tr>
    <?php } // end viewonly suppressed section
    ?>
    <tr>
      <td colspan="1" class="orderTH"><small>PO #</small></td>
      <td colspan="2" class="orderTH"><small>Item</small></td>
      <td colspan="1" class="orderTH"><small>Set</small></td>
      <td colspan="1" class="orderTH"><small>Matt</small></td>
      <td colspan="1" class="orderTH"><small>Box</small></td>
      <td colspan="1" class="orderTH"><small>Class</small></td>
      <td colspan="1" class="orderTH" align="right"><small>Total Pcs.</small></td>
    </tr>
<?php
$row = 0;
$pocnt = 0;
$j = 0;
foreach($pos_array as $po_id)
{
	$sql = "SELECT {$order_id_source[$j]} FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
	$query = mysql_query($sql);
	$res = mysql_fetch_row($query);
	$working_po_id = $res[0];
	echo '<tr valign="top">'."\n".'<td class="orderTD" rowspan="'.openItemCount($working_po_id).'"><small>'.$print_po_ids[$pocnt].'</small></td>'."\n";
	$new_po = true;
	$sql = "SELECT setqty, mattqty, qty, item FROM orders WHERE po_id = ".$working_po_id;
	$query = mysql_query($sql);
	while($result = mysql_fetch_assoc($query))
	{
		if(!openItem($working_po_id, $result['item'])) continue;
		$item = openItemInfo($working_po_id, $result['item']);
		$row++;
		$showrow = strlen($row) == 1 ? '0'.$row : $row; // define showrow to append to the end of the field names for id'ing in the process script
		// now we make the next row for this PO
		if(!$new_po) echo '<tr valign="top">';
  		?>
      	<td class="orderTD"><small><?php echo $item['partno']; ?></small><input type="hidden" name="_po<?php echo $showrow; ?>" id="_po<?php echo $showrow; ?>" value="<?php echo $po_ids[$pocnt]; ?>"><input type="hidden" name="setamt_<?php= $showrow ?>" id="setamt_<?php= $showrow ?>" value="<?php= $item['setamt'] ?>"><input type="hidden" id="weight_<?php= $showrow ?>" name="weight_<?php= $showrow ?>" value="<?php= $item['weight'] ?>"><input type="hidden" id="linetotalweight_<?php= $showrow ?>" name="linetotalweight_<?php= $showrow ?>" value="0"><input type="hidden" name="_srcpo<?php echo $showrow; ?>" id="_srcpo<?php echo $showrow; ?>" value="<?php echo $order_id_source[$j]; ?>"><input type="hidden" name="_suffpo<?php echo $showrow; ?>" id="_suffpo<?php echo $showrow; ?>" value="<?php echo $print_po_suff[$pocnt]; ?>"><input type="hidden" name="item<?php echo $showrow; ?>" id="item<?php echo $showrow; ?>" value="<?php echo $result['item'].'">'; ?></td>
      	<td class="orderTD"><small><?php echo $item['desc']; ?></small></td>
      	<?php
      	if($result['setqty']>0) {
			if(!$viewonly)
			{
        		echo '<td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="setqty';
        		echo $showrow;
        		echo '" id="set_';
        		echo $showrow;
        		echo '" onchange="recalcrow(\'';
        		echo $showrow;
        		echo '\','.$item['setqty'].');" tabindex="'.$tabindex.'">'."\n";
        		$tabindex++;
        		for($i=$item['set']; $i>=0; $i--) {
          			echo "\t<option value=\"$i\">$i</option>\n";
          			if($i==$item['set']) echo "\t<option value=\"blank\">---</option>\n";
        		}
        		echo "</select>\n";
        	}
        	else
        	{
        		?><td colspan="1" class="orderTD"><small><?php= $item['set'] ?><?php
        	}
      	} else {
      		echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"set_";
      		echo $showrow;
      		echo "\" name=\"setqty";
      		echo $showrow;
      		echo "\" value=\"0\">0";
      	} ?>
      	</small></td>
      	<?php
      	if($result['mattqty']>0) {
			if(!$viewonly)
			{
      			echo '<td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="mattqty';
      			echo $showrow;
         		echo '" id="matt_';
         		echo $showrow;
         		echo '" onchange="recalcrow(\'';
         		echo $showrow;
         		echo '\','.$item['setqty'].');" tabindex="'.$tabindex.'">'."\n";
         		$tabindex++;
         		for($i=$item['matt']; $i>=0; $i--)
         		{
         			echo "\t<option value=\"$i\">$i</option>\n";
    	      		if($i==$item['matt']) echo "\t<option value=\"blank\">---</option>\n";
         		}
         		echo "</select>\n";
         	}
         	else
         	{
         		?><td colspan="1" class="orderTD"><small><?php= $item['matt'] ?><?php
         	}
      	} else {
      		echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"matt_";
      		echo $showrow;
      		echo "\" name=\"mattqty";
      		echo $showrow;
      		echo "\" value=\"0\">0";
      	} ?>
      	</small></td>
      	<?php
      	if($result['qty']>0) {
			if(!$viewonly)
			{
      			echo '<td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="boxqty';
      			echo $showrow;
      			echo '" id="box_';
      			echo $showrow;
      			echo '" onchange="recalcrow(\'';
      			echo $showrow;
      			echo '\','.$item['setqty'].');" tabindex="'.$tabindex.'">'."\n";
      			$tabindex++;
      			for($i=$item['box']; $i>=0; $i--)
      			{
      				echo "\t<option value=\"$i\">$i</option>\n";
      				if($i==$item['box']) echo "\t<option value=\"blank\">---</option>\n";
      			}
      			echo "</select>\n";
      		}
      		else
      		{
      			?><td colspan="1" class="orderTD"><small><?php= $item['box'] ?><?php
      		}
      	} else {
      		echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"box_";
      		echo $showrow;
      		echo "\" name=\"boxqty";
      		echo $showrow;
      		echo "\" value=\"0\">0";
      	} ?>
      	</small></td>
      	<td class="orderTD" align="right"><small><?php if($viewonly) { echo '&nbsp;'; } else { ?><input type="text" size="15" name="class<?php echo $showrow; ?>" id="class<?php echo $showrow; ?>" tabindex="<?php echo $tabindex; $tabindex++; ?>"><?php } ?></small></td>
      	<td class="orderTD" align="right"><div id="linetotalpcs<?php echo $showrow; ?>" name="linetotalpcs<?php echo $showrow; ?>"><?php
      	static $formtotal;
      	$formtotal += ($item['set']*$item['setqty'])+$item['matt']+$item['box'];
      	echo ($item['set']*$item['setqty'])+$item['matt']+$item['box']; ?></div></td>
      	</tr>
		<?php
  		$new_po = false;
  	}
  	$pocnt++;
  	$j++;
}
echo '<input type="hidden" name="totalrows" id="totalrows" value="'.$row.'">'."\n";
if(!$viewonly)
{
?>   <tr>
	  <td colspan="5" align="left">Comment: <input type="text" name="po_comment" size="40"></td><td colspan="3" align="right">Total Pieces:&nbsp;&nbsp<span style="font-weight: bold;" id="totalpcs"><?php echo $formtotal; ?></span><br /><small>Weight:&nbsp;<span id="disp_weight">0</span> lbs.<input type="hidden" id="weight" name="weight" value="0"></small></td>
    </tr>
    <tr align="right">
      <td colspan="8" style="vertical-align: top;" align="right">Prepaid Amount Balances <?php
      $notfirst = false;
      for($i=0; $i<count($pos_array); $i++) {
      if($notfirst) echo '<br>';
      echo 'PO# '.$disp_pos_array[$i].': $';
      $sql = "SELECT SUM(freight) FROM BoL_forms WHERE po = ".$pos_array[$i];
      $query = mysql_query($sql);
      checkdberror($sql);
      $result = mysql_fetch_row($query);
      if($result[0] > ($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100))))) {
        echo '0.00';
      } else {
        echo number_format(number_format(($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100)))), 2)-number_format($result[0], 2), 2);
      }
      $notfirst = true;
      } ?><br /><input type="hidden" name="freightchgs"><?php if($_GET['shipto']) { ?><input type="hidden" name="shipto" value="<?php= $_GET['shipto'] ?>"><?php } ?><input type="hidden" name="prepaidbalance" value="<?php echo $prepaidbalance; ?>"><input type="hidden" name="dbuser" value="<?php= $user_id[0] ?>"><input value="Generate Bill of Lading" type="submit" tabindex="<?php echo $tabindex; ?>">
      </td>
    </tr><?php } else {
// define vars for label print
if(strpos($snapshot_result['last_name'], ','))
{
	$orig_lastname = substr($snapshot_result['last_name'], 0, strpos($snapshot_result['last_name'], ','));
	$orig_firstname = substr($snapshot_result['last_name'], strpos($snapshot_result['last_name'], ',') + 2);
	$namedisplay = "$orig_firstname $orig_lastname";
}
else
{
	$namedisplay = $snapshot_result['last_name'];
}
$labelname = strtoupper($namedisplay);
$labeladdress = strtoupper($snapshot_result['address']);
// this will be the address second line
// $labeladdress2 = strtoupper($snapshot_result['secondaddress']);
// just append it to the labeladdress field, makes it easier methinks
// if($labeladdress2 != '') $labeladdress .= ",'$labeladdress2'";
$labelcitystzip = strtoupper($snapshot_result['city'])." ".strtoupper($snapshot_result['state']).' '.$snapshot_result['zip'];
?><tr><td colspan="8" align="center" id="buttonzone"><button type="button" onclick="javascript:printPickTicket('<?php= $_GET['ids'] ?>')">Print Picking Ticket</button><button type="button" onclick="javascript:printPickLabel('<?php= $_GET['ids'] ?>','<?php= $labelname ?>\n<?php= $labeladdress ?>\n<?php= $labelcitystzip ?>')">Print Labels</button><button type="button" onclick="javascript:setLabelPrinter();">Configure Label Printer</button><br />
<button type="button" onclick="javascript:returnToQueue()">Return to Queue</button></td></tr><?php } ?>
  </tbody>
</table>
</form>
<div id="customaddyformhere"></div>
</body></html>