<?php
/* bolfix.php
 script to fix BOL db usage problem
 BoL_forms.user_id was re-established to hold snapshot_users.ID of the shippee
 we need to go through all records and fix them
 need to go through BoL_items to allow for multi-PO orders
 select distinct po from BoL_items ; gets all PO numbers that have been delivered
select COALESCE(shipto, snapshot_user) as user_id WHERE ID in (SELECT DISTINCT po FROM BoL_items); this gets all users shipped to, kinda
(select ID, last_name FROM snapshot_users WHERE ID IN
(select COALESCE(shipto, snapshot_user) as user_id FROM order_forms WHERE ID in (SELECT DISTINCT po FROM BoL_items)))
UNION
(select ID, last_name FROM snapshot_users WHERE ID IN
(SELECT user_id FROM BoL_forms WHERE po IN (SELECT DISTINCT po FROM BoL_items)));

modified version.php
ALTER TABLE  `order_forms` ADD INDEX  `snapshot_id` (  `snapshot_user` );
ALTER TABLE `BoL_forms` ADD INDEX `po` ( `po` ) ;
*/
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
// get all BOL POs
echo "Time is now ".time(); // now left in for query time knowledge
$starttime = time(); // track start time
$sql = "SELECT DISTINCT po FROM BoL_items";
$que = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_assoc($que))
{
	if($result['po'] != '') $bol_pos[] = $result['po']; // if the po is blank or null, do not count
}
echo "<br />\nNumber of POs in shipping system = ".count($bol_pos)."<br />\n";
echo "Get user id from BoL & order_forms; if they do not match, possibly update BoL.user_id<br />\n";
$numoriguser = 0;
$numboluser = 0;
$numpos = 0;
foreach($bol_pos as $bol_po)
{
	$sql = "SELECT ID, last_name FROM snapshot_users WHERE ID IN (SELECT COALESCE(shipto, snapshot_user) as user_id FROM order_forms WHERE ID = $bol_po)";
	$que = mysql_query($sql);
	checkdberror($sql);
	$numoriguser++;
	$orig_user = mysql_fetch_assoc($que);
	$sql = "SELECT ID, last_name FROM snapshot_users WHERE ID IN (SELECT user_id FROM BoL_forms WHERE po = $bol_po)";
	$que = mysql_query($sql);
	checkdberror($sql);
	$numboluser++;
	$bol_user = mysql_fetch_assoc($que);
	if($bol_user != $orig_user)
	{
		$numpos++;
		$sql = "SELECT DISTINCT bol_id FROM BoL_items WHERE po = $bol_po";
		$que = mysql_query($sql);
		checkdberror($sql);
		while($res = mysql_fetch_assoc($que))
		{
			// populate arrays with all the data we need
			$bad_bols[] = $res['bol_id'];
			$bad_bol_po[] = $bol_po;
			$bad_bol_orig[] = $orig_user['ID'];
			$bad_bol_bol[] = $bol_user['ID'];
		}
	}
	else
	{
		echo "PO# $bol_po will not be changed; BoL user = Original user = {$bol_user['last_name']}<br />\n"; // displaying for knowledge sake
	}
}
echo "Count of bad BoLs before culling known deviations: ".count($bad_bols)."<br />\n";
echo "Removing ch orders and BoLs which use a shipping agent...<br />\n";
// for each bad bol, check if the original order is a chorder OR if the bol id is for a shipping agent; if so, pull out of the result set
$culled = 0;
for($i=0; $i<count($bad_bols); $i++)
{
	// is the original order a ch? if so, pull it out and move on
	// SQL code borrowed/stolen from addbol.php
	$sql = "SELECT servicelevel FROM ch_order WHERE po = {$bad_bol_po[$i]}";
	$que = mysql_query($sql);
	checkdberror($sql);
	if(mysql_num_rows($que)>0)
	{
		// this is a ch order; kick from the bad pile
		// note that array_splice resets the keys; therefore, we need to decrement $i before returning so it hits the correct value
		echo "BOL # {$bad_bols[$i]} culled: ch order<br />\n";
		array_splice($bad_bols, $i, 1);
		array_splice($bad_bol_po, $i, 1);
		array_splice($bad_bol_orig, $i, 1);
		array_splice($bad_bol_bol, $i, 1);
		$culled++;
		$i--;
		continue;
	}
	// is the bol id for a shipping agent? if so, we're good
	// if the user_id field isn't set in the original, we need to do so, therefore skip the culling process
	if(!$bad_bol_bol[$i])
	{
		// document why we're skipping the cull in this case
		echo "BOL # {$bad_bols[$i]}, PO # {$bad_bol_po[$i]} not culled: BoL user_id not set<br />\n";
		continue;
	}
	$sql = "SELECT ID FROM shipping_agents WHERE snapshot_userid = {$bad_bol_bol[$i]}";
	$que = mysql_query($sql);
	checkdberror($sql);
	if(mysql_num_rows($que)>0)
	{
		// this is a shipping agent, remove from the bad pile
		// note that array_splice resets the keys; therefore, we need to decrement $i before returning so it hits the correct value
		echo "BOL # {$bad_bols[$i]} culled: shipping agent<br />\n";
		array_splice($bad_bols, $i, 1);
		array_splice($bad_bol_po, $i, 1);
		array_splice($bad_bol_orig, $i, 1);
		array_splice($bad_bol_bol, $i, 1);
		$culled++;
		$i--;
		continue;
	}
}
//var_dump($bad_bols);
echo "Number culled: $culled<br />\n";
echo "Count of BoLs to be updated: ".count($bad_bols)."<br />\n";
// update the bad bols
$updated = 0;
for($i=0; $i<count($bad_bols); $i++)
{
	$sql = "UPDATE BoL_forms SET user_id = '{$bad_bol_orig[$i]}' WHERE ID = '{$bad_bols[$i]}'";
	mysql_query($sql);
	checkdberror($sql);
	$updated++;
}
echo "Number of original user queries: $numoriguser<br />\n";
echo "Number of bol user queries: $numboluser<br />\n";
echo "Number of bad POs: $numpos<br />\n";
echo "Number of bols updated: $updated<br />\n";
echo "End time = ".time()."<br />\n";
echo "Total time to run query = ".number_format((time()-$starttime)/60)." minutes";
?>