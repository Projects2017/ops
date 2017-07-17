<?php
$extra_javascript = "
function delete_order(tag, po) {
	if (confirm(\"Do you really want to delete PO#\" + po + \"?\")) {
		gFormAlertResult = function(text) { }
		postForm(tag);
		rmrow(tag);
	}
}

function print_order(tag, po) {
	window.open('viewpo.php?po='+po+'&printclose=1&erasesummary=1');
    tag = getParent(tag,'td');
	deleteTagContents(tag);
}

var type = 'MAS90';
var lastqueuetag = null;

function csvqueuepre(tag, po, dealer, formvendor) {
    lastqueuetag = tag;
	window.open('report-logAandM90.php?type='+type+'&po='+po+'&dealer='+dealer+'&formvendor='+formvendor,'export','width=350,height=300');
	tag = getParent(tag,'td');
	lastqueuetag = tag;
}

function csvqueuepost() {
    // Error checking!
    if (lastqueuetag == null) {
    	alert('There was an internal error... refreshing page');
    	window.refresh();
    	return;
    }
    
    deleteTagContents(lastqueuetag);
	var oA=lastqueuetag.appendChild(document.createElement('a'));
    oA.href='export';
    oA.onclick = function() { window.open('report-logAandM90.php?type='+type+'&exportonly=yes','export','width=350,height=300');return(false); };
    var oText = oA.appendChild (document.createTextNode('Queued'));
    
    lastqueuetag = null;
}
";
require("database.php");
require("secure.php");
require("../shipping/inc_shipping.php");

// Claims Redirect
if (isset($_GET['claim_redirect']) && is_numeric($_GET['claim_redirect'])) {
	$claim = array();
	$claim['form'] = 'furniture';
	$claim['action'] = 'insert';
	$claim['auto_po'] = $_GET['claim_redirect'];
	$po = $_GET['claim_redirect'] - 1000;
	$sql = "SELECT `order_forms`.* FROM `order_forms` WHERE `order_forms`.`ID` = '".$po."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if (!mysql_num_rows($query))
		die("non-existant po");
	$po = mysql_fetch_assoc($query);
	$claim['auto_user_id'] = $po['user'];
	$sql = "SELECT `oorvendor` FROM `forms` WHERE `ID` = '".$po['form']."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$result = mysql_fetch_assoc($query);
	if ($result) {
		$claim['auto_vendor_id'] = $result['oorvendor'];
	}
	$sql = "SELECT `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone` FROM `snapshot_users` WHERE `ID` = '".$po['shipto']."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$result = mysql_fetch_assoc($query);
	if ($result) {
		$name = "";
		if ($result['first_name'] && $result['last_name']) {
			$claim['auto_c_name'] = $result['last_name'].', '.$result['first_name'];
		} elseif ($result['first_name']) {
			$claim['auto_c_name'] = $result['first_name'];
		} elseif ($result['last_name']) {
			$claim['auto_c_name'] = $result['last_name'];
		}
		$claim['auto_c_name'] = $result['last_name'].', '.$result['first_name'];
		$claim['auto_name'] = $result['phone'];
		$claim['auto_c_addy'] = $result['address'] . ($result['address2']?("\n".$result['address2']):"") . "\n" .
			$result['city'].", ".$result['state']." ".$result['zip'];
	}
	$url = array();
	foreach ($claim as $id => $val) {
		$url[] = urlencode($id)."=".urlencode($val);
	}
	header("Location: /form.php?".implode("&",$url));
	exit();
}

require("menu.php");

/*
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
*/
// Start Filtering
// Get Filters from POST/GET
$filter = array();
foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^f_(.+)",$key, $reg))// && $value
		$filter[$reg[1]] = $value;
}

if ((!array_key_exists('txnsperpage',$filter))||(!$filter['txnsperpage'])) {
    $TxsPerPage = 30;
} else {
	$TxsPerPage = $filter['txnsperpage'];
	unset($filter['txnsperpage']);
}

