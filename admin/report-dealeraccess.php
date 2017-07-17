<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" );

$sql = "select * from users where disabled != 'Y' AND nonPMD != 'Y' order by last_name, first_name";
$query = mysql_query( $sql );
checkDBError();
?>
<br>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td colspan="3" align="right"><a href="users.php">User List</a></td>
  </tr>
  <tr> 
    <td class="fat_black_12" bgcolor="#fcfcfc">Name</td>
    <td class="fat_black_12" bgcolor="#fcfcfc">User</td>
    <td class="fat_black_12" bgcolor="#fcfcfc" colspan="2">Address</td>
  </tr>
  <tr> 
    <td class="text_12" bgcolor="#fcfcfc"></td>
    <td class="text_12" bgcolor="#fcfcfc">Vendor</td>
    <td class="text_12" bgcolor="#fcfcfc" colspan="2">Form</td>
  </tr>
  <?php
while( $result = mysql_fetch_array( $query ) )
{
?>
  <tr> 
    <td valign="top" class="fat_black_12"> 
      <?php echo $result['last_name'] ?>,
      <?php echo $result['first_name'] ?>
    </td>
    <td valign="top" class="fat_black_12"> 
      <?php echo $result['username'] ?>
      </td>
    <td valign="top" class="fat_black_12"> 
      <?php echo $result['address']?>,
      <?php echo $result['city']?>,
      <?php echo $result['state'] ?>
      &nbsp;&nbsp; 
      <?php echo $result['zip'] ?>
    </td>
  </tr>
  <?php
	$sql = "select ID, name from vendors ORDER BY vendors.name";
	$query2 = mysql_query($sql);
	checkDBError();
	
	$vendors = 1;
	while( $result2 = mysql_fetch_array( $query2 ) )
	{
		$vendornamed = false;
		$sql = "select forms.* from forms inner join form_access ON form_access.form = forms.ID  where form_access.user = '".$result['ID']."' AND vendor='".$result2['ID']."' ORDER BY forms.name";
		$query3 = mysql_query($sql);
		while( $result3 = mysql_fetch_array( $query3 ) ) {
?>
  <tr> 
    <td align="right" class="text_12">
      <?php
		if (!$vendornamed) echo $vendors;
?>
</td>
    <td class="text_12"> 
      <?php
		if (!$vendornamed) {
			echo $result2['name'];
			$vendornamed = true;
			$vendors++;
		}
?>
    </td>
    <td class="text_12">
      <?php
		echo $result3['name'];
?>
    </td>
  </tr>
  <?php
		}
	}
}
?>
</table>
<?php footer($link); ?>
