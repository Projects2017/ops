<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" );

$sql = "select `forms`.`ID`, `vendors`.`name` as vname, `forms`.`name` as fname from `forms` inner join `vendors` on `forms`.`vendor` = `vendors`.`ID` ORDER BY `vendors`.`name`, `forms`.`name`;";

//$sql = "select * from users where disabled != 'Y' AND nonPMD != 'Y' order by last_name, first_name";
$query = mysql_query( $sql );
checkDBError();
?>
<br>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td colspan="4" align="right"><a href="vendors.php">Vendor List</a> || <a href="vendors.php?action=loginlist">Vendor Logins</a> || <a href="report-vendoraccess.php">Vendor Access Report</a></td>
  </tr>
  <tr> 
    <td class="fat_black_12" bgcolor="#fcfcfc" colspan="2">Vendor</td>
    <td class="fat_black_12" bgcolor="#fcfcfc">Form</td>
    <td class="fat_black_12" bgcolor="#fcfcfc" colspan="2">&nbsp;</td>
  </tr>
  <tr> 
    <td class="fat_black_12">&nbsp;</td>
    <td class="fat_black_12">Name</td>
    <td class="fat_black_12">Address</td>
    <td class="fat_black_12">Phone</td>
  </tr>
  <?php
while( $result = mysql_fetch_array( $query ) )
{
?>
  <tr> 
    <td valign="top" class="fat_black_12" bgcolor="#fcfcfc" colspan="2"> 
      <?php echo $result['vname'] ?>
    </td>
    <td valign="top" class="fat_black_12" bgcolor="#fcfcfc" colspan="2"> 
      <?php echo $result['fname'] ?>
      </td>
  </tr>
  <?php
	$sql = "select `users`.* FROM `form_access` INNER JOIN `users` ON `form_access`.`user` = `users`.`ID`  WHERE `nonPMD` != 'Y' AND `disabled` != 'Y' AND `form_access`.`form` = '".$result['ID']."' ORDER BY `users`.`last_name`";
	$query2 = mysql_query($sql);
	checkDBError();
	
	$dealers = 0;
	while( $result2 = mysql_fetch_array( $query2 ) )
	{
		$dealers++;
		//$sql = "select * from users where disabled != 'Y' AND nonPMD != 'Y' order by last_name, first_name"
		//$sql = "select forms.* from forms inner join form_access ON form_access.form = forms.ID  where form_access.user = '".$result['ID']."' AND vendor='".$result2['ID']."' ORDER BY forms.name";
		//$query3 = mysql_query($sql);
		//while( $result3 = mysql_fetch_array( $query3 ) ) {
?>
  <tr> 
    <td align="right" valign="top" class="text_12">
      <?php
		echo $dealers;
?>
	</td>
    <td valign="top" class="text_12"> 
      <?php echo $result2['last_name'] ?>,
      <?php echo $result2['first_name'] ?>
    </td>
    <td valign="top" class="text_12"> 
      <?php //echo $result2['address']?>
      <?php echo $result2['city']?>,
      <?php echo $result2['state'] ?>
      <?php echo $result2['zip'] ?>
    </td>
    <td valign="top" class="text_12">
      <?php echo $result2['phone'] ?>
    </td>
  </tr>
  <?php
		//}
	}
}
?>
</table>
<?php footer($link); ?>
