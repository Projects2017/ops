<?php
if(!isset($_GET) && !$_COOKIE['shipping_agent']) {
	die('This page requires data to be sent via GET or have a shipping agent selected.');
}
$po_id = $_GET['id'] ? $_GET['id']-1000 : $_COOKIE['po_id'];
$makeEdi = isset($_GET['edi']) && $_GET['edi'] == 1 ? true : false;
$viewonly = isset($_GET['viewonly']) ? true : false;
$superadminview = false; // if we're a superadmin, we're able to go into viewonly mode any time, showing the entire original order
if($_COOKIE['po_id']) setcookie('po','',time()-2);
if($_COOKIE['carrier_name'])
{
	$carrier_name = $_COOKIE['carrier_name'];
	setcookie('carrier_name','',time()-2);
}
if($_COOKIE['carrier_abbrev'])
{
	$carrier_abbrev = $_COOKIE['carrier_abbrev'];
	setcookie('carrier_abbrev','',time()-2);
}
if($_COOKIE['txtservice_level'])
{
	if($_COOKIE['txtservice_level']!='') $txtservice_level = $_COOKIE['txtservice_level'];
	setcookie('txtservice_level','',time()-2);
}
if($_COOKIE['tracking_num'])
{
	$tracking_num = $_COOKIE['tracking_num'];
	setcookie('tracking_num','',time()-2);
}
if($_COOKIE['ship_date'])
{
	setcookie('ship_date','',time()-2);
}
if(is_numeric($_COOKIE['shipping_agent']))
{
	$shipping_agent = $_COOKIE['shipping_agent'];
	setcookie('shipping_agent','',time()-2);
}
else
{
	setcookie('shipping_agent','',time()-2);
}
if($_COOKIE['nocarrier'])
{
	$nocarrier = true;
	setcookie('nocarrier','',time()-2);
}
$po_source = $_COOKIE['source'] ? $_COOKIE['source'] : ($_GET['source'] ? $_GET['source'] : "pmd");
require_once('../database.php');
$duallogin = 1;
include_once("../vendorsecure.php");
if (!$vendorid)
	include_once("../secure.php");
