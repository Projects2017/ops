<?php

require ("../../database.php");
require ("../../secure.php");
include ('../../include/BigBoard.php');
include ('../../include/User.php');

$month_start    = date($_REQUEST['y']."-".$_REQUEST['m']."-1");
$month_end    = date($_REQUEST['y']."-".$_REQUEST['m']."-t");

$manager = '';
if(isset($_REQUEST['manager']))
    $manager = $_REQUEST['manager'];

$mattress_month = BigBoard::getLeaders($month_start, $month_end, "Bedding",10,$manager);

?>

<?php if (count($mattress_month)>0) { ?>
	<table width="100%">
		<tbody>
		<?php for($x=0;$x<count($mattress_month);$x++) {
			if (!empty($mattress_month[$x]['big_board_name'])){
				$strDisplayName = $mattress_month[$x]['big_board_name'];
			} else {
				$strDisplayName = $mattress_month[$x]['last_name'];
			}
		?>
			<tr>
				<td class="photo"><a href="#"><img alt="" src="img/img12.png"/></a></td>
				<td class="name"><a href="#"><?php=$strDisplayName?></a></td>
				<td class="amount"><a href="#">$<?php=number_format($mattress_month[$x]['total'])?></a></td>
			</tr>
		<?php  } ?>


		</tbody>
	</table>
	<?php } else { ?>
	<center>No results for<Br><?php=$_REQUEST['y']."-".$_REQUEST['m']?></center>
<?php } ?>
