<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" ); 
	
	$sql = "SELECT ID,first_name,last_name FROM users WHERE disabled != 'Y' ORDER BY last_name,first_name";
        $query = mysql_query($sql);
        checkDBError();
	
	?>
	<br>
<span class="fat_black">Order As</span><br><br>
<table width="90%" border="0" cellpadding="5" cellspacing="0">
  <tr bgcolor="#fcfcfc"> 
    <td width="40%" class="fat_black_12"><strong>Name</strong></td>
    <td colspan="11" class="fat_black_12"></td>
  </tr>
  <?php
	while( $result = mysql_fetch_Array( $query ) )
	{
	?>
  <tr> 
    <td width="45%"><a href="/form-view.php?ID=<?php echo $_GET['ID'] ?>&orderas=<?php echo $result['ID'] ?>">
      <?php echo $result['last_name']; ?> - <?php echo $result['first_name']; ?></a></td>
  </tr>
  <?php
	}
	?>
</table>
<br>