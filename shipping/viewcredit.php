<?php
// viewcredit.php
// script to view a credit request for potential processing (if admin)
if(!isset($_GET)) {
	die('This page requires data to be sent via GET.');
}
$cr_id = $_GET['id'];
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
$sql = "SELECT po, comment, credit_approved, credit_po, createdate FROM BoL_forms WHERE ID = $cr_id";
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
$po_id = $result['po'];
$cr_comment = $result['comment'];
$credit_po = $result['credit_po'];
$print_po_id = $po_id + 1000;
$createdate = $result['createdate'];
$approval_status = $result['credit_approved'];
if($approval_status=='1' && $_COOKIE['printcredit']=='yes') {
  $print_form = true;
} else { $print_form = false; }
setcookie('printcredit','',time()-5);
$print_cr_id = $cr_id + 1000;
$sql = "SELECT po, credit_approved, createdate FROM BoL_forms WHERE ID = $cr_id";
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
$po_id = $result['po'];
$print_po_id = $po_id + 1000;
$createdate = $result['createdate'];
$approval_status = $result['credit_approved'];
$print_cr_id = $cr_id + 1000;
$sql = "SELECT snapshot_user, form, user, customer, shipto, freight_percentage, total, snapshot_form FROM order_forms WHERE ID = ".$po_id;
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
$user_id = $result['snapshot_user'];
$formid = $result['snapshot_form'];
$customerid = $result['customer'];
$shiptoid = $result['shipto'];
$freightperc = $result['freight_percentage'];
if ($freightperc == "") $freightperc = 0;
$ordertotal = $result['total'];
// figure out how many table columns will be required; only additional ones if a customer and/or shipto is referenced
$tablecols = 2;
if($customerid) $tablecols++;
if($shiptoid) $tablecols++;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>Retail Service Systems Shipping Credit Request</title>
 
  <link rel="stylesheet" href="/css/styles.css" type="text/css">
  <script src="credit.js" language="javascript" type="text/javascript"></script>
  </head><body<?php if($print_form) echo ' onload="javascript:printCredit();"'; ?>>
<div id="hidemenu"><?php include("../menu.php"); ?></div>
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="2">
      <h1>Retail Service Systems</h1>
      <span style="font-weight: bold;">SHIPPING CREDIT REQUEST<br />ORIGINAL - NOT NEGOTIABLE</span>
      </td>
    </tr>
  </tbody>
</table>
<?php if(secure_is_admin() && $approval_status==0) { ?> <form name="approvecredit" action="approvecredit.php" method="post">
<input type="hidden" name="cr_id" value="<?php echo $cr_id; ?>">  <?php } ?>
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td style="text-align: center;" colspan="<?php echo $tablecols; ?>">
      <hr style="width: 100%; height: 2px;" /><big style="font-weight: bold;"><span style="white-space: nowrap">CREDIT REQUEST #:&nbsp; <?php echo $print_cr_id; ?></span>&nbsp;&nbsp;
<span style="white-space: nowrap">Date:&nbsp; <?php echo date('m/d/Y', strtotime($createdate)); ?></span>&nbsp;&nbsp; <span style="white-space: nowrap">Time:&nbsp; <?php echo date('h:iA', strtotime($createdate)); ?></span><br />
      </big>
      <hr style="width: 100%; height: 2px;" /></td>
    </tr>
    <tr>
      <td style="vertical-align: top;"><b>Consignee:</b><br />
<?php
$sql = "SELECT * from snapshot_users WHERE id = ".$user_id;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
?>
<small><?php echo $result['last_name'].', '.$result['first_name']." <strong>(".$result['orig_id'].")</strong><br />".$result['address']."<br />";
if($result['address2']) echo $result['address2']."<br />\n";
echo $result['city'].", ".$result['state'].". ".$result['zip']."<br />";
if ($result['email']) echo $result['email']."<br />\n";
echo "PH:".$result['phone']; ?></small></td>
<?php if($customerid) { ?>
<td style="vertical-align: top;"><b>Customer:</b><br />
<?php
$sql = "SELECT * from snapshot_users WHERE id = ".$customerid;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
?>
<small><?php echo $result['last_name'];
if($result['first_name']) echo ', '.$result['first_name'];
if($result['orig_id']) echo " <strong>(".$result['orig_id'].")</strong>";
echo "<br />".$result['address']."<br />";
if($result['address2']) echo $result['address2']."<br />";
echo $result['city'].", ".$result['state'].". ".$result['zip']."<br />";
if ($result['email']) echo $result['email']."<br />\n";
echo "PH:".$result['phone']; ?></small></td>
<?php }
if($shiptoid) { ?>
<td style="vertical-align: top;"><b>Ship To:</b><br />
<?php
$sql = "SELECT * from snapshot_users WHERE id = ".$shiptoid;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
?>
<small><?php echo $result['last_name'];
if($result['first_name']) echo ', '.$result['first_name'];
if($result['orig_id']) echo " <strong>(".$result['orig_id'].")</strong>";
echo "<br />".$result['address']."<br />";
if($result['address2']) echo $result['address2']."<br />";
echo $result['city'].", ".$result['state'].". ".$result['zip']."<br />";
if ($result['email']) echo $result['email']."<br />\n";
echo "PH:".$result['phone']; ?></small></td>
<?php } ?>
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
<table style="width: 80%; margin-right: auto; margin-left: auto; text-align: left;" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
<td colspan="8" rowspan="1" style="vertical-align: top;">
      <hr style="width: 100%; height: 2px;">
      <div style="text-align: center;"><big><b>PO#: <?php echo $po_id + 1000; ?>
