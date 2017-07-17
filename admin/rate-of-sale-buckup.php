<?
require("database.php");
require("secure.php");
require("menu.php");


// count_pieces = Boolean value to determine whether quantities of sets or quantities of pieces are used in calculating totals
// if true, any set quantities in orders are multiplied by the setqty in the snapshot_items table to get the # of pieces
$count_pieces = false;
if ($_GET['group_forms'] == 'Y') {
    $group_forms = true;
} else {
    $group_forms = false;
}

// doSubtotal function
// adds subtotal as needed by vendor, form & header

function doSubtotal($arr, $item, $title, $bgcolor="") {
    if($bgcolor!="") $bgcolor = " bgcolor='$bgcolor'";
    if($arr['Set'] != 0 || $arr['Matt'] != 0 || $arr['Box'] != 0) { echo "<TR$bgcolor><TD class='fat_black_12' align=right>$title Total:</TD>\n";
        if (is_array($_GET['quantities'])) {
            foreach($_GET['quantities'] as $quant) {
                echo "  <TD class='fat_black_12' align=center>" . number_format($arr[$quant]) . "</TD>\n";
            }
        }
        if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
            echo "  <TD class='fat_black_12' align=center>" . number_format($item) . "</TD>\n";
        }
        echo "</TR>";
    }
}

?>

<SCRIPT type='text/javascript'>
  function show_hide(divid) {
    if(document.getElementById("div_" + divid)) {
      var displaytype = (document.getElementById("div_" + divid).style.display == 'none') ? new Array('', 'Hide') : new Array('none', 'Show');
      document.getElementById("div_" + divid).style.display = displaytype[0];
      document.getElementById("link_" + divid).innerHTML = displaytype[1] + " Details";
    }
  }
