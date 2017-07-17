<?php
/* Run it via a function so we don't have issues around messing up global scope variables */

function display_head_menu() {
    global $outmail_override, $bigboardint;
    $display_header = secure_is_dealer();
    $admin_menu = secure_is_admin();
    $sadmin_menu = secure_is_superadmin();
    $loginas_menu = $_COOKIE['pmd_suuser']&&secure_is_admin();
    // Don't show dealer menu to admins unless they are su-ed in.
    $dealer_menu = (secure_is_dealer()&&!secure_is_admin())||$loginas_menu;
    $admin_logout = false;
    if (!$dealer_menu)
        $admin_logout = true;

    if ($display_header) { ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="noprint">
            <tr>
                    <td class="fat_black" colspan="2">Retail Service Systems</td>
            </tr>
    <?php
    if ($admin_menu) { ?>
            <tr>
                    <td bgcolor="#CCCC99"<?php if (!$admin_logout): ?> colspan="2"<?php endif; ?>><span class="fat_black_12">Menu:&nbsp;&nbsp;&nbsp;</span><?php if (!$loginas_menu) { ?><a href="/selectvendor.php">Home</a>&nbsp;&nbsp;&nbsp;<?php } ?><a href="/admin/users.php">Dealers</a>&nbsp;&nbsp;&nbsp;<a href="/admin/vendors.php">Vendors / Forms</a>&nbsp;&nbsp;&nbsp;<a href="/admin/report-orders.php">Orders</a>&nbsp;&nbsp;&nbsp;<?php if ($sadmin_menu) { ?><a href="/admin/orders-summary.php">Summary</a>&nbsp;&nbsp;&nbsp;<?php } ?><a href="/admin/rate-of-sale.php">Rate of Sale</a>&nbsp;&nbsp;&nbsp;<a href="/admin/announce-admin.php">Announcements</a>&nbsp;&nbsp;&nbsp;<a href="/admin/shipdiff.php">Shipping Speed</a>&nbsp;&nbsp;&nbsp;<a href="/admin/ch_summary.php">CommerceHub</a><?php if (!$loginas_menu) { ?>&nbsp;&nbsp;&nbsp;<a href="/form.php">Claims</a>&nbsp;&nbsp;&nbsp;<a href="/shipping/shipping.php">Shipping System</a><?php } ?>&nbsp;&nbsp;&nbsp;<a href="/wiki/">Wiki</a>&nbsp;&nbsp;&nbsp;<?php if (secure_is_manager()): ?><a href="/users.php">Sales Report</a>&nbsp;&nbsp;&nbsp;<?php endif; ?><a href="<?php if ($bigboardint) : ?>/leaderboard/<?php else: ?>http://www.boxdropbigboard.com/<?php endif; ?>">Big Board</a></td>
                    <?php if($admin_logout) { ?>
                    <td bgcolor="#CCCC99" align="right"><span class="text_12"><a href="/logout.php">Logout</a></span></td>
                    <?php } ?>
            </tr>
    <?php  if ($sadmin_menu) { ?>
            <tr>
                    <td bgcolor="#CCCC99"><span class="fat_black_12">Quick Links:&nbsp;&nbsp;&nbsp;</span><a href="/admin/users.php?action=view">Add New Dealer</a>&nbsp;&nbsp;&nbsp;<a href="/admin/users-payment.php">Insert a Payment</a>&nbsp;&nbsp;&nbsp;<a href="/admin/balance-summary.php?defaults=Y">Account Balances</a> &nbsp;&nbsp;&nbsp;<a href="/admin/cms/">CMS</a> </td><td bgcolor="#CCCC99" align="right">&nbsp;&nbsp;&nbsp;</td>
            </tr>
    <?php 	}
    }
    if ($dealer_menu) {
    ?>
            <tr>
                    <td bgcolor="#CCCC99"><span class="fat_black_12"><?php if (secure_is_admin()) { ?>Dealer <?php } ?>Menu:&nbsp;&nbsp;&nbsp;</span><a href="/wiki/">In The News</a>&nbsp;&nbsp;&nbsp;<a href="/selectvendor.php">Select Vendor</a>&nbsp;&nbsp;&nbsp;<a href="/summary.php">Orders</a>&nbsp;&nbsp;&nbsp;<a href="/form.php">Claims/OOR</a>&nbsp;&nbsp;&nbsp;<a href="/stock.php">Stock Report</a>&nbsp;&nbsp;&nbsp;<a href="/wiki/">Wiki</a>&nbsp;&nbsp;&nbsp;<a href="<?php if ($bigboardint) : ?>/leaderboard/<?php else: ?>http://www.boxdropbigboard.com/<?php endif; ?>">Big Board</a></td>

                    <td bgcolor="#CCCC99" align="right"><span class="text_12"><?php if($loginas_menu) { ?>Logged&nbsp;in&nbsp;as:&nbsp;<span class="fat_black_12"><?php echo db_user_fullname($_COOKIE['pmd_suuser']); ?></span>&nbsp;&nbsp;&nbsp;<?php } ?><a href="/logout.php">Logout</a></span></td>

            </tr>
    <?php
    } ?>
    <?php
    if ($outmail_override) {
    ?>
            <tr>
                    <td class="text_12" colspan="2" bgcolor="#FF9999" align="center">TESTING SITE, ALL E-MAILS FROM THIS SITE WILL GO TO <?php= strtoupper($outmail_override) ?></td>
            </tr>
    <?php
    }
    ?>
    </table>
    <?php
    }
}

function display_vendor_head_menu() {
    global $outmail_override;
	?>
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="noprint">
    <tr>
        <td class="fat_black" colspan="2">Retail Service Systems</td>
    </tr>
    <tr>
        <td bgcolor="#CCCC99">&nbsp;&nbsp;&nbsp;<a href="/vloginmenu.php">Home</a>&nbsp;&nbsp;&nbsp;<a href="/form.php">Claims</a>&nbsp;&nbsp;&nbsp;<a href="/shipping/shipping.php">Shipping System</a></td>
        <td bgcolor="#CCCC99" align="right"><span class="text_12"><a href="/logout.php">Logout</a></span></td>
    </tr>
    <?php
    if ($outmail_override) {
    ?>
            <tr>
                    <td class="text_12" colspan="2" bgcolor="#FF9999" align="center">TESTING SITE, ALL E-MAILS FROM THIS SITE WILL GO TO <?php= strtoupper($outmail_override) ?></td>
            </tr>
    <?php
    }
    ?>
    </table>
    <?php
}
if (secure_is_vendor()) {
	display_vendor_head_menu();
} else {
	display_head_menu();
}
