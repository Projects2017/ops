<?php
/* READ ME IF BROKEN!
 *   MoS_forms and MoS_form_items tables need to be a mirror of
 *   the RSS live table format of forms and form_items.
 */
require("MoS_database.php");
require("MoS_admin_secure.php");
require( "../inc_orders.php" );

$sql = "SELECT * FROM MoS_director WHERE form_id = $ID";
$query = mysql_query($sql);
if (mysql_num_rows($query) == 1) {
	//-- Change the ID to the one in MoS_director, in case it somehow changed
	$line = mysql_fetch_array($query, MYSQL_ASSOC);
	$ID = $line['MoS_form_id'];
}
else {
	//-- Copy that form over to the MoS_form table and immediately take a snapshot of the whole form to ensure all the snapshot
	//-- references are pointing at the MoS tables, otherwise there's no way to tell what table they're pointing at and cannot
	//-- be properly viewed on the orders approval/etc screen
	$sql = "INSERT INTO MoS_director VALUES (null, $ID, $ID)";
	mysql_query($sql);
        checkDBerror($sql);
	$sql = "INSERT INTO MoS_forms SELECT * FROM forms WHERE ID=$ID";
	mysql_query($sql) ;
        checkDBerror($sql);
        $sql = "INSERT INTO MoS_form_discount SELECT * FROM form_discount WHERE form_id = ". $ID;
        mysql_query($sql);
        checkDBerror($sql);
        copyDbEntry('form_freight', 'MoS_form_freight', 'form_id', $ID, array('id'));
        copyDbEntry('form_discount', 'MoS_form_discount', 'form_id', $ID, array('id'));
        $sql = "INSERT INTO MoS_form_freight SELECT * FROM form_freight WHERE form_id = ". $ID;
        mysql_query($sql);
        checkDBerror($sql);
	$sql = "INSERT INTO MoS_form_headers SELECT * FROM form_headers WHERE form = $ID";
	mysql_query($sql);
        checkDBerror($sql);
	$sql = "SELECT ID FROM MoS_form_headers WHERE form = $ID";
	$result = mysql_query($sql);
        checkDBerror($sql);
	while ($line = mysql_fetch_row($result)) {
		$sql = "INSERT INTO MoS_form_items SELECT * FROM form_items WHERE header = " . $line[0];
		mysql_query($sql);
                checkDBerror($sql);
                $sql = "SELECT ID FROM MoS_form_items WHERE header = ". $line[0];
                $result2 = mysql_query($sql);
                checkDBerror($sql);
                while ($item = mysql_fetch_assoc($result2)) {
                    copyDbEntry('form_item_discount', 'MoS_form_item_discount', 'item_id', $item['ID'], array('id'));
                    copyDbEntry('form_item_freight', 'MoS_form_item_freight', 'item_id', $item['ID'], array('id'));
                }
	}
	snapshot_update('form', $ID);
}
$_POST['ID'] = $ID;
$_GET['ID'] = $ID;
$_REQUEST['ID'] = $ID;

require("../inc_form-edit.php");