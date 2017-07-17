<?
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
    $TxsPerPage = 60;
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
    $sql = "SELECT order_forms.*, exported_orders_log.mas90, exported_orders_log.mas90_queue FROM order_forms $export_join $vendor_join WHERE deleted=0 ".$where." ORDER BY `ID` DESC";
else
    $sql = "SELECT order_forms.*, exported_orders_log.mas90, exported_orders_log.mas90_queue FROM order_forms $export_join $vendor_join WHERE deleted=0 ".$where." ORDER BY `ID` DESC LIMIT ".$start.", ".$TxsPerPage;
$query = mysql_query($sql);
checkDBError($sql);

$txns = array();
while ($result = mysql_fetch_Array($query)) {
    $txns[] = $result;
}
$last_id = $txns[count($txns) - 1]['ID']; // Grabbing the ID of the last record
$sql = "SELECT COUNT(*) FROM `order_forms` ".$vendor_join." WHERE `order_forms`.`deleted` = 0 AND `order_forms`.`ID` < '".$last_id."' ".$where;
$query = mysql_query($sql);
checkDBError($sql);
$result = mysql_fetch_array($query);
$totaltxns = $result[0] + count($txns); // Calculating total transactions
$totalpages = ceil($totaltxns / $TxsPerPage); // Overridden later if page not 1
$new_arry = array_reverse($txns, true);

