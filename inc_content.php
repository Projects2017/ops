<?php
/* shared page content functions */

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');

require_once("inc_viewpo.php"); // script with additional functions which display OOR & shipping data

function getHeader($header, $loc = '') {
	global $sql;
	$sql = "SELECT header FROM " . $loc . "snapshot_headers WHERE id=".$header;
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query))
		return $result['header'];
	return "";
}


function getOrderType($type) {
	if ($type == "c")
		$order = "Credit";
	elseif ($type == "f")
		$order = "Bill";
	else
		$order = "Order";
	return $order;
}

/** Look up Discount or Freight Type for a user/form combination.
 *
 * Returns final discount (combined effects of user,form,vendor)
 *
 * @param string $type freight|discount
 * @param int $userid User ID to look up
 * @param int $formid Form ID to look up
 * @return string A tiered discount string.
 */
function getDiscount($type, $userid,$formid, $table_prefix = '')
{
    if (!is_numeric($userid)) die("getDiscountPercentage: userid must be numeric.");
    if (!is_numeric($formid)) die("getDiscountPercentage: formid must be numeric.");
    if ($type != 'discount' && $type != 'freight' && $type != 'markup' && $type != 'tier') die("getDiscountPercentage: type must be discount, markup, tier or freight.");

    // First Check if User has something special
    $disc = loadDiscount($type,array("form_id" => $formid,"user_id"=>$userid),"user");
    if ($disc)
        return $disc;
    if ($type != 'markup' && $type != 'tier') {
        // Then Check Overall Form Discount
        $disc = loadDiscount($type,array("form_id" => $formid),$table_prefix."form");
        if ($disc)
            return $disc;
        // Last Resort Check Vendor Discount
        $sql = "SELECT vendor FROM forms WHERE ID = $formid";
        $query = mysql_query($sql);
        checkDBError($sql);
        if ($result = mysql_fetch_array($query)) {
            return loadDiscount($type,array("vendor_id" => $result['vendor']),"vendor");
        }
    }
    // Nothing else, Discount must be 0
    return 0;
}

function restorePO($po) {
	if (!is_numeric($po)) die("Delete PO: Non-Numeric PO ID#");
	$po_id = $po - 1000;
	$sql = "UPDATE order_forms SET deleted=0 WHERE ID=$po_id";
	$query = mysql_query($sql);

	if (!mysql_affected_rows()) die("Delete PO: Non-Existant PO");
}

function deletePO($po) {
	if (!is_numeric($po)) die("Delete PO: Non-Numeric PO ID#");
	$po_id = $po - 1000;
	$sql = "UPDATE order_forms SET deleted=1 WHERE ID=$po_id";
	$query = mysql_query($sql);

	if (!mysql_affected_rows()) die("Delete PO: Non-Existant PO");
}

function MoS_processPO($po, $date) {
	global $MoS_enabled;
	if (!$MoS_enabled) die("MoS_processPO: This isn't a Market Order System");
	if (!is_numeric($po)) die("MoS_Proccess PO: Non-Numeric PO ID#");
	if (!is_numeric($date)) die("MoS_Proccess PO: Non-Numeric Timestamp");
	$po_id = $po-1000;
	$sql = "UPDATE MoS_order_forms SET processed='Y',process_time='".date("Y-m-d H:i:s", $date)."' WHERE PMD_order_id='$po_id'";
	mysql_query($sql);
	checkDBError($sql);
}

function processPO($po) {
	if (!is_numeric($po)) die("Proccess PO: Non-Numeric PO ID#");
	$po_id = $po-1000;

	$sql = "SELECT form, user, ordered, snapshot_form, processed, site, total FROM order_forms WHERE ID = ".$po_id;
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		if ($result['processed'] == 'Y') return; // changed to not die when a po is already processed because it's annoying
		$user2 = $result['user'];
		$form = $result['form'];
		$date = $result['ordered'];
		$site = $result['site'];
                $amount = $result['total'];
		$snapshot_form = $result['snapshot_form'];
	} else {
		die("Process PO: Non-Existant PO");
	}

        $sql = "SELECT email, email2, proc_email, proc_email2, proc_url FROM vendors INNER JOIN forms ON `forms`.`vendor` = `vendors`.`ID` WHERE `forms`.`ID` = '".$form."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_Array($query))
		$vemail = $result;

        // Send to EDI, do this before marking processed, if it fails, we don't want to mark it processed.
        if (substr($vemail['proc_url'],0,4) == 'EDI:' && $amount >= 0) {
            submitEdiOrder($po,substr($vemail['proc_url'],4));
        }

	$proc_date = date("Y-m-d H:i:s");
	$sql = "UPDATE order_forms SET processed='Y',process_time='".$proc_date."' WHERE ID='$po_id'";
	mysql_query($sql);
	checkDBError($sql);

        if ($amount < 0) {
            $sql = "UPDATE order_forms SET email_vendor='".date("Y-m-d")."' WHERE ID='".$po_id."'";
            mysql_query($sql);
            checkDBError();
        }

	submitBoL($po); // Add to BoL System

	$sql = "SELECT email, email2, email3 FROM users WHERE ID=$user2";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		$email = $result[0];
		$email2 = $result[1];
		$email3 = $result[2];
	} else {
		unset($email);
		unset($email2);
		unset($email3);
	}

	$msg = '<html><head><style>'.PrintBaseCSS().'</style></head><body><b>Your order has been submitted; please retain for your records.<br><br>Do not reply to this e-mail, contact your Dealer Support Person for any issues associated with this order.<hr><br><br>' . OrderForWeb($po, 'dealer', 'D').'</body></html>';

	$subject = 'Order Submitted - PO # '.$po;
	$headers = "Content-Type: text/html; charset=ISO-8859-1";
	$headers .= "\nFrom: RSS Orders <orders@retailservicesystems.com>";
	if ($email2 <> "") $headers .= "\nCc: ".$email2;
	if ($email3 <> "") $headers .= "\nCc: ".$email3;
	sendmail($email, $subject, $msg, $headers);

	// Start Vendors E-Mail
	$subject = 'Order Processed - PO # '.$po;
	$msg = '<html><head><style>'.PrintBaseCSS().'</style></head><body><b>A new order was processed. Please see below for details.<hr><br><br>' . OrderForWeb($po, 'orders', 'V').'</body></html>';

	$headers = "From: RSS Orders <orders@retailservicesystems.com>";
	$headers .= "\nContent-Type: text/html; charset=ISO-8859-1";
	$headers .= "\nBcc: orders@retailservicesystems.com";
	if (!empty($vemail['email'])) sendmail($vemail['email'], $subject, $msg, $headers);
	$headers = "From: RSS Orders <orders@retailservicesystems.com>";
	$headers .= "\nContent-Type: text/html; charset=ISO-8859-1";
	if (!empty($vemail['email2'])) sendmail($vemail['email2'], $subject, $msg, $headers);
	
	if ($vemail['proc_url'] == 'CorsicanaXML') {
		submitCorsicana($po);
	}

	// Send to MoS, do this last in case we encounter an error, we don't end up stopping mid process
	submitMoS($po, $site, $proc_date);
}

function PrintBaseCSS(){
	return '.fat_black,.fat_red{font-size:16px;font-weight:700}.text_12,.text_large,a,a:hover,p{text-decoration:none}.fat_black,.fat_black_12,.fat_black_14,.fat_red,.text_large{font-weight:700}@media print{.noprint{display:none}}.fat_red{font-family:Arial,Helvetica,sans-serif;color:#C00}.fat_black,.fat_black_12,.fat_black_14,.text_12,.text_large{font-family:Arial,Helvetica,sans-serif;color:#000}.text_12{font-size:12px}.text_large{font-size:18px}.fat_black_12{font-size:12px}.fat_black_14{font-size:14px}.orderTH,.orderTHRow td,a,p{font-family:Arial,Helvetica,sans-serif;font-size:12px}body{background-color:#EDECDA}a:hover{color:#666}a{color:#C00}p{color:#000}.totalRow{border-top-width:1px;border-bottom-width:1px;border-top-style:solid;border-bottom-style:solid;border-top-color:#033;border-bottom-color:#033}.orderTH,.orderTHRow td{padding:5px;font-weight:700;background:#cc9;color:#000}.orderTD,.orderTDfail{padding:5px;border-bottom:1px solid #fcfcfc;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#000}.customerRowLeft,.customerRowRight{font-family:Arial,Helvetica,sans-serif;font-size:12px;border-top:1px solid #fff;border-bottom:1px solid #fff;font-weight:700}.orderTDfail{color:#c00;font-weight:700}.orderTDheading{padding:5px;background:#fcfcfc;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#000}.orderTD_approved{color:#0A0}.orderTD_declined{color:#A00}.customerRowLeft{border-left:1px solid #fff}.customerRowRight{border-right:1px solid #fff}.alert,.text_16{font-size:16px;font-family:Arial,Helvetica,sans-serif}.alert{font-weight:700;color:#C00}h1{font-family:Arial,Helvetica,sans-serif;font-size:28px;color:#333}h2,h3{font-family:Arial,Helvetica,sans-serif}.userAddressTable{border:1px solid #CC9}blockquote.article,blockquote.float,img blockquote.float{display:block;font-family:Arial,Helvetica,sans-serif;color:#000;margin:5px;border:2px solid #CC9;font-size:75%;padding:5px;background:#FFF}blockquote.article{width:60%}blockquote.float{float:right;width:30%}img blockquote.float{float:right}.balanceOwed{color:#A00}.balancePaid{color:#0A0}';
}

function submitEdiOrder($po, $ediName) {
        require("include/edi/bo_orderedi.php"); // Include classes
        $poStruct = new PurchaseOrderStruct();
        $poStruct->Load($po);
        $poStruct->SendEDI($ediName);
}

function submitCorsicana($po) {
	include(dirname(__FILE__).'/include/corsicana.php');
	processCorsicanaXML($po);
}

function submitMoS($po, $site, $date) {
	global $admin_pass;
	if (!is_numeric($po)) die("Submit MoS: Non-Numeric PO ID#");
	if (MoS_siteinfo($site)) {
		$site = MoS_siteinfo($site);
		$url = sprintf($site['procurl'],urlencode($po),urlencode($date));
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".urlencode($admin_pass));
		curl_exec($ch); // The output really isn't important at the moment.
		curl_close($ch);
	}
}

