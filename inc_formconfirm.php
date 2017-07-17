<?php

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
    die ('<h2>Direct Execution Prohibited</h2>');

$orderas = false;
$modify_avail = true;
$block_blocks = true;
if (isset($_GET['orderas']) && is_numeric($_GET['orderas']) && secure_is_admin()) {
    $orderas = true;
    $block_blocks = false;
    $userid = $_GET['orderas'];
}

// Does form have Dropship?
$dropshipforms = array(987,928);
if (in_array($form, $dropshipforms)) {
	$dropship = true;
} else {
	$dropship = false;
}

// Does Dealer have Access to this Form
if (!secure_is_admin() && !vendor_access('D', $userid, $form)) die("You don't have Access to this form");

$items = array();
$backorder = array();
$bo_priceper = array();
$bo_shown = array();
$num_of_items = $_POST['num_of_items'];
for ($c = 0; $c < $num_of_items; $c++) {
    $item = array();
    if (!isset($_POST["item${c}"]))
        break;

    $item['item_id'] = $_POST["item${c}"];
    $item['setqty'] = (int)$_POST["setqty${c}"];
    $item['mattqty'] = (int)$_POST["mattqty${c}"];
    $item['qty'] = (int)$_POST["qty${c}"];
    if ($_POST["backorderqty${c}"] > 0) {
        $item['backorder'] = $_POST["backorderqty${c}"];
        $backorder[$item['item_id']] = $_POST["backorderqty${c}"];
    }
    if ($item['setqty'] == 0 && $item['mattqty'] == 0 && $item['qty'] == 0 && !$item['backorder'])
        continue;
    $items[] = $item;
    if (!$orderas) {
        if (!$MoS_enabled && ($item['qty'] < 0) || ($item['setqty'] < 0) || ($item['mattqty'] < 0)) {
            die("You may not order negative amounts of items");
        }
    }
}

$order = submitOrder($userid, 1, '', $_POST['form'], $items, true, false, $modify_avail, $block_blocks);
?>
<h3 align="center">Order Confirmation</h3>

<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
    <tr valign="top">
        <td class="text_16" width="50%">
            <b>Dealer:</b><br>
            <?php
            echo $order['user_lastname'] . ", " . $order['user_firstname'] . "<br>";
            //	if ($result['address'] != "")
            //		echo $result['address']."<br>".$result['city'].", ".$result['state'].". ".$result['zip']."<br>";
            ?>
            <br>
            <b>Date: </b> <?php echo date("F j, Y", strtotime($order['ordered'])); ?>
        </td>
        <td class="text_16" width="50%"><b>Vendor: </b> <br>
            <?php
            echo $order['vendor_name'] . "<br>";
            if (secure_is_admin()) {
                if ($order['vendor_address'] != "")
                    echo $order['vendor_address'] . "<br>" . $order['vendor_city'] . ", " . $order['vendor_state'] . ". " . $order['vendor_zip'] . "<br>";
            }
            ?>
        </td>
    </tr>
