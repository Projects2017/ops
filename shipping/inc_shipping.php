<?php
// inc_shipping.php
// required shipping functions, specifically for the freight & credits

// adding the Mozilla printing thing here
$firefoxprint = '';
if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox'))
{
	// we're in Firefox
	// Pop in the object code
	$firefoxprint = "<object ID=\"PMDPrint\" TYPE=\"application/x-itst-activex\" clsid=\"{9CF0975F-43DB-3307-83FF-8E73172C723C}\"></object>\n";
}
else
{
	$firefoxprint = $_SERVER['HTTP_USER_AGENT'];
}

function queryFailOnZero($sql, $error)
{
	// function to run a db query and write an error if there are zero rows returned
	$resource = @mysql_query($sql);
	checkDBerror($sql);
	if(mysql_num_rows($resource)<1)
	{
		// there was a problem with the query as entered
		setcookie('BoL_msg', $error);
		header('Location: shipping.php');
	}
	return $resource;
}

function sendError($wwwDesc, $emailDesc, $output, $redirect) {
	setcookie('BoL_msg', 'There was a problem '.$wwwDesc.'. The system administrator has been notified. We are sorry for the inconvenience.');
	ini_set(sendmail_from, 'Shipping Queue Daemon <noreply@retailservicesystems.com>');
	sendmail('Shipping DBA <will@retailservicesystems.com>', 'Shipping Queue Daemon Error - '.$emailDesc, $output);
	header('Location: '.$redirect);
	exit();
}

function getVendorname($formid) {
	$origid_sql = "SELECT orig_id FROM snapshot_forms WHERE id = $formid";
	$vendor_sql = "SELECT vendor FROM forms WHERE ID IN ($origid_sql)";
	$vendorname_sql = "SELECT name AS vendorname FROM vendors WHERE ID in ($vendor_sql)";
	$query = mysql_query($vendorname_sql);
	checkdberror($vendorname_sql);
	$result = mysql_fetch_assoc($query);
	return $result['vendorname'];
}

function getVendorNames() {
	$vendors = getVendorInfo();
	$vendor_names = array();
	foreach($vendors as $vendor_list) {
		if(!in_array($vendor_list['vname'], $vendor_names)){
			$vendor_names[] = $vendor_list['vname'];
		}
	}
	return $vendor_names;
}

function getVendorNameIds() {
	$vendors = getVendorInfo();
	$vendor_names = array();
	foreach($vendors as $vendor_list) {
		if(!in_array($vendor_list['vname'], $vendor_names)){
			$vendor_names[$vendor_list['vid']] = $vendor_list['vname'];
		}
	}
	return $vendor_names;
}

// getShippingData(po)
// returns carrier[], tracking[] & shipdate[] info on the specified PO
function getShippingData($po_id)
{
	$return = array();
	//$sql = "SELECT carrier, trackingnum AS tracking, shipdate FROM BoL_forms WHERE ID IN (SELECT DISTINCT bol_id FROM BoL_items WHERE po = $po_id)";
	$sql = "SELECT trackingnum AS tracking FROM BoL_forms WHERE ID IN (SELECT DISTINCT bol_id FROM BoL_items WHERE po = $po_id)";
	$que = mysql_query($sql);
	checkdberror($sql);
	while($res = mysql_fetch_assoc($que))
	{
		$return[] = $res;
	}
	foreach($return as $rec)
	{
		//$carrier[] = $rec['carrier'] ? $rec['carrier'] : '[None entered]';
		$tracking[]= $rec['tracking'];
		//$shipdate[]= $rec['shipdate'] ? $rec['shipdate'] : '[Unknown date]';
	}
	if($carrier || $tracking || $shipdate)
	{
		//return array('carrier' => $carrier, 'tracking' => $tracking, 'shipdate' => $shipdate);
		return $tracking;
	}
	else
	{
		return;
	}
}

function getVendorInfo() {
	if(secure_is_vendor()) { // if they are a vendor...
                global $vendorid;
		$vendorsql = "SELECT DISTINCT forms.ID, vendors.ID as vid, forms.name as fname, vendors.name as vname FROM forms INNER JOIN vendors ON vendors.ID = forms.vendor WHERE forms.vendor IN (SELECT vendor FROM vlogin_access WHERE user = '$vendorid') ORDER BY vendors.name, forms.name";
		$query = mysql_query($vendorsql);
		checkdberror($vendorsql);
		while ($result = mysql_fetch_assoc($query)) {
			$vendors[] = $result;
		}
	} else { // and if they are not a vendor
		$vendorsql = "SELECT DISTINCT forms.ID, vendors.ID as vid, forms.name as fname, vendors.name as vname FROM forms INNER JOIN vendors ON vendors.ID = forms.vendor ORDER BY vendors.name, forms.name";
		$query = mysql_query($vendorsql);
		checkdberror($vendorsql);
		//$vendors[] = array();
		while ($result = mysql_fetch_assoc($query)) {
			$vendors[] = $result;
		}
	}
	return $vendors;
}


function getDealerInfo() {
	$dealersql = "SELECT DISTINCT users.last_name AS dealername, users.id AS deal_id FROM order_forms INNER JOIN users ON order_forms.user = users.id WHERE disabled != 'Y'";
	if(buildVendorQuery()!='') $dealersql .= " AND order_forms.form IN (".buildVendorQuery().")";
	$dealersql .= " ORDER BY users.last_name";
	$query = mysql_query($dealersql);
	checkdberror($dealersql);
	$dealers[] = array();
	while ($result = mysql_fetch_assoc($query)) {
		$dealers[] = $result;
	}
	return $dealers;
}

function buildVendorQuery($chosen_vendor = "all") {
	global $chosen_form;
	// debugging code ... show all
	//echo "<br />\n<br />\nin buildVendorQuery($chosen_vendor)<br />\n<br />\n";
        if(!isset($chosen_form)) $chosen_form = "all";
	if(!$chosen_form) $chosen_form = "all";
	if($chosen_form == "all") {
		//echo "All forms...<br />\n";
		if (secure_is_vendor()) {
                        global $vendorid;
			//echo "we're a vendor...<br />\n";
			$sqlvendor = "SELECT ID FROM forms WHERE vendor IN (SELECT vendor FROM vlogin_access WHERE `user` = '$vendorid')";
			//echo "SQL = $sqlvendor<br />\n";
		} else {
			$sqlvendor = "";
			//echo "sqlvendor = NULL<br />\n";
		}
	} elseif ($chosen_vendor != "all") { // if there's a specific vendor chosen
		//echo "No specific vendor selected<br />\n";
		$sqlvendor = "SELECT ID FROM forms WHERE vendor IN (SELECT ID FROM vendors WHERE name = '$chosen_vendor')";
	}
	return $sqlvendor;
}

