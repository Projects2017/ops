<?php	
if ($dboldversion < "200512051800") {
         $sql = "CREATE TABLE `config` (
                   `id` int(11) NOT NULL auto_increment,
                   `name` varchar(50) NOT NULL default '',
                   `value` varchar(50) NOT NULL default '',
                   PRIMARY KEY  (`id`),
                   UNIQUE KEY `name` (`name`)
                 )";
          mysql_query($sql);
		  $sql = "INSERT INTO `config` VALUES (NULL, 'dbversion', '200512051800');";
		  mysql_query($sql);
	  }
	  
	  if ($dboldversion < "200512291500") {
		  $sql = "UPDATE `claimscolumns` SET `datatype_special` = '|Damage|Warranty|Shortage|Refused|Parts' WHERE `idnum` =108 LIMIT 1";
		  mysql_query($sql);
	  }
	  
	  if ($dboldversion < "200512291600") {
	  	 // Changes how order snapshots work
		 runsqlfile("200512291600.sql");
	  }

	  if ($dboldversion < "200601171300") {
	  	$sql = "ALTER TABLE `users` CHANGE `district` `manager` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE `users` SET `manager` = ''";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200601250100") {
	  	runsqlfile("200601250100.sql");
	  }

	  if ($dboldversion < "200602030100") {
	  	$sql = 
		"ALTER TABLE `forms` ADD `address` VARCHAR( 255 ) NOT NULL AFTER `minimum` ,
		ADD `city` VARCHAR( 100 ) NOT NULL AFTER `address` ,
		ADD `state` CHAR( 2 ) NOT NULL AFTER `city` ,
		ADD `zip` VARCHAR( 5 ) NOT NULL AFTER `state` ,
		ADD `phone` VARCHAR( 20 ) NOT NULL AFTER `zip` ,
		ADD `fax` VARCHAR( 20 ) NOT NULL AFTER `phone` ;";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200602070400") {
	  	require_once("inc_orders.php");
	  	$sql = "SELECT ID, name FROM vendors";
		$query = mysql_query($sql);
		checkDBError($sql);
		while ($row = mysql_fetch_assoc($query)) {
			snapshot_update_vendor($row['ID']);
		}
	  }

	  if ($dboldversion < "200603062000") {
	    $sql = "ALTER TABLE `vendors` ADD `freight` DOUBLE(8,2) unsigned NOT NULL DEFAULT '0.00' AFTER `discount`";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "ALTER TABLE `vendors` CHANGE `discount` `discount` DOUBLE( 8, 2 ) unsigned NOT NULL DEFAULT '0.00'";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "ALTER TABLE `forms` ADD `discount` DOUBLE(8,2) unsigned NULL DEFAULT NULL AFTER `fax`, ADD `freight` DOUBLE(8,2) unsigned NULL DEFAULT NULL AFTER `discount`";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "ALTER TABLE `freight` CHANGE `freight` `freight` DOUBLE( 8, 2 ) UNSIGNED NOT NULL DEFAULT '0'";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "SELECT ID, vendor_id, freight FROM `freight` WHERE `user_id` = 0";
		$query = mysql_query($sql);
		checkDBError($sql);
		while ($row = mysql_fetch_assoc($query)) {
		  // Set the proper vendor
		  $sql = "UPDATE `vendors` SET freight = '".$row['freight']."' WHERE ID = '".$row['vendor_id']."'";
		  mysql_query($sql);
		  checkDBError($sql);
		  // Clean up the old table
		  $sql = "DELETE FROM `freight` WHERE ID = '".$row['ID']."'";
		  mysql_query($sql);
		  checkDBError($sql);
	    }
		$sql = "CREATE TABLE `discount` (`ID` int( 10 ) unsigned NOT NULL AUTO_INCREMENT , `form_id` int( 10 ) unsigned NOT NULL default '0', `user_id` int( 10 ) unsigned NOT NULL default '0', `discount` double( 8, 2 ) unsigned NOT NULL default '0.00', PRIMARY KEY ( `ID` ))";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "SELECT ID, vendor_id, user_id, freight FROM `freight`";
		$query = mysql_query($sql);
		checkDBError($sql);

		$freights = array();
		while ($row = mysql_fetch_assoc($query)) {
			$freights[] = $row;
		}
		$sql = "TRUNCATE TABLE `freight`";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "ALTER TABLE `freight` CHANGE `vendor_id` `form_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0'";
		mysql_query($sql);
		checkDBError($sql);
		foreach ($freights as $freight) {
			$sql = "SELECT ID FROM `users` WHERE ID = ".$freight['user_id'];
			$query = mysql_query($sql);
			checkDBError($sql);
			if (!mysql_fetch_row($query)) {
				continue;
			}
			$sql = "SELECT ID FROM `vendors` WHERE ID = ".$freight['vendor_id'];
			$query = mysql_query($sql);
			checkDBError($sql);
			if (!mysql_fetch_row($query)) {
				continue;
			}
			$sql = "SELECT ID FROM `forms` WHERE vendor = ".$freight['vendor_id'];
			$forms = mysql_query($sql);
			checkDBError($sql);
			while ($form = mysql_fetch_assoc($forms)) {
				$sql = "INSERT INTO `freight` (`form_id`,`user_id`, `freight`) VALUES (".$form['ID'].",".$freight['user_id'].",".$freight['freight'].")";
				$query = mysql_query($sql);
				checkDBError($sql);
			}
		}
      }
	  if ($dboldversion < "200603100600") {
		$sql = "ALTER TABLE `snapshot_items` ADD `setqty` SMALLINT DEFAULT '2' NOT NULL";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "ALTER TABLE `form_items` ADD `setqty` SMALLINT DEFAULT '2' NOT NULL ;";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200604042300") {
	  	$sql = " INSERT INTO `config` ( `id` , `name` , `value` )
		VALUES (
		'', 'mangertext', 'Level'
		) ";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "CREATE TABLE `managers` (
		`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
		`name` VARCHAR( 40 ) NOT NULL ,
		`order` FLOAT NOT NULL ,
		PRIMARY KEY ( `id` )
		) TYPE = MYISAM ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "INSERT INTO `managers` ( `name`, `order` )
		VALUES ('1',1), ('TBD',2), ('2b',3), ('3',4), ('4/5',5)";
		mysql_query($sql);
		checkDBError($sql);
	  }
	  if ($dboldversion < "200604060100") {
	  	$sql = "ALTER TABLE `form_items` CHANGE `set_` `set_` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ,
		CHANGE `matt` `matt` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ,
		CHANGE `box` `box` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE form_items SET `set_` = NULL WHERE `set_` = '';";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE form_items SET `matt` = NULL WHERE `matt` = '';";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE form_items SET `box` = NULL WHERE `box` = '';";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200605010500") {
	  	$sql = "ALTER TABLE `claims` ADD `default_vendor_filter` VARCHAR( 50 ) NOT NULL AFTER `default_filter` ;";
		mysql_query($sql);
		checkDBError($sql);
	  }

      if ($dboldversion < "200606100600") {
	  	$sql = "ALTER TABLE `users` ADD `level` VARCHAR( 20 ) NOT NULL ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE `users` SET `level` = `manager`;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE `config` SET `value` = 'Manager';";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200609040100") {
		$sql = "CREATE TABLE `announcement` (
				`id` INT NOT NULL AUTO_INCREMENT ,
				`subject` VARCHAR( 100 ) NOT NULL ,
				`text` TEXT NOT NULL ,
				`source` TEXT NOT NULL ,
				`expire` DATETIME NOT NULL ,
				PRIMARY KEY ( `id` )
				) TYPE = MYISAM ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "CREATE TABLE `announcement_bound` (
				  `user_id` int(11) NOT NULL default '0',
				  `announcement_id` int(11) NOT NULL default '0',
				  KEY `user_id` (`user_id`)
				) TYPE = MyISAM ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "CREATE TABLE `announcement_unread` (
				  `user_id` int(11) NOT NULL default '0',
				  `announcement_id` int(11) NOT NULL default '0',
				  KEY `user_id` (`user_id`)
				) TYPE = MyISAM ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "INSERT INTO `announcement` (`subject`,`text`,`source`,`expire`) VALUES ('Test','Testing...','Testing...', 2006-10-10)";
		mysql_query($sql);
		checkDBError($sql);
		$temp = mysql_insert_id();
		$sql = "INSERT INTO `announcement_bound` (`user_id`,`announcement_id`) VALUES (106, ".$temp.")";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "INSERT INTO `announcement_unread` (`user_id`,`announcement_id`) VALUES (106, ".$temp.")";
		mysql_query($sql);
		checkDBError($sql);
	  }

	  if ($dboldversion < "200609050600") {
		$sql = "ALTER TABLE `announcement_bound` ADD `read` SMALLINT DEFAULT '0' NOT NULL ;";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "UPDATE `announcement_bound` SET `read` = 1";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "SELECT * FROM `announcement_unread`";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$sql = "UPDATE `announcement_bound` SET `read` = 0 WHERE `announcement_id` = ".$row['announcement_id']." AND user_id = ".$row['user_id'];
			mysql_query($sql);
			checkDBError($sql);
		}
	  }

	  if ($dboldversion < "200609181300") {
		$sql = 'ALTER TABLE `claims` ADD `cansplit` INT(1) DEFAULT \'0\' NOT NULL AFTER `massedit`';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'ALTER TABLE `claims` ADD `splitclear` TINYTEXT AFTER `cansplit`';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'ALTER TABLE `claimstest` ADD `cansplit` INT(1) DEFAULT \'0\' NOT NULL AFTER `massedit`';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'ALTER TABLE `claimstest` ADD `splitclear` TINYTEXT AFTER `cansplit`';
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `claims` SET `cansplit` = 1, `splitclear` = 'tracking,shipping_info' WHERE `name` = 'order'";
		mysql_query($sql);
		checkdberror($sql);
		$sql = "DELETE FROM `claimscolumns` WHERE `form` = 'order' AND (`id` = 'shipped' OR `id` = 'routing')";
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'UPDATE `claimscolumns` SET `on_summary` = \'0\' WHERE `idnum` = 61 LIMIT 1';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'ALTER TABLE `claim_order` ADD `carrier` VARCHAR( 150 ) NOT NULL AFTER `tracking` ,
		ADD `shipdate` DATE NOT NULL AFTER `carrier` ;';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`) VALUES (\'\', \'carrier\', \'Carrier\', \'0\', \'text\', \'1\', \'0\', \'1\', \'1\', \'1\', \'0\', \'order\', \'\', NULL, \'14\', \'Include Phone Number\', \'0\', \'0\', \'0\', \'150\')';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'UPDATE `claimscolumns` SET `order` = 15 WHERE `idnum` =60 LIMIT 1';
		mysql_query($sql);
		checkdberror($sql);
		$sql = 'INSERT INTO `claimscolumns` (`idnum`, `id`, `nicename`, `required`, `datatype`, `on_summary`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`) VALUES (\'\', \'shipdate\', \'Ship Date\', \'0\', \'date\', \'1\', \'0\', \'1\', \'1\', \'1\', \'0\', \'order\', \'\', NULL, \'16\', NULL, \'0\', \'0\', \'0\', \'-1\')';
		mysql_query($sql);
		checkdberror($sql);
	  }

	  if ($dboldversion < "200609191300") {
	  	$sql = 'INSERT INTO `claimscolumns` (`id`, `nicename`, `required`, `datatype`, `on_summary`, `multiline`, `insert`, `edit`, `visible`, `massedit`, `form`, `url_prefix`, `datatype_special`, `order`, `note`, `dealerfilter`, `vendorfilter`, `adminfilter`, `limit`) VALUES (\'shipped\', \'Ship\', 0, \'checkbox\', 1, 0,0,1,1,1, \'order\', \'\', NULL, 14, NULL, 0,0,0,-1)';
		mysql_query($sql);
		checkdberror($sql);
	  }
	  if ($dboldversion < "200609220900") {
		$sql = "CREATE TABLE `exported_orders` (
				  `id` int(11) NOT NULL auto_increment,
				  `po_id` int(11) NOT NULL default '0',
				  `export_id` int(11) default NULL,
				  `name` varchar(255) NOT NULL default '',
				  `date` varchar(15) NOT NULL default '',
				  `vendor` varchar(255) NOT NULL default '',
				  `product` varchar(50) NOT NULL default '',
				  `total` varchar(20) NOT NULL default '',
				  `type` enum('Access','MAS90') NOT NULL default 'Access',
				  PRIMARY KEY  (`id`)
				);";
		mysql_query($sql);
		checkdberror($sql);
		$sql = "CREATE TABLE `exported_orders_log` (
				  `id` int(11) NOT NULL auto_increment,
				  `po_id` int(11) NOT NULL default '0',
				  `access` int(11) default NULL,
				  `mas90` int(11) default NULL,
				  `access_queue` tinyint(1) NOT NULL default '0',
				  `mas90_queue` tinyint(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `po_id` (`po_id`)
				);";
		mysql_query($sql);
		checkdberror($sql);
	  }

	  if ($dboldversion < "200609261400") {
	  	$sql = "UPDATE  `exported_orders`  SET  `total`  = REPLACE(SUBSTR(`total` FROM 2),',','');";
		mysql_query($sql);
		checkdberror($sql);
	  }

	  if ($dboldversion < "200610021430") {
		  $sql = "ALTER TABLE `users` ADD `Access_name` VARCHAR( 100 ) NOT NULL ,ADD `MAS90_name` VARCHAR( 100 ) NOT NULL ;";
		  mysql_query($sql);
		  checkdberror($sql);
		  $sql = "ALTER TABLE `vendors` ADD `Access_name` VARCHAR( 100 ) NOT NULL , ADD `Access_type` ENUM( 'Bedding', 'Case Goods' ) NOT NULL ,ADD `MAS90_type` ENUM( 'BEDDING', 'CASE', 'ROYAL' ) NOT NULL ;";
		  mysql_query($sql);
		  checkdberror($sql);
	  }

?>