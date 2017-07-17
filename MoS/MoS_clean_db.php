<?php
require('MoS_database.php');
require('MoS_admin_secure.php');
require('MoS_menu.php');
if (!secure_is_superadmin())
	die("Access Denied");

// Now we make sure
// If you automate this, send $_POST['apass'] = $adminpass and $_POST['sure'] = 'Y'

if ($_POST['sure'] != 'Y') {
	?>
	<form action="MoS_clean_db.php" method="post">
	<h2>Reset Market</h2>
	<h3 style="color: red">This will erase all MoS Form Changes and User Preferences</h3><br>
	Are you sure? <input type="checkbox" name="sure" value="Y"><br>
	If so enter site Admin Password: <input type="password" name="apass"><br>
	Select Default Orderable State: <select name="orderable">
		<option value="Y">Yes</option>
		<option value="N">No</option>
	</select><br>
	<input type="submit" value="Empty Database">
	</form>
	<?php
	exit();
} else {
	if ($_POST['apass'] != $admin_pass) die('Invalid Password');
	function runsql($sql) {
		//$sql = implode("\n",$sql);
		$sql = explode(";",$sql);
		array_pop($sql);
		foreach ($sql as $query) {
			$query = trim($query);
			set_time_limit(0);
			mysql_query($query); // Run that sucker...
			checkDBError($query);
		}
	}

	$sql = <<<EOD
        TRUNCATE TABLE `MoS_form_discount`;
        TRUNCATE TABLE `MoS_form_freight`;
	TRUNCATE TABLE `MoS_form_headers`;
	TRUNCATE TABLE `MoS_form_items`;
        TRUNCATE TABLE `MoS_form_item_discount`;
        TRUNCATE TABLE `MoS_form_item_freight`;
	TRUNCATE TABLE `MoS_form_access`;
	TRUNCATE TABLE `MoS_forms`;
	TRUNCATE TABLE `MoS_session`;
	TRUNCATE TABLE `MoS_login_prefs;
	TRUNCATE TABLE `MoS_director`;
EOD;
	/* 
		TRUNCATE TABLE `MoS_snapshot_forms`;
		TRUNCATE TABLE `MoS_snapshot_headers`;
		TRUNCATE TABLE `MoS_snapshot_items`;
		TRUNCATE TABLE `MoS_order_forms`;
		TRUNCATE TABLE `MoS_orders`;
	*/

	runsql($sql);

	$sql = "SELECT forms.ID as fid FROM forms";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if ($_POST['orderable'] == 'Y') {
		$orderable = 'Y';
	} else {
		$orderable = 'N';
	}
	while ($results = mysql_fetch_Array($query, MYSQL_ASSOC))
	{
		$sql = "INSERT INTO `MoS_form_access` (`form_id`, `enabled`) VALUES (".$results['fid'].",'".$orderable."')";
		$query2 = mysql_query($sql);
		checkDBerror($sql);
	}
	echo "DONE";
}
?>