function makeQueueQuery($arg, $verify = 0)
{
	$sql_where = '';
	$show_type = $arg['show_type'];
	$chosen_vendor = $arg['chosen_vendor'];
	$chosen_dealer = $arg['chosen_dealer'];
	$chosen_form = $arg['chosen_form'];
	$groupmulti = $arg['groupmulti'];
	$from_date = $arg['from_date'];
	$thru_date = $arg['thru_date'];
	$searchopt = $arg['searchopt'];
	$searchfor = $arg['searchfor'];
	unset($arg);
	$searchfor = $searchfor != '' ? $searchfor - 1000 : $searchfor;
	$sqlvendor = buildVendorQuery($chosen_vendor);
	if($verify)
	{
		$sql = "SELECT COUNT(order_forms.ID) AS query_count, COUNT(BoL_queue.po) AS po_count";
	}
	else
	{
		$sql = "SELECT snapshot_forms.address AS formaddress, snapshot_forms.name as formname, order_forms.ID AS po, order_forms.form AS orig_form_id, order_forms.ordered, order_forms.snapshot_form AS form_id, order_forms.nobolmerge AS notmultiable, edi_files.processed AS edi, snapshot_users.last_name AS dealer_name, snapshot_users.address AS dealer_address, snapshot_users.city AS dealer_city, snapshot_users.state  AS dealer_state, snapshot_users.zip AS dealer_zip, BoL_queue.ID AS bol_id, BoL_queue.po as bol_po, BoL_queue.createdate, BoL_queue.source as bol_source, BoL_queue.totalset, BoL_queue.totalmatt, BoL_queue.totalbox, BoL_queue.updated, BoL_queue.picktix_printed AS picktix, BoL_queue.ptlabel_printed AS label, BoL_queue.complete";
	}
	if($searchopt == "BoL_forms.ID" && $searchfor != '' && !$verify) $sql .= ", BoL_forms.ID ";
	$sql .= " FROM ((";
	if($searchopt == "BoL_forms.ID" && $searchfor != '') $sql .= "(";
	$sql .= "snapshot_forms INNER JOIN order_forms ON snapshot_forms.id = order_forms.snapshot_form) LEFT OUTER JOIN edi_files ON edi_files.po_id LIKE CONCAT('%', order_forms.ID, '%') INNER JOIN ";
	if($searchopt == "BoL_forms.ID" && $searchfor != '') $sql .= "BoL_forms ON order_forms.ID IN (SELECT DISTINCT po FROM BoL_items WHERE BoL_items.bol_id = '$searchfor')) INNER JOIN ";
	$sql .= "snapshot_users ON order_forms.snapshot_user = snapshot_users.id";
	if($chosen_dealer!='' && $chosen_dealer!='all')
	{ // add the dealer filter if needed
		$sql .= " AND snapshot_users.last_name = '".$chosen_dealer."'";
	}
	$sql .= "), BoL_queue";
	$sql .= " WHERE order_forms.ID = BoL_queue.po AND (BoL_queue.totalset + BoL_queue.totalmatt + BoL_queue.totalbox >= 0)";
	switch($show_type)
	{
		case "open":
			$sql_where .= " AND NOT BoL_queue.complete";
			break;
		case "closed":
			$sql_where .= " AND BoL_queue.complete";
			break;
		case "all":
			break;
	}
	if($searchopt == 'order_forms.ID')
	{
		$sql_dateorder = "order_forms.ordered";
	}
	elseif($searchopt == 'BoL_forms.ID' && $searchfor != '')
	{
		// only filter for BOLs if a specific BOL is chosen
		$sql_dateorder = "BoL_items.processed";
	}
	else
	{
		// this should do a non-filtered filter, if that makes any sense
		$sql_dateorder = 'order_forms.ordered';
	}
	$fromdate = strtotime($from_date);
	$thrudate = strtotime($thru_date);
	if($thrudate < $fromdate)
	{
		// they were switched, fix it
		$realfrom = $thrudate;
		$realthru = $fromdate;
		$thrudate = $realthru;
		$fromdate = $realfrom;
		unset($realthru);
		unset($realfrom);
	}
	$from_month = date('m', $fromdate);
	$from_day = date('d', $fromdate);
	$from_year = date('Y', $fromdate);
	$thru_month = date('m', $thrudate);
	$thru_day = date('d', $thrudate);
	$thru_year = date('Y', $thrudate);
	if($sql_dateorder == 'BoL_items.processed')
	{
		$sq = "SELECT DISTINCT po FROM BoL_items WHERE $sql_dateorder >= '$from_year-$from_month-$from_day 00:00:00' AND $sql_dateorder <= '$thru_year-$thru_month-$thru_day 23:59:59' ORDER BY po";
		$que = mysql_query($sq);
		$ques = array();
		while($res = mysql_fetch_assoc($que))
		{
			$ques[] = $res['po'];
		}
		$ques_imp = implode(',', $ques);
		if (count($ques) > 1)
			$sqladd = " AND BoL_queue.po IN ($ques_imp)";
		else
			$sqladd = " AND BoL_queue.po = '".$ques_imp."'";
	}
	else
	{
		$sqladd = " AND $sql_dateorder >= '$from_year-$from_month-$from_day 00:00:00' AND $sql_dateorder <= '$thru_year-$thru_month-$thru_day 23:59:59'";
	}
	$sqladd .= " AND order_forms.deleted != 1";
	$sql_where .= $sqladd;

	if($sqlvendor) { // add the vendor filter if need be
		$sql_where .= " AND order_forms.form IN (".$sqlvendor.")";
	}
	if($chosen_form!='' && $chosen_form!='all') { // add the form filter if needed
		$sql_where .= " AND order_forms.form = $chosen_form";
	}
	if($searchfor != '')
	{
		$sql_where = " AND $searchopt = '$searchfor'";
	}
	$sql_where .= " ORDER BY ";
	if($groupmulti=="1" && (secure_is_vendor() || secure_is_admin()))
	{
		$sql_where .= "snapshot_users.last_name, snapshot_users.address, snapshot_users.city, snapshot_users.state, snapshot_users.zip, snapshot_forms.name, snapshot_forms.address, order_forms.snapshot_form, ";
	}
	else
	{
		$sql_where .= "order_forms.snapshot_form, ";
	}
	$sql_where .= "BoL_queue.po";
	$sql_query = $sql.$sql_where;
	// debugging code
	return $sql_query;
}

