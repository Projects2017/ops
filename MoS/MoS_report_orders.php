<?php

require("MoS_database.php");

if (isset($_GET['u'])) {
	require("MoS_user_secure.php");
	require("MoS_dealer_menu.php");
	
	if (!isset($_GET['date'])) {
		$_GET['date'] = "7day";
	}
	
	$and_user = " AND MoS_order_forms.user = '" . $_GET['u'] . "'";

	?>
		<Form name='date_form' method='get' action='MoS_report_orders.php'>
		<INPUT type='hidden' name='u' value='<?php echo $_GET['u']; ?>'>
		<SELECT name='date' onchange='submit();'><option value='<?php echo DATE("Y-m-d"); ?>'>Today</option><option value=''>All days</option><option value='7day'>Last 7 Days</option><option value='3day'>Last 3 Days</option></SELECT>
		</FORM>
		<SCRIPT>document.date_form.date.value = '<?php echo $_GET['date']; ?>';</SCRIPT>
	<?php
}
else 
{
	require("MoS_admin_secure.php");
	require("MoS_menu.php");
	
	if (!isset($_GET['start_date'])) {
		$_GET['start_date'] = date("Y-m-d");
	}
	
	if (!isset($_GET['end_date'])) {
		$_GET['end_date'] = date("Y-m-d",strtotime("-7 days"));
	}

        if ($_GET['start_date'] < $_GET['end_date']) {
                $end_date_temp = $_GET['start_date'];
                $_GET['start_date'] = $_GET['end_date'];
                $_GET['end_date'] = $end_date_temp;
        }
	
	$_GET['date'] = $_GET['start_date'].":".$_GET['end_date'];
?>
<BR>
<H3>Market Order System - Order Queue</H3>

<FORM name='date_form' method='get' action='MoS_report_orders.php'>
Dates between: <SELECT name='start_date' onchange='submit();'>
<OPTION value="<?php= DATE("Y-m-d", strtotime("-7 days")) ?>">7 Days Ago</OPTION>
<OPTION value="<?php= DATE("Y-m-d", strtotime("-3 days")) ?>">3 Days Ago</OPTION>
<OPTION value="<?php= DATE("Y-m-d") ?>">Today</OPTION>
<?php
	$sql = "SELECT DISTINCT(DATE_FORMAT(MoS_order_forms.ordered, '%Y-%m-%d')) as ordered FROM MoS_order_forms ORDER BY MoS_order_forms.ordered DESC";
	$query = mysql_query($sql) or die(mysql_error());
	checkDBError($sql);
	while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
		echo "<OPTION value='" . DATE("Y-m-d",strtotime($result['ordered'])) . "'>" . DATE("Y-m-d", strtotime($result['ordered'])) . "</OPTION>\n";
	}
?>

</SELECT>
and
<SELECT name='end_date' onchange='submit();'>
<OPTION value="<?php= DATE("Y-m-d", strtotime("-7 days")) ?>">7 Days Ago</OPTION>
<OPTION value="<?php= DATE("Y-m-d", strtotime("-3 days")) ?>">3 Days Ago</OPTION>
<OPTION value="<?php= DATE("Y-m-d") ?>">Today</OPTION>
<?php
	$sql = "SELECT DISTINCT(DATE_FORMAT(MoS_order_forms.ordered, '%Y-%m-%d')) as ordered FROM MoS_order_forms ORDER BY MoS_order_forms.ordered DESC";
	$query = mysql_query($sql) or die(mysql_error());
	checkDBError($sql);
	while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
		echo "<OPTION value='" . DATE("Y-m-d",strtotime($result['ordered'])) . "'>" . DATE("Y-m-d", strtotime($result['ordered'])) . "</OPTION>\n";
	}
?>

