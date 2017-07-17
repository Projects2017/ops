<?php
// showcredit.php
// displays a list of BOLs & credit requests for an order for the user to choose

require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
if(!isset($_GET)) {
	die('This page requires data to be sent via GET.');
}
if(!$_GET['id']) {
  die("PO ID must be sent via GET.");
} else {
  $po_id = $_GET['id'] - 1000;
  $print_po_id = $po_id;
}
// if 'claim' is set, it's from OOR; allow for adding another credit request
$_GET['claim'] ?	$from_OOR = true : $from_OOR = false; 
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>RFE Shipping Credit Requests for PO # <?php echo $print_po_id; ?></title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
</head>
<body>
<?php include("../menu.php"); ?>
<p align="center" style="font-size: 24px; font-weight: bold;">Royal Furniture Express Shipping<br />Credit Requests for PO # <?php echo $print_po_id; ?></p>
<?php if (secure_is_admin()) { ?>
  <form name="creditreq" action="addcredit.php" method="get"><input type="hidden" name="id" value="<?php echo $po_id; ?>">
<?php } ?>
<table width="80%" align="center" cellpadding="2" cellspacing="2" border="0">
<?php if($_COOKIE['credit_msg']) {
echo '<tr><td colspan="7" align="center" class="text_12" style="font-size: 14px; color: red">'.$_COOKIE['credit_msg']."</td></tr>\n";
} ?>
<tr><td align="center" colspan="5" class="text_12" style="font-size: 14px"><a href="shipping.php">Back to Queue</a></td></tr>
<tr>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Request ID</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Sets</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Matts</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Boxes</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Status</th>
</tr>
<?php
$i = 0;
$sql = "SELECT ID, setamt, mattamt, boxamt, credit_approved FROM BoL_forms WHERE po = ".$po_id." AND type = 'cred'";
$query = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_assoc($query)) {
  $credit_status = $result['credit_approved'];
  $i++;
  ?>
<tr>
  <td align="center" class="text_12" style="font-size: 14px"><a href="viewcredit.php?id=<?php echo $result['ID']; ?>"><?php echo $result['ID']+1000; ?></a></td>
<?php if (secure_is_admin()) { ?>
  <input type="hidden" name="<?php echo $i; ?>" value="<?php echo $result['ID']; ?>"> <?php } ?>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['setamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['mattamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['boxamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php
  switch($result['credit_approved']) {
    case 0:
      echo '<a href="viewcredit.php?id='.$result['ID'].'">Pending</a>';
      break;
    case 1:
      echo '<a href="viewcredit.php?id='.$result['ID'].'">Approved</a>';
      break;
    case 2:
      echo '<a href="viewcredit.php?id='.$result['ID'].'">Denied</a>';
      break;
   } ?>
</td></tr>
<?php } ?>
</table>
<?php if (secure_is_admin()) { 
 echo "<tr>";
 if($from_OOR) echo '<input type="submit" name="submit" value="Add Credit Request">';
 echo "</form>"; } ?>
</body>
</html>