function getQueue($arg, $verify = 0) {
	//var_dump($arg);
	$sql_query = makeQueueQuery($arg);
	// debugging
	//echo $sql_query;
	if($verify)
	{
		$sql_query .= " LIMIT 1"; // just get the first one
		$query = mysql_query($sql_query);
		checkdberror($sql_query);
		// reset the query
		$sql_query = makeQueueQuery($arg, 1); // get the count
		$qcount = mysql_query($sql_query);
		checkdberror($sql_query);
		$query_cnt = mysql_fetch_assoc($qcount);
		$query_count = $query_cnt['query_count'];
	}
	else
	{
		$query = mysql_query($sql_query);
		checkdberror($sql_query);
	}
	$ordernum = array();
	$order_source = array();
	$dealer_name = array();
	$dealer_address = array();
	$dealer_city = array();
	$dealer_state = array();
	$dealer_zip = array();
	$bol_id = array();
	$form_id = array();
	$orig_form_id =array();
	$formname = array();
	$orderdate = array();
	$notmultiable = array();
	$orderdue = array();
	$ship_date = array();
	$orderqty = array();
	$complete = array();
	$picktix = array();
	$label = array();
	while($result = mysql_fetch_assoc($query))
	{
		$ordernum[] = $result['po'] + 1000;
		$order_source[] = $result['bol_source'];
		$dealer_name[] = $result['dealer_name'];
		$dealer_address[] = $result['dealer_address'];
		$dealer_city[] = $result['dealer_city'];
		$dealer_state[] = $result['dealer_state'];
		$dealer_zip[] = $result['dealer_zip'];
		$bol_id[] = $result['bol_id'];
		$form_id[] = $result['form_id'];
		$orig_form_id[] = $result['orig_form_id'];
		$notmultiable[] = $result['notmultiable'];
		$formname[] = $result['formname'];
		$picktix[] = $result['picktix'];
		$label[] = $result['label'];
		$edi[] = $result['edi'];
		$orderdate[] = date('n/j/Y', strtotime($result['ordered']));
		switch($arg['show_type'])
		{
			case "open":
				$orderdue[] = date('n/j/Y', strtotime($result['ordered']) + (60*60*24*7));
				$orderqty[] = $result['totalset'] + $result['totalmatt'] + $result['totalbox'];
				break;
			case "closed":
				$ship_date[] = date('n/j/Y', strtotime($result['updated']));
				break;
			case "all":
				$orderdue[] = date('n/j/Y', strtotime($result['ordered']) + (60*60*24*7));
				$orderqty[] = $result['totalset'] + $result['totalmatt'] + $result['totalbox'];
				$ship_date[] = date('n/j/Y', strtotime($result['updated']));
				$complete[] = $result['complete'];
				break;
		}				
	}
	switch ($arg['show_type'])
	{
		case "open":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'notmultiable' => $notmultiable, 'form_name' => $formname, 'orderdate' => $orderdate, 'edi' => $edi, 'orderdue' => $orderdue, 'orderqty' => $orderqty, 'picktix' => $picktix, 'label' => $label);
			break;
		case "closed":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'notmultiable' => $notmultiable, 'form_name' => $formname, 'edi' => $edi, 'orderdate' => $orderdate, 'shipdate' => $ship_date, 'picktix' => $picktix, 'label' => $label);
			break;
		case "all":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'notmultiable' => $notmultiable, 'form_name' => $formname, 'edi' => $edi, 'orderdate' => $orderdate, 'orderdue' => $orderdue, 'shipdate' => $ship_date, 'orderqty' => $orderqty, 'complete' => $complete, 'picktix' => $picktix, 'label' => $label);
			break;
	}			
	$queue_info = array('data' => $queue, 'count' => $verify ? $query_count : count($ordernum));
	return $queue_info;
}

function getBoQueue($arg) {
	$show_type = $arg['show_type'];
	$chosen_dealer = $arg['chosen_dealer'];
	$chosen_form = $arg['chosen_form'];
	$groupmulti = $arg['groupmulti'];
	$from_year = $arg['from_year'];
	$from_month = $arg['from_month'];
	$from_day = $arg['from_day'];
	$thru_year = $arg['thru_year'];
	$thru_month = $arg['thru_month'];
	$thru_day = $arg['thru_day'];
	$searchopt = $arg['searchopt'];
	$searchfor = $arg['searchfor'];
	unset($arg);
	$searchfor = $searchfor != '' ? $searchfor - 1000 : $searchfor;
	$sqlvendor = buildVendorQuery();
	$sql = "SELECT snapshot_forms.address AS formaddress, snapshot_forms.name as formname, order_forms.ID AS po, order_forms.form AS orig_form_id, order_forms.ordered, order_forms.snapshot_form AS form_id, snapshot_users.last_name AS dealer_name, snapshot_users.address AS dealer_address, snapshot_users.city AS dealer_city, snapshot_users.state  AS dealer_state, snapshot_users.zip AS dealer_zip, BoL_queue.ID AS bol_id, BoL_queue.po as bol_po, BoL_queue.createdate, BoL_queue.source as bol_source, BoL_queue.totalset, BoL_queue.totalmatt, BoL_queue.totalbox, BoL_queue.updated, BoL_queue.complete";
	if($searchopt == "BoL_forms.ID" && $searchfor != '') $sql .= ", BoL_forms.ID ";
	$sql .= " FROM ((";
	if($searchopt == "BoL_forms.ID" && $searchfor != '') $sql .= "(";
	$sql .= "snapshot_forms INNER JOIN order_forms ON snapshot_forms.id = order_forms.snapshot_form) INNER JOIN ";
	if($searchopt == "BoL_forms.ID" && $searchfor != '') $sql .= "BoL_forms ON order_forms.ID IN (SELECT DISTINCT po FROM BoL_items WHERE BoL_items.bol_id = '$searchfor')) INNER JOIN ";
	$sql .= "snapshot_users ON order_forms.snapshot_user = snapshot_users.id";
	if($chosen_dealer!='' && $chosen_dealer!='all')
	{ // add the dealer filter if needed
		$sql .= " AND snapshot_users.last_name = '".$chosen_dealer."'";
	}
	$sql .= "), BoL_queue";
	$sql .= " WHERE order_forms.ID = BoL_queue.po AND (BoL_queue.totalset + BoL_queue.totalmatt + BoL_queue.totalbox >= 0)";
	switch($show_type)
	{
		case "open":
			$sql_where .= " AND NOT BoL_queue.complete";
			break;
		case "closed":
			$sql_where .= " AND BoL_queue.complete";
			break;
		case "all":
			break;
	}
	if($searchopt == 'order_forms.ID')
	{
		$sql_dateorder = "order_forms.ordered";
	}
	else
	{
		$sql_dateorder = "BoL_items.processed";
	}
	if($sql_dateorder == 'BoL_items.processed')
	{
		$sq = "SELECT DISTINCT po FROM BoL_items WHERE $sql_dateorder >= '$from_year-$from_month-$from_day 00:00:00' AND $sql_dateorder <= '$thru_year-$thru_month-$thru_day 23:59:59' ORDER BY po";
		$que = mysql_query($sq);
		$ques = array();
		while($res = mysql_fetch_assoc($que))
		{
			$ques[] = $res['po'];
		}
		$ques_imp = implode(',', $ques);
		if (count($ques_imp) > 1)
			$sqladd = " AND BoL_queue.po IN ($ques_imp)";
		else
			$sqladd = " AND BoL_queue.po = '".$ques_imp."'";
	}
	else
	{
		$sqladd = " AND $sql_dateorder >= '$from_year-$from_month-$from_day 00:00:00' AND $sql_dateorder <= '$thru_year-$thru_month-$thru_day 23:59:59'";
	}
	$sqladd .= " AND order_forms.deleted != 1";
	$sql_where .= $sqladd;

	if($sqlvendor) { // add the vendor filter if need be
		$sql_where .= " AND order_forms.form IN (".$sqlvendor.")";
	}
	if($chosen_form!='' && $chosen_form!='all') { // add the form filter if needed
		$sql_where .= " AND order_forms.form = $chosen_form";
	}
	if($searchfor != '')
	{
		$sql_where = " AND $searchopt = '$searchfor'";
	}
	$sql_where .= " ORDER BY ";
	if($groupmulti=="1" && (secure_is_vendor() || secure_is_admin()))
	{
		$sql_where .= "snapshot_users.last_name, snapshot_users.address, snapshot_users.city, snapshot_users.state, snapshot_users.zip, snapshot_forms.name, snapshot_forms.address, order_forms.snapshot_form, ";
	}
	else
	{
		$sql_where .= "order_forms.snapshot_form";
	}
	$sql_where .= "BoL_queue.po";
	$sql_query = $sql.$sql_where;
	$query = mysql_query($sql_query);
	checkdberror($sql_query);
	
	$ordernum = array();
	$order_source = array();
	$dealer_name = array();
	$dealer_address = array();
	$dealer_city = array();
	$dealer_state = array();
	$dealer_zip = array();
	$bol_id = array();
	$form_id = array();
	$orig_form_id =array();
	$formname = array();
	$orderdate = array();
	while($result = mysql_fetch_assoc($query)) {
		$ordernum[] = $result['po'] + 1000;
		$order_source[] = $result['bol_source'];
		$dealer_name[] = $result['dealer_name'];
		$dealer_address[] = $result['dealer_address'];
		$dealer_city[] = $result['dealer_city'];
		$dealer_state[] = $result['dealer_state'];
		$dealer_zip[] = $result['dealer_zip'];
		$bol_id[] = $result['bol_id'];
		$form_id[] = $result['form_id'];
		$orig_form_id[] = $result['orig_form_id'];
		$formname[] = $result['formname'];
		$orderdate[] = date('n/j/Y', strtotime($result['ordered']));
		switch($show_type)
		{
			case "open":
				$orderdue[] = date('n/j/Y', strtotime($result['ordered']) + (60*60*24*7));
				$orderqty[] = $result['totalset'] + $result['totalmatt'] + $result['totalbox'];
				break;
			case "closed":
				$ship_date[] = date('n/j/Y', strtotime($result['updated']));
				break;
			case "all":
				$orderdue[] = date('n/j/Y', strtotime($result['ordered']) + (60*60*24*7));
				$orderqty[] = $result['totalset'] + $result['totalmatt'] + $result['totalbox'];
				$ship_date[] = date('n/j/Y', strtotime($result['updated']));
				$complete[] = $result['complete'];
				break;
		}				
	}
	switch ($show_type)
	{
		case "open":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'form_name' => $formname, 'orderdate' => $orderdate, 'orderdue' => $orderdue, 'orderqty' => $orderqty);
			break;
		case "closed":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'form_name' => $formname, 'orderdate' => $orderdate, 'shipdate' => $ship_date);
			break;
		case "all":
			$queue = array('po' => $ordernum, 'source' => $order_source, 'name' => $dealer_name, 'address' => $dealer_address, 'city' =>$dealer_city, 'state' => $dealer_state, 'zip' => $dealer_zip, 'bol' => $bol_id, 'form' => $form_id, 'orig_form' => $orig_form_id, 'form_name' => $formname, 'orderdate' => $orderdate, 'orderdue' => $orderdue, 'shipdate' => $ship_date, 'orderqty' => $orderqty, 'complete' => $complete);
			break;
	}			
	$queue_info = array('data' => $queue, 'count' => count($ordernum));
	return $queue_info;
}

