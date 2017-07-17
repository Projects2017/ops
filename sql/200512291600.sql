CREATE TABLE `snapshot_forms` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`orig_id` INT( 10 ) ,
`orig_vendor` INT( 10 ) ,
`name` VARCHAR( 100 ) NOT NULL ,
`address` VARCHAR( 255 ) NOT NULL ,
`city` VARCHAR( 100 ) NOT NULL ,
`state` CHAR( 2 ) NOT NULL ,
`zip` VARCHAR( 5 ) NOT NULL ,
`phone` VARCHAR( 20 ) NOT NULL ,
`fax` VARCHAR( 20 ) NOT NULL ,
`email` VARCHAR( 50 ) NOT NULL ,
`email2` VARCHAR( 50 ) NOT NULL ,
`prepaidfreight` CHAR( 1 ) NOT NULL ,
`discount` FLOAT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `orig_id` , `orig_vendor` )
) TYPE = MYISAM ;

CREATE TABLE `snapshot_headers` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`orig_id` INT( 10 ) ,
`form` INT( 10 ) NOT NULL ,
`header` VARCHAR( 100 ) NOT NULL ,
`display_order` FLOAT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `orig_id` )
) TYPE = MYISAM ;

CREATE TABLE `snapshot_items` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`orig_id` INT( 10 ) ,
`header` INT( 10 ) NOT NULL ,
`partno` VARCHAR( 100 ) NOT NULL ,
`description` VARCHAR( 100 ) NOT NULL ,
`price` VARCHAR( 100 ) NOT NULL ,
`size` VARCHAR( 100 ) NOT NULL ,
`color` VARCHAR( 100 ) NOT NULL ,
`set_` VARCHAR( 100 ) NOT NULL ,
`matt` VARCHAR( 100 ) NOT NULL ,
`box` VARCHAR( 100 ) NOT NULL ,
`display_order` FLOAT NOT NULL ,
`cubic_ft` FLOAT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `orig_id` , `header`, `partno` )
) TYPE = MYISAM ;

CREATE TABLE `snapshot_users` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`orig_id` INT( 10 ) ,
`first_name` VARCHAR( 100 ) NOT NULL ,
`last_name` VARCHAR( 100 ) NOT NULL ,
`address` VARCHAR( 255 ) NOT NULL ,
`city` VARCHAR( 255 ) NOT NULL ,
`state` CHAR( 2 ) NOT NULL ,
`zip` VARCHAR( 5 ) NOT NULL ,
`phone` VARCHAR( 20 ) NOT NULL ,
`fax` VARCHAR( 20 ) NOT NULL ,
`secondary` CHAR ( 1 ) NOT NULL DEFAULT 'N',
PRIMARY KEY ( `id` ) ,
INDEX ( `orig_id` )
) TYPE = MYISAM ;

ALTER TABLE `forms` ADD `snapshot` INT( 10 ) NOT NULL ;

INSERT INTO `snapshot_forms` (`orig_id`, `orig_vendor`, `name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `email2`, `prepaidfreight`,`discount`) SELECT `forms`.`id` AS orig_id, `vendors`.`id` AS vendor_id, `forms`.`name`, `vendors`.`address`, `vendors`.`city`, `vendors`.`state`, `vendors`.`zip`, `vendors`.`phone`, `vendors`.`fax`, `vendors`.`email`, `vendors`.`email2`, `vendors`.`prepaidfreight`, `vendors`.`discount` FROM `forms` LEFT JOIN `vendors` ON `forms`.`vendor` = `vendors`.`ID`;

UPDATE `forms`, `snapshot_forms` SET `forms`.`snapshot` = `snapshot_forms`.`id` WHERE `forms`.`id` = `snapshot_forms`.`orig_id`;

ALTER TABLE `form_headers` ADD `snapshot` INT( 10 ) NOT NULL ;

INSERT INTO `snapshot_headers` (`orig_id`, `form`, `header`, `display_order`) SELECT `form_headers`.`id`, `snapshot_forms`.`id`, `form_headers`.`header`, `form_headers`.`display_order` FROM `form_headers` LEFT JOIN `snapshot_forms` ON `form_headers`.`id` = `snapshot_forms`.`orig_id`;

UPDATE `form_headers`, `snapshot_headers` SET `form_headers`.`snapshot` = `snapshot_headers`.`id` WHERE `form_headers`.`id` = `snapshot_headers`.`orig_id`;

ALTER TABLE `form_items` ADD `snapshot` INT( 10 ) NOT NULL ;

INSERT INTO `snapshot_items` (`orig_id`, `header`, `partno`, `description`, `price`, `size`, `color`, `set_`, `matt`, `box`, `display_order`, `cubic_ft`) SELECT `form_items`.`id`, `snapshot_headers`.`id`, `form_items`.`partno`, `form_items`.`description`, `form_items`.`price`, `form_items`.`size`, `form_items`.`color`, `form_items`.`set_`, `form_items`.`matt`, `form_items`.`box`, `form_items`.`display_order`, `form_items`.`cubic_ft` FROM `form_items` LEFT JOIN `snapshot_headers` ON `form_items`.`header` = `snapshot_headers`.`orig_id`;

UPDATE `form_items`, `snapshot_items` SET `form_items`.`snapshot` = `snapshot_items`.`id` WHERE `form_items`.`id` = `snapshot_items`.`orig_id`;

ALTER TABLE `users` ADD `snapshot` INT( 10 ) NOT NULL ;

ALTER TABLE `users` ADD `snapshot2` INT( 10 );

INSERT INTO `snapshot_users` (`orig_id`, `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `secondary`) SELECT `id`, `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, 'N' FROM `users`;

UPDATE `users`, `snapshot_users` SET `users`.`snapshot` = `snapshot_users`.`id` WHERE `users`.`id` = `snapshot_users`.`orig_id` AND `snapshot_users`.`secondary` = 'N';

INSERT INTO `snapshot_users` (`orig_id`, `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `secondary`) SELECT `id`, `first_name`, `last_name`, `address2`, `city2`, `state2`, `zip2`, `phone`, `fax`, 'Y' FROM `users` WHERE `address` != '';

