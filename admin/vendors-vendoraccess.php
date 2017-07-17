<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" );

function hasAccess( $user, $vendor )
{
	$sql = "select ID from vlogin_access where user=$user and vendor=$vendor";
	$query = mysql_query( $sql );
	checkDBerror();
	
	if( mysql_num_rows( $query ) > 0 )
		return true;
	return false;
}

if( $submit1 != "" )
{
	$sql = "delete from vlogin_access where user=$ID";
	mysql_query($sql);
	checkDBError();
	
	reset ($_POST);
	while (list ($key, $val) = each ($_POST)) 
	{
		if( substr( $key, 0, 1 ) == 'V' && $val != '' )
		{
			$vendor=substr( $key, 1, 100 );
			$sql = "insert into vlogin_access values ('null', $ID, $vendor)";
			mysql_query( $sql );
			checkDBError();
		}
	}
}


$sql = "select name from vendor where id=$ID";
$query = mysql_query( $sql );
checkDBError();

if( mysql_num_rows( $query ) > 0 )
	assignFieldsToVars( $query );

$sql = "select * from vendors order by name";
$query = mysql_query( $sql );
checkDBError();

if( mysql_num_rows( $query ) == 0 )
{
?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">

	<div>
  <span class="fat_red">No Vendors In The Database!</span>
</div>
<?php
	footer();
	exit;
}
?><br>
<div class="fat_black"><?php echo $name; ?> Access</div>
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
  	<td colspan="2"><a href="vendors.php?action=loginview">New Vendor Login</a></td>
    <td colspan="1" align="right"><a href="vendors.php">Vendor List</a> || <a href="vendors.php?action=loginlist">Vendor Logins</a></td>
  </tr>
  <tr bgcolor="#fcfcfc"> 
    <td class="fat_black_12">Access</td>
    <td class="fat_black_12">Vendor Name</td>
	<td class="fat_black_12">&nbsp;</td>
  </tr>
  <form action="vendors-vendoraccess.php" method="post">
    <input type="hidden" name="ID" value="<?php echo $ID ?>">
    <?php
while( $result = mysql_fetch_Array( $query ) )
{
?>
    <tr> 
      <td>
        <input type="checkbox" name="V<?php echo $result['ID'] ?>" value="Y"<?php if( hasAccess( $ID, $result['ID'] ) ) echo " CHECKED"; ?>>
      </td>
      <td class="text_12">
        <?php echo $result['name']; ?>
      </td>
    </tr>
    <?php
}
?>
    <tr> 
      <td colspan=2 align="center">
        <input type="submit" name="submit1" style="background-color:#CA0000;color:white" value="Submit Changes">
      </td>
    </tr>
  </form>
</table>

<?php
footer($link);
?>