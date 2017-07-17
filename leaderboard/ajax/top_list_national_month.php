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

$mattress_month = BigBoard::getLeaders($month_start, $month_end, $_REQUEST['t'],$_REQUEST['ttl'],$manager,'',true);

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

			# border for ranking
			switch($mattress_month[$x][level]){
				case "1": 
					$borderColor = "#cccccc";
					break;
				case "2": 
					$borderColor = "#CD7F32";
					break;
				case "3": 
					$borderColor = "#FFD700";
					break;
				case "4/5": 
					$borderColor = "#b9f2ff";
					break;
				default:
					$borderColor = "#FFF";
					break;
			}
		?>
			<tr>
				<td class="photo"><a href="#"><div class="smallProfilePhotoCrop" style="border:2px solid <?php=$borderColor?> !important;"><img alt="" class="smallProfilePhoto" src="<?php=profilePhoto($mattress_month[$x]['photo'])?>"/></div></a></td>
				<td class="name"><a href="#"><?php=$strDisplayName?></a></td>
				<td class="amount"><a href="#">$<?php=number_format($mattress_month[$x]['total'])?></a></td>
			</tr>
		<?php  } ?>


		</tbody>
	</table>
	<?php } else { ?>
	<center>No results for<Br><?php=$_REQUEST['m']."/".$_REQUEST['y']?></center>
<?php } ?>
