<?php
include_once("../form.inc.php");
/* This function is not used and does not produce a validatable XML
function csv2xmloor($csvfilename, $returnresult = false) {
	//if (!is_numeric($vendorid)) return false;
	$csvfp = fopen($csvfilename, "r");
	//if (!$csvfp) return false;
	$line = fgetcsv($csvfp, 2000); //-- Headers
	$headers = $line;
	$headers = str_replace(":", "", $headers);
	$headers = str_replace(" ", "", $headers);
	foreach($headers as $key => $value) {
		if ($value == "") {
			unset($headers[$key]);
		}
		else {
			$headers[$key] = preg_replace("/[^A-Za-z0-9#]/", "_", $headers[$key]);
		}

	}
	$headers = array_flip($headers);
	
	//$sql = "SELECT `key` FROM `vendor` WHERE `id` = '".$vendorid."'";
	//$result = mysql_query($sql);
	//checkDBerror($sql);
	//$result = mysql_fetch_assoc($result);
	//$vendorkey = $result['key'];
	
	$xmloutput = '<?phpxml version="1.0" encoding="utf-8"?>'."\n";
	$xmloutput .= '<message xmlns="http://www.pmdfurniture.com/schemas/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.pmdfurniture.com/schemas/ http://www.pmdfurniture.com/schemas/message.xsd">'."\n";
	//$xmloutput .= "  <greeting>\n";
	//$xmloutput .= "    <vendorid>".$vendorid."</vendorid>\n";
	//$xmloutput .= "    <vendorkey>".$vendorkey."</vendorkey>\n";
	//$xmloutput .= "  </greeting>\n";
	$xmloutput .= "  <orders>\n";
	
	if (!$returnresult) {
		print $xmloutput;
		$xmloutput = "";
	}

	while ($line = fgetcsv($csvfp, 2000)) {
	$strPO = $line[$headers['PO#']];
	$strDealer = $line[$headers['Dealer']];
	$strCarrier = $line[$headers['Carrier']];
	$strTracking = $line[$headers['Tracking#']];
	$dtShipDate = $line[$headers['ShipDate']];
	$strShipInfo = $line[$headers['ShippingInfo']];
	$dtDeliveryDate = $line[$headers['DeliveryDate']];
	$tmDeliveryTime = $line[$headers['DeliveryTime']];
				
	//	if (preg_match("/[a-zA-z0-9]/", implode("", $line)) != 0) {
			$xmloutput .= "	<item>\n";
			$xmloutput .= "		<PO#>".$line[$headers['PO#']]."</PO#>\n";
			$xmloutput .= "		<Dealer>".$line[$headers['Dealer']]."</Dealer>\n";
			$xmloutput .= "		<Carrier>".$line[$headers['Carrier']]."</Carrier>\n";
			$xmloutput .= "		<Tracking#>".$line[$headers['Tracking#']]."</Tracking#>\n";
			$xmloutput .= "		<ShipDate>".$line[$headers['ShipDate']]."</ShipDate>\n";
			$xmloutput .= "		<ShippingInfo>".$line[$headers['ShippingInfo']]."</ShippingInfo>\n";
			$xmloutput .= "		<DeliveryDate>".$line[$headers['DeliveryDate']]."</DeliveryDate>\n";
			$xmloutput .= "		<DeliveryTime>".$line[$headers['DeliveryTime']]."</DeliveryTime>\n";
			$xmloutput .= "	</item>\n";

			if (!$returnresult) {
				print $xmloutput;
				$xmloutput = "";
			}
	}
	
	$xmloutput .= "  </orders>\n";
	$xmloutput .= "</message>\n";
	
	fclose($csvfp);
	if (!$returnresult) {
		print $xmloutput;
		return true;
	} else {
		return $xmloutput;
	}
}
*/