function submitBoL($po) {
	if (!is_numeric($po)) die("Submit BoL: Non-Numeric PO ID#");
	// send to BOL processing
	$po_id = $po-1000;
	// Check to make sure that we havn't inserted this BoL before
	$sql = "SELECT po FROM BoL_queue WHERE po = ".$po_id;
	$que = mysql_query($sql);
	if (mysql_num_rows($que)) die ("Submit BoL: BoL Previously Processed");
	// We havn't processed the PO yet, so let's start the process
	// Now check to see if the form has the Shipping System turned off
	$sql = "SELECT form FROM order_forms WHERE ID = $po_id"; // this query returns the form # of the PO
	$sql2 = "SELECT useshipping FROM forms WHERE ID IN ($sql)"; // ...which is used to return the useshipping status
	$que = mysql_query($sql2);
	$res = mysql_fetch_array($que);
	if($res['useshipping']!="Y" || $res['nobolmerge']) return; // if the value of useshipping is anything other than 'Y' (the default) or nobolmerge is non-zero, return without adding to the queue
	$sql = "SELECT total, freight_percentage, processed FROM order_forms WHERE ID = $po_id";
	checkdberror($sql);
	$que = mysql_query($sql);
	if (!mysql_num_rows($que)) die("Submit BoL: Non-Existant PO ID#");
	// PO is real... but is it processed?
	$res = mysql_fetch_assoc($que);
	if ($res['processed'] != 'Y') die("Submit BoL: Cannot Queue a UnProcessed PO");
	$total = $res['total'];
	$freightPercentage = $res['freight_percentage'];
	$sql =  "SELECT SUM(setqty) as totset, SUM(mattqty) as totmatt, SUM(qty) as totbox FROM orders WHERE po_id = $po_id";
	checkdberror($sql);
	$que = mysql_query($sql);
	$res = mysql_fetch_assoc($que);
	$totalset = $res['totset'];
	$totalmatt = $res['totmatt'];
	$totalbox = $res['totbox'];
	$sql = "INSERT INTO BoL_queue (po, totalset, totalmatt, totalbox, prepaidfreight, createdate) VALUES ($po_id, $totalset, $totalmatt, $totalbox, ".number_format($total - ($total/(1+($freightPercentage/100))), 2, '.','').", NOW())";
	$runsql = mysql_query($sql);
	checkdberror($sql);
	return $runsql ? true : false;
}

function creditAvail($dealer_id) {
	if (!is_numeric($dealer_id))
		return 0;
	$sql = "SELECT credit_limit FROM users WHERE ID='".$dealer_id."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	$credit_limit = 0;
	if ($result = mysql_fetch_array($query, MYSQL_ASSOC))
		$credit_limit = $result['credit_limit'];
	if (!is_numeric($credit_limit))
		$credit_limit = 0;
	$sql = "SELECT SUM(total) FROM order_forms WHERE user='".$dealer_id."' AND deleted=0";
	$query = mysql_query($sql);
	checkDBError($sql);
	$balance = 0;
	if ($result = mysql_fetch_array($query))
		$balance = $result[0];
	$credit_limit = $credit_limit - $balance;
	if ($credit_limit < 0)
		$credit_limit = 0;
	return $credit_limit;
}

function calcPrice($type, $item, $user_id, $form_id, $table_prefix) {
    if ($type != 'box' && $type != 'matt' && $type != 'set') {
        die("Calc Price: unknown type, must be one of item, box, matt, set");
    }
    switch ($type) {
        case 'box':
            $pricefield = 'price';
            $costfield = 'cost';
            $markupfield = 'markup';
            break;
        case 'set':
            $pricefield = 'set_';
            $costfield = 'set_cost';
            $markupfield = 'set_markup';
            if (!isset($item[$pricefield]))
                $pricefield = 'set';
            break;
        default:
            $pricefield = $type;
            $costfield = $type.'_cost';
            $markupfield = $type.'_markup';
            break;
    }
    $tier = getDiscount('tier', $user_id,$form_id,$table_prefix);
    if (substr($tier, strlen($tier)-1,1) == '%') {
        $tier = substr($tier, 0, strlen($tier)-1);
    }
    if ($tier === '') {
        $tier = 0;
    }
    if (!is_numeric($tier)) {
        die("Only percent tiers are supported at this time. Please contact support to have them update your settings.");
    }
    // Check if item is exempt from Dealer Tier.
    if ($item['item_tier_override']) {
        $tier = 0;
    }
    // If a price is set, then we use that.
    if ($item[$pricefield]) {
        $price = $item[$pricefield];
        $price += $item[$costfield] * ($tier/100);
        return $price;
    }
    $markup = getDiscount('markup', $user_id,$form_id,$table_prefix);
    if ($markup === 0) {
        // If markup is blank, return above price field, even if it was 0
        if ($item[$markupfield] === '') {
            $price = $item[$pricefield];
            $price += $item[$costfield] * ($tier/100);
            return $price;
        }
        $markup = $item[$markupfield];
    }
    // If we don't have a cost field to work with... then we should return price.
    if ($item[$costfield] === ''|| is_null($item[$costfield])) {
        $price = $item[$pricefield];
        $price += $item[$costfield] * ($tier/100);
        return $price;
    }
    if (!is_numeric($item[$costfield])||!is_numeric($markup)) {
        $price = $item[$pricefield];
        $price += $item[$costfield] * ($tier/100);
        return $price;
    }
    // return item price after markup.
    $price = ($item[$costfield] * ($markup/100))+$item[$costfield];
    // Round up before applying tier markup
    $price = ceil($price);
    // Tier markups
    $price += $price * ($tier/100);
    // round result up.
    return ceil($price);
}

function add_user($user_id, $name, $address, $address2, $city, $state, $zip, $phone, $email) {
    $sql = "SELECT id
            FROM snapshot_users 
            WHERE last_name = '".mysql_real_escape_string($name)."'
              AND address = '".mysql_real_escape_string($address)."' 
              AND address2 = '".mysql_real_escape_string($address2)."'
              AND city = '".mysql_real_escape_string($city)."'
              AND state = '".mysql_real_escape_string($state)."'
              AND zip = '".mysql_real_escape_string($zip)."'
              AND phone = '".mysql_real_escape_string($phone)."'
              AND email = '".mysql_real_escape_string($email)."';";
    $query = mysql_query($sql);
    checkdberror($sql);
    if (mysql_num_rows($query) == 0) {
        $sql = "INSERT INTO snapshot_users
                  (orig_id, last_name, address, address2, city, state, zip, phone, email)
                VALUES (
                  '".mysql_real_escape_string($user_id)."',
                  '".mysql_real_escape_string($name)."',
                  '".mysql_real_escape_string($address)."',
                  '".mysql_real_escape_string($address2)."',
                  '".mysql_real_escape_string($city)."',
                  '".mysql_real_escape_string($state)."',
                  '".mysql_real_escape_string($zip)."',
                  '".mysql_real_escape_string($phone)."',
                  '".mysql_real_escape_string($email)."'
                );";
        $query = mysql_query($sql);
        checkdberror($sql);
        return mysql_insert_id();
    } else {
        $ret = mysql_fetch_assoc($query);
        return $ret['id'];
    }
}

