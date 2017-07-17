<?php
// viewbol.php
// used to display BoLs onscreen and for printing
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
include('inc_shipping.php');
if (!$vendorid)
   include("../secure.php");
if(!$_GET['id']) {
  die("This script requires a BoL ID to be passed via GET.");
}
if(!(secure_is_admin() || secure_is_vendor())) die("Access Denied");
$bol_id = $_GET['id'];
$print_bol_id = $bol_id + 1000;
$edi_order = false;
$sql = "SELECT multi_po, po, po_suffix, user_id AS snapshot_user_id, carrier, trackingnum, oor_updated FROM BoL_forms WHERE ID = $bol_id";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
$snapshot_user_id = $result['snapshot_user_id'];
if($result['multi_po']!="" && $result['multi_po']!="0") { $multipo = true; } else { $multipo = false; }
$carrier_name = $result['carrier'];
$oor_updated = $result['oor_updated'];
$trackingnum = $result['trackingnum'];
if($multipo) {
  $sql2 = "SELECT po, po_suffix FROM BoL_items WHERE bol_id = $bol_id";
  $que2 = mysql_query($sql2);
  checkdberror($sql2);
  $po_array = array();
  $po_suffix_array = array();
  while ($res2 = mysql_fetch_assoc($que2)) {
    $po_array[] = $res2['po'];
    $po_suffix_array[] = $res2['po_suffix'];
  }
  $po_array_unique = array_unique($po_array);
  $po = implode(',', $po_array);
  $po_unique = implode(',', $po_array_unique);
  $po_suffix_unique = array_unique($po_suffix_array);
  $po_suffix = implode(',', $po_suffix_array);
} else {
  $po = $result['po'];
  $po_suffix = $result['po_suffix'];
  // see if we're talking about a CH order
  $sql = "SELECT servicelevel FROM ch_order WHERE po = '$po'";
  checkdberror($sql);
  $res = mysql_query($sql);
  if(mysql_num_rows($res)>0)
  {
  	$chorder = true;
  }
  else
  {
  	$chorder = false;
  }
  // now see if we're in an EDI-based order
  $sql = "SELECT processed FROM edi_files WHERE po_id LIKE '%$po%'";
  $query = mysql_query($sql);
  checkdberror($sql);
  if(mysql_num_rows($query)> 0)
  {
  	$edi_order = true;
	require_once('../include/edi/edi.php');
  }
}
$sql = "SELECT user, form, snapshot_user AS orig_userid, COALESCE(shipto, snapshot_user) AS userid, shipto, freight_percentage, total, snapshot_form FROM order_forms WHERE ID";
if (!$po) {
  $sql .= " = '$po'";
} else {
  $sql .= " IN ($po)";
}
$query = mysql_query($sql);
checkDBerror($sql);
$form_name = array();
while($result = mysql_fetch_assoc($query)) {
  $user_id[] = $result['userid'];
  $base_user_id = $result['user'];
  if($result['shipto']!='' || !is_null($result['shipto']))
  {
  	$useshipto = true;
  }
  else
  {
  	$useshipto = false;
  }
  $orig_userid = $result['shipto'] ? $result['shipto'] : $result['userid'];
  $form_id[] = $result['snapshot_form'];
  if ($result['freight_percentage'] == "") { $freightperc[] = 0; } else { $freightperc[] = $result['freight_percentage']; }
  $ordertotal[] = $result['total'];
  $sq2 = "SELECT COALESCE(shipper, name) AS shipper_name FROM forms WHERE id = '".$result['form']."'";
  $query2 = mysql_query($sq2);
  checkdberror($sq2);
  $result2 = mysql_fetch_assoc($query2);
  $form_name[] = $result2['shipper_name'];
}
$form_name = array_unique($form_name);
if(count($form_name)<2) {
	$interim = $form_name[0];
	unset($form_name);
	$form_name = $interim;
}
// Page begins...
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>Bill of Lading <?php echo $print_bol_id; ?></title>

  <link rel="stylesheet" href="/css/styles.css" type="text/css">
  <!--<script src="../include/printing.js" language="javascript" type="text/javascript"></script>-->
  <script src="bol.js" language="javascript" type="text/javascript"></script>

<?php
/*
 *
 * Adding inline style definition for the display of the customer address next to the Shipper address in the bottom panel of the page
 * Done 9/12/08 by Ostin
 *
 */
