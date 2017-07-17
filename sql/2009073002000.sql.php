<?php
/* 
 * Summary SQL upgrade file for pre-2009073002000
 */

if ($dboldversion < "200610021430") {
// Include Summary File for pre-200610021430
    include($basedir."sql/200610021430.summary.php");
}
if ($dboldversion < "200610161600") {
    $sql = "ALTER TABLE `order_forms` CHANGE `ordered` `ordered` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `order_forms` INNER JOIN `orders` ON `order_forms`.`ID` = `orders`.`po_id` SET `order_forms`.`ordered` = CONCAT(`orders`.`ordered`,' ',`orders`.`ordered_time`)";
    mysql_query($sql);
    checkDBError($sql);
}
if ($dboldversion < "200610242000") {
    $sql = "ALTER TABLE `vendors` ADD `proc_email` CHAR( 1 ) NOT NULL DEFAULT 'N', ADD `proc_email2` CHAR( 1 ) NOT NULL DEFAULT 'N';";
    mysql_query($sql);
    checkDBError($sql);
}
if ($dboldversion < "200610251700") {
    $sql = 'ALTER TABLE `forms` ADD `oorvendor` INT(11) NOT NULL AFTER `freight`;';
    mysql_query($sql);
    checkDBError($sql);
}

if ($dboldversion < "200611010900") {
    $sql = 'ALTER TABLE `forms` ADD `allowfree` ENUM( \'N\', \'Y\' ) NOT NULL DEFAULT \'N\';';
    mysql_query($sql);
    checkDBError($sql);
}

if ($dboldversion < "200611141200") {
    runsqlfile('200611141200.sql');
}

if ($dboldversion < "200611192300") {
    $sql = 'ALTER TABLE `claims` ADD `changeemail` VARCHAR(100) NOT NULL AFTER `fromemail`;';
    mysql_query($sql);
    checkDBError($sql);
    $sql = 'UPDATE `claims` SET `changeemail` = \'support@pmdfurniture.com\' WHERE `name` = \'order\';';
    mysql_query($sql);
    checkDBError($sql);
}

if ($dboldversion < "200612110000") {
    $sql = 'ALTER TABLE `form_items` ADD `numinset` SMALLINT NULL ;';
    mysql_query($sql);
    checkDBError($sql);
}

if ($dboldversion < "200612130300") {
    $sql = 'ALTER TABLE `form_items` CHANGE `numinset` `numinset` SMALLINT(6) NOT NULL DEFAULT \'0\'';
    mysql_query($sql);
    checkDBError($sql);
}

if ($dboldversion < "200701061800") {
    if ($MoS_enabled) {
        $sql = "CREATE TABLE `MoS_form_access` ( ".
            "`form_id` int(11) NOT NULL, ".
            "`enabled` char(1) NOT NULL default 'Y', ".
            "KEY `form_id` (`form_id`) ".
            ") ENGINE=MyISAM DEFAULT CHARSET=latin1; ";
        mysql_query($sql);
        checkDBError($sql);
        $sql = "SELECT forms.ID as fid FROM forms";
        $query = mysql_query($sql);
        checkDBerror($sql);
        $orderable = 'Y';
        while ($results = mysql_fetch_Array($query, MYSQL_ASSOC)) {
            $sql = "INSERT INTO `MoS_form_access` (`form_id`, `enabled`) VALUES (".$results['fid'].",'".$orderable."')";
            $query2 = mysql_query($sql);
            checkDBerror($sql);
        }
    }
}

