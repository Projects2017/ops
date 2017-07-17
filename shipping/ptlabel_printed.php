<?php
// ptlabel_printed.php
// script to update the ptlabel_printed field in BoL_queue
// start w/ the db requirements
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');
// first we split the GET[id] by semicolon to allow for >1 PO
$getids = explode(';',$_GET['id']);
foreach($getids as $po)
{
	// make the query real quick
	$sql = "UPDATE BoL_queue SET ptlabel_printed = 1 WHERE po = $po";
	$que = mysql_query($sql);
	checkdberror($sql);
}
// go back to the viewonly view
if(strpos($_GET['id'],';'))
{
	// this is a multi
	header('Location: multiaddbol.php?ids='.$_GET['id']."&viewonly");
}
else
{
	header("Location: addbol.php?id=".($_GET['id']+1000)."&viewonly");
}
?>