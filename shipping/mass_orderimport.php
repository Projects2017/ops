<?php
// do mass import of orders to OOR tables
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require_once('../form.inc.php');
$sql = "SELECT DISTINCT po, bol_id FROM BoL_items WHERE type = 'bol'";
$que = mysql_query($sql);
while($result = mysql_fetch_assoc($que))
{
	$pos[] = $result['po'];
	$bols[] = $result['bol_id'];
}
//print_r($pos);
//print_r($bols);
for($i=0; $i<count($bols); $i++)
{
	$sql = "SELECT trackingnum, carrier, shipdate FROM BoL_forms WHERE ID = {$bols[$i]}";
//	echo "$sql<br />\n";
	$qu = mysql_query($sql);
	while($res = mysql_fetch_assoc($qu))
	{
		if($res['trackingnum']=='') continue;
		$po[] = $pos[$i];
		$tracking[] = $res['trackingnum'];
		$carrier[] = $res['carrier'];
		$shipdate[] = $res['shipdate'];
	}
}
//print_r($po);
//print_r($tracking);
//print_r($carrier);
//print_r($shipdate);
//die();
for($i=0; $i<count($po); $i++)
{
	$items = formdata('order',0, array('po' => $po[$i]+1000));
	foreach ($items as $id => $key) {
		if($key['tracking'] != '')
		{
			$pre_tracking = $key['tracking']."; ";
		}
		else
		{
			$pre_tracking = "";
		}
		if($key['carrier'] != '')
		{
			$pre_carrier = $key['carrier']."; ";
		}
		else
		{
			$pre_carrier = "";
		}
		formupdate('order',$key['id'], array('tracking' => $pre_tracking."BOL #: ".$tracking[$i], 'carrier' => $pre_carrier."BOL Carrier: ".$carrier[$i], 'shipdate' => $shipdate[$i]));
	}
}
echo "Completed successfully.";
?>