?>
<style type="text/css">
.customer_address
{
	width: 50%;
}
</style>
<!--<object ID='WB' WIDTH=0 HEIGHT=0 CLASSID='CLSID:8856F961-340A-11D0-A96B-00C04FD705A2'></object>-->
<!--<object ID='PMDPrint' name='PMDPrint' WIDTH=0 HEIGHT=0 CLASSID='CLSID:9CF0975F-43DB-3307-83FF-8E73172C723C'></object>-->
<!--<script language='VBScript' src="bol.vbs"></script>-->
  </head><body>
<div id="hidemenu"><?php include("../menu.php"); ?></div>
<table style="width: 700; text-align: left; margin-left: auto; margin-right: auto;" align="center" border="1" cellpadding="3" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="2" style="border-style: thin; bottom-border-width:0px">
<?php if(is_array($form_name)) {
	foreach($form_name as $f_name) { ?><h1<?php if(count($form_name)>1) { ?> style="font-size:20px"<?php } ?>><?php echo $f_name; ?></h1><?php }
} else {
	echo "<h1>$form_name</hi>\n"; 
} ?></td></tr>
<tr>
<td style="border-width: 0px;"><small><span style="font-weight: bold; text-align: left">STRAIGHT BILL OF LADING</small></span></td><td style="border-style: thin; border-width:0px" align="right"><small><span style="font-weight: bold; text-align: right">ORIGINAL - NOT NEGOTIABLE</span></small>
      </td>
    </tr>
  </tbody>
</table>
<table style="width: 700; text-align: left; margin-left: auto; margin-right: auto;" align="center" border="1" cellpadding="3" cellspacing="0">
  <tbody>
    <tr>
      <td style="text-align: center;" colspan="2">
      <p style="font-weight: bold; font-size: 16px">BOL#:&nbsp; <?php echo $print_bol_id; ?>&nbsp;&nbsp;
Date:&nbsp; <?php
$sql = "SELECT createdate, carrier, shipdate, weight, servicelevel, comment FROM BoL_forms WHERE ID = '".$bol_id."'";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
$ship_weight = $result['weight'];
$po_comment = $result['comment'];
$service_level = $result['servicelevel'];
echo date('m/d/Y', strtotime($result['createdate'])); ?>&nbsp;&nbsp; Time:&nbsp; <?php echo date('h:iA', strtotime($result['createdate'])); ?>
      </p>
      </td>
    </tr>
    <tr valign="top">
      <td width="50%" rowspan="3">
      <p class="text_16" style="font-size: 14px"><span style="font-weight: bold;">Carrier:&nbsp;<?php
$carrier = $result['carrier'];
if($edi_order)
{
	// we're in an EDI-based order...first thing we do is see what the Retailer was
	// if they use their own shipcodes, we transform the shipcode to theirs
	$edi = new Edi();
	$edi->LoadFromPO($po); // EDI orders are assumed to be singletons
	switch($edi->mEdiVendor->mTypeCode)
	{
		case "WMI":
			$sql = "SELECT name FROM wm_shipcodes WHERE code = '{$result['carrier']}'";
			$query = mysql_query($sql);
			checkdberror($sql);
			$ret = mysql_fetch_assoc($query);
			$carrier = $ret['name'];
			break;
		default:
			break;
	}
}
if(secure_is_admin()) {
      		if($carrier!='')
      			{
      			echo '<input type="hidden" id="origcarrier" name="origcarrier" value="'.$result['carrier'].'"><span id="editcarrier" title="Click to change" onClick="editCarrier('.$bol_id.');">'.$carrier.'</span>'; }
      		else
      			{ echo '<input type="hidden" id="origcarrier" name="origcarrier" value=""><span id="editcarrier" class="noprint" title="Click to enter carrier name" onClick="editCarrier('.$bol_id.');">[Click to enter carrier name]</span>'; }
      	}
      	else 
     		{ echo $carrier; }
