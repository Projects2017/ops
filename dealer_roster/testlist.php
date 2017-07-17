<?php
$dir = opendir('photos/');
?>
<table>
<?php while ($photo = readdir($dir)) {
     if (substr($photo,0,1) == '.') continue;
?>
<tr>
  <td><img src="photos/<?php echo $photo; ?>"></td>
  <td><?php echo htmlentities($photo); ?></td>
</tr>
<?php } ?>
</table>
