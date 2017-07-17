<?php
// setcarrier.php
// script to apply a changed carrier name
require('inc_shipping.php'); // get the shipping functions
if(!$_POST) // if vars aren't POST'd in, exit
    sendError("setting a BOL's carrier name", "BOL View - Set Carrier (setcarrier.php line 5)", "Unauthorized access to setcarrier.php without POSTing variables", 'shipping.php');
// add the required scripts for db & user capabilities
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require_once('inc_postbol.php');
// let's do this
$bol_id = $_POST['bol_id'];
$newcarrier = $_POST['newcarrier'];
$sql = "UPDATE BoL_forms SET carrier = '$newcarrier' WHERE ID = $bol_id";
checkdberror($sql);
mysql_query($sql);
setCarrier($bol_id);
header('Location: viewbol.php?id='.$bol_id);
exit();
?>