<?php
$extra_javascript = "
var type = 'Access';
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
require('../header.php');
require("database.php");
require("secure.php");
require('../nav.php');
require('../sidenav.php');

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');

function getEmailVendorDate($po) {
	$po_id = $po-1000;
	$sql = "select email_vendor from order_forms where ID=$po_id";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query))
		return $result['email_vendor'];
	return '0000-00-00';
}

function customerRow($snapshot_id, $orig_user_id) {
        $sql = "SELECT `last_name`, `first_name` FROM `users` where `ID` = '".$orig_user_id."'";
        $query = mysql_query($sql);
        checkDBError($sql);
        if ($result = mysql_fetch_array($query)) {
            return "<tr>
		<td class=\"customerRowLeft\" colspan=\"10\"> ".$result[0].", ".$result[1]."</td>
		</tr>";
        } // else ... rest of function
	// If we don't have a corresponding user record, use a snapshot record
        $sql = "SELECT last_name, first_name, address, city, state, zip FROM snapshot_users WHERE ID=".$snapshot_id;
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		$return_string = "<tr> 
			<td class=\"customerRowLeft\"> ".$result[0].", ".$result[1]."</td>
		  </tr>";
	} else {
		$return_string = "<tr><td colspan=\"10\">error</td></tr>";
	}
	return $return_string;
}

function subtotalRow($price, $cost = null) {
        $return_string = "<tr> 
                <td class=\"fat_black_12\" colspan=\"3\" align=\"right\">Sub-Total</td>";
        $return_string .= "
                <td class=\"fat_black_12\" colspan=\"1\"  align=\"left\">  ".makethislooklikemoney($cost)."</td>";
        // if ($cost) {
            $return_string .= "
                <td class=\"fat_black_12\" colspan=\"6\"  align=\"left\">  ".makethislooklikemoney($price)."</td>";
        // }
        $return_string .= "
          </tr>";
	return $return_string;
}

function getFormName($header) {
	if ($header == NULL) return "Corrupt";
	$sql = "select name from snapshot_forms where ID=$header";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_array($query))
		return $result[0];
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

function getFormMin($form) {
	if (!is_numeric($form)) return false;
	$sql = "select `minimum` from forms where ID=".$form;
	$query = mysql_query($sql);
	checkDBError($sql);
	$minimum = "D:::0";
	if($result = mysql_fetch_array($query))
		$minimum = $result[0];
	return viewpo_getmin($minimum);
}


function formatDate($date) {
	return date('m/d/Y', strtotime($date));
}