if (!array_key_exists('datefrom',$filter)) {
	$filter['datefrom'] = date("m/d/Y");
}
if (!array_key_exists('dateto',$filter)) {
	$filter['dateto'] = date("m/d/Y",strtotime('+1 day'));
}
$where = '';
// Assemble Filter Where
foreach ($filter as $k => $i) {
	$i = stripslashes($i);
	$table = "order_forms";
	if ($i == "0"||$i == '')
		continue;

	if ($k == 'datefrom') $i = '+'.$i;
	if ($k == 'dateto') $i = "-".$i;

	$where .= " AND ";
	if ($i[0] == "+") {
		$i = ltrim($i, "+");
		$sign = ">=";
	} elseif ($i[0] == "-") {
		$i = ltrim($i, "-");
		$sign = "<=";
	} elseif ($i[0] == "|") {
		$type = 2;
		$cond = "OR";
		$i = ltrim($i, "|");
		$i = explode('|',$i);
		$sign = array();
		foreach ($i as $d => $z) {
			$sign[$d] = '=';
		}
	} else {
		$sign = "=";
	}
	
	$ftable = $table;
	$vjoin = false;
	
	if ($k == 'po') {
		$k = 'ID';
		if (is_array($i)) {
			foreach($i as $d => $z) {
				$i[$d] = $i[$d]-1000;
			}
		} else {
			$i = $i-1000;
		}
	} elseif ($k == 'date'||$k == 'datefrom'||$k == 'dateto') {
		$k = 'ordered';
		if (is_array($i)) {
			foreach($i as $d => $z) {
				$i[$d] = date('Y-m-d',strtotime($i[$d]));
			}
		} else {
			$i = date('Y-m-d',strtotime($i));
		}
	} elseif ($k == 'vendor') {
		$ftable = 'forms';
		$k = 'vendor';
		$vjoin = true;
	} elseif ($k == 'orderid'||$k == 'merchantpo') {
		$ftable = 'ch_order';
	}
	// echo "$k -> $i<br>";
	if (is_array($sign)) {
		$f_where = array();
		foreach ($i as $d => $z) {
			$f_where[] = "`".$ftable."`.`".mysql_escape_string($k)."` ".$sign[$d]." '".mysql_escape_string($i[$d])."'";
		}
		$where .= "(".implode(' '.$cond.' ', $f_where).")";
	} else {
		$where .= "`".$ftable."`.`".mysql_escape_string($k)."` ".$sign." '".mysql_escape_string($i)."'";
	}
}
$filter['txnsperpage'] = $TxsPerPage;
// End Filtering

if (isset($_REQUEST['req_page'])&&$_REQUEST['req_page'])
	$page = $_REQUEST['req_page'];
else
	$page = 1;
	
$allpages = false;
if ($page == 'all') {
	$allpages = true;
	$page = 1;
}

function getClaimNumbers($po)
{
	// returns array(id, status) of CH order claims, otherwise "" if no claims
	global $sql;
	$retarray = array();
	$sql = "SELECT id, status FROM claim_furniture WHERE po = $po";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while($result = mysql_fetch_assoc($query))
	{
		$retarray[] = array('id' => $result['id'], 'status' => $result['status']);
	}
	if(count($retarray)>=1) return $retarray;
	return "";
}

function getFormName($form)
{
	global $sql;
	$sql = "select snapshot_forms.name from snapshot_forms where snapshot_forms.ID='".$form."'";
	$query = mysql_query($sql);
	checkDBError();

	if ($result = mysql_fetch_array($query))
		return $result['name'];
	return "";
}

function assemblePageList($pages) {
	foreach ($pages as $id => $page) {
		$query = rewrite_url(array('req_page' => $page));
		$pages[$id] = "<a href=\"?".$query."\">".$page."</a>";
	}
	$pages = implode(' ',$pages);
	return $pages;
}

function getUserName($user)
{
	$sql = "select first_name,last_name from snapshot_users where id='$user'";
	$query = mysql_query($sql);
	checkDBError($sql);

	if($result = mysql_fetch_array($query))
		return $result['last_name'].", ".$result['first_name'];
	return "";
}

function getOrderType($type)
{
	if ($type == "c") return "credit";
	elseif ($type == "f") return "bill";
	else return "order";
}

$export_join = " LEFT JOIN exported_orders_log ON order_forms.ID = exported_orders_log.po_id ";
$vendor_join = "";
if ($vjoin) $vendor_join = " LEFT JOIN `forms` ON `order_forms`.`form` = `forms`.`ID` ";

