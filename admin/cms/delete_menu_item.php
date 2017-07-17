<?php
require("../database.php");
require("../secure.php");

$sql = "DELETE FROM cms_menu_items WHERE cms_menu_item_id=".$_REQUEST['cms_menu_item_id'].";";
mysql_query($sql);
Header("Location: menus.php");