if($service_level) { ?><br />Service Level:&nbsp;<?php echo $service_level; } ?></span></p>
      </td>
      <td width="50%">
      <p class="text_16" style="font-size: 14px"><span style="font-weight: bold;">Tracking No:&nbsp;<?php
      	if(secure_is_admin()) {
      		if($trackingnum)
      			{ echo '<input type="hidden" id="origtracking" name="origtracking" value="'.$trackingnum.'">';
      			if($orderobj->mShipCode != '75') // if Ceva, tracking already set, don't allow edit
      			{
      				?><span id="edittracking" title="Click to change" onClick="edit<?php
      				if($edi_order && isset($edi)) { ?>Walmart<?php } ?>Tracking(<?php
      				echo $bol_id.','.$po.',\''.$result['carrier'].'\');">';
      			}
      			echo $trackingnum.'</span>';
      		}
      		else
      			{ echo '<input type="hidden" id="origtracking" name="origtracking" value=""><span id="edittracking" class="noprint" title="Click to enter tracking number" onClick="edit';
      			if($edi_order && isset($edi)) { ?>EdiOrder<?php } ?>Tracking('<?php
      			echo $bol_id.'\');">[Click to enter tracking number]</span>'; }
      	}
      	else 
     		{ echo $trackingnum; }
     ?></span></p></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><span style="font-weight: bold; font-size: 14px" class="text_16">Date:&nbsp;<?php echo date('m/d/Y', strtotime($result['shipdate'])); ?></span>
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top;" class="text_16"><span style="font-size: 14px"><b>Consignee:</b></span><br />
<?php
$sql = "SELECT * from snapshot_users WHERE id = '".$snapshot_user_id."'";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
?>
      <small><?php echo $result['last_name'];
      if(!$useshipto) echo ($result['first_name'] ? ', '.$result['first_name']." <strong>(".$result['orig_id'].")</strong><br />Company" : "");
      echo "<br />".$result['address']."<br />";
      if($result['address2']) echo $result['address2']."<br />";
      echo $result['city'].", ".$result['state'].". ".$result['zip']."<br />";
      if ($result['email']) { echo $result['email']."<br />"; }
      echo "PH:".$result['phone']; ?><br />CALL 2 HRS PRIOR TO DELIVERY</small></td>
      <td style="vertical-align: top;" class="text_16"><span style="font-size: 14px"><b>Shipper:</b></span><br />
      <small><?php
$sql = "SELECT address, city, state, zip, phone, fax FROM snapshot_forms WHERE id = '".$form_id[0]."'";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
if(is_array($form_name)) {
	foreach($form_name as $f_name) {
		echo $f_name."<br />";
	}
} else {
	echo $form_name."<br />";
}
echo $result['address']."<br />\n".$result['city'].", ".$result['state'].". ".$result['zip']."<br />\n";
echo "PH:".$result['phone']."<br />\nFAX:".$result['fax']."</small></td>\n";
?></tr>
  </tbody>
</table>
<table style="width: 700; margin-right: auto; margin-left: auto; text-align: left;" align="center" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="8" rowspan="1" style="vertical-align: top;">
      <hr style="width: 100%; height: 2px;">
      <div style="text-align: center; font-size: 14px" class="text_16"><b>PO#: <?php
if($po_array) {
  foreach($po_array as $k => $v) {
    $sq2 = "SELECT orig_po, po FROM BoL_queue WHERE COALESCE(orig_po, po) = '$v'";
    $qu2 = mysql_query($sq2);
    checkdberror($sq2);
    $re2 = mysql_fetch_assoc($qu2);
    if(is_null($re2['orig_po'])) { $builddisplay = $re2['po']; $po_src = "po"; } else { $builddisplay = $re2['orig_po']; $po_src = "orig_po"; }
    $order_complete[] = isPOClosed($builddisplay, true, $po_src);
    if($po_suffix_array[$k]!="") { $builddisplay = ($builddisplay+1000).chr($po_suffix_array[$k]); } else { $builddisplay = ($builddisplay+1000); }
    $po_display_array[] = $builddisplay;
  }
  $po_disp = array_unique($po_display_array);
  sort($po_disp);
  echo implode(", ", $po_disp);
} else {
  echo $po + 1000;
  $order_complete = isPOClosed($po, true);
  if($po_suffix!="") echo ".".chr($po_suffix);
}
?>
&nbsp;&nbsp;&nbsp;Date: <?php
$sql = "SELECT ordered, snapshot_form FROM order_forms WHERE ID IN (";
if($multipo&&$po_unique) {
  $sql .= "$po_unique)";
} else {
  $sql .= "$po)";
}
$query = mysql_query($sql);
checkdberror($sql);
while ($result = mysql_fetch_assoc($query)) {
  $orderdatetime[] = $result['ordered'];
  $snapshot_form[] = $result['snapshot_form'];
}
foreach($orderdatetime as $orderdate) {
  $compare_date[] = date('m/d/Y', strtotime($orderdate));
}
$print_dates = array_unique($compare_date);
sort($print_dates);
echo implode(", ", $print_dates);
echo '&nbsp;&nbsp;&nbsp;Time: ';
foreach($orderdatetime as $ordertime) {
  $compare_time[] = date('h:iA', strtotime($orderdate));
}
$print_times = array_unique($compare_time);
sort($print_times);
echo implode(", ", $print_times);
?></b></div>
      </td>
    </tr>
    <tr>
      <td class="orderTH" align="right"><small>PO #</small></td>
      <td colspan="2" class="orderTH"><small>Item</small></td>
      <td class="orderTH" align="right"><small>Set</small></td>
      <td class="orderTH" align="right"><small>Matt</small></td>
      <td class="orderTH" align="right"><small>Box</small></td>
      <td class="orderTH" align="right"><small>Class</small></td>
      <td class="orderTH" align="right"><small>Pieces</small></td>
    </tr>
<?php
$row = 0;
$row_po = "";
$po_information = array();
// get counts of the # of items per PO in this BOL
$sql = "SELECT po, COUNT(item) FROM BoL_items WHERE bol_id = '$bol_id' AND (setamt + mattamt + boxamt > 0) GROUP BY po";
$query = mysql_query($sql);
while($result = mysql_fetch_row($query)) {
    $sq2 = "SELECT orig_po, po FROM BoL_queue WHERE COALESCE(orig_po, po) = '$v'";
    $qu2 = mysql_query($sq2);
    checkdberror($sq2);
    $re2 = mysql_fetch_assoc($qu2);
    if(is_null($re2['orig_po'])) { $builddisplay = $re2['po']; } else { $builddisplay = $re2['orig_po']; }


  $sql2 = "SELECT orig_po, po FROM BoL_queue WHERE COALESCE(orig_po, po) = '{$result[0]}'";
  $que2 = mysql_query($sql2);
  $res2 = mysql_fetch_assoc($que2);
  if(is_null($res2['orig_po'])) { $po_infos = array('count' => $result[1], 'orig_po' => $res2['po']); } else { $po_infos = array('count' => $result[1], 'orig_po' => $res2['orig_po']); }
  $po_information[$result[0]] = $po_infos;
}
if(secure_is_admin() && !$multipo)
{
	?><form name="cancelform" method="post" action="do_cancelbol.php"><input type="hidden" name="formid" value="<?php echo $bol_id; ?>"><?php
}
$sql = "SELECT po, item, setamt, mattamt, boxamt, class FROM BoL_items WHERE bol_id = '$bol_id' ORDER BY po, item";
$query = mysql_query($sql);
while($result = mysql_fetch_assoc($query))
{
  if($result['setamt']+$result['mattamt']+$result['boxamt']>0) {
	$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = '".$result['item']."'";
	$query2 = mysql_query($sql2);
	$result2 = mysql_fetch_array($query2);
	$row++;
  ?>
	<tr valign="top">
<?php if($row_po != $result['po']) {
     echo '<td class="orderTD" rowspan="'.$po_information[$result['po']]['count'].'"><small>'.($po_information[$result['po']]['orig_po']+1000).'</small></td>';
     $row_po = $result['po'];
  } ?>
      <td class="orderTD"><small><?php echo $result2['partno']; ?></small></td>
      <td class="orderTD"><small><?php echo $result2['description']; ?></small></td>
      <td class="orderTD" align="right"><small><?php echo $result['setam'];
      
      // ch order credit stuff
      if($result['setamt']!=0 && secure_is_admin() && !$multipo && $chorder && $bol_cancel) // qty>0, admin, single po, CH order & BOL Cancel turned on
      {
      	?><span class="noprint">&nbsp;Remove<select onchange="detectCancel('delset<?php echo $result['item']; ?>');" name="delset<?php echo $result['item']; ?>"><?php
      	for($i=0; $i<=$result['setamt']; $i++)
      	{
      		?><option value="<?php echo $i; if($i==0) echo "\" selected=\"selected"; ?>"><?php echo $i; ?></option><?php
      	}
      ?></select><input type="hidden" name="rsnset<?php echo $result['item']; ?>" value=""><?php
      }
      // end ch order credit
      
      
     
     ?></span></small></td>
      <td class="orderTD" align="right"><small><?php echo $result['mattamt'];


	// ch order credit
      if($result['mattamt']!=0 && secure_is_admin() && !$multipo && $chorder && $bol_cancel) // qty>0, admin, single po, CH order & BOL Cancel turned on
      {
      	?><span class="noprint">&nbsp;Remove<select onchange="detectCancel('delmatt<?php echo $result['item']; ?>');" name="delmatt<?php echo $result['item']; ?>"><?php
      	for($i=0; $i<=$result['mattamt']; $i++)
      	{
      		?><option value="<?php echo $i; if($i==0) echo "\" selected=\"selected"; ?>"><?php echo $i; ?></option><?php
      	}
      ?></select><input type="hidden" name="rsnmat<?php echo $result['item']; ?>" value=""><?php
      }
      // end ch order credit
      
      
      ?></span></small></td>
      <td class="orderTD" align="right"><small><?php echo $result['boxamt'];
      
      // ch order credit
      if($result['boxamt']!=0 && secure_is_admin() && !$multipo && $chorder && $bol_cancel) // qty>0, admin, single po, Ch order & BOL Cancel turned on
      {
      	?><span class="noprint">&nbsp;Remove<select onchange="detectCancel('delbox<?php echo $result['item']; ?>');" name="delbox<?php echo $result['item']; ?>"><?php
      	for($i=0; $i<=$result['boxamt']; $i++)
      	{
      		?><option value="<?php echo $i; if($i==0) echo "\" selected=\"selected"; ?>"><?php echo $i; ?></option><?php
      	}
      ?></select><input type="hidden" name="rsnbox<?php echo $result['item']; ?>" value=""><?php
      }
      // end ch order credit
      
      
      ?></span><small></small></td>
      <td class="orderTD" align="right"><small><?php if(!$result['class']) { echo '&nbsp;'; } else { echo $result['class']; } ?></small></td>
      <td class="orderTD" align="right"><?php
      static $formtotal;
      $formtotal += ($result['setamt']*$result2['setamt'])+$result['mattamt']+$result['boxamt'];
      echo ($result['setamt']*$result2['setamt'])+$result['mattamt']+$result['boxamt']; ?></td>
    </tr>
<?php }
}
?>   <tr>
      <td colspan="4" align="left"><span class="orderTD">Comment: </span><span class="orderTD"><?php
	if(secure_is_admin() || secure_is_vendor()) {
     	if($po_comment) {
			echo '<input type="hidden" id="origcomment" name="origcomment" value="'.$po_comment.'"><span id="editcomment" title="Click to change" onClick="editComment('.$bol_id.');">'.$po_comment.'</span>';
		} else {
			echo '<input type="hidden" id="origcomment" name="origcomment" value=""><span id="editcomment" class="noprint" title="Click to enter comment" onClick="editComment('.$bol_id.');">[Click to enter comment]</span>';
		}
	} else {
		echo $po_comment;
	}
?></span></td><td colspan="4" align="right" class="orderTD">Weight:&nbsp;<span style="font-weight: bold;"><?php if($ship_weight=="") { echo "0"; } else { echo $ship_weight; } ?>&nbsp;lbs.&nbsp;&nbsp;</span>Total Pieces:&nbsp;&nbsp<span style="font-weight: bold;"><?php echo $formtotal; ?></span></td>
    </tr>
    <tr align="right">
      <td class="orderTD" valign="top" colspan="2" style="font-weight: bold;"><?php
      $comp = true;
      if($multi_po)
      {
      	foreach($order_complete as $shipcheck)
      	{
        		if($shipcheck==0) $comp = false;
      	}
      }
      else
      {
      	if($order_complete==0) $comp = false;
      }
      if($comp) { echo "Order Complete"; } else { echo "Partial Shipment"; } ?></td>
      <td colspan="2" style="vertical-align: top;" align="right" class="text_12">&nbsp;<?php
	  $freightbalance = array();
	  $freightbalance_yn = false;
	  $prepaidbalance = array();
	  $prepaidbalance_yn = false;
	  $prepaid_balance = false;
	  $straightfreight = array();
	  $straightfreight_yn = false;
	  for($i=0;$i<count($freightperc);$i++)
	  {
	  	if ($freightperc[$i] != 0)
	  	{
      		$prepaidbalance_yn = true;
        	$sql = "SELECT bol_id FROM BoL_items WHERE po = '";
        	if($multipo)
        	{
          		$sql .= $po[$i];
        	}
        	else
        	{
          		$sql .= $po;
        	}
			$sql .= "'";
			//echo "sql = $sql<br />\n";
        	$query = mysql_query($sql);
        	checkdberror($sql);
        	$res = mysql_fetch_row($query);
        	$bols = $res[0];
        	$sql = "SELECT COUNT(freight) as freightcount FROM BoL_forms WHERE ID = '$bols' AND freight IS NOT NULL"; // see if there's an amount posted
			//echo "sql = $sql<br />\n";
        	$query = mysql_query($sql);
        	checkdberror($sql);
        	$result = mysql_fetch_array($query);
        	if($result['freightcount']>0)
        	{
        		$displayfreight = true;
        		//echo "displayfreight = true<br />\n";
        	}
        	else
        	{
        		$displayfreight = false;
        		//echo "displayfreight = false<br />\n";
        	}
        
        	$sql = "SELECT SUM(freight) FROM BoL_forms WHERE ID = '$bols'";
			//echo "sql = $sql<br />\n";
        	$query = mysql_query($sql);
        	checkdberror($sql);
        	$result = mysql_fetch_row($query);
        	//echo "result[0] = {$result[0]}; ";
        	//echo "ordertotal[i] = {$ordertotal[$i]}; freightperc[i] = {$freightperc[$i]}; ";
        	//echo "comparison result is ".($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100))));
        	if($result[0] > ($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100)))))
        	{
          		$freightbalance_yn = true;
				//echo "freightbalance_yn = true<br />\n";
          		$freightbalance[] = $result[0]-number_format($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100))),2);
        	} 
        	else
        	{
        		$prepaidbalance_yn = true;
				//echo "prepaidbalance_yn = true<br />\n";
          		$prepaidbalance[] = number_format(($ordertotal[$i] - ($ordertotal[$i]/(1+($freightperc[$i]/100)))), 2)-$result[0];
        	}
      	}
      	else
      	{
        	$straightfreight_yn = true;
        	//echo "freightbalance_yn = true<br />\n";
        	$sql = "SELECT COUNT(freight) as freightcount FROM BoL_forms WHERE ID = '$bol_id' AND freight IS NOT NULL"; // see if there's an amount posted
			//echo "sql = $sql<br />\n";
        	$query = mysql_query($sql);
        	checkdberror($sql);
        	$result = mysql_fetch_array($query);
        	if($result['freightcount']>0)
        	{
        		$displayfreight = true;
        		//echo "displayfreight = true<br />\n";
        	}
        	else
        	{
        		$displayfreight = false;
        		//echo "displayfreight = false<br />\n";
        	}
        	$sq = "SELECT freight FROM BoL_forms WHERE ID = '$bol_id'";
			//echo "sql = $sq<br />\n";
        	$qu = mysql_query($sq);
        	$re = mysql_fetch_assoc($qu);
        	$straightfreight = number_format($re['freight'], 2);
        	//echo "straightfreight = $straightfreight<br />\n";
      	}
     }
     if($prepaidbalance_yn && array_sum($prepaidbalance) >= 0)
     {
     	$freight_display = "Prepaid Freight Balance: $".number_format(array_sum($prepaidbalance), 2);
      	$freight_amt = number_format(array_sum($prepaidbalance), 2);
     }
     if($straightfreight_yn)
     {
      	$freight_display = "Freight Amt: $".number_format($straightfreight, 2);
      	$freight_amt = number_format($straightfreight, 2);
     }
     //echo "this is freight_amt";
     //var_dump($freight_amt);
     //echo "this is freight_display";
     //var_dump($freight_display);
     // if there is a freight amount to display, we'll put it into the form; otherwise, we'll add an input field which would apply the freight to the right account, just as before
     if(isset($freight_display) && $displayfreight)
     {
      	if(secure_is_superadmin())
      	{
      		echo '<input type="hidden" id="origfreight" name="origfreight" value="'.$freight_amt.'"><span id="editfreight" title="Click to change freight amount" onClick="edit';
      		if($edi_order && isset($edi)) { echo 'EdiOrder'; }
      		echo 'Freight('.$bol_id.','.$base_user_id.',['.$po.']);">';
      	}
      	echo $freight_display;
      	if(secure_is_superadmin())
      	{
      		echo '</span>';
      	}
      } else {
      	if(secure_is_admin()) {
      		{ echo '<input type="hidden" id="origfreight" name="origfreight" value=""><span id="editfreight" class="noprint" title="Click to enter freight amount" onClick="edit';
      		if($edi_order && isset($edi)) { ?>EdiOrder<?php }
      		echo 'Freight('.$bol_id.','.$base_user_id.',['.$po.']);">[Click to enter freight]</span>'; }
      	}
      	else 
     		{ echo "&nbsp;"; }
      }      
      ?></td><?php
       foreach($freightbalance as $freightcheck) {
         if($freightcheck>0) $doit = true;
       } ?><td style="vertical-align: top;" colspan="4"><div class="text_12" style="font-size: 14px"><?php
       if($doit) { echo '<b><u>FREIGHT IN EXCESS OF PREPAID BALANCE WILL BE CHARGED TO ACCOUNT</u></b>'; } else { echo '<b>FREIGHT PREPAID</b>'; } ?></div></td>
    </tr>
    <tr>
      <td colspan="4" style="vertical-align: top; font-size: 8px">NOTE:&nbsp; Where the rate is dependent on value,