require_once('inc_shipping.php');
require_once('../inc_content.php');
if($viewonly)
{
	// viewonly mode
	// get the PO entry date from the db
	$sql = "SELECT ordered FROM order_forms WHERE id = $po_id";
	$que = mysql_query($sql);
	checkDBerror($sql);
	$res = mysql_fetch_assoc($que);
	$podate = $res['ordered'];
}
$sql = "SELECT COALESCE(shipto, snapshot_user) AS userid, shipto, snapshot_user, freight_percentage, total, snapshot_form FROM order_forms WHERE ID = ".$po_id;
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
if(mysql_num_rows($query)<1)
{
	// PO doesn't exist, so we'll redirect to the queue w/ an error message
	setcookie('BoL_msg', "The PO # entered, ".$_GET['id'].", does not exist in the system.");
	header('Location: shipping.php');
}
$formid = $result['snapshot_form'];
$user_id = $result['userid'];
$snapshotuser = $result['snapshot_user'];
$freightperc = $result['freight_percentage'];
$useshipto = false;
if($result['shipto']!='' || !is_null($result['shipto'])) $useshipto = true;
if ($freightperc == "") $freightperc = 0;
$ordertotal = $result['total'];
// check if the order is ch; if so, add the service level field
$chchecksql = "SELECT servicelevel FROM ch_order WHERE po = '$po_id'";
checkdberror($chchecksql);
$chcheckque = mysql_query($chchecksql);
if(mysql_num_rows($chcheckque)>0)
{
	$chorder = true;
	$chres = mysql_fetch_assoc($chcheckque);
	$chlevel = $chres['servicelevel'];
}
else
{
	$chorder = false;
}
$origid_sql = "SELECT orig_id FROM snapshot_forms WHERE id = $formid";
$vendor_sql = "SELECT vendor FROM forms WHERE ID IN ($origid_sql)";
$vendorname_sql = "SELECT name AS vendorname FROM vendors WHERE ID in ($vendor_sql)";
$query = queryFailOnZero($vendorname_sql, "Unfortunately, a problem locating the vendor information has been encountered. Please let the system administrator know.<br />\n$vendorname_sql");
$result = mysql_fetch_assoc($query);
$vendorname = $result['vendorname'];
$sql = "SELECT name FROM snapshot_forms WHERE id = ".$formid;
$query = queryFailOnZero($sql, "Unfortunately, a problem locating the vendor information has been encountered. Please let the system administrator know.");
$result = mysql_fetch_assoc($query);
$form_name = $result['name'];
// if we have a shipping agent, get their snapshot info
if($shipping_agent && $shipping_agent != 'n')
{
	$sql = "SELECT snapshot_userid AS uid FROM shipping_agents WHERE ID = $shipping_agent";
	$que = queryFailOnZero($sql, "Unfortunately, a problem locating the shipping agent information has been encountered. Please let the system administrator know.");
	$result = mysql_fetch_assoc($que);
	$uid = $result['uid'];
	$orig_address = $user_id;
	$snapshotuser = $result['uid'];
}
else
{
	$uid = $_GET['shipto'] ? $_GET['shipto'] : $user_id;
	$snapshotuser = $uid;
	$orig_address = $uid;
}
$sql = "SELECT * from snapshot_users WHERE id = $uid";
$query = queryFailOnZero($sql, "Unfortunately, a problem locating the user information has been encountered. Please let the system administrator know.");
$snapshot_result = mysql_fetch_array($query);
// get the original user info for the tooltip
$sql = "SELECT * FROM snapshot_users WHERE id = $orig_address";
$query = queryFailOnZero($sql, "Unfortunately, a problem locating the user information has been encountered. Please let the system administrator know.");
$orig_addressinfo = mysql_fetch_assoc($query);
$sql = "SELECT ID FROM BoL_queue WHERE po = $po_id AND source = '$po_source'";
$que = queryFailOnZero($sql, "Unfortunately, a problem locating the shipping queue information has been encountered. Please let the system administrator know.");
$res = mysql_fetch_assoc($que);
$queue_id = $res['ID'];
// we need to see if the order has been totally shipped and credit-requested first before going any further
$cont = false;
$sql = "SELECT setqty, mattqty, qty, item, COALESCE(po_lineid, ID) AS lineid FROM orders WHERE po_id = ".$po_id;
$query = queryFailOnZero($sql, "Unfortunately, a problem locating the order information has been encountered. Please let the system administrator know.");
while($result = mysql_fetch_assoc($query))
{
	$totset = 0;
	$totmatt = 0;
	$totbox = 0;
	$ids_seen = array(0 => 0);
	$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
	$query2 = queryFailOnZero($sql2, "Unfortunately, a problem locating the item information has	been encountered. Please let the system administrator know.");
	$result2 = mysql_fetch_array($query2);
	// grab counts of how many of this item have already been shipped and reduce the # available by that amount
	$sq2 = "SELECT ID as itemid, setamt, mattamt, boxamt FROM BoL_items WHERE po = $po_id AND IF(type = 'cred', credit_approved != 2, TRUE) AND item = ".$result['item'];
	$que2 = mysql_query($sq2);
	checkDBerror($sq2);
	if(mysql_num_rows($que)>0)
	{
		while($res2 = mysql_fetch_assoc($que2)) {
			if (!array_search($res2['itemid'], $ids_seen)) {
				$ids_seen[] = $res2['itemid'];
				$totset += $res2['setamt'];
				$totmatt += $res2['mattamt'];
				$totbox += $res2['boxamt'];
			}
		}
	}
	if($result['setqty']-$totset!=0 || $result['mattqty']-$totmatt!=0 || $result['qty']-$totbox!=0) $cont = true;
}
if(!$cont && !$viewonly) {
	setcookie('BoL_msg', 'Order has either been completed or a credit request has been submitted which would complete the order. No items are available for this order.', time()+5);
	header("Location: shipping.php");
}
else if(!$cont && secure_is_superadmin())
{
	// order's gone out, we're in the superadmin view now, so show all ordered items
	$superadminview = true;
}

