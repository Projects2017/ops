<?php
// add the required scripts for db & user capabilities
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require_once('../form.inc.php');
require_once('inc_postbol.php');
$bol_id = $_GET['id'];
$sql = "SELECT DISTINCT po FROM BoL_items WHERE bol_id = $bol_id"; //get the pos in this bol
checkdberror($sql);
$que = mysql_query($sql);
while($res = mysql_fetch_assoc($que))
{
	$pos[] = $res['po']; // put them into an array
}
$po = array_unique($pos); //make sure they're unique
$po_id = implode(',', $po); //and finally a string
$sql = "SELECT trackingnum, carrier, shipdate FROM BoL_forms WHERE ID = $bol_id";
checkdberror($sql);
$que = mysql_query($sql);
$result = mysql_fetch_assoc($que);
// add to the OOR if necessary
if(stristr($po_id, ',')) // if we're dealing with multiple pos, put them into an array
{
	$pos = explode(',',$po_id);
}
else
{
	$pos[0] = $po_id; // if a single po, put into an array anyway for easier iterations
}
$pos = array_unique($pos);
foreach($pos as $po_run)
{
	$items = formdata('order',0, array('po' => $po_run+1000));
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
                $new_tracking = "BOL #: ".$result['trackingnum'];
                $new_carrier = "BOL Carrier: ".$result['carrier'];
                if (strpos($pre_tracking,$new_tracking) === false) {
                    $post_tracking = $pre_tracking.$new_tracking;
                } else {
                    $post_tracking = $key['tracking'];
                }
                if (strpos($pre_carrier,$new_carrier) === false) {
                    $post_carrier = $pre_carrier.$new_carrier;
                } else {
                    $post_carrier = $key['carrier'];
                }
		formupdate('order',$key['id'], array('tracking' => $post_tracking, 'carrier' => $post_carrier, 'shipdate' => $result['shipdate']));
	}
}

// add to CH queue
addCHQueue($bol_id);
// set the oor_updated boolean in the queue to true
$sql = "UPDATE BoL_forms SET oor_updated = 1 WHERE ID = $bol_id";
$que = mysql_query($sql);
checkdberror($sql);
header('Location: viewbol.php?id='.$bol_id);
exit();
?>