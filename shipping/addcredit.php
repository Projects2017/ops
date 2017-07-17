<?php
// addcredit.php
// script to add a credit request to the BOL system
if(!isset($_GET)) {
	die('This page requires data to be sent via GET.');
}
$po_id = ($_GET['id']-1000);
$po_source = $_GET['source'] ? $_GET['source'] : "pmd";
$fromEdi = isset($_GET['edi']) && $_GET['edi'] == 1 ? true : false;
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
$sql = "SELECT orig_po, po FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
$query = mysql_query($sql);
$res = mysql_fetch_assoc($query);
if(is_null($res['orig_po'])) { $order_id = $res['po']; $orig_source = "po"; } else { $order_id = $res['orig_po']; $orig_source = "orig_po";}
$sql = "SELECT ID, $orig_source as po_num FROM BoL_queue WHERE $orig_source = $order_id AND source = '$po_source'";
$que = mysql_query($sql);
$res = mysql_fetch_assoc($que);
$queue_id = $res['ID'];
$orig_po = $res['po_num'];
$queue_po = $order_id;
// we need to see if the order has been totally shipped and credit-requested first before going any further
$cont = false;
$sql = "SELECT setqty, mattqty, qty, item FROM orders WHERE po_id = ".$order_id;
$query = mysql_query($sql);
while($result = mysql_fetch_assoc($query))
{
  $totset = 0;
  $totmatt = 0;
  $totbox = 0;
	$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
	$query2 = mysql_query($sql2);
	$result2 = mysql_fetch_array($query2);
  // grab counts of how many of this item have already been shipped and reduce the # available by that amount
    $sq2 = "SELECT ID as itemid, setamt, mattamt, boxamt FROM BoL_items WHERE po = $order_id AND IF(type = 'cred', credit_approved != 2, TRUE) AND item = ".$result['item'];
    $que2 = mysql_query($sq2);
    while($res2 = mysql_fetch_assoc($que2)) {
    $totset += $res2['setamt'];
    $totmatt += $res2['mattamt'];
    $totbox += $res2['boxamt'];
  }
  if($result['setqty']-$totset!=0 || $result['mattqty']-$totmatt!=0 || $result['qty']-$totbox!=0) $cont = true;
}
if(!$cont) {
  setcookie('BoL_msg', 'Order has either been completed or a credit request has been submitted which would complete the order. No items are available for this order.', time()+5);
  header("Location: shipping.php");
}
// get some basic order information
$sql = "SELECT snapshot_user, form, user, customer, shipto, freight_percentage, total, snapshot_form FROM order_forms WHERE ID = ".$order_id;
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
$customerid = $result['customer'];
$shiptoid = $result['shipto'];
$user_id = $result['snapshot_user'];
$formid = $result['snapshot_form'];
$freightperc = $result['freight_percentage'];
if ($freightperc == "") $freightperc = 0;
$ordertotal = $result['total'];
// figure out how many table columns will be required; only additional ones if a customer and/or shipto is referenced
$tablecols = 2;
if($customerid) $tablecols++;
if($shiptoid) $tablecols++;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>RSS Shipping Credit Request</title>
 
  <link rel="stylesheet" href="/css/styles.css" type="text/css">
  <script src="credit.js" language="javascript" type="text/javascript"></script>
  </head><body>
<?php include("../menu.php"); ?>
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
<form name="do_addcredit" action="do_addcredit.php" method="post">
<input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
<input type="hidden" name="po_source" value="<?php echo $po_source; ?>">
<table style="width: 80%; text-align: left; margin-left: auto; margin-right: auto;" border="1" cellpadding="5" cellspacing="0">
  <tbody>
    <tr>
      <td style="text-align: center;" colspan="<?php echo $tablecols; ?>">
<?php
$sql = "SELECT MAX(ID) as last_id FROM BoL_forms";
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);
$nextCredit = $result['last_id'] + 1001;
?>
      <hr style="width: 100%; height: 2px;" /><big style="font-weight: bold;">CREDIT REQUEST #:&nbsp; <?php echo $nextCredit; ?>&nbsp;&nbsp;
Date:&nbsp; <?php echo date('m/d/Y'); ?>&nbsp;&nbsp; Time:&nbsp; <?php echo date('h:iA'); ?><br />
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
echo "PH:".$result['phone']; 

?></small></td>
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
      <td colspan="7" rowspan="1" style="vertical-align: top;">
      <hr style="width: 100%; height: 2px;">
      <div style="text-align: center;"><big><b>PO#: <?php echo ($orig_po + 1000); ?>
&nbsp;&nbsp;&nbsp;Date: <?php
$sql = "SELECT ordered, snapshot_form FROM order_forms WHERE ID = ".$order_id;
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_assoc($query);
$orderdatetime = $result['ordered'];
$snapshot_form = $result['snapshot_form'];
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
    </tr>