function getAdminPrintColor()
{
	return "red";
}

function getCompleteColor()
{
	return "green";
}

function getBolQueueString($bol_id, $numberonly = false)
{
	$freightentered = getBolFreightEnteredStatus($bol_id);
	if(!$numberonly) $display = $freightentered ? '{ ' : '* ';
	$display .= ($bol_id+1000);
	if(!$numberonly) $display .= $freightentered ? ' }' : ' *';
	return $display;
}

function getBolInfo($bol_id)
{
	$sql = "SELECT * FROM BoL_forms WHERE ID = '$bol_id'";
	$que = mysql_query($sql);
	checkdberror($sql);
	$bolforms = mysql_fetch_assoc($que);
	unset($bolforms['comment']);
	$bolforms['displaystring'] = getBolQueueString($bolforms['ID']);
	$bolforms['displaycolor'] = getBolOnlyStatus($bolforms['ID'], true);
	return $bolforms;
}

function getQtyShipped($po) {
	$po = $po-1000;
	$sql2 = "SELECT SUM(setamt) as totset, SUM(mattamt) as totmatt, SUM(boxamt) as totbox FROM BoL_items WHERE po = $po AND type = 'bol'";
	$query2 = mysql_query($sql2);
	checkdberror($sql2);
	if(mysql_num_rows($query2)>0)
	{
		$result2 = mysql_fetch_assoc($query2);
		$qtyshipped = ($result2['totset'] + $result2['totmatt'] + $result2['totbox']);
	}
	$sql3 = "SELECT SUM(setamt) as totset, SUM(mattamt) as totmatt, SUM(boxamt) as totbox FROM BoL_items WHERE po = $po AND type = 'cred' AND credit_approved != 2";
	$que3 = mysql_query($sql3);
	$res3 = mysql_fetch_assoc($que3);
	$qtyshipped += ($res3['totset'] + $res3['totmatt'] + $res3['totbox']);
	return $qtyshipped;
}

function getAllBols($po_num) {
	// figure out which BOLs/credit reqs are from this PO
	$sqlbol2 = "SELECT bol_id FROM BoL_items WHERE po = ".$po_num;
	$que2 = mysql_query($sqlbol2);
	checkdberror($sqlbol2);
	while($res = mysql_fetch_assoc($que2)) {
		$bols[] = $res['bol_id'];
	}
	if(count($bols)>1) {
		$bolx = array_unique($bols);
		$bols_disp = implode(', ', $bolx);
	} else if(count($bols)==1) {
		$bols_disp = $bols[0];
	} else {
		$bols_disp = 0;
	}
	unset($bols);
	unset($bolx);
	// only display the View BoLs and View Credit Requests if there are any in the order
	$sqlbol = "SELECT ID FROM BoL_forms WHERE ID IN ($bols_disp) AND type = 'bol'";
	unset($bols_disp);
	$que = mysql_query($sqlbol);
	checkdberror($sqlbol);
	while($res = mysql_fetch_assoc($que)) {
		$bols[] = $res['ID'];
	}
	return $bols;
}

function getBolFreightEnteredStatus($bol_id) {
	$status = false;
	$sql = "SELECT freight FROM BoL_forms WHERE ID = $bol_id";
	checkdberror($sql);
	$que = mysql_query($sql);
	$ret = mysql_fetch_assoc($que);
	if(!is_null($ret['freight']) && $ret['freight']!='') $status = true;
	return $status;	
}

function getBolTrackingEnteredStatus($bol_id) {
	$status = false;
	$sql = "SELECT trackingnum FROM BoL_forms WHERE ID = $bol_id";
	checkdberror($sql);
	$que = mysql_query($sql);
	$ret = mysql_fetch_assoc($que);
	if(!is_null($ret['trackingnum']) && $ret['trackingnum']!='') $status = true;
	return $status;
}

function getBolAdminPrintStatus($bol_id) {
	$status = false;
	$sql = "SELECT adminprinted FROM BoL_forms WHERE ID = $bol_id";
	checkdberror($sql);
	$que = mysql_query($sql);
	$ret = mysql_fetch_assoc($que);
	if($ret['adminprinted']==0){
		return false;
	}
	else
	{
		return true;
	}
}

function getBolOnlyStatus($bol, $returncolor = false)
{
	$freightstatus = getBolFreightEnteredStatus($bol);
	$trackingstatus = getBolTrackingEnteredStatus($bol);
	$printstatus = getBolAdminPrintStatus($bol);
	if($returncolor)
	{
		if(!$printstatus & $freightstatus)
		{
			return getAdminPrintColor();
		}
		else if($freightstatus && $trackingstatus && $printstatus)
		{
			return getCompleteColor();
		}
		else
		{
			return "none";
		}
	}
	if($freightstatus && $trackingstatus && $printstatus)
	{
		return true;
	}
	else
	{
		return false;
	}
}