shippers are required to state specifically in writing the agreed or
declared value of the property.&nbsp; The agreed or declared value of
the property hereby specifically stated by the shipper to be not
exceeding:<br />
      <br />
      <br />
      <br />
$&nbsp;<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;
per&nbsp;<span style="text-decoration: underline;"> &nbsp; &nbsp;
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></td>
      <td colspan="4" style="vertical-align: top; text-align: center; font-size: 8px">Subject to Section 7 of the
conditions.&nbsp; If this
shipment is to be delivered to the consignee without recourse on the
consignor, the consignor shall sign the following statement: <br />
      <br />
The carrier shall not make delivery of the shipment without payment
of freight and all other lawful charges.<br />
      <br />
      __________________________________________<br />
      <span style="font-style: italic;">(Signature of Consignor)</span></td>

    </tr>
    <tr>
      <td colspan="8">
      <div><span style="font-size: 8px">RECEIVED,
subject to the classifications and lawful filed tariffs in effect on
the date of the issue of this Bill of Lading, the property described
above in apparent good order, except as noted (contents and condition
of contents of packages unknown), marked, consigned, and destined as
indicated above which said carrier (the word carrier being&nbsp;
understood throughout this contract as meaning any person or corporation
in possession of the&nbsp;property under the contradict) agrees to
carry to its usual place of delivery at said destination, if on its
route, otherwise to deliver to another carrier to said
destination.&nbsp; It is mutually agree as to each carrier of all or
any of said property overall all or any portion of said route to
destination end as to each party at any time interested in all or any
of said property, that every service to be performed here under shall
be subject to all the bill of lading terms and conditions in the
governing classification on the date of shipment.<br /><br />Shipper
hereby certifies that he is familiar with all the terms and conditions
of the said bill of lading set forth in the classification or tariff
which governs the transportation of this shipment, and the said terms
and conditions are hereby agreed to by the shipper and accepted from
himself and his assigns.</span></div>
      </td>
    </tr>
    <tr>
      <td colspan="4" style="vertical-align: top;">
      <table border="0" width="100%" style="text-align: left; margin-left: auto; margin-right: auto;">
      <tr>
      <td class="customer_address">
      <span style="font-weight: bold; font-size: 12px" align="left">SHIPPER:</span><br />
      <span><small><?php