</SELECT>
<BR><BR>
<B>Filters: </B>&nbsp;&nbsp;&nbsp;
Team: <SELECT name='team'><OPTION value=''>All Teams</OPTION>
<?php
$teams = teams_list();
foreach ($teams as $team) {
	echo "<OPTION value='" . $team . "'>Team " . $team . "</OPTION>\n";
}
?>
</SELECT>
<SCRIPT type='text/javascript'>document.date_form.team.value = '<?php echo $_GET['team']; ?>';</SCRIPT>
<?php
$sql = "SELECT DISTINCT(vendors.ID) as ID, vendors.name as vname FROM vendors, MoS_orders, forms WHERE MoS_orders.form = forms.ID AND forms.vendor = vendors.ID ORDER BY vendors.name";
$query = mysql_query($sql);
checkDBError($sql);
$vendorslist = array();
while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
	$vendorslist[$line['ID']] = $line;
	$vendorslist[$line['ID']]['forms'] = array();
	$sql = "SELECT DISTINCT(forms.ID) as ID, forms.name as fname FROM MoS_orders, forms WHERE MoS_orders.form = forms.ID AND forms.vendor = ".$line['ID']." ORDER BY forms.name";
	$query2 = mysql_query($sql);
	checkDBError($sql);
	while ($fline = mysql_fetch_array($query2, MYSQL_ASSOC)) {
		$vendorslist[$line['ID']]['forms'][$fline['ID']] = $fline;
	}
}
?>
&nbsp;&nbsp;&nbsp; Vendor: <SELECT name='vendor' onchange="setvendor(this.options[this.selectedIndex].value);"><OPTION value=''>All Vendors</OPTION>
<?php
foreach ($vendorslist as $line) {
	echo "<OPTION value='" . $line['ID'] . "'>" . $line['vname'] . "</OPTION>\n";
}
?>
</SELECT>
<SPAN id="form_select" style="display: none">
&nbsp;&nbsp;&nbsp; Form: <SELECT name='form'><OPTION value=''>All Forms</OPTION>
</SELECT>
</SPAN>
<SCRIPT type='text/javascript'>
	<?php echo js_array('vendorslist',$vendorslist); ?>
	function setvendor(vendorid) {
		var formselect = document.date_form.form;
		var formview = document.getElementById('form_select');
		if (vendorslist[vendorid]) {
			formselect.length = 0;
			var y = document.createElement('option');
			y.value = '';
			y.appendChild(document.createTextNode('All Forms'));
			formselect.appendChild(y);
			for (var i in vendorslist[vendorid].forms) {
				y = document.createElement('option');
				y.value = vendorslist[vendorid].forms[i].ID;
				y.appendChild(document.createTextNode(vendorslist[vendorid].forms[i].fname));
				formselect.appendChild(y);
			}
			formview.style.display = '';
		} else {
			formview.style.display = 'none';
			formselect.length = 0;
			var y = document.createElement('option');
			y.value = '';
			y.appendChild(document.createTextNode('All Forms'));
			formselect.appendChild(y);
		}
	}
	document.date_form.vendor.value = '<?php echo $_GET['vendor']; ?>';
	setvendor('<?php echo $_GET['vendor']; ?>');
	document.date_form.form.value = '<?php echo $_GET['form']; ?>';
</SCRIPT>


&nbsp;&nbsp;&nbsp; Dealer: <SELECT name='dealer'><OPTION value=''>All Dealers</OPTION>
<?php
$sql = "SELECT DISTINCT(users.ID) as ID, users.first_name, users.last_name FROM users, MoS_orders WHERE MoS_orders.user = users.ID ORDER BY users.last_name, users.first_name";
$query = mysql_query($sql);
checkDBError($sql);
while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
	echo "<OPTION value='" . $line['ID'] . "'>" . $line['last_name'] . ", " . $line['first_name'] . "</OPTION>\n";
}
?>
</SELECT>
<SCRIPT type='text/javascript'>document.date_form.dealer.value = '<?php echo $_GET['dealer']; ?>';</SCRIPT>
&nbsp;&nbsp;&nbsp;
<INPUT type='submit' value='Filter Results'><BR>
<INPUT type='checkbox' name='view_deleted' <?php if ($_GET['view_deleted'] == "on") { echo " CHECKED "; }?>>See deleted orders
<INPUT type='checkbox' name='view_processed' <?php if ($_GET['view_processed'] == "on") { echo " CHECKED "; }?>>See processed orders
</FORM>