function getBolStatus($po_id)
{
	//echo "<br />\nRunning getBolStatus on $po_id<br />\n";
	$complete = isPOClosed($po_id, true);
	$bols = getAllBols($po_id);
	if(count($bols)==0)
	{
		//echo "<br />\nCount of bols = 0; returning false<br />\n";
		return false;
	}
	foreach($bols as $bol) {
		$freightstatus[] = getBolFreightEnteredStatus($bol);
		$trackingstatus[] = getBolTrackingEnteredStatus($bol);
		$printstatus[] = getBolAdminPrintStatus($bol);
	}
	if(array_unique($freightstatus)==array(true))
	{
		$freight_entered = true;
	}
	else
	{
		$freight_entered = false;
	}
	if(array_unique($trackingstatus)==array(true))
	{
		$tracking_entered = true;
	}
	else
	{
		$tracking_entered = false;
	}
	if(array_unique($printstatus)==array(true))
	{
		$admin_printed = true;
	}
	else
	{
		$admin_printed = false;
	}
	if($freight_entered && $tracking_entered && $admin_printed && $complete)
	{
		//echo "<br />\ngetBolStatus = true<br />\n";
		return true;
	}
	else
	{
		//echo "<br />\ngetBolStatus = false<br />\n";
		return false;
	}
}

function getBolCSVCompleteDate($bol)
{
	$sql = "SELECT UNIX_TIMESTAMP(csv_exported) AS completetime FROM BoL_forms WHERE ID = '$bol'";
	$query = mysql_query($sql);
	checkdberror($sql);
	//echo "timestamp = $sql<br />";
	$return = mysql_fetch_assoc($query);
	return is_null($return['completetime']) ? 0 : $return['completetime'];
}

function getAllCredits($po_num) {
	// figure out which BOLs/credit reqs are from this PO
	$sqlbol2 = "SELECT bol_id FROM BoL_items WHERE po = ".$po_num;
	$que2 = mysql_query($sqlbol2);
	checkdberror($sqlbol2);
	$bols = array();
	while($res = mysql_fetch_assoc($que2)) {
		$bols[] = $res['bol_id'];
	}
	if(count($bols)>1) {
		$bolx = array_unique($bols);
		$bols_disp = implode(', ', $bolx);
	} else if(count($bols)==1) {
		$bols_disp = $bols[0];
	} else {
		$bols_disp = 0;
	}
	unset($bols);
	unset($bolx);
	$sqlcred = "SELECT ID FROM BoL_forms WHERE ID IN ($bols_disp) AND type = 'cred'";
	$que = mysql_query($sqlcred);
	checkdberror($sqlcred);
	$creds = array();
	while($res = mysql_fetch_assoc($que)) {
		$creds[] = $res['ID'];
	}
	return $creds;
}


function preInsertCheck($po_id) {
	if (!is_numeric($po_id)) die("Shipping: preInsertCheck: orig_po non-numeric");
	// we need to see if the order has been totally shipped and credit-requested first before going any further
	$cont = false;
	$sql = "SELECT setqty, mattqty, qty, item FROM orders WHERE po_id = ".$po_id;
	//echo $sql."<br />";
	$query = mysql_query($sql);
	checkdberror($sql);
	if (!mysql_num_rows($query)) die("Shipping: preInsertCheck: no such PO");
	while($result = mysql_fetch_assoc($query))
	{
		// echo "- - - - New Item - - - -<br />";
		// foreach($result as $k => $v) {
		// 	echo "$k => $v<br />\n";
		// }
		$totset = 0;
		$totmatt = 0;
		$totbox = 0;
		$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$result['item'];
		// echo $sql2."<br />";
		$query2 = mysql_query($sql2);
		checkDBerror($sql2);
		if (!mysql_num_rows($query2)) die("Shipping: preInsertCheck: unable to locate snapshot for item on PO");
		$result2 = mysql_fetch_array($query2);
		// foreach($result2 as $k => $v) {
		// 	echo "$k => $v<br />\n";
		// }
		// grab counts of how many of this item have already been shipped and reduce the # available by that amount
		$sq2 = "SELECT ID as itemid, setamt, mattamt, boxamt FROM BoL_items WHERE po = $po_id AND IF(type = 'cred', credit_approved != 2, TRUE) AND item = ".$result['item'];
		// echo $sq2."<br />";
		$que2 = mysql_query($sq2);
		checkDBerror($sq2);
		while($res2 = mysql_fetch_assoc($que2)) {
			// foreach($res2 as $kk => $vv) {
			// 	echo "$kk => $vv<br />\n";
			// }
			// echo "------------------<br />";
			$totset += $res2['setamt'];
			// echo "totset = $totset<br />";
			$totmatt += $res2['mattamt'];
			// echo "totmattt = $totmatt<br />";
			$totbox += $res2['boxamt'];
			// echo "totbox = $totbox<br />";
		}
		if($result['setqty']-$totset>0 || $result['mattqty']-$totmatt>0 || $result['qty']-$totbox>0) $cont = true;
		// echo "cont = $cont<br />";
	}
	// die();
	if(!$cont) {
		setcookie('BoL_msg', 'Order has either been completed or a credit request has been submitted which would complete the order. No items are available for this order.', time()+5);
		header("Location: shipping.php");
		exit();
	}
}


function isPOClosed($po_id, $returnstat = false, $po_source = "po") {
	//echo "checking $po_id...<br />";
	// check to see if the order is complete; if so, set the complete Boolean to true
	$sql = "SELECT totalset, totalmatt, totalbox FROM BoL_queue WHERE po = $po_id";
	//echo "$sql<br />";
	$que = mysql_query($sql);
	if(checkdberror($sql,false)) {
		sendError("checking the order status", "isPOClosed (inc_shipping.php line 15)", checkdberror($sql,false), 'shipping.php');
	}
	$order = mysql_fetch_assoc($que);
	$sql2 = "SELECT SUM(setamt) as totset, SUM(mattamt) as totmatt, SUM(boxamt) as totbox FROM BoL_items WHERE (po = $po_id) AND IF(type = 'cred', credit_approved = 1, TRUE)";
	//echo "$sql2<br />";
	$que2 = mysql_query($sql2);
	if(checkdberror($sql2)) {
		sendError("checking the order status", "isPOClosed - BOL Count (inc_shipping.php line 21)", checkdberror($sql2,false), 'shipping.php');
	}
	$res2 = mysql_fetch_assoc($que2);
	$totset = $res2['totset'];
	$totmatt = $res2['totmatt'];
	$totbox = $res2['totbox'];
	// check the #s
	if($order['totalset']==$totset && $order['totalmatt']==$totmatt && $order['totalbox']==$totbox) {
		if($returnstat) return true;
		$sq = "UPDATE BoL_queue SET complete = 1 WHERE po = $po_id"; // it is complete
		$qu = mysql_query($sq);
		if(checkdberror($sq,false)) {
			sendError("updating the order status", "isPOClosed - Status Update (inc_shipping.php line 48)", checkdberror($sq,false), 'shipping.php');
		}
	} elseif($returnstat) return false;
}

