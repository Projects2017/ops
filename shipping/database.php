<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');

require("../config.default.php"); // Include Default Config
require("../config.inc.php"); // Include Base Dir/DB/Super-Admin Pass info

# Make defines from config, and unset config vars.
define('MERCHANT_LOGIN_ID',$config_merchant_login_id);
define('MERCHANT_TRANSACTION_KEY',$config_merchant_transaction_id);
define('MERCHANT_SANDBOX', $config_merchant_sandbox);
unset($config_merchant_login_id);
unset($config_merchant_transaction_id);
unset($config_merchant_sandbox);

$BoL_enabled = true;
#if (!$BoL_enabled) die ("BoL Disabled");
$link = mysql_connect($databasehost, $databaseuser, $databasepass);
mysql_select_db($databasename);

require("../inc_database.php");
require("../version.php");