$real_page = $page - 1; // Page 1 is 0 as long as far as the computer is concerned.
$start = $real_page * $TxsPerPage;
unset($real_page); // Don't need this var anymore
if ($allpages)
	$sql = "SELECT order_forms.*, ch_order.merchantpo, ch_order.orderid, exported_orders_log.mas90, exported_orders_log.mas90_queue FROM order_forms INNER JOIN `ch_order` ON `order_forms`.`ID` = `ch_order`.`po` $export_join $vendor_join WHERE deleted=0 ".$where." ORDER BY `ID` DESC";
else
	$sql = "SELECT order_forms.*, ch_order.merchantpo, ch_order.orderid, exported_orders_log.mas90, exported_orders_log.mas90_queue FROM order_forms INNER JOIN `ch_order` ON `order_forms`.`ID` = `ch_order`.`po` $export_join $vendor_join WHERE deleted=0 ".$where." ORDER BY `ID` DESC LIMIT ".$start.", ".$TxsPerPage;
$query = mysql_query($sql);
checkDBError($sql);

$txns = array();
while ($result = mysql_fetch_Array($query)) {
	$txns[] = $result;
}
$last_id = $txns[count($txns) - 1]['ID']; // Grabbing the ID of the last record
$sql = "SELECT COUNT(*) FROM order_forms INNER JOIN `ch_order` ON `order_forms`.`ID` = `ch_order`.`po` $export_join $vendor_join WHERE deleted=0 AND `order_forms`.`ID` < '".$last_id."' ".$where;
// FROM `order_forms` ".$vendor_join." WHERE `order_forms`.`deleted` = 0 AND `order_forms`.`ID` < '".$last_id."' ".$where;
$query = mysql_query($sql);
checkDBError($sql);
$result = mysql_fetch_array($query);
$totaltxns = $result[0] + count($txns); // Calculating total transactions
$totalpages = ceil($totaltxns / $TxsPerPage); // Overridden later if page not 1
$new_arry = array_reverse($txns, true);

if ($page != 1) {
	$sql = "SELECT COUNT(*) FROM order_forms INNER JOIN `ch_order` ON `order_forms`.`ID` = `ch_order`.`po` $export_join $vendor_join WHERE deleted=0 ".$where;
	//$sql = "SELECT COUNT(*) FROM `order_forms` ".$vendor_join." WHERE `order_forms`.`deleted` = 0 ".$where;
	$query = mysql_query($sql);
	checkDBError($sql);
	$result = mysql_fetch_array($query);
	$totalpages = ceil($result[0] / $TxsPerPage);
}
// Calculate Page Numbers
if ($totalpages == 0) $nopages = true;
else {
	$nopages = false;
	$beginpages = array();
	$prepages = array();
	$postpages = array();
	$endpages = array();
	if ($page > 3) {
		for ($i = 1; $i <= 3; $i++) {
			if ($i >= $page - 3) break;
			$beginpages[] = $i;
		}
	}
	if ($totalpages - 5 < $pages) {
		for ($i = $totalpages; $i >= $totalpages - 5; $i--) {
			$endpages[] = $i;
		}
		$endpages = array_reverse($endpages);
	}
	if ($page > 1) {
		$c = 0;
		for ($i = $page - 1; $i >= 1; $i--) {
			$c++;
			$prepages[] = $i;
			if ($c == 5) break;
		}
		$prepages = array_reverse($prepages);
	}
	if ($page < $totalpages) {
		for ($i = $page + 5; $i >= $page + 1; $i--) {
			if ($i > $totalpages) continue;
			$postpages[] = $i;
		}
		$postpages = array_reverse($postpages);
	}
	if ($totalpages - $page > 3) {
		for ($i = $totalpages; $i >= $totalpages - 2; $i--) {
			if ($i <= $page + 5) continue;
			$endpages[] = $i;
		}
		$endpages = array_reverse($endpages);
	}
}

