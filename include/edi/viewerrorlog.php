<?php
require_once('edi.php');
$edi = new Edi();
// viewtosend.php
$doc = fopen($edi->mWalmartEdiPath.'/msg_tosend/error.log', 'r');
$readdata = fread($doc, filesize($edi->mWalmartEdiPath.'/msg_tosend/'.$thisfile));
?><pre><?php print_r($readdata); ?></pre>