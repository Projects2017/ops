<?php
require("MoS_database.php");
require("MoS_admin_secure.php");
require("MoS_menu.php");

$sql = "select MoS_forms.minimum, vendors.name from MoS_forms left join vendors on vendors.ID=MoS_forms.vendor where MoS_forms.ID=$ID";
$query = mysql_query($sql);
checkDBError($sql);

if ($result = mysql_fetch_Array($query))
{
	$vendorname = $result['name'];
	$minimum = $result['minimum'];
}
?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
<br>
<form action="MoS_form-order-edit.php" method="post">
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
	<td colspan="2"><p class="fat_black"><?php echo $vendorname; ?> - Reorder Headings</p></td>
</tr>
<?php
$sql = "select * from MoS_form_headers where form=$ID order by header";
$query = mysql_query($sql);
checkDBError($sql);

while ($result = mysql_Fetch_array($query))
{
	$form_id = $result['form'];
?><tr><td>
          <input name="<?php echo $result['ID']; ?>" type="text" id="<?php echo $result['ID']; ?>" size="3" maxlength="6" value="<?php echo $result['display_order']; ?>"></td>
          <td><p><?php echo $result['header']; ?></p></td></tr>
        <?php } ?>
<tr><td colspan="2">
        <input name="form_id" type="hidden" id="form_id" value="<?php echo $form_id; ?>">
        <input name="what" type="hidden" id="what" value="headers"> 
        <input type="submit" name="submit" style="background-color:#CA0000;color:white" value="Update Order">
	</td>
</tr>
</table>
</form>
</body>
</html>
