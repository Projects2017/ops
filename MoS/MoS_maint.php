<?php
require("MoS_database.php");
require("MoS_admin_secure.php");


if (!secure_is_superadmin()) {
	require("MoS_menu.php");
	die("Access Denied");
}

if ($_GET['orderable'] != '') {
	if ($_GET['orderable'] == 'Y' || $_GET['orderable'] == 'N') {
		$sql = "SELECT `form_id` as fid FROM `MoS_form_access`";
		$query = mysql_query($sql);
		checkDBerror($sql);
		while ($results = mysql_fetch_Array($query, MYSQL_ASSOC))
		{
			$sql = "UPDATE `MoS_form_access` SET `enabled` = '".$_GET['orderable']."' WHERE `form_id` = '".$results['fid']."'";
			$query2 = mysql_query($sql);
			checkDBerror($sql);
		}
	}
	header("Location: MoS_maint.php?note=".urlencode("Forms Updated"));
	exit();
} elseif ($_GET['stocked'] != '') {
	
} elseif ($_POST['comment'] != '') {
	$sql = "REPLACE INTO `MoS_config` SET `name` = 'comment', `value` = '".$_POST['comment']."'";
	mysql_query($sql);
	checkdberror($sql);
	header("Location: MoS_maint.php?note=".urlencode("Order Comment Updated."));
	exit();
} elseif ($_GET['cleandb'] != '') {
    $sql = "SHOW TABLES;";
    $query = mysql_query($sql);
    checkDBerror($sql);
    while ($table = mysql_fetch_array($query, MYSQL_NUM)) {
            if (!MoS_includes_table($table[0])) {
                $sql = "DROP TABLE `".$table[0]."`";
                mysql_query($sql);
                checkDBerror($sql);
            }
    }
}

require("MoS_menu.php");

?>
<?php if ($_GET['note']) {
	?><strong><?php echo $_GET['note']; ?></strong><BR><?php
} ?>
<BR>	
<FONT FACE=ARIAL>
<B>Database Operations</B><BR>
<a href="MoS_clean_db.php">Reset Market</a> | <a href="mos_sync.php">Sync Forms</a><BR>&nbsp;<BR>
<B>Form Operations</B><BR>
<a href="MoS_maint.php?orderable=Y">Make All Orderable</a> | <a href="MoS_maint.php?orderable=N">Make All Non-Orderable</a><BR>&nbsp;<BR>
<!--<B>Item Operations</B><BR>
<a href="MoS_maint.php?stocked=Y">Make All In Stock</a> | <a href="MoS_maint.php?stocked=N">Make All Out of Stock</a><BR>&nbsp;<BR>-->
<B>Auto Comment</B><BR>
<form method="post"><input type="text" size="50" value="<?php echo getconfig('comment','MoS_'); ?>" name="comment"><input type="submit"></form><br>
<B>Developer Operations</B><BR>
<a href="MoS_maint.php?cleandb=Y">Clean DB of Extraneous Tables</a>