/* Submit Order
	if $preview = false
	returns PO#
	else $preview = true
	returns array of order info
*/
/*
	items array elemnts should look like:
	array(
		'item_id' => item_id
		'setqty' => setqty
		'mattqty' => mattqty
		'qty' => qty
	MoS does not use this function...
*/
function submitOrder($user_id, $address = 1, $comments, $form, $items = array(), $preview = false, $pre_process = false, $modify_avail = true, $block_blocks = true, $customer = null, $shipto = null) {
	global $MoS_enabled;
	if (!is_numeric($user_id)) die('submitOrder: Non-Numeric User-ID');
	if (!is_numeric($address)) die('submitOrder: Non-Numeric Address-ID');
	if (!is_numeric($form)) die('submitOrder: Non-Numeric Form-ID');
	$po_insert = array();
	$po_insert['user'] = $user_id;
	$po_insert['form'] = $form;
	if ($MoS_enabled) {
		$sql = "SELECT * FROM MoS_director WHERE form_id = ".$form;
		$query = mysql_query($sql);
		checkDBerror($sql);
		if (mysql_num_rows($query) == 1) {
			//-- Change the ID to the one in MoS_director, in case it somehow changed
			$line = mysql_fetch_array($query, MYSQL_ASSOC);
			$ID = $line['MoS_form_id'];
			$table_prefix = "MoS_";
			$po_insert['snapshot_location'] = "MOS";
		}
		else {
			$table_prefix = "";
			$po_insert['snapshot_location'] = "PMD";
		}
		$po_insert['PMD_order_id'] = '0';
	} else {
		$table_prefix = "";
	}
	if ($pre_process) {
		$po_insert['processed'] = 'Y';
	} else {
		$po_insert['processed'] = 'N';
	}
	if (($form == 1003 || $form == 1007) && strtotime('2016-02-01') > time()) {
		$po_insert['ordered'] = date('Y-m-d ', strtotime('2016-02-01')).date('G:i');
	} else {
		$po_insert['ordered'] = date('Y-m-d G:i');
	}
	$po_insert['comments'] = $comments;
	$po_insert['deleted'] = 0;
	$po_insert['type'] = 'o';
	$po_insert['messages'] = array();
	$po_insert['user_address_num'] = $address;
	if ($address == '1') {
		$snapfield = "snapshot";
	} elseif ($address == '2') {
		$snapfield = "snapshot2";
	} else {
		$snapfield = "snapshot";
	}
	$sql = "select ".$snapfield.", first_name, last_name, address, city, state, zip from users where ID='".$user_id."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query, MYSQL_BOTH)) {
		$po_insert['user_firstname'] = $result['first_name'];
		$po_insert['user_lastname'] = $result['last_name'];
		$po_insert['user_address'] = $result['address'];
		$po_insert['user_city'] = $result['city'];
		$po_insert['user_state'] = $result['state'];
		$po_insert['user_zip'] = $result['zip'];
		$snapshot_user = $result[0];
	}
	$po_insert['snapshot_user'] = $snapshot_user ? $snapshot_user : 0;
	if (!is_null($customer)) {
		$po_insert['customer'] = $customer;
		$po_insert['customer_null'] = 'N';
	}
        if (!is_null($shipto)) {
		$po_insert['shipto'] = $shipto;
		$po_insert['shipto_null'] = 'N';
        }
	// Get snapshot form
	$sql = "SELECT mattratio, minimum, snapshot FROM " . $table_prefix . "forms WHERE ID = '".$form."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_assoc($query)) {
		$snapshot_form = $result['snapshot'];
                $minimum = $result['minimum'];
                $mattratio = $result['mattratio'];
	}
	$po_insert['snapshot_form'] = $snapshot_form ? $snapshot_form : 0;


	$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from " . $table_prefix . "snapshot_forms where id='".$snapshot_form."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		$po_insert['vendor_name'] = $result['name'];
		$po_insert['vendor_address'] = $result['address'];
		$po_insert['vendor_city'] = $result['city'];
		$po_insert['vendor_state'] = $result['state'];
		$po_insert['vendor_zip'] = $result['zip'];
		$po_insert['vendor_phone'] = $result['phone'];
		$po_insert['vendor_fax'] = $result['fax'];
		$po_insert['prepaidfreight'] = $result['prepaidfreight'];
	}

	$freight_master = getDiscount('freight', $user_id,$form,$table_prefix);
	$discount_master = getDiscount('discount', $user_id, $form,$table_prefix);

	$subtotal = 0;
        $totalcost = 0;
	$totalqty = 0;
	$itemdiscamount = 0;
        $itemfreightamount = 0;
	$total_cubic_ft = 0;
        $total_seats = 0;
	$out_of_stock = 0;
	$discount_total = 0; // Total twords % discount
        $freight_total = 0;
	$qtyordered = 0;
        $qtybackordered = 0;
        $totalmatts = 0;
        $totalsets = 0;
	// Process Items Here


	foreach ($items as $k => $item) {
		$total = 0;
                $cost = 0;
		$disc_total = 0;
                $fre_total = 0;
                $local_freight_total = 0;
                $local_discount_total = 0;
		$sql = "SELECT * FROM " . $table_prefix . "form_items WHERE ID=".$item['item_id'];
		$query = mysql_query($sql);
		checkDBError($sql);
		$result = mysql_fetch_array($query);
		if (($result['price'] == "" || is_null($result['price']))
                    && (
                        ($result['markup'] == "" || is_null($result['markup']))
                        || ($result['cost'] == "" || is_null($result['cost']))
                    )) {
                    $items[$k]['price'] = $result['box'];
                    $items[$k]['cost'] = $result['box_cost'];
                    $items[$k]['markup'] = $result['box_markup'];
                } else {
                    $items[$k]['price'] = $result['price'];
                    $items[$k]['cost'] = $result['cost'];
                    $items[$k]['markup'] = $result['markup'];
                    if ($result['price'] == "")
                        $items[$k]['price'] = 0;
                    if ($result['cost'] == "")
                        $items[$k]['cost'] = 0;
                }
		$items[$k]['cubic_ft'] = $result['cubic_ft'];
		$items[$k]['header'] = $result['header'];
		$items[$k]['partno'] = $result['partno'];
        $items[$k]['item_tier_override'] = $result['item_tier_override'];
		$items[$k]['description'] = $result['description'];
		$items[$k]['set'] = $result['set_'];
                $items[$k]['set_cost'] = $result['set_cost'];
                $items[$k]['set_markup'] = $result['set_markup'];
		$items[$k]['qtyinset'] = $result['setqty'];
                $items[$k]['seats'] = $result['seats'];
		$items[$k]['discount_tiers'] = loadDiscount('discount',array("item_id" => $result['ID']),$table_prefix."form_item");
                $items[$k]['freight_tiers'] = loadDiscount('freight',array("item_id" => $result['ID']),$table_prefix."form_item");
		$items[$k]['matt'] = $result['matt'];
                $items[$k]['matt_cost'] = $result['matt_cost'];
                $items[$k]['matt_markup'] = $result['matt_markup'];
		$items[$k]['alloc'] = $result['alloc'];
		$items[$k]['avail'] = $result['avail'];
		$items[$k]['stock'] = $result['stock'];
		if ($result['alloc'] == "" || $result['alloc'] < 0) {
			$items[$k]['alloc'] = -1;
			$items[$k]['avail'] = -1;
		}
		if ($items[$k]['snapshot_id']&&secure_is_admin()) {
			$sql = "SELECT a.id AS item_snap, b.id as header_snap, a.partno, a.description, a.price, a.cost, a.set_, a.set_cost, a.matt, a.matt_cost, a.box, a.box_cost, c.alloc, c.avail, c.stock, a.item_tier_override FROM snapshot_items AS a INNER JOIN ".$table_prefix."form_items AS c ON a.orig_id = c.ID INNER JOIN snapshot_headers AS b ON a.header = b.id WHERE a.id = '".$items[$k]['snapshot_id']."'";
			$query = mysql_query($sql);
			checkDBError($sql);
			if ($result = mysql_fetch_assoc($query)) {
				$items[$k]['partno'] = $result['partno'];
                $items[$k]['item_tier_override'] = $result['item_tier_override'];
				$items[$k]['description'] = $result['description'];
				if ($result['box'] != "") {
					$items[$k]['price'] = $result['box'];
                                        $items[$k]['cost'] = $result['box_cost'];
				} else {
					$items[$k]['price'] = $result['price'];
                                        $items[$k]['cost'] = $result['cost'];
					if ($result['price'] == "")
						$items[$k]['price'] = 0;
                                        if ($result['cost'] == "")
                                                $items[$k]['cost'] = 0;
				}
				$items[$k]['set'] = $result['set_'];
                                $items[$k]['set_cost'] = $result['set_cost'];
				$items[$k]['matt'] = $result['matt'];
                                $items[$k]['matt_cost'] = $result['matt_cost'];
				$items[$k]['item'] = $result['item_snap'];
				$items[$k]['header'] = $result['header_snap'];
				$items[$k]['alloc'] = $result['alloc'];
				$items[$k]['avail'] = $result['avail'];
				$items[$k]['stock'] = $result['stock'];
			}
		} else {
			$sql = "SELECT a.snapshot AS item_snap, b.snapshot AS header_snap, a.alloc, a.avail, a.stock FROM " . $table_prefix . "form_items AS a INNER JOIN " . $table_prefix . "form_headers AS b ON a.header = b.ID WHERE a.ID='".$item['item_id']."'";
			$query = mysql_query($sql);
			checkDBError($sql);
			if ($result = mysql_fetch_assoc($query)) {
				$items[$k]['item'] = $result['item_snap'];
				$items[$k]['header'] = $result['header_snap'];
				$items[$k]['alloc'] = $result['alloc'];
				$items[$k]['avail'] = $result['avail'];
				$items[$k]['stock'] = $result['stock'];
			}
		}

                // Work out prices based on markup.
                $items[$k]['price'] = calcPrice('box', $items[$k], $user_id, $form, $table_prefix);
                $items[$k]['setprice'] = calcPrice('set', $items[$k], $user_id, $form, $table_prefix);
                $items[$k]['mattprice'] = calcPrice('matt', $items[$k], $user_id, $form, $table_prefix);

		if ($items[$k]['qty'] != 0 && $items[$k]['avail'] >= 0 && $items[$k]['qty'] > $items[$k]['avail']) {
			++$out_of_stock;
			$items[$k]['suff_stock'] = false;
		} elseif ($items[$k]['qty'] == 0 && $items[$k]['backorder']) {
			$items[$k]['suff_stock'] = true;
		} elseif (stock_block($items[$k]['stock'])) {
			++$out_of_stock;
			$items[$k]['avail'] = 0;
			$items[$k]['suff_stock'] = false;
		} else {
			$items[$k]['suff_stock'] = true;
		}
                $item_totalqty = 0;
                // Qty Totaling
                if ($item['setqty'] != 0) {
                    $item_totalqty += $items[$k]['setqty'] * $items[$k]['qtyinset'];
                }
                if ($items[$k]['mattqty'] != 0) {
                    $item_totalqty += $items[$k]['mattqty'];
                }
                if ($items[$k]['qty'] != 0) {
                    $item_totalqty += $items[$k]['qty'];
                }
                if ($mattratio > 0) {
                    $totalsets += $items[$k]['setqty'];
                    $totalmatts += $items[$k]['mattqty'];
                }
                $items[$k]['discount'] = discountMatch(item_totalqty,$items[$k]['discount_tiers']);
                $items[$k]['freight'] = discountMatch(item_totalqty,$items[$k]['freight_tiers']);
		// Price Totaling! Yay!
		if ($item['setqty'] != 0) {
			$realprice = discountCalc($items[$k]['setprice'], $items[$k]['discount']); // calcitemdiscount($items[$k]['set'], $item_totalqty, $items[$k]['discount']);
			if ($realprice == $items[$k]['setprice']) {
				$disc_total += round($items[$k]['setprice'] * $items[$k]['setqty'], 2);
			} else {
				$local_discount_total += ($items[$k]['setprice'] - $realprice) * $items[$k]['setqty'];
				// Amount Discounted
			}
                        $realprice = discountCalc($items[$k]['setprice'], $items[$k]['freight']);
			if ($realprice == $items[$k]['setprice']) {
				$fre_total += round($items[$k]['setprice'] * $items[$k]['setqty'], 2);
			} else {
				$local_freight_total += ($items[$k]['setprice'] - $realprice) * $items[$k]['setqty'];
				// Amount Freighted
			}
			$total += round($items[$k]['setprice'] * $items[$k]['setqty'], 2);
                        //print_r(calcPrice('set', $items[$k], $user_id, $form, $table_prefix));
                        //die();
                        $cost += round($items[$k]['set_cost'] * $items[$k]['setqty'], 2);
			$totalqty += $items[$k]['setqty'] * $items[$k]['qtyinset'];
			$qtyordered += $items[$k]['setqty'];
		}
		if ($items[$k]['mattqty'] != 0) {
			$realprice = discountCalc($items[$k]['mattprice'], $items[$k]['discount']);
			if ($realprice == $items[$k]['mattprice']) {
				$disc_total += round($items[$k]['mattprice'] * $items[$k]['mattqty'], 2);
			} else {
				$local_discount_total += ($items[$k]['mattprice'] - $realprice) * $items[$k]['mattqty'];
				// Amount Discounted
			}
                        $realprice = discountCalc($items[$k]['mattprice'], $items[$k]['freight']);
                        if ($realprice == $items[$k]['mattprice']) {
				$fre_total += round($items[$k]['mattprice'] * $items[$k]['mattqty'], 2);
			} else {
				$local_freight_total += ($items[$k]['mattprice'] - $realprice) * $items[$k]['mattqty'];
				// Amount Freight
			}
			$total += round($items[$k]['mattprice'] * $items[$k]['mattqty'], 2);
                        $cost += round($items[$k]['matt_cost'] * $items[$k]['mattqty'], 2);
			$totalqty += $items[$k]['mattqty'];
			$qtyordered += $items[$k]['mattqty'];
		}
		if ($items[$k]['qty'] != 0) {
			$realprice = discountCalc($items[$k]['price'], $items[$k]['discount']);
			if ($realprice == $items[$k]['price']) {
				$disc_total += round($items[$k]['price'] * $items[$k]['qty'], 2);
			} else {
				$local_discount_total += ($items[$k]['price'] - $realprice) * $items[$k]['qty'];
				// Amount Discounted
			}
                        $realprice = discountCalc($items[$k]['price'], $items[$k]['freight']);
			if ($realprice == $items[$k]['price']) {
				$fre_total += round($items[$k]['price'] * $items[$k]['qty'], 2);
			} else {
				$local_freight_total += ($items[$k]['price'] - $realprice) * $items[$k]['qty'];
				// Amount Freight
			}
			$total += round($items[$k]['price'] * $items[$k]['qty'], 2);
                        $cost += round($items[$k]['cost'] * $items[$k]['qty'], 2);
			$totalqty += $items[$k]['qty'];
			$qtyordered += $items[$k]['qty'];
			$total_cubic_ft += round($items[$k]['cubic_ft'] * $items[$k]['qty'], 2);
                        $total_seats += round($items[$k]['seats'] * $items[$k]['qty'], 0);
		}
                $qtybackordered += $items[$k]['backorder'];
                $items[$k]['discount_percentage'] = $total == 0?0:round(($local_discount_total/$total)*100,2);
                $items[$k]['freight_percentage'] = $total == 0?0:round(($local_freight_total/$total)*100,2);
		$items[$k]['total'] = $total;
                $items[$k]['total_cost'] = $cost;
		// Insert Per Item Discount Here...
		$subtotal += $total;
                $totalcost += $cost;
                $itemdiscamount += $local_discount_total;
                $itemfreightamount += $local_freight_total;
                $items[$k]['discount_total'] = $disc_total;
                $items[$k]['freight_total'] = $fre_total;
		$discount_total += $disc_total;
                $freight_total += $fre_total;
	}

        // Figure discounts and fright by seat when items have seats, and by total when they have totals...
        $discount = 0;
        $freight = 0;
        foreach ($items as $k => $item) {
            $discount += calcDiscount($item['discount_total'], $totalqty, $discount_master, $item['seats']);
            $freight += calcDiscount($item['freight_total'], $totalqty, $freight_master, $item['seats']);
        }
        $discount = round($discount,2);
        $freight = round($freight,2);

	//$discount = round(calcDiscount($discount_total, $totalqty, $discount_master, $total_seats),2);
        $discount_percentage = $discount_total?($discount/$discount_total) * 100:0;
	$discount = $discount * -1;
        //$freight = round(calcDiscount($freight_total, $totalqty, $freight_master, $total_seats),2);
        $freight_percentage = $freight_total?($freight/$freight_total) * 100:0;
        $itemdiscamount = round($itemdiscamount,2) * -1;
        $itemfreightamount = round($itemfreightamount,2);
        $producttotal = $subtotal;
        $subtotal = $producttotal + $discount;
	$subtotal = $subtotal + $itemdiscamount;
	$grandtotal = $subtotal + $freight;
        $grandtotal = $grandtotal + $itemfreightamount;


