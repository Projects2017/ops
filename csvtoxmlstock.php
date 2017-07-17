<?php
function csv2xmlstock($vendorid, $csvfilename, $returnresult = false) {
	if (!is_numeric($vendorid)) return false;
	$csvfp = fopen($csvfilename, "r");
	//if (!$csvfp) return false;
	$line = fgetcsv($csvfp, 2000); //-- Headers
	$headers = $line;
	foreach($headers as $key => $value) {
		if ($value == "") {
			unset($headers[$key]);
		}
		else {
			$headers[$key] = preg_replace("/[^A-Za-z0-9#]/", "_", $headers[$key]);
		}
	}
	$headers = array_flip($headers);
	
	$sql = "SELECT `key` FROM `vendor` WHERE `id` = '".$vendorid."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	$result = mysql_fetch_assoc($result);
	$vendorkey = $result['key'];
	
	$xmloutput = '<?phpxml version="1.0" encoding="utf-8"?>'."\n";
	$xmloutput .= '<message xmlns="http://www.pmdfurniture.com/schemas/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.pmdfurniture.com/schemas/ http://www.pmdfurniture.com/schemas/message.xsd">'."\n";
	$xmloutput .= "  <greeting>\n";
	$xmloutput .= "    <vendorid>".$vendorid."</vendorid>\n";
	$xmloutput .= "    <vendorkey>".$vendorkey."</vendorkey>\n";
	$xmloutput .= "  </greeting>\n";
	$xmloutput .= "  <stock>\n";
	
	if (!$returnresult) {
		print $xmloutput;
		$xmloutput = "";
	}
	
	while ($line = fgetcsv($csvfp, 2000)) {
		if (preg_match("/[a-zA-z0-9]/", implode("", $line)) != 0) {
			$xmloutput .= "    <item sku=\"";
			$xmloutput .= $line[$headers['sku']];
			$xmloutput .= "\">\n";
			if (is_numeric($line[$headers['alloc']])) {
				if ($line[$headers['alloc']] <= 0) {
					$xmloutput .= "      <stockstatus>Out of Stock</stockstatus>\n";
				} else {
					$xmloutput .= "      <stockstatus>In Stock</stockstatus>\n";
					$xmloutput .= "      <allocation>".$line[$headers['alloc']]."</allocation>\n";
				}
			} else {
				$xmloutput .= "      <stockstatus>";
				switch ($line[$headers['status']]) {
					case 'In Stock': $xmloutput .= 'In Stock';break;
					case 'Out of Stock': $xmloutput .= 'Out of Stock';break;
					case 'Discontinued': $xmloutput .= 'Discontinued';break;
					case 'Due in 1 week': $xmloutput .= 'Due in 1 week';break;
					case 'Due in Jan.': $xmloutput .= 'Due in Jan.';break;
					case 'Due in Feb.': $xmloutput .= 'Due in Feb.';break;
					case 'Due in Mar.': $xmloutput .= 'Due in Mar.';break;
					case 'Due in Apr.': $xmloutput .= 'Due in Apr.';break;
					case 'Due in May.': $xmloutput .= 'Due in May.';break;
					case 'Due in Jun.': $xmloutput .= 'Due in Jun.';break;
					case 'Due in Jul.': $xmloutput .= 'Due in Jul.';break;
					case 'Due in Aug.': $xmloutput .= 'Due in Aug.';break;
					case 'Due in Sept.': $xmloutput .= 'Due in Sept.';break;
					case 'Due in Oct.': $xmloutput .= 'Due in Oct.';break;
					case 'Due in Nov.': $xmloutput .= 'Due in Nov.';break;
					case 'Due in Dec.': $xmloutput .= 'Due in Dec.';break;
					default: $xmloutput .= 'Out of Stock'; // If we don't recognize it, just make it out of stock
				}
				$xmloutput .= "</stockstatus>\n";
				if (is_numeric($line[$headers['day']])&&($line[$headers['day']] > 0)) {
					$xmloutput .= "      <stockday>".$line[$headers['day']]."</stockday>\n";
				}
			}
			$xmloutput .= "    </item>\n";
			if (!$returnresult) {
				print $xmloutput;
				$xmloutput = "";
			}
		}
	}
	
	$xmloutput .= "  </stock>\n";
	$xmloutput .= "</message>\n";
	
	fclose($csvfp);
	if (!$returnresult) {
		print $xmloutput;
		return true;
	} else {
		return $xmloutput;
	}
}
?>