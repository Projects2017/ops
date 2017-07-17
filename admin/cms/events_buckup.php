<?php
require("../database.php");
require("../secure.php");

#added cms_events and cms_event_categories tables to DB

?>

<?php require("menu.php");  ?>


<table width="760">
    <tr>
        <td align="left">
            <h1><a href="index.php" style="font-size: 28px">Content Management</a>: Events</h1>
        </td>
        <td align="right">
            <a href="edit_event.php">+ Add New Event</a>
        </td>
    </tr>
</table>
<?php
if(!empty($_REQUEST['msg'])) {
    echo "<div style='width:100%; background-color:#d9edf7; padding:5px;'><h2>".$_REQUEST['msg']."</h2></div><br style='clear:both;'/>";
}

?>
<?php
$sql = "select * from cms_events where deleted = 0 order by title ";
$query = mysql_query($sql);
checkDBError();
?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
    <tr bgcolor="#CCCC99">
        <td class="fat_black_12">Event Title</td>
        <td class="fat_black_12">Start Date</td>
        <td class="fat_black_12">End Date</td>
        <td class="fat_black_12">Actions</td>
    </tr>

    <?php

    while ($row = mysql_fetch_array($query)) {
        ?>
        <tr bgcolor="#FFFFFF">
            <td class="text_12"><?=$row['title']?></td>
            <td class="text_12"><?=date("m/d/Y",strtotime($row['start_date']))?></td>
            <td class="text_12"><?=date("m/d/Y",strtotime($row['end_date']))?></td>
            <td><a href="edit_event.php?cms_event_id=<?=$row['cms_event_id']?>">Edit</a> &nbsp;|&nbsp; <a href="javascript:confirmDelete(<?=$row['cms_event_id']?>)">Delete</a></td>
        </tr>
        <?php
    }
    ?>

</table>
<script>
  function confirmDelete(id){
    var r = confirm("Confirm Event Delete");
    if (r == true) {
      document.location = "delete_event.php?id=" + id;
    }
  }

</script>