//	$producttotal = $subtotal;
//	$discount = $discount_total * ($discount_percentage * .01);
//	if ($discount < 0)
//		$discount = $discount-$discount-$discount;
//	else
//		$discount = "-".$discount;
//	$freight = $subtotal * ($freight_percentage * .01);
//	$subtotal = $producttotal + $discount;
//	$subtotal = $subtotal - $itemdiscamount; // Remove Item Discounts from Subtotal
//	$grandtotal = $subtotal + $freight;
//
//	if ($freight == "-0") $freight = 0;
//	if ($discount == "-0") $discount = 0;

	$po_insert['product_total'] = $producttotal;
	$po_insert['subtotal'] = $subtotal;
	$po_insert['freight'] = $freight;
	$po_insert['discount'] = $discount;
        $po_insert['freight_percentage'] = $freight_percentage;
        $po_insert['discount_percentage'] = $discount_percentage;
	$po_insert['item_discount'] = $itemdiscamount;
        $po_insert['item_freight'] = $itemfreightamount;
	$po_insert['total'] = $grandtotal;
        $po_insert['totalcost'] = $totalcost;
        $po_insert['totalcost_null'] = 'N';
	$po_insert['totalqty'] = $totalqty;
	$po_insert['total_cubic_ft'] = $total_cubic_ft;
        $po_insert['total_seats'] = $total_seats;
	$po_insert['out_of_stock'] = $out_of_stock;

	if (!trim($po_insert['user_address'])) {
		$message = array();
		$message['text'] = 'You must have an address attached to your user before ordering. Contact dealer support.';
		$message['block'] = 'Y';
		$po_insert['messages']['noaddress'] = $message;
	}

	if ($qtyordered == 0 && $qtybackordered == 0) {
		$message = array();
		$message['text'] = "At least one item must be ordered to submit a PO, please go back ".
		  "and order an item.";
		$message['block'] = 'Y';
		$po_insert['messages']['noitems'] = $message;
	}
	if ($out_of_stock) {
		$message = array();
		$message['text'] = "One or more of the items in your order is insufficiently stocked. ".
		  "Please go back and reduce the quantities of those items.";
		$message['block'] = 'Y';
		$po_insert['messages']['nostock'] = $message;
	}
        if ($totalmatts > $mattratio * $totalsets) {
            $message = array();
            $message['text'] = "You have ordered more mattresses than allowed, please increase the ".
                "ratio of sets to mattresses to 1:".$mattratio." for your order.";
            $message['block'] = 'Y';
            $po_insert['messages']['toomanymatts'] = $message;
        }
	if (!$MoS_enabled) {
            if ($minimum) {
                $min_data = viewpo_getmin($minimum);
                //return array('text' => $minimum,
                //'type' => $min_type,
                //'minimum' => $raw_min,
                //'formatted' => $formatted);
                if ($min_data['type'] == 'D') {
                    if ($min_data['minimum'] > $producttotal) {
                        $message = array();
                        $message['text'] = "Your order did not meet the minimum order requirement of $".$min_data['minimum'].".";
                        $message['block'] = 'N';
                        $po_insert['messages']['minimum'] = $message;
                    }
                } elseif($min_data['type'] == 'P') {
                    if ($min_data['minimum'] > $totalqty) {
                        $message = array();
                        $message['text'] = "Your order did not meet the minimum order requirement of ".$min_data['minimum'].".";
                        $message['block'] = 'N';
                        $po_insert['messages']['minimum'] = $message;
                    }
                }
            }
		$credit_limit = creditAvail($user_id);
		if ($po_insert['total'] && $po_insert['total'] >= $credit_limit) {
			$message = array();
			$message['text'] = "This order will cause you to exceed your Order To Limit. ".
			  "Please make arrangements to make payment immediately. If you have questions regarding this message, ".
			  "please do not hesitate to contact Amy Bowen (614-203-6126). ".
			  "Thank You";
			$message['block'] = 'Y';
			$po_insert['messages']['creditlimit'] = $message;
		}
		$po_insert['credit_limit'] = $credit_limit;
	}

	// Preview, We aren't submitting an order! yay!
	if ($preview) {
		$po_insert['items'] = $items;
		return $po_insert;
	}

	if ($block_blocks) {
		foreach ($po_insert['messages'] as $message) {
			if (checkbox2boolean($message['block'])) {
				$po_insert['items'] = $items;
				return $po_insert;
			}
		}
	}

        // Only process an order if there is something to order, otherwise we leave it alone.
        if ($qtyordered != 0) {
            // From here on out, we're committing the order to database..
            // God Help Us All if this fails....
            $po_insert['user_address'] = $po_insert['user_address_num'];
            if ($MoS_enabled) {
                    $sql = buildInsertQuery("MoS_order_forms",$po_insert,true);
            } else {
                    $sql = buildInsertQuery("order_forms",$po_insert);
            }
            mysql_query($sql);
            checkDBError($sql);
            $po_id = mysql_insert_id();
            foreach ($items as $item) {
                    if ($item['qty'] == 0 && $item['backorder']) continue; // We're not ordering it
                    $item['user'] = $user_id;
                    $item['ordered'] = $po_insert['ordered'];
                    $item['form'] = $po_insert['form'];
                    $item['po_id'] = $po_id;
                    $item['ordered_time'] = date("H:i:s");
                    $item['snapshot_user'] = $po_insert['snapshot_user'];
                    $item['snapshot_form'] = $po_insert['snapshot_form'];
                    if ($modify_avail) {
                            if ($item['qty'] > 0) { // If it's negative, we don't want it to affect alloc
                                    if ($item['alloc'] != "" && $item['alloc'] >= 0) {
                                            if ($item['qty'] >= $item['avail'] && $item['stock'] == 1) {
                                                    $temp = stock_status(2);
                                                    $query2 = "";
                                                    if ($temp['zeroday'] == 'Y') {
                                                            $query2 .= " ,stock_day=0";
                                                    }
                                                    $query2 = " ,stock=2";
                                                    unset($temp);
                                            } else {
                                                    $query2 = "";
                                            }
                                            $sql = "UPDATE form_items SET avail=avail-".$item['qty']."${query2} WHERE ID=".$item['item_id'];
                                            mysql_query($sql);
                                            //echo $sql;
                                            checkDBError($sql);
                                    }
                            }
                    }
                    $item['setprice_null'] = true;
                    $item['mattprice_null'] = true;
                    $item['price_null'] = true;
                    if ($MoS_enabled) {
                            $sql = buildInsertQuery("MoS_orders",$item,true);
                    } else {
                            $sql = buildInsertQuery("orders",$item);
                    }
                    mysql_query($sql);
                    checkDBError($sql);
            }
            orderForWeb($po_id+1000, 'A', 'A');
            return $po_id+1000;
        } else {
            return false;
        }
}

/* Submit Edi Order
	if $preview = false
	returns PO#
	else $preview = true
	returns array of order info
*/
/*
	items array elemnts should look like:
	array(
		'item_id' => item_id
		'lineid' => PO line item number
		'setqty' => setqty
		'mattqty' => mattqty
		'qty' => qty
	MoS does not use this function...
*/

function submitCreditFee($user_id, $type, $comments, $total, $date = null) {
  global $BoL_enabled;
	if (is_null($date)) $date = time();
	if (!is_numeric($user_id)) die('submitCreditFee: Non-Numeric User-ID');
	if ($type != 'c' && $type != 'f') die('submitCreditFee: Invalid Type, For Orders use submitOrder');
	if (!is_numeric($total)) die('submitCreditFee: Non-Numeric Amount');
	if (!is_numeric($date)) die('submitCreditFee: Invalid Date Format, Use UNIX Epoch');
	$values = array();
	$values['ordered'] = date('Y-m-d G:i:s',$date);
	$values['user'] = $user_id;
	$values['type'] = $type;
	$sql = "SELECT snapshot FROM users WHERE ID = '".$user_id."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_assoc($query)) {
		$values['snapshot_user'] = $result['snapshot'];
	}
  if ($type=="c" && $total>=0)
      $total = $total-$total-$total; // make negative
  if ($type=="f")
      $total = abs($total);
	$values['total'] = $total;
	$values['comments'] = $comments;
	$values['processed'] = 'Y';
	$values['process_time'] = date('Y-m-d G:i:s');
	$sql = buildInsertQuery("order_forms", $values, true);
	mysql_query($sql);
	checkDBError($sql);
	$po_id = mysql_insert_id();
	$po = $po_id + 1000;
	if ($values['type'] == 'f') { // If it's a fee
		$sql = "SELECT email, email2, email3 FROM users WHERE ID=".$user_id;
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_array($query)) {
			$email = $result[0];
			$email2 = $result[1];
			$email3 = $result[2];
		} else {
			unset($email);
			unset($email2);
			unset($email3);
		}
		$msg =
		"This is a bill; please retain for your records.\n\n".

		"Do not reply to this e-mail, contact your Dealer Support Person for any issues associated with this Bill.\n\n".

		"-----------------------------------------------------------\n";

		$body = OrderForEmail($po,'D');
		$msg .= $body;

		$subject = date( "m/d/Y", $date)." Bill";
		$headers = "From: RSS Orders <orders@retailservicesystems.com>";
		if ($email2 <> "") $headers .= "\nCc: ".$email2;
		if ($email3 <> "") $headers .= "\nCc: ".$email3;
		$headers .= "\nBcc: orders@retailservicesystems.com";
		sendmail($email, $subject, $msg, $headers);
	}
	return $po;
}