if ($dboldversion < "200701111000") {
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_form_items` ADD `numinset` SMALLINT NOT NULL DEFAULT '0';";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "ALTER TABLE `MoS_forms` ADD `oorvendor` INT( 11 ) NOT NULL DEFAULT '0' AFTER `freight` ;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "ALTER TABLE `MoS_forms` ADD `allowfree` ENUM( 'N', 'Y' ) NOT NULL DEFAULT 'N';";
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "200701170700") {
    $sql = "ALTER TABLE `forms` ADD `alloworder` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'Y';";
    mysql_query($sql);
    checkDBerror($sql);
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_forms` ADD `alloworder` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'Y';";
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "200701220800") {
    $sql = "ALTER TABLE `form_items` ADD `discount` VARCHAR( 12 ) NOT NULL ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `snapshot_items` ADD `discount` VARCHAR( 12 ) NOT NULL ;";
    mysql_query($sql);
    checkDBerror($sql);
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_form_items` ADD `discount` VARCHAR( 12 ) NOT NULL ;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "ALTER TABLE `MoS_snapshot_items` ADD `discount` VARCHAR( 12 ) NOT NULL ;";
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "200702111900" && $MoS_enabled) {
    $sql = "CREATE TABLE `MoS_session` ( "
        ."`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , "
        ."`key` VARCHAR( 13 ) NOT NULL , "
        ."`lastaccess` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , "
        ."`user_id` INT NOT NULL , "
        ."`admin` CHAR NOT NULL "
        .") ENGINE = MYISAM ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200702142000") {
    $sql = "ALTER TABLE `vendors` ADD `proc_url` VARCHAR( 100 ) NOT NULL;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200702262000") {
#$sql = "CREATE TABLE `session` ( "
#	."`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , "
#	."`key` VARCHAR( 13 ) NOT NULL , "
#	."`lastaccess` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , "
#	."`login_id` INT NOT NULL , "
#	."`su_login_id` INT DEFAULT NULL , "
#	."`su_login_type` INT DEFAULT NULL , "
#	."`type` CHAR NOT NULL "
#	.") ENGINE = MYISAM ;";
#mysql_query($sql);
#checkDBerror($sql);
    $sql = "CREATE TABLE `login` ( "
        ."`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , "
        ."`username` VARCHAR( 100 ) NOT NULL , "
        ."`password` VARCHAR( 100 ) NOT NULL , "
        ."`type` ENUM('D','M','A','S','V') NOT NULL DEFAULT 'D', "
        ."`relation_id` INT UNSIGNED NOT NULL "
        .") ENGINE = MYISAM ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "SELECT ID, admin, username, password FROM users";
    $query = mysql_query($sql);
    checkDBerror($sql);
    while ($row = mysql_fetch_assoc($query)) {
        if ($row['admin'] == 'Y') $row['admin'] = 'A';
        elseif ($row['admin'] == 'N') $row['admin'] = 'D';
        elseif ($row['admin'] == '') $row['admin'] = 'D';
        $sql = "INSERT INTO `login` (`username`,`password`,`type`,`relation_id`) VALUES ('".mysql_escape_string($row['username'])."','".mysql_escape_string($row['password'])."','".mysql_escape_string($row['admin']?$row['admin']:'D')."','".$row['ID']."');";
        mysql_query($sql);
        checkDBerror($sql);
    }
    $sql = "SELECT id, login, password FROM vendor";
    $query = mysql_query($sql);
    checkDBerror($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $sql = "INSERT INTO `login` (`username`,`password`,`type`,`relation_id`) VALUES ('".mysql_escape_string($row['login'])."','".mysql_escape_string($row['password'])."','V','".$row['id']."');";
        mysql_query($sql);
        checkDBerror($sql);
    }
    $sql = "ALTER TABLE `users` DROP `username`, DROP `password`;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `vendor` DROP `login`, DROP `password`;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200703130300") {
    if (!$MoS_enabled) {
        $sql = 'UPDATE `claimscolumns` SET `on_summary` = \'0\' WHERE `claimscolumns`.`idnum` = 114 LIMIT 1;';
        mysql_query($sql);
        checkDBerror($sql);
        $sql = 'INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `multiline`, `insert`, `edit`, `visible`, '
            ."`massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`) VALUES "
            ."(NULL, 'cr_link', 'CR', '0', 'text', '1', '0', '1', '1', '1', '0', 'order', 'http://bol.pmddealer.com', NULL, '19', 'Link to BOL/Shipping site', "
            ."'1', '0', '0', '-1');";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "ALTER TABLE `claim_order` ADD `cr_link` CHAR(1) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '$' AFTER `shipping_info`;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '21' WHERE `claimscolumns`.`idnum` = 63 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '20' WHERE `claimscolumns`.`idnum` = 62 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '19' WHERE `claimscolumns`.`idnum` = 61 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '18' WHERE `claimscolumns`.`idnum` = 117 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '17' WHERE `claimscolumns`.`idnum` = 60 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '16' WHERE `claimscolumns`.`idnum` = 116 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `claimscolumns` SET `order` = '15' WHERE `claimscolumns`.`idnum` = 120 LIMIT 1;";
        mysql_query($sql);
        checkDBerror($sql);
    }
}
if ($dboldversion < "200703290200") {
// Add SKU's and Vendor Keys
    $sql = ' ALTER TABLE `form_items` ADD `sku` VARCHAR( 50 ) NOT NULL  ;';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = ' ALTER TABLE `form_items` ADD INDEX ( `sku` ) ;';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'ALTER TABLE `vendor` ADD `key` VARCHAR( 50 ) NOT NULL ;';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'SELECT `id` FROM `vendor`';
    $query = mysql_query($sql);
    checkDBerror($sql);
    while ($row = mysql_fetch_assoc($query)) {
        $sql = 'UPDATE `vendor` SET `key` = "'.mysql_escape_string(md5(uniqid(''))).'" WHERE `id` = "'.$row['id'].'"';
        mysql_query($sql);
        checkDBerror($sql);
    }
}
if ($dboldversion < "200704041200") {
    $sql = "ALTER TABLE `users` CHANGE `region` `division` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `users` SET `division` = '1'";
    mysql_query($sql);
    checkDBerror($sql);
}
if ($dboldversion < "200704150100") {
    $sql = "ALTER TABLE `forms` ADD `header_order` ENUM( 'manual', 'ascending', 'decending' ) NOT NULL DEFAULT 'manual' COMMENT 'Ordering of Headers'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `forms` CHANGE `header_order` `header_order` ENUM( 'manual', 'ascending', 'decending' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ascending' COMMENT 'Ordering of Headers'";
    mysql_query($sql);
    checkDBerror($sql);
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_forms` ADD `header_order` ENUM( 'manual', 'ascending', 'decending' ) NOT NULL DEFAULT 'manual' COMMENT 'Ordering of Headers'";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "ALTER TABLE `MoS_forms` CHANGE `header_order` `header_order` ENUM( 'manual', 'ascending', 'decending' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'ascending' COMMENT 'Ordering of Headers'";
        mysql_query($sql);
        checkDBerror($sql);
    }
}
if ($dboldversion < "200704151300"&&$MoS_enabled) {
    $sql = ' ALTER TABLE `MoS_form_items` ADD `sku` VARCHAR( 50 ) NOT NULL  ;';
    mysql_query($sql);
    checkDBerror($sql);
}
if ($dboldversion < "200707081100" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` ADD `credit_po` INT UNSIGNED NULL AFTER `credit_approved` ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `BoL_forms` ADD `comment` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `credit_po` ;";
    mysql_query($sql);
    checkDBerror($sql);

}
if ($dboldversion < "200707182000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `claimscolumns` ADD `on_po` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `on_summary` ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `url_prefix` = '/shipping/showcredit.php?claim=1&id=',
`note` = 'Link to Shipping CRs' WHERE `claimscolumns`.`idnum` =120 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200707190000" && !$MoS_enabled) {
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =63 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200707230200" && $MoS_enabled) {
    $sql = "CREATE TABLE `MoS_config` (`id` int(11) NOT NULL auto_increment,`name` varchar(50) NOT NULL default '',`value` varchar(50) NOT NULL default '',PRIMARY KEY  (`id`),UNIQUE KEY `name` (`name`));";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "REPLACE INTO `MoS_config` SET `name` = 'comment', `value` = 'Market Order System 2007 PO #%po%'";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200707262300" && !$MoS_enabled) {
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =55 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =56 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =61 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =62 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `on_po` = '0' WHERE `claimscolumns`.`idnum` =114 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `claimscolumns` ( `idnum` , `id` , `nicename` , `required` , `datatype` , `on_summary` , `on_po` , `multiline` , `insert` , `edit` , `visible` , `massedit` , `form` , `url_prefix` , `datatype_special` , `order` , `note` , `dealerfilter` , `vendorfilter` , `adminfilter` , `limit` )
VALUES (
NULL , 'id', 'Claim ID', '0', 'number', '0', '1', '0', '1', '1', '1', '0', 'bedding', '', NULL , '0', NULL , '0', '0', '0', '-1'
), (
NULL , 'id', 'Claim ID', '0', 'number', '0', '1', '0', '1', '1', '1', '0', 'furniture', '', NULL , '0', NULL , '0', '0', '0', '-1'
);";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200709130130" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` CHANGE `shippernum` `trackingnum` VARCHAR(35) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200709231330" && !$MoS_enabled) {
    $sql = "ALTER TABLE `forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `vendor`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `snapshot_forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `name`";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200709231415" && $MoS_enabled) {
    $sql = "ALTER TABLE `MoS_forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `vendor`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `MoS_snapshot_forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `name`";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200709240213" && $MoS_enabled) {
    $sql = "ALTER TABLE `forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `vendor`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `snapshot_forms` ADD `shipper` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `name`";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200710011226") {
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_order_forms` ADD `site` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `user_address`";
        mysql_query($sql);
        checkDBerror($sql);
    } else {
        $sql = "ALTER TABLE `order_forms` ADD `site` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `user_address`";
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "200710141300" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` CHANGE `freight` `freight` DECIMAL(6,2) NULL"; // enables freight amounts to be 0.00
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "200710150000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` ADD `adminprinted` BOOL NOT NULL DEFAULT '0' AFTER `credit_approved`";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "200710182030" && !$MoS_enabled) {
    $sql = "ALTER TABLE `forms` ADD `useshipping` ENUM( 'Y', 'N' ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Y' AFTER `alloworder`";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "200710282457") {
    $sql = "ALTER TABLE `forms` ADD `backorder` ENUM( 'Y', 'N' ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Y' AFTER `header_order`";
    mysql_query($sql);
    checkdberror($sql);
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_forms` ADD `backorder` ENUM( 'Y', 'N' ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Y' AFTER `header_order`";
        mysql_query($sql);
        checkdberror($sql);
    }
}

if ($dboldversion < "200710282458" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` CHANGE `freight` `freight` DECIMAL( 9, 2 ) NULL DEFAULT NULL ";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `BoL_queue` CHANGE `prepaidfreight` `prepaidfreight` DECIMAL( 9, 2 ) NULL DEFAULT NULL ";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "200711041817" && !$MoS_enabled) {
    $sql = "
  		CREATE TABLE `backorder` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`date` DATE NOT NULL ,
			`form_id` INT NOT NULL ,
			`user_id` INT NOT NULL ,
			`address` TINYINT NOT NULL DEFAULT '1',
			`canceled` TINYINT NOT NULL DEFAULT '0',
			`completed` TINYINT NOT NULL DEFAULT '0'
		) ENGINE = MYISAM ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "
		CREATE TABLE `backorder_item` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`backorder_id` INT NOT NULL ,
			`item_id` INT NOT NULL ,
			`qty` INT NOT NULL ,
			`snapshot_id` INT NOT NULL ,
			`canceled` TINYINT NOT NULL DEFAULT '0',
			`completed` INT NOT NULL DEFAULT '0'
		) ENGINE = MYISAM ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200711120924" && !$MoS_enabled) {
    $sql = "ALTER TABLE `backorder` CHANGE `date` `date` DATETIME NOT NULL ";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200711142300" && !$MoS_enabled) {
    $sql = "CREATE TABLE `wodsstats` (
			`stat_id` int(10) unsigned NOT NULL auto_increment primary key,
			`user_id` int(10) unsigned NOT NULL default '0',
			`stat_date` date NOT NULL default '0000-00-00',
			`insert_qty` smallint(6) NOT NULL default '0',
			`insert_sales_qty` smallint(6) NOT NULL default '0',
			`sign_qty` smallint(6) NOT NULL default '0',
			`sign_sales_qty` smallint(6) NOT NULL default '0',
			`customers_qty` smallint(8) NOT NULL default '0',
			`sales_qty` smallint(8) NOT NULL default '0',
			`closing_percentage` decimal(3,2) NOT NULL default '0',
			`retail_sales_sum` decimal(10,2) NOT NULL default '0.00',
			`retail_sales_avg` decimal(10,2) NOT NULL default '0.00',
			`gross_profit_sum` decimal(10,2) NOT NULL default '0.00',
			`gross_profit_avg` decimal(10,2) NOT NULL default '0.00',
			`expenses_sum` decimal(10,2) NOT NULL default '0.00',
			`profit` decimal(10,2) NOT NULL default '0.00',
			`createdate` DATE NOT NULL ,
			`edits` INT(11) NOT NULL DEFAULT '1',
			`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
		) ENGINE = MyISAM ;";

    mysql_query($sql);
    checkDBerror($sql);
}


if ($dboldversion < "200711150000" && !$MoS_enabled) {
    $sql = "CREATE TABLE `fieldvisit` (
			`visit_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`visitor_id` INT UNSIGNED NOT NULL ,
			`dealer` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,
			`manager` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,
			`visitdate` DATE NOT NULL ,
			`writeup` BLOB NOT NULL ,
			`grade1` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade2` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade3` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade4` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade5` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade6` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade7` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade8` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade9` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`grade10` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
			`createdate` DATE NOT NULL ,
			`edits` INT NOT NULL ,
			`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
		) ENGINE = MYISAM ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `users` ADD `wodsable` CHAR( 1 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'N' AFTER `team` ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200711160000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `forms` CHANGE `backorder` `backorder` ENUM('Y','N') DEFAULT 'N' NOT NULL ";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `forms` SET `backorder` = 'N'";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200712020000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `claimscolumns` ADD `logedit` ENUM( '0', '1' ) NOT NULL DEFAULT '0';";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `on_po`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`, `logedit`) VALUES (NULL, \'delivery_date\', \'Delivery Date\', \'0\', \'date\', \'1\', \'1\', \'0\', \'0\', \'1\', \'1\', \'0\', \'order\', \'\', NULL, \'66\', NULL, \'0\', \'0\', \'0\', \'-1\', \'1\'), (NULL, \'delivery_time\', \'Delivery Time\', \'0\', \'text\', \'1\', \'1\', \'0\', \'0\', \'1\', \'1\', \'0\', \'order\', \'\', NULL, \'67\', NULL, \'0\', \'0\', \'0\', \'10\', \'1\');';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'ALTER TABLE `claim_order` ADD `delivery_date` DATE NOT NULL, ADD `delivery_time` VARCHAR(10) NOT NULL;';
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200712052000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `wodsstats` CHANGE `insert_qty` `inserts_out` SMALLINT( 6 ) NOT NULL DEFAULT '0',
CHANGE `insert_sales_qty` `inserts_show` SMALLINT( 6 ) NOT NULL DEFAULT '0',
CHANGE `sign_qty` `signs_out` SMALLINT( 6 ) NOT NULL DEFAULT '0',
CHANGE `sign_sales_qty` `signs_sold` SMALLINT( 6 ) NOT NULL DEFAULT '0',
CHANGE `customers_qty` `repeats_show` SMALLINT( 8 ) NOT NULL DEFAULT '0',
CHANGE `sales_qty` `repeats_sold` SMALLINT( 8 ) NOT NULL DEFAULT '0',
CHANGE `retail_sales_sum` `inserts_retail` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `retail_sales_avg` `repeats_retail` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `gross_profit_sum` `inserts_profit` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `gross_profit_avg` `repeats_profit` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `expenses_sum` `signs_retail` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `profit` `others_profit` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `wodsstats` ADD `inserts_sold` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `others_profit` ,
ADD `signs_show` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `inserts_sold` ,
ADD `signs_profit` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `signs_show` ,
ADD `repeats_out` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `signs_profit` ,
ADD `others_out` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `repeats_out` ,
ADD `others_show` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `others_out` ,
ADD `others_sold` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `others_show` ,
ADD `others_retail` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `others_sold`;";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "200712122300") {
    if (!$MoS_enabled) {
        $sql = "ALTER TABLE `order_forms` ADD `printedonsummary` ENUM('N','Y') DEFAULT 'N' AFTER `site`;";
        mysql_query($sql);
        checkDBerror($sql);
    }
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_order_forms` ADD `printedonsummary` ENUM('N','Y') DEFAULT 'N' AFTER `site`;";
        mysql_query($sql);
        checkDBerror($sql);
    }
}


if ($dboldversion < "200712191745" && !$MoS_enabled) {
    $sql = "ALTER TABLE `wodsstats` CHANGE `inserts_out` `inserts_out` INT NOT NULL DEFAULT '0',
CHANGE `inserts_show` `inserts_show` INT NOT NULL DEFAULT '0',
CHANGE `signs_out` `signs_out` INT NOT NULL DEFAULT '0',
CHANGE `signs_sold` `signs_sold` INT NOT NULL DEFAULT '0',
CHANGE `repeats_show` `repeats_show` INT NOT NULL DEFAULT '0',
CHANGE `repeats_sold` `repeats_sold` INT NOT NULL DEFAULT '0'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `salestats` CHANGE `ads_calls` `ads_calls` INT NOT NULL DEFAULT '0', CHANGE `ads_appts` `ads_appts` INT NOT NULL DEFAULT '0', CHANGE `ads_show` `ads_show` INT NOT NULL DEFAULT '0', CHANGE `ads_sold` `ads_sold` INT NOT NULL DEFAULT '0', CHANGE `babycase_calls` `babycase_calls` INT NOT NULL DEFAULT '0', CHANGE `babycase_appts` `babycase_appts` INT NOT NULL DEFAULT '0', CHANGE `babycase_show` `babycase_show` INT NOT NULL DEFAULT '0', CHANGE `babycase_sold` `babycase_sold` INT NOT NULL DEFAULT '0', CHANGE `bedding_signs_calls` `bedding_signs_calls` INT NOT NULL DEFAULT '0', CHANGE `bedding_signs_appts` `bedding_signs_appts` INT NOT NULL DEFAULT '0', CHANGE `bedding_signs_show` `bedding_signs_show` INT NOT NULL DEFAULT '0', CHANGE `bedding_signs_sold` `bedding_signs_sold` INT NOT NULL DEFAULT '0', CHANGE `bedroom_calls` `bedroom_calls` INT NOT NULL DEFAULT '0', CHANGE `bedroom_appts` `bedroom_appts` INT NOT NULL DEFAULT '0', CHANGE `bedroom_show` `bedroom_show` INT NOT NULL DEFAULT '0', CHANGE `bedroom_sold` `bedroom_sold` INT NOT NULL DEFAULT '0', CHANGE `living_calls` `living_calls` INT NOT NULL DEFAULT '0', CHANGE `living_appts` `living_appts` INT NOT NULL DEFAULT '0', CHANGE `living_show` `living_show` INT NOT NULL DEFAULT '0', CHANGE `living_sold` `living_sold` INT NOT NULL DEFAULT '0', CHANGE `dining_calls` `dining_calls` INT NOT NULL DEFAULT '0', CHANGE `dining_appts` `dining_appts` INT NOT NULL DEFAULT '0', CHANGE `dining_show` `dining_show` INT NOT NULL DEFAULT '0', CHANGE `dining_sold` `dining_sold` INT NOT NULL DEFAULT '0', CHANGE `cg_signs_calls` `cg_signs_calls` INT NOT NULL DEFAULT '0', CHANGE `cg_signs_appts` `cg_signs_appts` INT NOT NULL DEFAULT '0', CHANGE `cg_signs_show` `cg_signs_show` INT NOT NULL DEFAULT '0', CHANGE `cg_signs_sold` `cg_signs_sold` INT NOT NULL DEFAULT '0'";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2008001122310") {
    if ($MoS_enabled) {
        $sql = "CREATE TABLE `MoS_login_prefs` (
		  `login_id` int(11) NOT NULL,
		  `name` char(30) NOT NULL,
		  `content` varchar(255) NOT NULL,
		  PRIMARY KEY  (`login_id`,`name`)
		) COMMENT='Holds Login Preferences';";
        mysql_query($sql);
        checkDBerror($sql);
    } else {
        $sql = "CREATE TABLE `login_prefs` (
		  `login_id` int(11) NOT NULL,
		  `name` char(30) NOT NULL,
		  `content` varchar(255) NOT NULL,
		  PRIMARY KEY  (`login_id`,`name`)
		) COMMENT='Holds Login Preferences';";
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "2008001142310" && $MoS_enabled) {
    $sql = "ALTER TABLE `MoS_forms` ADD `useshipping` ENUM( 'Y', 'N' ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Y' AFTER `alloworder`";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008002061700" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_forms` ADD `csv_exported` DATETIME NULL AFTER `credit_po`";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008002061701" && !$MoS_enabled) {
    $sql = "ALTER TABLE `order_forms` ADD `csv_exported` DATETIME NULL AFTER `printedonsummary`";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008002132238" && !$MoS_enabled) {
// changes to Field Visit form
    $sql = "CREATE TABLE `fieldvisit_columns` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ,
`description` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ,
`sort` INT( 10 ) UNSIGNED NULL DEFAULT '1', `section` INT NOT NULL DEFAULT '0',
`type` ENUM( 'text', 'checkbox', 'textarea', 'option', 'section', 'label' ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'text',
`options` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ,
 `required` BOOL NOT NULL DEFAULT '0', `numeric` BOOL NULL DEFAULT '0',
INDEX ( `name` )
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Field visit columns' ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql ="INSERT INTO `fieldvisit_columns` (name, description, `sort`, section, type) VALUES ('dealer_info', 'Dealer Information', 1, 0, 'section'),('phone','Phone Script', 3, 0, 'section'), ('door_greet', 'Door Greeting', 4, 0, 'section'
), (
'ads', 'Advertising', 5, 0, 'section'
), (
'competition', 'Competition', 6, 0, 'section'
), (
'merch', 'Merchandising Line-Up', 7, 0, 'section'
), (
'pricesheets', 'Price Sheets', 8, 0, 'section'
), (
'sales', 'Sales Presentation', 9, 0, 'section'
), (
'ops', 'Operations', 10, 0, 'section'
), (
'orgdev', 'Organizational Development', 11, 0, 'section'
), (
'attitude', 'Attitude', 12, 0, 'section'
), (
'dealer_todo', 'Dealer To-Do List after Field Visit', 13, 0, 'textarea'
), (
'addl_comments', 'Additional Comments', 14, 0, 'textarea'
);";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql = "INSERT INTO `fieldvisit_columns` ( name, description, sort, section,
TYPE , options )
VALUES (
'field_visit_date', 'Date of Field Visit', 1, 1, 'text', NULL
), (
'field_visit_time', 'Length of Time at Field Visit', 2, 1, 'text', NULL
), (
'sq_ft', 'Warehouse Square Footage', 3, 1, 'text', NULL
), (
'rent', 'Rent', 4, 1, 'text', NULL
), (
'weekly_exp', 'Weekly Expenses (excluding dealer salary)', 5, 1, 'text', NULL
), (
'weekly_ad_exp', 'Weekly Ad Expense', 6, 1, 'text', NULL
), (
'avg_weekly_gp', 'Average Weekly Gross Profit', 7, 1, 'text', NULL
), (
'best_gp_day', 'Best Gross Profit Day (in $)', 8, 1, 'text', NULL
), (
'best_gp_week', 'Best Gross Profit Week (in $)', 9, 1, 'text', NULL
), ( 'propersign', 'Proper Sign on Door w/ Clearance Center Policies', 2, 0, 'checkbox', NULL
), (
'ph_word4word', 'Word for Word', 1, 3, 'option', '1,2,3,4,5'
), (
'ph_coverage', 'Coverage (missed calls)', 2, 3, 'option', '1,2,3,4,5'
), (
'ph_comments', 'Comments', 3, 3, 'textarea', NULL
), (
'dr_word4word', 'Word for Word', 1, 4, 'option', '1,2,3,4,5'
), (
'dr_comments', 'Comments', 2, 4, 'textarea', NULL
), (
'ad_sources', 'Number of Sources', 1, 5, 'text', NULL
), (
'ad_total', 'Number of Ads in All Sources', 2, 5, 'text', NULL
), (
'ad_quality', 'Quality of Ads', 3, 5, 'option', '1,2,3,4,5'
), (
'ad_fbl', 'First, Best, Lowest Price', 4, 5, 'checkbox', NULL
), (
'ad_prices', 'Price on Ads', 5, 5, 'label', NULL
), (
'ad_twinfull', 'Twin/Full Size', 6, 5, 'text', NULL
), (
'ad_queen', 'Queen', 7, 5, 'text', NULL
), (
'ad_king', 'King', 8, 5, 'text', NULL
), (
'ad_memory', 'Memory Foam', 9, 5, 'text', NULL
), (
'ad_bedroom', 'Bedroom Set', 10, 5, 'text', NULL
), (
'ad_dining', 'Dining Room Set', 11, 5, 'text', NULL
), (
'ad_leatherlive', 'Leather Living Room Set', 12, 5, 'text', NULL
), (
'ad_microlive', 'Microfiber Living Room Set', 13, 5, 'text', NULL
), (
'ad_addl', 'Additional Ads', 14, 5, 'text', NULL
), (
'ad_comments', 'Comments', 15, 5, 'textarea', NULL
), ('competitors_classifieds','Competition in Classifieds',1,6,'checkbox',NULL), ('competitors_ads','Number of Competitor\'s Ads',2,6,'text',NULL), ('competitors_price','Price on Competitor\'s Ads',3,6,'label',NULL), ('compete_bedding_twinfull','Twin/Full Size',4,6,'text',NULL), ('compete_bedding_queen','Queen',5,6,'text',NULL), ('compete_bedding_king','King',6,6,'text',NULL), ('compete_bedding_memory','Memory Foam',7,6,'text',NULL), ('compete_bedding_set','Bedroom Set',8,6,'text',NULL), ('compete_diningroom_set','Dining Room Set',9,6,'text',NULL), ('compete_livingroom_set_leather','Leather Living Room Set',10,6,'text',NULL), ('compete_livingroom_set_micro','Microfiber Living Room Set',11,6,'text',NULL), ('compete_comments','Comments',12,6,'textarea',NULL), ('lineup','List of Lineup',1,7,'label',NULL), ('bedding_lineup','Bedding',2,7,'textarea',NULL), ('premium_bedding_lineup','Premium Bedding',3,7,'textarea',NULL), ('bedroom_sets_lineup','Bedroom Sets',4,7,'textarea',NULL), ('dinette_sets_lineup','Dinette/Pub Sets',5,7,'textarea',NULL), ('formal_dining_lineup','Formal Dining Sets',6,7,'textarea',NULL), ('microfiber_lineup','Microfiber Sets',7,7,'textarea',NULL), ('leather_sets_lineup','Leather Sets',8,7,'textarea',NULL), ('chenille_lineup','Chenille',9,7,'textarea',NULL), ('protect_a_bed','Protect-a-Bed',10,7,'textarea',NULL), ('guardsman','Guardsman',11,7,'checkbox',NULL), ('cleanliness','Cleanliness',12,7,'checkbox',NULL), ('bed_spreads','Bed Spreads',13,7,'checkbox',NULL), ('premium_bedding_section','Premium Bedding Section',14,7,'checkbox',NULL), ('merch_comments','Comments',15,7,'textarea',NULL), ('quoted_properly','Quoted Properly',1,8,'checkbox',NULL), ('corrected_priced','Corrected Priced',2,8,'checkbox',NULL), ('pricesheets_comments','Comments',3,8,'textarea',NULL), ('sales_quote_price','Quote Price Properly',1,9,'option','1,2,3,4,5'), ('sales_lie_on_beds','Lie on All Beds',2,9,'option','1,2,3,4,5'), ('sales_build_value','Build Value',3,9,'option','1,2,3,4,5'), ('sales_addons','Add-ons After Sale',4,9,'option','1,2,3,4,5'), ('sales_comments','Comments',5,9,'textarea',NULL), ('ops_pickup','Pick Up Program',1,10,'checkbox',NULL), ('ops_delivery','Delivery Program',2,10,'checkbox',NULL), ('ops_organization','Overall Organization of Warehouse/Showroom',3,10,'option','1,2,3,4,5'), ('ops_comments','Comments',4,10,'textarea',NULL), ('org_dev_pt','Number of Part-Time Assistants',1,11,'text',NULL), ('org_dev_ft','Number of Full-Time Assistants',2,11,'text',NULL), ('org_dev_follow','Following the PMD Organizational Program',3,11,'checkbox',NULL), ('org_dev_comments','Comments',4,11,'textarea',NULL), ('attitude_dealer','Overall Attitude of Dealer',1,12,'option','1,2,3,4,5'), ('attitude_followthru','Follow Through',2,12,'option','1,2,3,4,5'), ('attitude_coachable','Coachable',3,12,'option','1,2,3,4,5'), ('none','',4,12,'label',NULL);";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `type` = 'checkbox' WHERE `fieldvisit_columns`.`id` =65 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="ALTER TABLE `fieldvisit` CHANGE `dealer` `dealer_id` INT NULL ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="ALTER TABLE `fieldvisit` DROP `manager`;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="ALTER TABLE `fieldvisit` CHANGE `visitdate` `field_visit_date` DATE NOT NULL;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="ALTER TABLE `fieldvisit` ADD `field_visit_time` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `field_visit_date` ,
ADD `sq_ft` DECIMAL( 11, 2 ) NULL AFTER `field_visit_time` ,
ADD `rent` DECIMAL( 11, 2 ) NULL AFTER `sq_ft` ,
ADD `weekly_exp` DECIMAL( 11, 2 ) NULL AFTER `rent` ,
ADD `weekly_ad_exp` DECIMAL( 11, 2 ) NULL AFTER `weekly_exp` ,
ADD `avg_weekly_gp` DECIMAL( 11, 2 ) NULL AFTER `weekly_ad_exp` ,
ADD `best_gp_day` DECIMAL( 11, 2 ) NULL AFTER `avg_weekly_gp` ,
ADD `best_gp_week` DECIMAL( 11, 2 ) NULL AFTER `best_gp_day` ,
ADD `propersign` BOOL NULL AFTER `best_gp_week` ,
ADD `ph_word4word` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `propersign` ,
ADD `ph_coverage` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `ph_word4word` ,
ADD `ph_comments` BLOB NULL AFTER `ph_coverage` ,
ADD `dr_word4word` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `ph_comments` ,
ADD `dr_comments` BLOB NULL AFTER `dr_word4word` ,
ADD `ad_sources` INT NULL AFTER `dr_comments` ,
ADD `ad_total` INT NULL AFTER `ad_sources` ,
ADD `ad_quality` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `ad_total` ,
ADD `ad_fbl` BOOL NULL AFTER `ad_quality` ,
ADD `ad_twinfull` DECIMAL( 11, 2 ) NULL AFTER `ad_fbl` ,
ADD `ad_queen` DECIMAL( 11, 2 ) NULL AFTER `ad_twinfull` ,
ADD `ad_king` DECIMAL( 11, 2 ) NULL AFTER `ad_queen` ,
ADD `ad_memory` DECIMAL( 11, 2 ) NULL AFTER `ad_king` ,
ADD `ad_bedroom` DECIMAL( 11, 2 ) NULL AFTER `ad_memory` ,
ADD `ad_dining` DECIMAL( 11, 2 ) NULL AFTER `ad_bedroom` ,
ADD `ad_leatherlive` DECIMAL( 11, 2 ) NULL AFTER `ad_dining` ,
ADD `ad_microlive` DECIMAL( 11, 2 ) NULL AFTER `ad_leatherlive` ,
ADD `ad_addl` BLOB NULL AFTER `ad_microlive` ,
ADD `ad_comments` BLOB NULL AFTER `ad_addl` ,
ADD `competitors_classifieds` BOOL NULL AFTER `ad_comments` ,
ADD `competitors_ads` INT NULL AFTER `competitors_classifieds` ,
ADD `compete_bedding_twinfull` DECIMAL( 11, 2 ) NULL AFTER `competitors_ads` ,
ADD `compete_bedding_queen` DECIMAL( 11, 2 ) NULL AFTER `compete_bedding_twinfull` ,
ADD `compete_bedding_king` DECIMAL( 11, 2 ) NULL AFTER `compete_bedding_queen` ,
ADD `compete_bedding_memory` DECIMAL( 11, 2 ) NULL AFTER `compete_bedding_king` ,
ADD `compete_bedding_set` DECIMAL( 11, 2 ) NULL AFTER `compete_bedding_memory` ,
ADD `compete_diningroom_set` DECIMAL( 11, 2 ) NULL AFTER `compete_bedding_set` ,
ADD `compete_livingroom_set_leather` DECIMAL( 11, 2 ) NULL AFTER `compete_diningroom_set` ,
ADD `compete_livingroom_set_micro` DECIMAL( 11, 2 ) NULL AFTER `compete_livingroom_set_leather` ,
ADD `compete_comments` BLOB NULL AFTER `compete_livingroom_set_micro` ,
ADD `bedding_lineup` BLOB NULL AFTER `compete_comments` ,
ADD `premium_bedding_lineup` BLOB NULL AFTER `bedding_lineup` ,
ADD `bedroom_sets_lineup` BLOB NULL AFTER `premium_bedding_lineup` ,
ADD `dinette_sets_lineup` BLOB NULL AFTER `bedroom_sets_lineup` ,
ADD `formal_dining_lineup` BLOB NULL AFTER `dinette_sets_lineup` ,
ADD `microfiber_lineup` BLOB NULL AFTER `formal_dining_lineup` ,
ADD `leather_sets_lineup` BLOB NULL AFTER `microfiber_lineup` ,
ADD `chenille_lineup` BLOB NULL AFTER `leather_sets_lineup` ,
ADD `protect_a_bed` BOOL NULL AFTER `chenille_lineup` ,
ADD `guardsman` BOOL NULL AFTER `protect_a_bed` ,
ADD `cleanliness` BOOL NULL AFTER `guardsman` ,
ADD `bed_spreads` BOOL NULL AFTER `cleanliness` ,
ADD `premium_bedding_section` BOOL NULL AFTER `bed_spreads` ,
ADD `merch_comments` BLOB NULL AFTER `premium_bedding_section` ,
ADD `quoted_properly` BOOL NULL AFTER `merch_comments` ,
ADD `corrected_priced` BOOL NULL AFTER `quoted_properly` ,
ADD `pricesheets_comments` BLOB NULL AFTER `corrected_priced` ,
ADD `sales_quote_price` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `pricesheets_comments` ,
ADD `sales_lie_on_beds` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `sales_quote_price` ,
ADD `sales_build_value` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `sales_lie_on_beds` ,
ADD `sales_addons` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `sales_build_value` ,
ADD `sales_comments` BLOB NULL AFTER `sales_addons` ,
ADD `ops_pickup` BOOL NULL AFTER `sales_comments` ,
ADD `ops_delivery` BOOL NULL AFTER `ops_pickup` ,
ADD `ops_organization` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `ops_delivery` ,
ADD `ops_comments` BLOB NULL AFTER `ops_organization` ,
ADD `org_dev_pt` DECIMAL( 5, 2 ) NULL AFTER `ops_comments` ,
ADD `org_dev_ft` DECIMAL( 5, 2 ) NULL AFTER `org_dev_pt` ,
ADD `org_dev_follow` BOOL NULL AFTER `org_dev_ft` ,
ADD `org_dev_comments` BLOB NULL AFTER `org_dev_follow` ,
ADD `attitude_dealer` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `org_dev_comments` ,
ADD `attitude_followthru` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `attitude_dealer` ,
ADD `attitude_coachable` ENUM( '1', '2', '3', '4', '5' ) NULL AFTER `attitude_followthru` ,
ADD `dealer_todo` BLOB NULL AFTER `attitude_coachable` ,
ADD `addl_comments` BLOB NULL AFTER `dealer_todo` ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =16 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =17 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =18 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql ="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =19 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sq1="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =20 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =21 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =22 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =29 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `numeric` = '1' WHERE `fieldvisit_columns`.`id` =30 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql="UPDATE `fieldvisit_columns` SET `required` = '1' WHERE `fieldvisit_columns`.`id` =14 LIMIT 1 ;";
    $que = mysql_query($sql);
    $sql = "ALTER TABLE `fieldvisit` CHANGE `propersign` `propersign` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `competitors_classifieds` `competitors_classifieds` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `protect_a_bed` `protect_a_bed` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `guardsman` `guardsman` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `cleanliness` `cleanliness` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `bed_spreads` `bed_spreads` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `premium_bedding_section` `premium_bedding_section` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `quoted_properly` `quoted_properly` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `corrected_priced` `corrected_priced` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `ops_pickup` `ops_pickup` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `ops_delivery` `ops_delivery` TINYINT( 1 ) NOT NULL DEFAULT '0',
CHANGE `org_dev_follow` `org_dev_follow` TINYINT( 1 ) NOT NULL DEFAULT '0';";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql ="ALTER TABLE `fieldvisit` ADD `clearance_phone` VARCHAR( 15 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `dealer_id` ;";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql ="ALTER TABLE `fieldvisit` ADD `email_number` ENUM( '1', '2', '3' ) NULL AFTER `clearance_phone` ;";
    $que = mysql_query($sql);
    checkdberror($sql);


}

if($dboldversion < "2008002212100" && !$MoS_enabled) {
    $sql = "ALTER TABLE `fieldvisit` CHANGE `corrected_priced` `correctly_priced` TINYINT( 1 ) NOT NULL DEFAULT '0'";
    $que = mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `fieldvisit_columns` SET `name` = 'correctly_priced', `description` = 'Correctly Priced' WHERE `fieldvisit_columns`.`id` =72 LIMIT 1 ;";
    $que = mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008003102100" && !$MoS_enabled) {
    $sql = "CREATE TABLE `commercehub_counter` (
	`key` VARCHAR( 25 ) NOT NULL ,
	`int` INT NOT NULL DEFAULT '1',
	PRIMARY KEY ( `key` )
	)";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `commercehub_counter` VALUES ('fa',2),('inv',1),('confirm',1),('package',1)";
    mysql_query($sql);
    checkDBerror($sql);

    $sql = 'CREATE TABLE `ch_personplace` ('
        . ' `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, '
        . ' `ch_id` VARCHAR(20) NULL, '
        . ' `name1` VARCHAR(255) NOT NULL, '
        . ' `address1` VARCHAR(255) NOT NULL, '
        . ' `address2` VARCHAR(255) NOT NULL, '
        . ' `city` VARCHAR(255) NOT NULL, '
        . ' `state` VARCHAR(255) NOT NULL, '
        . ' `country` VARCHAR(255) NOT NULL, '
        . ' `postalCode` VARCHAR(40) NOT NULL, '
        . ' `dayPhone` VARCHAR(20) NOT NULL, '
        . ' `email` VARCHAR(255) NOT NULL, '
        . ' `partnerId` VARCHAR(20) NOT NULL, '
        . ' `companyName` VARCHAR(255) NOT NULL,'
        . ' INDEX (`ch_id`)'
        . ' )'
        . ' ENGINE = myisam;';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'CREATE TABLE `ch_order` ('
        . ' `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
        . ' `po` INT NOT NULL, '
        . ' `billto` INT NOT NULL, '
        . ' `shipto` INT NOT NULL, '
        . ' `batch` INT NOT NULL, '
        . ' `merchantpo` INT NOT NULL,'
        . ' INDEX (`po`)'
        . ' )'
        . ' ENGINE = myisam;';
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2008003211000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `fieldvisit` ADD `files` BLOB NULL AFTER `addl_comments` ;";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008032214000") {
    $prefix = '';
    if ($MoS_enabled) $prefix = 'MoS_';
    $sql = 'ALTER TABLE `'.$prefix.'order_forms` ADD `customer` INT NULL AFTER `csv_exported` ,';
    $sql .= ' ADD `shipto` INT NULL AFTER `customer` ,';
    $sql .= ' ADD `nobolmerge` TINYINT( 1 ) NOT NULL DEFAULT "0" AFTER `snapshot_form` ;';
    mysql_query($sql);
    checkDBerror($sql);
    $sql = 'ALTER TABLE `snapshot_users` CHANGE `address` `address` TINYTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL';
    mysql_query($sql);
    checkDBerror($sql);

    if (!$MoS_enabled) {
        $sql = 'ALTER TABLE `ch_personplace` ADD `snapshot` INT NOT NULL AFTER `ch_id` ;';
        mysql_query($sql);
        checkDBerror($sql);
        $sql = 'SELECT * FROM `ch_order`';
        $query = mysql_query($sql);
        checkDBerror($sql);
        while ($order = mysql_fetch_assoc($query)) {
            $personplace = array();
            $personplace['billto'] = $order['billto'];
            $personplace['shipto'] = $order['shipto'];
            $orderid = $order['ID'];
            foreach ($personplace as $id => $pp) {
                $sql = 'SELECT * FROM `ch_personplace` WHERE `id` = "'.$pp.'"';
                $query2 = mysql_query($sql);
                checkDBerror($sql);
                $ppr = mysql_fetch_assoc($query2);
                $sql = 'INSERT INTO `snapshot_users` (`id`, `orig_id`, `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `secondary`) VALUES (NULL, NULL,';
                $sql .= ' "'.mysql_escape_string($ppr['companyName']).'",';
                $sql .= '"'.mysql_escape_string($ppr['name1']).'"';
                $addy = $ppr['address1'];
                if ($ppr['address2']) $addy .= "\n ".$ppr['address2'];
                $sql .= ',"'.mysql_escape_string($addy).'", ';
                $sql .= '
				 "'.mysql_escape_string($ppr['city']).'",
				 "'.mysql_escape_string($ppr['state']).'",
				 "'.mysql_escape_string($ppr['postalCode']).'",
				 "'.mysql_escape_string($ppr['dayPhone']).'",
				 "", "N");';
                mysql_query($sql);
                checkDBerror($sql);
                $newid = mysql_insert_id();
                $sql = "UPDATE `ch_personplace` SET `snapshot` = '".$newid."'";
                mysql_query($sql);
                checkDBerror($sql);
                $personplace[$id] = $newid;
            }
            $sql = 'UPDATE `order_forms` SET `customer` = "'.$personplace['billto'].'", `shipto` = "'.$personplace['shipto'].'", `nobolmerge` = 1 WHERE `ID` = "'.$orderid.'"';
            mysql_query($sql);
            checkDBerror($sql);
        }
    }
}

