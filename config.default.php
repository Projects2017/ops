<?php
// These values MUST be set in config.inc.php
// They will not be read here, this is just an example

date_default_timezone_set('America/New_York');

$basedir = dirname(__FILE__).'/'; // Note trailing slash
$docdir = $basedir.'doc/'; // Document directory
$vdocdir = $basedir.'d/'; // Document directory visible from superusers
$xmldir = $docdir.'xml/'; // XML Documents directory
$tmpdir = dirname(dirname(__FILE__)).'/tmp/'; // Path to temporary files directory
$listdir = $tmpdir."listmem";
$docdir = $basedir.'doc/'; // Document directory
$vdocdir = $basedir.'d/'; // Document directory visible from superusers
$xmldir = $docdir.'xml/'; // XML Documents directory
$databasename = "dbname";
$databasehost = 'localhost';
$databaseuser = 'dbuser';
$databasepass = 'dbpass';
//$link = mysql_pconnect('localhost', 'dbuser', 'dbpass');
//mysql_select_db($databasename);
$admin_pass = "adminpass"; // password required to change a user to highest security
$thumbconverter = 'imagemagick'; // 'gd1','gd2','imagemagick'
// Cancellation System for BOLs Enabled?
$bol_cancel = false;

// Override for Sending mail
$outmail_override = false;

// Market Order System
$MoS_enabled = false; // Is this site MoS or normal?
$BoL_enabled = false; // Is this site BoL or normal?
$MoS_IP = ''; // MoS IP to allow orders from
// MoS Site Definitions
// arrays should look like
// $MoS_clients['SITENAME']['IP'] = '127.0.0.1'
// $MoS_clients['SITENAME']['procurl'] =
//   'http://mos.pmddealer.com/MoS_updatepo.php?po=%s&date=%s'; // First %s is the PO #, second %s is the date processed

$MoS_clients = array();

// Market Order System Master..
$MoS_MasterPath = 'http://ext.pmddealer.com/';
$MoS_MasterFTPHost = 'ext.pmddealer.com';
$MoS_MasterFTPUser = 'pmdextdev';
$MoS_MasterFTPPass = 'xGdf783TT';
$MoS_MasterFTPDBFile  = "MoS_dump.sql";
$MoS_MasterFTPDBFileGZ = $MoS_MasterFTPDBFile.".gz";

$email_addr = "xml@pmdfurniture.com";
$email_host = "localhost";
$email_login = "xml@pmdfurniture.com";
$email_pass = "maquis22";
$email_mailbox = "INBOX";

$enable_backorder = false;

$as2_testing_partnername = "SoflexDevNebula";
$as2_testing = true;

$commercehub_host = 'localhost';
$commercehub_port = '2121';
$commercehub_user = 'comerceweb';
$commercehub_pass = '8UmUpAte6ucruSpev6wrepHE';
$commercehub_dealerlogin = 'costco';

$commercehub_inv_up = '/incoming/inventory';
$commercehub_poconfirms_up = '/costco/incoming/confirms';
$commercehub_fa_up = '/incoming/fa';

$commercehub_order_dl = '/costco/outgoing/orders';
$commercehub_remit_dl = '/costco/outgoing/payment';
$commercehub_email_err = 'will@retailservicesystems.com';

$commercehub_tmp_dir = '/var/www/vhosts/pmddealer.com/private/commercehub_test';

$labelmaster=array('test'=> true);
$ups_login=array('AccessLicenseNumber'=>'AE9T3JGAOEAR4JA0',
                 'UserId'=>'putuseridhere',
                 'Password'=>'swordfish');//Change these to reflect actual login in config.inc.php

$ftpcreds=array("addy"=>"140.174.10.195",
                "name"=>"soflex",
                "pass"=>"welcome1");

$corsicana_processXML = false;

//address info
$companyname = "Retail Service Systems";
$companyaddress = "4660 Kenny Rd - Suite C";
$companycity = "Columbus";
$companystate = "OH";
$companyzip = "43220";

// Post Login Redirect
// $system_login_redirect = false;

// Internal Big Board Enabled (and appropraite redirect)
$bigboardint = true;
$system_login_redirect = "/leaderboard/";

$config_as2copy_cmd = "/var/www/vhosts/pmddealer.com/webbin/as2copy";
$config_as2test_cmd = "/var/www/vhosts/pmddealer.com/webbin/as2test";

# Testing Account - Testing Guide:
# https://developer.authorize.net/hello_world/testing_guide/
$config_merchant_login_id = '9eNXtRt3H2r';
$config_merchant_transaction_id = '6u5P3X9Sr75X3uQQ';
$config_merchant_sandbox = true;

