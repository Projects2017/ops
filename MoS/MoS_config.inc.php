<?php
// Include site Config
include('../config.default.php');
include('../config.inc.php');

# Make defines from config, and unset config vars.
define('MERCHANT_LOGIN_ID',$config_merchant_login_id);
define('MERCHANT_TRANSACTION_KEY',$config_merchant_transaction_id);
define('MERCHANT_SANDBOX', $config_merchant_sandbox);
unset($config_merchant_login_id);
unset($config_merchant_transaction_id);
unset($config_merchant_sandbox);

if (!$MoS_enabled) die ("Market Order System Disabled");


/* Removing This Config Info (it's identical to the parent one)
//-- Connection to the MoS database
$basedir = "/home/.kink/pmdmos/pmdmos.com/";
$tmpdir = "/home/.kink/pmdmos/tmp/";
$databasename = "pmdext";
$link = mysql_connect('mysql.pmdmos.com', 'pmdext', 'xGdf783TT');
mysql_select_db($databasename);
$admin_pass = "xGdf783TT"; // password required to change a user to highest security
*/
?>