function openItemCount($po_id) { // po_id = shipping order id
	$count = 0;
	$sql = "SELECT item FROM orders WHERE po_id = ".$po_id;
	$query = mysql_query($sql);
	checkdberror($sql);
	while($result = mysql_fetch_assoc($query)) {
		$count += openItem($po_id, $result['item']);
	}
	return $count;
}

function openItem($po_id, $item) {
	$sql = "SELECT setqty, mattqty, qty FROM orders WHERE po_id = $po_id AND item = $item";
	$query = mysql_query($sql);
	checkdberror($sql);
	$result = mysql_fetch_assoc($query);
	$sql2 = "SELECT partno, description, setqty AS setamt FROM snapshot_items WHERE id = ".$item;
	$query2 = mysql_query($sql2);
	checkdberror($sql2);
	$result2 = mysql_fetch_array($query2);
	// grab counts of how many of this item have already been shipped and reduce the # available by that amount
	$shippedset = 0;
	$shippedmatt = 0;
	$shippedbox = 0;
	$sq2 = "SELECT SUM(setamt) as shippedset, SUM(mattamt) as shippedmatt, SUM(boxamt) as shippedbox FROM BoL_items WHERE po = $po_id AND type = 'bol' AND item = $item";
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['shippedset'];
	$shippedmatt += $res2['shippedmatt'];
	$shippedbox += $res2['shippedbox'];
	$sq2 = "SELECT SUM(setamt) as creditset, SUM(mattamt) as creditmatt, SUM(boxamt) as creditbox FROM BoL_items WHERE po = $po_id AND item = $item AND type = 'cred' AND credit_approved = 1";
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['creditset'];
	$shippedmatt += $res2['creditmatt'];
	$shippedbox += $res2['creditbox'];
	if(!($result['setqty']-$shippedset==0 && $result['mattqty']-$shippedmatt==0 && $result['qty']-$shippedbox==0)) 
	{
		return 1;
	} else {
		return 0;
	} // move on to the next row if they are all 0 amounts
}

function openItemInfo($po_id, $item) {
	$sql = "SELECT setqty, mattqty, qty FROM orders WHERE po_id = $po_id AND item = $item";
	$query = mysql_query($sql);
	checkdberror($sql);
	$result = mysql_fetch_assoc($query);
	$sql2 = "SELECT partno, description, setqty, weight FROM snapshot_items WHERE id = ".$item;
	$query2 = mysql_query($sql2);
	checkdberror($sql2);
	$result2 = mysql_fetch_array($query2);
	$iteminfo = Array();
	$iteminfo['partno'] = $result2['partno'];
	$iteminfo['desc'] = $result2['description'];
	$iteminfo['setamt'] = $result2['setqty'];
	$iteminfo['weight'] = $result2['weight'];
	// grab counts of how many of this item have already been shipped and reduce the # available by that amount
	// first, see if there's been a BoL generated for this PO
	$shippedset = 0;
	$shippedmatt = 0;
	$shippedbox = 0;
	$sq2 = "SELECT SUM(setamt) as shippedset, SUM(mattamt) as shippedmatt, SUM(boxamt) as shippedbox FROM BoL_items WHERE po = $po_id AND type = 'bol' AND item = $item";
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['shippedset'];
	$shippedmatt += $res2['shippedmatt'];
	$shippedbox += $res2['shippedbox'];
	$sq2 = "SELECT SUM(setamt) as creditset, SUM(mattamt) as creditmatt, SUM(boxamt) as creditbox FROM BoL_items WHERE po = $po_id AND item = $item AND type = 'cred' AND credit_approved = 1";
	$que2 = mysql_query($sq2);
	checkdberror($sq2);
	$res2 = mysql_fetch_assoc($que2);
	$shippedset += $res2['creditset'];
	$shippedmatt += $res2['creditmatt'];
	$shippedbox += $res2['creditbox'];
	$iteminfo['set'] = $result['setqty']-$shippedset;
	$iteminfo['matt'] = $result['mattqty']-$shippedmatt;
	$iteminfo['box'] = $result['qty']-$shippedbox;
	return $iteminfo;
}

function getCSVQueue($filters, $ch_orders = false)
{
	$filters['s_date'] = mktime(0,0,0,$filters['from_month'],$filters['from_day'],$filters['from_year']);
	$filters['e_date'] = mktime(23,59,59,$filters['thru_month'],$filters['thru_day'],$filters['thru_year']);
	// Remove Extraneous Date Fields
	unset($filters['from_year']);
	unset($filters['from_month']);
	unset($filters['from_day']);
	unset($filters['thru_year']);
	unset($filters['thru_month']);
	unset($filters['thru_day']);
	// Unset Dealer if ALL is used
	if ($filters['chosen_dealer'] == 'all'||!$filters['chosen_dealer']) unset($filters['chosen_dealer']);
	if (is_numeric($filters['chosen_vendor']) && $filters['chosen_vendor'] >= 1) {
	if (is_numeric($filters['chosen_form']) && $filters['chosen_form'] >= 1) {
			// Form Chosen, don't send Vendor info as it's redundent
			unset($filters['chosen_vendor']);
		} else {
			// No form chosen, don't send form
			unset($filters['chosen_form']);
		}
	} else {
		unset($filters['chosen_form']);
		unset($filters['chosen_vendor']);
	}
	// Remove Seach Options if there is no number.
	if (!is_numeric($filters['searchfor'])) {
		unset($filters['searchopt']);
		unset($filters['searchfor']);
	}
	// Debug
	/*
	echo "Start: ".date("r",$filters['s_date'])." End: ".date("r",$filters['e_date'])."<br/>\n";
	echo "<pre>";
	print_r($filters);
	echo "</pre>";
	*/
	// Select Function for Final Processing
	// print_r($filters);
	if ($filters['show_type'] == 'po') return getCSVSalesordQueue($filters, $ch_orders);
	if ($filters['show_type'] == 'bol') return getCSVSOIQueue($filters, $ch_orders);
	else return array(); // Default to PO search
}

