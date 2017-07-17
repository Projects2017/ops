<?php

require ("../../database.php");
require ("../../secure.php");
include ('../../include/BigBoard.php');
include ('../../include/User.php');

$month_start    = date($_REQUEST['y']."-".$_REQUEST['m']."-1");
$month_end    = date($_REQUEST['y']."-".$_REQUEST['m']."-31");

$manager = '';
if(isset($_REQUEST['mgr']))
    $manager = $_REQUEST['mgr'];

$sql = "select sum(total) as total, u.manager as name from order_forms as o left join users as u on u.id = o.user where o.ordered >= '".$month_start."' AND o.ordered <= '".$month_end."' and o.deleted = 0 and u.nonPMD <> 'Y' and total > 0 group by u.manager ORDER BY total DESC";

$arrManagersMonthly = mysql_query($sql);
while($objManagerMonthly = mysql_fetch_assoc($arrManagersMonthly)){
	$managersMonthly[] = $objManagerMonthly;
}

$o = 1;
	foreach($managersMonthly as $manager){
	?>
									<div class="row">
<div class="col-sm-12" style="padding:7px;border-top:1px solid #ccc;">

<?php
		if ($manager['name'] != "None" && ($manager['name'] != "Clifton Mast")){	
			echo $o.". ".$manager['name']."<br>";
			$o++;
		}
	?>
</div>
</div>


	<?php
	}
?>
