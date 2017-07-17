 CREATE TABLE `sos` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `user_id` INT NOT NULL ,
 `date` DATE NOT NULL ,
 `trs` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `cogs` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `bd` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `salestax` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `ufunds` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `ops_pickups` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `ops_deliveries` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `ops_deliveryfees` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 `wsent` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00',
 INDEX ( `user_id` , `date` )
 ) ENGINE = MYISAM ;


 CREATE TABLE `sos_exp` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `user_id` INT NOT NULL ,
 `type` ENUM( 'i', 'c' ) NOT NULL ,
 `active` SMALLINT( 1 ) NOT NULL ,
 `cat_id` INT NOT NULL ,
 `subcat_id` INT NOT NULL ,
 `note` VARCHAR( 50 ) NOT NULL ,
 INDEX ( `user_id` , `cat_id` )
 ) ENGINE = MYISAM ;

 CREATE TABLE `sos_user_cat` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `sos_id` INT NOT NULL ,
 `exp_id` INT NOT NULL ,
 `value` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00'
 ) ENGINE = MYISAM ;


 CREATE TABLE `sos_cat` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `name` VARCHAR( 50 ) NOT NULL ,
 `order` INT NOT NULL
 ) ENGINE = MYISAM ;

 CREATE TABLE `sos_subcat` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `cat_id` INT NOT NULL ,
 `name` VARCHAR( 50 ) NOT NULL ,
 `order` INT NOT NULL
 ) ENGINE = MYISAM ;

 ALTER TABLE `sos_cat` ADD `needsub` INT( 1 ) NOT NULL DEFAULT '0' AFTER `name` ;

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Ads', '0', '1');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Bank Service', '0', '2');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Rent', '0', '3');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Phone', '0', '4');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Professional Fees', '0', '5');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Utilities', '0', '6');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Insurance', '0', '7');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Freight', '0', '8');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Payroll', '1', '9');

 INSERT INTO `sos_subcat` ( `id` , `cat_id`, `name` , `order` ) VALUES (
 NULL , '9', 'Dealer', '1');

 INSERT INTO `sos_subcat` ( `id` , `cat_id`, `name` , `order` ) VALUES (
 NULL , '9', 'Assistant', '2');

 INSERT INTO `sos_subcat` ( `id` , `cat_id`, `name` , `order` ) VALUES (
 NULL , '9', 'Warehouse', '3');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Payroll Taxes', '0', '10');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'License Fee', '0', '11');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Sales Tax', '0', '12');

 INSERT INTO `sos_cat` ( `id` , `name` , `needsub` , `order` ) VALUES (
 NULL , 'Misc', '0', '13');
