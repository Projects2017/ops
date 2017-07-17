<?php
// picktix_printed.php
// script to update the picktix_printed field in BoL_queue
// start w/ the db requirements
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
// first we need to see if the get[id'] is a single value or a semi-colon delimited list
// if semi-colon delimited, we'll do this a couple times
$run_array = explode(';',$_GET['id']);
foreach($run_array as $id)
{
	// make the query real quick
	$sql = "UPDATE BoL_queue SET picktix_printed = 1 WHERE po = $id";
	$que = mysql_query($sql);
	checkdberror($sql);
	// see if the order is in OOR first
	$sql = "SELECT id FROM claim_order WHERE po = ".($id+1000);
	$que = mysql_query($sql);
	if(mysql_num_rows($que)>0)
	{
		$res = mysql_fetch_assoc($que);
		$sql = "UPDATE claim_order SET factory_confirm = 'on' WHERE id = ".$res['id'];
		$que = mysql_query($sql);
		checkDBerror($sql);
	}
}
// go back to the proper viewonly view based on # of ids sent in
if(count($run_array)>1)
{
	header("Location: multiaddbol.php?ids=".($_GET['id'])."&viewonly");
}
else
{
	header("Location: addbol.php?id=".($_GET['id']+1000)."&viewonly");
}
?>