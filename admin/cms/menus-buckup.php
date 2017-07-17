<?php
require("../database.php");
require("../secure.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>RSS</title>
    <link rel="stylesheet" href="styles.css" type="text/css">
    <link href="css/jquery.nestable.css" rel="stylesheet" />
</head>
<body bgcolor="#EDECDA">
<?php require("menu.php");  ?>

<script src="js/jquery.nestable.js"></script>



<table width="760">
    <tr>
        <td align="left">
            <h1><a href="index.php" style="font-size: 28px">Content Management</a>: Menus</h1>
        </td>
        <td align="right">
            <a href="edit_menu_item.php">+ Add New Menu</a>
        </td>
    </tr>
</table>

<?php
$sql = "select * from cms_menu_items WHERE depth=0 order by menu_order";
$query = mysql_query($sql);
checkDBError();

?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
    <tr bgcolor="#CCCC99">
        <td class="fat_black_12">Menu Title</td>
        <td class="fat_black_12">Actions</td>
    </tr>


    <tr>
        <td colspan="2">


            <div class="dd" id="nestable">
                <ol class="dd-list">

                    <?php

                    while ($row = mysql_fetch_array($query)) {
                        ?>
                        <li class="dd-item dd3-item" data-id="<?=$row['cms_menu_item_id']?>">

                            <div class="dd-handle dd3-handle">Drag</div><div class="dd3-content"><?=$row['menu_text']?> &nbsp;<a class="btn btn-xs btn-default btn-edit-ivr-row" data-id="1" href="#edit" style="border-radius:12px;font-size:10px;" data-original-title="" title=""><i class="fa fa-pencil"></i></a>  <a class="delete_ivr_row" href="#" data-id="<?=$row['cms_menu_item_id']?>" data-original-title="" title="">Delete</a>
                                <a href="edit_menu_item.php?cms_menu_item_id=<?=$row['cms_menu_item_id']?>">Edit</a>
                            </div>

                            <?php
                            $curLevel1 = mysql_query("select * from cms_menu_items WHERE depth=1 AND parent_id=".$row['cms_menu_item_id']." order by menu_order");
                            ?>
                            <ol class="dd-list">
                                <?php
                                while ($rowLevel1 = mysql_fetch_array($curLevel1)) {
                                    ?>
                                    <li class="dd-item dd3-item" data-id="<?=$rowLevel1['cms_menu_item_id']?>">
                                        <div class="dd-handle dd3-handle">Drag</div><div class="dd3-content"><?=$rowLevel1['menu_text']?> &nbsp;<a class="btn btn-xs btn-default btn-edit-ivr-row" data-id="1" href="#edit" style="border-radius:12px;font-size:10px;" data-original-title="" title=""><i class="fa fa-pencil"></i></a>  <a class="delete_ivr_row" href="#" data-id="<?=$rowLevel1['cms_menu_item_id']?>" data-original-title="" title="">Delete</a>
                                            <a href="edit_menu_item.php?cms_menu_item_id=<?=$rowLevel1['cms_menu_item_id']?>">Edit</a>
                                        </div>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ol>
                        </li>
                        <?php
                    }
                    ?>

                </ol>

            </div>
        </td>
    </tr>

</tablE>

<script>

  $(function(){
    $('.dd').nestable({ /* config options */ });

    var updateOutput = function(e)
    {
      var list   = e.length ? e : $(e.target),
        output = list.data('output');
      if (window.JSON) {
        json = window.JSON.stringify(list.nestable('serialize'));
        $.post( "update_menu_items.php", { json: json })
          .done(function( data ) {
//				alert( "Data Loaded: " + data );
          });
      }
    };

    var deleteItem = function(e){
      confDelete(e.attr("data-id"));
    };

    $(".delete_ivr_row").click(function(){
      deleteItem($(this));
    });

    $("#save_ivr_item").click(function(){
      saveItem();
    });

    $("#save_ivr_name").click(function(){
      saveIVRName();
    });

    $('#new_nestable').nestable({
      group: 0
    })
      .on('change', hideMe);

    $('#nestable').nestable({
      group: 1,
      maxDepth: 10
    })
      .on('change', updateOutput);

    // output initial serialised data
//    updateOutput($('#nestable').data('output', $('#nestable-output')));

  });


  function hideMe(){
    $("#new_nestable").hide();
  }

  function confDelete(cms_menu_item_id){
    if (confirm("Are you sure you want to delete this menu item?")) {
      document.location = "delete_menu_item.php?cms_menu_item_id=" + cms_menu_item_id;
    }
  }


</script>