function formcsvimport($form, $searchcolumn, $csvfilename) {
	$linenum = 1; // 1 is header
	$errors = array();
	$csvfp = fopen($csvfilename, "r");
	//if (!$csvfp) return false;
	$line = fgetcsv($csvfp, 2000); //-- Headers
	$headers = $line;
	
	$fields = forminfo($form); // Get Column Info for Form
	foreach($headers as $key => $value) {
		$value = strtolower($value);
		if (($value == '')||($value == 'vendor_id')||!isset($fields[$value])||!$fields[$value]['edit']||!$fields[$value]['visible']) {
			if (!isset($errors[$linenum])) $errors[$linenum] = Array();
			$errors[$linenum][] = Array('type' => 'warn', 'desc' => 'Dropping malformed column header "'.$value.'". Column will not be processed.');
			unset($headers[$key]);
		} else {
			$headers[$key] = preg_replace("/[^A-Za-z0-9_]/", "_", $value);
		}
	}

	$headers = array_flip($headers);
	
	if (!isset($headers[$searchcolumn])) {
		if (!isset($errors[$linenum])) $errors[$linenum] = Array();
		$errors[$linenum][] = Array('type' => 'fatal', 'desc' => $searchcolumn.' header not found, processing stopped.');
	}
	$succ_lines = 0;
	$succ_rows = 0;
	//forminfo('order');
	if (!$errors) { // If errors have already occured, then don't process the rest of the file
		while ($line = fgetcsv($csvfp, 2000)) {
			++$linenum;
			
			$update = array();
			foreach ($headers as $key => $val) { // Loop through available headers and extract fields
				if (!isset($line[$val])) { // Not present in CSV Line array, so we don't throw any warnings later.
					$line[$val] = '';
				}
				// Check for field specific problems.
				if ($fields[$key]['required'] && !$line[$val]) { // Required Fields are not blankable
					if (!isset($errors[$linenum])) $errors[$linenum] = Array();
					$errors[$linenum][] = Array('type' => 'warn', 'desc' => 'Field "'.$key.'" not blankable. Skipping update of this value.');
				} elseif (($fields[$key]['datatype'] == 'number') && !is_numeric($line[$val])) { // Test for Numeric Fields
					if (!isset($errors[$linenum])) $errors[$linenum] = Array();
					$errors[$linenum][] = Array('type' => 'warn', 'desc' => 'Non-Numeric Value in Column "'.$key.'". Skipping update of this value.');
				} elseif (($fields[$key]['datatype'] == 'checkbox')) { // If it's a checkbox, convert value over.
					if (($update[$key] == '1') || strtolower(substr($update[$key],0,1)) == 'y') { // Matches Yes or 1 as checked
						$update[$key] = 'on';
					} else { // Anything else must be unchecked
						$update[$key] = '';
					}
				} elseif ($fields[$key]['datatype'] == 'date') { // Treat Dates Specially
					if ($line[$val]) { // Only test for format if something is present
						$temp = strtotime($line[$val]);
						if ($temp) { // Format Succeeded in Parsing, so we're adding it to val
							$update[$key] = $line[$val];
						} else { // Failed to Parse, Throwing warning and not using value
							if (!isset($errors[$linenum])) $errors[$linenum] = Array();
							$errors[$linenum][] = Array('type' => 'warn', 'desc' => 'Non-parsable date in column "'.$key.'". Skipping update of this value.');	
						}
					} else { // Blank Value, So pass it along
						$update[$key] = '';
					}
				} elseif (($fields[$key]['datatype'] == 'text') && ($fields[$key]['limit'] != '-1') && (strlen($line[$val]) > $fields[$key]['limit'])) {
					// Text Field and the Length Limit is exceeded
					if (!isset($errors[$linenum])) $errors[$linenum] = Array();
					$errors[$linenum][] = Array('type' => 'warn', 'desc' => 'Text too long in field "'.$key.'". Truncating value.');	
					$update[$key] = substr($line[$val],0,$fields[$key]['limit']);
				} else { // Nothing is wrong, add it.
					$update[$key] = $line[$val];
				}
			}
			
			if (!$update[$searchcolumn] || !is_numeric($update[$searchcolumn])) {
				if (!isset($errors[$linenum])) $errors[$linenum] = Array();
				$errors[$linenum][] = Array('type' => 'error', 'desc' => $searchcolumn." is non-numeric, unable to process this line.");
				continue; // Error... move on to next line
			}
			
			$formdata = formdata($form,0,array($searchcolumn => $update[$searchcolumn]));
			if (!$formdata) {
				if (!isset($errors[$linenum])) $errors[$linenum] = Array();
				$errors[$linenum][] = Array('type' => 'error', 'desc' => "No matching record found to update.");
				continue; // Error... move on to next line
			}
			
			$old_succ_rows = $succ_rows;
			foreach ($formdata as $row) { // Updating each row of data matched
				$rowupdate = Array();
				foreach ($update as $col => $val) {
					if ($formdata[$col] != $val) // Only send update if it's an actual update
						$rowupdate[$col] = $val; // BUG: This will always send dates, regardless of change.
				}
				$result = formupdate($form,$row['id'],$rowupdate);
				if (is_array($result)) { // Failure of Update will return an array
					if (!isset($errors[$linenum])) $errors[$linenum] = Array();
					$errors[$linenum][] = Array('type' => 'error', 'desc' => "Failed to update row, no good reason, please contact RSS Dev Staff.");
					continue; // Error... move on to next line
				} else {
					// TODO: Update Update since SMS tag.
					$sql = "UPDATE `claim_".$form."` SET `upsincesms` = '1' WHERE `id` = '".$row['id']."'";
					mysql_query($sql);
					checkDBerror($sql);
					++$succ_rows;
				}
			}
			
			unset($rowupdate);
			if ($old_succ_rows == $succ_rows) {
				if (!isset($errors[$linenum])) $errors[$linenum] = Array();
				$errors[$linenum][] = Array('type' => 'error', 'desc' => "Failed to update line, no good reason, please contact RSS Dev Staff.");
				continue; // Error... move on to next line
			} else {
				++$succ_lines;
			}
		}
	}
	if ($succ_lines) {
		// Report the Successes
		$errors['succ'] = array('lines' => $succ_lines, 'rows' => $succ_rows);
	}
	return $errors;
}
?>