if ($search == "" && $ordered == "")
{
// Calc Previous Work Day
switch (date('w')) {
	case 6: // Pass thru
	case 0: // Pass thru
	case 1: $pday = strtotime('last friday');
		break;
	default: $pday = strtotime('yesterday');
}
?>
<body class="hold-transition skin-blue sidebar-mini" bgcolor="#EDECDA">
<div class="wrapper">
    <div class="content-wrapper">
        <p align="center"><br><b>Enter Date to View Orders:</b></p>
        <form action="report-orders.php" method="get">
            <table border="0" cellpadding="0" cellspacing="3" align="center">
                <tr>
                    <td>&nbsp;</td>
                    <td><p>
                            <select name="m1">
                                <?php
                                for ($x=1; $x <=12; $x++) {
                                    if ($x == date("m",$pday))
                                        echo "<option value=\"$x\" selected>$monthName[$x]</option>";
                                    else
                                        echo "<option value=\"$x\">$monthName[$x]</option>";
                                }
                                ?>
                            </select>
                            <select name="d1">
                                <?php
                                for ($x=1; $x <=31; $x++) {
                                    if ($x == date("d",$pday))
                                        echo "<option value=\"$x\" selected>$x</option>";
                                    else
                                        echo "<option value=\"$x\">$x</option>";
                                }
                                ?>
                            </select>
                            <select name="y1">
                                <?php
                                // for ($x=2002; $x <= date("Y") + 1; $x++) {
                                for ($x=date("Y")+1; $x >= 2002; $x--) {
                                    if ($x == date("Y",$pday))
                                        echo "<option value=\"$x\" selected>$x</option>";
                                    else
                                        echo "<option value=\"$x\">$x</option>";
                                }
                                ?>
                            </select>
                            <select name="time1">
                                <option value='00:00:00'>Midnight</option>
                                <option value='15:01:00'>3:01pm</option>
                            </select>
                            <b>to</b>
                            <select name="m2">
                                <?php
                                for ($x=1; $x <=12; $x++) {
                                    if ($x == date("m"))
                                        echo "<option value=\"$x\" selected>$monthName[$x]</option>";
                                    else
                                        echo "<option value=\"$x\">$monthName[$x]</option>";
                                }
                                ?>
                            </select>
                            <select name="d2">
                                <?php
                                for ($x=1; $x <=31; $x++) {
                                    if ($x == date("d"))
                                        echo "<option value=\"$x\" selected>$x</option>";
                                    else
                                        echo "<option value=\"$x\">$x</option>";
                                }
                                ?>
                            </select>
                            <select name="y2">
                                <?php
                                //for ($x=2002; $x <= date("Y") + 1; $x++) {
                                for ($x=date("Y")+1; $x >= 2002; $x--) {
                                    if ($x == date("Y"))
                                        echo "<option value=\"$x\" selected>$x</option>";
                                    else
                                        echo "<option value=\"$x\">$x</option>";
                                }
                                ?>
                            </select>
                            <select name="time2">
                                <option value='23:59:59'>11:59pm</option>
                                <option value='15:00:59'>3:00pm</option>
                            </select>
                        </p></td>
                </tr>
                <tr>
                    <td align="right">
                        <p><b>Customer:</b></p></td>
                    <td><select name="customer" size="1">
                            <?php
                            $sql = "SELECT ID,first_name,last_name FROM users ORDER BY last_name,first_name";
                            $query = mysql_query($sql);
                            checkDBError($sql);
                            echo "<option value=\"\">All Customers</option>\n";
                            $teams = teams_list();
                            echo "<option value=\"team=all\">All Teams (".$teams[0]."-".$teams[count($teams)-1].")</option>\n";
                            unset($teamcount);
                            foreach($teams as $team) {
                                echo "<option value=\"team=".$team."\"";
                                //if ($team == $dealerteam)
                                //   echo " selected";
                                echo ">All of Team ".$team."</option>";
                            }

                            $managers = managers_list();
                            echo "<option value=\"manager=all\">All Managers</option>\n";
                            foreach($managers as $manager) {
                                echo "<option value=\"manager=".$manager['name']."\"";
                                //if ($team == $dealerteam)
                                //   echo " selected";
                                echo ">All of Manager ".$manager['name']."</option>";
                            }

                            $divisions = division_list();
                            echo "<option value=\"division=all\">All Divisions (".$divisions[0]."-".$divisions[count($divisions)-1].")</option>\n";
                            echo "<option value=\"division=\">Dealers with no division</option>\n";
                            foreach($divisions as $division) {
                                echo "<option value=\"division=".$division['name']."\">Division ".$division['name']."</option>";
                            }

                            while ($result = mysql_fetch_Array($query)) {
                                echo "<option value=\"".$result['ID']."\">".$result['last_name']." - ".$result['first_name']."</option>";
                            }
                            ?>
                        </select></td>
                </tr>
                <tr>
                    <td align="right">
                        <p><b>View:</b></p></td>
                    <td><p>
                            <TABLE>
                                <TR><TD><p><input name="order_proc" type="radio" value="0" checked>Unprocessed Orders &nbsp;</p></TD>
                    <TD><p><input name="order_proc" type="radio" value="1">Processed & Unprocessed Orders &nbsp;</p></TD>
                </TR>
                <TR><TD><p><input name="deleted" type="radio" value="0" checked>Active Orders &nbsp;</p></TD>
                    <TD><p><input name="deleted" type="radio" value="1">Deleted Orders </p></TD>
                </TR>
                <?php if ($backorder_enable) { ?>
                    <TR><TD><p><input name="backorder" type="radio" value="0" onclick="this.form.action = 'report-orders.php';" checked>Purchase Orders</p></TD>
                        <TD><p><input name="backorder" type="radio" value="1" onclick="this.form.action = 'report-backorder.php';">Back Orders</p></TD>
                    </TR>
                <?php } ?>
            </TABLE>
            </p></td>
            </tr>
            <tr>
                <td align="right"><p><b>View Individual POs:</b></p></td>
                <td><p>
                        <input type="checkbox" name="withpos" CHECKED />
                    </p></td>
            </tr>
            </tr>
            <tr>
                <td colspan="2"><p><i>&nbsp;<br>
                            Select only <b>ONE</b> of the following criteria:</i></p></td>
            </tr>
            <tr>
                <td align="right">
                    <p><b>PO #:</b></p></td>
                <td><p><input type="text" name="ponum" size="10"></p></td>
            </tr>
            <tr>
                <td align="right">
                    <p><b>Vendor:</b></p></td>
                <td><select name="vendor" size="1">
                        <?php
                        $sql = "SELECT ID,name FROM vendors ORDER BY name";
                        $query = mysql_query($sql);
                        checkDBError($sql);
                        echo "<option value=\"\">All Vendors</option>";
                        while ($result = mysql_fetch_Array($query)) {
                            echo "<option value=\"".$result['ID']."\">".$result['name']."</option>";
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td align="right">
                    <p><b>Category:</b></p></td>
                <td><select name="vendorcategory" size="1">
                        <option value="">All Categories</option>
                        <option value="Bedding">Bedding</option>
                        <option value="Case Goods">Case Goods</option>
                        <option value="Upholstery">Upholstery</option>
                    </select></td>
            </tr>
            <tr>
                <td align="right">
                    <p><b>Item Number:</b></p></td>
                <td><input type="text" name="itemnum" size="10"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;<br><input type="submit" name="search" value="View Orders">
                    <input type="reset" value="Reset"></td>
            </tr>
            </table>
        </form>
        <?php
        footer($link);
        exit;
        }
        if ($ordered == '') {
            $ordered = "$y1-$m1-$d1 $time1";
            $ordered2 = "$y2-$m2-$d2 $time2";
        }
        $daterange = "order_forms.ordered BETWEEN '$ordered' AND '$ordered2'";

        $withpo = false;
        if (isset($_GET['withpos'])&&$_GET['withpos']) {
            $withpo = true;
        }
        ?>
        <br>
        <table border="0" cellspacing="0" cellpadding="3">
            <tr bgcolor="#fcfcfc">
                <td class="fat_black_12">Name</td>
                <td class="fat_black_12">Form</td>
                <td class="fat_black_12">Date</td>
                <td class="fat_black_12">Cost</td>
                <td class="fat_black_12">Price</td>
                <td class="fat_black_12">Paid</td>
                <td class="fat_black_12">Proc'd</td>
                <td class="fat_black_12">PO #</td>
                <td class="fat_black_12" align="center">AutoLog</td>
                <!--<td class="fat_black_12">Details</td>-->
                <td class="fat_black_12">A</td>
            </tr>
            <tr><td colspan="10">&nbsp;</td></tr>
            <?php
            //add searching by customer

            if ($deleted == "") $deleted = 0; /* error catch in case this is not defined */
            $alreadycustomer = 0;
            $and_customer = "";
            $and_join = "";
            $and_order_proc = "";
            if (ereg("^team=(.+)",$customer, $reg)) {
                if ($reg[1] == 'all') {
                    // Return all teams in team list
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $teams = teams_list();
                    if (count($teams) > 0) {
                        foreach ($teams as $id => $val) {
                            $teams[$id] = "users.team='".mysql_escape_string($val)."'";
                        }
                        $and_customer = " (".implode(" OR ",$teams).") AND ";
                    }
                } else {
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $and_customer = " users.team='".mysql_escape_string($reg[1])."' AND ";
                }
                $alreadycustomer = 1;
            } elseif (ereg("^manager=(.+)",$customer, $reg)) {
                if ($reg[1] == 'all') {
                    // Return all teams in team list
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $managers = managers_list();
                    if (count($managers) > 0) {
                        foreach ($managers as $id => $val) {
                            $managers[$id] = "users.manager='".mysql_escape_string($val['name'])."'";
                        }
                        $and_customer = " (".implode(" OR ",$managers).") AND ";
                    }
                } else {
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $and_customer = " users.manager='".mysql_escape_string($reg[1])."' AND ";
                }
                $alreadycustomer = 1;
            } elseif (ereg("^division=(.*)",$customer, $reg)) {
                if ($reg[1] == 'all') {
                    // Return all teams in team list
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $divisions = division_list();
                    if (count($divisions) > 0) {
                        foreach ($divisions as $id => $val) {
                            $divisions[$id] = "users.division='".mysql_escape_string($val)."'";
                        }
                        $and_customer = " (".implode(" OR ",$divisions).") AND ";
                    }
                } else {
                    $and_join = " INNER JOIN users ON order_forms.user = users.ID";
                    $and_customer = " users.division='".mysql_escape_string($reg[1])."' AND ";
                }
                $alreadycustomer = 1;
            }

            if (($customer <> "") && (!$alreadycustomer))
                $and_customer = " order_forms.user=$customer AND ";

            if($order_proc == 0)
                $and_order_proc = " order_forms.email_vendor = '0000-00-00'  AND ";

            $export_join = " LEFT JOIN exported_orders_log ON order_forms.ID = exported_orders_log.po_id ";

            //$sql = "SELECT ID, ordered, form, user, total, processed FROM order_forms
            // WHERE $and_customer type='o' AND ordered BETWEEN '$ordered' AND '$ordered2' ORDER BY user";
            $sql = "SELECT order_forms.paid, order_forms.ID, CHAR_LENGTH(order_forms.comments) as comm_len, order_forms.ordered, order_forms.user, order_forms.form, order_forms.snapshot_form, order_forms.snapshot_user, order_forms.totalcost, order_forms.total, order_forms.processed, order_forms.type, exported_orders_log.access, exported_orders_log.access_queue, order_forms.discount_percentage
 FROM order_forms LEFT JOIN snapshot_users ON order_forms.snapshot_user = snapshot_users.id $and_join $export_join
 WHERE $and_customer order_forms.deleted=$deleted AND $and_order_proc order_forms.type='o' AND $daterange
 ORDER BY snapshot_users.last_name, snapshot_users.first_name, order_forms.snapshot_user, order_forms.ordered";

            if ($ponum <> "") {
                $po_id = ($ponum-1000);
                $sql = "SELECT order_forms.paid, order_forms.ID, CHAR_LENGTH(order_forms.comments) as comm_len, order_forms.ordered, order_forms.user, order_forms.form, order_forms.snapshot_form, order_forms.snapshot_user, order_forms.totalcost, order_forms.total, order_forms.processed, order_forms.type, exported_orders_log.access, exported_orders_log.access_queue, order_forms.discount_percentage FROM order_forms $export_join WHERE order_forms.ID='$po_id' ORDER BY order_forms.snapshot_user";
            }
            if ($vendor <> "") {
                $sql = "SELECT order_forms.paid, order_forms.ID, CHAR_LENGTH(order_forms.comments) as comm_len, order_forms.ordered, order_forms.user, order_forms.form, order_forms.snapshot_form, order_forms.snapshot_user, order_forms.totalcost, order_forms.total,
	 order_forms.processed, order_forms.type, exported_orders_log.access, exported_orders_log.access_queue, order_forms.discount_percentage FROM order_forms INNER JOIN forms ON order_forms.form = forms.ID  $and_join $export_join
	 WHERE $and_customer order_forms.deleted=$deleted AND $and_order_proc order_forms.type='o' AND forms.vendor=$vendor
	 AND order_forms.ordered AND $daterange ORDER BY order_forms.user, order_forms.snapshot_user";
            }
            if ($vendorcategory <> "") {
                $sql = "SELECT order_forms.paid, order_forms.ID, CHAR_LENGTH(order_forms.comments) as comm_len, order_forms.ordered, order_forms.user, order_forms.form, order_forms.snapshot_form, order_forms.snapshot_user, order_forms.totalcost, order_forms.total,
	 order_forms.processed, order_forms.type, exported_orders_log.access, exported_orders_log.access_queue, order_forms.discount_percentage FROM order_forms INNER JOIN forms ON order_forms.form = forms.ID
         INNER JOIN `vendors` ON `vendors`.`ID` = `forms`.`vendor`
         $and_join $export_join
	 WHERE $and_customer order_forms.deleted=$deleted AND $and_order_proc order_forms.type='o' AND vendors.Access_type=\"".mysql_escape_string($vendorcategory)."\"
	 AND order_forms.ordered AND $daterange ORDER BY order_forms.user, order_forms.snapshot_user";
            }
            if ($itemnum <> "") {
                /* item description will be in either form_items or order_snapshot depending on the date of the order */

                /* check the form_items table */
                $sql = "SELECT orders.po_id FROM orders INNER JOIN snapshot_items ON orders.item=snapshot_items.id
	 WHERE snapshot_items.partno='".trim($itemnum)."'";
                $query = mysql_query($sql);
                checkDBError($sql);
                $postring = "";
                while ($result = mysql_fetch_array($query))
                    $postring .= $result[0].",";
                $postring = substr($postring,0,strlen($postring)-1); /* delete last comma from the string */
                $sql = "SELECT order_forms.paid, order_forms.ID, CHAR_LENGTH(order_forms.comments) as comm_len, order_forms.ordered, order_forms.user, order_forms.form, order_forms.snapshot_form, order_forms.snapshot_user, order_forms.totalcost, order_forms.total, order_forms.processed, order_forms.type, exported_orders_log.access, exported_orders_log.access_queue, order_forms.discount_percentage FROM order_forms $and_join $export_join WHERE $and_customer order_forms.deleted=$deleted AND $and_order_proc order_forms.type='o' AND $daterange AND order_forms.ID IN ($postring) ORDER BY order_forms.user, order_forms.snapshot_user";
            }
            $query = mysql_query($sql);
            checkDBError($sql);
            $user = 0;
            $grand_total = 0;
            $cost_grand_total = 0;
            $sub_total = 0;
            $cost_sub_total = 0;

            while ($result = mysql_fetch_array($query)) {

                if ($user <> $result['user']) {
                    if ($sub_total) {
                        echo subtotalRow($sub_total, $cost_sub_total);
                        $sub_total = 0;
                        $cost_sub_total = 0;
                    }
                    $snapshot_user = $result['snapshot_user'];
                    $user = $result['user'];
                    echo customerRow($snapshot_user, $user);
                    $intUnpaidCount = 0;
                    $sql_unpaid = "SELECT order_forms.user, SUM(order_forms.total) AS balance, users.ID, users.first_name, users.last_name, users.dealer_type, users.credit_limit FROM order_forms INNER JOIN users ON order_forms.user = users.ID WHERE order_forms.deleted=0 AND order_forms.user=".$result['user']." GROUP BY order_forms.user ORDER BY users.last_name, users.first_name";

                    $query_unpaid = mysql_fetch_array(mysql_query($sql_unpaid));

                    $balance = $query_unpaid['balance'];

                }
                $po = ($result['ID'] + 1000);
                $grand_total += $result['total'];
                $sub_total += $result['total'];
                $cost_grand_total += $result['totalcost'];
                $cost_sub_total += $result['totalcost'];
                if ($withpo) {
                    ?>
                    <tr<?php	$minimum = getFormMin($result['form']);
                    if (($result['type'] == 'o') && $minimum) {
                        $overlimit = false;
                        if ($minimum['type'] == 'P') {
                            //$overlimit = true;
                        } elseif ($minimum['type'] == 'D') {
                            if ($minimum['minimum'] > $result['total'])
                                $overlimit = true;
                        }

                        if ($overlimit)
                            echo ' bgcolor="#EFC2C6"';
                    }
                    ?>>
                        <td>&nbsp;</td>
                        <td class="text_12"><?php
                            if ($result['type'] == 'o')
                                echo getFormName($result['snapshot_form']);
                            else
                                echo getOrderType($result['type']);
                            ?></td>
                        <td class="text_12"><?php echo formatDate($result['ordered']); /* ordered = 1 */ ?></td>
                        <td class="text_12"><?php echo makeThisLookLikeMoney($result['totalcost']); ?></td>
                        <td class="text_12" <?php if ($result['discount_percentage']): ?> bgcolor="#CCFFCC"<?php endif; ?>><?php echo makeThisLookLikeMoney($result['total']); ?></td>
                        <td class="text_12"> <?php if ($result['paid']==1) echo 'PAID';?><?php if ($balance>0) echo "<span title='". makeThisLookLikeMoney($balance)."'><font style='color:red;'>*</font></span>";?></td>
                        <td class="text_12" align="center"> <?php if ($result['type'] == 'o') echo $result['processed']; ?></td>
                        <td class="text_12" align="center"><a href="report-orders-details.php?po=<?php echo $po; ?>&request=<?php echo urlencode($_SERVER['QUERY_STRING']); ?>"><?php echo $po; ?></a><?php if ($result['comm_len'] > 0) { echo "*"; } ?>&nbsp;[<a href="report-orders-details.php?po=<?php echo $po; ?>&for=vendor&request=<?php echo urlencode($_SERVER['QUERY_STRING']); ?>">V</a>]</td>
                        <td class="text_12" align="center"><?php
                            if ($result['type'] == 'o') {
                                $email_vendor = getEmailVendorDate($po);
                                echo "<a href=\"autolog\" OnClick=\"window.open('report-orderdb.php?po=$po&form=".$result['form']."&username2=".$result['user']."&vendor=".$result['ID']."&date=".$result['ordered']."','orderdb','width=350,height=300');return(false);\">";
                                if ($result['processed'] == "Y")
                                    if ($email_vendor == "0000-00-00")
                                        echo "AutoLog";
                                    else
                                        echo "Logged ".date("n/j/y",strtotime($email_vendor));
                                echo "</a>";
                                if ($result['processed'] == "Y")
                                    echo "";
                            }
                            ?></td>
                        <!--<td class="text_12"> <a href="report-orders-details.php?po=<?php echo $po; ?>&request=<?php echo urlencode($_SERVER['QUERY_STRING']); ?>">Details</a><?php if ($result['comm_len'] > 0) { echo "*"; } ?>-->
                        </td>
                        <TD class='text_12'>
                            <?php
                            if (is_null($result['access'])) {
                                ?>
                                <A href='export' OnClick="csvqueuepre(this,'<?php echo $po; ?>','<?php echo $result['snapshot_user'];?>','<?php echo $result['snapshot_form'];?>');return(false);"><IMG src='../images/export_icon.gif' border=0></A>
                                <?php
                            }
                            elseif($result['access_queue'] == 1) {
                                ?>
                                <A href='export' OnClick="window.open('report-logAandM90.php?type=Access&exportonly=yes','export','width=350,height=300');return(false);">Queued</A>
                                <?php
                            }
                            else {
                                echo "Y";
                            }
                            ?>
                        </TD>
                    </tr>
                    <?php
                }
            }
            if ($sub_total || $cost_sub_total) {
                echo subtotalRow($sub_total, $cost_sub_total);
                $sub_total = 0;
                $cost_sub_total = 0;
            }
            ?>
            <tr>
                <td class="fat_black_12" colspan="3" align="right">Display Total:</td>
                <td class="fat_black_12" colspan="1" align="left"><?php echo makethislooklikemoney($cost_grand_total); ?></td>
                <td class="fat_black_12" colspan="4" align="left"><?php echo makethislooklikemoney($grand_total); ?></td>
            </tr>
        </table>
    </div>
</div>
<?php require ("../footer-new.php"); ?>
</body>

</html>