$sql = "SELECT COALESCE(shipper, name) AS shipper_name, address, city, state, zip, phone, fax FROM snapshot_forms WHERE id = '".$form_id[0]."'";
if(is_array($form_name)) {
	foreach($form_name as $f_name) {
		echo $f_name."</small></span><br />";
	}
} else {
	echo $form_name."</small></span><br />";
}
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
echo '<span><small>'.$result['address']."</small></span><br />\n";
echo '<span><small>'.$result['city'].", ".$result['state']."  ".$result['zip']."</small></span></td>\n";
$orig_sql = "SELECT last_name, address, city, state, zip FROM snapshot_users WHERE ID = $orig_userid";
$orig_query = mysql_query($orig_sql);
checkdberror($orig_sql);
$orig_result = mysql_fetch_assoc($orig_query);
if(strpos($orig_result['last_name'], ','))
{
	$orig_lastname = substr($orig_result['last_name'], 0, strpos($orig_result['last_name'], ','));
	$orig_firstname = substr($orig_result['last_name'], strpos($orig_result['last_name'], ',') + 2);
	$orig_namedisplay = "$orig_firstname $orig_lastname";	
}
else
{
	$orig_namedisplay = $orig_result['last_name'];
}
?>
	<td class="customer_address">
    <span style="font-weight: bold; font-size: 12px" align="left">CUSTOMER:</span><br />