function getCSVSalesordQueue($filters, $ch) {
	$where = array();
	$join = array();
	$order = array();
	$limit = array();

	// Default Searches
	$where[] = "`order_forms`.`processed` = 'Y'";
	$where[] = "`order_forms`.`deleted` = 0";
	$where[] = "`order_forms`.`type` = 'o'";
	$order[] = "`order_forms`.`ID` ASC";
	// End Default Searches

	// Search Dates
	if (isset($filters['s_date']))
	{
		$where[] = "`order_forms`.`ordered` >= '".date("Y-m-d 00:00:00",$filters['s_date'])."'";
	}
	if (isset($filters['e_date']))
	{
		$where[] = "`order_forms`.`ordered` <= '".date("Y-m-d 23:59:59",$filters['e_date'])."'";
	}

	// Search Dealer
	if (isset($filters['chosen_dealer']))
	{
		$where[] = "`order_forms`.`user` = '".$filters['chosen_dealer']."'";
	}

	// Search Form
	if (isset($filters['chosen_form']))
	{
		$where[] = "`order_forms`.`form` = '".$filters['chosen_form']."'";
	}

	// Search Vendor
	if (isset($filters['chosen_vendor']))
	{
		$join['forms'] = "`order_forms`.`form` = `forms`.`ID`";
		$where[] = "`forms`.`vendor` = '".$filters['chosen_vendor']."'";
	}
	
	// Search Options PO/BoL
	if (isset($filters['searchopt']))
	{
		if ($filters['searchopt'] == 'po')
		{
			$where[] = "`order_forms`.`ID` = '".($filters['searchfor'] - 1000)."'";
		}
		if ($filters['searchopt'] == 'bol')
		{
			$join['BoL_items'] = "`order_forms`.`ID` = `BoL_items`.`po`";
			$where[] = "`BoL_items`.`bol_id` = '".($filters['searchfor'] - 1000)."'";
		}
	}

	// CH Orders?
	if($ch)
	{
		$join['ch_order'] = "`order_forms`.`ID` = `ch_order`.`po`";
	}

	// Limits amount of records return
	//    Order is very important with these ifs
	if (isset($filters['s_limit']))
	{
		$limit[] = $filters['s_limit'];
	}
	if (isset($filters['e_limit']))
	{
		$limit[] = $filters['e_limit'];
	}

	// Build Query from Arrays
	$sql = !$ch ? "SELECT `order_forms`.`ID`, UNIX_TIMESTAMP(`order_forms`.`csv_exported`) AS `csv` FROM `order_forms`" :
	 "SELECT `order_forms`.`ID`, UNIX_TIMESTAMP(`order_forms`.`chcsv_exported`) AS `csv` FROM `order_forms`";
	foreach($join as $table => $on)
	{
		$sql .= " INNER JOIN `".$table."` ON ".$on;
	}
	if ($where)
	{
		$sql .= " WHERE ".implode(" AND ",$where);
	}
	if ($order)
	{
		$sql .= " ORDER BY ".implode(", ",$order);
	}
	if ($limit)
	{
		$sql .= " LIMIT ".implode(", ",$limit);
	}

	// Run Query
	$query = mysql_query($sql);
	checkDBerror($sql);

	// Turn Result into return array
	$return = array();
	while ($row = mysql_fetch_assoc($query))
	{
		$return[$row['ID'] + 1000] = $row['csv'];
	}
	
	return $return;
}

function getCSVSOIQueue($filters, $ch)
{
	// function to return BOL number(s) based on query filters
	$user_id = $filters['user_id']; // id of the user who entered the thing in the first place
	$s_date = $filters['s_date']; // unix time stamp of the start date filter
	$e_date = $filters['e_date']; // unix time stamp of the end date filter
	$chosen_dealer = $filters['chosen_dealer']; // user id of the dealer we want
	$chosen_vendor = $filters['chosen_vendor']; // user id of the vendor we want
	$chosen_form = $filters['chosen_form']; // form we want
	$form_type = $filters['searchopt']; // if we're searching for BOLs or CRs
	$target_number = $filters['searchfor']; // # of the BOL/CR we want, if known
	$s_limit = $filters['s_limit']; // start location of the query pull
	$e_limit = $filters['e_limit']; // # of rows to return
	unset($filters); // we can let filters[] go now
	// time to build the query
	if($ch)
	{
		$sql = "SELECT `BoL_forms`.`ID`, `ch_order`.`po` FROM BoL_forms";
		$sql .= " LEFT OUTER JOIN BoL_items ON `BoL_forms`.`ID` = `BoL_items`.`bol_id` INNER JOIN `ch_order` ON `BoL_items`.`po` = `ch_order`.`po`";
	}
	else
	{
		$sql = "SELECT ID FROM BoL_forms";
	}
	$addand = false; // keep track of whether we need to add "and" to the end of the query parcel
	if($s_date) // start of date range
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		$sql .= "createdate >= '".date('Y-m-d H:i:s', $s_date)."'";
		$addand = true;		
	}
	if($e_date) // end of date range
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		$sql .= "createdate <= '".date('Y-m-d H:i:s', $e_date)."'";
		$addand = true;		
	}
	if($user_id) // filter by user id
	{
		$sql .= " WHERE user_id = '".$user_id."'";
		$addand = true;
	}
	if($chosen_dealer) // filter by a dealer
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		$sql .= "ID IN (SELECT bol_id FROM BoL_items WHERE po IN (SELECT ID FROM order_forms WHERE user = '".$chosen_dealer."'))";
		$addand = true;
	}
	if($chosen_vendor) // filter by a vendor
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		$sql .= "ID IN (SELECT bol_id FROM BoL_items WHERE po IN (SELECT ID from order_forms WHERE form IN (SELECT ID FROM forms WHERE vendor = '".$chosen_vendor."')))";
		$addand = true;
	}
	if($chosen_form) // filter by a form
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		$sql .= "ID IN (SELECT bol_id FROM BoL_items WHERE po IN (SELECT ID FROM order_forms WHERE form = '".$chosen_form."'))";
		$addand = true;
	}

	if($target_number) // a discrete value being searched for
	{
		if($addand)
		{
			$sql .= " AND ";
		}
		else
		{
			$sql .= " WHERE ";
		}
		if($form_type=='po') // if the value is a PO #, we need to translate to a BOL ID
		{
			$sql .= "ID IN (SELECT bol_id FROM BoL_items WHERE po = '".($target_number-1000)."')";
		}
		else
		{
			$sql .= "ID = '".($target_number-1000)."'";
		}
		$addand = true;
	}

	if($s_limit || $e_limit)
	{
		$sql .= " LIMIT $s_limit,$e_limit";
	}
	$query = mysql_query($sql);
	checkdberror($sql);
	while($result = mysql_fetch_array($query))
	{
		if(getBolOnlyStatus($result['ID']))
		{
			// add this bol to the array
			$return[$result['ID']+1000] = getBolCSVCompleteDate($result['ID']);
		}
	}
	//return array(1927 => 0,1930 => time());
	return $return;
}

function getPoFromBol($bol_id)
{
	// returns po # from a given Bol id (from the db)
	$sql = "SELECT DISTINCT po FROM BoL_items WHERE bol_id = '$bol_id'";
	checkdberror($sql);
	$que = mysql_query($sql);
	while($result = mysql_fetch_assoc($que))
	{
		if(mysql_num_rows()>1)
		{
			$return[] = $result['po'];
		}
		else
		{
			$return = $result['po'];
		}
	}
	return $return;
}

function runQue($sq)
{
	// function to run a single-line mysql query quickly
	// so i don't have to type type type type type....
	checkdberror($sq);
	$que = mysql_query($sq);
	return mysql_fetch_assoc($que);
}