<?php
// find out if we're dealing with a ch order; if so, we'll use the drop-down credit choices
$sql = "SELECT po FROM ch_order WHERE po = '$orig_po'";
checkdberror($sql);
$que = mysql_query($sql);
if(mysql_num_rows($que)>0)
{
	// we're dealing with a ch order, so prep the arrays of cancel reasons & cancel reason codes
	$chorder = true; // boolean for simplicity
	$cancelcodes = Array('merchant_request','info_missing','out_of_stock','discontinued');
	$cancelreason = Array("Cancelled at Merchant's Request",'Order Info Missing','Out of Stock','Product Has Been Discontinued');
}
$row = 0;
$sql = "SELECT setqty, mattqty, qty, item FROM orders WHERE po_id = ".$order_id;
$query = mysql_query($sql);
while($result = mysql_fetch_assoc($query))
{
$totset = 0;
$totmatt = 0;
$totbox = 0;
	$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
	$query2 = mysql_query($sql2);
	$result2 = mysql_fetch_array($query2);
  // grab counts of how many of this item have already been shipped and reduce the # available by that amount
    $sq2 = "SELECT ID as itemid, setamt, mattamt, boxamt FROM BoL_items WHERE po = $order_id AND IF(type = 'cred', credit_approved != 2, TRUE) AND item = ".$result['item'];
    $que2 = mysql_query($sq2);
    while($res2 = mysql_fetch_assoc($que2)) {
    $totset += $res2['setamt'];
    $totmatt += $res2['mattamt'];
    $totbox += $res2['boxamt'];
    }
  if($result['setqty']-$totset==0 && $result['mattqty']-$totmatt==0 && $result['qty']-$totbox==0) continue;
  $row++;
  // now we make the rows
  if(strlen($row)<2) { $row_compare = "0".$row; } else { $row_compare = $row; }
  ?>
	<tr valign="top">
      <td class="orderTD"><small><?php echo $result2['partno']; ?></small><input type="hidden" name="item<?php echo $row_compare.'" value="'.$result['item'].'">'; ?></td>
      <td class="orderTD"><small><?php echo $result2['description']; ?></small></td>
      <?php
      if($result['setqty']>0 && $result['setqty']-$totset!=0) {
        echo '<td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="setqty'.$row_compare.'" id="set_'.$row.'" onchange="recalcrow('.$row.','.$result2['setamt'].');">'."\n";
        for($i=0; $i<=($result['setqty']-$totset); $i++) {
          echo "\t<option value=\"$i\">$i</option>\n";
          }
        echo "</select>\n";
      } else echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" name=\"setqty$row_compare\" id=\"set_$row\" value=\"0\">0"; ?></small></td>
      <?php
      if($result['mattqty']>0 && $result['mattqty']-$totmatt!=0) {
        echo '<td colspan="1" class="orderTD" style="background-color: yellow;"><small><select name="mattqty'.$row_compare.'" id="matt_'.$row.'" onchange="recalcrow('.$row.','.$result2['setamt'].');">'."\n";
        for($i=0; $i<=($result['mattqty']-$totmatt); $i++) {
          echo "\t<option value=\"$i\">$i</option>\n";
          }
        echo "</select>\n";
      } else echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" name=\"mattqty$row_compare\" id=\"matt_$row\" value=\"0\">0"; ?></small></td>
      <?php
      if($result['qty']>0 && $result['qty']-$totbox!=0) {
        echo '<td class="orderTD" style="background-color: yellow;"><small><select name="boxqty'.$row_compare.'" id="box_'.$row.'" onchange="recalcrow('.$row.','.$result2['setamt'].');">'."\n";
        for($i=0; $i<=($result['qty']-$totbox); $i++) {
          echo "\t<option value=\"$i\">$i</option>\n";
          }
        echo "</select>\n";
      } else echo "<td colspan=\"1\" class=\"orderTD\"><small><input type=\"hidden\" id=\"box_$row\" name=\"boxqty$row_compare\" value=\"0\">0" ?></small></td>
      <td class="orderTD" align="left"><small><?php
      if($chorder)
      {
      	// this is a ch order, so use the dropdown selector
      	?><select name="reason<?php echo $row_compare; ?>"><?php
      	for($i=0; $i<count($cancelcodes); $i++)
      	{
      		?><option value="<?php echo $cancelcodes[$i]; ?>"><?php echo $cancelreason[$i]; ?></option>
      		<?php
      	}
      	?></select>
      	<?php
      }
      else
      {
      	?><input type="text" name="reason<?php echo $row_compare; ?>"><?php
      }
	?>
	</small></td>
	<td class="orderTD" align="right"><div id="linetotalpcs<?php echo $row; ?>" name="linetotalpcs">0</div></td>
	</tr>
<?php
$totset = 0;
$totmatt = 0;
$totbox = 0;
};
echo '<input type="hidden" name="rows" value="'.$row.'">'."\n";
?>   <tr>
	  <td colspan="5" align="left"><input type="hidden" name="po_source" value="<?php echo $orig_source; ?>">Comment: <input type="text" name="cr_comment" size="40"/></td><td colspan="2" align="right"><input type="submit" name="submit" value="Submit Credit Request"<?php if($fromEdi) echo ' onclick="return EdiCreditVerify();"'; ?>>&nbsp;&nbsp;&nbsp;&nbsp;Total Pieces:&nbsp;&nbsp<span style="font-weight: bold;" id="totalpcs">0</span></td>
    </tr>
  </tbody>
</table><?php
if($fromEdi && secure_is_admin())
{
	?><table style="width: 80%; text-align: right; margin-left: auto; margin-right: auto;" border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td>Send Credit Response File to Retailer&nbsp;<input type="checkbox" title="Check this box to have a cancellation response file sent to the retailer." name="sendedifile" onclick="EdiSendCancelVerify();" checked="checked">
			</td>
		</tr>
	</table>
<?php
} ?>
</form>
</body></html>