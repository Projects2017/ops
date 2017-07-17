<?php
if ($type == 'html') {
	require "inc_htmldisplay.php";
} elseif ($type == 'csv') {
	require "inc_csvdisplay.php";
}
?>