if($makeEdi)
{
	require_once('../include/edi/edi.php');
	
	require_once('../include/edi/bo_shippingedi.php');
	// this is an EDI order, let's grab some important info
	// start w/ the objects
	
	// get what info we can from the dbs
	$ediobj = new Edi();
	$ediobj->LoadFromPO($po_id);
	if(!$ediobj->mRejected)
	{
		$orderobj = $ediobj->Process();
	}
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title><?php echo $vendorname; ?> - Bill of Lading</title>
 
  <link rel="stylesheet" href="/css/styles.css" type="text/css">
<!--<object ID='WB' WIDTH=0 HEIGHT=0 CLASSID='CLSID:8856F961-340A-11D0-A96B-00C04FD705A2'></object>-->
<!--<object ID='Zebra' name='Zebra' WIDTH=0 HEIGHT=0 CLASSID='CLSID:4FF3084E-5A2F-3C1A-AA51-8842AFB15AA0'></object>-->
<!--<script src="../include/printing.js" language="javascript" type="text/javascript"></script>-->
  <script src="bol.js" language="javascript" type="text/javascript"></script>
<!--<script src="bol.vbs" language="VBScript"></script>-->
  </head><body onload="setVars();">
<?php include("../menu.php"); ?>
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" align="center" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="2">
      <h1><?php echo $vendorname; ?></h1>
	  <?php if($viewonly) { ?>
	  <span style="font-weight: bold" align="left"><?php if(!$superadminview) { ?>Open <?php } ?>Items for PO# <?php= $po_id+1000 ?></span>
	  <div style="font-weight: bold" align="right"><?php= date('m/d/Y h:ia', strtotime($podate)) ?></div>
	  <?php } else { ?>
      <small><span style="font-weight: bold;">STRAIGHT BILL OF LADING</span><br />
      <span style="font-weight: bold;">ORIGINAL - NOT NEGOTIABLE</span></small><br ><?php } ?>
      </td>
    </tr>
  </tbody>
</table>
<form id="addbol" name="addbol" action="do_addbol.php" method="post">
<?php
$cnt = 0;
foreach($_GET as $getname => $val)
{
	?><input type="hidden" name="getname<?php= $cnt ?>" value="<?php= $getname ?>">
	<input type="hidden" name="getvalue<?php= $cnt ?>" value="<?php= $val ?>"><?php
	$cnt++;
}

?><input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
<?php if($makeEdi) { ?><input type="hidden" name="edi" value="1">
<input type="hidden" name="store_number" value="<?php= $orderobj->mStoreNumber ?>">
<?php } ?>
<input type="hidden" name="queue_id" value="<?php echo $queue_id; ?>">
<?php if($viewonly) { ?><input type="hidden" name="viewonly" id="viewonly" value="1">
<?php } ?>
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" align="center" border="1" cellpadding="5" cellspacing="0">
  <tbody><?php if(!$viewonly)
{ // don't need to see this info if we're just viewing the leftover items
  ?>
    <tr>
      <td style="text-align: center;" colspan="2">
<?php
$sql = "SELECT MAX(ID) as last_id FROM BoL_forms";
$query = mysql_query($sql);
$result2 = mysql_fetch_assoc($query);
$nextBoL = $result2['last_id'] + 1001;
?>
      <hr style="width: 100%; height: 2px;" /><big style="font-weight: bold;">BOL#:&nbsp; <?php echo $nextBoL; ?>&nbsp;&nbsp;
Date:&nbsp; <?php echo date('m/d/Y'); ?>&nbsp;&nbsp; Time:&nbsp; <?php echo date('h:iA'); ?><br />
      </big>
      <hr style="width: 100%; height: 2px;" /></td>
    </tr>
    <tr valign="top">
      <td width="50%">
      <p class="text_16"><small><span style="font-weight: bold;">Name of Carrier:
      <select name="carrier_name" id="carrier_name" onChange="setCarrier();">
      <?php
    $selectShipper = true;
    if($makeEdi)
    {
    	// EDI-based orders have the shipper set in the submission, so go ahead & pre-set it to the correct one
    	if(!is_null($orderobj->mShipCode))
    	{
    		switch($EdiVendor->mTypeCode)
    		{
    			case 'WMI':
    				$selectShipper = false; // presetting the shipper, so don't give the option to select
 		   			$sql = "SELECT name FROM wm_shipcodes WHERE code = '".ltrim($orderobj->mShipCode, "0")."'";
 		   			$query = mysql_query($sql);
    				checkdberror($sql);
    				$return = mysql_fetch_assoc($query);
    				echo '<option value="'.ltrim($orderobj->mShipCode, "0").'">'.$return['name'].'</option>';
    				break;
    			case 'TVI':
    				$selectShipper = false; // presetting the shipper, so don't give the option to select
    				switch($orderobj->mShipCode)
    				{
    					case 'UPSGS':
    						$set_shipcode = 'UPSET_CG';
    						break;
    					case 'UPS1S':
    						$set_shipcode = 'UPSET_ND';
    						break;
    					case 'UPS2S':
    						$set_shipcode = 'UPSET_SE';
    						break;
    					case 'UPSG':
    						$set_shipcode = 'UPSN_CG';
    						break;
    					default:
    						$set_shipcode = $orderobj->mShipCode;
    				}
					// now get the shipcode info
					$sql = "SELECT ch_shipcodes.shipcode, ch_shipcodes.description, shipping_carriers.name, COALESCE(shipping_carriers.shortname, shipping_carriers.name)
					AS abbrev FROM shipping_carriers LEFT OUTER JOIN ch_shipcodes ON UCASE(ch_shipcodes.description) LIKE CONCAT('%', UCASE(COALESCE(shipping_carriers.shortname, shipping_carriers.name)), '%') AND 
					ch_shipcodes.selectable = '1'";
					$sql .= " WHERE ch_shipcodes.shipcode = '$set_shipcode'";
					$que = queryFailOnZero($sql, "Unfortunately there was an error while retrieving ship code information. Please let the system administrator know.");
    				$res = mysql_fetch_assoc($que);
					?><option value="<?php= $res['abbrev'] ?>"><?php= $res['name'] ?></option><?php
					break;
			}
    	}
    }
    else
    {
	    ?><option value="n/a"></option><?php
    }
    if($selectShipper)
    {
		$sql = "SELECT ch_shipcodes.shipcode, ch_shipcodes.description, shipping_carriers.name, COALESCE(shipping_carriers.shortname, shipping_carriers.name)
			AS abbrev FROM shipping_carriers LEFT OUTER JOIN ch_shipcodes ON UCASE(ch_shipcodes.description) LIKE CONCAT('%', UCASE(COALESCE(shipping_carriers.shortname, shipping_carriers.name)), '%') AND 
			ch_shipcodes.selectable = '1'";
		if(isset($set_shipcode)) $sql .= " WHERE ch_shipcodes.shipcode = '$set_shipcode'";
		$sql .= " ORDER BY shipping_carriers.name";
		$que = queryFailOnZero($sql, "Unfortunately there was an error while retrieving ship code information. Please let the system administrator know.");
		while($res = mysql_fetch_assoc($que))
		{
			if(is_null($res['shipcode']) || $res['shipcode']=='')
			{
				continue;
			}
			//echo 'prior_shipper = '.$prior_shipper.'; abbrev = '.$res['abbrev']."\n";
			if($res['abbrev'] == $prior_shipper)
			{
				continue;
			}
			else
			{
				$prior_shipper = $res['abbrev'];
			}
			if(!$selected_shipcarrier)
			{
				$selected_shipcarrier = $res['name'];
				$selected_carrierabbrev = $res['abbrev'];
				$selected_shipcode = $res['shipcode'];
			}
			$shipcodes[$res['abbrev']] = $res['shipcode'];
			?><option value="<?php= $res['abbrev'] ?>"<?php
			if(isset($carrier_name) && $carrier_name == $res['abbrev']) // sets the default
			{
				$selected_shipcarrier = $res['name'];
				$selected_carrierabbrev = $res['abbrev'];
				echo ' selected="selected"';
			} ?>><?php= $res['name'] ?></option>
		<?php
		}
	}
	?>
    </select><input type="hidden" id="carrier_abbrev" name="carrier_abbrev" value="">
    <?php
    if(isset($set_shipcode))
    {
    	// a specific shipcode is being used, so grab it and reproduce...
    	?><input type="hidden" id="shipcode" name="shipcode" value="<?php= $set_shipcode ?>">
    	<?php
    }
	if($makeEdi)
	{
		?><input type="hidden" id="orig_carrier_code" name="orig_carrier_code" value="<?php= $orderobj->mShipCode ?>">
		<?php
    }
	if(isset($shipcodes))
	{
		foreach($shipcodes as $abb => $code)
		{
			?><input type="hidden" id="<?php= $abb ?>" name="code_<?php= $abb ?>" value="<?php= $code ?>">
			<?php
		}
	}
   ?><br /><?php

   if($chorder)
   {
      // get the service level info for this order
      $sql = "SELECT shipcode as code, description FROM ch_shipcodes WHERE shipcode = '$chlevel' AND description LIKE '%$carrier_abbrev%' AND selectable = 1";
      checkdberror($sql);
      $que = mysql_query($sql);
      if(mysql_num_rows($que)>0)
      {
      	$shipcodes[] = mysql_fetch_assoc($que);
      }
   }
      ?><input type="hidden" id="service_level" name="service_level" value="<?php if($chorder && !$txtservice_level) { echo $selected_shipcode ? $selected_shipcode : $chlevel; } else if($txtservice_level) echo $txtservice_level; ?>">
      </span></small>
      </p>
      <?php
      if(isset($nocarrier) && $nocarrier)
      {
      	?><span style="color: red; font-size: 12pt; font-weight: bold">Carrier Required</span>
      	<?php
      }
      ?>
      </td>
      <td width="50%">
      <p class="text_16"><small><span style="font-weight: bold;">Tracking No:</span><input type="text" length="15" name="tracking_num"<?php
      if(isset($tracking_num) && $tracking_num!='')
      {
      	echo " value=\"$tracking_num\"";
      }
      else if($orderobj->mShipCode == '75') // 75 = Ceva, which has the tracking number preset
      {
      	echo " value=\"{$orderobj->mRetailPONumber}W\" readonly";
      }?>><br />
      <span style="font-weight: bold;">Date:</span><input type="text" length="15" name="ship_date" value="<?php echo date('n/d/Y'); ?>"></small></p>
      </td>
    </tr><?php } // end of viewonly suppressed section; want to see shipping address
	  ?>
    <tr>
      <td style="vertical-align: top;"><span id="editconsignwrap"><span id="editconsignee" title="Click here to enter custom address" onClick="editConsignee(<?php= $po_id ?>,<?php= $user_id ?>,'<?php= $po_source ?>'<?php if($_GET['shipto']) echo ',1,'.$_GET['shipto']; ?>)"><b><span title="<?php
	// populate the span title with address information
	echo 'Original Address: '.$orig_addressinfo['last_name'].'; '.$orig_addressinfo['address'].'; '.$orig_addressinfo['city'].' '.$orig_addressinfo['state'].'  '.$orig_addressinfo['zip'];
      ?>">Consignee:</span><?php if(!$viewonly) { // only need the option to select custom address if not viewonly
	  ?> (click here <?php
	  if(isset($_GET['shipto']) && $_GET['shipto']) { echo "to cancel "; } else { echo 'for '; } ?>custom address)</b></span>&nbsp;<select id="shipping_agent" name="shipping_agent" onchange="submit();"><option value="n">Click to select Shipping Agent</option>
	  <?php
	  // now we go through the shipping agent db to populate the select
	  $sql = "SELECT sa.ID, user.last_name as uname FROM shipping_agents sa INNER JOIN snapshot_users user ON sa.snapshot_userid = user.ID";
	  $que = mysql_query($sql);
	  checkdberror($sql);
	  while($result = mysql_fetch_assoc($que))
	  {
		  ?><option value="<?php= $result['ID'] ?>"<?php if(isset($shipping_agent) && $result['ID']==$shipping_agent) echo ' selected="selected"'; ?>><?php= ($result['ID']+1000) ?>: <?php= $result['uname'] ?></option>
		  <?php
	  }
		?></select><?php } // end viewonly mode
		?>
	  <br />