function doBolCancels($cancels)
{
	// function to submit BOL cancellations
	// $cancels is an Array of bol, comments and Array(item,orderid,type,amt,rsn)	
	// (int)bol = BoL_items.bol_id of the item in question
	// (str)comments = user entered comments on the cancellation
	// (int)item = BoL_items.item ( = snapshot_items.id) of the item
	// (int)orderid = orders.ID of the original line item
	// (string)type = 'box', 'matt' or 'set', depending upon which item type was decremented
	// (int)amt = the amount to subtract
	// (str)rsn = reason code for the cancellation
	// define the cancel arrays here too (also defined in bol.js for page access)
	$canceltypes = Array('merchant_request','customer_refused');
	// canceltypes = rsn string which will be coming into the function
	$canceldesc = Array("Cancelled at Merchant's Request",'Customer Refused Delivery'); // corresponding full text desc
	// first thing will be to submit an order for negative amounts
	
	/* debugging code
	echo "Cancels<br />\n";
	var_dump($cancels);
	*/
	// roll through each cancel, one at a time:
	$sets = Array();
	$canccomment = "BOL Cancellation on ".date('m-d-Y')." for BOL# {$cancels['bol']}\n";
	foreach($cancels as $cancel)
	{
		if(!is_array($cancel)) continue;
		// get the item base id from the snapshot_id
		$sql = "SELECT orig_id, description FROM snapshot_items WHERE id = '{$cancel['item']}'";
		//echo "First SQL: $sql<br />\n";
		$res = runQue($sql);
		$cancorigitem = $res['orig_id'];
		$cancdesc = $res['description'];
		//echo "Results: $cancorigitem & $cancdesc<br />\n";
		// get the user id first, which is in the bol_form (yay!)
		$sql = "SELECT po FROM BoL_forms WHERE ID = {$cancels['bol']}";
		//echo "Next SQL: $sql<br />\n";
		$res = runQue($sql);
		$cancpo = $res['po'];
		//echo "Results: $cancpo<br />\n";
		// get the real user id for the order push
		$sql = "SELECT user FROM order_forms WHERE ID = $cancpo";
		///echo "Next SQL: $sql<br />\n";
		$res = runQue($sql);
		$cancuser_id = $res['user'];
		//echo "Result: $cancuser_id<br />\n";
		// set up comment
		$canccomment .= "---------\n";
		$canccomment .= "Item $cancdesc: ".$canceldesc[(array_search($cancel['rsn'], $canceltypes))]."\n";
		// get form #
		$sql = "SELECT form FROM order_forms WHERE ID = $cancpo";
		$res = runQue($sql);
		$cancform = $res['form'];
		// start to form the items
		// item[snapshot_id, qty, mattqty, setqty]
		// find out if this item / po combo has been started...if so, just add to the list
		if(!$sets[$cancel['item']]) // if the item's been made already, just add to the list
		{
			$sets[$cancel['item']] = Array('item_id' => $cancorigitem, 'snapshot_id' => $cancel['item']);
		}
		switch($cancel['type'])
		{
			case "box":
				$sets[$cancel['item']]['qty'] = -($cancel['amt']);
				break;
			default:
				$sets[$cancel['item']][$cancel['type']] = -($cancel['amt']);
				break;
		}
	}
	if($cancels['comments'])
	{
		$canccomment .= "\nCancellation Comments:\n".$cancels['comments'];
	}
	//echo "\n\nOrder Data:\n\n";
	//echo "User: $cancuser_id; Comment: $canccomment; Form: $cancform\n\n";
	//echo "Sets:\n\n";
	//var_dump($sets);
	//die();
	// submit order, without blocking & without hitting stock levels
	$order = submitOrder($cancuser_id, 1, mysql_escape_string($canccomment), $cancform, $sets, false, false, false, false);
	// set nobolmerge = 1 for the cancelation order
	$sql = "UPDATE order_forms SET nobolmerge = 1 WHERE ID = '".($order-1000)."'";
	checkdberror($sql);
	$que = mysql_query($sql);
	// now update ch_canceled
	// do this for each line item (i.e. $cancels)
	foreach($cancels as $cancel)
	{
		if(!is_array($cancel)) continue;
		$sql = "SELECT po FROM ch_order WHERE po = '$cancpo'"; // using prior defined value for the po
		checkdberror($sql);
		$que = mysql_query($sql);
		if(!mysql_num_rows($que)>0) return; // po not in ch_order, so go away
		if($itemrsn[$cancel['orderid']] && $cancel['rsn']!=$itemrsn[$cancel['orderid']][count($itemrsn[$cancel['orderid']]) - 1]) // if reasons are different for an item, create an array
		{
			$copysum = Array($itemsum[$cancel['orderid']]);
			$copyrsn = Array($itemrsn[$cancel['orderid']]);
			$copysum[] = $cancel['amt'];
			$copyrsn[] = $cancel['rsn'];
			$itemsum[$cancel['orderid']] = $copysum;
			$itemrsn[$cancel['orderid']] = $copyrsn;
		}
		else
		{
			$itemsum[$cancel['orderid']] += $cancel['amt'];
			$itemrsn[$cancel['orderid']] = $cancel['rsn'];
		}
	}
	// lay out the query
	foreach($itemsum as $itemkey => $itemsum)
	{
		$sql = "INSERT INTO ch_canceled (po, order_id, qty, reason) VALUES ('$cancpo', '$itemkey', '$itemsum', '$itemrsn[$itemkey]')";
		checkdberror($sql);
		$que = mysql_query($sql);
	}	
}

function makeShippingEdi($po_id, $bol_id, $packtype, $printpacking = true)
{
	require_once(dirname(__FILE__).'/../include/edi/bo_shippingedi.php');
	// this is an EDI order, let's grab some important info
	// start w/ the objects
	require_once(dirname(__FILE__).'/../include/edi/edi.php');
	if($packtype == '') $packtype = array('type' => 'default', 'vendor' => 'default');
	$shipment = new ShippingEdi();
	$shipment->Load($bol_id);
	$shipment->GetRetailerPOFromOrder($po_id);
	$shipment->mEdiVendor->LoadFromFilename($shipment->mEdiFilename);
	$test = new EdiSH();
	$does_exist = $test->Build($shipment);
	if($does_exist) $test->Send();
	$data = new EdiData();
	$data->LoadFromEdiObject($test);
	//$data->Load($test->mOutputData);
	DoEdi::UpdateDb('retailer_po', $data, $shipment->mRetailerPO);
	DoEdi::UpdateDb('po_id', $data, $bol_id);
	if($printpacking) header('Location: printpacking.php?po='.$po_id.'&bol='.$bol_id.'&vendor='.$packtype['vendor'].'&type='.$packtype['type']);
}

class BolPackage
{
	public $mPackageId;
	public $mBolId;
	public $mBoxNumber;
	public $mItems;
	public $mWeight;
	
	function __construct()
	{
		$this->mItems = array();
	}
	
	public function GenerateASN()
	{
		global $EdiVendor;
		$asn = '0'.str_pad($EdiVendor->mVendorId, 7, '0', STR_PAD_LEFT).str_pad(DoEdi::GetNextDbNumber('asn_sequence_number'), 9, '0', STR_PAD_LEFT);
		// generate check digit
		$sum1 = 0;
		$sum2 = 0;
		$check = 0;
		for($i=0; $i<strlen($asn); $i = $i + 2)
		{
			$sum1 += substr($asn, $i, 1);
		}
		$check = $sum1*3;
		for($i=1; $i<strlen($asn); $i = $i + 2)
		{
			$sum2 += substr($asn, $i, 1);
		}	
		$check += $sum2;
		$realcheck = 10 - ($check % 10);
		if($realcheck == 10) $realcheck = 0;
		$asn .= $realcheck;
		$realasn = '00'.$asn;
		$sql = "UPDATE shipping_packages SET bar_code = '$realasn' WHERE ID = ".$this->mPackageId;
		mysql_query($sql);
		checkdberror($sql);
		// reset the package weight
		$this->mWeight = 0;
	}

}

class BolPackageItem
{
	public $mItemId;
	public $mPackageId;
	public $mPOLineNumber;
	public $mBOLLineNumber;
	public $mBolItemNumber;
	public $mItemQty;
	public $mWeight;
}
?>