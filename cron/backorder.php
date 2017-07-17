<?php
require("../database.php");
require("../inc_backorder.php");
function secure_is_admin() { return true; }
function secure_is_dealer() { return true; }
function secure_is_vendor() { return false; }

// Check Backorders and process! woo!
bo_checkstock();
?>