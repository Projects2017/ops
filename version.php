<?php


if ($link) {
    $dbversion = "2017050912560"; // Date/Houri/Serial of latest version yyyymmddhh

    $sql = "SELECT `value` FROM `config` WHERE `name` = 'dbversion' LIMIT 1";
    $result = mysql_query($sql, $link);
    if (!mysql_num_rows($result)) {
        $dboldversion = "100";
    } else {
        $array = @mysql_fetch_array($result, MYSQL_ASSOC);
        $dboldversion = $array['value'];
        unset($array);
    }

    $sql = "SELECT `value` FROM `config` WHERE `name` = 'upgrading' LIMIT 1";
    $result = @mysql_query($sql);
    if (!@mysql_num_rows($result)) {
        $sql = "INSERT INTO `config` (`name`,`value`) VALUES ('upgrading', '0')";
        mysql_query($sql);
    } else {
        $upgrade_status = @mysql_fetch_array($result, MYSQL_ASSOC);
        $upgrade_status = $upgrade_status['value'];
        if ($upgrade_status) {

           include($basedir.'down.php');
           exit();
        }
    }

    unset ($sql);
    unset ($result);

    if ($dboldversion < $dbversion) {
        //echo "Cur: ".$dboldversion." New: ".$dbversion."<br>";
        function runsqlfile($file) {
            global $basedir;
            $sql = file($basedir."sql/".$file);
            $sql = implode("\n",$sql);
            $sql = explode(";",$sql);
            array_pop($sql);
            foreach ($sql as $query) {
                set_time_limit(0);
                mysql_query($query); // Run that sucker...
                checkDBError($query);
            }
        }
        $sql = "UPDATE `config` SET `value` = '1' WHERE `name` = 'upgrading'";
        mysql_query($sql);
        ignore_user_abort(true);
        include($basedir.'down.php');
        set_time_limit(0);
        flush();
        ob_flush();

        // Update dbversion in database so that others don't upgrade... while we are
        $sql = "UPDATE `config` SET `value` = '".$dbversion."' WHERE `name` = 'dbversion'";
        mysql_query($sql);

        // top is oldest, thus happens first in upgrade
        if ($dboldversion < "2009073002000") {
        // Include Summary File for pre-2009073002000
            include($basedir."sql/2009073002000.sql.php");
        }

        if ($dboldversion < "2009102923000") {
            // Change over Discounts/Freights to text fields.

            // Doing Item Discounts First
            $sql = "CREATE TABLE `form_item_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `item_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `item_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "CREATE TABLE `form_item_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `item_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `item_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Add freight and discount percentages to `orders` (the order line table)
            $sql = "ALTER TABLE `orders` ADD `freight` CHAR(10) NOT NULL DEFAULT '0' AFTER `item` ,";
            $sql .= " ADD `discount` CHAR(10) NOT NULL DEFAULT '0' AFTER `freight` ";
            mysql_query($sql);
            checkDBerror($sql);

            // Import Historic Item Discounts into the order lines
            $sql = "SELECT `ID`, `item`, `setqty`, `mattqty`, `qty` FROM `orders`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $sql = "SELECT `discount` FROM `snapshot_items` WHERE `id` = '".$row['item']."'";
                $result2 = mysql_query($sql);
                if ($row2 = mysql_fetch_assoc($result2)) {
                    // Insert Per Item Discount Here...
                    $sql = "UPDATE `orders` SET `discount` = '".$row2['discount']."' WHERE `ID` = '".$row['ID']."'";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
            }



            // Import Current Item Discounts
            $sql = "SELECT `ID`, `discount` FROM `form_items`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['discount']) {
                    // It does! we should insert it into the live usage table.
                    $sql = "INSERT INTO `form_item_discount` VALUES (NULL, '".$row['ID']."',1,0,0,'".$row['discount']."')";
                    mysql_query($sql);
                    checkDBerror($sql);
                    $orig_id = mysql_insert_id();
                }
            }

            // Vendor Discount and Freight Table Creation
            $sql = "CREATE TABLE `vendor_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `vendor_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `vendor_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "CREATE TABLE `vendor_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `vendor_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `vendor_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Import Existing Vendor Freight and Discounts
            $sql = "SELECT `ID`, `discount`, `freight` FROM `vendors`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['discount']) {
                    $sql = "INSERT INTO `vendor_discount` (`vendor_id`, `discount`) VALUES ('".$row['ID']."','".$row['discount']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
                if ($row['freight']) {
                    $sql = "INSERT INTO `vendor_freight` (`vendor_id`, `freight`) VALUES ('".$row['ID']."','".$row['freight']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
            }

            // Form Freight and Discounts Table Creation
            $sql = "CREATE TABLE `form_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Freight Tables
            $sql = "CREATE TABLE `form_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Import Existing Form Freight and Discounts
            $sql = "SELECT `ID`, `discount`, `freight` FROM `forms`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if (!is_null($row['discount'])) {
                    $sql = "INSERT INTO `form_discount` (`form_id`, `discount`) VALUES ('".$row['ID']."','".$row['discount']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
                if ($row['freight']) {
                    $sql = "INSERT INTO `form_freight` (`form_id`, `freight`) VALUES ('".$row['ID']."','".$row['freight']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
            }

            // Now to remove old info...
            $sql = "ALTER TABLE `vendors` DROP `discount`, DROP `freight`;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `forms` DROP `discount`, DROP `freight`;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `form_items` DROP `discount`;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `snapshot_items` DROP `discount`;";
            mysql_query($sql);
            checkDBerror($sql);

            // User Discount Tables
            $sql = "CREATE TABLE `user_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `user_id` INT NOT NULL ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `user_id`, `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "SELECT `form_id`, `user_id`, `discount` FROM `discount`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $sql = "INSERT INTO `user_discount` (`user_id`,`form_id`,`discount`) VALUES ('".$row['user_id']."','".$row['form_id']."','".$row['discount']."%')";
                mysql_query($sql);
                checkDBerror($sql);
            }

            $sql = "DROP TABLE `discount`";
            mysql_query($sql);
            checkDBerror($sql);

            // User Freight Tables
            $sql = "CREATE TABLE `user_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `user_id` INT NOT NULL ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `user_id`, `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "SELECT `form_id`, `user_id`, `freight` FROM `freight`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $sql = "INSERT INTO `user_freight` (`user_id`,`form_id`,`freight`) VALUES ('".$row['user_id']."','".$row['form_id']."','".$row['freight']."%')";
                mysql_query($sql);
                checkDBerror($sql);
            }

            $sql = "DROP TABLE `freight`";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "SELECT MAX(ID) as `po` FROM `order_forms`";
            $query = mysql_query($sql);
            checkDBerror($sql);

            $row = mysql_fetch_assoc($query);
            $row = $row['po'];

            // Just for sanities sake... log the latest order_form ID to config
            $sql = "INSERT INTO `config` (`name`,`value`) VALUES ('2009102923000','".$row."')";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2009110511230") {
            $sql = "ALTER TABLE `users` ADD `dealer_type` ENUM('F','L') NOT NULL DEFAULT 'F' AFTER `division`";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "UPDATE `users` SET `dealer_type` = 'L'";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2009110511231") {
            $sql = "ALTER TABLE `users` CHANGE `dealer_type` `dealer_type` ENUM( 'F', 'L', 'B', 'O' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'F'";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2009110515340") {
            $sql = "ALTER TABLE `users` ADD `franchiselist` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `dealerlist`,";
            $sql .= " ADD `franchiselist2` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `franchiselist`,";
            $sql .= " ADD `franchiselist3` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `franchiselist2`";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "ALTER TABLE `users` ADD `licenselist` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `dealerlist`,";
            $sql .= " ADD `licenselist2` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `licenselist`,";
            $sql .= " ADD `licenselist3` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `licenselist2`";
            mysql_query($sql);
            checkDBerror($sql);
            // If their on the dealers list, add them intelligently to both.
            $sql = "UPDATE `users` SET `licenselist` = 'Y' WHERE `dealerlist` = 'Y' AND (`dealer_type` = 'L' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `users` SET `licenselist2` = 'Y' WHERE `dealerlist2` = 'Y' AND (`dealer_type` = 'L' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `users` SET `licenselist3` = 'Y' WHERE `dealerlist3` = 'Y' AND (`dealer_type` = 'L' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `users` SET `franchiselist` = 'Y' WHERE `dealerlist` = 'Y' AND (`dealer_type` = 'F' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `users` SET `franchiselist2` = 'Y' WHERE `dealerlist2` = 'Y' AND (`dealer_type` = 'F' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `users` SET `franchiselist3` = 'Y' WHERE `dealerlist3` = 'Y' AND (`dealer_type` = 'F' OR `dealer_type` = 'B')";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2010010418340" && $MoS_enabled) {
            // Change over Discounts/Freights to text fields.

            // Doing Item Discounts First
            $sql = "CREATE TABLE `MoS_form_item_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `item_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `item_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "CREATE TABLE `MoS_form_item_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `item_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `item_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Add freight and discount percentages to `orders` (the order line table)
            $sql = "ALTER TABLE `MoS_orders` ADD `freight` CHAR(10) NOT NULL DEFAULT '0' AFTER `item` ,";
            $sql .= " ADD `discount` CHAR(10) NOT NULL DEFAULT '0' AFTER `freight` ";
            mysql_query($sql);
            checkDBerror($sql);

            // Import Historic Item Discounts into the order lines
            $sql = "SELECT `ID`, `item`, `setqty`, `mattqty`, `qty` FROM `MoS_orders`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $sql = "SELECT `discount` FROM `MoS_snapshot_items` WHERE `id` = '".$row['item']."'";
                $result2 = mysql_query($sql);
                if ($row2 = mysql_fetch_assoc($result2)) {
                    // Insert Per Item Discount Here...
                    $sql = "UPDATE `MoS_orders` SET `discount` = '".$row2['discount']."' WHERE `ID` = '".$row['ID']."'";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
            }



            // Import Current Item Discounts
            $sql = "SELECT `ID`, `discount` FROM `MoS_form_items`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['discount']) {
                    // It does! we should insert it into the live usage table.
                    $sql = "INSERT INTO `MoS_form_item_discount` VALUES (NULL, '".$row['ID']."',1,0,0,'".$row['discount']."')";
                    mysql_query($sql);
                    checkDBerror($sql);
                    $orig_id = mysql_insert_id();
                }
            }

            // Form Freight and Discounts Table Creation
            $sql = "CREATE TABLE `MoS_form_discount` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `discount` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Freight Tables
            $sql = "CREATE TABLE `MoS_form_freight` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `form_id` INT NOT NULL ,\n";
            $sql .= "  `order` INT NOT NULL ,\n";
            $sql .= "  `from` INT NOT NULL ,\n";
            $sql .= "  `to` INT NOT NULL ,\n";
            $sql .= "  `freight` VARCHAR( 12 ) NOT NULL ,\n";
            $sql .= "  INDEX ( `form_id`, `order` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);

            // Import Existing Form Freight and Discounts
            $sql = "SELECT `ID`, `discount`, `freight` FROM `MoS_forms`";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if (!is_null($row['discount'])) {
                    $sql = "INSERT INTO `MoS_form_discount` (`form_id`, `discount`) VALUES ('".$row['ID']."','".$row['discount']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
                if ($row['freight']) {
                    $sql = "INSERT INTO `MoS_form_freight` (`form_id`, `freight`) VALUES ('".$row['ID']."','".$row['freight']."%')";
                    mysql_query($sql);
                    checkDBerror($sql);
                }
            }

            $sql = "ALTER TABLE `MoS_form_items` ADD `upc` CHAR( 13 ) NULL DEFAULT NULL";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `MoS_forms` DROP `discount`, DROP `freight`;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `MoS_form_items` DROP `discount`;";
            mysql_query($sql);
            checkDBerror($sql);

            $sql = "ALTER TABLE `MoS_snapshot_items` DROP `discount`;";
            mysql_query($sql);
            checkDBerror($sql);

        }

        if ($dboldversion < "2010020711560") {
            $sql = "ALTER TABLE `forms` ADD `mattratio` INT( 10 ) NOT NULL DEFAULT '-1' COMMENT 'Number of Matts Allowed Per Set'";
            mysql_query($sql);
            checkDBerror($sql);
            if ($MoS_enabled) {
                $sql = "ALTER TABLE `MoS_forms` ADD `mattratio` INT( 10 ) NOT NULL DEFAULT '-1' COMMENT 'Number of Matts Allowed Per Set'";
                mysql_query($sql);
                checkDBerror($sql);
            }
        }
        if ($dboldversion < "2010021719430" && !$MoS_enabled) {
            $sql = "ALTER TABLE `salestats` ADD `bedding_craigslist_calls` INT( 11 ) NOT NULL AFTER `bedding_internet_profit`  ,
                    ADD `bedding_craigslist_appts` INT( 11 ) NOT NULL AFTER `bedding_craigslist_calls` ,
                    ADD `bedding_craigslist_show` INT( 11 ) NOT NULL AFTER `bedding_craigslist_appts` ,
                    ADD `bedding_craigslist_sold` INT( 11 ) NOT NULL AFTER `bedding_craigslist_show` ,
                    ADD `bedding_craigslist_retail` DECIMAL( 8, 2 ) NOT NULL AFTER `bedding_craigslist_sold` ,
                    ADD `bedding_craigslist_profit` DECIMAL( 8, 2 ) NOT NULL AFTER `bedding_craigslist_retail` ,
                    ADD `cg_craigslist_calls` INT( 11 ) NOT NULL AFTER `cg_internet_profit`  ,
                    ADD `cg_craigslist_appts` INT( 11 ) NOT NULL AFTER `cg_craigslist_calls` ,
                    ADD `cg_craigslist_show` INT( 11 ) NOT NULL AFTER `cg_craigslist_appts` ,
                    ADD `cg_craigslist_sold` INT( 11 ) NOT NULL AFTER `cg_craigslist_show` ,
                    ADD `cg_craigslist_retail` DECIMAL( 8, 2 ) NOT NULL AFTER `cg_craigslist_sold` ,
                    ADD `cg_craigslist_profit` DECIMAL( 8, 2 ) NOT NULL AFTER `cg_craigslist_retail` ;";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "ALTER TABLE `users` ADD `clbeta` ENUM( 'N', 'Y' ) NOT NULL AFTER `wodsable`";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2010022610540" && !$MoS_enabled) {
            $sql = "UPDATE `claims` SET `default_order` = '!factory_confirm' WHERE `name` = 'order'";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2010031611030" && !$MoS_enabled) {
                $sql = "ALTER TABLE `claims` ADD  `default_dateperiod` VARCHAR( 20 ) NOT NULL AFTER  `sms`";
                mysql_query($sql);
                checkDBerror($sql);

                $sql = "UPDATE `claims` SET `default_dateperiod` = '2 MONTH' WHERE `name` = 'order'";
                mysql_query($sql);
                checkDBerror($sql);
        }
        if ($dboldversion < "2010081920080" && !$MoS_enabled) {
                //$sql = "UPDATE `claimscolumns` SET `order` = `order` + 1 WHERE `form` = 'furniture' AND `order` >= 23";
                //mysql_query($sql);
                //checkDBerror($sql);
                $sql = "ALTER TABLE  `claim_furniture` ADD  `lotnum` VARCHAR( 20 ) NOT NULL DEFAULT  '' AFTER  `s_onbol`;";
                mysql_query($sql);
                checkDBerror($sql);
                $sql = "INSERT INTO  `claimscolumns` (`idnum`,`id`,`nicename`,`required`,`datatype`,`on_summary`,`on_po`,`multiline`,`insert`,`edit`,`visible`,`massedit`,`form`,`url_prefix`,`datatype_special`,`order`,`note`,`dealerfilter`,`vendorfilter`,`adminfilter`,`limit`,`logedit`)
                    VALUES ( NULL ,  'lotnum',  'Lot Number',  '0',  'text',  '1',  '1',  '0',  '1',  '1',  '1',  '0',  'furniture',  '', NULL ,  '32',  '(8 Digit Number Below Bar Code)',  '0',  '0',  '0',  '20',  '0' );";
                mysql_query($sql);
                checkDBerror($sql);
        }
		if ($dboldversion < "2010101310050" && !$MoS_enabled) {
			$sql = "ALTER TABLE  `claims` ADD  `vendor_insertable` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `subject`";
			mysql_query($sql);
			checkDBerror($sql);
		}
        if ($dboldversion < "2010112911040") {
            $sql = "ALTER TABLE  `users` ADD  `fee_pmt_amount` DECIMAL( 9, 2 ) NOT NULL DEFAULT  '1000.00' AFTER  `remaining_fee_pmts`";
            mysql_query($sql);
            checkDBerror($sql);
        }
        if ($dboldversion < "2011021510170") {
            // IP Lockout Table
            $sql = "CREATE TABLE `ip_lockout` (\n";
            $sql .= "  `ip` CHAR( 39 ) NOT NULL PRIMARY KEY ,\n";
            $sql .= "  `attempts` INT NOT NULL ,\n";
            $sql .= "  `lasttry` DATETIME NOT NULL ,\n";
            $sql .= "  INDEX ( `ip`, `lasttry` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2011021511030") {
            // Session Tracking Table
            $sql = "CREATE TABLE `login_session` (\n";
            $sql .= "  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,\n";
            $sql .= "  `key` CHAR(13) NOT NULL ,\n";
            $sql .= "  `login_id` INT NOT NULL ,\n";
            $sql .= "  `lastaccess` DATETIME NOT NULL ,\n";
            $sql .= "  `ip` CHAR(39) NOT NULL ,\n";
            $sql .= "  INDEX ( `id`, `lastaccess` ),\n";
            $sql .= "  UNIQUE ( `login_id` )\n";
            $sql .= ") ENGINE = MYISAM ;";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if ($dboldversion < "2011030212580") {
            // Session Tracking Table
            $sql = "SELECT `id`, `user`, `form` FROM `form_access` ORDER BY `user`, `form`";
            $query = mysql_query($sql);
            checkDBerror($sql);
            $user = 0;
            $form = 0;
            $delete = array();
            while ($row = mysql_fetch_assoc($query)) {
                if ($row['user'] == $user && $row['form'] == $form) {
                    $delete[] = $row['id'];
                }
                $user = $row['user'];
                $form = $row['form'];
            }
            if ($delete) {
                $sql = "DELETE FROM `form_access` WHERE `id` IN (".implode(',',$delete).")";
                mysql_query($sql);
                checkDBerror($sql);
            }
        }

        if ($dboldversion < "2011040510020" && !$MoS_enabled) {
            $sql = "ALTER TABLE `claims` ADD `smsupdatereq` VARCHAR(60) NOT NULL DEFAULT '' AFTER `sms`";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE  `claims` SET  `smsupdatereq` =  'shipdate,delivery_date,delivery_time' WHERE  `name` = 'order';";
            mysql_query($sql);
            checkDBerror($sql);
        }

        // Format change half
        if ($dboldversion < "2011051809580" && !$MoS_enabled) {
            $sql = "ALTER TABLE  `claimscolumns`
                ADD  `onsms` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `logedit` ,
                ADD  `triggersms` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `onsms`";
            mysql_query($sql);
            checkDBerror($sql);
        }

        // Data change
        if ($dboldversion < "2011051809581" && !$MoS_enabled) {
            $sql = "INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `on_po`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`, `logedit`, `onsms`, `triggersms`) VALUES (NULL, 'vendor_id', 'Vendor', '0', '', '1', '0', '0', '1', '1', '1', '0', 'order', '', NULL, '-1', NULL, '1', '0', '1', '-1', '0', '1', '0');";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "UPDATE `claimscolumns` SET `onsms` = '1' WHERE `form` = 'order' AND `id` IN ('vendor_id','po','carrier','shipping_info','delivery_date','delivery_time')";
            mysql_query($sql);
            checkDBerror($sql);
            $sql = "SELECT `name`, `sms`, `smsupdatereq` FROM `claims`";
            $query = mysql_query($sql);
            checkDBerror($sql);
            while ($result = mysql_fetch_assoc($query)) {
                // check that sms is enabled for the form.
                if (!$result['sms'])
                    continue;
                // Make sure there are triggers to be converted.
                if (!$result['smsupdatereq'])
                    continue;
                $triggersms = explode(",",$result['smsupdatereq']);
                $triggers = array();
                foreach ($triggersms as $element) {
                    $triggers[] = "'".mysql_escape_string($element)."'";
                }
                $sql = "UPDATE `claimscolumns` SET `triggersms` = '1' WHERE `form` = '".mysql_escape_string($result['name']);
                $sql .= "' AND `id` IN (".implode(",",$triggers).")";
                mysql_query($sql);
                checkDBerror($sql);
            }
        }

        // Format Change second half
        if ($dboldversion < "2011051809582" && !$MoS_enabled) {
            $sql = "ALTER TABLE  `claims` DROP  `smsupdatereq`";
            mysql_query($sql);
            checkDBerror($sql);
        }

        if($dboldversion < "2013030216000") {
            $sql = "ALTER TABLE `form_items` ADD `seats` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft` ;";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE `snapshot_items` ADD `seats` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft`;";
            mysql_query($sql);
            checkdberror($sql);
            if ($MoS_enabled) {
                $sql = "ALTER TABLE `MoS_form_items` ADD `seats` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft` ;";
                mysql_query($sql);
                checkdberror($sql);
                $sql = "ALTER TABLE `MoS_snapshot_items` ADD `seats` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft`;";
                mysql_query($sql);
                checkdberror($sql);
            }
        }

        if ($dboldversion < "2013080702000") {
            $sql = "ALTER TABLE `claim_order` DROP `cr_link`";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "DELETE FROM `claimscolumns` WHERE `id` = 'cr_link' AND `form` = 'order'";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "UPDATE `claimscolumns` SET `order` = `order` - 1 WHERE `form` = 'order' AND `order` >= 16";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE  `claim_order` ADD  `invoiced` ENUM(  '',  'on' ) NOT NULL DEFAULT  '' AFTER  `factory_confirm`";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "UPDATE `claimscolumns` SET `order` = `order` + 1 WHERE `form` = 'order' AND `order` >= 14";
            mysql_query($sql);
            checkdberror($sql);
            $sql = 'INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `on_po`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`, `logedit`, `onsms`, `triggersms`) VALUES (NULL, \'invoiced\', \'Inv\', \'0\', \'checkbox\', \'1\', \'1\', \'0\', \'1\', \'1\', \'1\', \'1\', \'order\', \'\', NULL, \'14\', NULL, \'0\', \'0\', \'0\', \'-1\', \'0\', \'0\', \'0\');';
            mysql_query($sql);
            checkdberror($sql);
            $sql = "UPDATE `claimscolumns` SET  `nicename` =  'Conf' WHERE  `id` = 'factory_confirm' AND `form` = 'order'";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2013082114000") {
            $sql = "ALTER TABLE  `edi_vendor` ADD  `sender_id` CHAR( 15 ) NULL DEFAULT NULL AFTER  `as2_sendername` , ADD  `sender_qualifier` CHAR( 2 ) NULL DEFAULT NULL AFTER  `sender_id`";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "UPDATE `edi_vendor` SET `sender_id` = '829014849' AND `sender_qualifier` = '01'";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2013082114001") {
            $sql = "CREATE TABLE `document` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `subject` varchar(100) NOT NULL DEFAULT '',
                `text` text NOT NULL,
                `source` text NOT NULL,
                `filter` varchar(255) NOT NULL DEFAULT '[]',
                `expire` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=latin1";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "CREATE TABLE `document_unread` (
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `document_id` int(11) NOT NULL DEFAULT '0',
                    KEY `user_id` (`user_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2013120218540") {
            $sql = "ALTER TABLE  `form_items`
                        ADD  `cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `price`,
                        ADD  `set_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `set_`,
                        ADD  `matt_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `matt`,
                        ADD  `box_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `box` ;";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE  `snapshot_items`
                    ADD  `cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `price`,
                    ADD  `set_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `set_`,
                    ADD  `matt_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `matt`,
                    ADD  `box_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `box` ;";
            mysql_query($sql);
            checkdberror($sql);
            if ($MoS_enabled) {
                $sql = "ALTER TABLE  `MoS_form_items`
                        ADD  `cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `price`,
                        ADD  `set_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `set_`,
                        ADD  `matt_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `matt`,
                        ADD  `box_cost` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `box` ;";
                mysql_query($sql);
                checkdberror($sql);
                $sql = "ALTER TABLE  `MoS_snapshot_items`
                        ADD  `cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `price`,
                        ADD  `set_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `set_`,
                        ADD  `matt_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `matt`,
                        ADD  `box_cost` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER  `box` ;";
                mysql_query($sql);
                checkdberror($sql);
            }
        }

        if ($dboldversion < "2014033111080") {
            $sql = "ALTER TABLE  `vendors` CHANGE  `Access_type`  `Access_type` ENUM(  'Bedding',  'Case Goods',  'Upholstery' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Bedding'";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2014050822590") {
            $sql = "
              CREATE TABLE `user_termpayments` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `label` VARCHAR(50) NOT NULL,
                `date` date NOT NULL,
                `amount` decimal(9,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=latin1
              ";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2014110420370") {
            $sql = "ALTER TABLE `order_forms` ADD `totalcost` DOUBLE(8,2) NULL DEFAULT NULL AFTER `total`;";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2015010201230") {
            $sql = "ALTER TABLE  `form_items` ADD  `markup` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `cost` ;";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE  `form_items` ADD  `set_markup` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `set_cost` ;";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE  `form_items` ADD  `matt_markup` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `matt_cost` ;";
            mysql_query($sql);
            checkdberror($sql);
            $sql = "ALTER TABLE  `form_items` ADD  `box_markup` VARCHAR( 100 ) NOT NULL DEFAULT  '' AFTER  `box_cost` ;";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2015010210090") {
            $sql = "CREATE TABLE IF NOT EXISTS `user_markup` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `form_id` int(11) NOT NULL,
                    `order` int(11) NOT NULL,
                    `from` int(11) NOT NULL,
                    `to` int(11) NOT NULL,
                    `markup` varchar(12) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`,`form_id`,`order`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2015011023240") {
            $sql = "ALTER TABLE `orders`
                ADD `setprice` DECIMAL(9,2) NULL DEFAULT NULL AFTER `setqty`,
                ADD `mattprice` DECIMAL(9,2) NULL DEFAULT NULL AFTER `mattqty`,
                ADD `price` DECIMAL(9,2) NULL DEFAULT NULL AFTER `qty`;";
            mysql_query($sql);
            checkdberror($sql);
        }

        if ($dboldversion < "2015071627440") {
            $sql = "CREATE TABLE `user_tier` (
                `id` int(11) NOT NULL,
                  `user_id` int(11) NOT NULL,
                  `form_id` int(11) NOT NULL,
                  `order` int(11) NOT NULL,
                  `from` int(11) NOT NULL,
                  `to` int(11) NOT NULL,
                  `tier` varchar(12) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            mysql_query($sql);
            checkDBError($sql);
        }

        if ($dboldversion < "2016021120380") {
            $sql = "ALTER TABLE `form_items` ADD `item_tier_override` TINYINT(1) NOT NULL DEFAULT '0' AFTER `upc`;";
            mysql_query($sql);
            checkDBError($sql);
        }

        if ($dboldversion < "2016021120381") {
            $sql = "ALTER TABLE `snapshot_items` ADD `item_tier_override` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sku`;";
            mysql_query($sql);
            checkDBError($sql);
        }

        if ($dboldversion < "2016051120310") {
            $sql = "UPDATE `managers` SET `name` = 'Bob Wert' WHERE `name` = 'Troy Meath';";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "UPDATE `users` SET `manager` = 'Bob Wert' WHERE `manager` = 'Troy Meath';";
            mysql_query($sql);
            checkDBError($sql);
        }

        if ($dboldversion < "2016062700000") {
            $sql = "ALTER TABLE  `users` ADD  `photo` VARCHAR( 500 ) NOT NULL;";
            mysql_query($sql);
            checkDBError($sql);

        }

        if ($dboldversion < "2016072821410") {
             include(dirname(__FILE__)."/version/2016072821410-single.php");
        }

        if ($dboldversion < "2016080100000") {
            $sql = "ALTER TABLE `users` ADD COLUMN `dealer_name_override2` varchar(500);";
            mysql_query($sql);
            checkDBError($sql);
        }
        if ($dboldversion < "2016080100001") {
            $sql = "ALTER TABLE  `users` CHANGE  `dealer_name_override2`  `dealer_name_override` VARCHAR( 500 ) ";
            mysql_query($sql);
            checkDBError($sql);
        }
        if ($dboldversion < "2016080100002") {
            $sql = "ALTER TABLE  `users` CHANGE  `dealer_name_override`  `big_board_name` VARCHAR( 500 ) ";
            mysql_query($sql);
            checkDBError($sql);
        }

	  if ($dboldversion < "2016081400002") {
            $sql = "ALTER TABLE `cms_sliders` ADD `slide_delay` INT NOT NULL AFTER `slider_name`";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "UPDATE `cms_sliders` SET `slide_delay` = '10'";
            mysql_query($sql);
            checkDBError($sql);
        }

	if ($dboldversion < "2016100812540") {
	    $sql = "ALTER TABLE  `vendors` CHANGE  `MAS90_type`  `MAS90_type` ENUM(  'BEDDING',  'CASE',  'LIVING',  'ROYAL',  'DISC' ) NOT NULL DEFAULT  'BEDDING'";
	    mysql_query($sql);
	    checkDBerror($sql);
	}

    if ($dboldversion < "2016100812541") {
        $sql = "UPDATE `vendors` SET `MAS90_type` = 'ROYAL' WHERE `MAS90_type` = 'LIVING'";
        mysql_query($sql);
	    checkDBerror($sql);
	    $sql = "ALTER TABLE  `vendors` CHANGE  `MAS90_type`  `MAS90_type` ENUM(  'BEDDING',  'CASE',  'ROYAL',  'DISC' ) NOT NULL DEFAULT  'BEDDING'";
	    mysql_query($sql);
	    checkDBerror($sql);
	}

        if ($dboldversion < "2016102300001") {

            $sql = "CREATE TABLE `po_payments` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`po_id` int(11) DEFAULT NULL,`login_id` int(11) DEFAULT NULL,`payment_amount` double(8,2) DEFAULT NULL,`payment_datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,`payment_status` varchar(50) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;";
            mysql_query($sql);
            checkDBError($sql);
            
            $sql = "CREATE TABLE `login_customerprofiles` (`ID` int(11) unsigned NOT NULL AUTO_INCREMENT,`login_id` int(11) DEFAULT NULL,`customer_profile_id` varchar(20) DEFAULT NULL,PRIMARY KEY (`ID`)) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;";
            mysql_query($sql);
            checkDBError($sql);
            
            $sql = "ALTER TABLE `orders` ADD `paid` INT NOT NULL DEFAULT '0' AFTER `snapshot_form`";
            mysql_query($sql);
            checkDBError($sql);

            $sql = "ALTER TABLE `order_forms` ADD `paid` INT NOT NULL DEFAULT '0' AFTER `snapshot_form`";
            mysql_query($sql);
            checkDBError($sql);

        }

	  if ($dboldversion < "2016122000002") {
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_grandday` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_2kday` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_5kweek` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_10kweek` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_250kweek` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_500kweek` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_millyear` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_mrecord_mattress` CHAR (1)";
            mysql_query($sql);
            checkDBError($sql);
        }

	  if ($dboldversion < "2017010500001") {
            $sql = "ALTER TABLE `users` ADD COLUMN `lb_incentive_ranking` INT(1)";
            mysql_query($sql);
            checkDBError($sql);
        }


	  if ($dboldversion < "2017011000001") {
            $sql = "ALTER TABLE `users` MODIFY `lb_grandday` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_2kday` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_5kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_10kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_250kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_500kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_millyear` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_mrecord_mattress` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_incentive_ranking` INT(1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
        }


	  if ($dboldversion < "2017011000002") {
            $sql = "ALTER TABLE `claim_bedding` ADD `last_claim_email_sent` DATETIME AFTER `c_barcode`";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `claim_furniture` ADD `last_claim_email_sent` DATETIME AFTER `lotnum`";
            mysql_query($sql);
            checkDBError($sql);
        }

        if ($dboldversion < "2017020800001") {
            $sql = "ALTER TABLE `order_forms` CHANGE `discount_percentage` `discount_percentage` FLOAT NOT NULL DEFAULT '0';";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `order_forms` CHANGE `freight_percentage` `freight_percentage` FLOAT NOT NULL DEFAULT '0';";
            mysql_query($sql);
            checkDBError($sql);
        }

	  if ($dboldversion < "2017011000001") {
            $sql = "ALTER TABLE `users` MODIFY `lb_grandday` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_2kday` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_5kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_10kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_250kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_500kweek` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_millyear` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_mrecord_mattress` CHAR (1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            $sql = "ALTER TABLE `users` MODIFY `lb_incentive_ranking` INT(1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
        }

	  	if ($dboldversion < "2017020900001") {
	  		$sql = "CREATE TABLE `cms_countdown` (
  `cms_countdown_id` int(11) NOT NULL AUTO_INCREMENT,
  `cms_countdown_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`cms_countdown_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);
            
            $sql = "CREATE TABLE `cms_events` (
  `cms_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted` bit(1) DEFAULT b'0',
  KEY `cms_event_id` (`cms_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);

			$sql = "CREATE TABLE `cms_news_articles` (
  `cms_news_article_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `publish_date` datetime DEFAULT NULL,
  `content` text,
  `deleted` bit(1) DEFAULT NULL,
  PRIMARY KEY (`cms_news_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);

			$sql = "CREATE TABLE `cms_resource_categories` (
  `cms_resource_category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  PRIMARY KEY (`cms_resource_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);

			$sql = "CREATE TABLE `cms_resources` (
  `cms_resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cms_resource_category_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `filename` varchar(500) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  PRIMARY KEY (`cms_resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);

            $sql = "ALTER TABLE `users` ADD COLUMN `furniture_and_mattress` INT(1) NOT NULL";
            mysql_query($sql);
            checkDBError($sql);
            
			$sql = "ALTER TABLE `salestats` ADD INDEX `user_id` (`user_id`)";
            mysql_query($sql);
            checkDBError($sql);

		}
		
	  	if ($dboldversion < "2017021400001") {
	  		$sql = "ALTER TABLE `users` MODIFY COLUMN furniture_and_mattress CHAR(1);";
            mysql_query($sql);
            checkDBError($sql);
		}

	  	if ($dboldversion < "2017021400002") {
	  		$sql = "ALTER TABLE `users` MODIFY COLUMN furniture_and_mattress CHAR(1) NOT NULL;";
            mysql_query($sql);
            checkDBError($sql);
		}

	  	if ($dboldversion < "2017021500001") {
	  		$sql = "ALTER TABLE `users` MODIFY COLUMN big_board_name VARCHAR(500) NOT NULL;";
            mysql_query($sql);
            checkDBError($sql);
		}
		
		if ($dboldversion < "2017030607191") {
			$sql = "SELECT `ID`, `comments` FROM `order_forms` WHERE `comments` LIKE 'PO# %Transaction ID: %'";
			$result = mysql_query($sql);
			checkDBError($sql);
			while ($po = mysql_fetch_assoc($result)) {
				$matches = array();
				preg_match_all('!\d+!', $po['comments'], $matches);
				if (isset($matches[0][0]) && isset($matches[0][1])) {
					$sql = "UPDATE `order_forms` SET `comments` = 'PO# ".(((int) $matches[0][0])+1000)."\nTransaction ID: ".$matches[0][1]."' WHERE `ID` = ".$po['ID'];
					mysql_query($sql);
					checkDBerror($sql);
				}
			}
		}

		if ($dboldversion < "2017032207193") {
            $sql = "ALTER TABLE `cms_events` ADD COLUMN `filename` VARCHAR(500)";
			mysql_query($sql);
            checkDBError($sql);
		}
		
		if ($dboldversion < "2017032407201") {
			$sql = "CREATE TABLE IF NOT EXISTS `cms_options` (
  `cms_option_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) DEFAULT NULL,
  `option_value` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`cms_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			mysql_query($sql);
            checkDBError($sql);
            $sql = "TRUNCATE `cms_options`";
			mysql_query($sql);
            checkDBError($sql);
            $sql = "INSERT INTO `cms_options` (`cms_option_id`, `option_name`, `option_value`) VALUES (1,'incentive_trip_total_to_display',''),(2,'incentive_trip_image_path',''),(3,'incentive_trip_countdown_date','2017-09-24'),(4,'incentive_trip_line_1','Riviera Maya, Mexico'),(5,'incentive_trip_line_2','Iberostar Paraiso Beach Resort'),(6,'incentive_trip_line_3','September 24th to 28th 2017'),(7,'incentive_trip_initial_sales_date','2016-09-01');";
			mysql_query($sql);
            checkDBError($sql);
            $sql = "INSERT INTO `cms_options` (`cms_option_id`, `option_name`, `option_value`) VALUES (8,'incentive_trip_sales_floor','75000');";
			mysql_query($sql);
            checkDBError($sql);
		}

	if ($dboldversion < "2017050813400") {
		$sql = "INSERT INTO `cms_templates` (`id`, `name`, `deleted`) VALUES (6, 'Blank', 0);";
		mysql_query($sql);
		checkDBError($sql);
	}

	if ($dboldversion < "2017050912560") {
		$sql = "ALTER TABLE `cms_resources` ADD `is_page` int(1) NOT NULL DEFAULT '0' AFTER `title`;";
		mysql_query($sql);
		checkDBerror($sql);
	}

        // Add more upgrades here... (newest at bottom)
	// Looks like:
	// if ($dboldversion < "2017050912560") {
	// 	$sql = "alter table with blah;";
	// 	mysql_query($sql);
	// 	checkDBerror($sql);
	// }

        // Done Upgrading, go ahead and let everyone back in...
        $sql = "UPDATE `config` SET `value` = '0' WHERE `name` = 'upgrading'";
        mysql_query($sql);
        exit();
    } // End if DB Old
} // End if Link


