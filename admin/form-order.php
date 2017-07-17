<?php
require("database.php");
require("secure.php");
$extra_onload = "sortables_init();";
require("menu.php");

$sql = "select forms.minimum, vendors.name from forms left join vendors on vendors.ID=forms.vendor where forms.ID=$ID";
$query = mysql_query($sql);
checkDBError();

if ($result = mysql_fetch_Array($query))
{
	$vendorname = $result['name'];
	$minimum = $result['minimum'];
}
?>
<form action="form-order-edit.php" method="post">
  <table border="0" cellpadding="5" cellspacing="0" id="headersort" class="sortable">
    <tr class="skiptop">
	  <td colspan="2"><p class="fat_black"><?php echo $vendorname; ?> - Reorder Headings</p></td>
    </tr>
    <tr>
      <th class="fat_black_12" bgcolor="#fcfcfc">Order</th>
      <th class="fat_black_12" bgcolor="#fcfcfc">Header</th>
    </tr>
<?php
$sql = "select * from form_headers where form=$ID order by display_order";
$query = mysql_query($sql);
checkDBError();

while ($result = mysql_Fetch_array($query))
{
	$form_id = $result['form'];
?>     <tr>
       <td><input value="<?php echo $result['display_order']; ?>" name="<?php echo $result['ID']; ?>" type="text" id="<?php echo $result['ID']; ?>" size="3" maxlength="6"></td>
       <td>
         <p><?php echo $result['header']; ?></p>
       </td>
     </tr>
<?php } ?>
	<tr class="sortbottom">
	  <td colspan="2">
        <input name="form_id" type="hidden" id="form_id" value="<?php echo $form_id; ?>">
        <input name="what" type="hidden" id="what" value="headers"> 
        <input type="submit" name="submit" style="background-color:#CA0000;color:white" value="Update Order">
      </td>
    </tr>
  </table>
</form>

<?php footer($link); ?>