function OrderForEmail($po, $for = 'U') {
	if ($for == 'U') {
		if (secure_is_vendor()) {
			$for = 'V';
		}
		if (secure_is_dealer()) {
			$for = 'D';
		}
		if (secure_is_admin()) {
			$for = 'A';
		}
	}
	global $MoS_enabled;
	$po_id = $po-1000;
	$sql = "SELECT form, user, type, ordered, total, comments, freight_percentage, discount_percentage, snapshot_user, snapshot_form, user_address FROM order_forms WHERE ID='$po_id'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($query) > 0) {
		$rows = mysql_fetch_array($query);
		$form = $rows["snapshot_form"];
		$user = $rows["snapshot_user"];
		$ordered = $rows['ordered'];
		$user_id = $rows['user'];
		$comments = $rows["comments"];
		$freight_percentage = $rows["freight_percentage"];
		$discount_percentage = $rows["discount_percentage"];
		$user_address = $rows['user_address'];
		$type = $rows['type'];
		$sgrandtotal = $rows['total'];
	}
	$msg = "";

	if ($MoS_enabled && $rows['snapshot_location'] == "MOS") {
		$table_prefix = "MoS_";
	}
	else {
		$table_prefix = "";
	}

	$msg .= "\r\nDEALER:  \r\n";
	if ($user_address == 2)
		$sql = "SELECT first_name, last_name, address, address2 city, state, zip, phone, fax FROM snapshot_users WHERE ID=$user AND secondary='Y'";
	else
		$sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax FROM snapshot_users WHERE ID=$user AND secondary='N'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		$msg .= $result['last_name'].", ".$result['first_name']." (".$user_id.")\r\n";
		if ($result['address'] != "")
			$msg .= $result['address']."\r\n";
			if ($result['address2']) $msg .= $result['address2']."\r\n";
			$msg .= $result['city'].", ".$result['state'].". ".$result['zip']."\r\n";
	}
	if ($type != 'f') { // If it's not a Bill/Fee
		$msg .= "\r\nVENDOR:  \r\n";
		$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from " . $table_prefix . "snapshot_forms where " . $table_prefix . "snapshot_forms.id='".$form."'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_Array($query)) {
			$msg .= $result['name']."\r\n";
			if ($for != 'D') {
				if ($result['address'] != "")
					$msg .= $result['address']."\r\n".$result['city'].", ".$result['state'].". ".$result['zip']."\r\n";
			}
			if ($result['name'] != "") $form_name = $result['name'];
		}
		$sql = 'SELECT DISTINCT a.setqty, a.mattqty, a.qty, a.setprice, a.mattprice, a.price as boxprice, b.partno, b.description, a.discount, a.freight, b.price, b.set_, b.matt, b.box, b.header, b.cubic_ft, b.setqty as qtyinset FROM '. ($MoS_enabled ? 'MoS_orders' : 'orders') .' as a INNER JOIN ' . $table_prefix . 'snapshot_items AS b ON b.id = a.item WHERE a.po_id=\''.$po_id.'\' ORDER BY b.header, b.display_order';
		// "DISTINCT" added 3/16/04. Somehow, duplicate items are occasionally being added to the snapshots table.
		// Since the cause of this can't be found, we'll fix the problem here.
		$query = mysql_query($sql);
		$subtotal = 0;
		$itemdiscamount = 0;
                $itemfreightamount = 0;
		$discount_total = 0;
                $freight_total = 0;
		$totalqty = 0;
		$oldheader = 0;
	} // End if not Bill/Fee
	$ponum = $po_id+1000;
	$msg .= "\r\nDATE: ".date("F j, Y",strtotime($ordered))."  PO#: $ponum \r\n\r\n";
	if ($type != 'f') { // If it's not a Bill/Fee
		while ($result = mysql_fetch_array($query))
		{
			if ($oldheader != $result['header']) {
				$oldheader = $result['header'];
				$msg .= "\r\n".getHeader($result['header'], $table_prefix)."\r\n";
			}

			$total = 0;
			$disc_total = 0;
                        $fre_total = 0;

                        if (!is_null($result['boxprice'])) {
                            $price = $result['boxprice'];
                        } else {
                            // Legacy Orders
                            if ($result['box'] != "")
                                    $price = $result['box'];
                            else {
                                    $price = $result['price'];
                                    if ($price == "")
                                            $price = 0;
                            }
                        }

			$price = str_replace("$", "", $price);
                        if (!is_null($result['setprice'])) {
                            $set = str_replace("$", "", $result['setprice']);
                        } else {
                            $set = str_replace("$", "", $result['set_']);
                        }

                        if (!is_null($result['mattprice'])) {
                            $matt = str_replace("$", "", $result['mattprice']);
                        } else {
                            $matt = str_replace("$", "", $result['matt']);
                        }

			$msg .= $result['description']." (".$result['partno'].")"."  ";

			$showbox = false;
			if($result['setqty'] != 0) { $msg .= " Set: ".$result['setqty']; $showbox = true; }
			if($result['setqty'] != 0) {
				$realprice = discountCalc($set, $result['discount']); //$set*(($result['discount_percentage']/100)+1);
				if ($realprice == $set) {
					$disc_total += round($set * $result['setqty'], 2);
				} else {
					$itemdiscamount += ($set - $realprice) * $result['setqty'];
					// Amount Discounted
				}
                                $realprice = $realprice = discountCalc($set, $result['freight']);//$set*(($result['freight_percentage']/100)+1);
				if ($realprice == $set) {
					$fre_total += round($set * $result['setqty'], 2);
				} else {
					$itemfreightamount += ($set - $realprice) * $result['setqty'];
					// Amount Freight
				}
                                if ($for != 'V')
                                    $msg .= " ".makeThisLookLikeMoney($set);
				$total += round($set * $result['setqty'], 2);
				$totalqty += $result['setqty'] * $result['qtyinset'];
			}

			if($result['mattqty'] != 0) { $msg .= " Matt: ".$result['mattqty']; $showbox = true;}
			if($result['mattqty'] != 0) {
				$realprice = discountCalc($matt, $result['discount']);// $realprice = $matt* (($result['discount_percentage']/100)+1);
				if ($realprice == $matt) {
					$disc_total += round($matt * $result['mattqty'], 2);
				} else {
					$itemdiscamount += ($matt - $realprice) * $result['mattqty'];
					// Amount Discounted
				}
                                $realprice = discountCalc($matt, $result['freight']);//$realprice = $matt* (($result['freight_percentage']/100)+1);
				if ($realprice == $matt) {
					$fre_total += round($matt * $result['mattqty'], 2);
				} else {
					$itemfreightamount += ($matt - $realprice) * $result['mattqty'];
					// Amount Freight
				}
                                if ($for != 'V')
                                    $msg .= " ".makeThisLookLikeMoney(round($matt * $result['mattqty'], 2));
				$total += round($matt * $result['mattqty'], 2);
				$totalqty += $result['mattqty'];
			}

			if($result['qty'] != 0) { if( $showbox ) $msg .= " Box: "; $msg .= $result['qty']; }
			if($result['qty'] != 0) {
				$realprice = discountCalc($price, $result['discount']); //$realprice = $price*(($result['discount_percentage']/100)+1);
				if ($realprice == $price) {
					$disc_total += round($price * $result['qty'], 2);
				} else {
					$itemdiscamount += ($price - $realprice) * $result['qty'];
					// Amount Discounted
				}
                                $realprice = discountCalc($price, $result['freight']); // $realprice = $price*(($result['freight_percentage']/100)+1);
				if ($realprice == $price) {
					$fre_total += round($price * $result['qty'], 2);
				} else {
					$itemfreightamount += ($price - $realprice) * $result['qty'];
					// Amount Freight
				}
				$msg .= " ".makeThisLookLikeMoney($price);
				$total += round($price * $result['qty'], 2);
				$totalqty += $result['qty'];
			}
			$subtotal += $total;
			$discount_total += $disc_total;
                        $freight_total += $fre_total;
			$msg .= "\r\n";
		}

		$producttotal = $subtotal;
		$discount = $discount_total * ($discount_percentage * .01);
		if ($discount < 0)
			$discount = $discount-$discount-$discount;
		else
			$discount = "-".$discount;
                $itemdiscamount = $itemdiscamount * -1;
		$freight = $subtotal * ($freight_percentage * .01);
		$subtotal = $producttotal + $discount;
		$subtotal = $subtotal + $itemdiscamount; // Remove Item Discounts from Subtotal
		$grandtotal = $subtotal + $freight + $itemfreightamount;

		//$producttotal = $subtotal;
		//$discount = $discount_total * ($discount_percentage * .01);
		//$discount = "-".$discount; //negative
		//$subtotal = $producttotal + $discount;
		//$freight = $subtotal * ($freight_percentage * .01);
		//$subtotal = $producttotal - $itemdiscamount;
		//$grandtotal = $subtotal + $freight;

		if ($freight == "-0") $freight = 0;
		if ($discount == "-0") $discount = 0;

		$msg .= "\r\nPieces: ".$totalqty;
                if ($for != 'V') {
                    $msg .= "\r\nProduct Total: ".makeThisLookLikeMoney($producttotal);
                    $msg .= "\r\nDiscount: ".makeThisLookLikeMoney($discount);
                    if ($itemdiscamount)
                        $msg .= "\r\nItem Discounts: ".makeThisLookLikeMoney($itemdiscamount);
                    $msg .= "\r\nSubtotal: ".makeThisLookLikeMoney($subtotal);
                    $msg .= "\r\nFreight: ".makeThisLookLikeMoney($freight);
                    if ($itemfreightamount)
                            $msg .= "\r\nItem Freight: ".makeThisLookLikeMoney($itemfreightamount);
                    $msg .= "\r\nGrand Total: ".makeThisLookLikeMoney($grandtotal);

                    if ($itemdiscamount) { $msg .= "* - Indicates Item Discount\r\n\r\n"; }
                }
	} else { // Is Fee/Bill
            if ($for != 'V')
		$msg .= "Total: ".makeThisLookLikeMoney($sgrandtotal);
	}
	if ($comments <> "") $msg .= "\r\nComments:\r\n$comments";

	return $msg;
}

