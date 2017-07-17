<?php
die("Access Denied");
require_once('MoS_database.php');
// If someone tries to look, this will crash and give them nothing

$sql = <<<EOT

CREATE TABLE `MoS_form_headers` (
  `ID` int(10) NOT NULL auto_increment,
  `form` int(10) NOT NULL default '0',
  `header` varchar(100) NOT NULL default '',
  `display_order` float NOT NULL default '0',
  `snapshot` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `formheaderindex` (`form`)
);

CREATE TABLE `MoS_form_items` (
  `ID` int(10) NOT NULL auto_increment,
  `header` int(10) NOT NULL default '0',
  `partno` varchar(100) NOT NULL default '',
  `description` varchar(100) NOT NULL default '',
  `price` varchar(100) NOT NULL default '',
  `size` varchar(100) NOT NULL default '',
  `color` varchar(100) NOT NULL default '',
  `set_` varchar(100) default NULL,
  `matt` varchar(100) default NULL,
  `box` varchar(100) default NULL,
  `stock` int(11) NOT NULL default '1',
  `display_order` float NOT NULL default '0',
  `stock_date` date default NULL,
  `stock_day` int(11) NOT NULL default '0',
  `cubic_ft` float default NULL,
  `alloc` int(11) NOT NULL default '-1',
  `avail` int(11) NOT NULL default '-1',
  `snapshot` int(10) NOT NULL default '0',
  `setqty` smallint(6) NOT NULL default '2',
  PRIMARY KEY  (`ID`)
);

CREATE TABLE `MoS_forms` (
  `ID` int(10) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `vendor` int(10) NOT NULL default '0',
  `minimum` varchar(20) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `city` varchar(100) NOT NULL default '',
  `state` char(2) NOT NULL default '',
  `zip` varchar(5) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `fax` varchar(20) NOT NULL default '',
  `discount` double(8,2) unsigned default NULL,
  `freight` double(8,2) unsigned default NULL,
  `snapshot` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
);

CREATE TABLE `MoS_order_forms` (
  `ID` int(10) NOT NULL auto_increment,
  `processed` char(1) NOT NULL default '',
  `ordered` datetime NOT NULL default '0000-00-00 00:00:00',
  `process_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `form` int(10) NOT NULL default '0',
  `user` int(10) NOT NULL default '0',
  `comments` text NOT NULL,
  `freight_percentage` float unsigned NOT NULL default '0',
  `discount_percentage` float unsigned NOT NULL default '0',
  `total` double(8,2) NOT NULL default '0.00',
  `type` char(1) NOT NULL default 'o',
  `email_vendor` date NOT NULL default '0000-00-00',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `user_address` tinyint(4) NOT NULL default '1',
  `snapshot_user` int(10) default NULL,
  `snapshot_form` int(10) default NULL,
  `snapshot_location` enum('MOS','PMD') NOT NULL default 'PMD',
  `PMD_order_id` int(11) NOT NULL,
  KEY `ID` (`ID`)
);


CREATE TABLE `MoS_orders` (
  `ID` int(10) NOT NULL auto_increment,
  `user` int(10) NOT NULL default '0',
  `setqty` int(10) NOT NULL default '0',
  `mattqty` int(10) NOT NULL default '0',
  `qty` int(10) NOT NULL default '0',
  `item` int(10) NOT NULL default '0',
  `ordered` date NOT NULL default '0000-00-00',
  `form` int(10) NOT NULL default '0',
  `po_id` int(10) NOT NULL default '0',
  `ordered_time` time NOT NULL default '00:00:00',
  `snapshot_user` int(10) default NULL,
  `snapshot_form` int(10) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `po_id` (`po_id`)
);

CREATE TABLE `MoS_snapshot_forms` (
  `id` int(10) NOT NULL auto_increment,
  `orig_id` int(10) default NULL,
  `orig_vendor` int(10) default NULL,
  `name` varchar(100) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `city` varchar(100) NOT NULL default '',
  `state` char(2) NOT NULL default '',
  `zip` varchar(5) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `fax` varchar(20) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `email2` varchar(50) NOT NULL default '',
  `prepaidfreight` char(1) NOT NULL default '',
  `discount` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `orig_id` (`orig_id`,`orig_vendor`)
);

CREATE TABLE `MoS_snapshot_headers` (
  `id` int(10) NOT NULL auto_increment,
  `orig_id` int(10) default NULL,
  `form` int(10) NOT NULL default '0',
  `header` varchar(100) NOT NULL default '',
  `display_order` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `orig_id` (`orig_id`)
);

CREATE TABLE `MoS_snapshot_items` (
  `id` int(10) NOT NULL auto_increment,
  `orig_id` int(10) default NULL,
  `header` int(10) NOT NULL default '0',
  `partno` varchar(100) NOT NULL default '',
  `description` varchar(100) NOT NULL default '',
  `price` varchar(100) NOT NULL default '',
  `size` varchar(100) NOT NULL default '',
  `color` varchar(100) NOT NULL default '',
  `set_` varchar(100) NOT NULL default '',
  `matt` varchar(100) NOT NULL default '',
  `box` varchar(100) NOT NULL default '',
  `display_order` float NOT NULL default '0',
  `cubic_ft` float NOT NULL default '0',
  `setqty` smallint(6) NOT NULL default '2',
  PRIMARY KEY  (`id`),
  KEY `orig_id` (`orig_id`,`header`,`partno`)
);

CREATE TABLE `MoS_director` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `form_id` INT(11) NOT NULL ,
  `MoS_form_id` INT(11) NOT NULL 
);

EOT;
//$sql = implode("\n",$sql);
$sql = explode(";",$sql);
array_pop($sql);
foreach ($sql as $query) {
	mysql_query($query);
	checkDBError($query);
}
