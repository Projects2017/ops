<?php
require_once(dirname(dirname(__FILE__))."/config.default.php"); // Include Default Config
require_once(dirname(dirname(__FILE__))."/config.inc.php"); // Include Base Dir/DB/Super-Admin Pass info

# Make defines from config, and unset config vars.
define('MERCHANT_LOGIN_ID',$config_merchant_login_id);
define('MERCHANT_TRANSACTION_KEY',$config_merchant_transaction_id);
define('MERCHANT_SANDBOX', $config_merchant_sandbox);
unset($config_merchant_login_id);
unset($config_merchant_transaction_id);
unset($config_merchant_sandbox);

if ($MoS_enabled||$BoL_enabled) die ("Standard Site Disabled");

require_once(dirname(dirname(__FILE__))."/inc_database.php");
$link = mysql_connect($databasehost, $databaseuser, $databasepass);
mysql_select_db($databasename);
require_once(dirname(dirname(__FILE__))."/version.php");