</SCRIPT>
<B>Rate of Sale:</B><BR>
<FORM name='rate_form' method='get' action='<? echo $_SERVER['PHP_SELF']; ?>'>
    Customer: <select name="customer" size="1">
        <?
        $sql = "SELECT ID,first_name,last_name FROM users ORDER BY last_name,first_name";
        $query = mysql_query($sql);
        checkDBError($sql);
        echo "<option value=\"\">All Customers</option>\n";
        $teams = teams_list();
        // echo "<option value=\"team=:ALL:\"".(($_GET['customer'] == ':ALL:')?" SELECTED":"").">All Teams (".$teams[0]."-".$teams[count($teams)-1].")</option>\n";
        unset($teamcount);
        foreach($teams as $team) {
            echo "<option value=\"team=".$team."\"";
            //if ($team == $dealerteam)
            //   echo " selected";
            echo (($_GET['customer'] == 'team='.$team)?" SELECTED":"");
            echo ">All of Team ".$team."</option>";
        }

        while ($result = mysql_fetch_Array($query)) {
            echo "<option value=\"".$result['ID']."\"".(($_GET['customer'] == $result['ID'])?" SELECTED":"").">".$result['last_name']." - ".$result['first_name']."</option>";
        }
        ?>
    </select>
    <BR><BR>
    Date Range:
    <?php
    $monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
    ?>
    <select name="m1">
        <?
        for ($x=1; $x <=12; $x++) {
            echo "<option value=\"$x\">$monthName[$x]</option>";
        }
        ?>
    </select>
    <select name="d1">
        <?
        for ($x=1; $x <=31; $x++) {
            echo "<option value=\"$x\">$x</option>";
        }
        ?>
    </select>
    <select name="y1">
        <?
        for ($x=2002; $x <= date('Y')+1; $x++) {
            echo "<option value=\"$x\">$x</option>";
        }
        ?>
    </select>
    <b>to</b>
    <select name="m2">
        <?
        for ($x=1; $x <=12; $x++) {
            echo "<option value=\"$x\">$monthName[$x]</option>";
        }
        ?>
    </select>
    <select name="d2">
        <?
        for ($x=1; $x <=31; $x++) {
            echo "<option value=\"$x\">$x</option>";
        }
        ?>
    </select>
    <select name="y2">
        <?
        for ($x=2002; $x <=date('Y')+1; $x++) {
            echo "<option value=\"$x\">$x</option>";
        }
        unset($monthName);
        unset($x);
        ?>
    </SELECT>&nbsp;&nbsp;&nbsp;<INPUT type='submit' value='Filter Results'><BR><BR>
    <?php
    if (isset($_GET['vendor'])) {
        //-- Then dates will have been passed through
        ?>
        <SCRIPT type='text/javascript'>
          document.rate_form.m1.value = '<?php echo $_GET['m1'];?>';
          document.rate_form.d1.value = '<?php echo $_GET['d1'];?>';
          document.rate_form.y1.value = '<?php echo $_GET['y1'];?>';
          document.rate_form.m2.value = '<?php echo $_GET['m2'];?>';
          document.rate_form.d2.value = '<?php echo $_GET['d2'];?>';
          document.rate_form.y2.value = '<?php echo $_GET['y2'];?>';
        </SCRIPT>
    <?php
    }
    else {
    $old_date = DATE("Y-m-d", strtotime("-30 days"));
    ?>
        <SCRIPT type='text/javascript'>
          document.rate_form.m1.value = '<?php echo DATE("n", strtotime($old_date));?>';
          document.rate_form.d1.value = '<?php echo DATE("j", strtotime($old_date));?>';
          document.rate_form.y1.value = '<?php echo DATE("Y", strtotime($old_date));?>';
          document.rate_form.m2.value = '<?php echo DATE("n");?>';
          document.rate_form.d2.value = '<?php echo DATE("j");?>';
          document.rate_form.y2.value = '<?php echo DATE("Y");?>';
        </SCRIPT>
        <?php
        unset($old_date);
    }
    ?>
    Quantities: <INPUT type='checkbox' id='qSet' name='quantities[]' value='Set' CHECKED>Set Qty&nbsp;&nbsp;&nbsp;
    <INPUT type='checkbox' id='qMatt' name='quantities[]' value='Matt' CHECKED>Matt Qty&nbsp;&nbsp;&nbsp;
    <INPUT type='checkbox' id='qBox' name='quantities[]' value='Box' CHECKED>Box Qty&nbsp;&nbsp;&nbsp;
    <I>Selecting none of these will show only the total (Set + Matt + Box)</I>
    <?php
    if (isset($_GET['team'])) {
        ?>
        <SCRIPT type='text/javascript'>
          document.getElementById('qSet').checked = false;
          document.getElementById('qMatt').checked = false;
          document.getElementById('qBox').checked = false;
          <?php
          if (is_array($_GET['quantities'])) {
              foreach($_GET['quantities'] as $quant) {
                  echo "document.getElementById('q" . $quant . "').checked = true;\n";
              }
          }
          ?>
        </SCRIPT>
        <?php
    }
    ?>
    <BR><BR>
    Group Result By Vendor &amp; Form<INPUT type='checkbox' id='group_forms' name='group_forms' value='Y' <? if ($group_forms) echo "CHECKED"; ?>>
    <BR><BR>
    Choose a vendor:
    <SELECT name='vendor' onChange='if (typeof(document.rate_form.set_or_item) != "undefined") { document.rate_form.set_or_item.value = ""; } submit()'>
        <OPTION value=''></OPTION>
        <OPTION value=':ALL:'>All Vendors</OPTION>
        <?

        $query = "SELECT ID, name FROM vendors ORDER BY name";
        $results = mysql_query($query);
        checkDBError($query);
        while ($row = mysql_fetch_array($results)) {
            echo "<OPTION value='" . $row['ID'] . "'>" . $row['name'] . "</OPTION>\n";
        }
        mysql_free_result($results);
        echo "</SELECT><BR><BR>";
        //-- Grab the forms, or if there is only one, auto select it and keep this part hidden
        if ($_GET['vendor'] != '') {
            echo "<SCRIPT>document.rate_form.vendor.value = '" . $_GET['vendor'] . "';</SCRIPT>\n";
            if ($_GET['vendor'] != ":ALL:") {
                $and_vendor_id = " AND vendors.ID = '" . $_GET['vendor'] . "' ";
            }
            $query = "SELECT forms.ID, forms.name FROM vendors, forms WHERE " .
                "vendors.ID = forms.vendor " . $and_vendor_id .
                "ORDER BY forms.name";
            $results = mysql_query($query);
            checkDBError($query);
            if (mysql_num_rows($results) == 1) {
                $row = mysql_fetch_array($results, MYSQL_ASSOC);
                $_GET['form'] = $row['ID'];
                echo "<B>Vendor only has one form: " . $row['name'] . "</B><BR><BR>";
                $one = TRUE;
            }
            elseif (mysql_num_rows($results) > 1) {
                echo "Choose a form: <SELECT name='form' onChange='submit();'>\n";
                echo "<OPTION value=''></OPTION>\n<OPTION value=':ALL:'>All Forms</OPTION>\n";
                while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
                    echo "<OPTION value='" . $row['ID'] . "'>" . $row['name'] . "</OPTION>\n";
                }
                echo "</SELECT><BR><BR>\n";
            }
            else {
                echo "That vendor has no forms.<BR>";
                exit;
            }
            mysql_free_result($results);
        }
        //-- Grab the items/sets
        if ($_GET['form'] != '') {
            if (!$one) {
                echo "<SCRIPT>document.rate_form.form.value = '" . $_GET['form'] . "';</SCRIPT>\n";
            }
            echo "Choose a header/set: <SELECT name='set_or_item' onChange='if (this.value == \":ALL:\") { if (confirm(\"Are you sure you want to choose all? It can take quite some time to processs\")) { submit();}} else { submit();}'>\n";
            echo "<OPTION value=''></OPTION>\n<OPTION value=':ALL:'>All Sets/Items</OPTION>\n";
            if ($_GET['form'] != ":ALL:") {
                $and_form_id = " AND forms.ID = '" . $_GET['form'] . "' ";
            }
            // Get the items
            $query ="SELECT form_headers.ID as a_id, form_headers.header as a_header " .
                "FROM vendors, forms, form_headers WHERE " .
                "vendors.ID = forms.vendor " . $and_vendor_id . " AND " .
                "forms.ID = form_headers.form " . $and_form_id .
                "ORDER BY CAST(form_headers.header AS SIGNED), form_headers.header";
            $results = mysql_query($query);
            checkDBError($query);
            while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
                echo "<OPTION value='H:::" . $row['a_id'] . "'>" . $row['a_header'] . "</OPTION>\n";
            }
            mysql_free_result($results);
            echo "</SELECT>\n";
        }
        if ($_GET['set_or_item'] != '') {
        echo "<SCRIPT>document.rate_form.set_or_item.value = '" . $_GET['set_or_item'] . "';</SCRIPT>\n";
        ?>
    <BR><BR>
        <table border="0" cellspacing="0" cellpadding="3">
            <tr bgcolor="#fcfcfc">
                <td>&nbsp;</td>
                <?
                if (is_array($_GET['quantities'])) {
                    foreach($_GET['quantities'] as $quant) {
                        echo '<td class="fat_black_12" align=center>' . $quant . ' Qty</td>' . "\n";
                    }
                }
                if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
                    echo '<td class="fat_black_12" align=center>Total</td>' . "\n";
                }
                ?>
            </tr>
            <?
            $month1 = strlen($_GET['m1']) == 1 ? '0'.$_GET['m1'] : $_GET['m1'];
            $day1 = strlen($_GET['d1']) == 1 ? '0'.$_GET['d1'] : $_GET['d1'];
            $month2 = strlen($_GET['m2']) == 1 ? '0'.$_GET['m2'] : $_GET['m2'];
            $day2 = strlen($_GET['d2']) == 1 ? '0'.$_GET['d2'] : $_GET['d2'];
            $from_date = DATE("Y-m-d", strtotime($_GET['y1'] . "-" . $month1 . "-" . $day1))." 00:00:00";
            $to_date = DATE("Y-m-d", strtotime($_GET['y2'] . "-" . $month2 . "-" . $day2))." 23:59:59";

            if ($_GET['set_or_item'] != '' && $_GET['set_or_item'] != ":ALL:") {
                $pieces = explode(":::", $_GET['set_or_item']);
                $and_header = " AND form_headers.ID = ".$pieces[1];
            }
            // - Filtering by Customer
            if (isset($_GET['customer']) && $_GET['customer'] && $_GET['customer'] != ':ALL:') {
                if (substr($_GET['customer'],0,5) == "team=" && substr($_GET['customer'],5) != ':ALL:') {
                    $and_customer_selected = " AND users.team = '" . substr($_GET['customer'],5) . "'";
                } elseif (is_numeric($_GET['customer'])) {
                    $and_customer_selected = " AND users.ID = '".$_GET['customer']."'";
                }
            }
            // End Filtering by Customer.
            if ($_GET['vendor'] != ":ALL:") {
                $and_vendor_id = " AND vendors.ID = '" . $_GET['vendor'] . "'";
            }
            if ($_GET['form'] != ":ALL:") {
                $and_form_id = " AND forms.ID = '" . $_GET['form'] . "'";
            }
            $query = "SELECT users.last_name, vendors.ID as vendor_ID, vendors.name as vendor_name, forms.ID as form_ID, forms.name as form_name, orders.setqty as Setqty,";
            $query .= "orders.mattqty as Mattqty, orders.qty as Boxqty, (orders.setqty + orders.mattqty + orders.qty) as total, order_forms.ID as pos, orders.ID as linenums, orders.item as lineitem, form_headers.ID as header_ID, form_headers.header";
            $query .= " as header_name FROM (((((order_forms, orders) LEFT OUTER JOIN users ON (order_forms.user = users.ID)) LEFT OUTER JOIN forms ";
            $query .= "ON (order_forms.form = forms.ID)) LEFT OUTER JOIN form_headers ON (forms.ID = form_headers.form)) LEFT OUTER JOIN snapshot_items ON (orders.item = snapshot_items.ID)) LEFT OUTER JOIN vendors ON (forms.vendor = ";
            $query .= "vendors.ID) WHERE order_forms.processed = 'Y' AND order_forms.deleted != 1 and order_forms.type = 'o' and order_forms.total > 0 and ";
            $query .= "order_forms.ID = orders.po_id and order_forms.ordered BETWEEN '$from_date' AND '$to_date'".$and_vendor_id.$and_form_id.$and_customer_selected.$and_header;
            //    $query .= " GROUP BY vendors.name, forms.name, form_headers.header";
            $query .= " GROUP BY ";
            if (!$group_forms) $query .= "snapshot_items.partno, ";
            $query .= "orders.item, users.last_name, order_forms.ID";
            checkdberror($query);
            $res = mysql_query($query);
            if(!$res) echo mysql_error();
            $vendor = "";
            $dealer = "";
            $form = "";
            $set = "";
            $header_id = 0;
            $item_part_desc = "";
            $item_total = 0;
            $items = array();
            $vendortots = array();
            $formtots = array();
            $headertots = array();
            $itemtots = array();
            $totals = array();
            $vendor_item_total = 0;
            $form_item_total = 0;
            $header_item_total = 0;
            $item_subtotal = 0;
            $div_id = 0;
            $show_item = false;

            while($data = mysql_fetch_assoc($res)) {

                $sql2 = "SELECT header, partno, description FROM snapshot_items WHERE id = ".$data['lineitem'];
                $que2 = mysql_query($sql2);
                checkdberror($sql2);
                $res2 = mysql_fetch_row($que2);

                if ($vendor != $data['vendor_name']&&$group_forms) {
                    doSubtotal($itemtots, $item_subtotal, $item_part_desc);
                    if($show_item) echo "</TBODY>\n";
                    doSubtotal($headertots, $header_item_total, $set, '#0099FF');
                    doSubtotal($formtots, $form_item_total, $form, '#BB5555');
                    doSubtotal($vendortots, $vendor_item_total, $vendor, '#669900');
                    $vendor = $data['vendor_name'];
                    $vendortots['Set'] = 0;
                    $vendortots['Matt'] = 0;
                    $vendortots['Box'] = 0;
                    $vendor_item_total = 0;
                    $formtots['Set'] = 0;
                    $formtots['Matt'] = 0;
                    $formtots['Box'] = 0;
                    $form_item_total = 0;
                    $headertots['Set'] = 0;
                    $headertots['Matt'] = 0;
                    $headertots['Box'] = 0;
                    $header_item_total = 0;
                    $item_part_desc = $res2[1].' - '.$res2[2];
                    $itempart = $data['lineitem'];
                    $itemtots['Set'] = 0;
                    $itemtots['Matt'] = 0;
                    $itemtots['Box'] = 0;
                    $item_subtotal = 0;
                    echo "<TR bgcolor='#669900'><TD class='fat_black_12' colspan=5>Vendor: " . $vendor . "</TD></TR>\n";
                    $show_item = false;
                }
                if ($form != $data['form_name']&&$group_forms) {
                    doSubtotal($itemtots, $item_subtotal, $item_part_desc);
                    if($show_item) echo "</TBODY>\n";
                    doSubtotal($headertots, $header_item_total, $set, '#0099FF');
                    doSubtotal($formtots, $form_item_total, $form, '#BB5555');
                    $form = $data['form_name'];
                    $formtots['Set'] = 0;
                    $formtots['Matt'] = 0;
                    $formtots['Box'] = 0;
                    $form_item_total = 0;
                    $headertots['Set'] = 0;
                    $headertots['Matt'] = 0;
                    $headertots['Box'] = 0;
                    $header_item_total = 0;
                    $item_part_desc = $res2[1].' - '.$res2[2];
                    $itempart = $data['lineitem'];
                    $itemtots['Set'] = 0;
                    $itemtots['Matt'] = 0;
                    $itemtots['Box'] = 0;
                    $item_subtotal = 0;
                    echo "<TR bgcolor='#BB5555'><TD class='fat_black_12' colspan=5>Form: " . $form . "</TD></TR>\n";
                    $show_item = false;
                }

                if ($header_id != $res2[0]) {
                    $sql3 = "SELECT header FROM snapshot_headers WHERE id = ".$res2[0];
                    $que3 = mysql_query($sql3);
                    checkdberror($sql3);
                    $res3 = mysql_fetch_row($que3);
                    $new_set = $res3[0];
                    $header_id = $res2[0];
                }
                if ($set != $new_set) {
                    if(!$group_forms) {
                        echo "</TBODY><TBODY>\n";
                        doSubtotal($itemtots, $item_subtotal, "Item ");
                    } else doSubtotal($itemtots, $item_subtotal, $item_part_desc);
                    if($show_item) echo "</TBODY>\n";
                    doSubtotal($headertots, $header_item_total, $set, '#CCCCFF'); // , '#0099FF');
                    //$sql3 = "SELECT header FROM snapshot_headers WHERE id = ".$res2[0];
                    //$que3 = mysql_query($sql3);
                    //checkdberror($sql3);
                    //$res3 = mysql_fetch_row($que3);
                    //$set = $res3[0];
                    $set = $new_set;
                    //$header_id = $res2[0];
                    $headertots['Set'] = 0;
                    $headertots['Matt'] = 0;
                    $headertots['Box'] = 0;
                    $header_item_total = 0;
                    $item_part_desc = $res2[1].' - '.$res2[2];
                    $itempart = $data['lineitem'];
                    $itemtots['Set'] = 0;
                    $itemtots['Matt'] = 0;
                    $itemtots['Box'] = 0;
                    $item_subtotal = 0;
                    echo "<TR bgcolor='#0099FF'><TD class='fat_black_12' colspan=5>$title_word" . $set . "</TD></TR>\n";
                    $show_item = false;
                }

                if ($itempart != $data['lineitem']&&$group_forms) {
                    doSubtotal($itemtots, $item_subtotal, $item_part_desc);
                    $item_part_desc = $res2[1].' - '.$res2[2];
                    $itempart = $data['lineitem'];
                    $itemtots['Set'] = 0;
                    $itemtots['Matt'] = 0;
                    $itemtots['Box'] = 0;
                    $item_subtotal = 0;
                    if($show_item) echo "</TBODY>\n";
                    $show_item = false;
                }
                // check the #s of the quantities we want/need
                // if they're zero, move on to the next record
                if(is_array($_GET['quantities'])) {
                    $go = false;
                    foreach($_GET['quantities'] as $quant) {
                        if($data[$quant.'qty']!=0) $go = true;
                    }
                    if(!$go) continue;
                }
                $sql2 = "SELECT partno, description, setqty as qty_in_set FROM snapshot_items WHERE id = ".$data['lineitem'];
                $que2 = mysql_query($sql2);
                checkdberror($sql2);
                $res2 = mysql_fetch_assoc($que2);
                if($res2['partno'] != $items[0] || $res2['description'] != $items[1]) {
                    if($show_item) {
                        if (!$group_forms) {
                            echo "</TBODY><TBODY>";
                            doSubtotal($itemtots, $item_subtotal, "Item ");
                            $itemtots['Set'] = 0;
                            $itemtots['Matt'] = 0;
                            $itemtots['Box'] = 0;
                            $item_subtotal = 0;
                        }
                        echo "</TBODY>\n";
                    }
                    echo "<TR bgcolor='orange'>\n" .
                        "<TD class='fat_black_12' colspan=4>Item: " . $res2['partno'] . " - " . $res2['description']."</TD>";
                    echo "<TD class='text_12'><A id='link_" . $div_id . "' href='javascript:show_hide(\"" . $div_id . "\")'>Show Details</A>";
                    echo "</TD></TR>\n";
                    echo "<TBODY id='div_" . $div_id . "' style='display:none'>\n";
                    $div_id++;
                    $items[0] = $res2['partno'];
                    $items[1] = $res2['description'];
                    $qty_in_set = $res2['setqty'];
                    $show_item = true;
                }
                $out_line = "<TR>\n";
                $out_line .= "  <TD class='fat_black_12' align=right>";
                $out_line .= "<a href=\"report-orders-details.php?po=".($data['pos']+1000)."\">";
                $out_line .= $data['last_name']." - PO # ".($data['pos']+1000);
                $out_line .= "</a>";
                $out_line .= "</TD>\n";
                $cust_total = 0;
                if (is_array($_GET['quantities'])) {
                    foreach($_GET['quantities'] as $quant) {
                        if($quant=="Set") {
                            $quantity = $count_pieces ? ($data[$quant.'qty'] * $qty_in_set) : $data[$quant.'qty'];
                        } else {
                            $quantity = $data[$quant.'qty'];
                        }
                        $out_line .= "  <TD class='fat_black_12' align=center>" . $data[$quant.'qty'] . "</TD>\n";
                        $cust_total += $quantity;
                        $itemtots[$quant] += $quantity;
                        $vendortots[$quant] += $quantity;
                        $formtots[$quant] += $quantity;
                        $headertots[$quant] += $quantity;
                        $vendor_item_total += $quantity;
                        $form_item_total += $quantity;
                        $header_item_total += $quantity;
                        $item_subtotal += $quantity;
                        $item_total += $quantity;
                        $totals[$quant] += $quantity;
                    }
                } else {
                    $total_to_use = $count_pieces ? ($data['Boxqty'] + $data['Mattqty'] + ($data['Setqty'] * $qty_in_set)) : $data['total'];
                    $cust_total += $total_to_use;
                    $item_total += $total_to_use;
                    $item_subtotal += $total_to_use;
                    $vendortots['Box'] += $total_to_use;
                    $formtots['Box'] += $total_to_use;
                    $headertots['Box'] += $total_to_use;
                    $itemtots['Box'] += $total_to_use;
                    $vendor_item_total += $total_to_use;
                    $form_item_total += $total_to_use;
                    $header_item_total += $total_to_use;
                    $totals['Box'] += $total_to_use;
                }
                $out_line .= "  <TD class='fat_black_12' align=center>" . $cust_total . "</TD>\n";
                $out_line .= "</TR>";
                if ($cust_total > 0) {
                    echo $out_line;
                }
            }
            if ($group_forms) doSubtotal($itemtots, $item_subtotal, $item_part_desc);
            else doSubtotal($itemtots, $item_subtotal, "Item ");
            echo "</TBODY>\n";
            if ($group_forms) {
                doSubtotal($headertots, $header_item_total, $set, '#0099FF');
                doSubtotal($formtots, $form_item_total, $form, '#BB5555');
                doSubtotal($vendortots, $vendor_item_total, $vendor, '#669900');
            } else {
                doSubtotal($headertots, $header_item_total, $set, '#CCCCFF');
            }

            if ($item_total > 0) {
                echo "<TR>\n";
                echo "  <TD class='fat_black_12' align=right>Total:</TD>\n";
                if (is_array($_GET['quantities'])) {
                    foreach($_GET['quantities'] as $quant) {
                        echo "  <TD class='fat_black_12' align=center>" . number_format($totals[$quant]) . "</TD>\n";
                    }
                } else {
                    echo " <TD class='fat_black_12' align=center>" . number_format($totals['Boxqty']) . "</TD>\n";
                }
                if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
                    echo "  <TD class='fat_black_12' align=center>" . number_format($item_total) . "</TD>\n";
                }
                echo "</TR>";
            } else {
                echo "<TR><TD colspan=5><I>None were sold within that time frame</I></TD></TR>\n";
                //echo "<SCRIPT>document.getElementById('link_" . $div_id . "').innerHTML = '';</SCRIPT>\n";
            }
            }
            ?></table><?
        ?>