if ($dboldversion < "2008032913000") {
    $sql = "ALTER TABLE `snapshot_items` ADD `sku` VARCHAR( 50 ) NOT NULL AFTER `discount` ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `snapshot_items` SET `sku` = (select `form_items`.sku from `form_items` WHERE `snapshot_items`.`orig_id` = `form_items`.`ID`) WHERE `snapshot_items`.`orig_id` != 0;";
    mysql_query($sql); // One hell of a massive update
    checkDBerror($sql);
    $sql = "ALTER TABLE `snapshot_users` ADD `address2` VARCHAR( 255 ) NOT NULL AFTER `address` ;";
    mysql_query($sql);
    checkDBerror($sql);

    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_snapshot_items` ADD `sku` VARCHAR( 50 ) NOT NULL AFTER `discount` ;";
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "UPDATE `MoS_snapshot_items` SET `sku` = (select `MoS_form_items`.sku from `MoS_form_items` WHERE `MoS_snapshot_items`.`orig_id` = `MoS_form_items`.`ID`) WHERE `MoS_snapshot_items`.`orig_id` != 0;";
        mysql_query($sql); // One hell of a massive update
        checkDBerror($sql);
    }

    if (!$MoS_enabled) {
        $sql = 'ALTER TABLE `ch_order` ADD `servicelevel` VARCHAR( 50 ) NOT NULL AFTER `merchantpo` ;';
        mysql_query($sql);
        checkDBerror($sql);

        $sql = 'CREATE TABLE `ch_bolqueue` ('
            . ' `bol_id` INT NOT NULL, '
            . ' `processed` SMALLINT NOT NULL,'
            . ' PRIMARY KEY (`bol_id`)'
            . ' )'
            . ' ENGINE = myisam;';
        mysql_query($sql);
        checkDBerror($sql);

        $sql = 'CREATE TABLE `ch_canceled` ('
            . ' `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
            . ' `po` INT NOT NULL, '
            . ' `order_id` INT NOT NULL, '
            . ' `qty` INT NOT NULL, '
            . ' `reason` VARCHAR(50) NOT NULL, '
            . ' `processed` SMALLINT NOT NULL,'
            . ' INDEX (`po`)'
            . ' )'
            . ' ENGINE = myisam;';
        mysql_query($sql);
        checkDBerror($sql);

        $sql = 'TRUNCATE TABLE `ch_personplace` ';
        mysql_query($sql);
        checkDBerror($sql);

        $sql = 'SELECT `ID` FROM `order_forms` WHERE `ID` IN (SELECT `po` FROM `ch_order`)';
        $query = mysql_query($sql);
        checkDBerror($sql);

        while ($row = mysql_fetch_assoc($query)) {
            $sql = 'DELETE FROM `BoL_queue` WHERE `po` = "'.$row['ID'].'"';
            mysql_query($sql);
            checkDBerror($sql);

            $sql = 'SELECT `ID` FROM `BoL_forms` WHERE `po` = "'.$row['ID'].'"';
            $query2 = mysql_query($sql);
            checkDBerror($sql);

            while ($row2 = mysql_fetch_assoc($query2)) {
                $sql = 'DELETE FROM `BoL_forms` WHERE `ID` = "'.$row2['ID'].'"';
                mysql_query($sql);
                checkDBerror($sql);
                $sql = 'DELETE FROM `BoL_items` WHERE `bol_id` = "'.$row2['ID'].'"';
                mysql_query($sql);
                checkDBerror($sql);
            }

            $sql = 'DELETE FROM `order_forms` WHERE `ID` = "'.$row['ID'].'"';
            mysql_query($sql);
            checkDBerror($sql);
        }

        $sql = 'TRUNCATE TABLE `ch_order` ';
        mysql_query($sql);
        checkDBerror($sql);
    }
}