<small><?php echo $snapshot_result['last_name'];
if((!$useshipto && !isset($_GET['shipto'])) && !isset($shipping_agent)) echo ', '.$snapshot_result['first_name'];
if(!isset($_GET['shipto']) && !isset($shipping_agent)) echo "<strong>(".$snapshot_result['orig_id'].")</strong>";
echo "<br />".$snapshot_result['address']."<br />";
if($snapshot_result['address2']) echo $snapshot_result['address2']."<br />";
if ($snapshot_result['email']) { echo $snapshot_result['email']."<br />"; }
echo $snapshot_result['city'].", ".$snapshot_result['state'].". ".$snapshot_result['zip']."<br />PH:".$snapshot_result['phone']; ?><br />CALL 2 HRS PRIOR TO DELIVERY</small></span></span></td>
<td style="vertical-align: top;"><b>Shipper:</b><br />
<small><?php
$sql = "SELECT address, city, state, zip, phone, fax FROM snapshot_forms WHERE id = $formid";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
echo $result['address']."<br />\n".$result['city'].", ".$result['state'].". ".$result['zip']."<br />\n";
echo "PH:".$result['phone']."<br />\nFAX:".$result['fax']."</small></td>\n";
?></tr>
  </tbody>
</table>
<table style="width: 80%; margin-right: auto; margin-left: auto; text-align: left;" align="center" border="1" cellpadding="5" cellspacing="0">
  <tbody><?php if(!$viewonly) { // only need to see the PO header if we're adding a new one
  ?>
    <tr>
      <td colspan="7" rowspan="1" style="vertical-align: top;">
      <hr style="width: 100%; height: 2px;">
      <div style="text-align: center;"><big><b>PO#: <?php   // figure if a PO suffix needs to be added
$sql = "SELECT ID, po_suffix FROM BoL_forms WHERE po = $po_id"; // get all suffixes used so far: single POs first
$que = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_assoc($que)) {
	if (!is_null($result['po_suffix']))
	{
		$suffixes_bols[] = $result['ID'];
		$suffixes[] = $result['po_suffix'];
	}
}
if(isset($suffixes_bols) && $suffixes_bols) $suff_bols = implode(',', $suffixes_bols);
$sql = "SELECT po_suffix FROM BoL_items WHERE po = $po_id";
if(isset($suff_bols) && $suff_bols) $sql .= " AND bol_id NOT IN ($suff_bols)";
$que = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_assoc($que))
{
	if(!is_null($result['po_suffix']))
	{
		$suffixes[] = $result['po_suffix'];
	}
}
// get all the current suffixes for this base PO # and throw them into an array
// if there's currently suffixes, display the last entered + 1; otherwise, a null field
if(isset($suffixes) && $suffixes) {
	sort($suffixes);
	$suff = array_pop($suffixes)+1;
} else if(mysql_num_rows($que)) {
	$suff = "0";
} else {
	$suff = "";
}
echo $po_id + 1000;
if($suff!="" && $suff!='0') {
	echo '<input type="hidden" name="po_suffix" value="'.$suff.'">'.chr($suff);
} else {
	echo '<input type="hidden" name="po_suffix" value="">';
}?>
&nbsp;&nbsp;&nbsp;Date: <?php
$sql = "SELECT ordered, snapshot_form FROM order_forms WHERE ID = ".$po_id;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
$orderdatetime = $result['ordered'];
$snapshot_form = $result['snapshot_form'];
echo date('m/d/Y', strtotime($orderdatetime)).'&nbsp;&nbsp;&nbsp;Time: '.date('h:iA', strtotime($orderdatetime)); ?></b></big></div>
      </td>
    </tr>