<?php
// define vars for label print
$labelname = strtoupper($orig_namedisplay);
$labeladdress = strtoupper($orig_result['address']);
// this will be the address second line
// $labeladdress2 = strtoupper($orig_result['secondaddress']);
// just append it to the labeladdress field, makes it easier methinks
// if($labeladdress2 != '') $labeladdress .= "\n$labeladdress2";
$labelcitystzip = strtoupper($orig_result['city'])." ".strtoupper($orig_result['state']).' '.$orig_result['zip'];

echo '<span><small>'."$orig_namedisplay</small></span><br />\n";
echo '<span><small>'.$orig_result['address']."</small></span><br />\n";
echo '<span><small>'.$orig_result['city'].", ".$orig_result['state']."  ".$orig_result['zip']."</small></span><br />\n";
?></td>
	</tr>
	</table>
      <td colspan="4" style="vertical-align: top;"><br />
      <small>Carrier:<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
      </span>Per: &nbsp; &nbsp; &nbsp;<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
      </span>Date: &nbsp; &nbsp;<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></small><br />
      </td>
    </tr>
    <tr>
      <td colspan="8" align="center"><div id="printbtn"><button type="button" onclick="javascript:returnToQueue()"<?php if(!$oor_updated || !$trackingnum) { echo ' title="OOR Updated & Tracking number required" disabled'; } ?>>Return to Queue</button>
      <?php if(secure_is_admin() && !$multipo && $chorder && $bol_cancel) { ?><span id="hideme" style="visibility: hidden; display: none">
      <br />Cancellation Comments:<input type="text" name="cancelcomments" size="80"><br /><button onclick="submit();" id="cancelbtn">
      Remove Selected Item(s)/Amount(s)</button></span></form><?php } ?><br /><?php
      if($edi_order && isset($edi) && $edi->mEdiVendor->mTypeCode == 'WMI' && $freight_amt >= 0)
      {
      	?><button type="button" onclick="window.location='printwalmartpacking.php?po=<?php= $po ?>&bol=<?php= $bol_id ?>'">Print Walmart Packing Slip</button><?php
      }
      if($edi_order && secure_is_superadmin())
      {
      	// first we see if the shipment EDI has already been generated...if not, create a button that makes it
      	// add the EDI stuff
      	require_once('../include/edi/edi.php');
      	$edi = new EdiSH();
      	$run = $edi->EdiExistsCheck($bol_id);
      	if(!$run) {
      	?><button type="button" onclick="javascript:makeShipmentEdi(<?php= $bol_id ?>)">Generate Shipment EDI File</button><?php }
      }

      ?><button type="button" onclick="javascript:printBoL()"<?php if(!$oor_updated || !$trackingnum) { echo ' title="OOR Updated & Tracking number required" disabled'; } ?>>
      Print BoL</button>&nbsp;<button type="button" onclick="javascript:printLabel('<?php= $labelname ?>\n<?php= $labeladdress ?>\n<?php= $labelcitystzip ?>')">
      Print Label</button>&nbsp;<button type="button" onclick="javascript:setLabelPrinter();">Configure Label Printer</button>&nbsp;<button type="button" onclick="javascript:updateOOR(<?php echo $bol_id; ?>)">Update OOR</button><?php 
      if(secure_is_admin()) { ?>&nbsp;<button type="button" onclick="javascript:printAdmin(<?php echo $bol_id; ?>)">Admin Print</button>&nbsp;<?php } ?></div></td>
    </tr>
  </tbody>
</table>
</body></html>
