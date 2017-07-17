<?php
// makeshippingedi.php
// script to generate shipment EDI
// requires GET'd id, which = bol_id
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');
$bol_id = $_GET['id'];
// now get the po id
// should be single
$sql = "SELECT po FROM BoL_items WHERE bol_id = $bol_id";
$que = mysql_query($sql);
checkDBerror($sql);
while($ret = mysql_fetch_row($que))
{
	$recs[] = $ret;
}
$thepo = array_unique($recs);
if(count($thepo)>1) die('There is an error. Cannot have > 1 PO per EDI.');
if(count($thepo)==1) $thispo = $thepo[0];
$thepo = $thispo[0];

makeShippingEdi($thepo, $bol_id, false); // false = don't print packing slip
header('Location: viewbol.php?id='.$bol_id);
// should be done
exit();
?>