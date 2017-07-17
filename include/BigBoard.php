<?php

class BigBoard {
    public static $email = "gary@retailservicesystems.com";

    public static function printContentBlock($intPageID,$strContentBlock){
    	$sql = "SELECT * from cms_page_content WHERE cms_page_id = ".$intPageID." AND content_block_variable = '".$strContentBlock."'";
    	$result = mysql_query($sql);
    	$row = mysql_fetch_array($result);
    	echo $row['content_block_content'];
	}
	
	public static function allTimeSales($limit=60,$initialSalesDate='2001-01-01 00:00:00',$incentive_trip_sales_floor='75000'){
		$query = "select sum(total) as total_sales, u.first_name, u.last_name,u.ID as id, u.photo, u.big_board_name, u.lb_incentive_ranking, u.level,u.furniture_and_mattress from order_forms as o left join users as u on u.id = o.user left join forms as f ON o.form = f.ID left join vendors as v on v.ID = f.vendor where o.ordered >= '".$initialSalesDate."' and o.deleted = 0 and u.nonPMD <> 'Y' and v.Access_type in ('Bedding','Upholstery','Case Goods') GROUP BY o.user ORDER BY total_sales desc LIMIT ".$limit;
        $result = mysql_query($query) or die(mysql_error());
       	while($row = mysql_fetch_assoc($result)){
       		if ($row[total_sales] > $incentive_trip_sales_floor){
				$arrResult[] = $row;
			}
       	}
        return $arrResult;
	}

    public static function getLeaders($start, $stop, $category = 'all', $count = 25, $manager = '', $user_id = '', $isNational = '', $totalLimit=0){
    	$realCount = $count;
        $query = "select  sum(total) as total,  u.first_name, u.last_name,u.ID as id, u.photo, u.big_board_name, u.lb_incentive_ranking, u.level,u.furniture_and_mattress from order_forms as o
                    left join users as u on u.id = o.user ";

        $query .= " left join forms as f  ON o.form = f.ID ";
        $query .= " left join vendors as v on v.ID = f.vendor ";

        $query .= "
                    where o.ordered >= '".$start." 00:00:00' and o.ordered <= '".$stop." 23:59:59'
                        and o.deleted = 0
                        and u.nonPMD <> 'Y'";

        if($manager != '' AND $manager != 'None' AND empty($isNational)) {
            $query .= " and u.manager = '".$manager."'";
        }

		switch($category){

	        case 'Bedding':
	        	$query .= " and v.Access_type in ('Bedding') ";
	        	break;				

			case 'Furniture':
				if (empty($user_id)){
		        	$query .= " and v.Access_type in ('Bedding','Upholstery','Case Goods') ";
		        } else {
		        	$query .= " and v.Access_type in ('Upholstery','Case Goods') ";
		        }
	        	break;				
	        	
			case 'All':
		        $query .= " and v.Access_type in ('Bedding','Upholstery','Case Goods') ";
				break;

	        default:
	            $query .= " and v.Access_type = '".$category."' ";
	        	break;

		}
		
        if(!empty($user_id)) {
            $query .= " and u.ID = '".$user_id."'";
        }

			$count = 50;

        $query .="
                        GROUP BY o.user
                        ORDER BY total desc
                        limit ".$count."
                       ";
        $data = array();
        $result = mysql_query($query) or die(mysql_error());
        
        while($row = mysql_fetch_array($result)) {

			if (empty($user_id) && empty($isNational) && $row['total'] > 0){
				switch($category){
					case 'Bedding':
						if ($row['furniture_and_mattress'] != 'Y'){
							$data[] = $row;
						}
						break;				

					case 'Furniture':
						if ($row['furniture_and_mattress'] == 'Y'){
							$data[] = $row;
						}
						break;				
					default:
						$data[] = $row;
						break;
				}
			} else {
				if (!empty($isNational) && $realCount == 1) {
					switch($category){
						case 'Bedding':
							if ($row['furniture_and_mattress'] != 'Y'){
								$data[] = $row;
							}
							break;				

						case 'Furniture':
							if ($row['furniture_and_mattress'] == 'Y'){
								$data[] = $row;
							}
							break;				
						default:
							$data[] = $row;
							break;
					}
				} else {			
					if ($row['total'] > 0){
						$data[] = $row;
					}
				}
			}

        }
#        $data = BigBoard::mergeAccounts($data, 463,576);
#        $data = BigBoard::mergeAccounts($data, 465,579);
//        echo $data;

		if (empty($isNational)){	
			return array_slice($data,0,10);
		} else {
			return array_slice($data,0,25);
		}
    }



    public static function mergeAccounts($data, $user1, $user2) {
        $total_user = 0;
        $index_to_remove = -1;
        // find second user
        for($x = 0;$x<count($data);$x++){
            if($data[$x]['id'] == $user2){
                $index_to_remove = $x;
                $total_user = $data[$x]['total'];
            }
        }


        // find first user
        for($x = 0;$x<count($data);$x++){
            if($data[$x]['id'] == $user1){
                $data[$x]['total'] = $data[$x]['total'] +  $total_user;
            }
        }
        if($index_to_remove != -1) {
            unset($data[$index_to_remove]);
            $data = array_values($data);
        }
        return $data;
    }

}


?>
