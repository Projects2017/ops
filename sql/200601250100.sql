CREATE TABLE `form_access` (
`id` INT NOT NULL AUTO_INCREMENT ,
`user` INT( 10 ) NOT NULL ,
`form` INT( 10 ) NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `user` )
) TYPE = MYISAM ;

INSERT INTO `form_access` (`user`,`form`) SELECT vendor_access.user, forms.ID as form FROM `vendor_access` INNER JOIN forms ON vendor_access.vendor = forms.vendor;
