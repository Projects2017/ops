<?php

// This generates lists of dealers e-mails that are used to sync list-servs.
require("../database.php");
// "SELECT email, email2, email3 FROM users"

/** Make sure temp directory is present **/
if (!is_dir($listdir)) {
	$succ = mkdir($listdir);
	if (!$succ)
		die("Cannot make ".$listdir." temp directory");
	unset($succ);
}

/** Team Lists **/
$sql = "SELECT email, teamlist, email2, teamlist2, email3, teamlist3, team FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$team = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['teamlist'] == "Y"))
		$team[$row['team']][] = $row['email'];
	if ($row['email2'] && ($row['teamlist2'] == "Y"))
		$team[$row['team']][] = $row['email2'];
	if ($row['email3'] && ($row['teamlist3'] == "Y"))
		$team[$row['team']][] = $row['email3'];
}

if (!isset($team['A'])) $team['A'] = array();
if (!isset($team['B'])) $team['B'] = array();
if (!isset($team['C'])) $team['C'] = array();
if (!isset($team['D'])) $team['D'] = array();
if (!isset($team['F'])) $team['F'] = array();

foreach ($team as $key => $value) {
	$value = array_unique($value);
	$file = fopen ($listdir."/team".$key,"w");
	if (!$file) die("unable to open/create team file for writing");
	foreach ($value as $email) {
		fwrite($file, $email."\n");
	}
	fclose($file);
}

/** Dealers List **/
$sql = "SELECT email, dealerlist, email2, dealerlist2, email3, dealerlist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$dealerlist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['dealerlist'] == "Y"))
		$dealerlist[] = $row['email'];
	if ($row['email2'] && ($row['dealerlist2'] == "Y"))
		$dealerlist[] = $row['email2'];
	if ($row['email3'] && ($row['dealerlist3'] == "Y"))
		$dealerlist[] = $row['email3'];
}
$dealerlist = array_unique($dealerlist);
$file = fopen ($listdir."/dealers","w");
foreach ($dealerlist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);

/** License List **/
$sql = "SELECT email, licenselist, email2, licenselist2, email3, licenselist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$licenselist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['licenselist'] == "Y"))
		$licenselist[] = $row['email'];
	if ($row['email2'] && ($row['licenselist2'] == "Y"))
		$licenselist[] = $row['email2'];
	if ($row['email3'] && ($row['licenselist3'] == "Y"))
		$licenselist[] = $row['email3'];
}
$licenselist = array_unique($licenselist);
$file = fopen ($listdir."/licensee","w");
foreach ($licenselist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);


/** Franchise List **/
$sql = "SELECT email, franchiselist, email2, franchiselist2, email3, franchiselist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$franchiselist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['franchiselist'] == "Y"))
		$franchiselist[] = $row['email'];
	if ($row['email2'] && ($row['franchiselist2'] == "Y"))
		$franchiselist[] = $row['email2'];
	if ($row['email3'] && ($row['franchiselist3'] == "Y"))
		$franchiselist[] = $row['email3'];
}
$franchiselist = array_unique($franchiselist);
$file = fopen ($listdir."/franchise","w");
foreach ($franchiselist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);

/** Managers List **/
$sql = "SELECT email, managerlist, email2, managerlist2, email3, managerlist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$managerlist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['managerlist'] == "Y"))
		$managerlist[] = $row['email'];
	if ($row['email2'] && ($row['managerlist2'] == "Y"))
		$managerlist[] = $row['email2'];
	if ($row['email3'] && ($row['managerlist3'] == "Y"))
		$managerlist[] = $row['email3'];
}
$managerlist = array_unique($managerlist);
$file = fopen ($listdir."/managers","w");
foreach ($managerlist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);

/** Home Office List **/
$sql = "SELECT email, homelist, email2, homelist2, email3, homelist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$homelist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['homelist'] == "Y"))
		$homelist[] = $row['email'];
	if ($row['email2'] && ($row['homelist2'] == "Y"))
		$homelist[] = $row['email2'];
	if ($row['email3'] && ($row['homelist3'] == "Y"))
		$homelist[] = $row['email3'];
}
$homelist = array_unique($homelist);
$file = fopen ($listdir."/homeoffice","w");
foreach ($homelist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);

/** WODS List **/
$sql = "SELECT email, wodslist, email2, wodslist2, email3, wodslist3 FROM users WHERE disabled != 'Y'";
$result = mysql_query($sql);
checkDBError($sql);

$wodslist = array();
while ($row = mysql_fetch_Array($result)) {
	if ($row['email'] && ($row['wodslist'] == "Y"))
		$wodslist[] = $row['email'];
	if ($row['email2'] && ($row['wodslist2'] == "Y"))
		$wodslist[] = $row['email2'];
	if ($row['email3'] && ($row['wodslist3'] == "Y"))
		$wodslist[] = $row['email3'];
}
$wodslist = array_unique($wodslist);
$file = fopen ($listdir."/wods","w");
foreach ($wodslist as $email) {
	fwrite($file, $email."\n");
}
fclose($file);
?>
