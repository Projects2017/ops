<?
require("database.php");
require("secure.php");

$TxsPerPage = 30;
if (isset($_REQUEST['req_page'])&&$_REQUEST['req_page'])
    $page = $_REQUEST['req_page'];
else
    $page = 1;

// Remove Processing of Pagination, Only All!
$page = 'all';

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
        $pages[$id] = "<a href=\"?req_page=".$page."\">".$page."</a>";
    }
    $pages = implode(' ',$pages);
    return $pages;
}

function getUserName($user)
{
    global $sql;
    $sql = "select first_name,last_name from users where ID=$user";
    $query = mysql_query($sql);
    checkDBError();

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

$sql = "SELECT `credit_limit` FROM `users` WHERE `ID` = '".$userid."'";
$result = mysql_query($sql);
checkDBError($sql);
$result = mysql_fetch_assoc($result);
$credit_limit = $result['credit_limit'];
if ($credit_limit < 0)
    $credit_limit = 0;

?>
<html>
<head>
    <title>RSS</title>
    <link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>
<?
require('menu.php');

$real_page = $page - 1; // Page 1 is 0 as long as far as the computer is concerned.
$start = $real_page * $TxsPerPage;
unset($real_page); // Don't need this var anymore
if ($allpages)
    $query = mysql_query("SELECT * FROM order_forms WHERE user=".$userid." AND deleted=0 ORDER BY `ordered` DESC");
else
    $query = mysql_query("SELECT * FROM order_forms WHERE user=".$userid." AND deleted=0 ORDER BY `ordered` DESC LIMIT ".$start.", ".$TxsPerPage);
checkDBError();

if (mysql_num_rows($query) == 0) {
    echo "<span class=\"fat_red\">There are no transactions in the database for this dealer.</span>";
}
else {
    $txns = array();
    while ($result = mysql_fetch_Array($query)) {
        $txns[] = $result;
    }
    $last_date = $txns[count($txns) - 1]['ordered']; // Grabbing the ID of the last record
    $sql = "SELECT COUNT(*), SUM(`total`) FROM `order_forms` WHERE `user` = '".$userid."' AND `deleted` = 0 AND `ordered` < '".$last_date."'";
    $query = mysql_query($sql);
    checkDBError($sql);
    $result = mysql_fetch_array($query);
    $totaltxns = $result[0] + count($txns); // Calculating total transactions
    $totalpages = ceil($totaltxns / $TxsPerPage); // Overridden later if page not 1
    $total = $result[1];
    $new_arry = array_reverse($txns, true);
    foreach($new_arry as $num => $txn) {
        $total += $txn['total'];
        $txns[$num]['running_balance'] = $total;
    }
    if ($page == 1) $balance = $total;
    else {
        $sql = "SELECT COUNT(*), SUM(`total`) FROM `order_forms` WHERE `user` = '".$userid."' AND `deleted` = 0";
        $query = mysql_query($sql);
        checkDBError($sql);
        $result = mysql_fetch_array($query);
        $totalpages = ceil($result[0] / $TxsPerPage);
        $balance = $result[1];
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
    <table border="0" cellspacing="0" cellpadding="5" width="70%" align="center">
        <tr>
            <td colspan="8" align="center"><h3>Summary for <? echo getUserName($userid); ?></h3></td>
            <!--<td colspan="3" align="right">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="9HYBSWNQLL5AE">
                <input type="image" src="http://www.thedailyzen.net/images/payaccount.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
            </td>-->
        </tr>
        <!--<tr>
            <td colspan="7" align="left" class="text_12">
              Order of entries:
              <form>
              <select id="type" onchange="window.open(this.options[this.selectedIndex].value,'_top')">
                  <option value="summary.po.php">Ordered by PO (paged)</option>
                  <option value="summary.date.php" selected>Ordered by Date</option>
              </select>
              </form>
          </td>
        </tr>-->
        <? if (!$nopages) { ?>
            <!--<tr>
    <td colspan="7" align="right" class="text_12">
		Page:
		<? if ($page != 1) { ?><a href="?req_page=<? echo $page - 1; ?>"><< Prev</a><? } ?>
		[<? echo $pager; ?>]
		<? if ($page != $totalpages) { ?><a href="?req_page=<? echo $page + 1; ?>">Next >></a> <? } ?>
		<? if ($allpages) { ?>[All Pages]<? } else { ?>[<a href="?req_page=all">All Pages</a>]<? } ?>
	</td>
  </tr>-->
        <? } /* end if !$nopages */ ?>
        <tr bgcolor="#fcfcfc">
            <td bgcolor="#CCCC99" class="fat_black_12">Order Date</td>
            <td bgcolor="#CCCC99" class="fat_black_12">PO #</td>
            <td bgcolor="#CCCC99" class="fat_black_12">Form</td>
            <td bgcolor="#CCCC99" class="fat_black_12" align="right">Total</td>
            <td bgcolor="#CCCC99" class="fat_black_12" align="right">Balance</td>
            <td bgcolor="#CCCC99" class="fat_black_12" align="right">Avail</td>
            <td bgcolor="#CCCC99" class="fat_black_12" align="center">Details</td>
            <td bgcolor="#CCCC99" class="fat_black_12">Comments</td>
        </tr>
        <tr>
            <td bgcolor="#FFFFFF" class="fat_black_12" colspan="4" align="right">Current Balance as of <? echo(date('m/d/Y')); ?>:</td>
            <td bgcolor="#FFFFFF" class="fat_black_12" align="right"><? echo makeThisLookLikeMoney($balance); ?></td>
            <td bgcolor="#FFFFFF" class="fat_black_12" colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td bgcolor="#FFFFFF" class="fat_black_12" colspan="5" align="right">Order To Limit:</td>
            <td bgcolor="#FFFFFF" class="fat_black_12" colspan="2"><? echo makeThisLookLikeMoney($credit_limit); ?></td>
            <td bgcolor="#FFFFFF" class="fat_black_12" colspan="2">&nbsp;</td>
        </tr>
        <?
        foreach ($txns as $result) {
            //if ($result['type'] == "o")
            $po = $result['ID'] + 1000;
            //else
            //	$po = "";
            $avail_credit = $credit_limit - $result['running_balance'];
            if ($avail_credit < 0)
                $avail_credit = 0;
            ?>
            <tr valign="top">
                <td bgcolor="#FFFFFF" class="text_12"><? echo date('m/d/Y', strtotime($result['ordered'])); ?></td>
                <td bgcolor="#FFFFFF" class="text_12"><? echo $po; ?></td>
                <td bgcolor="#FFFFFF" class="text_12"><? echo getFormName($result['snapshot_form']); ?></td>
                <td bgcolor="#FFFFFF" class="text_12" align="right" nowrap><? echo makeThisLookLikeMoney($result['total']); ?></td>
                <td bgcolor="#FFFFFF" class="text_12" align="right" nowrap><b><? echo makeThisLookLikeMoney($result['running_balance']); ?></b></td>
                <td bgcolor="#FFFFFF" class="text_12" align="right" nowrap><b><? echo makeThisLookLikeMoney($avail_credit); ?></b></td>
                <td bgcolor="#FFFFFF" class="text_12" align="center"><a href="orders-details.php?po=<? echo $result['ID']+1000; ?>">view&nbsp;<? echo getOrderType($result['type']); ?></a></td>
                <td bgcolor="#FFFFFF" class="text_12"><? echo $result['comments']; ?></td>
            </tr>
            <?
        }
        ?>
    </table>
    <?
}
mysql_close($link);
?>
<p align="center">[<a href="selectvendor.php">Back to Vendor List</a>]</p>
</body>
</html>
