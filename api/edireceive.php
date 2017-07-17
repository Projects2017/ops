#!/usr/bin/php -d error_reporting=E_ALL\&\!E_NOTICE -q
<?php
// this script is only run via command line
// accepts as arguments a list of files to read & process
if($argc == 1) die("Missing argument(s): full path to the EDI file(s) to process\n");
require_once(dirname(dirname(__FILE__)).'/include/edi/edi.php');
require_once(dirname(dirname(__FILE__)).'/include/edi/bo_shippingedi.php');
// skip #1 which is this filename
$skip = true;
$error = 0;
foreach($argv as $k => $file)
{
	if($skip) { $skip = false; continue; }
	// file = filename of the EDI we want to process
	$doc = fopen($file, 'r');
	$readdata = fread($doc, filesize($file));
	fclose($doc);
	if(strlen($readdata) != 0 && !is_null($readdata))
	$proc = array('data' => $readdata, 'filename' => basename($file));
	$test = new Edi($proc);
	$confirm = $test->Confirm();
	$process = $test->Process();
	if(!$error && $confirm && $process) { $error = 0; } else { $error = 1; }
}
if(!$error) { echo 0; } else { echo -1; }
?>