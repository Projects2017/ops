<?php require('header.php'); ?>
<?php
require("database.php");
require("secure.php");
require("form.inc.php");
require("announce.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<link rel="stylesheet" href="styles.css" type="text/css">

<body class="hold-transition skin-blue sidebar-mini" bgcolor="#EDECDA">
<div class="wrapper">
    <?php //include('menu.php'); ?>
    <?php require ('nav.php'); ?>
    <?php require ('sidenav.php'); ?>
    <div class="content-wrapper">
        <?php include('dashboard.php');?>
        <BLOCKQUOTE class="float">
            <table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
                <tr bgcolor="#CCCC99">
                    <td class="fat_black_12">Claims Type</td>
                    <td class="fat_black_12">Waiting</td>
                    <td class="fat_black_12">Open</td>
                </tr>
                <?php
                $claims = formsummaries();
                foreach($claims as $y => $x) {
                    $forminfo = forminfo($y,1);
                    ?>
                    <tr bgcolor="#FFFFFF">
                        <td class="text_12"><a href="form.php?form=<?php echo $forminfo['name']; ?>&action=display"><?php echo $forminfo['nicename']; ?></a></td>
                        <td class="text_12"><?php echo $x['own']; ?></td>
                        <td class="text_12"><?php echo $x['open']; ?></td>
                    </tr>
                <?php } ?>
                <tr bgcolor="#CCCC99">
                    <td class="fat_black_12" colspan=50>Orders</td>
                </tr>
                <tr>
                    <td class="text_12"><?php if (secure_is_admin()) { ?><a href="admin/report-orders.php"><?php } ?>Unprocessed<?php if (secure_is_admin()) { ?></a><?php } ?></td>
                    <td class="text_12">
                        <?php
                        /*$sql = "SELECT count(ID) FROM order_forms WHERE user='" . $userid . "' AND deleted=0 AND processed='N'";
                        $results = mysql_query($sql);
                        checkDBError($sql);
                        $row = mysql_fetch_row($results);
                        echo $row[0];*/
                        //############################################
                        $sql = "SELECT * FROM users WHERE ID='" . $userid . "'";
                        $user = mysql_query($sql);
                        checkDBError($sql);
                        $user = mysql_fetch_array($user, MYSQL_ASSOC);

                        $count_bo_sql = "SELECT count(ID) FROM backorder WHERE canceled=0 AND completed=0";
                        $count_sql = "SELECT count(ID) FROM order_forms WHERE deleted=0 AND processed='N'";
                        //if ($user['team'] == "*" && $user['admin'] != "") {
                        //	//-- Nothing to add to the query
                        //}
                        //elseif ($user['team'] != "" && $user['admin'] != "") {
                        //	$sql = "SELECT ID from users WHERE team = '" . $user['team'] . "'";
                        //	$users = mysql_query($sql);
                        //	$user_arr = array();
                        //	while ($row = mysql_fetch_row($users)) {
                        //		$user_arr[] = $row[0];
                        //	}
                        //	$count_sql .= " AND user IN ('" . implode("', '", $user_arr) . "')";
                        //}
                        //else {
                        //	$count_sql .= " AND user='" . $userid . "'";
                        //}
                        if ($user['admin'] == "") {
                            $count_sql .= " AND user='" . $userid . "'";
                            $count_bo_sql .= " AND user_id='" . $userid . "'";
                        }
                        $results = mysql_query($count_sql);
                        checkDBError($sql);
                        $row = mysql_fetch_row($results);
                        echo $row[0];
                        $results = mysql_query($count_bo_sql);
                        checkDBError($sql);
                        $bo = mysql_fetch_row($results);
                        $bo = $bo[0];
                        //############################################
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="text_12"><?php if (secure_is_admin()) { ?><a href="admin/report-orders.php"><?php } ?>Backorders<?php if (secure_is_admin()) { ?></a><?php } ?></td>
                    <td class="text_12"><?php= $bo ?></td>
                </tr>
                <tr bgcolor="#CCCC99">
                    <td class="fat_black_12" colspan=50>Announcements</td>
                </tr>
                <?php
                $announcements = announce_list($userid, true, 0);
                $x = 0;
                $more_announcements = false;
                foreach($announcements as $announce) {
                    $x++;
                    if ($x > 10) {
                        $more_announcements = true;
                        break; // Limit the number of announcements to 20
                    }
                    ?>
                    <tr bgcolor="#FFFFFF">
                        <td class="text_12" colspan=50><?php echo "<a href=\"announce.php?id=".$announce['id']."\">".htmlentities($announce['subject'])."</a>"; if (!$announce['read']) echo " (unread)" ?></td>
                    </tr>
                    <?php
                }
                if ($more_announcements) {
                    ?>
                    <tr bgcolor="#FFFFFF">
                        <td class="text_12" align="right" colspan=50>[<a href="announce.php?id=recent">More</a>]</td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </BLOCKQUOTE>
        <br>
        <!-- Start Reading the top announcement if there is one --->
        <?php
        $announce_id = null;
        // Extract Oldest Announcement that's unread
        foreach ($announcements as $announce)
            if (!$announce['read'])
                $announce_id = $announce['id'];
        if ((!$_COOKIE['pmd_suuser'])&&$announce_id) {
            $announce = announce_read($announce_id, $userid);
            ?>
            <BLOCKQUOTE class="article">
                <table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
                    <tr bgcolor="#CCCC99">
                        <td class="fat_black_12"><?php echo htmlentities($announce['subject']); ?></td>
                    </tr>
                    <tr>
                        <td class="text_12">
                            <?php
                            echo $announce['text'];
                            //echo nl2br(htmlentities(print_r($_SERVER,true)));
                            ?>
                        </td>
                    </tr>
                </table>
                <br style="clear: both;">
            </BLOCKQUOTE>
        <?php } ?>
        <!-- End Announcements --->
        <FONT FACE=ARIAL>
            <B>SALES STATISTICS</B><BR>
            <a href="salestats_form.php">Enter Sales Stats</a><?php if ($user['wodsable'] == 'Y' || secure_is_manager()) { ?>/<a href="wodsstats_form.php">WODS</a><?php } ?> | <a href="salestats_edit.php">View & Edit Sales Stats</a><?php if ($user['wodsable'] == 'Y' || secure_is_manager()) { ?>/<a href="wodsstats_edit.php">WODS</a><?php } ?> |<?php if (secure_is_manager()) { ?> <a href="salestats_filter.php">Filter & Rank Sales Stats</a>/<a href="wodsstats_filter.php">WODS</a> |<?php } ?> <a href="salestats_query.php">View Sales Stats</a><?php if ($user['wodsable'] == 'Y' || secure_is_manager()) { ?>/<a href="wodsstats_query.php">WODS</a><?php }

            /*
            if(secure_is_admin() || secure_is_superadmin()) {
            ?>| <a href="wodsstats_suspect.php">View Edited WODS Entries</a><?php }
            */
            ?><BR>&nbsp;<br />
            <?php
            if(secure_is_manager() || secure_is_admin() || secure_is_superadmin()) {
                ?>
                <B>FIELD VISITS</B><BR>
                <a href="field_visit.php">Enter Field Visit</a> | <a href="field_visit_view.php">View Field Visit</a><br />&nbsp;<br /><?php } ?>

            <B>CL PROGRAM</B><BR>
            <a href="clemail.php">CL Accounts</a><br />&nbsp;<br />

            <!--<B>DOCUMENT CENTER</B><BR>
<?php if (secure_is_admin()): ?><a href="/admin/document-admin.php">Documents Admin</a> | <?php endif; ?><a href="/documents.php">Documents</a> | <a href="/d/dealer/dealerlist/">Dealer List</a>
<BR>&nbsp;
<BR>-->

            <!-- <B>DOCUMENT CENTER</B><BR>
<a href="/docs/doc_center/dealers.html">Dealers</a><?php if (secure_is_manager()) { ?> | <a  href="/docs/doc_center/managers.html">Managers</a><?php } if (secure_is_superadmin()) { ?> | <a href="/d/exec/">Executives</a><?php } ?> | <a href="http://forums.pmdfurniture.com/" target="_forums">Forums</a> | <a href="clemail.php">CL Email Accounts</a>
<BR>&nbsp;
<BR>-->
            <B>DAMAGE AND CLAIMS</B><BR>

            <a href="/form.php">Claims Database</A> | <a href="/docs/damage_faq.html">Furniture Claims Procedures</A> | <a href="http://thedailyzen.net/manager/doccenter.html">Document Center</A> | <a href="/d/dealer/proration/">Corsicana Pro-Proration Tool</a> <BR>&nbsp;<BR>

            <B>ORDER REPORTS and TOOLS</B><BR>
            <a href="/form.php?form=order&fvendor=&action=display">Open Order Report</A> | <a href="/stock.php">Out of Stock Report</A> |  <a href="/docs/trucking.html">Trucking Companies</A>  <BR>
            &nbsp;<BR>

            <!--<B>RSS CONTACT DIRECTORIES</B><BR>
<a href="/docs/pmdstaff/index.html">RSS Home Office Staff Directory</A> <?php if (secure_is_manager()) { ?>  | <a href="/users.php">Dealer Listing (45 Day Activity)</a><?php } ?> <?php if (secure_is_manager()) { ?>  | <a href="/dealer_roster">Dealer Roster</a> <a href="/dealer_roster/map.php">/ Locator</a><?php } else { ?> | <a href="/dealer_roster">Manager Roster</a><?php } ?> <BR><BR>-->
            <?php require ('footer-new.php')?>
            <?php
            $query = mysql_query("select * from users where ID=$userid");
            checkDBError();

            $sql = "SELECT ID, name FROM vendors ORDER BY vendors.name";
            $query = mysql_query($sql);
            checkDBError();
            ?>
            <B>ORDER SUMMARY</B><BR>
            <a href="summary.php">View Your Order Summary</a> <BR>&nbsp;
            <br>
            <B>RSS VENDOR WEBSITES</B><BR>
            <a href="https://www.giftcraft.com/Wholesale_signin.aspx?" target="_blank">Gift Craft</a> | <a href="http://www.topline-furniture.us/" target="_blank">Home Elegance</a> | <a href="http://customer.newclassicfurniture.com/ECommerce/General/Default.aspx" target="_blank">New Classic</a> | <a href="http://www.klaussner.com/Login.aspx" target="_blank">Klaussner</a><br>
            <br>
            <B>SELECT A VENDOR</B><BR>


            <table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
                <tr bgcolor="#CCCC99">
                    <td class="fat_black_12">Vendor</td>
                    <td class="fat_black_12">Form</td>
                    <td class="fat_black_12">Price List</td>
                    <td class="fat_black_12">Previous Orders</td>
                </tr>
                <?php
                $numforms = 0;
                while($result = mysql_fetch_array($query)) {
                    $sql = "select forms.* from forms inner join form_access ON form_access.form = forms.ID  where form_access.user = '$userid' AND vendor='".$result['ID']."' AND forms.alloworder = 'Y' ORDER BY forms.name";
                    $query2 = mysql_query($sql);
                    checkDBError();
                    while ($result2 = mysql_fetch_array($query2)) {
                        ++$numforms;
                        ?>
                        <tr bgcolor="#FFFFFF">
                            <td class="text_12"><?php echo $result['name'] ?></td>
                            <td><a href="form-view.php?ID=<?php echo $result2['ID'] ?>"><?php echo $result2['name'] ?></a></td>
                            <td><a href="print-view.php?ID=<?php echo $result2['ID'] ?>">price list</a></td>
                            <td><a href="orders.php?ID=<?php echo $result2['ID'] ?>">view orders</a></td>
                        </tr>
                        <?php
                    }
                }
                if (!$numforms) {
                    ?>
                    <tr bgcolor="#FFFFFF">
                        <td class="text_12" colspan="4" align="center">You do not have access to any forms!</td>
                    </tr>
                    <?php
                }
                mysql_close($link);
                ?>
                <tr>
                    <td><img src="images/furniture1.jpg" width="238" height="150"></td>
                    <td align="center"><img src="images/furniture2.jpg" width="238" height="150"></td>
                    <td colspan="2"><img src="images/furniture3.jpg" width="238" height="150"></td>
                </tr>
            </table>
    </div>

</div>

</body>
</html>