</table>
<br>
<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
    <tr>
        <td width="20%" colspan="2" class="orderTH">Item</td>
        <td width="15%" colspan="2" class="orderTH">Set</td>
        <td width="15%" colspan="2" class="orderTH">Matt</td>
        <td width="15%" colspan="<?php echo $backorder ? "2" : "5"; ?>" class="orderTH">Box</td>
        <?php if ($backorder) { ?>
            <td width="10%" colspan="2" class="orderTH">BO</td>
            <td width="10%" class="orderTH" align="right">BO Total</td>
        <?php } ?>
        <td width="10%" class="orderTH" align="right">Total</td>
    </tr>
    <?php

    $subtotal = 0;
    $total_cubic_ft = 0;
    $oldheader = 0;
    $hiddenfields = array();
    $x = 0;

    function add_hidden_field($name, $value)
    {
        global $hiddenfields;
        $hiddenfields[] = "<input type=\"hidden\" name=\"${name}\" value=\"${value}\" />";
    }

    $out_of_stock = 0;
    $totalqty = 0;
    foreach ($order['items'] as $item) {
        if ($oldheader != $item['header']) {
            $oldheader = $item['header'];
            echo "<tr><td colspan=\"12\" class=\"orderTDheading\">" . getHeader($item['header']) . "</td></tr>";
        }

        $price = str_replace("$", "", $item['price']);
        $set = str_replace("$", "", $item['set_']);
        $matt = str_replace("$", "", $item['matt']);

        $total = 0;
        $showbox = false;
        if ($item['suff_stock']) {
            $item_insufficient_stock = false;
            $tr_class = "orderTD";
        } else {
            $item_insufficient_stock = true;
            $tr_class = "orderTDfail";
        }
        print "<tr class=\"${tr_class}\">\n";
        print "<td>";
        if ($item['discount']) {
            echo '*';
        }
        print $item['partno'];
        print "</td>\n";
        print "<td>" . $item['description'] . "</td>\n";
        if ($item['setqty'] != 0) {
            print "<td>Set: " . $item['setqty'] . "</td>\n";
            print "<td align=\"right\">" . makeThisLookLikeMoney($item['set']) . "</td>\n";
            $showbox = true;
        } else
            print "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
        if ($item['mattqty'] != 0) {
            print "<td>Matt: " . $item['mattqty'] . "</td>\n";
            print "<td align=\"right\">" . makeThisLookLikeMoney($item['matt']) . "</td>\n";
            $showbox = true;
        } else
            print "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
        if ($item['qty'] != 0) {
            if ($showbox)
                $text = "Box: " . $item['qty'];
            else
                $text = $item['qty'];
            if ($item_insufficient_stock)
                $text = $text . " (" . $item['avail'] . " avail.)";
            print "<td>" . $text . "</td>\n";
            print "<td align=\"right\">" . makeThisLookLikeMoney($item['price']) . "</td>\n";
        } else
            print "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
        if ($backorder && $backorder[$item['item_id']]) {
            $bo_shown[$item['item_id']] = true;
            print "<td>" . $backorder[$item['item_id']] . "</td>\n";
            print "<td align=\"right\">" . makeThisLookLikeMoney($item['price']) . "</td>\n";
            print "<td align=\"right\">" . makeThisLookLikeMoney($backorder[$item['item_id']] * $item['price']) . "</td>\n";
            if (!$order['backorder_total']) $order['backorder_total'] = 0;
            $order['backorder_total'] += $backorder[$item['item_id']] * $item['price'];
        } else {
            print "<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
        }
        print "<td align=\"right\">" . makeThisLookLikeMoney($item['total']) . "</td>\n";
        print "</tr>\n";
        $subtotal += $total;
        add_hidden_field("item${x}", $item['item_id']);
        add_hidden_field("setqty${x}", $item['setqty']);
        add_hidden_field("mattqty${x}", $item['mattqty']);
        if ($backorder[$item['item_id']]) add_hidden_field("backorderqty${x}", $backorder[$item['item_id']]);
        add_hidden_field("item${x}", $item['item_id']);
        add_hidden_field("qty${x}", $item['qty']);
        $x = $x + 1;
    }
    $num_of_items = $x;
    $block = false;
    foreach ($order['messages'] as $message) {
        if (checkbox2boolean($message['block'])) {
            $block = true;
        }
    }
    if ((!$block_blocks) || !$block) {

    ?>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Approximate Volume:</b></td>
        <td class="text_12" align="right"><b><?php echo $order['total_cubic_ft'] ?> cu. ft.</b></td>
    </tr>
    <?php if ($order['total_seats'] > 0): ?>
        <tr>
            <td colspan="11" align="right" class="text_12"><b>Total Seats:</b></td>
            <td class="text_12" align="right"><b><?php echo $order['total_seats'] ?></b></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Pieces:</b></td>
        <td class="text_12" align="right"><b><?php echo $order['totalqty'] ?></b></td>
    </tr>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Product Total:</b></td>
        <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['product_total']) ?></b></td>
    </tr>
    <?php if ($backorder) { ?>
        <tr>
            <td colspan="11" align="right" class="text_12"><b>Backorder Total:</b></td>
            <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['backorder_total']) ?></b></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Discount:</b></td>
        <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['discount']) ?></b></td>
    </tr>
    <?php if ($order['item_discount']) { ?>
        <tr>
            <td colspan="11" align="right" class="text_12"><b>Item Discounts:</b></td>
            <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['item_discount']) ?></b></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Subtotal:</b></td>
        <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['subtotal']) ?></b></td>
    </tr>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Freight:</b></td>
        <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['freight']) ?></b></td>
    </tr>
    <?php if ($order['item_freight']) { ?>
        <tr>
            <td colspan="11" align="right" class="text_12"><b>Item Freight:</b></td>
            <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['item_freight']) ?></b></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="11" align="right" class="text_12"><b>Grand Total:</b></td>
        <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($order['total']) ?></b></td>
    </tr>
    <tr>
        <td colspan="12" class="text_12">&nbsp;</td>
    </tr>
    <?php if ($order['item_discount']) { ?>
        <tr>
            <td colspan="11" class='fat_black_12'>* - Indicates Item Discount</td>
        </tr>
    <?php } ?>
