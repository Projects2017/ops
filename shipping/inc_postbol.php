<?php
// script for post-BoL functions

function addCHQueue($bol)
{
	// Insert into CommerceHub Stuff
	$sql = "SELECT `ch_order`.`id` FROM `ch_order` WHERE `po` IN (SELECT `po` FROM `BoL_forms` WHERE `id` = '".$bol."')";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($query)) {
		$sql = 'INSERT INTO `ch_bolqueue` (`bol_id`, `processed`) VALUES ("'.$bol.'",0);';
		mysql_query($sql);
		checkDBerror($sql);
	}
}

function approvedCredit($cr)
{
	// function run after a credit request is approved
}

function approvedCreditNum($cr, $credit_id)
{
	// function run after credit (PO) # is generated
	
	// Insert into CommerceHub Stuff
	$sql = "SELECT `ch_order`.`id` FROM `ch_order` WHERE `po` IN (SELECT `po` FROM `BoL_forms` WHERE `id` = '".$cr."')";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($query)) {
		$sql = 'INSERT INTO `ch_bolqueue` (`bol_id`, `processed`) VALUES ("'.$cr.'",0);';
		mysql_query($sql);
		checkDBerror($sql);
	}
}

function deniedCredit($credit)
{
	// function run after a credit request is denied
}

function addedBol($bol)
{
	// function run after a BoL has been added
}

function addedCr($bol)
{
	// function run after a Credit Request has been added
}

function addedMultiBol($bol)
{
	// function run after a multi-PO BoL has been added
}

function setFreight($bol)
{
	// function run after freight has been entered for a BoL
}

function setCarrier($bol)
{
	// function run after the carrier for a BoL has been reset
}

function setComment($bol)
{
	// function run after a comment for a BoL has been added
}

function setTracking($bol)
{
	// function run after a tracking number for a BoL has been added
}
?>