// Build Output of Pager Selection
$pager = '';
if ($beginpages) {
	$pager .= assemblePageList($beginpages)."...";
}
if ($prepages) {
	$pager .= assemblePageList($prepages)." ";
}
if ($allpages) {
	$pager .= assemblePageList(array($page));
} else {
	$pager .= $page;
}
if ($postpages) {
	$pager .= " ".assemblePageList($postpages);
}
if ($endpages) {
	$pager .= "...".assemblePageList($endpages);
}
?><br>
<table id="ordersummary" border="0" cellspacing="0" cellpadding="5" width="90%" align="center">
  <tr class="skiptop"> 
    <td colspan="20" align="center"><h3>CommerceHub Summary</h3></td>
  </tr>
  <tr class="skiptop">
	<td colspan="20" class="text_12" align="center" style="vertical-align: bottom;">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
			Date From: <input class="date" type="text" value="<?php echo $filter['datefrom']; ?>" id="f_datefrom" name="f_datefrom" size="8">
			To: <input class="date" type="text" value="<?php echo $filter['dateto']; ?>" id="f_dateto" name="f_dateto" size="8">
			Dealer: <select id="f_user" name="f_user">
				<option value=0>All Dealers</option>
				<?php
				$userlist = db_user_getlist();
				foreach ($userlist as $value) {
					echo "\t\t\t<option value=\"".$value[id]."\"";
					if ($filter['user'] == $value[id])
						echo " selected";
					echo ">".$value[last_name]."</option>\n";
				} ?>
			</select>
			Vendor: <select id="f_vendor" name="f_vendor">
				<option value=0>All Vendors</option>
				<?php
				$userlist = db_vendors_getlist();
				foreach ($userlist as $value) {
					echo "\t\t\t<option value=\"".$value[id]."\"";
					if ($filter['vendor'] == $value[id])
						echo " selected";
					echo ">".$value[name]."</option>\n";
				} ?>
			</select>
			<br />
			PO: <input type="text" value="<?php echo $filter['po']; ?>" id="f_po" name="f_po" size="4">
			Merchant PO: <input type="text" value="<?php echo $filter['merchantpo']; ?>" id="f_merchantpo" name="f_merchantpo" size="6">
			Order: <input type="text" value="<?php echo $filter['orderid']; ?>" id="f_orderid" name="f_orderid" size="6">
			POs per Page:
			<input type="text" value="<?php echo $filter['txnsperpage']; ?>" id="f_txnsperpage" name="f_txnsperpage" size="2">
			<input type="submit" value="Filter By">
		</form>
	</td>
  </tr>
<?php if (!$nopages) { ?>
  <tr class="skiptop">
    <td colspan="20" align="right" class="text_12" style="vertical-align: bottom;">
		Page:
		<?php if ($page != 1) { ?><a href="?<?php echo rewrite_url(array('req_page' => $page - 1)); ?>"><< Prev</a><?php } ?>
		[<?php echo $pager; ?>]
		<?php if ($page != $totalpages) { ?><a href="?<?php echo rewrite_url(array('req_page' => $page + 1)); ?>">Next >></a> <?php } ?>
		<?php if ($allpages) { ?>[All Pages]<?php } else { ?>[<a href="?<?php echo rewrite_url(array('req_page' => 'all')); ?>">All Pages</a>]<?php } ?>
	</td>
  </tr>
<?php } /* end if !$nopages */ ?>
  <tr bgcolor="#fcfcfc"> 
    <td bgcolor="#CCCC99" class="fat_black_12">Order Date</td>
    <td bgcolor="#CCCC99" class="fat_black_12">PO #</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Merchant PO#</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Order#</td>
	<td bgcolor="#CCCC99" class="fat_black_12">&nbsp;</td>
    <td bgcolor="#CCCC99" class="fat_black_12" align="center">BOL</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Carrier</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Tracking #</td>
	<td bgcolor="#CCCC99" class="fat_black_12">Ship Date</td>
	<td bgcolor="#CCCC99" class="fat_black_12">Claims</td>
	<td bgcolor="#CCCC99" class="fat_black_12" colspan="10" align="center">Actions</td>
  </tr>