function OrderForWeb($po, $section, $for = 'U') {
	if ($for == 'U') {
		if (secure_is_vendor()) {
			$for = 'V';

		}
		if (secure_is_dealer()) {
			$for = 'D';
		}
		if (secure_is_admin()) {
			$for = 'A';
		}
	}
	global $MoS_enabled;
	/* form-confirm.php is the only order display page that does not use this function because the order is
	not yet in the database. */

	if (!is_numeric($po)) {
		die("Invalid PO#".$po);
	}

	$po_id = $po-1000;
	$order_table = "";

	/* get basic order information and variables */
	if ($MoS_enabled) {
		$sql = "SELECT ordered, snapshot_user, comments, freight_percentage, discount_percentage, type, total, processed, process_time, deleted, user, snapshot_form, user_address, snapshot_location, form, customer, shipto, PMD_order_id FROM MoS_order_forms WHERE ID='".$po_id."'";
	} else {
		$sql = "SELECT ordered, snapshot_user, comments, freight_percentage, discount_percentage, type, total, processed, process_time, deleted, user, snapshot_form, user_address, customer, shipto, form FROM order_forms WHERE ID='".$po_id."'";
	}
	$query = mysql_query($sql);
	checkDBerror($sql);
	if (!mysql_num_rows($query)) {
		die("PO#".$po_id." Not Found");
	}
	$result = mysql_fetch_array($query);
	//ADD ERROR CHECK FOR NO ROWS RETURNED HERE
	if ($MoS_enabled && $result['snapshot_location'] == "MOS") {
		$table_prefix = "MoS_";
	}
	else {
		$table_prefix = "";
	}

	$form = $result['snapshot_form'];
	$orig_form = $result['form'];
	$comments = $result['comments'];
	$freight_percentage = $result['freight_percentage'];
	$discount_percentage = $result['discount_percentage'];
	$type = $result['type'];
	$total = $result['total'];
	$order_date = $result['ordered'];
	$processed = $result['processed'];
	$process_time = $result['process_time'];
	if ($process_time == "0000-00-00 00:00:00") $process_time = "";
	else $process_time = date("m/d/Y g:i A", strtotime($process_time));
	$user2 = $result['snapshot_user'];
	$user_id = $result['user'];
	$customer_id = $result['customer'];
	$shipto_id = $result['shipto'];
	$deleted = $result['deleted'];
	if ($MoS_enabled) $pmd_order_id = $result['PMD_order_id'];
	$user_address = $result['user_address'];
	if ($comments == "") $comments = "No additional comments were given.";

	/* get order time */
	$time =  date("g:i A", strtotime($result['ordered']));
	if ($time == "12:00 AM") $time = "";

	/* Self Heal from bad code (Dealer) */
	if ((!$MoS_enabled) && $user2 == NULL && $user_id) {
		$sql = "SELECT `snapshot`, `snapshot2` FROM `users` WHERE ID = '".$user_id."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		if ($user_address == 2) {
			$row['snapshot'] = $row['snapshot2'];
		}
		$sql = "UPDATE `orders` SET `snapshot_user` = '".$row['snapshot']."' WHERE po_id = '".$po_id."'";
		$user2 = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_user` = '".$row['snapshot']."' WHERE ID = '".$po_id."'";
		mysql_query($sql);
		checkdberror($sql);
	}

	/* Self Heal from bad code (Vendor) */
	if ((!$MoS_enabled) && $form == NULL && $orig_form) {
		$sql = "SELECT `snapshot` FROM `forms` WHERE ID = '".$orig_form."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		$sql = "UPDATE `orders` SET `snapshot_form` = '".$row['snapshot']."' WHERE po_id = '".$po_id."'";
		$form = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_form` = '".$row['snapshot']."' WHERE ID = '".$po_id."'";
		mysql_query($sql);
		checkdberror($sql);
	}

	$dealer_address = "";
	if ($user_address == 2)
		$sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax, email FROM snapshot_users WHERE ID='$user2' AND secondary='Y'";
	else
		$sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax, email FROM snapshot_users WHERE ID='$user2' AND secondary='N'";
	$query = mysql_query($sql);
	checkDBError($sql);
	$user_phone = '';
	if ($result = mysql_fetch_Array($query)) {
		$dealer_address = $result['last_name'].", ".$result['first_name']." <strong>(".$user_id.")</strong><br>";
		$dealer_name = $result['last_name'].", ".$result['first_name']." <strong>(".$user_id.")</strong><br>";
		if($result[2] != "") {
			$dealer_address .= $result[2]."<br>";
			if ($result[3]) $dealer_address .= $result[3]."<br>";
			$dealer_address .= $result[4].", ".$result[5].". ".$result[6]."<br>";
		}
		if($result['email'] != "") {
			$customer_address .= $result['email']."<br>";
		}
		if($result['phone'] != "") {
			$dealer_address .= "PH:".$result['phone']."<br>";
			$dealer_name .= "PH:".$result['phone']."<br>";
		}
		if($result['fax'] != "") {
			$dealer_address .= "FAX:".$result['fax'];
			$dealer_name .= "FAX:".$result['fax'];
		}
		$user_phone = $result['phone']; // For use later
	}

	/* Get Customer Address for CH */
	$customer_address = "";
	$sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax, email FROM snapshot_users WHERE ID='$customer_id'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_Array($query)) {
		$customer_address = $result['last_name'].", ".$result['first_name']."<br>";
		if($result[2] != "") {
			$customer_address .= $result[2]."<br>";
			if ($result[3]) $customer_address .= $result[3]."<br>";
			$customer_address .= $result[4].", ".$result[5].". ".$result[6]."<br>";
		}
		if($result['email'] != "") { $customer_address .= $result['email']."<br>"; }
		if($result['phone'] != "") { $customer_address .= "PH:".$result['phone']."<br>"; }
		if($result['fax'] != "") { $customer_address .= "FAX:".$result['fax']; }
	}

	/* Get Ship To Address */
	$shipto_address = "";
	$sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax, email FROM snapshot_users WHERE ID='$shipto_id'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_Array($query)) {
		$shipto_address = $result['last_name'].", ".$result['first_name']."<br>";
		if($result['address']) {
			$shipto_address .= $result['address']."<br>";
			if ($result['address2']) $shipto_address .= $result['address2']."<br>";
			$shipto_address .= $result['city'].", ".$result['state'].". ".$result['zip']."<br>";
		}
		if($result['email'] != "") { $shipto_address .= $result['email']."<br>"; }
		if($result['phone'] != "") { $shipto_address .= "PH:".$result['phone']."<br>"; }
		if($result['fax'] != "") { $shipto_address .= "FAX:".$result['fax']; }
	}

	/* get vendor address */
	$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from " . $table_prefix . "snapshot_forms as a where id='".$form."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	$vendor_address = "";
	$form_name = "";
	if($result = mysql_fetch_Array($query)) {
		if ($result['name'] != "") {
			$form_name = $result['name'];
			$vendor_address .= $result['name']."<br />";
		}
		if ($for != 'D') {
			if($result['address'] != "") { $vendor_address .= nl2br($result['address'])."<br>".$result['city'].", ".$result['state'].". ".$result['zip']."<br>"; }
			if($result['phone'] != "") { $vendor_address .= "PH:".$result['phone']."<br>"; }
			if($result['fax'] != "") { $vendor_address .= "FAX:".$result['fax']; }
		}
		if ($result['prepaidfreight'] == "Y")
			$vendor_address .= "<br><b>FREIGHT PREPAID</b>";
		else
			$vendor_address .= "<br><b>DRIVER COLLECT FREIGHT</b>";

	}

	if ($deleted == 1)
		$order_table .= "<p class=\"alert\" align=\"center\">THIS ORDER HAS BEEN DELETED</p>\n";

	/* order heading - dealer and vendor addresses, etc. */
	$order_table .= "<table width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\">
	  <tr>
		<td colspan=\"2\"><h1>";
		if ($type == "c")
			$order_table .= "Credit";
		elseif ($type == "f")
			$order_table .= "Bill";
		else {
			if ($form_name != '')
				$order_table .= $form_name;
		}
	$order_table .= "</h1></td>
	  </tr>
	  <tr valign=\"top\">
		<td width=\"50%\"> <p class=\"text_16\"><b>Dealer:</b><br>";
        if ($for != 'V' || !$shipto_address) {
        	$order_table .= $dealer_address;
        } else {
		$order_table .= $dealer_name;
	}
        $order_table .="</p></td>
		<td width=\"50%\"> ";
		if (($type <> "c") && ($type <> "f"))
			$order_table .= "<p class=\"text_16\"><b>Vendor:</b><br>$vendor_address</p>";
	$order_table .= "</td>
	  </tr>";
	if ($shipto_address || $customer_address) {
		$order_table .= '<tr>
			<td width="50%"> <p class="text_16"><b>';
		if ($customer_address) $order_table .= 'Customer:';
		$order_table .= '</b><br>'.$customer_address.'</p></td>
			<td width="50%"> <p class="text_16"><b>';
		if ($shipto_address) $order_table .= 'Ship To:';
		$order_table .= '</b><br>'.$shipto_address.'</p></td>
		</tr>';
	}
	$order_table .= "</table>\n";
	/* end order heading */

	if ($type == "o") {         // We want it on all views now && (($section == "admin")||(!secure_is_dealer()))) {
		if ($user_phone != "")
			$order_table .= '<h2 align="center">Call '.$user_phone.' two hours before arrival of delivery</h2>';
	}

	$order_table .= "<h3 align=\"center\"><b>".($MoS_enabled ? 'Market PO' : 'PO')."#: $po &nbsp;&nbsp;&nbsp;";
	if ($MoS_enabled && $pmd_order_id) $order_table .= "RSS PO#: ".($pmd_order_id + 1000)." &nbsp;&nbsp;&nbsp;";
	$order_table .= "Date: ".date("m/d/Y", strtotime($order_date));
	if ($time) {
		$order_table .= "&nbsp;&nbsp;&nbsp;Time: ".$time;
	}
	$order_table .= "</h3>";

	if (($type == "c") || ($type == "f")) {
		$order_table .= "<table width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\">
		  <tr>
			<td width=\"75%\" class=\"orderTH\">Comments</td>
			<td width=\"25%\" class=\"orderTH\">Total</td>
		  </tr>
		  <tr>
			<td width=\"75%\" class=\"text_12\">".nl2br($comments)."</td>";
                if ($for != 'V')
                    $order_table .= "
			<td width=\"25%\" class=\"text_12\"><b>".makeThisLookLikeMoney($total)."</b></td>";
                $order_table .= "
		  </tr>
		</table>";
	}
	else {

		//if ($processed == "Y")
		//	$order_table .= "<h2 align=\"center\">ORDER PROCESSED<br><span class=\"text_12\">$process_time</span></h2>";
		//else
		//	$order_table .= "<h2 align=\"center\">ORDER</h2>";

		$order_table .= "<table width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\">
		  <tr>
			<td width=\"25%\" colspan=\"2\" class=\"orderTH\">Item</td>
			<td width=\"20%\" colspan=\"2\" class=\"orderTH\">Set</td>
			<td width=\"20%\" colspan=\"2\" class=\"orderTH\">Matt</td>
			<td width=\"20%\" colspan=\"2\" class=\"orderTH\">Box</td>
			<td width=\"15%\" class=\"orderTH\" align=\"right\">Total</td>
		  </tr>";

		$subtotal = 0;
		$itemdiscamount = 0;
                $itemfreightamount = 0;
		$totalqty = 0;
		$total_cubic_ft = 0;
                $total_seats = 0;
		$total_weight = 0;
		$oldheader = "";

                if ($for == 'V') {
                    $sql = 'SELECT DISTINCT a.setqty, a.mattqty, a.qty, b.partno, b.description, a.discount, a.freight, b.cost AS price, b.set_cost AS setprice, b.matt_cost AS mattprice, b.box_cost as boxprice, b.header, b.cubic_ft, b.seats, b.weight, b.setqty as qtyinset FROM '. ($MoS_enabled ? 'MoS_orders' : 'orders') .' as a INNER JOIN ' . $table_prefix . 'snapshot_items AS b ON b.id = a.item WHERE a.po_id=\''.$po_id.'\' ORDER BY b.header, b.display_order';
                } else {
                    $sql = 'SELECT DISTINCT a.setqty, a.mattqty, a.qty, a.setprice, a.mattprice, a.price as boxprice, b.partno, b.description, a.discount, a.freight, b.price, b.set_, b.matt, b.box, b.header, b.cubic_ft, b.seats, b.weight, b.setqty as qtyinset FROM '. ($MoS_enabled ? 'MoS_orders' : 'orders') .' as a INNER JOIN ' . $table_prefix . 'snapshot_items AS b ON b.id = a.item WHERE a.po_id=\''.$po_id.'\' ORDER BY b.header, b.display_order';
                }

		// "DISTINCT" added 3/16/04. Somehow, duplicate items are occasionally being added to the snapshots table.
		// Since the cause of this can't be found, we'll fix the problem here.
		$query = mysql_query($sql);
		checkdberror($sql);
		while($result = mysql_fetch_array($query)) {

			if ($oldheader != $result['header']) {
				$oldheader = $result['header'];
				$order_table .= "<tr><td colspan=\"9\" class=\"orderTDheading\"><b>".getHeader($result['header'], $table_prefix)."</b></td></tr>";
			}

			if (!is_null($result['boxprice'])) {
                            $price = $result['boxprice'];
                        } else {
                            // Legacy Orders
                            if ($result['box'] != "")
                                    $price = $result['box'];
                            else {
                                    $price = $result['price'];
                                    if ($price == "")
                                            $price = 0;
                            }
                        }

			$price = str_replace("$", "", $price);
                        if (!is_null($result['setprice'])) {
                            $set = str_replace("$", "", $result['setprice']);
                        } else {
                            $set = str_replace("$", "", $result['set_']);
                        }

                        if (!is_null($result['mattprice'])) {
                            $matt = str_replace("$", "", $result['mattprice']);
                        } else {
                            $matt = str_replace("$", "", $result['matt']);
                        }
			$total = 0;
			$disc_total = 0;
                        $fre_total = 0;
			$showbox = false;

			$order_table .= "<tr valign=\"top\">
				<td width=\"10%\" class=\"orderTD\">";
			if ($result['discount_percentage']) $order_table .= "*";
			$order_table .= $result['partno']."&nbsp;</td>
				<td width=\"15%\" class=\"orderTD\">".$result['description']."&nbsp;</td>";

			if ($result['setqty'] != 0) {
				$showbox = true;
                                $realprice = discountCalc($set, $result['discount']);//$set*(($result['discount_percentage']/100)+1);
				if ($realprice == $set) {
					$disc_total += round($set * $result['setqty'], 2);
				} else {
					$itemdiscamount += ($set - $realprice) * $result['setqty'];
					// Amount Discounted
				}
                                $realprice = discountCalc($set, $result['freight']); //$realprice = $set*(($result['freight_percentage']/100)+1);
				if ($realprice == $set) {
					$fre_total += round($set * $result['setqty'], 2);
				} else {
					$itemfreightamount += ($realprice - $set) * $result['setqty'];
					// Amount Freight
				}
				$total += round($set * $result['setqty'], 2);
				$totalqty += $result['setqty'] * $result['qtyinset'];
				$order_table .= "<td width=\"5%\" class=\"orderTD\">Set:&nbsp;".$result['setqty']."&nbsp;</td>
					<td width=\"15%\" align=\"right\" class=\"orderTD\">".makeThisLookLikeMoney($set)."&nbsp;</td>";
			}
			else {
				$order_table .= "<td colspan=\"2\" class=\"orderTD\">&nbsp;</td>";
			}

			if ($result['mattqty'] != 0) {
				$showbox = true;
                                $realprice = discountCalc($matt, $result['discount']); //$realprice = $matt*(($result['discount_percentage']/100)+1);
				if ($realprice == $matt) {
					$disc_total += round($matt * $result['mattqty'], 2);
				} else {
					$itemdiscamount += ($matt - $realprice) * $result['mattqty'];
					// Amount Discounted
				}
                                $realprice = discountCalc($matt, $result['freight']); //$realprice = $matt*(($result['freight_percentage']/100)+1);
				if ($realprice == $matt) {
					$fre_total += round($matt * $result['mattqty'], 2);
				} else {
					$itemfreightamount += ($realprice - $matt) * $result['mattqty'];
					// Amount Freight
				}
				$total += round($matt * $result['mattqty'], 2);
				$totalqty += $result['mattqty'];
				$total_weight += $result['weight']*$result['mattqty'];
				$order_table .= "<td width=\"5%\" class=\"orderTD\">Matt:&nbsp;".$result['mattqty']."&nbsp;</td>
					<td width=\"15%\" align=\"right\" class=\"orderTD\">".makeThisLookLikeMoney($matt)."&nbsp;</td>";
			}
			else {
				$order_table .= "<td colspan=\"2\" class=\"orderTD\">&nbsp;</td>";
			}

			$order_table .= "<td width=\"5%\" class=\"orderTD\">";
			if ($result['qty'] != 0) {
				if ($showbox) $order_table .= "Box:&nbsp;";
				$order_table .= $result['qty'];
			}
			$order_table .= "&nbsp;</td>
				<td width=\"15%\" align=\"right\" class=\"orderTD\">";
			if( $result['qty'] != 0 ) {
				$order_table .= ($for == 'V'?'&nbsp;':makeThisLookLikeMoney($price));
                                $realprice = discountCalc($price, $result['discount']);;
				if ($realprice == $price) {
					$disc_total += round($price * $result['qty'], 2);
				} else {
					$itemdiscamount += ($price - $realprice) * $result['qty'];
					// Amount Discounted
				}
                                $realprice = discountCalc($price, $result['freight']);
				if ($realprice == $price) {
					$fre_total += round($price * $result['qty'], 2);
				} else {
					$itemfreightamount += ($realprice - $price) * $result['qty'];
					// Amount Freight
				}
				$total += round($price * $result['qty'], 2);
				$totalqty += $result['qty'];
				$total_weight += $result['qty'] * $result['weight'];
				$total_cubic_ft += round($result['cubic_ft'] * $result['qty'], 2);
                                $total_seats += round($result['seats'] * $result['qty'], 0);
			}
			$order_table .= "&nbsp;</td>
				<td width=\"15%\" align=\"right\" class=\"orderTD\"><b>".makeThisLookLikeMoney($total)."</b></td>
				</tr>";
			$discount_total += $disc_total;
                        $freight_total += $fre_total;
			$subtotal += $total;
		} /* end while */

		$producttotal = $subtotal;
                if ($type == 'V') {
                    $discount = 0;
                    $itemdiscamount = 0;
                    $frieght = 0;
                } else {
                    $discount = $discount_total * ($discount_percentage * .01);
                    $discount = $discount * -1;
                    $itemdiscamount = $itemdiscamount * -1;
                    //$itemdiscamount = $itemdiscamount * -1;
                    $freight = $freight_total * ($freight_percentage * .01);
                }
		$subtotal = $producttotal + $discount;
		$subtotal = $subtotal + $itemdiscamount; // Apply Item Discounts to Subtotal
		$grandtotal = $subtotal + $freight + $itemfreightamount;

		//$producttotal = $subtotal;
		//$discount = $discount_total * ($discount_percentage * .01);
		//if ($discount < 0)
		//	$discount = $discount-$discount-$discount;
		//else
		//	$discount = "-".$discount;

		//$subtotal = $producttotal + $discount;
		//$freight = $subtotal * ($freight_percentage * .01);
		//$subtotal = $subtotal - $itemdiscamount;
		//$grandtotal = $subtotal + $freight;

		if ($freight == "-0") $freight = 0;
		if ($discount == "-0") $discount = 0;

		$sqlTotalPayments = mysql_query("SELECT SUM(payment_amount) as totalPayment from po_payments WHERE po_id = '".$po_id."' AND payment_status='approved';");
		$totalPayments = mysql_fetch_row($sqlTotalPayments);
		$totalPayments = $totalPayments[0];
		$totalDue = $grandtotal - $totalPayments;
		
		if ($totalDue > 0) { $outstandingClass = "balanceOwed"; } else { $outstandingClass = "balancePaid"; }

		$order_table .= "
		  <tr>
		    <td colspan=\"8\" align=\"right\" class=\"text_12\">Pieces:</td>
			<td class=\"text_12\" align=\"right\">".$totalqty."</td>
		  </tr>
		  <tr>
			<td colspan=\"8\" align=\"right\" class=\"text_12\">Approximate Volume:</td>
			<td class=\"text_12\" align=\"right\">".$total_cubic_ft." cu. ft.</td>
		  </tr>";
                if ($total_seats > 0) {
                    $order_table .= "
		  <tr>
			<td colspan=\"8\" align=\"right\" class=\"text_12\">Seats:</td>
			<td class=\"text_12\" align=\"right\">".$total_seats."</td>
		  </tr>";
                }
                $order_table .= "
		  <tr>
		  	<td colspan=\"8\" align=\"right\" class=\"text_12\">Approximate Weight:</td>
		  	<td class=\"text_12\" align=\"right\">".$total_weight." lbs.</td>
                  </tr>";
                if ($for == 'V') {
                    $order_table .= "
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">RSS Cost Total:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($producttotal)."</td>
                      </tr>";
                } else {
                    $order_table .= "
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Product Total:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($producttotal)."</td>
                      </tr>
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Discount:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($discount)."</td>
                      </tr>";
                    if ($itemdiscamount) {
                            $order_table .= "
                              <tr>
                                    <td colspan=\"8\" align=\"right\" class=\"text_12\">Item Discounts:</td>
                                    <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($itemdiscamount)."</td>
                              </tr>";
                    }
                    $order_table .= "
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Subtotal:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($subtotal)."</td>
                      </tr>
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Freight:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($freight)."</td>
                      </tr>";
                    if ($itemfreightamount) {
                            $order_table .= "
                              <tr>
                                    <td colspan=\"8\" align=\"right\" class=\"text_12\">Item Freight:</td>
                                    <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($itemfreightamount)."</td>
                              </tr>";
                    }
                    
                    $order_table .= "
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Grand Total:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($grandtotal)."</td>
                      </tr>
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12\">Total Payments:</td>
                            <td class=\"text_12\" align=\"right\">".makeThisLookLikeMoney($totalPayments)."</td>
                      </tr>
                      <tr>
                            <td colspan=\"8\" align=\"right\" class=\"text_12 ".$outstandingClass."\"><b>Outstanding Balance:</b></td>
                            <td class=\"text_12 ".$outstandingClass."\" align=\"right\"><b>".makeThisLookLikeMoney($totalDue)."</b></td>
                      </tr>
                      <tr>
                            <td colspan=\"9\">&nbsp;</td>
                      </tr>";
                    if ($itemdiscamount) { $order_table .= "
                      <tr>
                            <td colspan=\"9\" class='fat_black_12'>* - Indicates Item Discount</td>
                      </tr>
                    "; }
                }
		$order_table .= "
		  <tr>
			<td colspan=\"9\" class=\"orderTH\"><b>Comments</b></td>
		  </tr>
		  <tr>
			<td colspan=\"9\"><h3>";
		$order_table .= nl2br($comments);
		$order_table .= "</h3></td>
		  </tr>";
		if ($MoS_enabled && ($processed == "N") && ($section == "admin")) {
			/* BEGIN CREDIT CHECK */
			//$credit = implode('', FILE("http://ext.pmddealer.com/MoS/MoS_get_credit.php?hvar=valid&user=$user_id"));
			$ch = curl_init($GLOBALS['MoS_MasterPath'] . "/MoS_get_credit.php?hvar=valid&user=$user_id");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$credit = curl_exec($ch);
			curl_close($ch);
			$credit = explode("||", trim(trim($credit, "\n")));
			if (count($credit) == 2) {
				$credit_string = "<TABLE><TR align=right><TD>Credit Limit: </TD><TD><B>" . makeThisLookLikeMoney($credit[0]) . "</B></TD></TR>" .
									"<TR align=right><TD>Balance: </TD><TD><B>" . makeThisLookLikeMoney($credit[1]) . "</B></TD></TR></TABLE>";
				if ($credit[1] >= $credit[0]) {
					$credit_string .= "<p class=\"alert\">The credit limit has been exceeded</p>";
				}
			}
			else {
				$credit_string = "<B>Could not establish credit at this time.</B>";
			}
			$order_table .= "<tr>
				<td colspan=\"9\" align=\"right\" class=\"text_12\">$credit_string
				<form action=\"MoS_report-orders-process.php\" method=\"post\" onsubmit=\"\">
				<input type=\"hidden\" name=\"action\" value=\"A\">
				<input type=\"hidden\" name=\"ordered\" value=\"".$_GET["ordered"]."\">
				<input type=\"hidden\" name=\"ordered2\" value=\"".$_GET["ordered2"]."\">
				<input type=\"hidden\" name=\"date\" value=\"$date\">
				<input type=\"hidden\" name=\"po\" value=\"$po\">
				<input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">
				<input type=\"submit\" value=\"Approve Order\">
				</form></td>
				</tr>";
		}
		if (($processed == "N") && ($section == "admin")) {
			$order_table .= "<tr> \n";
			$order_table .= "	<td colspan=\"9\" align=\"right\" class=\"text_12\">\n";
			if ($MoS_enabled)
				$order_table .= "	<form action=\"MoS_report-orders-process.php\" method=\"post\" onsubmit=\"\">\n";
			else
				$order_table .= "	<form action=\"report-orders-process.php\" method=\"post\" onsubmit=\"\">\n";
			if ($MoS_enabled) $order_table .= "	<input type=\"hidden\" name=\"action\" value=\"D\">\n";
			$order_table .= "	<input type=\"hidden\" name=\"ordered\" value=\"".$_GET["ordered"]."\">\n";
			$order_table .= "	<input type=\"hidden\" name=\"ordered2\" value=\"".$_GET["ordered2"]."\">\n";
			$order_table .= "	<input type=\"hidden\" name=\"date\" value=\"$date\">\n";
			$order_table .= "	<input type=\"hidden\" name=\"po\" value=\"$po\">\n";
			$order_table .= "	<input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">\n";
			if ($MoS_enabled)
				$order_table .= "	<input type=\"submit\" value=\"Disapprove Order\">\n";
			else
				$order_table .= "	<input type=\"submit\" value=\"Process Order\">\n";
			$order_table .= "	</form></td>\n";
			$order_table .= "	</tr>";
		}
		if (!$MoS_enabled) {
			$order_table .= OpenOrderDisplay($po_id);
			$order_table .= ShippingDisplay($po_id);

			// Check for an associated shipping system credit request; if it exists, display a link
			$shipping_sql = "SELECT ID FROM BoL_forms WHERE credit_po = $po";
			$shipping_query = mysql_query($shipping_sql);
			while($shipping_result = mysql_fetch_assoc($shipping_query)) {
				 $order_table .= "<div style=\"noprint\"><tr>
				   <td colspan=\"9\" align=\"center\"><a href=\"/shipping/viewcredit.php?id={$shipping_result['ID']
				   }\">View Shipping Credit Request</a></td></tr></div>";
			}
		}

		if ($section == "admin") {
			$order_table .= "<tr>
			   <td colspan=\"9\" class=\"orderTH\"><hr></td>
		    </tr>";
			$order_table .= "<tr><td colspan=\"9\" align=\"center\">";
			$order_table .= "<font size=\"4\"><b>Confirm at https://login.retailservicesystems.com/vendor.php</b></font>";
			$order_table .= "</td></tr>";
		}
		$order_table .= "</table>";
		$order_table .= "<div id='grandTotal' style='display:none;'>".$totalDue."</div>";

	}

	return $order_table;
} /* end OrderForWeb */