<?php } // end viewonly suppressed section
?>
<tr>
      <td colspan="2" class="orderTH"><small>Item</small></td>
      <td colspan="1" class="orderTH"><small>Set</small></td>
      <td colspan="1" class="orderTH"><small>Matt</small></td>
      <td colspan="1" class="orderTH"><small>Box</small></td>
      <td colspan="1" class="orderTH"><small>Class</small></td>
      <td colspan="1" class="orderTH" align="right"><small>Total Pcs.</small></td>
    </tr>
<?php
$row = 0;
static $totalcontainers = 0;
$sql = "SELECT setqty, mattqty, qty, item, COALESCE(po_lineid, ID) AS lineid FROM orders WHERE po_id = ".$po_id;
$query = mysql_query($sql);
while($result = mysql_fetch_assoc($query))
{
	$sql2 = "SELECT partno, description, weight, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
	$query2 = mysql_query($sql2);
	$result2 = mysql_fetch_array($query2);
	// grab counts of how many of this item have already been shipped and reduce the # available by that amount
	// first, see if there's been a BoL generated for this PO
	$sq = "SELECT ID FROM BoL_forms WHERE po = $po_id AND type = 'bol'"; // BOLS only at first
	$que = mysql_query($sq);
	$shippedset = 0;
	$shippedmatt = 0;
	$shippedbox = 0;
	$sq2 = "SELECT SUM(setamt) as shippedset, SUM(mattamt) as shippedmatt, SUM(boxamt) as shippedbox FROM BoL_items WHERE po = $po_id AND type = 'bol' AND item = ".$result['item'];
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['shippedset'];
	$shippedmatt += $res2['shippedmatt'];
	$shippedbox += $res2['shippedbox'];
	$sq2 = "SELECT SUM(setamt) as creditset, SUM(mattamt) as creditmatt, SUM(boxamt) as creditbox FROM BoL_items WHERE po = $po_id AND type = 'cred' AND credit_approved = 1 AND item = ".$result['item'];
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['creditset'];
	$shippedmatt += $res2['creditmatt'];
	$shippedbox += $res2['creditbox'];

	if($result['setqty']-$shippedset==0 && $result['mattqty']-$shippedmatt==0 && $result['qty']-$shippedbox==0 && !$superadminview) continue;  // move on to the next row if they are all 0 amounts
	$row++;
	if(strlen($row)==2) { $row_compare = $row; } else { $row_compare = "0".$row; }
// now we make the rows
?>
<tr valign="top">
	<input type="hidden" name="lineid_<?php= $row_compare ?>" value="<?php= $result['lineid'] ?>">
	<input type="hidden" id="weight_<?php= $row_compare ?>" name="weight_<?php= $row_compare ?>" value="<?php= $result2['weight'] ?>"><input type="hidden" id="linetotalweight_<?php= $row_compare ?>" name="linetotalweight_<?php= $row_compare ?>" value="0"><input type="hidden" id="setamt_<?php= $row_compare ?>" name="setamt_<?php= $row_compare ?>" value="<?php= $result2['setamt'] ?>">
	<td class="orderTD"><small><?php echo $result2['partno']; ?></small><input type="hidden" name="item<?php echo $row_compare.'" value="'.$result['item'].'">'; ?></td>
	<td class="orderTD"><small><?php echo $result2['description']; ?></small></td>
<?php
if($result['setqty']>0 && ($result['setqty']-$shippedset!=0 || $superadminview)) {
	if(!$viewonly) {
	?><td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="setqty<?php= $row_compare ?>" id="set_<?php= $row_compare ?>" onchange="recalcrow('<?php= $row_compare ?>');">
	<?php
	for($i=($result['setqty']-$shippedset); $i>=0; $i--) {
		echo "\t<option value=\"$i\">$i</option>\n";
		if($i==($result['setqty']-$shippedset)) echo "\t<option value=\"blank\">---</option>\n";
	}
	echo "</select>\n";
	}
	else
	{
		// viewonly mode
		?><td colspan="1" class="orderTD"><small><?php= $superadminview ? $result['setqty'] : $result['setqty']-$shippedset ?><input type="hidden" id="set_<?php= $row_compare ?>" name="setqty<?php= $row_compare ?>" value="<?php= $superadminview ? $result['setqty'] : $result['setqty']-$shippedset ?>"><?php
	}
} else { 
	echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"set_$row_compare\" name=\"setqty$row_compare\" value=\"0\">0";
}
?>
</small></td>
<?php
if($result['mattqty']>0 && ($result['mattqty']-$shippedmatt!=0 || $superadminview)) {
	if(!$viewonly) {
	?><td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="mattqty<?php= $row_compare ?>" id="matt_<?php= $row_compare ?>" onchange="recalcrow('<?php= $row_compare ?>');">
	<?php
	for($i=($result['mattqty']-$shippedmatt); $i>=0; $i--) {
		echo "\t<option value=\"$i\">$i</option>\n";
		if($i==($result['mattqty']-$shippedmatt)) echo "\t<option value=\"blank\">---</option>\n";
	}
	echo "</select>\n";
	}
	else
	{
		// viewonly mode
		?><td colspan="1" class="orderTD"><small><?php= $superadminview ? $result['mattqty'] : $result['mattqty']-$shippedmatt ?><input type="hidden" id="matt_<?php= $row_compare ?>" name="mattqty<?php= $row_compare ?>" value="<?php= $superadminview ? $result['mattqty'] : $result['mattqty']-$shippedmatt ?>"><?php
	}
} else {
	echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"matt_$row_compare\" name=\"mattqty$row_compare\" value=\"0\">0"; }
?>
</small></td>
<?php
if($result['qty']>0 && ($result['qty']-$shippedbox!=0 || $superadminview)) {
	if(!$viewonly)
	{
		?><td colspan="1" class="orderTD" style="background-color: yellow;"><small>
		<select name="boxqty<?php= $row_compare ?>" id="box_<?php= $row_compare ?>" onchange="recalcrow('<?php= $row_compare ?>');">
		<?php
		for($i=($result['qty']-$shippedbox); $i>=0; $i--)
		{
			echo "\t<option value=\"$i\">$i</option>\n";
			if($i==($result['qty']-$shippedbox)) echo "\t<option value=\"blank\">---</option>\n";
		}
		echo "</select>\n";
	}
	else
	{
		// viewonly mode
		?><td colspan="1" class="orderTD"><small><?php= $superadminview ? $result['qty'] : $result['qty']-$shippedbox ?><input type="hidden" id="box_<?php= $row_compare ?>" name="boxqty<?php= $row_compare ?>" value="<?php= $superadminview ? $result['qty'] : $result['qty']-$shippedbox ?>"><?php
	}
} else {
	echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"box_$row_compare\" name=\"boxqty$row_compare\" value=\"0\">0";
}
?>
</small></td>
<td class="orderTD" align="right"><small><?php if($viewonly) { echo '&nbsp;'; } else { ?><input type="text" size="15" name="class<?php echo $row_compare; ?>"><?php } ?></small></td>
<td class="orderTD" align="right"><div id="linetotalpcs<?php echo $row_compare; ?>" name="linetotalpcs<?php echo $row_compare; ?>"><?php

static $formtotal;
$formtotal += (($result['setqty']-$shippedset)*$result2['setamt'])+($result['mattqty']-$shippedmatt)+($result['qty']-$shippedbox);
echo (($result['setqty']-$shippedset)*$result2['setamt'])+($result['mattqty']-$shippedmatt)+($result['qty']-$shippedbox);
?></div></td>
</tr>
<?php
}
echo '<input type="hidden" id="totalrows" name="rows" value="'.$row.'">'."\n";
if(!$viewonly)
{
?><tr>
<td colspan="5" align="left">Comment: <input type="text" name="po_comment" size="40"></td>
<td colspan="2" align="right">Total Pieces:&nbsp;&nbsp<span style="font-weight: bold;" name="totalpcs" id="totalpcs"><?php echo $formtotal; ?></span><br /><small>Weight:&nbsp;<span id="disp_weight">0</span> lbs.<input type="hidden" id="weight" name="weight" value="0"></small></td>
</tr>
<tr align="right">
<td colspan="7" style="vertical-align: top;" align="right">Prepaid Amount: <div id="prepaid"><?php
$sql = "SELECT SUM(freight) as freightsum FROM BoL_forms WHERE po = ".$po_id;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
if($result['freightsum']>($ordertotal - ($ordertotal/(1+($freightperc/100)))))
{
	$prepaidbalance = 0;
	echo "Prepaid Amount Met";
}
else
{
	$prepaidbalance = number_format(number_format(($ordertotal - ($ordertotal/(1+($freightperc/100)))), 2)-$result['freightsum'], 2);
	echo "$".$prepaidbalance;
}
?></div><br /><input type="hidden" name="freightchgs"><?php if($chorder || (isset($_GET['shipto']) && $_GET['shipto']) || (isset($shipping_agent) && $shipping_agent)) { ?><input type="hidden" name="snapshotuser" value="<?php echo ($_GET['shipto'] ? $_GET['shipto'] : $snapshotuser); ?>"><?php } ?><input type="hidden" name="dbuser" value="<?php= $user_id ?>"><input type="hidden" name="po_source" value="<?php echo $po_num_source; ?>"><input type="hidden" name="prepaidbalance" value="<?php echo $prepaidbalance; ?>"><input value="Generate Bill of Lading" type="submit" name="doit">
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
?><tr><td colspan="8" align="center" id="buttonzone"><button type="button" onclick="javascript:printPickTicket(<?php= $po_id ?>)">Print Order/PickTix</button><button type="button" onclick="javascript:printPickLabel('<?php= $po_id ?>','<?php= $labelname ?>\n<?php= $labeladdress ?>\n<?php= $labelcitystzip ?>')">Print Labels</button><button type="button" onclick="javascript:setLabelPrinter();">Configure Label Printer</button><br />
<button type="button" onclick="javascript:returnToQueue()">Return to Queue</button></td></tr><?php } ?>
  </tbody>
</table>
</form>
<div id="customaddyformhere"></div>
</body></html>