<?php
} //-- This ends the user check
if (isset($_GET['date']) && $_GET['date'] != "####") {

if ($_GET['view_processed'] == 'on') {
?>
	<TABLE><TR>
	<TD><B>Grand Total (without disapproved):</B> <SPAN id='gtotal2'></SPAN></TD>
	<TD width=50></TD>
	<TD><B>Grand Total (with disapproved):</B> <SPAN id='gtotal'></SPAN></TD>
	</TR></TABLE>
	<BR>
<?php
}
	if ($_GET['date'] == '3day') {
		$and_date = " AND MoS_order_forms.ordered BETWEEN '" . DATE("Y-m-d",strtotime("-3 days")) ."' AND '" . DATE("Y-m-d",strtotime("+1 day")) . "' ";
		echo "<SCRIPT>document.date_form.date.value = '" . $_GET['date'] . "';</SCRIPT>\n";
	} elseif ($_GET['date'] == '7day') {
		$and_date = " AND MoS_order_forms.ordered BETWEEN '" . DATE("Y-m-d",strtotime("-7 days")) ."' AND '" . DATE("Y-m-d",strtotime("+1 day")) . "' ";
		echo "<SCRIPT>document.date_form.date.value = '" . $_GET['date'] . "';</SCRIPT>\n";
	} elseif (count(explode(":",$_GET['date'])) == 2) {
		$and_date = explode(":",$_GET['date']);
		echo "<SCRIPT>document.date_form.start_date.value = '" . $_GET['start_date'] . "';</SCRIPT>\n";
		echo "<SCRIPT>document.date_form.end_date.value = '" . $_GET['end_date'] . "';</SCRIPT>\n";
		$and_date = " AND MoS_order_forms.ordered BETWEEN '" . DATE("Y-m-d",strtotime($and_date[1])) ."' AND '" . DATE("Y-m-d",strtotime("+1 day",strtotime($and_date[0]))) . "' ";
	} elseif ($_GET['date'] != "") {
		echo "<SCRIPT>document.date_form.date.value = '" . $_GET['date'] . "';</SCRIPT>\n";
		$and_date = " AND MoS_order_forms.ordered LIKE '" . DATE("Y-m-d", strtotime($_GET['date'])) . "%' ";
	}
	if ($_GET['team'] != "") {
		$and_team = " AND users.team = '" . $_GET['team'] . "' ";
	}
	if ($_GET['vendor'] != "") {
		$and_vendor = " AND vendors.ID = '" . $_GET['vendor'] . "' ";
	}
	if ($_GET['form'] != "") {
		$and_form = " AND forms.ID = '" . $_GET['form'] . "' ";
	}
	if ($_GET['dealer'] != "") {
		$and_dealer = " AND MoS_order_forms.user = '" . $_GET['dealer'] . "' ";
	}
	?><form method="post" action="MoS_report-orders-masssubmit.php?request=<?php echo urlencode($_SERVER['QUERY_STRING']); if (isset($_GET['u'])) { echo "&u=1"; }?>"><?php
	echo "<table border='0' cellspacing='0' cellpadding='3'>";
	
	//-- Unprocessed Orders
	$query_conditions = " processed = 'N' " . $and_date . $and_team . $and_vendor . $and_form . $and_dealer . $and_user;
	$unprocessed_total = makeOrderTable("black", "#AAAA00", "Unprocessed Orders", $query_conditions, "Market", 1);

	if ($_GET['view_processed'] == 'on') {
		//-- Disapproved
		$query_conditions = " processed = 'D' " . $and_date . $and_team . $and_vendor . $and_form . $and_dealer . $and_user;
		$disapproved_total = makeOrderTable("white", "#AA0000", "Disapproved Orders", $query_conditions, "Market");
	
		//-- Processed Orders
		$query_conditions = " (processed = 'A' OR processed = 'Y') " . $and_date . $and_team . $and_vendor . $and_form . $and_dealer . $and_user;
		$approved_total = makeOrderTable("white","#00AA00", "Approved Orders", $query_conditions, "PMD");
		
		$gtotal2 = $unprocessed_total + $approved_total;
		$gtotal = $gtotal2 + $disapproved_total;
	}

	echo '</table>';
	echo '</form>';
}
?>

</body>
</html>

<?php if ($_GET['view_processed'] == 'on') { ?>
<SCRIPT type='text/javascript'>
	document.getElementById('gtotal').innerHTML = '<?php echo makeThisLookLikeMoney($gtotal); ?>';
	document.getElementById('gtotal2').innerHTML = '<?php echo makeThisLookLikeMoney($gtotal2); ?>';
</SCRIPT>
<?php } ?>

<?php 
//---
//--- FUNCTIONS
//---

