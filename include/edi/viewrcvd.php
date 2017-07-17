<?php
// viewtosend.php
$dir = opendir(dirname(__FILE__).'/msg_rcvd/');
while($name = readdir($dir))
{
	if($name != '.' && $name != '..' && !is_dir($name))
	$files[] = $name;
}
$proc = array();
foreach($files as $thisfile)
{
	$doc = fopen(dirname(__FILE__).'/msg_rcvd/'.$thisfile, 'r');
	$readdata = fread($doc, filesize(dirname(__FILE__).'/msg_rcvd/'.$thisfile));
	$proc[] = array('data' => $readdata, 'filename' => $thisfile);
}
foreach($proc as $testdata)
{
	echo "<pre>";
	print_r($testdata);
	echo "</pre>";
}


?>