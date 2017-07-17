<?php
require("MoS_config.inc.php"); // Include Base Dir/DB/Super-Admin Pass info
$link = mysql_connect($databasehost, $databaseuser, $databasepass);
mysql_select_db($databasename);

if (file_exists($tmpdir.'sync.lock')) {
	function sync_wait($filename) {
		$time = time();
		while (file_exists($filename)) {
			if ($time + 60 < time())
				return false;
			sleep(rand(3,5));
		}
		return true;
	}
	if (!sync_wait($tmpdir.'sync.lock')) {
		$attntext = "We are performing system syncronization, please press refresh. You are seeing this message because the sync took longer than 60 seconds. Please hit F5 to refresh to resume your work.";
		include($basedir.'down.php');
		exit();
	}
}
require("../inc_database.php");
require("../version.php");
