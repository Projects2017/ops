<?
require("database.php");
require("secure.php");

if (!secure_is_manager()) die("You don't have access to this area");

if (!isset($_REQUEST['datefrom'])) {
    $_REQUEST['datefrom'] = date("m/d/Y", strtotime("-1 month"));
}
if (!isset($_REQUEST['dateto'])) {
    $_REQUEST['dateto'] = date("m/d/Y");
}

function getdealerstats($userid) {
    if (!is_numeric($userid)) die('Non-Numeric UserId Passed');
    $datefrom = date('Y-m-d',strtotime($_REQUEST['datefrom'])).' 00:00:00';
    $dateto = date('Y-m-d',strtotime($_REQUEST['dateto'])).' 23:59:59';

    $return = array();
    // Bedding
    $sql = 'SELECT COUNT(`order_forms`.`ID`) AS `count`, SUM(`order_forms`.`total`) AS `total` FROM `order_forms` INNER JOIN `forms` ON `order_forms`.`form` = `forms`.`ID` INNER JOIN `vendors` ON `forms`.`vendor` = `vendors`.`ID` WHERE `order_forms`.`user` = "'.$userid.'" AND `order_forms`.`deleted` = 0 AND `order_forms`.`type` = "o" AND `vendors`.`Access_type` = "Bedding" AND `order_forms`.`ordered` BETWEEN "'. $datefrom .'" AND "'. $dateto .'";';
    $query = mysql_query($sql);
    checkDBerror($sql);
    $temp = mysql_fetch_assoc($query);
    $return['bed_total'] = $temp['total'];
    $return['bed_count'] = $temp['count'];
    // Case Goods
    $sql = 'SELECT COUNT(`order_forms`.`ID`) AS `count`, SUM(`order_forms`.`total`) AS `total` FROM `order_forms` INNER JOIN `forms` ON `order_forms`.`form` = `forms`.`ID` INNER JOIN `vendors` ON `forms`.`vendor` = `vendors`.`ID` WHERE `order_forms`.`user` = "'.$userid.'" AND `order_forms`.`deleted` = 0 AND `order_forms`.`type` = "o" AND `vendors`.`Access_type` IN ("Case Goods", "Upholstery") AND `order_forms`.`ordered` BETWEEN "'. $datefrom .'" AND "'. $dateto .'";';
    $query = mysql_query($sql);
    checkDBerror($sql);
    $temp = mysql_fetch_assoc($query);
    $return['case_total'] = $temp['total'];
    $return['case_count'] = $temp['count'];
    // Totals
    $return['total'] = $return['bed_total'] + $return['case_total'];
    $return['count'] = $return['bed_count'] + $return['case_count'];
    return $return;
}

// Start Filtering
// Get Filters from POST/GET
$filter = array();
foreach($_REQUEST as $key => $value) {
    $reg = array();
    if(ereg("^f_(.+)",$key, $reg))// && $value
        $filter[$reg[1]] = $value;
}
/*
// Defaults
//if (!array_key_exists('team',$filter)) {
//	$filter['team'] = "*";
//}
$filter['disabled'] = '!Y';
$filter['nonPMD'] = '!Y';
// Assemble Filter Where
$where = " WHERE ";
$x = 0;
// Pull out email since it's wierd
if (array_key_exists('email', $filter)) {
    $emailtemp = $filter['email'];
    unset($filter['email']);
    if ($emailtemp) {
        $x++; // Incriment it so that it recognizes this as the first argument
        if ($x != 1)
            $where .= " AND ";
        $where .= "(`users`.`email` LIKE '%".mysql_escape_string($emailtemp)."%' ";
        $where .= "OR `users`.`email2` LIKE '%".mysql_escape_string($emailtemp)."%' ";
        $where .= "OR `users`.`email3` LIKE '%".mysql_escape_string($emailtemp)."%')";
    }
}
if (array_key_exists('state',$filter)&&$filter['state'] == '') {
    $filter['state'] = '0';
}
foreach ($filter as $k => $i) {
    if ($i == "0")
        continue;
    $x++;
    if ($x != 1)
        $where .= " AND ";
    if ($i[0] == "+") {
        $i = ltrim($i, "+");
        $sign = ">=";
    } elseif ($i[0] == "-") {
        $i = ltrim($i, "-");
        $sign = "<=";
    } elseif ($i[0] == "!") {
        $i = ltrim($i, "!");
        $sign = "!=";
    } else {
        $sign = "=";
    }
    // echo "$k -> $i<br>";
    $where .= "`users`.`".mysql_escape_string($k)."` ".$sign." '".mysql_escape_string($i)."'";
}
if ($emailtemp) {
    $filter['email'] = $emailtemp;
}
if ($x == 0)
    $where = "";
// Finish Filtering!



$sql = "select * from users ".$where." order by last_name ";
//echo $sql;
$query = mysql_query($sql);
checkDBError($sql);
$numrows = mysql_num_rows($query);
*/
$dealers = db_user_filterlist($filter);
?>
<html>
<head>
    <title>RSS</title>
    <link rel="stylesheet" href="styles.css" type="text/css">
    <script src="include/common.js"></script>
    <script src="include/sorttable.js"></script>
