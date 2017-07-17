<?php
require_once('edi.php');
$edi = new Edi();
// viewtosend.php
$dir = opendir($edi->mWalmartEdiPath.'/msg_tosend/');
while($name = readdir($dir))
{
	if($name != '.' && $name != '..' && !is_dir($name))
	$files[] = $name;
}
$proc = array();
if(count($files) == 0) die();
foreach($files as $thisfile)
{
	$doc = fopen($edi->mWalmartEdiPath.'/msg_tosend/'.$thisfile, 'r');
	$readdata = fread($doc, filesize($edi->mWalmartEdiPath.'/msg_tosend/'.$thisfile));
	$proc[] = array('data' => $readdata, 'filename' => $thisfile);
}
foreach($proc as $testdata)
{
	print_r($testdata);
}


?>