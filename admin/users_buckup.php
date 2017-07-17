<?php
require("database.php");
require("secure.php");

$fields = Array();
DBcreateFields("users", $fields, "<tr><td class=\"fat_black_12\" align=\"right\" width=\"30%\">[TITLE]: </td><td width=\"70%\">", "</td></tr>");

if(isset($_REQUEST['massedit']))
{
    $massedit = true;
}
else
{
    $massedit = false;
}
if(isset($_REQUEST['action']))
{
    $action = strtolower($_REQUEST['action']);
}
else
{
    $action = "";
}
if(isset($_REQUEST['csvexport'])) {
    $action = 'csvexport';
}
switch($action)
{
    case "update":
        if ($disabled == "") $disabled = 'N';

        // Set Admin Status
        $sql = "SELECT `admin` FROM `users` WHERE ID = ".$ID;
        $query = mysql_query($sql);
        checkDBError($sql);
        $row = mysql_fetch_assoc($query);
        if (secure_is_superadmin()) { // If Super Admin, only restriction is super, need password for that
            if (($admin == "S") && ($row['admin'] <> "S") && ($admin_password <> $admin_pass))
                $admin = $row['admin'];
        } else { // If not a superadmin, then set it to existing admin status,
            // as well as disallow setting super-admin user's password
            $admin = $row['admin'];
            if ($admin == 'S') $password = '';
        }

        // Set Admin Status, And make sure list appropriate list subscriptions are disabled.
        if ($admin == 'S') {
            $type = 'S';
        } elseif ($admin == 'Y') {
            $type = 'A';
        }  elseif ($admin == 'M') {
            $type = 'M';
            $homelist = 'N';
            $homelist2 = 'N';
            $homelist3 = 'N';
            //} elseif ($admin == 'N') {
            //	$type = 'D';
        } else {
            $type = 'D';
            $homelist = 'N';
            $homelist2 = 'N';
            $homelist3 = 'N';
            $managerlist = 'N';
            $managerlist2 = 'N';
            $managerlist3 = 'N';
        }

        if ($dealer_type == 'O' || $dealer_type == 'F') {
            $licenselist = 'N';
            $licenselist2 = 'N';
            $licenselist3 = 'N';
        }

        if ($dealer_type == 'O' || $dealer_type == 'L') {
            $franchiselist = 'N';
            $franchiselist2 = 'N';
            $franchiselist3 = 'N';
            $clbeta = 'N';
        }

        if ($wodsable != 'Y') {
            $wodslist = 'N';
            $wodslist2 = 'N';
            $wodslist3 = 'N';
        }
        // End Set Admin Status

        // Default CLBeta to "N"
        if (!$clbeta) {
            $clbeta = 'N';
        }
        if (((string) $lb_incentive_ranking) === '') {
            $lb_incentive_ranking = '0';
        }

        /* Begin Snapshot modification */
        $sql = "SELECT `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `address2`, `city2`, `state2`, `zip2`, `snapshot`, `snapshot2` , `photo` FROM users WHERE ID = '".$ID."'";
        $query = mysql_query($sql);
        checkDBError();
        if ($result = mysql_fetch_array($query)) {
            $snap1up = 0;
            $snap2up = 0;
            // If First and Last Name Change then both snaps update
            if ($first_name != $result['first_name']) {
                $snap1up = 1;
                $snap2up = 1;
            } elseif ($last_name != $result['last_name']) {
                $snap1up = 1;
                $snap2up = 1;
            } elseif ($phone != $result['phone']) {
                $snap1up = 1;
                $snap2up = 1;
            } elseif ($fax != $result['fax']) {
                $snap1up = 1;
                $snap2up = 1;
            }
            // Do we update Snap 1?
            if ($address != $result['address']) {
                $snap1up = 1;
            } elseif ($city != $result['city']) {
                $snap1up = 1;
            } elseif ($state != $result['state']) {
                $snap1up = 1;
            } elseif ($zip != $result['zip']) {
                $snap1up = 1;
            }
            // Do we update Snap 2?
            if ($address2 != $result['address2']) {
                $snap2up = 1;
            } elseif ($city2 != $result['city2']) {
                $snap2up = 1;
            } elseif ($state2 != $result['state2']) {
                $snap2up = 1;
            } elseif ($zip2 != $result['zip2']) {
                $snap2up = 1;
            }
            if (!$address2) { // If there isn't a second address.. no need to create a new one
                $snap2up = 0;
                $result['snapshot2'] = 0;
            }
            // Update Snap 1
            if ($snap1up) {
                $sql = "INSERT INTO snapshot_users (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `secondary`) VALUES (NULL, '".$ID."', '".$first_name."', '".$last_name."', '".$address."','', '".$city."', '".$state."', '".$zip."', '".$phone."', '".$fax."', '".$email."', 'N')";
                mysql_query($sql);
                $snapshot = mysql_insert_id();
                checkDBError();
            } else {
                $snapshot = $result['snapshot'];
            }
            // Update Snap 2
            if ($snap2up) {
                $sql = "INSERT INTO snapshot_users (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `secondary`) VALUES (NULL, '".$ID."', '".$first_name."', '".$last_name."', '".$address2."','', '".$city2."', '".$state2."', '".$zip2."', '".$phone."', '".$fax."', '".$email."', 'Y')";
                mysql_query($sql);
                $snapshot2 = mysql_insert_id();
                checkDBError();
            } else {
                $snapshot2 = $result['snapshot2'];
            }
        }
        /* End Snapshot modification */
        if(isset($_FILES['photo_upload']) && !empty($_FILES['photo_upload']['name'])) {
            $uploadfile = $_SERVER['DOCUMENT_ROOT']."/images/users/".$ID."_".basename($_FILES['photo_upload']['name']);
            if (move_uploaded_file($_FILES['photo_upload']['tmp_name'], $uploadfile)) {
                $photo = $ID."_".basename($_FILES['photo_upload']['name']);
            }
        } else {
            $photo = $result['photo'];
        }

        $sql = buildUpdateQuery("users", "ID=$ID");



        mysql_query($sql);
        checkDBError($sql);

        if ($username) {
            $sql = "SELECT id FROM `login` WHERE `type` != 'V' AND relation_id = '".$ID."'";
            $query = mysql_query($sql);
            checkDBError($sql);
            if (mysql_num_rows($query)) {
                $sql = "UPDATE login SET username = '".$username."', type = '".$type."'";
                if ($password)
                    $sql .= ", password = '".$password."'";
                $sql .= " WHERE type != 'V' AND relation_id = '".$ID."'";
                mysql_query($sql);
                checkDBError($sql);
            } else {
                $sql = "INSERT INTO `login` (`username`,`type`,`password`,`relation_id`) VALUES ('".$username."','".$type."','".$password."',".$ID.")";
                mysql_query($sql);
                checkDBerror($sql);
            }
        } else {
            $sql = "DELETE FROM `login` WHERE `type` != 'V' AND relation_id = '".$ID."'";
            mysql_query($sql);
            checkDBerror($sql);
        }

        header("Location: users.php?msg=$msg");
        exit;
        break;

    case "delete":
        /* Delete all dealer logins */
        $sql = "delete from login where type != 'V' and relation_id = '".$ID."'";
        mysql_query($sql);
        checkDBError($sql);
        /* Update Snapshots so they reflect a deleted user */
        $sql = "update snapshot_users SET orig_id = '0' WHERE orig_id = '".$ID."'";
        mysql_query($sql);
        checkDBError($sql);
        /* End Snapshot Modification */
        $sql = "delete from users where ID=$ID";
        mysql_query($sql);
        checkDBError($sql);

        header("Location: users.php");
        exit;
        break;

    case "create":
        if ($disabled == "") $disabled = 'N';
        /* Snapshot Creation */
        $sql = "INSERT INTO snapshot_users (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `secondary`) VALUES (NULL, '0', '".$first_name."', '".$last_name."', '".$address."','', '".$city."', '".$state."', '".$zip."', '".$phone."', '".$fax."', 'N')";
        mysql_query($sql);
        $snapshot = mysql_insert_id();
        checkDBError();

        if ($address2) {
            $sql = "INSERT INTO snapshot_users (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `secondary`) VALUES (NULL, '0', '".$first_name."', '".$last_name."', '".$address2."','', '".$city2."', '".$state2."', '".$zip2."', '".$phone."', '".$fax."', 'Y')";
            mysql_query($sql);
            $snapshot2 = mysql_insert_id();
            checkDBError();
        } else {
            $snapshot2 = 0;
        }

        /* End Snapshot Creation */
        $sql = buildInsertQuery("users");
        mysql_query($sql);
        checkDBError();

        $ID = mysql_insert_ID();
        /* Update Snapshots so they reflect new user */
        $sql = "update snapshot_users SET orig_id = '".$ID."' WHERE id = '".$snapshot."' OR id = '".$snapshot2."'";
        mysql_query($sql);
        checkDBError($sql);
        /* End Snapshot Modification */

        if ($admin == 'S') {
            $type = 'S';
        } elseif ($admin == 'Y') {
            $type = 'A';
        }  elseif ($admin == 'M') {
            $type = 'M';
        } elseif ($admin == 'N') {
            $type = 'D';
        } else {
            $type = 'D';
        }
        if ($username) {
            $sql = "insert into login (username,password,type,relation_id) VALUES ('".$username."','".$password."','".$type."','".$ID."');";
            mysql_query($sql);
            checkDBError($sql);
        } else {
            $password = '';
        }
    //passes through to the "view" case

    case "view":
        $extra_javascript = "";
        require("menu.php");

        if ($ID == "") {
            $action = "create";
            $result['nonPMD'] = 'N';
            $result['teamlist'] = 'Y';
            $result['teamlist2'] = 'Y';
            $result['teamlist3'] = 'Y';
            $result['dealerlist'] = 'Y';
            $result['dealerlist2'] = 'Y';
            $result['dealerlist3'] = 'Y';
            $result['franchiselist'] = 'Y';
            $result['franchiselist2'] = 'Y';
            $result['franchiselist3'] = 'Y';
            $result['licenselist'] = 'Y';
            $result['licenselist2'] = 'Y';
            $result['licenselist3'] = 'Y';
            $result['managerlist'] = 'Y';
            $result['managerlist2'] = 'Y';
            $result['managerlist3'] = 'Y';
            $result['homelist'] = 'Y';
            $result['homelist2'] = 'Y';
            $result['homelist3'] = 'Y';
            $result['wodslist'] = 'Y';
            $result['wodslist2'] = 'Y';
            $result['wodslist3'] = 'Y';
            $result['clbeta'] = 'N';
            $remaining_fee_pmts = '12';
            $fee_pmt_amount = '1000.00';
            $username = '';
            $password = '';
        } else {
            $query = mysql_query("select * from users where ID=$ID");
            checkDBError();
            $action = "update";

            if ($result = mysql_fetch_array($query)) {
                //$fields[0]->value=$result['username'];
                //$fields[1]->value=$result['password'];
                $fields[0]->value=$result['first_name'];
                $fields[1]->value=$result['last_name'];
                $address=$result['address'];
                $city=$result['city'];
                $state=$result['state'];
                $zip=$result['zip'];
                $fields[6]->value=$result['phone'];
                $fields[7]->value=$result['fax'];
                $fields[8]->value=$result['email'];
                $fields[9]->value=$result['email2'];
                $fields[9]->title='Email (2nd)';
                $fields[10]->value=$result['email3'];
                $fields[10]->title='Email (3rd)';
                $fields[11]->value=$result['disabled'];
                $fields[12]->value=$result['admin'];
                $admin_status=$result['admin'];
                $fields[13]->value=$result['credit_hold'];
                $fields[14]->value=$result['remaining_fee_pmts'];
                $fields[15]->value=$result['fee_payment_amount'];
                $fields[16]->value=$result['division'];
                $fields[17]->value=$result['dealer_type'];
                $fields[18]->value=$result['manager'];
                $fields[33]->value=$result['team'];
                $fields[34]->value=$result['wodsable'];
                $home_address=$result['home_address'];
                $business_address=$result['business_address'];
                $mailing_address=$result['mailing_address'];
                $cell_phone=$result['cell_phone'];
                $cell_provider = $result['cell_provider'];
                $business_phone=$result['business_phone'];
                $home_phone=$result['home_phone'];
                $email_address=$result['email_address'];
                $credit_limit=$result['credit_limit'];
                $remaining_fee_pmts = $result['remaining_fee_pmts'];
                $fee_pmt_amount = $result['fee_pmt_amount'];
                $address2=$result['address2'];
                $city2=$result['city2'];
                $state2=$result['state2'];
                $zip2=$result['zip2'];
                $fields[60]->value=$result['Access_name'];
                $fields[61]->value=$result['MAS90_name'];
                $fields[62]->value=$result['photo'];
                $fields[63]->value=$result['big_board_name'];

                $fields[64]->value=$result['lb_grandday'];
                $fields[65]->value=$result['lb_2kday'];
                $fields[66]->value=$result['lb_5kweek'];
                $fields[67]->value=$result['lb_10week'];
                $fields[68]->value=$result['lb_250kweek'];
                $fields[69]->value=$result['lb_500kweek'];
                $fields[70]->value=$result['lb_millyear'];
                $fields[71]->value=$result['lb_mrecord_mattress'];
                $fields[72]->value=$result['lb_incentive_ranking'];
                $furniture_and_mattress = $result['furniture_and_mattress'];

                $sql = "select * from login where type != 'V' AND relation_id = '".$ID."'";
                $query2 = mysql_query($sql);
                $result2 = mysql_fetch_assoc($query2);
                $username = $result2['username'];
                if (($security == "S" && $result['admin'] == "S")||($result['admin'] != "S"))
                    $password = $result2['password'];

                // Make sure list appropriate list subscriptions are enabled by default.
                if ($result['admin'] == 'S') {
                } elseif ($result['admin'] == 'Y') {
                }  elseif ($result['admin'] == 'M') {
                    $result['homelist'] = 'Y';
                    $result['homelist2'] = 'Y';
                    $result['homelist3'] = 'Y';
                } else {
                    $result['homelist'] = 'Y';
                    $result['homelist2'] = 'Y';
                    $result['homelist3'] = 'Y';
                    $result['managerlist'] = 'Y';
                    $result['managerlist2'] = 'Y';
                    $result['managerlist3'] = 'Y';
                }

                if ($result['dealer_type'] == 'O' || $result['dealer_type'] == 'F') {
                    $result['licenselist'] = 'Y';
                    $result['licenselist2'] = 'Y';
                    $result['licenselist3'] = 'Y';
                }

                if ($result['dealer_type'] == 'O' || $result['dealer_type'] == 'L') {
                    $result['franchiselist'] = 'Y';
                    $result['franchiselist2'] = 'Y';
                    $result['franchiselist3'] = 'Y';
                }

                if ($result['wodsable'] != 'Y') {
                    $result['wodslist'] = 'Y';
                    $result['wodslist2'] = 'Y';
                    $result['wodslist3'] = 'Y';
                }
            }
        }

        $fields[0]->title = "Location";
        $fields[1]->title = "Dealer Name";
        //if (!(($security == "S" && $result['admin'] == "S")||($result['admin'] != "S")))
        //	$fields[1]->display=false;
        $fields[2]->display=false; /* address1 */
        $fields[3]->display=false;
        $fields[4]->display=false;
        $fields[5]->display=false;
        $fields[28]->display=false; /* address2 */
        $fields[29]->display=false;
        $fields[30]->display=false;
        $fields[31]->display=false;

        $fields[11]->display=false;
        $fields[12]->display=false;
        $fields[13]->display=false;
        $fields[14]->display=false;
        $fields[15]->display=false;
        $fields[16]->display=false;
        $fields[17]->title = "User Type";
        $fields[17]->type = "selectval";
        $fields[17]->options = array();
        $fields[17]->options['F'] = 'Franchisee';
        $fields[17]->options['L'] = 'Licensee';
        $fields[17]->options['B'] = 'Both';
        $fields[17]->options['O'] = 'Other (Neither)';
        $fields[18]->display=false;
        $fields[19]->display=false;
        $fields[20]->display=false;
        $fields[21]->display=false;
        $fields[22]->display=false;
        $fields[23]->display=false;
        $fields[24]->display=false;
        $fields[25]->display=false;
        $fields[26]->display=false;
        $fields[27]->display=false; /* credit limit */
        $fields[32]->display=false; /* non-PMD Tag */
        $fields[33]->display=false; // wodsable bool
        $fields[34]->display=false; // CLBeta subscription
        $fields[35]->display=false; /* Team Tag */
        $fields[36]->display=false; // Team List
        $fields[37]->display=false;
        $fields[38]->display=false;
        $fields[39]->display=false; // Dealer List
        $fields[40]->display=false;
        $fields[41]->display=false;
        $fields[42]->display=false; // Licensee List
        $fields[43]->display=false;
        $fields[44]->display=false;
        $fields[45]->display=false; // Franchise List
        $fields[46]->display=false;
        $fields[47]->display=false;
        $fields[48]->display=false; // Managers/whatever List
        $fields[49]->display=false;
        $fields[50]->display=false;
        $fields[51]->display=false; // Home List
        $fields[52]->display=false;
        $fields[53]->display=false;
        $fields[54]->display=false; // WODS List
        $fields[55]->display=false;
        $fields[56]->display=false;
        $fields[57]->display=false; // Snapshot
        $fields[58]->display=false; // Snapshot2
        $fields[59]->display=false; // Level

        $fields[62]->display=false; // Photo
        $fields[63]->display=false; // big_board_name

        # LB (64-72)
        $fields[64]->display=false; // lb_grandday
        $fields[65]->display=false; // lb_2kday
        $fields[66]->display=false; // lb_5kweek
        $fields[67]->display=false; // lb_10week
        $fields[68]->display=false; // lb_250kweek
        $fields[69]->display=false; // lb_500kweek
        $fields[70]->display=false; // lb_millyear
        $fields[71]->display=false; // lb_mrecord_mattress
        $fields[72]->display=false; // lb_incentive_ranking

        $fields[73]->display=false; // furniture_and_mattress

        ?>
        <title>RSS Administration</title>
        <link rel="stylesheet" href="../styles.css" type="text/css">

        <form action="users.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="ID" value="<?php echo $ID; ?>">
            <table border="0" cellspacing="1" cellpadding="4" width="500">
                <?php DBdisplayFields($fields); ?>

                <tr><td class="fat_black_12" align="right" width="30%">New Photo: </td><td width="70%"><input type="file" name="photo_upload" id="photo_upload" value=""></td></tr>
            </table>


            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="3" class="orderTDheading">Dealer Logins</td>
                </tr>
                <tr>
                    <td width="30%">&nbsp;</td>
                    <td class="fat_black_12" width="30%">Username</td>
                    <?php if (secure_is_superadmin()) { ?>
                        <td class="fat_black_12" width="40%">Password</td>
                    <?php } elseif ($result['admin'] == 'S') { ?>
                        <td class="fat_black_12" width="40%">&nbsp;</td>
                    <?php }  else { ?>
                        <td class="fat_black_12" width="40%">Password</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td width="30%">&nbsp;</td>
                    <td class="text_12" width="30%"><input type="text" name="username" value="<?php echo htmlentities($username); ?>"></td>
                    <?php if (secure_is_superadmin()) { ?>
                        <td class="text_12" width="40%"><input type="text" name="password" value="<?php echo htmlentities($password); ?>"></td>
                    <?php } elseif ($result['admin'] == 'S') { ?>
                        <td class="text_12" width="40%">&nbsp;</td>
                    <?php } else { ?>
                        <td class="text_12" width="40%"><input type="text" name="password" value=""></td>
                    <?php } ?>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Primary Address</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Address:</td>
                    <td class="text_12" width="70%"><input type="text" name="address" value="<?php echo htmlentities($address); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">City:</td>
                    <td class="text_12" width="70%"><input type="text" name="city" value="<?php echo htmlentities($city); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">State:</td>
                    <td class="text_12" width="70%"><input type="text" name="state" value="<?php echo htmlentities($state); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Zip Code:</td>
                    <td class="text_12" width="70%"><input type="text" name="zip" value="<?php echo htmlentities($zip); ?>"></td>
                </tr>
                <tr>
                    <td colspan="2" class="orderTDheading">Secondary Address</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Address:</td>
                    <td class="text_12" width="70%"><input type="text" name="address2" value="<?php echo htmlentities($address2); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">City:</td>
                    <td class="text_12" width="70%"><input type="text" name="city2" value="<?php echo htmlentities($city2); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">State:</td>
                    <td class="text_12" width="70%"><input type="text" name="state2" value="<?php echo htmlentities($state2); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Zip Code:</td>
                    <td class="text_12" width="70%"><input type="text" name="zip2" value="<?php echo htmlentities($zip2); ?>"></td>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Accounting Information</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Order To Limit:</td>
                    <td class="text_12" width="70%">
                        <?php
                        if (secure_is_superadmin()) { //only a person with the highest security rating can the credit limit
                            ?>
                            $<input type="text" name="credit_limit" value="<?php echo $credit_limit; ?>">
                        <?php } else { ?>
                            <p>$<?php echo $result['credit_limit']; ?><input type="hidden" name="credit_limit" value="<?php echo $result['credit_limit']; ?>"></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Fee Payments Remaining:</td>
                    <td class="text_12" width="70%">
                        <?php if (secure_is_superadmin()) { ?>
                            <input type="text" name="remaining_fee_pmts" value="<?php echo $remaining_fee_pmts; ?>">
                        <?php } else { ?>
                            <p><?php echo $result['remaining_fee_pmts']; ?><input type="hidden" name="remaining_fee_pmts" value="<?php echo $result['remaining_fee_pmts']; ?>"></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Fee Payment Amount:</td>
                    <td class="text_12" width="70%">
                        <?php if (secure_is_superadmin()) { ?>
                            $<input type="text" name="fee_pmt_amount" value="<?php echo $fee_pmt_amount; ?>">
                        <?php } else { ?>
                            <p>$<?php echo $result['fee_pmt_amount']; ?><input type="hidden" name="fee_pmt_amount" value="<?php echo $result['fee_pmt_amount']; ?>"></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr id="clbeta_tr">
                    <td align="right" class="fat_black_12" width="30%">Commission Rate (%)</td>
                    <td width="70%"><input type="text" name="commission_rate" id="commission_rate" value="<?php echo $result['commission_rate']; ?>"> %
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500">
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Non-RSS:</td>
                    <td width="70%">
                        <?php
                        if ($security == "S") { //only a person with the highest security rating can change nonPMD status
                            ?>
                            <input type="checkbox" name="nonPMD" value="Y"<?php if( $result['nonPMD'] == 'Y' ) echo " CHECKED"; ?>>
                        <?php } else { ?>
                            <p><?php echo $result['nonPMD']; ?><input type="hidden" name="nonPMD" value="<?php echo $result['nonPMD']; ?>"></p>
                        <?php } ?>
                    </td></tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Inactive:</td>
                    <td width="70%">
                        <?php
                        if (secure_is_superadmin()) { //only a person with the highest security rating can change disability status

                            if ($result['disabled'] == "Y") $action = "enable";
                            else $action = "disable";
                            ?>
                            <input type="checkbox" name="disabled" value="Y"<?php if( $result['disabled'] == 'Y' ) echo " CHECKED"; ?>  onclick="return confirm('Are you sure you want to <?php echo $action; ?> this customer?')">
                        <?php } else { ?>
                            <p><?php echo $result['disabled']; ?><input type="hidden" name="disabled" value="<?php echo $result['disabled']; ?>"></p>
                        <?php } ?>
                    </td></tr>
                <tr valign="top">
                    <td align="right" class="fat_black_12" width="30%">Privilege Level:</td>
                    <td class="text_12" width="70%">
                        <?php
                        //only a person with the highest security rating can edit security
                        if (secure_is_superadmin()) {
                            ?>
                            <select name="admin" size="1" onchange="updateList(this.options[this.selectedIndex].value)">
                                <option value="">Dealer</option>
                                <option value="M"<?php if ($admin_status == 'M') echo " selected"; ?>>Manager</option>
                                <option value="Y"<?php if ($admin_status == 'Y') echo " selected"; ?>>Admin</option>
                                <option value="S"<?php if ($admin_status == 'S') echo " selected"; ?>>Super-Admin</option>
                            </select> <input type="hidden" name="admin_old" value="<?php echo $result['admin']; ?>">
                            <?php if ($admin_status <> "S") { ?>
                                <br>password required to change to Super-Admin:
                                <input type="password" name="admin_password" size="15">
                                <?php
                            }
                        }
                        else {
                            if ($admin_status == "Y")
                                echo "Admin";
                            elseif ($admin_status == "S")
                                echo "Super-Admin";
                            elseif ($admin_status == "M")
                                echo "Manager";
                            else
                                echo "Dealer";
                        }
                        ?>
                    </td></tr>
                <td align="right" class="fat_black_12" width="30%">Division:</td>
                <td width="70%"><select name="division" size="1">
                        <option value="">non specified</option>
                        <option value="1"<?php if ($result['division'] == "1") echo " selected"; ?>>1</option>
                        <option value="2"<?php if ($result['division'] == "2") echo " selected"; ?>>2</option>
                    </select>
                </td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Level:</td>
                    <td width="70%"><select name="level" size="1">
                            <option value="">None</option>
                            <option value="1"<?php if ($result['level'] == "1") echo " selected"; ?>>1</option>
                            <option value="TBD"<?php if ($result['level'] == "TBD") echo " selected"; ?>>TBD</option>
                            <option value="2"<?php if ($result['level'] == "2") echo " selected"; ?>>2</option>
                            <option value="3"<?php if ($result['level'] == "3") echo " selected"; ?>>3</option>
                            <option value="4/5"<?php if ($result['level'] == "4/5") echo " selected"; ?>>4/5</option>
                        </select>
                    </td>
                </tr>
                <tr><?php $temp = 0; ?>
                    <td align="right" class="fat_black_12" width="30%"><?php echo manager_name(); ?>:</td>
                    <td width="70%"><select name="manager" size="1">
                            <option value=""<?php if (!$result['manager']) { echo " selected"; $temp = 1;} ?>>None</option>
                            <?php $managers = managers_list(); foreach ($managers as $manager) { ?>
                                <option value="<?php= $manager['name'] ?>"<?php if ($result['manager'] == $manager['name']) { echo " selected"; $temp = 1;} ?>><?php= $manager['name'] ?></option>
                            <?php } ?>
                            <?php if ($temp == 0) { ?>
                                <option value="<?php echo $result['manager']; ?>" selected><?php echo $result['manager']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Team:</td>
                    <td width="70%"><input type="text" name="team" size="1" maxlength="1" value="<?php echo $result['team']; ?>">
                    </td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">WODS Enabled:</td>
                    <td width="70%"><input type="checkbox" name="wodsable" id="wodsable" onclick="updateWodsList()" value="Y"<?php if($result['wodsable']=='Y') echo " checked"; if(!(secure_is_admin() || secure_is_superadmin())) echo " disabled"; ?>>
                    </td>
                </tr>
                <tr id="clbeta_tr">
                    <td align="right" class="fat_black_12" width="30%">CL Beta:</td>
                    <td width="70%"><input type="checkbox" name="clbeta" id="clbeta" value="Y"<?php if($result['clbeta']=='Y') echo " checked"; if(!(secure_is_admin() || secure_is_superadmin())) echo " disabled"; ?>>
                    </td>
                </tr>
                <tr id="clbeta_tr">
                    <td align="right" class="fat_black_12" width="30%">Furniture & Mattress:</td>
                    <td width="70%">

                        <select name="furniture_and_mattress">
                            <option value="N">Mattress ONLY</option>
                            <option value="Y"<?php if($result['furniture_and_mattress']=='Y') echo " selected";?>>Furniture AND Mattress
                        </select>
                    </td>
                </tr>
                <tr id="clbeta_tr">
                    <td align="right" class="fat_black_12" width="30%">&nbsp;</td>
                    <td width="70%">
                        <font style="font-size:12px;color:#555;font-family:Arial,sans-serif">
                            <i>
                                *Furniture AND Mattress must be selected to show up on the Top 10 Furniture list.
                            </i>
                        </font>
                    </td>
                </tr>
            </table>

            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Leaderboard</td>
                </tr>
                <!--
	<tr>
	  <td align="right" class="fat_black_12" width="30%">Grand Day:</td>
	  <td width="70%"><input type="checkbox" name="lb_grandday" id="lb_grandday" value="Y"<?php if( $result['lb_grandday'] == 'Y' ) echo " CHECKED"; ?>></td>
	</tr>
-->
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Big Board Name:</td>
                    <td width="70%"><input type="text" name="big_board_name" value="<?php echo $result['big_board_name']; ?>"/> <font style="font-size:11px;font-family:arial;">(Custom name used if entered)</font></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Bronze Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_2kday" value="Y"<?php if( $result['lb_2kday'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Silver Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_5kweek" value="Y"<?php if( $result['lb_5kweek'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Gold Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_10kweek" value="Y"<?php if( $result['lb_10kweek'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Platinum Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_250kweek" value="Y"<?php if( $result['lb_250kweek'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">1/2 Million Dollar Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_500kweek" value="Y"<?php if( $result['lb_500kweek'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Million Dollar Club:</td>
                    <td width="70%"><input type="checkbox" name="lb_millyear" value="Y"<?php if( $result['lb_millyear'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>

                <tr>
                    <td align="right" class="fat_black_12" width="30%">Mattress Mon Rec:</td>
                    <td width="70%"><input type="checkbox" name="lb_mrecord_mattress" value="Y"<?php if( $result['lb_mrecord_mattress'] == 'Y' ) echo " CHECKED"; ?>></td>
                </tr>

                <tr>
                    <td align="right" class="fat_black_12" width="30%">Incentive Trip Enabled?</td>
                    <td width="70%"><input type="checkbox" name="lb_incentive_ranking" value="1"<?php if( $result['lb_incentive_ranking'] == '1' ) echo " CHECKED"; ?>></td>
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">List Serv Memberships</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Team List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="teamlist" value="Y"<?php if( $result['teamlist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="teamlist2" value="Y"<?php if( $result['teamlist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="teamlist3" value="Y"<?php if( $result['teamlist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Dealers List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="dealerlist" value="Y"<?php if( $result['dealerlist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="dealerlist2" value="Y"<?php if( $result['dealerlist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="dealerlist3" value="Y"<?php if( $result['dealerlist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr id="franchiselists">
                    <td align="right" class="fat_black_12" width="30%">Franchisee List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="franchiselist" value="Y"<?php if( $result['franchiselist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="franchiselist2" value="Y"<?php if( $result['franchiselist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="franchiselist3" value="Y"<?php if( $result['franchiselist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr id="licenselists">
                    <td align="right" class="fat_black_12" width="30%">Licensee List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="licenselist" value="Y"<?php if( $result['licenselist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="licenselist2" value="Y"<?php if( $result['licenselist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="licenselist3" value="Y"<?php if( $result['licenselist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr id="wodslist">
                    <td align="right" class="fat_black_12" width="30%">WODS List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="wodslist" value="Y"<?php if( $result['wodslist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="wodslist2" value="Y"<?php if( $result['wodslist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="wodslist3" value="Y"<?php if( $result['wodslist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr id="managerlist">
                    <td align="right" class="fat_black_12" width="30%">Managers List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="managerlist" value="Y"<?php if( $result['managerlist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="managerlist2" value="Y"<?php if( $result['managerlist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="managerlist3" value="Y"<?php if( $result['managerlist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
                <tr id="adminlist">
                    <td align="right" class="fat_black_12" width="30%">Home Office List:</td>
                    <td width="70%">
                        email<input type="checkbox" name="homelist" value="Y"<?php if( $result['homelist'] == 'Y' ) echo " CHECKED"; ?>>
                        email2<input type="checkbox" name="homelist2" value="Y"<?php if( $result['homelist2'] == 'Y' ) echo " CHECKED"; ?>>
                        email3<input type="checkbox" name="homelist3" value="Y"<?php if( $result['homelist3'] == 'Y' ) echo " CHECKED"; ?>>
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Inactive Information for Company Directory</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Home Address:</td>
                    <td width="70%"><textarea name="home_address" rows="3" cols="30" wrap="virtual"><?php echo htmlentities($home_address); ?></textarea></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Business Address:</td>
                    <td width="70%"><textarea name="business_address" rows="3" cols="30" wrap="virtual"><?php echo htmlentities($business_address); ?></textarea></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Mailing Address:</td>
                    <td width="70%"><textarea name="mailing_address" rows="3" cols="30" wrap="virtual"><?php echo htmlentities($mailing_address); ?></textarea></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Cell Phone:</td>
                    <td width="70%"><input type="text" name="cell_phone" size="30" maxlength="20" value="<?php echo htmlentities($cell_phone); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Cell Phone Provider:</td>
                    <td width="70%"><select name="cell_provider">
                            <?php
                            // set the initial cell provider info based upon what's in the db currently
                            require_once('../include/cellprovider.class.php');
                            $provider = new cellProvider($cell_provider);
                            // grab all the providers from the db and set up the select options, choosing the current one
                            $sql = "SELECT code, name FROM cell_providers ORDER BY name";
                            $que = mysql_query($sql);
                            checkDBerror($sql);
                            while($res = mysql_fetch_assoc($que))
                            {
                                ?><option value="<?php= $res['code'] ?>"<?php if($provider->getCode() == $res['code']) echo ' selected'; ?>><?php= $res['name'] ?></option>
                                <?php
                            }
                            ?></select></td>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Business Phone:</td>
                    <td width="70%"><input type="text" name="business_phone" size="30" maxlength="20" value="<?php echo htmlentities($business_phone); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Home Phone:</td>
                    <td width="70%"><input type="text" name="home_phone" size="30" maxlength="20" value="<?php echo htmlentities($home_phone); ?>"></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Email Address:</td>
                    <td width="70%"><input type="text" name="email_address" size="30" maxlength="100" value="<?php echo htmlentities($email_address); ?>"></td>
                </tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500">
                <tr>
                    <td width="30%">&nbsp;</td>
                    <?php if( $ID == "" ) { ?>
                        <td width="70%">
                            <div>
                                <input type="submit" name="action" style="background-color:#CA0000;color:white" value="Create">
                            </div>
                        </td>
                    <?php } else { ?>
                        <td width="70%">
                            <div>
                                <input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update">
                                <?php if ($security == "S") { ?>
                                    &nbsp;
                                    <input type="submit" name="action" style="background-color:#CA0000;color:white" value="Delete" onclick="return confirm('You are about to permanently delete this dealer. Are you sure you want to delete?')">
                                <?php } ?>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
            </table>
        </form>
        <script language="javascript" type="text/javascript">
          function updateList(type) {
            var managerlist = document.getElementById("managerlist");
            var adminlist = document.getElementById("adminlist");
            if (type == "M") {
              managerlist.style.display = "";
              adminlist.style.display = "none";
            } else if (type == "Y" || type == "S") {
              managerlist.style.display = "";
              adminlist.style.display = "";
            } else {
              managerlist.style.display = "none";
              adminlist.style.display = "none";
            }
          }
          updateList("<?php= $admin_status ?>");
          function updateFranchiseList() {
            var franchise = document.getElementById("franchiselists");
            var license = document.getElementById("licenselists");
            var type = document.getElementById("dealer_type");
            if (type.value == 'B') {
              franchise.style.display = "";
              license.style.display = "";
            } else if (type.value == 'F') {
              franchise.style.display = "";
              license.style.display = "none";
            } else if (type.value == 'L') {
              franchise.style.display = "none";
              license.style.display = "";
            } else {
              franchise.style.display = "none";
              license.style.display = "none";
            }
          }
          function updateWodsList() {
            var wodslist = document.getElementById("wodslist");
            var wodsable = document.getElementById("wodsable");
            if (wodsable.checked) {
              wodslist.style.display = "";
            } else {
              wodslist.style.display = "none";
            }

          }

          function updateFranchise() {
            updateFranchiseList();
            var dealer_type = document.getElementById("dealer_type");
            var clbeta = document.getElementById("clbeta_tr");
            if (dealer_type.value == 'B' || dealer_type.value == 'F') {
              clbeta.style.display = "";
            } else {
              clbeta.style.display = "none";
            }
          }
          updateWodsList();
          updateFranchise();
          document.getElementById("dealer_type").onchange = function() {  updateFranchise(); }
        </script>
        <?php
        break;

    case "massedit":
        // Start Filtering
        // Get Filters from POST/GET
        $filter = array();
        foreach($_REQUEST as $key => $value) {
            $reg = array();
            if(ereg("^f_(.+)",$key, $reg))// && $value
                $filter[$reg[1]] = $value;
        }

        // Defaults
        if (!array_key_exists('team',$filter)) {
            $filter['team'] = $dealerteam;
        }
        if (!array_key_exists('disabled',$filter)) {
            $filter['disabled'] = 'N';
        }
        if (!array_key_exists('nonPMD',$filter)) {
            $filter['nonPMD'] = '*';
        }

        $dealers = db_user_filterlist($filter); // get the dealers that matched the filter before
        foreach ($dealers as $result)
        {
            $lev = 'level'.$result['ID']; // setup the data pulls by appending the IDs
            $man = 'manager'.$result['ID'];
            $dealer_type = 'dealer_type'.$result['ID'];
            if($result['level'] != $_REQUEST[$lev] || $result['manager'] != $_REQUEST[$man] || $result['dealer_type'] != $_REQUEST[$dealer_type]) { // if something has changed, update
                $sql = "UPDATE users SET level = '{$_POST[$lev]}', manager = '{$_POST[$man]}', `dealer_type` = '{$_POST[$dealer_type]}' WHERE ID = '{$result['ID']}'";
                checkdberror($sql);
                mysql_query($sql);
            }
        }
        header('Location: /admin/users.php');
        break;


    case "csvexport":

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=exported_users.csv");
        header("Content-Transfer-Encoding: binary");

        $fp=fopen("php://output","w");

        $filter = array();
        foreach($_REQUEST as $key => $value) {
            $reg = array();
            if(ereg("^f_(.+)",$key, $reg))// && $value
                $filter[$reg[1]] = $value;
        }

        // Defaults
        if (!array_key_exists('team',$filter)) {
            $filter['team'] = $dealerteam;
        }
        if (!array_key_exists('disabled',$filter)) {
            $filter['disabled'] = 'N';
        }
        if (!array_key_exists('nonPMD',$filter)) {
            $filter['nonPMD'] = '*';
        }
        if (!array_key_exists('dealer_type',$filter)) {
            $filter['dealer_type'] = '*';
        }

        $dealers = db_user_filterlist($filter);

        $headers = array (
            'Location',
            'Name',
            'Address',
            'City',
            'State',
            'Zip',
            'Phone',
            'Fax',
            'Email',
        );

        $headerkey = array_flip($headers);

        fputcsv($fp,$headers);

        function convertarray($headerkey, $items) {
            global $headerkey;
            $output = array();
            for ($i = 0; $i < count($items); $i++) {
                $output[] = '';
            }
            foreach ($items as $key => $val) {
                if (!isset($headerkey[$key])) {
                    print_r($items);
                    die("\n\nUnknown Array Key: ".$key);
                }
                $output[$headerkey[$key]] = $val;
            }
            return $output;
        }

        foreach($dealers as $result) {
            $dealer = array(
                'Location' => $result['first_name'],
                'Name' => $result['last_name'],
                'Address' => $result['address'],
                'City' => $result['city'],
                'State' => $result['state'],
                'Zip' => $result['zip'],
                'Phone' => $result['phone'],
                'Fax' => $result['fax'],
                'Email' => $result['email'],
            );
            fputcsv($fp,convertarray($headerkey, $dealer));
            if ($result['address2'] ||$result['email2']) {
                $dealer = array(
                    'Location' => $result['first_name'],
                    'Name' => $result['last_name'],
                    'Address' => $result['address2'],
                    'City' => $result['city2'],
                    'State' => $result['state2'],
                    'Zip' => $result['zip2'],
                    'Phone' => '',
                    'Fax' => '',
                    'Email' => $result['email2'],
                );
                fputcsv($fp,convertarray($headerkey, $dealer));
            }
            if ($result['email3']) {
                $dealer = array(
                    'Location' => $result['first_name'],
                    'Name' => $result['last_name'],
                    'Address' => '',
                    'City' => '',
                    'State' => '',
                    'Zip' => '',
                    'Phone' => '',
                    'Fax' => '',
                    'Email' => $result['email3'],
                );
                fputcsv($fp,convertarray($headerkey, $dealer));
            }
        }

        fclose($fp);

        exit();
        break;

    default:
        require("menu.php");
        // Start Filtering
        // Get Filters from POST/GET
        $filter = array();
        foreach($_REQUEST as $key => $value) {
            $reg = array();
            if(ereg("^f_(.+)",$key, $reg))// && $value
                $filter[$reg[1]] = $value;
        }

        // Defaults
        if (!array_key_exists('team',$filter)) {
            $filter['team'] = $dealerteam;
        }
        if (!array_key_exists('disabled',$filter)) {
            $filter['disabled'] = 'N';
        }
        if (!array_key_exists('nonPMD',$filter)) {
            $filter['nonPMD'] = '*';
        }
        if (!array_key_exists('dealer_type',$filter)) {
            $filter['dealer_type'] = '*';
        }

        $dealers = db_user_filterlist($filter);
        //if (($security == "S") && (date("j") == 1)) {
        if ($security == "S") {
            // users with the highest security rating have ability to add
            // licensing fee payments on the first day of each month
            // or as long as the payment hasn't been made since the first day
            // may fall on a weekend
            $query = mysql_query("SELECT last_payment FROM fee_payments");
            checkDBError();
            $data = mysql_fetch_object($query);
            $last_payment = $data->last_payment;
            if (date("n",strtotime($last_payment)) <> date("n") ) {
                echo "<p class=\"class=text_12\">&nbsp;<br><b><a href=\"users-fees.php\">Click here</a></b> to update licensing fee payments for the month.</p>";
            }
        }

        ?>
        <br>

        <?php if (isset($msg) && $msg == "true") echo "<p><b>You did not enter the correct password required for changing a dealer 
	 to the highest security.<br>All other changes made to the dealer's information 
	 have been successfully saved.</b></p>"; ?>
        <table class="sortable" id="list2" border="0" cellspacing="0" cellpadding="5"<?php if($massedit) { echo ' width="65%"'; } ?>>
            <tr class="skiptop">
                <td colspan="4">
                    <a href="users.php?action=view">Add New Dealer</a>
                </td>
                <td colspan="4" align="right">
                    <a href="report-dealeraccess.php">Dealer Access Report</a><?php if(secure_is_admin()) { ?>&nbsp;||&nbsp;<a href="/editest.php">EDI Testing</a>&nbsp;||&nbsp;<a href="login-sessions.php">Login Sessions</a><?php } ?>
                </td>
            </tr>
            <FORM method="POST" enctype="multipart/form-data">
                <tr class="skiptop">
                    <td id="userlist" colspan="10" class="text_12">
                        Team: <SELECT name="f_team">
                            <OPTION VALUE="*" <?php if ($filter['team'] == '*') echo "SELECTED"; ?>>*</OPTION>
                            <OPTION VALUE="=*" <?php if ($filter['team'] == '=*') echo "SELECTED"; ?>>Only *</OPTION>
                            <OPTION VALUE="=" <?php if ($filter['team'] == '=') echo "SELECTED"; ?>>None</OPTION>
                            <?php
                            $teamlist = teams_list();
                            foreach ($teamlist as $value) {
                                echo "<OPTION VALUE=\"".$value."\"";
                                if ($filter['team'] == $value)
                                    echo " SELECTED";
                                echo ">".$value."</OPTION>";
                            }
                            echo "</SELECT>";
                            echo "Inactive: <SELECT name=\"f_disabled\">";
                            echo "<OPTION VALUE=\"*\">All</OPTION>";
                            echo "<OPTION VALUE=\"Y\"";
                            if ($filter['disabled'] == 'Y')
                                echo " SELECTED";
                            echo ">Y</OPTION>";
                            echo "<OPTION VALUE=\"N\"";
                            if ($filter['disabled'] == 'N')
                                echo " SELECTED";
                            echo ">N</OPTION>";
                            echo "</SELECT>";
                            ?>
                            <?php echo manager_name(); ?>: <select id="f_manager" name="f_manager"><?php $managers =  managers_list(); ?>
                                <option value="*" <?php if (isset($filter['manager']) && $filter['manager'] == '*') echo "SELECTED"; ?>>All</option>
                                <option value="=" <?php if (isset($filter['manager']) && $filter['manager'] == '=') echo "SELECTED"; ?>>None</option>
                                <?php foreach ($managers as $managerid) {
                                    ?><option value="<?php=$managerid['name'] ?>" <?php if (isset($filter['manager']) && $filter['manager'] == $managerid['name']) echo "SELECTED"; ?>><?php=$managerid['name'] ?></option><?php
                                }
                                ?>
                            </select>
                            Level: <select id="f_level" name="f_level">
                                <option value="*" <?php if (isset($filter['level']) && $filter['level'] == '*') echo "SELECTED"; ?>>All</option>
                                <option value="=" <?php if (isset($filter['level']) && $filter['level'] == '=') echo "SELECTED"; ?>>None</option>
                                <option value="1" <?php if (isset($filter['level']) && $filter['level'] == '1') echo "SELECTED"; ?>>1</option>
                                <option value="TBD" <?php if (isset($filter['level']) && $filter['level'] == 'TBD') echo "SELECTED"; ?>>TBD</option>
                                <option value="2" <?php if (isset($filter['level']) && $filter['level'] == '2') echo "SELECTED"; ?>>2</option>
                                <option value="3" <?php if (isset($filter['level']) && $filter['level'] == '3') echo "SELECTED"; ?>>3</option>
                                <option value="4/5" <?php if (isset($filter['level']) && $filter['level'] == '4/5') echo "SELECTED"; ?>>4/5</option>
                            </select>
                            Division: <select id="f_division" name="f_division">
                                <option value="*" <?php if (isset($filter['division']) && $filter['division'] == '*') echo "SELECTED"; ?>>All</option>
                                <option value="=" <?php if (isset($filter['division']) && $filter['division'] == '=') echo "SELECTED"; ?>>None</option>
                                <option value="1" <?php if (isset($filter['division']) && $filter['division'] == '1') echo "SELECTED"; ?>>1</option>
                                <option value="2" <?php if (isset($filter['division']) && $filter['division'] == '2') echo "SELECTED"; ?>>2</option>
                            </select>
                            Type: <select id="f_dealer_type" name="f_dealer_type">
                                <option value="*" <?php if (isset($filter['dealer_type']) && $filter['dealer_type'] == '*') echo "SELECTED"; ?>>All</option>
                                <option value="F" <?php if (isset($filter['dealer_type']) && $filter['dealer_type'] == 'F') echo "SELECTED"; ?>>Franchisee</option>
                                <option value="L" <?php if (isset($filter['dealer_type']) && $filter['dealer_type'] == 'L') echo "SELECTED"; ?>>Licensee</option>
                            </select>
                            <br/>
                            E-Mail: <input type=text value="<?php echo isset($filter['email']) ? $filter['email'] : ''; ?>" name="f_email">
                            State: <input type=text size=2 value="<?php if (isset($filter['state']) && $filter['state'] != '0') echo $filter['state']; ?>" name="f_state">
                            <input type=submit value="Filter"><?php if(secure_is_admin()) { ?>&nbsp;|&nbsp;<input type="submit" name="massedit" value="Mass Edit">&nbsp;|&nbsp;<input type="submit" name="csvexport" value="CSV Export">
                        <?php } ?></td>
                </tr>
            </form>
            <form name="massedit" method="post" action="users.php?action=massedit">
                <tr>
                    <th class="fat_black_12" bgcolor="#fcfcfc">Name</td>
                    <th class="fat_black_12" bgcolor="#fcfcfc">Location</td>
                    <th class="fat_black_12" bgcolor="#fcfcfc">Phone&nbsp;#</td>
                    <th class="fat_black_12" bgcolor="#fcfcfc">State</td>
                    <th class="fat_black_12" bgcolor="#fcfcfc">E-Mail</td>
                    <th class="fat_black_12" bgcolor="#fcfcfc"><?php if($massedit) { echo 'Level'; } else { echo '&nbsp;'; } ?></td>
                    <?php if($massedit) { ?>
                        <th class="fat_black_12" bgcolor="#fcfcfc">Type</th>
                        <th class="fat_black_12" bgcolor="#fcfcfc" align="right">Manager</td>
                    <?php } else { ?>
                        <th class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
                    <?php } ?>
                </tr>
                <?php if($massedit) { ?>
                    <tr>
                        <td class="text_12" colspan="9" align="right">
                            <?php   foreach($_REQUEST as $key => $value) {
                                if(substr($key, 0, 1)=="f") echo "<input type=\"hidden\" name=\"$key\" value=\"$value\">";
                            }
                            ?><input type="submit" value="Apply Changes"></td></tr> <?php } ?>
                <?php
                foreach ($dealers as $result)
                {
                    ?>
                    <tr>
                        <td class="text_12">
                            <?php if (($result['admin'] != "S")&&($result['admin'] != "A")) {
                            ?><a href="/selectvendor.php?suuser=<?php echo $result['ID'] ?>"><?php
                                echo $result['last_name'];
                                } else  {
                                    echo $result['last_name'];
                                }?>
                            </a></td>
                        <td class="text_12">
                            <?php echo $result['first_name']; ?>
                        </td>
                        <td class="text_12">
                            <?php echo $result['phone']; ?>
                        </td>
                        <td class="text_12">
                            <?php echo $result['state']; ?>
                        </td>
                        <td class="text_12">
                            <a href="mailto:<?php echo urlencode($result['email']);
                            //if ($result['email2']) echo "<br />".$result['email2'];
                            //if ($result['email3']) echo "<br />".$result['email3'];
                            ?>"><?php echo $result['email']; ?></a>
                        </td>
                        <td><?php if ($massedit) {
                                ?><select name="level<?php echo $result['ID']; ?>"><option value="*" <?php if ($result['level'] == '*') echo "SELECTED"; ?>>All</option>
                                <option value="=" <?php if ($result['level'] == '=') echo "SELECTED"; ?>>None</option>
                                <option value="1" <?php if ($result['level'] == '1') echo "SELECTED"; ?>>1</option>
                                <option value="TBD" <?php if ($result['level'] == 'TBD') echo "SELECTED"; ?>>TBD</option>
                                <option value="2" <?php if ($result['level'] == '2') echo "SELECTED"; ?>>2</option>
                                <option value="3" <?php if ($result['level'] == '3') echo "SELECTED"; ?>>3</option>
                                <option value="4/5" <?php if ($result['level'] == '4/5') echo "SELECTED"; ?>>4/5</option>
                                </select>
                            <?php } else { ?>
                            <a href="users.php?action=view&ID=<?php echo $result['ID']; ?>">Edit</a></td><?php } ?>
                        <?php if($massedit) {
                            ?><td><select name="dealer_type<?php echo $result['ID']; ?>"><option value="F"<?php if ($result['dealer_type'] == 'F') echo ' SELECTED'; ?>>Franchisee</option><option value="L"<?php if ($result['dealer_type'] == 'L') echo ' SELECTED'; ?>>Licensee</option><option value="B"<?php if ($result['dealer_type'] == 'B') echo ' SELECTED'; ?>>Both</option><option value="O"<?php if ($result['dealer_type'] == 'O') echo ' SELECTED'; ?>>Other</option></select></td>

                            <td><select name="manager<?php echo $result['ID']; ?>">
                                <?php foreach ($managers as $managerid) {
                                    ?><option value="<?php=$managerid['name'] ?>" <?php if ($result['manager'] == $managerid['name']) echo "SELECTED"; ?>><?php=$managerid['name'] ?></option><?php
                                } ?></select></td><?php
                        } else {
                            ?><td><a href="users-formaccess.php?ID=<?php echo $result['ID']; ?>">Form Access</a></td>
                            <td><a href="users-orders-csv.php?ID=<?php echo $result['ID']; ?>">CSV</a></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </form>
        </table>
        <?php
        break;
}

footer($link);
?>