</table>
    <center> <?php
        foreach ($order['messages'] as $message) {
            echo "<p class=\"alert\">" . $message['text'] . "</p>";
        } ?></center>
<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
    <tr>
        <td class="orderTH" colspan=2>Comments</td>
    </tr>
    <tr>
        <td class="text_12"><?php
            print "<form name=\"form1\" method=\"post\" action=\"" . ($MoS_enabled ? "MoS_form-submit.php" : "form-submit.php") . ($orderas ? '?orderas=' . $userid : '') . "\">\n";

            $sql = "select address, city, address2, city2 from users where ID=$userid";
            $query = mysql_query($sql);
            checkDBError();
            if ($rows = mysql_fetch_array($query)) {
                $addresses = array();
                $addresses['1'] = $rows['address'].", ".$rows['city'];
               
                if ($rows['address2'] <> "") {
                    $addresses['2'] = $rows['address2'].", ".$rows['city2'];
                } else {
                    add_hidden_field("user_address", 1);
                }
            }
            ?>
            <div id="other_addr_input" style="float: right; width: 45%;">
              <table>
		<?php if ($dropship): ?>
                <tr class="dropship-select">
                  <td class="orderTH">Drop Ship:</td>
                  <td><input type="checkbox" name="shipto_dropship" id="shipto_dropship" onchange="dropShipSwitch();" onpropertychange="dropShipSwitch();" value="Y" /></td>
                </tr>
                <tr class="dropship">
                  <td class="orderTH">Name:</td>
                  <td><input type="text" name="shipto_last_name" /></td>
                </tr>
                <tr class="dropship">
                  <td class="orderTH">Address:</td>
                  <td><input type="text" name="shipto_address" /></td>
                </tr>
                <tr class="dropship">
                  <td class="orderTH">City, State, Zip:</td>
                  <td><input type="text" name="shipto_city" />, <input type="text" name="shipto_state" size="2" /> <input type="text" name="shipto_zip" /></td>
                </tr>
                <tr class="dropship">
                  <td class="orderTH">Phone:</td> 
                  <td><input type="text" name="shipto_phone" /></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="orderTH">Ordering Location:</td>
                  <td>
                    <select name="user_address">
                    <?php foreach ($addresses as $address_id => $address): ?>
                      <option value="<?php= $address_id ?>"><?php= htmlentities($address) ?></option>
                    <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
              </table>
            </div>
            <?php
            print "<textarea name=\"comments\" cols=\"50\" rows=\"7\" id=\"comments\"></textarea><br>";
            print "<br>\n";
            add_hidden_field("freight_percentage", $order['freight_percentage']);
            add_hidden_field("discount_percentage", $order['discount_percentage']);
            add_hidden_field("total", $order['total']);
            add_hidden_field("total_cubic_ft", $order['total_cubic_ft']);
            add_hidden_field("num_of_items", $num_of_items);
            add_hidden_field("form", $order['form']);
            add_hidden_field("type", "o");
            print implode("\n", $hiddenfields);
            ?><div style="clear: right;"></div><?php
            print "<input type=\"submit\" name=\"Submit1\" value=\"Place This Order\">\n";
            print "</form>\n";
            } else { ?>
            <center> <?php
                foreach ($order['messages'] as $message) {
                    echo "<p class=\"alert\">" . $message['text'] . "</p>";
                }
                } ?>
                <form name="form2" method="post"
                      action="<?php echo($MoS_enabled ? "MoS_form-view.php" : "form-view.php"); ?>?ID=<?php echo $_POST['form']; ?><?php if ($orderas): ?>&orderas=<?php= $userid ?><?php endif; ?>">
                    <?php
                    foreach ($_POST as $k => $v) {
                        echo "<input type=\"hidden\" name=\"" . $k . "\" id=\"" . $k . "\" value=\"" . htmlspecialchars(stripslashes($v)) . "\" />";
                    }
                    ?>
                    <input type="submit" name="Submit2" value="Revise Your Order">
                    <?php if ($order['out_of_stock'] == 0) { ?>
                </form>
            </center>
        </td>
    </tr>
</table>
<?php } ?>
<p>&nbsp;</p>
<?php mysql_close($link); ?>
<?php if ($dropship): ?>
<script>
function dropShipSwitch() {
    var dropShipElements = document.getElementsByClassName("dropship");
    if (document.getElementById('shipto_dropship').checked) {
       for (var i = 0; i < dropShipElements.length; i++) {
           dropShipElements[i].style.display = '';
       }
    } else {
       for (var i = 0; i < dropShipElements.length; i++) {
           dropShipElements[i].style.display = 'none';
       }
    }
}
dropShipSwitch();
</script>
<?php endif; ?>
</body>
</html>