</head>
<body>
<?php require('menu.php'); ?>
<table class="sortable" id="users" border="0" cellspacing="0" cellpadding="5" align="left">
    <tr class="skiptop">
        <td colspan="10" class="text_12">
            <?
            echo "<FORM method=\"POST\" enctype=\"multipart/form-data\">";
            ?>
            Team: <SELECT name="f_team">
                <OPTION VALUE="*" <? if ($filter['team'] == '*') echo "SELECTED"; ?>>*</OPTION>
                <OPTION VALUE="=*" <? if ($filter['team'] == '=*') echo "SELECTED"; ?>>Only *</OPTION>
                <OPTION VALUE="=" <? if ($filter['team'] == '=') echo "SELECTED"; ?>>None</OPTION>
                <?
                $teamlist = teams_list();
                foreach ($teamlist as $value) {
                    echo "<OPTION VALUE=\"".$value."\"";
                    if ($filter['team'] == $value)
                        echo " SELECTED";
                    echo ">".$value."</OPTION>";
                }
                echo "</SELECT>";
                ?>
                <? echo manager_name(); ?>: <select id="f_manager" name="f_manager"><? $managers =  managers_list(); ?>
                    <option value="*" <? if ($filter['manager'] == '*') echo "SELECTED"; ?>>All</option>
                    <option value="=" <? if ($filter['manager'] == '=') echo "SELECTED"; ?>>None</option>
                    <? foreach ($managers as $managerid) {
                        ?><option value="<?=$managerid['name'] ?>" <? if ($filter['manager'] == $managerid['name']) echo "SELECTED"; ?>><?=$managerid['name'] ?></option><?
                    }
                    ?>
                </select>
                Level: <select id="f_level" name="f_level">
                    <option value="*" <? if ($filter['level'] == '*') echo "SELECTED"; ?>>All</option>
                    <option value="=" <? if ($filter['level'] == '=') echo "SELECTED"; ?>>None</option>
                    <option value="1" <? if ($filter['level'] == '1') echo "SELECTED"; ?>>1</option>
                    <option value="TBD" <? if ($filter['level'] == 'TBD') echo "SELECTED"; ?>>TBD</option>
                    <option value="2" <? if ($filter['level'] == '2') echo "SELECTED"; ?>>2</option>
                    <option value="3" <? if ($filter['level'] == '3') echo "SELECTED"; ?>>3</option>
                    <option value="4/5" <? if ($filter['level'] == '4/5') echo "SELECTED"; ?>>4/5</option>
                </select>
                Division: <select id="f_division" name="f_division">
                    <option value="*" <? if ($filter['division'] == '*') echo "SELECTED"; ?>>All</option>
                    <option value="=" <? if ($filter['division'] == '=') echo "SELECTED"; ?>>None</option>
                    <option value="1" <? if ($filter['division'] == '1') echo "SELECTED"; ?>>1</option>
                    <option value="2" <? if ($filter['division'] == '2') echo "SELECTED"; ?>>2</option>
                </select>
                <br/>
                E-Mail: <input type=text value="<? echo $filter['email']; ?>" name="f_email">
                State: <input type=text size=2 value="<? if ($filter['state'] != '0') echo $filter['state']; ?>" name="f_state"><br />
                Date From: <input class="date" type="text" value="<? echo $_REQUEST['datefrom']; ?>" id="datefrom" name="datefrom" size="8">
                To: <input class="date" type="text" value="<? echo $_REQUEST['dateto']; ?>" id="dateto" name="dateto" size="8">
                <input type=submit value="Filter"><br />
        </td>
    </tr>
    <tr>
        <td class="fat_black_12" bgcolor="#fcfcfc">Name</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Location</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Phone&nbsp;#</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">State</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Type</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">E-Mail</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Bedding</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">#</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Case</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">#</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">Total</td>
        <td class="fat_black_12" bgcolor="#fcfcfc">#</td>
    </tr>
    <?
    $total = array();
    $total['count'] = 0;
    $total['bed_count'] = 0;
    $total['case_count'] = 0;
    $total['total'] = 0;
    $total['bed_total'] = 0;
    $total['case_total'] = 0;
    $numrows = 0;
    foreach ($dealers as $result)
    {
        ++$numrows;
        ?>
        <tr>
            <td class="text_12"><?
                echo $result['last_name'];
                ?></td>
            <td class="text_12"><?
                echo $result['first_name'];
                ?></td>
            <td class="text_12"><?
                echo $result['phone'];
                ?></td>
            <td class="text_12"><?
                echo $result['state'];
                ?></td>
            <td class="text_12"><?
                echo $result['dealer_type'];
                ?></td>
            <td class="text_12"><?
                echo $result['email'];
                //if ($result['email2']) echo "<br />".$result['email2'];
                //if ($result['email3']) echo "<br />".$result['email3'];

                ?></td>
            <?
            $stats = getdealerstats($result['ID']);
            $total['count'] += $stats['count'];;
            $total['bed_count'] += $stats['bed_count'];;
            $total['case_count'] += $stats['case_count'];;
            $total['total'] += $stats['total'];
            $total['bed_total'] += $stats['bed_total'];
            $total['case_total'] += $stats['case_total'];
            ?>
            <td class="text_12" style="text-align: right"><?
                echo makeThisLookLikeMoney($stats['bed_total']);
                ?></td>
            <td class="text_12" style="text-align: right"><?
                echo $stats['bed_count'];
                ?></td>
            <td class="text_12" style="text-align: right"><?
                echo makeThisLookLikeMoney($stats['case_total']);
                ?></td>
            <td class="text_12" style="text-align: right"><?
                echo $stats['case_count'];
                ?></td>
            <td class="text_12" style="text-align: right"><?
                echo makeThisLookLikeMoney($stats['total']);
                ?></td>
            <td class="text_12" style="text-align: right"><?
                echo $stats['count'];
                ?></td>
        </tr>
    <? } ?>
    <tr class="sortbottom">
        <td colspan="6" class="fat_black_12" bgcolor="#fcfcfc">Total Dealers Displayed: <? echo $numrows; ?></td>
        <td class="fat_black_12"  bgcolor="#fcfcfc" style="text-align: right"><?
            echo makeThisLookLikeMoney($total['bed_total']);
            ?></td>
        <td class="fat_black_12" bgcolor="#fcfcfc" style="text-align: right"><?
            echo $total['bed_count'];
            ?></td>
        <td class="fat_black_12" bgcolor="#fcfcfc" style="text-align: right"><?
            echo makeThisLookLikeMoney($total['case_total']);
            ?></td>
        <td class="fat_black_12" bgcolor="#fcfcfc" style="text-align: right"><?
            echo $total['case_count'];
            ?></td>
        <td class="fat_black_12" bgcolor="#fcfcfc" style="text-align: right"><?
            echo makeThisLookLikeMoney($total['total']);
            ?></td>
        <td class="fat_black_12" bgcolor="#fcfcfc" style="text-align: right"><?
            echo $total['count'];
            ?></td>
    </tr>
</table>
<br>
<br style="clear: both;">
<div id="debugout">&nbsp;</div>
</body>
</html>
