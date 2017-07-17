<?php
require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');
if(!secure_is_superadmin()) die("Unauthorized user. Permission denied.");
require('archive.php');
require('xml.php');
displayHeader("Import", "");
displayForm();
?>
</body>
</html>