if ($page != 1) {
    $sql = "SELECT COUNT(*), SUM(`order_forms`.`total`) FROM `order_forms` ".$vendor_join." WHERE `order_forms`.`deleted` = 0 ".$where;
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
<table id="ordersummary" border="0" cellspacing="0" cellpadding="5" width="90%" align="center"<?
if ($nopages || $allpages||$totalpages == 1) { ?> class="sortable"<? }
?>>
    <tr class="skiptop">
        <td colspan="8" align="center"><h3>RSS Orders System Summary</h3></td>
    </tr>
    <tr class="skiptop">
        <td colspan="9" class="text_12" align="center" style="vertical-align: bottom;">
            <form action="<? echo $_SERVER['PHP_SELF']; ?>" method="get">
                Date From: <input class="date" type="text" value="<? echo $filter['datefrom']; ?>" id="f_datefrom" name="f_datefrom" size="8">
                To: <input class="date" type="text" value="<? echo $filter['dateto']; ?>" id="f_dateto" name="f_dateto" size="8">
                Type:
                <select id="f_type" name="f_type">
                    <option value=""<? if ($filter['type'] == '') echo ' SELECTED'; ?>>All</option>
                    <option value="|o|f"<? if ($filter['type'] == '|o|f') echo ' SELECTED'; ?>><? echo getOrderType('o'); ?> or <? echo getOrderType('f'); ?></option>
                    <option value="o"<? if ($filter['type'] == 'o') echo ' SELECTED'; ?>><? echo getOrderType('o'); ?></option>
                    <option value="f"<? if ($filter['type'] == 'f') echo ' SELECTED'; ?>><? echo getOrderType('f'); ?></option>
                    <option value="c"<? if ($filter['type'] == 'c') echo ' SELECTED'; ?>><? echo getOrderType('c'); ?></option>
                </select>
                Dealer: <select id="f_user" name="f_user">
                    <option value=0>All Dealers</option>
                    <?
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
                    <?
                    $userlist = db_vendors_getlist();
                    foreach ($userlist as $value) {
                        echo "\t\t\t<option value=\"".$value[id]."\"";
                        if ($filter['vendor'] == $value[id])
                            echo " selected";
                        echo ">".$value[name]."</option>\n";
                    } ?>
                </select>
                <br />
                PO: <input type="text" value="<? echo $filter['po']; ?>" id="f_po" name="f_po" size="4">
                POs per Page:
                <input type="text" value="<? echo $filter['txnsperpage']; ?>" id="f_txnsperpage" name="f_txnsperpage" size="2">
                <input type="submit" value="Filter By">
            </form>
        </td>
    </tr>
    <? if (!$nopages) { ?>
        <tr class="skiptop">
            <td colspan="9" align="right" class="text_12" style="vertical-align: bottom;">
                Page:
                <? if ($page != 1) { ?><a href="?<? echo rewrite_url(array('req_page' => $page - 1)); ?>"><< Prev</a><? } ?>
                [<? echo $pager; ?>]
                <? if ($page != $totalpages) { ?><a href="?<? echo rewrite_url(array('req_page' => $page + 1)); ?>">Next >></a> <? } ?>
                <? if ($allpages) { ?>[All Pages]<? } else { ?>[<a href="?<? echo rewrite_url(array('req_page' => 'all')); ?>">All Pages</a>]<? } ?>
            </td>
        </tr>
    <? } /* end if !$nopages */ ?>
    <tr bgcolor="#fcfcfc">
        <td bgcolor="#CCCC99" class="fat_black_12">Order Date</td>
        <td bgcolor="#CCCC99" class="fat_black_12">PO #</td>
        <td bgcolor="#CCCC99" class="fat_black_12">Form</td>
        <td bgcolor="#CCCC99" class="fat_black_12">Dealer</td>
        <td bgcolor="#CCCC99" class="fat_black_12" align="right">Amount</td>
        <td bgcolor="#CCCC99" class="fat_black_12" align="center">Details</td>
        <td bgcolor="#CCCC99" class="fat_black_12">Comments</td>
        <!--
	<td bgcolor="#CCCC99" class="fat_black_12">MAS90</td>
	<? if (secure_is_superadmin()) { ?><td bgcolor="#CCCC99" class="fat_black_12">DO</td>
	<td bgcolor="#CCCC99" class="fat_black_12">Print</td><? } ?>
	-->
        <td bgcolor="#CCCC99" class="fat_black_12" colspan=3>Actions</td>
    </tr>
    <?
    foreach ($txns as $result) {
        //if ($result['type'] == "o")
        $po = $result['ID'] + 1000;
        //else
        //	$po = "";
        ?>
        <tr valign="top">
            <td bgcolor="#FFFFFF" class="text_12"><? echo date('m/d/Y h:ia', strtotime($result['ordered'])); ?></td>
            <td bgcolor="#FFFFFF" class="text_12"><? echo $po; ?></td>
            <td bgcolor="#FFFFFF" class="text_12"><? echo getFormName($result['snapshot_form']); ?></td>
            <td bgcolor="#FFFFFF" class="text_12"><? echo getUserName($result['snapshot_user']); ?></td>
            <td bgcolor="#FFFFFF" class="text_12" align="right"><? echo makeThisLookLikeMoney($result['total']); ?></td>
            <td bgcolor="#FFFFFF" class="text_12" align="center"><a href="viewpo.php?po=<? echo $result['ID']+1000; ?>" target="order_window">view&nbsp;<? echo getOrderType($result['type']); ?></a><?php if ($result['type'] == 'o'): ?>&nbsp;<a href="viewpo.php?po=<? echo $result['ID']+1000; ?>&for=vendor" target="order_window">[V]</a><?php endif; ?></td>
            <td bgcolor="#FFFFFF" class="text_12"><? echo $result['comments']; ?></td>
            <td bgcolor="#FFFFFF" class="text_12">
                <?php
                if (is_null($result['mas90'])) {
                    ?>
                    <A OnClick="csvqueuepre(this, '<?php echo $po; ?>', '<?php echo $result['snapshot_user'];?>', '<?php echo $result['snapshot_form'];?>'); return (false);"><IMG src='../images/export_icon.gif' border=0></A>
                    <?php
                }
                elseif($result['mas90_queue'] == 1) {
                    ?>
                    <A href='export' OnClick="window.open('report-logAandM90.php?type=MAS90&exportonly=yes','export','width=350,height=300');return(false);">Queued</A>
                    <?php
                }
                else {
                    echo "Y";
                }
                ?>
            </td>
            <? if (secure_is_superadmin()) { ?>
                <td bgcolor="#FFFFFF" class="text_12">
                    <form action="report-orders-delete.php">
                        <input type="hidden" name="po" value="<?php echo $po; ?>">
                        <input type="hidden" name="delete" value="1">
                        <A href='Delete' OnClick="delete_order(this,<?php echo $po; ?>);return(false);"><img src="/images/button_drop.png" border=0></a>
                    </form>
                </td>
                <td bgcolor="#FFFFFF" class="text_12">
                    <? if ($result['printedonsummary'] != 'Y') { ?><a OnClick="print_order(this,<?php echo $po; ?>);return(false);"><img border=0 src="/images/print.gif" alt="Print"></a><? } else { ?>&nbsp;<? } ?>
                </td>
            <? } ?>
        </tr>
        <?
    }
    ?>
</table>
<?
mysql_close($link);
?>
</body>
</html>