&nbsp;&nbsp;&nbsp;Date: <?php
$sql = "SELECT ordered, snapshot_form FROM order_forms WHERE ID = ".$po_id;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_row($query);
$orderdatetime = $result[0];
$snapshot_form = $result[1];
echo date('m/d/Y', strtotime($orderdatetime)).'&nbsp;&nbsp;&nbsp;Time: '.date('h:iA', strtotime($orderdatetime)); ?></b></big></div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="orderTH"><small>Item</small></td>
      <td colspan="1" class="orderTH"><small>Set</small></td>
      <td colspan="1" class="orderTH"><small>Matt</small></td>
      <td colspan="1" class="orderTH"><small>Box</small></td>
      <td class="orderTH"><small>Reason for Credit</small></td>
      <td colspan="1" class="orderTH" align="right"><small>Total Pcs.</small></td>
      <td colspan="1" class="orderTH" align="right"><small>Approved</small></td>
    </tr>
<?php
$row = 0;
$sql = "SELECT setqty, mattqty, qty, item FROM orders WHERE po_id = ".$po_id;
$query = mysql_query($sql);
while($result = mysql_fetch_assoc($query))
{
  $sql2 = "SELECT setamt, mattamt, boxamt, credit_reason, credit_approved FROM BoL_items WHERE item = ".$result['item']." AND bol_id = ".$cr_id;
  $query2 = mysql_query($sql2);
  while($result2 = mysql_fetch_assoc($query2))
    {
    	$sql3 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
    	$query3 = mysql_query($sql3);
    	$result3 = mysql_fetch_array($query3);
    	$setqty_match = $result3['setqty'] == 0 ? 1 : $result3['setqty'];
	  ?><tr valign="top">
      <td class="orderTD"><small><?php echo $result3['partno']; ?></small><input type="hidden" name="item<?php echo $row.'" value="'.$result['item'].'">'; ?></td>
      <td class="orderTD"><small><?php echo $result3['description']; ?></small></td>
      <td colspan="1" class="orderTD"><small><input type="hidden" name="setqty<?php echo $row; ?>" value="<?php echo $result2['setamt']; ?>"><?php echo $result2['setamt']; ?></small></td>
      <td colspan="1" class="orderTD"><small><input type="hidden" name="mattqty<?php echo $row; ?>" value="<?php echo $result2['mattamt']; ?>"><?php echo $result2['mattamt']; ?></small></td>
      <td colspan="1" class="orderTD"><small><input type="hidden" name="boxqty<?php echo $row; ?>" value="<?php echo $result2['boxamt']; ?>"><?php echo $result2['boxamt']; ?></small></td>
      <td colspan="1" class="orderTD"><small><input type="hidden" name="crreason<?php echo $row; ?>" value="<?php echo stripslashes($result2['credit_reason']); ?>"><?php if(!$result2['credit_reason']||$result2['credit_reason']=="") { echo 'N/a'; } else { echo stripslashes($result2['credit_reason']); } ?></small></td>
      <td colspan="1" class="orderTD"><small><input type="hidden" name="totpcs<?php echo $row; ?>" value="<?php echo ($result2['setamt']*$setqty_match)+$result2['mattamt']+$result2['boxamt']; ?>"><?php echo ($result2['setamt']*$setqty_match)+$result2['mattamt']+$result2['boxamt']; ?></small></td>
      <td colspan="1" class="orderTD"><small><?php if (secure_is_admin()) { ?><input type="checkbox" name="yesno<?php echo $row; ?>"<?php if ($approval_status!=0) { echo ' disabled'; }
if($approval_status==1 && $result2['credit_approved']==1) {
  echo ' checked';
} else if($approval_status==0) { echo ' checked'; } ?>><?php } else {
      if($approval_status==1 && $result2['credit_approved']==1) { echo 'Approved'; } else if($approval_status==0) { echo 'Pending'; } else { echo 'Denied'; }
   }
      static $formtotal;
      $formtotal += ($result2['setamt']*$setqty_match)+$result2['mattamt']+$result2['boxamt'];
      $row++;
?></small></td></tr>
<?php
  }
}
?><tr>
	  <?php if($cr_comment!="") { ?><td colspan="6" align="left" class="orderTH"><small>Comment</small></td><td colspan="2" rowspan="2" <?php 
	  } else {
	  	?><td colspan="8" <?php 
	  } ?>align="right"><input type="hidden" name="rows" value="<?php echo $row; ?>"><span class="orderTD">Total Pieces:&nbsp;&nbsp<span style="font-weight: bold;" id="totalpcs"><?php echo $formtotal; ?></span></span><?php if(secure_is_admin()) echo '<br /><span class="orderTD">Credit PO #:  <span style="font-weight: bold"><a href="/admin/viewpo.php?po='.($credit_po+1000).'">'.($credit_po+1000);
	  ?>
	  </td>
    </tr>
  <tr>
    <td colspan="6" align="left" class="orderTD"><small><?php echo stripslashes($cr_comment); ?></small></td>
  </tr>
  </tbody>
</table>
<table width="80%" align="center" border="0">
  <tr>
    <td align="center" class="noprint"><?php if(secure_is_admin() && $approval_status==0) { ?><input type="submit" name="approve" value="Approve">&nbsp;&nbsp;<input type="submit" name="deny" value="Deny">&nbsp;&nbsp;<?php } else { ?><button onclick="window.location='./shipping.php'">Return to Queue</button> <?php } ?></td>
  </tr>
</table>
<?php
 if (secure_is_admin() && $approval_status==0) { ?></form><?php } ?>
</body></html>