UPDATE `users`, `snapshot_users` SET `users`.`snapshot2` = `snapshot_users`.`id` WHERE `users`.`id` = `snapshot_users`.`orig_id` AND `snapshot_users`.`secondary` = 'Y';

ALTER TABLE `orders` ADD `snapshot_user` INT( 10 );
ALTER TABLE `orders` ADD `snapshot_form` INT( 10 );

UPDATE `orders`, `snapshot_users` SET `orders`.`snapshot_user` = `snapshot_users`.`id` WHERE `orders`.`user` = `snapshot_users`.`orig_id`;

UPDATE `orders`, `snapshot_forms` SET `orders`.`snapshot_form` = `snapshot_forms`.`id` WHERE `orders`.`form` = `snapshot_forms`.`orig_id`;

ALTER TABLE `order_forms` ADD `snapshot_user` INT( 10 );
ALTER TABLE `order_forms` ADD `snapshot_form` INT( 10 );

UPDATE `order_forms`, `snapshot_users` SET `order_forms`.`snapshot_user` = `snapshot_users`.`id` WHERE `order_forms`.`user` = `snapshot_users`.`orig_id`;

UPDATE `order_forms`, `snapshot_forms` SET `order_forms`.`snapshot_form` = `snapshot_forms`.`id` WHERE `order_forms`.`form` = `snapshot_forms`.`orig_id`;

ALTER TABLE `order_snapshot` ADD INDEX `header_index` ( `header` ) ;
ALTER TABLE `snapshot_headers` ADD INDEX `header_index2` ( `header` ) ;

-- http://dev.mysql.com/doc/refman/4.1/en/insert-select.html
--  Currently, you cannot insert into a table and select from 
--  the same table in a subquery.

CREATE TABLE `temp_headers` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`orig_id` INT( 10 ) ,
`form` INT( 10 ) NOT NULL ,
`header` VARCHAR( 100 ) NOT NULL ,
`display_order` FLOAT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `orig_id` )
) TYPE = MYISAM ;


INSERT INTO `temp_headers` (`header`) SELECT DISTINCT order_snapshot.header FROM order_snapshot LEFT JOIN snapshot_headers ON order_snapshot.header = snapshot_headers.header WHERE snapshot_headers.id IS NULL;
 
INSERT INTO `snapshot_headers` (`header`) SELECT `temp_headers`.`header` FROM `temp_headers`;

DROP TABLE `temp_headers`;

UPDATE `order_snapshot` INNER JOIN `snapshot_headers` USING (`header`) SET `order_snapshot`.`header` = `snapshot_headers`.`id`;
 
ALTER TABLE `snapshot_headers` DROP INDEX `header_index2` ;
ALTER TABLE `order_snapshot` DROP INDEX `header_index` ;
 
ALTER TABLE `snapshot_items` ADD INDEX `temp_partno` (`partno`,`description`,`price`,`size`,`set_`,`matt`,`box`,`cubic_ft`,`color`);
 
INSERT INTO `snapshot_items` (`partno`,`description`,`price`,`size`,`set_`,`matt`,`box`,`cubic_ft`,`color`,`header`) SELECT DISTINCT `order_snapshot`.`partno`, `order_snapshot`.`description`, `order_snapshot`.`price`, `order_snapshot`.`size`, `order_snapshot`.`set_`, `order_snapshot`.`matt`, `order_snapshot`.`box`, `order_snapshot`.`cubic_ft`, `order_snapshot`.`color`, `order_snapshot`.`header` FROM `order_snapshot` LEFT JOIN `snapshot_items` USING(`partno`,`description`,`price`,`size`,`set_`,`matt`,`box`,`cubic_ft`,`color`) WHERE `snapshot_items`.`id` IS NULL;
 
UPDATE `orders`, `snapshot_items` SET `orders`.`item` = `snapshot_items`.`id` WHERE `orders`.`item` = `snapshot_items`.`orig_id` AND `orders`.`po_id` < 21842;
  
ALTER TABLE `order_snapshot` ADD INDEX `temp_partno2` (`partno`,`description`,`price`,`size`,`set_`,`matt`,`box`,`cubic_ft`,`color`);

ALTER TABLE `orders` ADD INDEX ( `po_id` ) ;

ALTER TABLE `order_snapshot` ADD `new` INT( 10 ) DEFAULT '0' NOT NULL ;

UPDATE `order_snapshot` INNER JOIN `snapshot_items` USING (`partno`,`description`,`price`,`size`,`set_`,`matt`,`box`,`cubic_ft`,`color`) SET `order_snapshot`.`new` = `snapshot_items`.`id`;

UPDATE `orders` INNER JOIN `order_snapshot` ON `order_snapshot`.`orders_id` = `orders`.`ID` SET `orders`.`item` = `order_snapshot`.`new` WHERE `orders`.`po_id` >= 21842;

ALTER TABLE `snapshot_items` DROP INDEX `temp_partno`;

DROP TABLE `order_snapshot` ;
