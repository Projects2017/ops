<?php

require("MoS_database.php");
require("MoS_user_secure.php");
//require("../form.inc.php");
//require("../mssg.inc.php");
require("MoS_dealer_menu.php");

	/*$sql = "SELECT * FROM MoS_director";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$director = array();
	while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$director[$line['form_id']] = $line['MoS_form_id'];
	} */

	$sql = "SELECT vendors.name as vname, forms.name as fname, forms.ID as fid FROM vendors, forms, MoS_form_access, form_access WHERE form_access.user = '".$userid."' AND form_access.form = forms.ID AND vendors.ID = forms.vendor AND forms.ID = MoS_form_access.form_id AND MoS_form_access.enabled = 'Y' ORDER BY vendors.name";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$resulta = array();
	while ($results = mysql_fetch_Array($query, MYSQL_ASSOC))
	{
		$resulta[$results['fid']] = $results;
	}
	if (count($resulta)) {
		$sql = "SELECT MoS_forms.ID, MoS_forms.name as fname, vendors.name as vname FROM MoS_forms, vendors WHERE vendors.ID = MoS_forms.vendor AND MoS_forms.ID in (" . implode(', ', array_keys($resulta)) . ")";
		$query = mysql_query($sql);
		checkDBerror($sql);
		while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$resulta[$line['ID']]['fname'] = $line['fname'];
			$resulta[$line['ID']]['vname'] = $line['vname'];
		}
	}
	//-- Resort in case a vendor changed
	function cmp($a, $b) {
	   return strcmp($a["vname"].$a['fname'], $b["vname"].$b['fname']);
	}
	usort($resulta, "cmp");

?>
<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Vendor</td>
    <td class="fat_black_12">Form</td>
    <td class="fat_black_12"></td>
  </tr>
  <?php
	$numforms = 0;
	foreach($resulta as $result) {
		++$numforms;
	?>
  <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php echo $result['vname'] ?></td>
    <td><a href="MoS_form-view.php?ID=<?php echo $result['fid'] ?>"><?php echo $result['fname'] ?></a></td>
    <td></td>
  </tr>
  <?php
	}
	if (!$numforms) {
?>
<tr bgcolor="#FFFFFF">
    <td class="text_12" colspan="3" align="center">You do not have access to any forms!</td>
  </tr>
<?php
	}
mysql_close($link);
?>
<tr>
    <td><img src="../images/furniture1.jpg" width="238" height="150"></td>
    <td align="center"><img src="../images/furniture2.jpg" width="238" height="150"></td>
    <td><img src="../images/furniture3.jpg" width="238" height="150"></td>
  </tr>
</table>
</body>
</html>