function getEmailHeaders($from_name, $from_email, $email2, $bcc_email) {
	/* headers need to be in the correct order... */
	$headers = "From: $from_name <$from_email>\n";
	if ($email2 <> "") $headers .= "Cc: $email2\n";
	$headers .= "Reply-To: <$from_email>\n";
        if ($bcc_email) {
		$headers .= "Bcc: <$bcc_email>\n";
	}
	$headers .= "MIME-Version: 1.0\n";
	/* the following must be one line (post width too small) */
	$headers .= "Content-Type: multipart/related;type=\"multipart/alternative\"; boundary=\"----=MIME_BOUNDRY_main_message\"\n";
	$headers .= "X-Sender: $from_name<$from_email>\n";
	$headers .= "X-Mailer: PHP4\n"; //mailer
	$headers .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
	$headers .= "Return-Path: <$from_email>\n";
	$headers .= "Link: <https://login.retailservicesystems.com/styles.css>; rel=\"stylesheet\"\n";
	$headers .= "This is a multi-part message in MIME format.\n";
	$headers .= "------=MIME_BOUNDRY_main_message \n";
	$headers .= "Content-Type: multipart/alternative; boundary=\"----=MIME_BOUNDRY_message_parts\"\n";
	return $headers;
}

function getEmailMessage($msg_text, $msg_html) {
	/* plaintext section begins */
	$message = "------=MIME_BOUNDRY_message_parts\n";
	$message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
	$message .= "Content-Transfer-Encoding: quoted-printable\n";
	$message .= "Content-ID: RSSmsg\n"; //addition
	$message .= "\n";
	$message .= "$msg_text \n";
	$message .= "\n";
	/* html section begins */
	$message .= "------=MIME_BOUNDRY_message_parts\n";
	$message .= "Content-Type: text/html; charset=iso-8859-1\n";
	$message .= "Content-Transfer-Encoding: quoted-printable\n";
	$message .= "Content-ID: RSShtmlmsg\n"; //addition
	$message .= "\n";
	/* html message begins */
	$message .= "<link rel=3D\"stylesheet\" href=3D\"https://login.retailservicesystems.com/styles.css\" type=3D\"text/css\">";
	$message .= "$msg_html \n";
	/* html message ends */
	$message .= "\n";
	/* this ends the message part */
	$message .= "------=MIME_BOUNDRY_message_parts--\n";
	$message .= "\n";
	/* message ends */
	$message .= "------=MIME_BOUNDRY_main_message--\n";
	return $message;
}