// if $mass = 1, then show checkbox that mass processes orders
function makeOrderTable($text, $bg, $name, $query_conditions, $place, $mass = 0) {
	if ($_GET['view_deleted'] != "on") {
		$and_deleted = " AND deleted = 0 ";
	}
	else {
		$and_deleted = "";
	}
	$sql = "SELECT SUM(counted) FROM ((SELECT COUNT(MoS_order_forms.ID) as counted FROM MoS_order_forms, users, snapshot_forms, vendors, forms 
			WHERE MoS_order_forms.user = users.ID 
			AND MoS_order_forms.form = forms.ID
			AND MoS_order_forms.snapshot_form = snapshot_forms.id
			AND MoS_order_forms.snapshot_location = 'PMD'
			$and_deleted
			AND snapshot_forms.orig_vendor = vendors.ID AND " . $query_conditions . "
			)UNION(
			SELECT COUNT(MoS_order_forms.ID) as counted FROM MoS_order_forms, users, MoS_snapshot_forms, vendors, forms
			WHERE MoS_order_forms.user = users.ID 
			AND MoS_order_forms.form = forms.ID
			AND MoS_order_forms.snapshot_form = MoS_snapshot_forms.id
			AND MoS_order_forms.snapshot_location = 'MOS'
			$and_deleted
			AND MoS_snapshot_forms.orig_vendor = vendors.ID AND " . $query_conditions . ")) as counted";
	$query = mysql_query($sql);
	checkDBError($sql);
	$result = mysql_fetch_row($query);
	?>
	  <tr bgcolor="<?php echo $bg; ?>">
		<td class="fat_black_12" style='color: <?php echo $text; ?>;' colspan=<?php echo $mass?"7":"6"; ?>><?php echo $name; ?> (<?php echo $result[0]; ?>)</td>
	  </tr>
	  <tr bgcolor="#fcfcfc"> 
		<td class="fat_black_12">Name</td>
		<td class="fat_black_12" width=150>Form</td>
		<td class="fat_black_12" width=90>Date</td>
		<td class="fat_black_12" width=90>Total</td>
		<td class="fat_black_12" width=80><?php echo $place;?> PO #</td>
		<td class="fat_black_12">Details</td>
		<?php if ($mass&&!isset($_GET['u'])) { ?><td class="fat_black_12">Proc</td><?php } ?>
	  </tr>
	  <tr><td colspan="8">&nbsp;</td></tr>
	<?php 
	/*$sql = "SELECT MoS_order_forms.ID, CHAR_LENGTH(MoS_order_forms.comments) as comm_len, MoS_order_forms.ordered, MoS_order_forms.user, MoS_order_forms.form, MoS_order_forms.snapshot_form, MoS_order_forms.snapshot_user, MoS_order_forms.total, MoS_order_forms.processed
	 FROM MoS_order_forms, users, snapshot_forms, vendors, forms LEFT JOIN snapshot_users ON MoS_order_forms.snapshot_user = snapshot_users.id 
	 WHERE MoS_order_forms.user = users.ID 
	 AND MoS_order_forms.snapshot_form = snapshot_forms.id
	 AND snapshot_forms.orig_vendor = vendors.ID 
	 AND MoS_order_forms.form = forms.ID
	 AND " . $query_conditions . " 
	 ORDER BY MoS_order_forms.ordered, MoS_order_forms.ID DESC, snapshot_users.last_name, snapshot_users.first_name";*/

	 if ($place == "PMD") {
		 $order_by = "PMD_order_id";
	 }
	 else {
		 $order_by = "ID";
	 }
