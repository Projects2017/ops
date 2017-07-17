<?php
require ("../database.php");
require ("../secure.php");
require ("../form.inc.php");
require ("../announce.inc.php");
include ('../include/BigBoard.php');
?>

<?php include 'includes/header.php';?>

<?php BigBoard::printContentBlock(4,'main_content'); ?>

<?php include 'includes/footer.php';?>