if ($dboldversion < "2008032914450") {
    $sql = "ALTER TABLE `BoL_forms` ADD `servicelevel` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_bin NULL AFTER `csv_exported`";
    checkdberror($sql);
    mysql_query($sql);
}

if ($dboldversion < "2008040320300") {
    $sql = "CREATE TABLE `ch_shipcodes` (
`shipcode` VARCHAR( 8 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,
`description` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,
`selectable` BOOL NOT NULL DEFAULT '0',
PRIMARY KEY ( `shipcode` ) ,
INDEX ( `description` )
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'CommerceHub Ship Codes';";
    checkdberror($sql);
    mysql_query($sql);
    $sql = "INSERT INTO `ch_shipcodes` (shipcode, description, selectable) VALUES ('PYLR','A. Duie Pyle Inc.',0),('PYLR_SE','A. Duie Pyle Inc. - 2nd Day',0),('PYLR_ND','A. Duie Pyle Inc. - Next Day',0),('PYLR_DS','A. Duie Pyle Inc. - Threshold Service',0),('ABFS','ABF',0),('ABFS_SE','ABF - 2nd Day',0),('ABFS_G2','ABF - Basic Service',0),('ACFS','AC Freight Systems',0),('ACFS_G2','AC Freight Systems - Basic Service',0),('AERO_G2','Aeronet - Basic Service',0),('AIRB_ND','Airborne Freight Corp - Overnight Express',0),('AITW_G2','AIT Worldwide Logistics - Basic Service',0),('AITW_09','AIT Worldwide Logistics - Delivery and Install',0),('AITW_DS','AIT Worldwide Logistics - Threshold Service',0),('AFMI','Alto Freight',0),('ARFW','American Freightways',0),('ARFW_SE','American Freightways - 2nd Day',0),('AEWS_CG','American West - Ground',0),('AAUE','Andlauer Transportation Services',0),('AMEX','Apex Motor Express Ltd.',0),('AVRT','Averitt Express',0),('BXGL_G2','Bax Global, Inc.',0),('BXGL','Bax Global, Inc.',1),('BNAF','Bax Global, Inc.',1),('BXGL_DS','Bax Global, Inc. - Threshold Service',0),('BTVP','Best Overnite',0),('BEST','Bestar',0),('BTGD','Bullet Trucking, Inc.',0),('RBTW','C.H. Robinson Worldwide Inc.',0),('RBTW_SE','C.H. Robinson Worldwide Inc. - 2nd Day',0),('CAOE','California Overnight',0),('CAOE_SE','California Overnight - 2nd Day',0),('CAOE_ND','California Overnight - Next Day',0),('CG_SE','Canada Post, 2nd day',0),('CG','Canada Post, ground',0),('CFWL_G2','Canadian Freightways - Basic Service',0),('CNPL','Canpar',0),('CNPL_SE','Canpar - 2nd Day',0),('CGVO','CCT Logistics',0),('CENF','Central Freight Lines',0),('CENT','Central Freight Lines',1),('CETR','Central Transport Inc.',0),('CTII', 'Central Transport Inc.',1),('CTQH_G2','Champion Logistics - Basic Service',0),('CTQH_09','Champion Logistics - Delivery and Install',0),('CTQH_DS','Champion Logistics - Threshold Service',0),('CLKE','Clarke Transport',0),('CEGW','Continental Freightways',0),('CEGW_SE','Continental Freightways - 2nd Day',0),('CWCE','Conway',0),('CCX','Conway',1),('CWCE_SE','Conway - 2nd Day',0),('CLMM','Cornerstone Shipping Solutions',0),('CGLY_DS','Custom Global Logistics - Threshold Service',0),('DTST','DATS Trucking Inc.',0),('DAYR','Day & Ross)',0),('DYLT','Daylight Transport',0),('DAFG','Dayton Freight',0),('DPHE','Dependable Highway Express',0),('DHL_SE','DHL Shipping - 2nd Day Air',0),('DHL_CG','DHL Shipping - Ground',0),('DHL_ND','DHL Shipping - Next Day Air',0),('RDMD','Diamond Delivery',0),('DYN','Dynamex',0),('EGLE_G2','Eagle Freight - Basic Service',0),('EGLE_09','Eagle Freight - Delivery and Install',0),('EAGL_DS','Eagle Freight - Threshold Service',0),('EPCE','Epic Express',0),('EXLA','Estes Express Lines',0),('EXLA_CG','Estes Express Lines',1),('EXLA_SE','Estes Express Lines - 2nd Day',0),('ELGS_G2','Exel Logistics - Basic Service',0),('ELGS_09','Exel Logistics - Delivery and Install',0),('EXDO','Expeditor\'s International of Washington',0),('EXNS','Express Transport',0),('EXNS_SE','Express Transport - 2nd Day',0),('FMTP_G2','Farmore Transportation Services - Basic Service',0),('FMTP_DS','Farmore Transportation Services -Threshold Service',0),('FASC','Fastfrate',0),('FSTK_G2','Fastrek - Basic Service',0),('FDLG_DS','Federal Logistics, Inc. - Threshold Service',0),('FEDX_SE','Fedex 2nd Day',0),('FDEG_SE','Fedex 2nd Day',1),('FEDF','FEDEX Freight',0),('FXFE','FEDEX Freight',1),('FDEN','FEDEX Freight',1),('FEDX_CG','Fedex Ground',0),('FDEG_CG','Fedex Ground',1),('FEDX_09','FEDEX Home Service',0),('FDE_IE','FEDEX International Economy 5 day',0),('FDE_SG','FEDEX International Ground 7 day',0),('FDE_IX','FEDEX International Priority 3 Day',0),('FEDX_ND','Fedex Next Day',0),('FDEG_ND','Fedex Next Day',1),('FEDX_3D','Fedex Three Day',0),('GCMP','Genwest',0),('GLLD','Glenncoe Transport',0),('GELJ','Globaltranz',0),('GFXP_DS','Ground Freight Expeditors - Threshold Service',0),('POLN','Hellman Worldwide Logistics',0),('HDIR_G2','HEP Direct - Basic Service',0),('HDIR_09','HEP Direct - Delivery and Install',0),('HDIR_DS','HEP Direct - Threshold Service',0),('HWDQ_G2','Hollywood Delivery Services - Basic Service',0),('BEKN_G2','Home Direct USA - Basic Service',0),('BEKN_09','Home Direct USA - Premium (White Glove)',0),('BEKN_DS','Home Direct USA - Threshold Service',0),('INF','Infinity Spas private carrier',0),('KIDY','Kindersley Transport',0),('KIDY_G2','Kindersley Transport - Basic Service',0),('KKWQ','KKW Trucking, Inc.',0),('KKWQ_G2','KKW Trucking, Inc. - Basic Service',0),('LKVL','Lakeville Motor Express, Inc.',0),('LDSI_G2','Lodeso, Inc. - Basic Service',0),('LDSI_09','Lodeso, Inc. - Delivery and Install',0),('LDSI_DS','Lodeso, Inc. - Threshold Service',0),('LYAF_SE','Lynden Transport 2 Day Saver',0),('LYAF_G2','Lynden Transport Curbside Delivery',0),('LTIA_DS','Lynden Transport In Home Delivery or Threshold',0),('LYAF_ND','Lynden Transport Next Day Priority',0),('LTIA_09','Lynden White Glove',0),('MANI_G2','Manitoulin Transport - Basic Service',0),('MYSY_09','Manna Freight Systems - Delivery and Install',0),('MTVL_G2','Mountain Valley Express - Basic Service',0),('NPME_G2','New Penn Motor Express - Basic Service',0),('NORM','Normandin truck carrier',0),('OAKH_G2','Oak Harbor Freight Lines, Inc. - Basic Service',0),('ODFL','Old Dominion',0),('OTHR','Other',0),('OVNT','Overnite',0),('UPGF','Overnite',1),('PENS_G2','Peninsula Truck Lines - Basic Service',0),('PTSL_G2','Penner International - Basic Service',0),('PAAF','Pilot Air Freight',0),('PITD','PITT Ohio',0),('DPIT','PITT Ohio/Direct Ship',0),('PJXI_G2','Pjax, Inc. - Basic Service',0),('PLYC_09','Plycon Transportation - Delivery and Install',0),('PRLA_SE','Purolator Courier, 2nd day',0),('PRLA_CG','Purolator Courier, ground',0),('QKNE_ND','QRC Logistics Ltd. - Next Day',0),('QXTI','QuikX Transportation',0),('QXTI_G2','QuikX Transportation - Basic Service',0),('RNLO','R & L Carriers',0),('RNLO_SE','R & L Carriers - 2nd Day',0),('RNLO_G2','R & L Carriers - Basic Service',0),('RNLO_ND','R & L Carriers - Next Day',0),('RDFS','Road Runner',0),('RDWY','Roadway',0),('TRRB','Robert Transport',0),('SAIA','Saia Motor Freight',0),('SDCR_CG','Sameday Right-O-Way - Ground',0),('SEUE','Security Express',0),('SEKW','Seko',0),('SNDY','Select Daily',0),('SVBA','Service By Air',0),('SHF_09','Sharut Furniture - Delivery and Install',0),('SMTM_CG','Smart Mail (USPS Affiliate)',0),('SMTM','Smart Mail (USPS Affiliate)',1),('SEFL','Southeastern Freight Lines',0),('SEFL_G2','Southeastern Freight Lines - Basic Service',0),('SPCG','Specialized Transportation',0),('SPCG_09','Specialized Transportation - Delivery and Install',0),('SSDS_G2','Speedy Spa Delivery - Basic Service',0),('BUFM','Speedy Transport',0),('SUND_09','Sun Delivery, Inc. - Delivery and Install',0),('DELL_CG','Tandem Carriers - UPS and Purolator',0),('TGAF_G2','Target Logistics',0),('TGAF','Target Logistics',1),('TLI','Target Logistics',1),('TDAH_G2','Total Transportation Services-Basic Service',1),('TDAH_09','Total Transportation Services-Delivery and Install',0),('TDAH_DS','Total Transportation Services-Threshold',0),('TTLQ','TotalLine Transport',0),('TLIE_09','Trail Lines - Delivery and Install',0),('TWLP','Transwest',0),('PTQL_G2','TST Porter - Basic Service',0),('UPSN_SE','UPS 2nd Day Air',0),('UB','UPS 2nd Day Air',1),('UPSET_SE','UPS 2nd Day Air, Signature Required',0),('UY','UPS 2nd Day Air, Signature Required',1),('UPSN_3D','UPS 3 Day Select',0),('UPGS','UPS Freight (service level unspecified)',0),('UPSN_CG','UPS Ground',0),('UG','UPS Ground',1),('UPSET_CG','UPS Ground, Signature Required',0),('UX','UPS Ground, Signature Required',1),('UPSN_ND','UPS Next Day Air',0),('UPND','UPS Next Day Air',1),('UR','UPS Next Day Air',1),('UPSET_ND','UPS Next Day Air, Signature Required',0),('UZ','UPS Next Day Air, Signature Required',1),('HMES','USF Holland',0),('RETL','USF Reddaway',0),('USPS_ND','USPS Express Mail',0),('USPSB_FC','USPS First Class Mail',0),('USPS_BC','USPS Package Services (Parcel Post)',0),('USPS_PB','USPS Priority Mail',0),('VKMF_G2','Van Kam Freightways - Basic Service',0),('VDSL_G2','Vast Trucking - Basic Service',0),('VDSL_09','Vast Trucking - Delivery and Install',0),('VDSL_DS','Vast Trucking - Threshold Service',0),('VSXP','Vision Express',0),('VSXP_G2','Vision Express - Basic Service',0),('VITY','Vitran Canada',0),('VITY_G2','Vitran Canada - Basic Service',0),('VITR','Vitran Express',0),('VOLT','Volunteer Express',0),('WWAT','Watkins',0),('WKSH_G2','Watkins Shepard Trucking, Inc. - Basic Service',0),('WKSH_DS','Watkins Shepard Trucking, Inc. - Threshold Service',0),('WGLS','Western Logistics',0),('WTVA_09','Wilson Trucking -  Delivery and Install',0),('WTVA_G2','Wilson Trucking - Basic Service',0),('WTVA_DS','Wilson Trucking - Threshold Service',0),('CEMH_09','World Wide Delivery Center - Delivery and Install',0),('XGSI_G2','Xpress Global Systems - Basic Service',0),('YFSY','Yellow',0),('DYEL','Yellow / Direct Ship=',0)";
    checkdberror($sql);
    mysql_query($sql);

}

if ($dboldversion < "2008051410100") {
    $sql = "ALTER TABLE `snapshot_users` ADD `email` VARCHAR( 255 ) NOT NULL AFTER `fax` ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `snapshot_users` SET `email` = (select `ch_personplace`.`email` from `ch_personplace` WHERE `snapshot_users`.`id` = `ch_personplace`.`snapshot`) WHERE `snapshot_users`.`orig_id` IS NULL;";
    mysql_query($sql); // One hell of a massive update
    checkDBerror($sql);
}

if ($dboldversion < "2008052122000") {
    $sql = "ALTER TABLE `fieldvisit` CHANGE `ph_comments` `ph_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `dr_comments` `dr_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `ad_addl` `ad_addl` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `ad_comments` `ad_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `compete_comments` `compete_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `bedding_lineup` `bedding_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `premium_bedding_lineup` `premium_bedding_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `bedroom_sets_lineup` `bedroom_sets_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `dinette_sets_lineup` `dinette_sets_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `formal_dining_lineup` `formal_dining_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `microfiber_lineup` `microfiber_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `leather_sets_lineup` `leather_sets_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `chenille_lineup` `chenille_lineup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `merch_comments` `merch_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `pricesheets_comments` `pricesheets_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `sales_comments` `sales_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `ops_comments` `ops_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `org_dev_comments` `org_dev_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `dealer_todo` `dealer_todo` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `addl_comments` `addl_comments` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `files` `files` TEXT CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL, CHANGE `writeup` `writeup` TEXT CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
    checkdberror($sql);
    mysql_query($sql);
}

if ($dboldversion < "2008052311000") {
    $sql = "ALTER TABLE `salestats` ADD `bedding_internet_calls` INT NOT NULL DEFAULT '0' AFTER `bedding_signs_profit` ,
				ADD `bedding_internet_appts` INT NOT NULL DEFAULT '0' AFTER `bedding_internet_calls` ,
				ADD `bedding_internet_show` INT NOT NULL DEFAULT '0' AFTER `bedding_internet_appts` ,
				ADD `bedding_internet_sold` INT NOT NULL DEFAULT '0' AFTER `bedding_internet_show` ,
				ADD `bedding_internet_retail` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00' AFTER `bedding_internet_sold` ,
				ADD `bedding_internet_profit` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00' AFTER `bedding_internet_retail`,
				ADD `cg_internet_calls` INT NOT NULL DEFAULT '0' AFTER `cg_signs_profit` ,
				ADD `cg_internet_appts` INT NOT NULL DEFAULT '0' AFTER `cg_internet_calls` ,
				ADD `cg_internet_show` INT NOT NULL DEFAULT '0' AFTER `cg_internet_appts` ,
				ADD `cg_internet_sold` INT NOT NULL DEFAULT '0' AFTER `cg_internet_show` ,
				ADD `cg_internet_retail` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00' AFTER `cg_internet_sold` ,
				ADD `cg_internet_profit` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00' AFTER `cg_internet_retail` ;";
    checkdberror($sql);
    mysql_query($sql);
}

if ($dboldversion < "2008061314300") {
// adding field visit changelog table
    $sql = "CREATE TABLE `fieldvisit_changelog` (
`changeid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`visitid` INT UNSIGNED NOT NULL ,
`editorid` INT UNSIGNED NOT NULL ,
`timestamp` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`changes` TEXT CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,
PRIMARY KEY ( `changeid` )
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Field Visits Change Log';";
    checkdberror($sql);
    mysql_query($sql);
}

if ($dboldversion < "2008062612000") {
// adding shipping agent table
    $sql = "CREATE TABLE `shipping_agents` (
`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`snapshot_userid` INT UNSIGNED NOT NULL
) ENGINE = MYISAM COMMENT = 'Shipping System: Shipping Agents';";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008070822300") {
    if ($MoS_enabled) {
        $sql = "ALTER TABLE  `MoS_order_forms` ADD INDEX  `snapshot_id` (  `snapshot_user` );";
    } else { // MoS not enabled!
        $sql = "ALTER TABLE  `order_forms` ADD INDEX  `snapshot_id` (  `snapshot_user` );";
        mysql_query($sql);
        checkdberror($sql);
        $sql = "ALTER TABLE `BoL_forms` ADD INDEX `po` ( `po` ) ;";
        mysql_query($sql);
        checkdberror($sql);
    }
}