function viewpo_vendorowner($po) {
	// Checking to make sure we have valid input.
	if (!is_numeric($po)) {
		die("Not a numeric PO#!");
	}
	$po_id = $po-1000;
	$query = mysql_query("SELECT form FROM order_forms WHERE ID='$po_id'");
	if (mysql_num_rows($query) == 0) {
		return 0;
	}
	$result = mysql_fetch_array($query);
	$sql = "select vendors.ID from forms left join vendors on vendors.ID=forms.vendor where forms.ID=".$result['form'];
	$query = mysql_query($sql);
	if (mysql_num_rows($query) == 0) {
		return 0;
	}
	$result = mysql_fetch_array($query);
	return $result['ID'];
}

function viewpo_dealerowner($po) {
	// Checking to make sure we have valid input.
	if (!is_numeric($po)) {
		die("Not a numeric PO#!");
	}
	$po_id = $po-1000;
	$query = mysql_query("SELECT user FROM order_forms WHERE ID='$po_id'");
	if (mysql_num_rows($query) == 0) {
		return 0;
	}
	$result = mysql_fetch_array($query);
	return $result['user'];
}

function markpoprinted($po) {
	if (!is_numeric($po)) {
		die("Not a numeric PO#!");
	}
	$po_id = $po - 1000;
	$sql = "UPDATE `order_forms` SET `printedonsummary` = 'Y' WHERE `ID` = '".$po_id."'";
	mysql_query($sql);
	checkDBerror($sql);
}
?>