//############################################
	$sql = "(SELECT MoS_order_forms.ID, deleted, MoS_order_forms.PMD_order_id, MoS_order_forms.snapshot_location, snapshot_users.last_name, snapshot_users.first_name, CHAR_LENGTH(MoS_order_forms.comments) as comm_len, MoS_order_forms.ordered, MoS_order_forms.user, MoS_order_forms.form, MoS_order_forms.snapshot_form, MoS_order_forms.snapshot_user, MoS_order_forms.total, MoS_order_forms.processed
	 FROM (MoS_order_forms, users, snapshot_forms, vendors, forms) LEFT JOIN snapshot_users ON MoS_order_forms.snapshot_user = snapshot_users.id 
	 WHERE MoS_order_forms.user = users.ID 
	 AND MoS_order_forms.snapshot_form = snapshot_forms.id
	 AND MoS_order_forms.form = forms.ID
     AND MoS_order_forms.snapshot_location = 'PMD' 
	 $and_deleted
	 AND snapshot_forms.orig_vendor = vendors.ID 
	 AND " . $query_conditions . "
	 ORDER BY last_name, first_name, ordered, $order_by DESC
	 ) UNION (
	 SELECT MoS_order_forms.ID, deleted, MoS_order_forms.PMD_order_id, MoS_order_forms.snapshot_location, snapshot_users.last_name, snapshot_users.first_name, CHAR_LENGTH(MoS_order_forms.comments) as comm_len, MoS_order_forms.ordered, MoS_order_forms.user, MoS_order_forms.form, MoS_order_forms.snapshot_form, MoS_order_forms.snapshot_user, MoS_order_forms.total, MoS_order_forms.processed
	 FROM (MoS_order_forms, users, MoS_snapshot_forms, vendors, forms) LEFT JOIN snapshot_users ON MoS_order_forms.snapshot_user = snapshot_users.id 
	 WHERE MoS_order_forms.user = users.ID 
	 AND MoS_order_forms.form = forms.ID
	 AND MoS_order_forms.snapshot_form = MoS_snapshot_forms.id
	 AND MoS_order_forms.snapshot_location = 'MOS' 
	 $and_deleted
	 AND MoS_snapshot_forms.orig_vendor = vendors.ID 
	 AND " . $query_conditions . ") 
	 ORDER BY last_name, first_name, ordered, $order_by DESC ";

	$query = mysql_query($sql);
	checkDBError($sql);
	$user = 0;
	while ($result = mysql_fetch_array($query)) {

		if ($user <> $result['snapshot_user']) {
			$user = $result['snapshot_user'];
			echo customerRow($user);
		}
		$po = ($result['ID'] + 1000);
		if ($place == "PMD") {
			$po_to_show = $result['PMD_order_id']+1000;
		}
		else {
			$po_to_show = $po;
		}
		$total += $result['total'];
		if ($result['snapshot_location'] == "PMD") {
			$loc = "";
		}
		elseif ($result['snapshot_location'] == "MOS") {
			$loc = "MoS_";
		}
		?>
		  <tr <?php if ($result['deleted'] == 1) { echo " bgcolor='#C0C0C0' "; }?>> 
			<td>&nbsp;</td>
			<td class="text_12"><?php echo getFormName($result['snapshot_form'], $loc); ?></td>
			<td class="text_12"><?php echo formatDate($result['ordered']); // ordered = 1 ?></td>
			<td class="text_12"><?php echo makeThisLookLikeMoney($result['total']); ?></td>
			<td class="text_12"> <?php echo $po_to_show; ?> </td>
			<td class="text_12"> <a href="MoS_report-orders-details.php?po=<?php echo $po; ?>&request=<?php echo urlencode($_SERVER['QUERY_STRING']); if (isset($_GET['u'])) { echo "&u=1"; }?>">Details</a><?php if ($result['comm_len'] > 0) { echo "*"; } ?>
			</td>
		<?php if ($mass&&!isset($_GET['u'])) { ?>
			<td class="text_12"><input type="checkbox" name="po<?php echo $po; ?>" value="proc"></td>
		<?php } ?>
		  </tr>
		<?php
	}
	?>
	<TR bgcolor=black><TD height=1 colspan=<?php echo $mass?"7":"6"; ?>></TD></TR>
    <tr>
      <TD></TD><TD></TD>
	  <TD><B>Total:</B></TD>
  	  <TD><B><?php echo makeThisLookLikeMoney($total); ?></B></TD>
  	  <?php if ($mass&&!isset($_GET['u'])) { ?>
  	  <td colspan=3 align="right"><input type="submit" value="Approve Orders"></td>
  	  <?php } else { ?>
	  <TD></TD><TD></TD>
	  <?php } ?>
	</tr>
	<tr><td height=50></TD></TR><?php
	return $total;
}

function customerRow($user) {
	$sql = "SELECT last_name, first_name, address, city, state, zip FROM snapshot_users WHERE ID=$user";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query)) {
		$return_string = "<tr> 
			<td class=\"customerRowLeft\"> ".$result[0].", ".$result[1]."</td>
			<td class=\"customerRowRight\" colspan=\"7\"> ".$result[2].", ".$result[3].", ".$result[4].", &nbsp;".$result[5]."</td>
		  </tr>";
	} else {
		$return_string = "<tr><td colspan=\"8\">error</td></tr>";
	}
	return $return_string;
}

function getFormName($header, $loc) {
	if ($header == NULL) return "Corrupt";
	$sql = "select name from " . $loc . "snapshot_forms where ID=$header";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_array($query))
		return $result[0];
	return "";
}

function formatDate($date) {
	return date('m/d/Y', strtotime($date));
}

function getEmailVendorDate($po) {
	$po_id = $po-1000;
	$sql = "select email_vendor from MoS_order_forms where ID=$po_id";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query))
		return $result['email_vendor'];
	return '0000-00-00';
}

?>