if($dboldversion < "2008072420300" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_items` ADD INDEX `bol_id` (`bol_id`);";
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008081315350") {
    $sql = 'ALTER TABLE `users` ADD `wodslist` CHAR( 1 ) NOT NULL DEFAULT "N" AFTER `homelist3` ,
				ADD `wodslist2` CHAR( 1 ) NOT NULL DEFAULT "N" AFTER `wodslist` ,
				ADD `wodslist3` CHAR( 1 ) NOT NULL DEFAULT "N" AFTER `wodslist2` ;';
    mysql_query($sql);
    checkdberror($sql);
    $sql = 'UPDATE `users` SET `wodslist` = `dealerlist`, `wodslist2` = `dealerlist2`, `wodslist3` = `dealerlist3` WHERE `wodsable` = "Y"';
    mysql_query($sql);
    checkdberror($sql);
}

if ($dboldversion < "2008081915000") {
    if(!$MoS_enabled) {
        $sql = "ALTER TABLE `BoL_forms` ADD `chcsv_exported` DATETIME NULL AFTER `csv_exported` ;";
        mysql_query($sql);
        checkdberror($sql);
        $sql = "ALTER TABLE `order_forms` ADD `chcsv_exported` DATETIME NULL AFTER `csv_exported` ;";
        mysql_query($sql);
        checkdberror($sql);
    }
}

if ($dboldversion < "2008082808250" && !$MoS_enabled) {
    $sql = "ALTER TABLE `claim_bedding` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_furniture` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_order` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_parts` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_refused` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_shortage` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `claim_test` ADD `upsincesms` INT( 1 ) NOT NULL DEFAULT '0' AFTER `status`";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008091610150" && !$MoS_enabled) {
    $sql = "CREATE TABLE `shipping_carriers` (
`name` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Shipping Carriers';";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "INSERT INTO `shipping_carriers` ( `name` )
VALUES ('Addison'), ('BAX Global'), ('DHL'), ('Eagle Logistics'), ('Fed Ex Freight'), ('Fed Ex Ground'), ('HLS'), ('SAIA'), ('SMT'), ('UPS Freight'), ('UPS Ground');";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008091914000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `shipping_carriers` ADD `shortname` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `shipping_carriers` ADD PRIMARY KEY ( `name` );";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `shipping_carriers` SET `shortname` = 'Eagle' WHERE CONVERT( `shipping_carriers`.`name` USING utf8 ) = 'Eagle Logistics' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `shipping_carriers` SET `shortname` = 'FEDEX Freight' WHERE CONVERT( `shipping_carriers`.`name` USING utf8 ) = 'Fed Ex Freight' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `shipping_carriers` SET `shortname` = 'Fedex Ground' WHERE CONVERT( `shipping_carriers`.`name` USING utf8 ) = 'Fed Ex Ground' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `shipping_carriers` SET `shortname` = 'Saia' WHERE CONVERT( `shipping_carriers`.`name` USING utf8 ) = 'SAIA' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `shipping_carriers` SET `shortname` = 'Bax Global' WHERE CONVERT( `shipping_carriers`.`name` USING utf8 ) = 'BAX Global' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008100921300" && !$MoS_enabled) {
    $sql = "INSERT INTO `shipping_carriers` ( `name` , `shortname` ) VALUES ('Home Direct', 'Home Direct');";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'BEKN_09' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008101006110" && !$MoS_enabled) {
    $sql = "ALTER TABLE `ch_order` ADD `orderid` INT NOT NULL DEFAULT '0' AFTER `merchantpo`";
    mysql_query($sql);
    checkdberror($sql);

    $sql = "SELECT `ch_order`.`id`, TRIM(LEADING '#' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(`order_forms`.`comments`,' ',5),' ',-1),'\n',1)) AS `order_id` FROM `ch_order` INNER JOIN `order_forms` ON `order_forms`.`ID` = `ch_order`.`po`";
    $query = mysql_query($sql);
    checkdberror($sql);
    while ($result = mysql_fetch_assoc($query)) {
        $sql = "UPDATE `ch_order` SET `orderid` = '".mysql_escape_string($result['order_id'])."' WHERE `id` = ".$result['id'];
        mysql_query($sql);
        checkdberror($sql);
    }
}

