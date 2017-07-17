<?php

require ("../../database.php");
require ("../../secure.php");
include ('../../include/BigBoard.php');
include ('../../include/User.php');

$sql = "select user_id, (bedding_signs_retail + bedding_internet_retail + bedding_craigslist_retail + bedroom_retail + babycase_retail + bedroom_retail + dining_retail) AS total_retail from salestats HAVING total_retail > 2000 ORDER BY total_retail DESC LIMIT 10";
$arrResults = mysql_query($sql) or die(mysql_error());

        while($row = mysql_fetch_array($arrResults)) {
        	$curUser = "SELECT first_name, last_name, ID from users WHERE ID=".$row['user_id'];
			$objUser = mysql_query($curUser) or die(mysql_error());
			$objUser = mysql_fetch_assoc($objUser);
			#print_r($objUser);
        ?>
			<div class="box" data-value="<?php=$objUser['first_name']?> <?php=$objUser['last_name']?>">
				<div class="boximg"><img class="img-circle" alt="" src="img/img1.png"/></div>
				<div class="boxname"><?php=$objUser['first_name']?> <?php=$objUser['last_name']?></div>
			</div>
		<?php
#            print_r($row);
        }


?>