<?php

chdir(dirname(__FILE__));
require('inc_walmartinventory.php');

/**
 * Inventory Report Generation Object
 * @global WalmartInventory $inv
 */
$inv = new WalmartInventory();

$file = <<<EOT
FH|999999.20010801.032246.784879|FII|4.0.0|999999|Vendor|2677|Walmart.com
II|487988|009843873409|AC-67800|AC|500|3|4|||6.00|5.00|3.00|
II|487989|009843478497|AA-67800|AA||5|10|||6.00|5.00|3.00|
II|487990|009843848999|PO-67800|PO|500|||20011201||6.00|5.00|3.00|
II|487991|009843842638|JT-67800|JT||5|7|||6.00|5.00|3.00|
II|487992|009843829781|BO-67800|BO||3|5|||6.00|5.00|3.00|
II|487993|009843958732|SE-67800|SE|300|||20011201|20020115|6.00|5.00|3.00|
II|487994|009843459727|RO-67800|RO|300||||20020101|6.00|5.00|3.00|
II|487995|009843878211|NA-67800|NA|||||||||
II|487996|009843878212|DT-67800|DT|||||||||
FT|999999.20010801.032246.784879|8
EOT;
try {
    $inv->Import($file);
} catch (WalmartInventoryDetailException $e) {
    echo $e->invLine . "\n";
    throw $e;
}

$newfile = $inv->Export();
$file = trim($file);
$newfile = trim($newfile);
if ($newfile == $file) {
    echo "Inventory Success!\n";
} else {
    echo "Inventory Failed\n";
    echo $file;
    echo "\n\nNew File:\n";
    echo $newfile;
    echo "\n\nChanged Lines:\n";
    $file_explode = explode("\n",$file);
    $newfile_explode = explode("\n", $newfile);
    foreach ($file_explode as $i => $line) {
        if ($line != $newfile_explode[$i])
            echo $line."\nto: ".$newfile_explode[$i]."\n\n";
    }
}

$file = <<<EOT
FH|999999.20010801.141209.024234|FFC|4.0.0|2677|Walmart.com|999999|Supplier
FC|999999.20010801.132307.478911|FII
FT|999999.20010801.141209.024234|1
EOT;

try {
    $inv->Import($file);
} catch (WalmartInventoryDetailException $e) {
    echo $e->invLine . "\n";
    throw $e;
}

$newfile = $inv->Export();
$file = trim($file);
$newfile = trim($newfile);
if ($newfile == $file) {
    echo "Inventory Success!\n";
} else {
    echo "Inventory Failed\n";
    echo $file;
    echo "\n\nNew File:\n";
    echo $newfile;
    echo "\n\nChanged Lines:\n";
    $file_explode = explode("\n",$file);
    $newfile_explode = explode("\n", $newfile);
    foreach ($file_explode as $i => $line) {
        if ($line != $newfile_explode[$i])
            echo $line."\nto: ".$newfile_explode[$i]."\n\n";
    }
}

echo "</pre>";