if($dboldversion < "2008111815450" && !$MoS_enabled) {
    $sql = "ALTER TABLE `BoL_queue` ADD `picktix_printed` BOOL NOT NULL DEFAULT '0' AFTER `totalbox` , ADD `ptlabel_printed` BOOL NOT NULL DEFAULT '0' AFTER `picktix_printed` ;";
    $query = mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `BoL_forms` ADD `oor_updated` BOOL NOT NULL DEFAULT '0' AFTER `adminprinted` ;";
    $query = mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008112115300" && !$MoS_enabled) {
// lots of updates
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'BXGL' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'BNAF' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CENF' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CENT' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CTII' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CCX' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'EXLA_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FDEG_SE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FXFE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FDEN' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FDEG_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FDEG_ND' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `description` = 'Lynden Transport Threshold Service' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'LTIA_DS' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `description` = 'Seko Standard' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'SEKW' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'SMTM' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'TGAF' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'TLI' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UB' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UY' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UX' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPND' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UR' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '0' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UZ' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `description` = 'Yellow / Direct Ship' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'DYEL' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `description` = 'Day & Ross' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'DAYR' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'BXGL_G2' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CETR' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'CWCE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'EXLA' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FEDX_SE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FEDF' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FEDX_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'FEDX_ND' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'SMTM_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'TGAF_G2' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSN_SE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSET_SE' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSN_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSET_CG' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSN_ND' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPSET_ND' LIMIT 1 ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "INSERT INTO `ch_shipcodes` ( `shipcode` , `description` , `selectable` ) VALUES ('BGLH_09', 'Banfield Group - Delivery and Install', '0'), ('CEVA_G2', 'CEVA Logistics - Basic Service', '0'),('CEVA_09','CEVA Logistics - Delivery and Install',0),('CEVA_DS','CEVA Logistics - Threshold Service',0),('SETP_G2','Direct Shippers - Basic Service',0),('DHRN','Dohrn Transfer Co.',0),('FXNL','Fedex National LTL',0),('GLDI','Glova-Link Distribution',0),('GLDI_G2','Glova-Link Distribution - Basic Service',0),('HAHE_G2','Harrah Industries - Basic Service',0),('HAHE_09','Harrah Industries - Delivery and Install',0),('HAHE_DS','Harrah Industries - Threshold Service',0),('LTL','LTL (service unspecified)',0),('LTL_G2','LTL Basic Service',0),('MFSY','Manna Distribution Services',0),('MYSY_DS','Manna Distribution Services - Threshold Service',0),('MFAF','Matheson Fast Freight Inc.',0),('MDLD','Midland Transport LTL Service',0),('NEBT_DS','Nebraska Trucking Company - Threshold',0),('NEMF_G2','New England Motor Freight - Basic Service',0),('PCLH','Platinum Cargo Logistics',0),('PCLH_09','Platinum Cargo Logistics - Delivery and Install',0),('PCLH_DS','Platinum Cargo Logistics - Threshold Service',0),('ATVL_G2','Primex Global Services - Basic Service',0),('PAXA','Priority Solutions International',0),('RPFW_CG','Rapid Freightways - Ground',0),('SDCR_DS','Sameday Right-O-Way - Threshold Service',0),('SEKW_09','Seko - Delivery, Install, Haul Away',0),('SEKW_DS','Seko - Threshold Service',0),('SHA','Shaw private carrier',0),('SMF_ND','Special Moments Flowers & Gifts Ltd.',0),('SPED','Spee Dee Delivery Service, Inc.',0),('TLIE_G2','Trail Lines - Basic Service',0),('UNSP','Unspecified',0),('UPSN_FC','UPS Mail Innovations - Standard',0),('WARD','WARD Trucking',0) ;";
    mysql_query($sql);
    checkdberror($sql);
}

if($dboldversion < "2008120215000") {
    $sql = "ALTER TABLE `form_items` ADD `weight` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft` ;";
    mysql_query($sql);
    checkdberror($sql);
    $sql = "ALTER TABLE `snapshot_items` ADD `weight` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft`;";
    mysql_query($sql);
    checkdberror($sql);
    if ($MoS_enabled) {
        $sql = "ALTER TABLE `MoS_form_items` ADD `weight` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft` ;";
        mysql_query($sql);
        checkdberror($sql);
        $sql = "ALTER TABLE `MoS_snapshot_items` ADD `weight` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `cubic_ft`;";
        mysql_query($sql);
        checkdberror($sql);
    }
}

if($dboldversion < "2008120215300" && !$MoS_enabled) {
    $sql = "SELECT idnum, url_prefix AS addy FROM claimscolumns WHERE url_prefix != '';";
    $que = mysql_query($sql);
    checkdberror($sql);
    while($res = mysql_fetch_assoc($que)) {
        if(substr($res['addy'],0,1)=="/") continue; // don't need to update if the addy is relative
        $newaddy = substr($res['addy'],strpos($res['addy'],'/',8));
        $sql2 = "UPDATE claimscolumns SET url_prefix = '$newaddy' WHERE idnum = '{$res['idnum']}';";
        mysql_query($sql2);
        checkdberror($sql2);
    }
}

if($dboldversion < "2008121015000" && !$MoS_enabled) {
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE CONVERT( `ch_shipcodes`.`shipcode` USING utf8 ) = 'UPGS' LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2008121016150" && !$MoS_enabled) {
// fixing the db from the above problems w/ shipcodes
// get the problem BOLs first
// query generated using phpMyAdmin
// hard-coded bad merchantpo's from email
    $sql = "SELECT BoL_forms.ID, ch_shipcodes.shipcode, COALESCE(shipping_carriers.shortname, shipping_carriers.name) AS carriername FROM (shipping_carriers LEFT OUTER JOIN ch_shipcodes ON UCASE(ch_shipcodes.description) LIKE CONCAT('%', UCASE(COALESCE(shipping_carriers.shortname, shipping_carriers.name)), '%') AND ch_shipcodes.selectable = 1) INNER JOIN BoL_forms ON UCASE(BoL_forms.carrier) LIKE CONCAT('%',UCASE(COALESCE(shipping_carriers.shortname, shipping_carriers.name)),'%') WHERE po IN (SELECT po FROM ch_order WHERE merchantpo IN ( 847003199555, 847003217847, 847003223795, 847003234112, 847003234110, 847003270645, 847003287509, 847003291916, 847003017741, 847003025487, 847003046749, 847003046749, 847003056183, 847003056182, 847003082491, 847003090672, 847003128599, 847003144671, 847003146750, 847003148644, 847003165735, 847003173965, 847003177468, 847003188371, 847003197226, 847003140158, 847003177466, 847003195369, 847003216026, 847003217848, 847003225826, 847003295089, 847003297651, 847003297649, 847003300946, 847003313741, 847003316598, 847003332599, 847003335956, 847003335956, 847003338546, 847003342580, 847003023795, 847003026453, 847003146172, 847003295086, 847003297652, 847003297648, 847003303119, 847003322036, 847003328009 ));";
    $que = mysql_query($sql);
    checkDBerror($sql);
    while($result = mysql_fetch_assoc($que)) {
    // for this BOL, first we fix the servicelevel coding
        $sql2 = "UPDATE BoL_forms SET servicelevel = '".$result['shipcode']."' WHERE ID = '".$result['ID']."';";
        mysql_query($sql2);
        checkDBerror($sql2);
        // now reset the ch_bolqueue to process the record
        $sql2 = "UPDATE ch_bolqueue SET processed = 0 WHERE bol_id = '".$result['ID']."';";
        mysql_query($sql2);
        checkDBerror($sql2);
    // that should be it
    }
}

