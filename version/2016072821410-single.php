<?php

$sql = "CREATE TABLE IF NOT EXISTS `cms_menu_items` (
  `cms_menu_item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `depth` int(11) DEFAULT NULL,
  `menu_text` varchar(80) DEFAULT NULL,
  `menu_link` varchar(500) DEFAULT NULL,
  `menu_order` int(11) DEFAULT NULL,
  `cms_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cms_menu_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
mysql_query($sql);
checkDBError($sql);

$sql = "INSERT INTO `cms_menu_items` (`parent_id`, `depth`, `menu_text`, `menu_link`, `menu_order`, `cms_page_id`) VALUES
(0, 0, 'My Dealer Page', '../selectvendor.php', 4, 0);";
mysql_query($sql);
checkDBError($sql);

$sql = "CREATE TABLE IF NOT EXISTS `cms_pages` (
  `cms_page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_title` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `show_title` int(1) NOT NULL DEFAULT '0',
  `template_id` int(3) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cms_page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;";
mysql_query($sql);
checkDBError($sql);

$sql = "CREATE TABLE IF NOT EXISTS `cms_page_content` (
  `cms_page_content_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cms_page_id` int(11) DEFAULT NULL,
  `content_block_name` varchar(50) DEFAULT NULL,
  `content_block_variable` varchar(50) DEFAULT NULL,
  `content_block_content` text,
  PRIMARY KEY (`cms_page_content_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
mysql_query($sql);
checkDBError($sql);

$sql = "CREATE TABLE IF NOT EXISTS `cms_sliders` (
  `cms_slider_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slider_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cms_slider_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
mysql_query($sql);
checkDBError($sql);

$sql = "CREATE TABLE IF NOT EXISTS `cms_slider_slides` (
  `cms_slider_slide_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cms_slider_id` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`cms_slider_slide_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
mysql_query($sql);
checkDBError($sql);

$sql = "CREATE TABLE IF NOT EXISTS `cms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
mysql_query($sql);
checkDBError($sql);

$sql = "INSERT INTO `cms_templates` (`id`, `name`, `deleted`) VALUES
(1, 'Default', 0),
(2, 'Homepage', 0),
(3, 'Gallery - 3 Column', 0),
(4, 'Grand Day', 0),
(5, 'Thanksgiving', 0);";
mysql_query($sql);
checkDBError($sql);