<?php
foreach ($txns as $result) {
	//if ($result['type'] == "o")
	$po = $result['ID'] + 1000;
	//else
	//	$po = "";
	
?>
<?php
$bol_query_type = "func";  // sql or func
if ($bol_query_type == "func") {
	$bol_ids = getAllBols($result['ID']);
} else {
	$sql = "SELECT `BoL_forms`.`ID` FROM `BoL_forms` INNER JOIN `BoL_items` ON `BoL_items`.`bol_id` = `BoL_forms`.`ID` WHERE `BoL_items`.`po` = '".$result['ID']."'";
	$query2 = mysql_query($sql);
	checkdberror($sql);
	$bol_ids = db_result2array($query2);
}
$bols = array();
if ($bol_ids)
foreach ($bol_ids as $k => $v) {
//while ($result2 = mysql_fetch_assoc($query2)) { // Start BoL While
	if ($bol_query_type == "func") {
		$result2 = getBolInfo($v);
	} else {
		$result2 = $v;
		$v = $result2['ID'];
	}
	$sql = "SELECT * FROM `ch_bolqueue` WHERE `bol_id` = '".$v."'";
	$query3 = mysql_query($sql);
	checkdberror($sql);
	$result3 = mysql_fetch_assoc($query3);
	mysql_free_result($query3);
	if ($result3) {
		if ($result3['processed'] == 1) {
			// It's been processed successfully white background
			$result2['bgcolor'] = "#FFFFFF";
		} elseif ($result3['processed'] == 0) {
			// Waiting on cron job processing blue background
			$result2['bgcolor'] = '#CCFFFF';
		} else {
			$result2['bgcolor'] = '#FF0000';
		}
	} else {
		// Not in table, red background
		$result2['bgcolor'] = '#FFCCCC';
	}
	// #CCFFFFF - Cool Blue
	// #FFFFBB - Cool Yellow
	// #FFCCCC - Cool Red
	
	if ($bol_query_type == 'sql') {
		$result2['displaycolor'] = $result2['bgcolor'];
		$result2['displaystring'] = (string) $result2['ID'] + 1000;
	}
	if ($result2['displaycolor'] == 'none') {
		$result2['displaycolor'] = $result2['bgcolor'];
	}
	$bols[] = $result2;
}
$num_bols = count($bols);
if ($num_bols == 0) $num_bols = 1;
// get claims info
$poclaims = getClaimNumbers($po);
if(count($poclaims)>$num_bols) $num_bols = count($poclaims);// set the number of rows to the greater of the number of BOLs or Claims
?>
    <tr valign="top"> 
      <td bgcolor="#FFFFFF" class="text_12" rowspan="<?php= $num_bols ?>"><?php echo date('m/d/Y h:ia', strtotime($result['ordered'])); ?></td>
      <td bgcolor="#FFFFFF" class="text_12" rowspan="<?php= $num_bols ?>"><a href="viewpo.php?po=<?php echo $result['ID']+1000; ?>" target="order_window" class="text_12"><?php echo $po; ?></a></td>
      <td bgcolor="#FFFFFF" class="text_12" rowspan="<?php= $num_bols ?>"><?php echo $result['merchantpo']; ?></td>
	  <td bgcolor="#FFFFFF" class="text_12" rowspan="<?php= $num_bols ?>"><?php echo $result['orderid']; ?></td>
	<?php /* Do First BoL */
	     $result2 = array_shift($bols);
		 if ($result2):
	?>
	      <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12">&nbsp;</td>
		  <td bgcolor="<?php= $result2['displaycolor'] ?>" align="center"><a href="/shipping/viewbol.php?id=<?php= $result2['ID'] ?>" target="order_window" class="text_12"><?php=$result2['displaystring'] ?></a></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"><?php= htmlentities($result2['carrier']) ?></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"><?php= htmlentities($result2['trackingnum']) ?></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"><?php= htmlentities($result2['shipdate']) ?></td>
	<?php else: ?>
		  <td bgcolor="#FFFFFF" colspan="5" class="text_12">&nbsp;</td>
	<?php endif;
	
// claim section
// see if this order has a claim associated with it first...
if(is_array($poclaims))
{
	?><td class="text_12" style="background-color: <?php
	switch($poclaims[0]['status'])
	{
		case "4":
			echo '#FF9900"';
			break;
		case "5":
			echo '#999999"';
			break;
		case "9":
			echo '#333333"';
			break;
		default:
			echo '#FFFFFF"';
			break;
	}
	?>><a href="../form.php?action=view&form=furniture&viewid=<?php= $poclaims[0]['id'] ?>" style="color: <?php if($poclaims[0]['status']=='9') { echo 'white'; } else { echo 'black'; } ?>"><?php= $poclaims[0]['id'] ?></a><?php
}
else
{
	?><td bgcolor="#FFFFFF">&nbsp;<?php
}
?></td>
	<td bgcolor="#FFFFFF" colspan="5" rowspan="<?php= $num_bols ?>" class="text_12">
		  	<a href="ch_summary.php?claim_redirect=<?php= $result['ID']+1000; ?>">
		  		<img src="/images/button_edit.png" border="0"></a>
</td>
</tr>
<?php

$current_line = 1;
$totalbols = count($bols);
foreach ($bols as $result2)
{
	?>
		<tr valign="top">
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"<?php
		  if($current_line == $totalbols) { ?> rowspan="<?php= count($poclaims)-$totalbols > 0 ? count($poclaims)-$totalbols : "1" ?>"<?php } ?>>&nbsp;</td>
		  <td bgcolor="<?php= $result2['displaycolor'] ?>" align="center"<?php
		  if($current_line == $totalbols) { ?> rowspan="<?php= count($poclaims)-$totalbols > 0 ? count($poclaims)-$totalbols : "1" ?>"<?php } ?>><a href="/shipping/viewbol.php?id=<?php= $result2['ID'] ?>" target="order_window" class="text_12"><?php=$result2['displaystring'] ?></a></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"<?php
		  if($current_line == $totalbols) { ?> rowspan="<?php= count($poclaims)-$totalbols > 0 ? count($poclaims)-$totalbols : "1" ?>"<?php } ?>><?php= htmlentities($result2['carrier']) ?></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"<?php
		  if($current_line == $totalbols) { ?> rowspan="<?php= count($poclaims)-$totalbols > 0 ? count($poclaims)-$totalbols : "1" ?>"<?php } ?>><?php= htmlentities($result2['trackingnum']) ?></td>
		  <td bgcolor="<?php= $result2['bgcolor'] ?>" class="text_12"<?php
		  if($current_line == $totalbols) { ?> rowspan="<?php= count($poclaims)-$totalbols > 0 ? count($poclaims)-$totalbols : "1" ?>"<?php } ?>><?php= htmlentities($result2['shipdate']) ?></td>
	<?php
	if(count($poclaims)>$current_line)
	{
		?>
		<td class="text_12" style="background-color: <?php
		switch($poclaims[$current_line]['status'])
		{
		case "4":
			echo '#FF9900"';
			break;
		case "5":
			echo '#999999"';
			break;
		case "9":
			echo '#333333"';
			break;
		default:
			echo '#FFFFFF"';
			break;
		}
		?>><a href="../form.php?action=view&form=furniture&viewid=<?php= $poclaims[$current_line]['id'] ?>" style="color: <?php if($poclaims[$current_line]['status']=='9') { echo 'white'; } else { echo 'black'; } ?>"><?php= $poclaims[$current_line]['id'] ?></a><?php
	}
	else
	{
		?><td bgcolor="#FFFFFF">&nbsp;<?php
	}
$current_line++;
?></td>
		</tr>
	<?php
} // End BoL While

// if the # of Claims > # Bols, add enough rows to show them in separate lines
while($current_line<count($poclaims))
{
	?>
	<tr valign="top">
	<td class="text_12" style="background-color: <?php
	switch($poclaims[$current_line]['status'])
	{
		case "4":
			echo '#FF9900"';
			break;
		case "5":
			echo '#999999"';
			break;
		case "9":
			echo '#333333"';
			break;
		default:
			echo '#FFFFFF"';
			break;
	}
	?>><a href="../form.php?action=view&form=furniture&viewid=<?php= $poclaims[$current_line]['id'] ?>" style="color: <?php if($poclaims[$current_line]['status']=='9') { echo 'white'; } else { echo 'black'; } ?>"><?php= $poclaims[$current_line]['id'] ?></a><?php
	$current_line++;
}


} // End PO While
?>
</table>
<?php
mysql_close($link);
?>
</body>
</html>