if($dboldversion < "2008121100000" && !$MoS_enabled) {
// cell provider changes
    $sql = "ALTER TABLE `users` ADD `cell_provider` CHAR( 3 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'oth' AFTER `cell_phone` ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `cell_providers` (
`code` CHAR( 3 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ,
`name` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_bin NULL ,
`email` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_bin NULL
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Cell Provider Information';";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `cell_providers` ( `code` , `name` , `email` ) VALUES ('ver', 'Verizon Wireless', 'vtext.com'),
('att', 'AT&T Wireless', 'txt.att.net'), ('tmo', 'T-Mobile', 'tmomail.net'), ('spr','Sprint Nextel','messaging.sprintpcs.com'),('qwe', 'Qwest', 'vtext.com'), ('all', 'AllTel', 'message.alltel.com'), ('usc', 'US Cellular', 'email.uscc.net'), ('oth', 'Other', NULL);";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2008121509000" && !$MoS_enabled) {
// add lookup datatype
    $sql = "ALTER TABLE `claimscolumns` CHANGE `datatype` `datatype` ENUM( 'text', 'number', 'date', 'upload', 'checkbox', 'select', 'lookup' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'text'";
    mysql_query($sql);
    checkDBerror($sql);
    // update the item datatype to lookup
    $sql = "UPDATE `claimscolumns` SET `datatype` = 'lookup' WHERE `claimscolumns`.`idnum` =5 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `datatype` = 'lookup' WHERE `claimscolumns`.`idnum` =101 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `claimscolumns` SET `datatype` = 'lookup' WHERE `claimscolumns`.`idnum` =16 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `shipping_carriers` (`name`, `shortname`) VALUES ('Yellow', 'Yellow'), ('Seko', 'Seko');";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2009042713000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `order_forms` ADD INDEX `user` ( `user` )";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `BoL_items` ADD `lineid` INT NULL DEFAULT NULL AFTER `item`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `iso_country_codes` (`number` CHAR( 3 ) NULL DEFAULT NULL ,
`name` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL ,`alpha3` CHAR( 3 ) NULL DEFAULT NULL) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'ISO Country Codes'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `iso_country_codes` ADD PRIMARY KEY ( `alpha3` ) ";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `iso_country_codes`  DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ROW_FORMAT = DYNAMIC";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO iso_country_codes (`number`, `name`, `alpha3`) VALUES (004,'Afghanistan','AFG'), (248,'�land Islands','ALA'), (008,'Albania','ALB'), (012,'Algeria','DZA'), (016,'American Samoa','ASM'), (020,'Andorra','AND'), (024,'Angola','AGO'), (660,'Anguilla','AIA'), (028,'Antigua and Barbuda','ATG'), (032,'Argentina','ARG'), (051,'Armenia','ARM'), (533,'Aruba','ABW'), (036,'Australia','AUS'), (040,'Austria','AUT'), (031,'Azerbaijan','AZE'), (044,'Bahamas','BHS'), (048,'Bahrain','BHR'), (050,'Bangladesh','BGD'), (052,'Barbados','BRB'), (112,'Belarus','BLR'), (056,'Belgium','BEL'), (084,'Belize','BLZ'), (204,'Benin','BEN'), (060,'Bermuda','BMU'), (064,'Bhutan','BTN'), (068,'Bolivia','BOL'), (070,'Bosnia and Herzegovina','BIH'), (072,'Botswana','BWA'), (076,'Brazil','BRA'), (092,'British Virgin Islands','VGB'), (096,'Brunei Darussalam','BRN'), (100,'Bulgaria','BGR'), (854,'Burkina Faso','BFA'), (108,'Burundi','BDI'), (116,'Cambodia','KHM'), (120,'Cameroon','CMR'), (124,'Canada','CAN'), (132,'Cape Verde','CPV'), (136,'Cayman Islands','CYM'), (140,'Central African Republic','CAF'), (148,'Chad','TCD'), (830,'Channel Islands',''), (152,'Chile','CHL'), (156,'China','CHN'), (344,'Hong Kong Special Administrative Region of China','HKG'), (446,'Macao Special Administrative Region of China','MAC'), (170,'Colombia','COL'), (174,'Comoros','COM'), (178,'Congo','COG'), (184,'Cook Islands','COK'), (188,'Costa Rica','CRI'), (384,\"C�te d'Ivoire\",'CIV'), (191,'Croatia','HRV'), (192,'Cuba','CUB'), (196,'Cyprus','CYP'), (203,'Czech Republic','CZE'), (408,\"Democratic People's Republic of Korea\",'PRK'), (180,'Democratic Republic of the Congo','COD'), (208,'Denmark','DNK'), (262,'Djibouti','DJI'), (212,'Dominica','DMA'), (214,'Dominican Republic','DOM'), (218,'Ecuador','ECU'), (818,'Egypt','EGY'), (222,'El Salvador','SLV'), (226,'Equatorial Guinea','GNQ'), (232,'Eritrea','ERI'), (233,'Estonia','EST'), (231,'Ethiopia','ETH'), (234,'Faeroe Islands','FRO'), (238,'Falkland Islands (Malvinas)','FLK'), (242,'Fiji','FJI'), (246,'Finland','FIN'), (250,'France','FRA'), (254,'French Guiana','GUF'), (258,'French Polynesia','PYF'), (266,'Gabon','GAB'), (270,'Gambia','GMB'), (268,'Georgia','GEO'), (276,'Germany','DEU'), (288,'Ghana','GHA'), (292,'Gibraltar','GIB'), (300,'Greece','GRC'), (304,'Greenland','GRL'), (308,'Grenada','GRD'), (312,'Guadeloupe','GLP'), (316,'Guam','GUM'), (320,'Guatemala','GTM'), (831,'Guernsey','GGY'), (324,'Guinea','GIN'), (624,'Guinea-Bissau','GNB'), (328,'Guyana','GUY'), (332,'Haiti','HTI'), (336,'Holy See','VAT'), (340,'Honduras','HND'), (348,'Hungary','HUN'), (352,'Iceland','ISL'), (356,'India','IND'), (360,'Indonesia','IDN'), (364,'Iran, Islamic Republic of','IRN'), (368,'Iraq','IRQ'), (372,'Ireland','IRL'), (833,'Isle of Man','IMN'), (376,'Israel','ISR'), (380,'Italy','ITA'), (388,'Jamaica','JAM'), (392,'Japan','JPN'), (832,'Jersey','JEY'), (400,'Jordan','JOR'), (398,'Kazakhstan','KAZ'), (404,'Kenya','KEN'), (296,'Kiribati','KIR'), (414,'Kuwait','KWT'), (417,'Kyrgyzstan','KGZ'), (418,\"Lao People's Democratic Republic\",'LAO'), (428,'Latvia','LVA'), (422,'Lebanon','LBN'), (426,'Lesotho','LSO'), (430,'Liberia','LBR'), (434,'Libyan Arab Jamahiriya','LBY'), (438,'Liechtenstein','LIE'), (440,'Lithuania','LTU'), (442,'Luxembourg','LUX'), (450,'Madagascar','MDG'), (454,'Malawi','MWI'), (458,'Malaysia','MYS'), (462,'Maldives','MDV'), (466,'Mali','MLI'), (470,'Malta','MLT'), (584,'Marshall Islands','MHL'), (474,'Martinique','MTQ'), (478,'Mauritania','MRT'), (480,'Mauritius','MUS'), (175,'Mayotte','MYT'), (484,'Mexico','MEX'), (583,'Micronesia, Federated States of','FSM'), (492,'Monaco','MCO'), (496,'Mongolia','MNG'), (499,'Montenegro','MNE'), (500,'Montserrat','MSR'), (504,'Morocco','MAR'), (508,'Mozambique','MOZ'), (104,'Myanmar','MMR'), (516,'Namibia','NAM'), (520,'Nauru','NRU'), (524,'Nepal','NPL'), (528,'Netherlands','NLD'), (530,'Netherlands Antilles','ANT'), (540,'New Caledonia','NCL'), (554,'New Zealand','NZL'), (558,'Nicaragua','NIC'), (562,'Niger','NER'), (566,'Nigeria','NGA'), (570,'Niue','NIU'), (574,'Norfolk Island','NFK'), (580,'Northern Mariana Islands','MNP'), (578,'Norway','NOR'), (275,'Occupied Palestinian Territory','PSE'), (512,'Oman','OMN'), (586,'Pakistan','PAK'), (585,'Palau','PLW'), (591,'Panama','PAN'), (598,'Papua New Guinea','PNG'), (600,'Paraguay','PRY'), (604,'Peru','PER'), (608,'Philippines','PHL'), (612,'Pitcairn','PCN'), (616,'Poland','POL'), (620,'Portugal','PRT'), (630,'Puerto Rico','PRI'), (634,'Qatar','QAT'), (410,'Republic of Korea','KOR'), (498,'Republic of Moldova','MDA'), (638,'R�union','REU'), (642,'Romania','ROU'), (643,'Russian Federation','RUS'), (646,'Rwanda','RWA'), (652,'Saint-Barth�lemy','BLM'), (654,'Saint Helena','SHN'), (659,'Saint Kitts and Nevis','KNA'), (662,'Saint Lucia','LCA'), (663,'Saint-Martin (French part)','MAF'), (666,'Saint Pierre and Miquelon','SPM'), (670,'Saint Vincent and the Grenadines','VCT'), (882,'Samoa','WSM'), (674,'San Marino','SMR'), (678,'Sao Tome and Principe','STP'), (682,'Saudi Arabia','SAU'), (686,'Senegal','SEN'), (688,'Serbia','SRB'), (690,'Seychelles','SYC'), (694,'Sierra Leone','SLE'), (702,'Singapore','SGP'), (703,'Slovakia','SVK'), (705,'Slovenia','SVN'), (090,'Solomon Islands','SLB'), (706,'Somalia','SOM'), (710,'South Africa','ZAF'), (724,'Spain','ESP'), (144,'Sri Lanka','LKA'), (736,'Sudan','SDN'), (740,'Suriname','SUR'), (744,'Svalbard and Jan Mayen Islands','SJM'), (748,'Swaziland','SWZ'), (752,'Sweden','SWE'), (756,'Switzerland','CHE'), (760,'Syrian Arab Republic','SYR'), (762,'Tajikistan','TJK'), (764,'Thailand','THA'), (807,'The former Yugoslav Republic of Macedonia','MKD'), (626,'Timor-Leste','TLS'), (768,'Togo','TGO'), (772,'Tokelau','TKL'), (776,'Tonga','TON'), (780,'Trinidad and Tobago','TTO'), (788,'Tunisia','TUN'), (792,'Turkey','TUR'), (795,'Turkmenistan','TKM'), (796,'Turks and Caicos Islands','TCA'), (798,'Tuvalu','TUV'), (800,'Uganda','UGA'), (804,'Ukraine','UKR'), (784,'United Arab Emirates','ARE'), (826,'United Kingdom of Great Britain and Northern Ireland','GBR'), (834,'United Republic of Tanzania','TZA'), (840,'United States of America','USA'), (850,'United States Virgin Islands','VIR'), (858,'Uruguay','URY'), (860,'Uzbekistan','UZB'), (548,'Vanuatu','VUT'), (862,'Venezuela (Bolivarian Republic of)','VEN'), (704,'Viet Nam','VNM'), (876,'Wallis and Futuna Islands','WLF'), (732,'Western Sahara','ESH'), (887,'Yemen','YEM'), (894,'Zambia','ZMB'), (716,'Zimbabwe','ZWE')";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `wm_shipcodes` (`code` CHAR( 3 ) NULL DEFAULT NULL, `name` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Walmart Ship Codes'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `wm_shipcodes` ADD PRIMARY KEY ( `code` )";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `wm_shipcodes` (`code`, `name`) VALUES (01,'USPS 3rd Class'), (02,'UPS Ground'), (03,'Clark Transport'), (04,'Yellow Freight System'), (05,'USPS Express Mail'), (07,'USPS 4th Inter'), (08,'UPS Basic'), (09,'UPS Second day Air'), (10,'USPS First Class'), (12,'Silver Star'), (13,'UPS Second Day Ground'), (14,'Consolidation'), (16,'UPS Next Day Air'), (17,'Common Carrier'), (19,'FedEx Express Saver (3 Day Service)'), (20,'FedEx Ground'), (21,'FedEx Priority One day'), (22,'FedEx Priority Two Day'), (23,'FedEx Priority Overnight'), (24,'FedEx Standard Overnight (PM Delivery)'), (26,'UPS Next Day Air Saver'), (29,'UPS 3 Day Select'), (30,'USPS Priority Mail'), (31,'USPS Priority Mail'), (32,'OLD Dominion Freight Line LTL'), (33,'Airborne Next Afternoon (PM Delivery)'), (34,'Airborne Ground Delivery'), (35,'Airborne Express'), (36,'Airborne'), (37,'Airborne 2-DAY'), (38,'UPS Ground'), (41,'ABF Freight System'), (42,'Bekins'), (43,'Pilot Freight Basic Delivery'), (44,'FedEx Priority Overnight w/Saturday Delivery'), (45,'UPS Next Day Air Saver (Saturday Delivery)'), (46,'SmartMail'), (47,'USPS Media Mail'), (48,'USPS Bound Printed Matter'), (50,'Bekins Premium'), (51,'USA Truck'), (52,'Contract Freighters, Inc'), (53,'Crete Carrier'), (54,'Eagle Global Logistics - Three Day Service'), (55,'Eagle Global Logistics - Economy'), (56,'US Xpress'), (57,'Heartland Express'), (58,'Dart Transit'), (61,'UPS Ground (CTN, GA)'), (62,'UPS Ground (Parsippany, NJ)'), (63,'UPS Ground (Chicago, IL)'), (64,'UPS Ground (Vernon, CA)'), (65,'FedEx SmartPost - BMC'), (66,'FedEx SmartPost - DDU'), (67,'FedEx Home Delivery'), (69,'UPS Mail Innovations BPM'), (70,'UPS Mail Innovations'), (71,'USPS'), (72,'Sun Delivery, Inc'), (73,'APX-DDU'), (74,'APX-BMC'), (77,'Direct to Store FedEx priority 1 day'), (78,'Direct to Store FedEx priority 2 day'), (79,'Direct to Store FedEx, Ground'), (80,'Direct to Store UPS, Ground'), (81,'In-Store delivery through WPM RDC'), (82,'Seko Worldwide'), (83,'DHL@home'), (84,'DHL@home(expedited)'), (85,'DHL 2nd Day'), (86,'DHL Next Day 03:00pm'), (87,'FTD Florist'), (88,'Direct to Store UPS Next Day Air '), (89,'1 Hour Burn on Demand'), (90,'Direct to Store - Freight'), (91,'In-Store delivery through Bypass WPM'), (92,'Direct to Store via DHL (Jewelry & Media)'), (95,'Fulfilled with Inventory from a Wal-Mart Store'), (96,'Wal_Mart Fleet for In-Store Pickup'), (97,'Direct to Store UPS, second Day Air'), (98,'Downloads'), (99,'In-Store Pickup'), (100,'DHL Next Day 12:00pm'), (101,'DHL Next Day w/Sat')";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `wm_edi` (`interchange_number` INT NOT NULL, `line_number` INT NOT NULL, `shipment_number` INT NOT NULL, `acknowledge_number` INT NOT NULL, `transaction_number` INT NOT NULL, `asn_sequence_number` INT NOT NULL) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin COMMENT = 'Walmart EDI Global Numbers'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "INSERT INTO `wm_edi` (`interchange_number`, `line_number`, `shipment_number`, `acknowledge_number`, `transaction_number`, `asn_sequence_number`) VALUES ('1', '1', '1', '1', '1', '1');";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `orders` ADD `po_lineid` INT NOT NULL AFTER `po_id`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `edi_files` (`filename` CHAR( 46 ) NOT NULL, `confirmed` BOOL NOT NULL DEFAULT '0', `processed` BOOL NOT NULL DEFAULT '0') ENGINE = MYISAM";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD `rejected` BOOL NOT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD `po_id` INT NOT NULL ";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `shipping_packages` (`ID` INT NOT NULL, `po` INT NOT NULL, `carrier_code` VARCHAR( 8 ) NOT NULL, `tracking_number` VARCHAR( 40 ) NOT NULL, `bar_code` CHAR( 20 ) NOT NULL, `weight` INT NOT NULL, `ship_date` DATE NOT NULL) ENGINE = MYISAM COMMENT = 'Shipping - Package Info'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `shipping_items` (`ID` INT NOT NULL AUTO_INCREMENT, `package_id` INT NOT NULL, `po_linenumber` INT NOT NULL, `qty` INT NOT NULL, `handling_cost` DECIMAL( 10, 2 ) NOT NULL, `giftwrap` BOOL NOT NULL, `giftwrap_cost` DECIMAL( 10, 2 ) NOT NULL, `gifttag` BOOL NOT NULL, `gifttag_cost` DECIMAL( 10, 2 ) NOT NULL, `giftmessage` VARCHAR( 200 ) NOT NULL, `giftmessage_cost` DECIMAL( 10, 2 ) NOT NULL, PRIMARY KEY ( `ID` )) ENGINE = MYISAM COMMENT = 'Shippping - Item Info'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` CHANGE `po` `bol` INT( 11 ) NOT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` ADD `freight` DECIMAL( 10, 2 ) NOT NULL AFTER `tracking_number`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_items` ADD `store_number` INT NOT NULL AFTER `po_linenumber`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` ADD PRIMARY KEY ( `ID` )";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` CHANGE `ID` `ID` INT( 11 ) NOT NULL AUTO_INCREMENT";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` ADD `store_number` INT NOT NULL AFTER `carrier_code`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `form_items` ADD `upc` CHAR( 13 ) NULL DEFAULT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD `retailer_po` VARCHAR( 20 ) NOT NULL AFTER `rejected`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD INDEX `retailer` ( `retailer_po` )";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD `sent` DATETIME NOT NULL AFTER `filename`, ADD `received` DATETIME NOT NULL AFTER `sent`, ADD `interchange` INT NOT NULL AFTER `received`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` ADD `ID` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY ( ID )";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `edi_groups` (`ID` INT NOT NULL AUTO_INCREMENT, `group` INT NOT NULL, `file_id` INT NOT NULL, PRIMARY KEY ( `ID` )) ENGINE = MYISAM COMMENT = 'EDI Group Number Table'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_groups` ADD `status` INT NOT NULL COMMENT '1=confirmed; -1=rejected'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE `edi_transactions` (`ID` INT NOT NULL AUTO_INCREMENT, `transaction` INT NOT NULL, `group_id` INT NOT NULL, `status` INT NOT NULL COMMENT '1=accepted; -1=rejected', PRIMARY KEY ( `ID` )) ENGINE = MYISAM COMMENT = 'EDI Transaction Number Table'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `upc` = '0081144301004' WHERE `form_items`.`ID` =82974 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `upc` = '0081144301003' WHERE `form_items`.`ID` =82975 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `upc` = '0081144301002' WHERE `form_items`.`ID` =82976 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `upc` = '0081144301001' WHERE `form_items`.`ID` =82977 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `upc` = '0081144301005' WHERE `form_items`.`ID` =82978 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` ADD `box_number` INT NOT NULL AFTER `bol`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_items` ADD `weight` INT NOT NULL AFTER `qty`";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` CHANGE `po_id` `po_id` VARCHAR( 20 ) NOT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE `ch_shipcodes`.`shipcode` = CAST( 0x53454b57 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE `ch_shipcodes`.`shipcode` = CAST( 0x53414941 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `ch_shipcodes` SET `selectable` = '1' WHERE `ch_shipcodes`.`shipcode` = CAST( 0x59465359 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `walmart_inventory` (
					`id` int(11) NOT NULL auto_increment,
					`formitems_id` int(11) default NULL COMMENT 'form_items ID',
					`item_id` int(11) NOT NULL COMMENT 'Walmart.com item number',
					`upc` char(13) NOT NULL COMMENT 'Walmart UPC',
					`sku` char(20) NOT NULL COMMENT 'PMD SKU',
					`avail_code` char(2) NOT NULL COMMENT 'Availability Code',
					`numavail` int(11) default NULL COMMENT 'Number Available',
					`mindaytoship` tinyint(4) default NULL COMMENT 'Minimum Days to Ship',
					`maxdaytoship` tinyint(4) default NULL COMMENT 'Maximum Days to Ship',
					`availstart` date default NULL COMMENT 'First Date Item Available',
					`availend` date default NULL COMMENT 'Last Date Item Available',
					`msrp` decimal(8,2) default NULL COMMENT 'MSRP',
					`retail` decimal(8,2) default NULL COMMENT 'Retail Price',
					`cost` decimal(8,2) default NULL COMMENT 'Cost to Walmart',
					`facility` char(20) NOT NULL default '' COMMENT 'Facility ID',
					`deletionssent` int(11) NOT NULL default '0' COMMENT 'Number of Inventory Sends Since Deleted',
					PRIMARY KEY  (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Walmart Inventory Information' AUTO_INCREMENT=6 ;
			";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2009042714000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `edi_files` ADD `archive_folder` CHAR( 6 ) NOT NULL AFTER `received`";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2009042715000" && !$MoS_enabled) {
    $sql = "INSERT INTO `walmart_inventory` (`id`, `formitems_id`, `item_id`, `upc`, `sku`, `avail_code`, `numavail`, `mindaytoship`, `maxdaytoship`, `availstart`, `availend`, `msrp`, `retail`, `cost`, `facility`, `deletionssent`) VALUES (1, 82977, 270533, '0081144301001', '270533', 'AC', 20, 2, 3, NULL, NULL, 94.00, 94.00, 94.00, 'TX', 0), (2, 82976, 270532, '0081144301002', '270532', 'AC', 20, 2, 3, NULL, NULL, 48.00, 48.00, 48.00, 'TX', 0), (3, 82975, 270531, '0081144301003', '270533', 'AC', 20, 2, 3, NULL, NULL, 25.00, 25.00, 25.00, 'TX', 0), (4, 82974, 270530, '0081144301004', '270530', 'AC', 20, NULL, NULL, NULL, NULL, 38.00, 38.00, 38.00, 'TX', 0), (5, 82978, 270543, '0081144301005', '270543', 'AC', 20, NULL, NULL, NULL, NULL, 97.50, 97.50, 97.50, 'TX', 0);";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2009042811000" && !$MoS_enabled) {
    $sql = "UPDATE `form_items` SET `price` = '38.00' WHERE `form_items`.`ID` =82974 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `price` = '25.00' WHERE `form_items`.`ID` =82975 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `price` = '48.00' WHERE `form_items`.`ID` =82976 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `price` = '94.00' WHERE `form_items`.`ID` =82977 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `form_items` SET `price` = '97.50' WHERE `form_items`.`ID` =82978 LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
}

if($dboldversion < "2009050716000" && !$MoS_enabled) {
    $sql = "ALTER TABLE `wm_shipcodes` ADD `freight` BOOL NOT NULL DEFAULT '0'";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x34 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3137 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3332 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3431 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3432 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3433 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3530 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3531 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3532 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "UPDATE `wm_shipcodes` SET `freight` = '1' WHERE `wm_shipcodes`.`code` = CAST( 0x3930 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `shipping_packages` CHANGE `tracking_number` `package_identifier` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    mysql_query($sql);
    checkDBerror($sql);
    $sql = "ALTER TABLE `edi_files` CHANGE `retailer_po` `retailer_po` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `po_id` `po_id` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009051421000" && !$MoS_enabled) {
    $sql = "INSERT INTO `wm_edi` (`interchange_number`, `line_number`, `shipment_number`, `acknowledge_number`, `transaction_number`, `asn_sequence_number`) VALUES ('1', '1', '1', '1', '1', '1');";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009051516300" && !$MoS_enabled) {
    $sql = "ALTER TABLE `edi_files` CHANGE `retailer_po` `retailer_po` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `po_id` `po_id` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009061521370" && !$MoS_enabled) {
    $sql = "ALTER TABLE `walmart_inventory` ADD `updated` INT( 1 ) NOT NULL DEFAULT '0' AFTER `deletionssent` ";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009070616300" && !$MoS_enabled) {
    $sql = "SELECT formitems_id as itemid, upc FROM walmart_inventory";
    $que = mysql_query($sql);
    checkDBerror($sql);
    while($ret = mysql_fetch_assoc($que)) {
        $sq = "UPDATE form_items SET upc = '".$ret['upc']."' WHERE ID = '".$ret['itemid']."'";
        $qu = mysql_query($sq);
        checkDBerror($sq);
    }
}

if ($dboldversion < "2009070816300" && !$MoS_enabled) {
    $sql = "ALTER TABLE `edi_files` CHANGE `retailer_po` `retailer_po` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `po_id` `po_id` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
    $que = mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009070901300" && !$MoS_enabled) {
    $sql = "CREATE TABLE `msrp_applied` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'msrp_applied ID', `orders_id` INT NOT NULL COMMENT 'orders table ID', `msrp` DECIMAL( 10, 2 ) NOT NULL COMMENT 'item MSRP') ENGINE = MYISAM CHARACTER SET ascii COLLATE ascii_bin COMMENT = 'MSRP for ordered items'";
    mysql_query($sql);
    checkDBerror($sql);
}

if ($dboldversion < "2009072819000" && !$MoS_enabled) {
    $sql = "INSERT INTO `wm_shipcodes` (`code`,`name`,`freight`) VALUES ('75','Ceva', '1');";
    mysql_query($sql);
    checkDBError($sql);

}

if ($dboldversion < "2009073002000" && !$MoS_enabled) {
    $sql = "CREATE TABLE `edi_vendor` (`vendor` CHAR( 20 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL ,`typecode` CHAR( 3 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL , `vendor_id` VARCHAR( 20 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL , `edi_id` CHAR( 15 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL , `edi_qualifier` CHAR( 2 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL , `testing` BOOL NOT NULL , `path` VARCHAR( 40 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
			) ENGINE = MYISAM CHARACTER SET ascii COLLATE ascii_bin COMMENT = 'EDI Vendor Data'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `edi_vendor` ADD UNIQUE `EDI_ID` ( `edi_id` , `edi_qualifier` ) ";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "INSERT INTO `edi_vendor` (`vendor`,`typecode`,`vendor_id`,`edi_id`,`edi_qualifier`,`testing`,`path`)
			VALUES ('pmd','','','829014849','01','0',''), ('walmart','WMI','45750','12571829','01','0','../../doc/as2/walmart/'), ('targettest','TVI','1452','078999252TGT','ZZ','1','../../doc/as2/target/'), ('target','TVI','1452','078999252TG','16','0','../../doc/as2/target/')";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `order_forms` ADD `retailer_orderdate` DATE NOT NULL AFTER `ordered`";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "CREATE TABLE `tv_edi` (`interchange_number` int( 11 ) NOT NULL , `line_number` int( 11 ) NOT NULL , `shipment_number` int( 11 ) NOT NULL , `acknowledge_number` int( 11 ) NOT NULL , `transaction_number` int( 11 ) NOT NULL ,
			`asn_sequence_number` int( 11 ) NOT NULL) ENGINE = MYISAM DEFAULT CHARSET = latin1 COLLATE = latin1_bin COMMENT = 'Target EDI Global Numbers';";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "INSERT INTO `tv_edi` (`interchange_number` , `line_number` , `shipment_number` , `acknowledge_number` , `transaction_number` , `asn_sequence_number`) VALUES ('1', '1', '1', '1', '1', '1');";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "INSERT INTO `forms` (name, vendor, oorvendor) VALUES ('Targettest', 369, 369)";
    mysql_query($sql);
    checkDBerror($sql);
    $formId = mysql_insert_id();
    $sql = "INSERT INTO `form_headers` (form, header, display_order) VALUES ('$formId', 'Target Product', '1')";
    mysql_query($sql);
    checkDBError($sql);
    $header = mysql_insert_id();
    $sql = "INSERT INTO `snapshot_headers` (orig_id, header) VALUES ('$header', 'Target Product')";
    mysql_query($sql);
    checkDBError($sql);
    $snapheader = mysql_insert_id();
    $sql = "INSERT INTO `snapshot_forms` (orig_id, orig_vendor, name) VALUES ('$formId', 369, 'Targettest')";
    mysql_query($sql);
    checkDBError($sql);
    $snapform = mysql_insert_id();
    $sql = "UPDATE `snapshot_headers` SET form = '$snapform' WHERE id = '$snapheader'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `forms` SET snapshot = '$snapform' WHERE ID = '$formId'";
    mysql_query($sql);
    checkDBError($sql);
    // insert targettest user
    $sql = "INSERT INTO users (last_name, first_name) VALUES ('Targettest', 'Targettest')";
    mysql_query($sql);
    checkDBError($sql);
    $userId = mysql_insert_id();
    $sql = "INSERT INTO snapshot_users (orig_id, first_name, last_name) VALUES ('$userId', 'Targettest', 'Targettest')";
    mysql_query($sql);
    checkDBError($sql);
    $snapuser = mysql_insert_id();
    $sql = "UPDATE users SET snapshot = '$snapuser' WHERE ID = '$userId'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "CREATE TABLE `wm_edi_groups` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `group` INT NOT NULL,
			`file_id` INT NOT NULL, `status` INT NOT NULL COMMENT '1 = accepted; -1 = rejected')
			ENGINE = MYISAM COMMENT = 'Walmart EDI Group Tracker Table'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "CREATE TABLE `tv_edi_groups` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `group` INT NOT NULL,
			`file_id` INT NOT NULL, `status` INT NOT NULL COMMENT '1 = accepted; -1 = rejected')
			ENGINE = MYISAM COMMENT = 'Target EDI Group Tracker Table'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "CREATE TABLE `wm_edi_transactions` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`transaction` INT NOT NULL, `group_id` INT NOT NULL, `status` INT NOT NULL COMMENT '1 = accepted; -1 = rejected')
			ENGINE = MYISAM COMMENT = 'Walmart EDI Transaction Tracker Table'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "CREATE TABLE `tv_edi_transactions` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`transaction` INT NOT NULL, `group_id` INT NOT NULL, `status` INT NOT NULL COMMENT '1 = accepted; -1 = rejected')
			ENGINE = MYISAM COMMENT = 'Target EDI Transaction Tracker Table'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `edi_vendor` ADD `send_997` BOOL NOT NULL DEFAULT '1',
			ADD `cancel_edi_type` CHAR( 3 ) NOT NULL DEFAULT '855'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `send_997` = '0',`cancel_edi_type` = '865' WHERE `edi_vendor`.`edi_id` = CAST( 0x303738393939323532544754 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x5a5a AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `send_997` = '0',`cancel_edi_type` = '865' WHERE `edi_vendor`.`edi_id` = CAST( 0x3037383939393235325447 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3136 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `edi_vendor` CHANGE `cancel_edi_type` `cancel_edi_type` CHAR( 2 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PR'";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `cancel_edi_type` = 'PR' WHERE `edi_vendor`.`edi_id` = CAST( 0x383239303134383439 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3031 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `cancel_edi_type` = 'PR' WHERE `edi_vendor`.`edi_id` = CAST( 0x3132353731383239 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3031 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `cancel_edi_type` = 'CA' WHERE `edi_vendor`.`edi_id` = CAST( 0x303738393939323532544754 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x5a5a AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `cancel_edi_type` = 'CA' WHERE `edi_vendor`.`edi_id` = CAST( 0x3037383939393235325447 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3136 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `tv_edi` ADD `inventory_number` INT NOT NULL";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `edi_vendor` ADD `warehouse_code` VARCHAR( 30 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL ";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `warehouse_code` = 'TGX4' WHERE `edi_vendor`.`edi_id` = CAST( 0x303738393939323532544754 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x5a5a AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `warehouse_code` = 'TGX4' WHERE `edi_vendor`.`edi_id` = CAST( 0x3037383939393235325447 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3136 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `shipping_packages` ADD `orig_carrier_code` VARCHAR( 30 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL AFTER `box_number`";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE shipping_packages SET orig_carrier_code = carrier_code";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "ALTER TABLE `edi_vendor` ADD `as2_sendername` VARCHAR( 20 ) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL AFTER `vendor_id`";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `as2_sendername` = 'Walmart' WHERE `edi_vendor`.`edi_id` = CAST( 0x3132353731383239 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3031 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `as2_sendername` = 'TargetTest' WHERE `edi_vendor`.`edi_id` = CAST( 0x303738393939323532544754 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x5a5a AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
    $sql = "UPDATE `edi_vendor` SET `as2_sendername` = 'Target' WHERE `edi_vendor`.`edi_id` = CAST( 0x3037383939393235325447 AS BINARY ) AND `edi_vendor`.`edi_qualifier` = CAST( 0x3136 AS BINARY ) LIMIT 1 ;";
    mysql_query($sql);
    checkDBError($sql);
}

?>
