<?php

class WodsStats {
	var $mTitle;
	var $mStatId;
	var $mUserId;
	var $mUserLastName;
	var $mUserFirstName;
	var $mDate;
	var $mInsertsOut = 0;
	var $mInsertsShow = 0;
	var $mInsertsPerc = 0.00;
	var $mInsertsSold = 0;
	var $mInsertsSoldPerc = 0.00;
	var $mInsertsRetail = 0.00;
	var $mInsertsProfit = 0.00;
	var $mInsertsProfitPerSale = 0.00;
	var $mInsertsGrossMargin = 0.00;
	var $mInsertsShowPerSale = 0.00;
	var $mInsertsDollarsPerShow = 0.00;
	var $mInsertsProfitAsPercOfBusiness = 0.00;
	var $mSignsShow = 0;
	var $mSignsPerc = 0.00;
	var $mSignsSold = 0;
	var $mSignsSoldPerc = 0.00;
	var $mSignsRetail = 0.00;
	var $mSignsProfit = 0.00;
	var $mSignsProfitPerSale = 0.00;
	var $mSignsGrossMargin = 0.00;
	var $mSignsShowPerSale = 0.00;
	var $mSignsDollarsPerShow = 0.00;
	var $mSignsProfitAsPercOfBusiness = 0.00;
	var $mRepeatsOut = 0;
	var $mRepeatsShow = 0;
	var $mRepeatsPerc = 0.00;
	var $mRepeatsSold = 0;
	var $mRepeatsSoldPerc = 0.00;
	var $mRepeatsRetail = 0.00;
	var $mRepeatsProfit = 0.00;
	var $mRepeatsProfitPerSale = 0.00;
	var $mRepeatsGrossMargin = 0.00;
	var $mRepeatsShowPerSale = 0.00;
	var $mRepeatsDollarsPerShow = 0.00;
	var $mRepeatsProfitAsPercOfBusiness = 0.00;
	var $mOthersOut = 0;
	var $mOthersShow = 0;
	var $mOthersPerc = 0.00;
	var $mOthersSold = 0;
	var $mOthersSoldPerc = 0.00;
	var $mOthersRetail = 0.00;
	var $mOthersProfit = 0.00;
	var $mOthersProfitPerSale = 0.00;
	var $mOthersGrossMargin = 0.00;
	var $mOthersShowPerSale = 0.00;
	var $mOthersDollarsPerShow = 0.00;
	var $mOthersProfitAsPercOfBusiness = 0.00;
	var $mTotalOut = 0;
	var $mTotalShow = 0;
	var $mTotalPerc = 0.00;
	var $mTotalSold = 0;
	var $mTotalSoldPerc = 0.00;
	var $mTotalRetail = 0.00;
	var $mTotalProfit = 0.00;
	var $mTotalProfitPerSale = 0.00;
	var $mTotalGrossMargin = 0.00;
	var $mTotalShowPerSale = 0.00;
	var $mTotalDollarsPerShow = 0.00;
	var $mTotalProfitAsPercOfBusiness = 0.00;
	var $mCreateDate;
	var $mEdits;
	/* private */ var $mDoWodsStats;

	function __construct() {
		$this->WodsStats();
	}

	function WodsStats() {
		$this->mDoWodsStats = new DoWodsStats();
	}

	function GetDates($date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($enddate)) die("WodsStats::GetDates : End Date is not timestamp");
		if ($date > $enddate) die("WodsStats::GetDates : Start is after End Date");
		if ($enddate) {
			$date = getdate($date);
			$enddate = getdate($enddate);
			if ($resolution == 'day') {
				$date = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$enddate['mday'],$enddate['year']);
			} elseif ($resolution == 'week') {
				// Week starts Sun and goes till Sat
				$date = mktime(0,0,0,$date['mon'],$date['mday'] - $date['wday'],$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$enddate['mday'] + 6 - $enddate['wday'],$enddate['year']);
			} elseif ($resolution == 'month') {
				$date = mktime(0,0,0,$date['mon'],1,$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$this->DaysInMonth($enddate['year'],1),$enddate['year']);
			} elseif ($resolution == 'year') {
				$date = mktime(0,0,0,1,1,$date['year']);
				$enddate = mktime(0,0,0,12,$this->DaysInMonth($enddate['year'],12),$enddate['year']);
			} else {
				die("WodsStats::GetDates : Unknown Resolution ".$resolution.".");
			}
		} else { // No end date
			$date = getdate($date);
			if ($resolution == 'day') {
				$enddate = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
				$date = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
			} elseif ($resolution == 'week') {
				$enddate = mktime(0,0,0,$date['mon'],$date['mday'] + 6 - $date['wday'],$date['year']);
				$date = mktime(0,0,0,$date['mon'],$date['mday'] - $date['wday'],$date['year']);
			} elseif ($resolution == 'month') {
				$enddate = mktime(0,0,0,$date['mon'],$this->DaysInMonth($date['year'],$date['mon']),$date['year']);
				$date = mktime(0,0,0,$date['mon'],1,$date['year']);
			} elseif ($resolution == 'year') {
				$enddate = mktime(0,0,0,12,$this->DaysInMonth($date['year'],12),$date['year']);
				$date = mktime(0,0,0,1,1,$date['year']);
			} else {
				die("WodsStats::GetDates : Unknown Resolution ".$resolution.".");
			}
		}
		return array('date' => $date, 'enddate' => $enddate);
	}

	function LoadSum($user_id, $date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($user_id)) die("WodsStats::LoadSum : Invalid Datatype for user_id (".gettype($user_id).")");
		$dates = $this->GetDates($date, $enddate, $resolution);
		$date = $dates['date'];
		$enddate = $dates['enddate'];
		// Retrive values from database
		$stats = $this->mDoWodsStats->GetSums($user_id,$date,$enddate);
		foreach ($stats as $key => $val) { // Set Nulls to 0
			if (is_null($val)) {
				$stats[$key] = 0;
			}
		}
		$this->mInsertsOut = $stats["inserts_out"];
		$this->mInsertsShow = $stats["inserts_show"];
		$this->mInsertsSold = $stats["inserts_sold"];
		$this->mInsertsRetail = $stats["inserts_retail"];
		$this->mInsertsProfit = $stats["inserts_profit"];
		$this->mSignsOut = $stats["signs_out"];
		$this->mSignsShow = $stats["signs_show"];
		$this->mSignsSold = $stats["signs_sold"];
		$this->mSignsRetail = $stats["signs_retail"];
		$this->mSignsProfit = $stats["signs_profit"];
		$this->mRepeatsOut = $stats["repeats_out"];
		$this->mRepeatsShow = $stats["repeats_show"];
		$this->mRepeatsSold = $stats["repeats_sold"];
		$this->mRepeatsRetail = $stats["repeats_retail"];
		$this->mRepeatsProfit = $stats["repeats_profit"];
		$this->mOthersOut = $stats["others_out"];
		$this->mOthersShow = $stats["others_show"];
		$this->mOthersSold = $stats["others_sold"];
		$this->mOthersRetail = $stats["others_retail"];
		$this->mOthersProfit = $stats["others_profit"];
		$this->Calculate();
	}

	function Calculate() {
		$this->mTotalOut = $this->mInsertsOut + $this->mSignsOut + $this->mRepeatsOut + $this->mOthersOut;
		$this->mTotalShow = $this->mInsertsShow + $this->mSignsShow + $this->mRepeatsShow + $this->mOthersShow;
		$this->mTotalSold = $this->mInsertsSold + $this->mSignsSold + $this->mRepeatsSold + $this->mOthersSold;
		$this->mTotalRetail = $this->mInsertsRetail + $this->mSignsRetail + $this->mRepeatsRetail + $this->mOthersRetail;
		$this->mTotalProfit = $this->mInsertsProfit + $this->mSignsProfit + $this->mRepeatsProfit + $this->mOthersProfit;
		$this->mInsertsPerc = $this->percent($this->divide($this->mInsertsShow, $this->mInsertsOut), 0);
		$this->mInsertsSoldPerc = $this->percent($this->divide($this->mInsertsSold, $this->mInsertsShow), 0);
		$this->mInsertsProfitPerSale = $this->divide($this->mInsertsProfit, $this->mInsertsSold);
		$this->mInsertsGrossMargin = $this->percent($this->divide($this->mInsertsProfit, $this->mInsertsRetail), 0);
		$this->mInsertsShowPerSale = $this->divide($this->mInsertsShow, $this->mInsertsSold);
		$this->mInsertsDollarsPerShow = $this->divide($this->mInsertsProfit, $this->mInsertsShow);
		$this->mSignsPerc = $this->percent($this->divide($this->mSignsShow, $this->mSignsOut), 0);
		$this->mSignsSoldPerc = $this->percent($this->divide($this->mSignsSold, $this->mSignsShow), 0);
		$this->mSignsProfitPerSale = $this->divide($this->mSignsProfit, $this->mSignsSold);
		$this->mSignsGrossMargin = $this->percent($this->divide($this->mSignsProfit, $this->mSignsRetail), 0);
		$this->mSignsShowPerSale = $this->divide($this->mSignsShow, $this->mSignsSold);
		$this->mSignsDollarsPerShow = $this->divide($this->mSignsProfit, $this->mSignsShow);
		$this->mRepeatsPerc = $this->percent($this->divide($this->mRepeatsShow, $this->mRepeatsOut), 0);
		$this->mRepeatsSoldPerc = $this->percent($this->divide($this->mRepeatsSold, $this->mRepeatsShow), 0);
		$this->mRepeatsProfitPerSale = $this->divide($this->mRepeatsProfit, $this->mRepeatsSold);
		$this->mRepeatsGrossMargin = $this->percent($this->divide($this->mRepeatsProfit, $this->mRepeatsRetail), 0);
		$this->mRepeatsShowPerSale = $this->divide($this->mRepeatsShow, $this->mRepeatsSold);
		$this->mRepeatsDollarsPerShow = $this->divide($this->mRepeatsProfit, $this->mRepeatsShow);
		$this->mOthersPerc = $this->percent($this->divide($this->mOthersShow, $this->mOthersOut), 0);
		$this->mOthersSoldPerc = $this->percent($this->divide($this->mOthersSold, $this->mOthersShow), 0);
		$this->mOthersProfitPerSale = $this->divide($this->mOthersProfit, $this->mOthersSold);
		$this->mOthersGrossMargin = $this->percent($this->divide($this->mOthersProfit, $this->mOthersRetail), 0);
		$this->mOthersShowPerSale = $this->divide($this->mOthersShow, $this->mOthersSold);
		$this->mOthersDollarsPerShow = $this->divide($this->mOthersProfit, $this->mOthersShow);
		$this->mTotalPerc = $this->percent($this->divide($this->mTotalShow, $this->mTotalOut), 0);
		$this->mTotalSoldPerc = $this->percent($this->divide($this->mTotalSold, $this->mTotalShow), 0);
		$this->mTotalProfitPerSale = $this->divide($this->mTotalProfit, $this->mTotalSold);
		$this->mTotalGrossMargin = $this->percent($this->divide($this->mTotalProfit, $this->mTotalRetail), 0);
		$this->mTotalShowPerSale = $this->divide($this->mTotalShow, $this->mTotalSold);
		$this->mTotalDollarsPerShow = $this->divide($this->mTotalProfit, $this->mTotalShow);
		$this->mInsertsProfitAsPercOfBusiness = $this->percent($this->divide($this->mInsertsProfit, $this->mTotalProfit), 0);
		$this->mSignsProfitAsPercOfBusiness = $this->percent($this->divide($this->mSignsProfit, $this->mTotalProfit), 0);
		$this->mRepeatsProfitAsPercOfBusiness = $this->percent($this->divide($this->mRepeatsProfit, $this->mTotalProfit), 0);
		$this->mOthersProfitAsPercOfBusiness = $this->percent($this->divide($this->mOthersProfit, $this->mTotalProfit), 0);
		$this->mTotalProfitAsPercOfBusiness = $this->percent($this->divide($this->mTotalProfit, $this->mTotalProfit), 0);
	}

	function divide($num1, $num2) {
    	if ($num2 == 0)
        	$return_num = 0;
    	else
        	$return_num = $num1/$num2;
    	return $return_num;
	}

	function percent($num, $decimals) {
    	$num = round($num, $decimals+2);
    	$return_num = $num * 100;
    	return $return_num;
	}

	function DaysInMonth($Year, $MonthInYear ) {
		if ( in_array ( $MonthInYear, array ( 1, 3, 5, 7, 8, 10, 12 ) ) )
			return 31;
		if ( in_array ( $MonthInYear, array ( 4, 6, 9, 11 ) ) )
			return 30;
		if ( $MonthInYear == 2 )
			return ( checkdate ( 2, 29, $Year ) ) ? 29 : 28;
		return false;
	}
}

class WodsStatsQuery {
	var $stats = array();

	function GetDates($date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($enddate)) die("WodsStats::GetDates : End Date is not timestamp");
		if ($date > $enddate) die("WodsStats::GetDates : Start is after End Date");
		if ($enddate) {
			$date = getdate($date);
			$enddate = getdate($enddate);
			if ($resolution == 'day') {
				$date = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$enddate['mday'],$enddate['year']);
			} elseif ($resolution == 'week') {
				// Week starts Sun and goes till Sat
				$date = mktime(0,0,0,$date['mon'],$date['mday'] - $date['wday'],$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$enddate['mday'] + 6 - $enddate['wday'],$enddate['year']);
			} elseif ($resolution == 'month') {
				$date = mktime(0,0,0,$date['mon'],1,$date['year']);
				$enddate = mktime(0,0,0,$enddate['mon'],$this->DaysInMonth($enddate['year'],1),$enddate['year']);
			} elseif ($resolution == 'year') {
				$date = mktime(0,0,0,1,1,$date['year']);
				$enddate = mktime(0,0,0,12,$this->DaysInMonth($enddate['year'],12),$enddate['year']);
			} else {
				die("WodsStats::LoadSum : Unknown Resolution ".$resolution.".");
			}
		} else { // No end date
			$date = getdate($date);
			if ($resolution == 'day') {
				$enddate = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
				$date = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
			} elseif ($resolution == 'week') {
				$enddate = mktime(0,0,0,$date['mon'],$date['mday'] + 6 - $date['wday'],$date['year']);
				$date = mktime(0,0,0,$date['mon'],$date['mday'] - $date['wday'],$date['year']);
			} elseif ($resolution == 'month') {
				$enddate = mktime(0,0,0,$date['mon'],$this->DaysInMonth($date['year'],$date['mon']),$date['year']);
				$date = mktime(0,0,0,$date['mon'],1,$date['year']);
			} elseif ($resolution == 'year') {
				$enddate = mktime(0,0,0,12,$this->DaysInMonth($date['year'],12),$date['year']);
				$date = mktime(0,0,0,1,1,$date['year']);
			} else {
				die("WodsStats::LoadSum : Unknown Resolution ".$resolution.".");
			}
		}
		return array('date' => $date, 'enddate' => $enddate);
	}

	function DaysInMonth($Year, $MonthInYear ) {
		if ( in_array ( $MonthInYear, array ( 1, 3, 5, 7, 8, 10, 12 ) ) )
			return 31;
		if ( in_array ( $MonthInYear, array ( 4, 6, 9, 11 ) ) )
			return 30;
		if ( $MonthInYear == 2 )
			return ( checkdate ( 2, 29, $Year ) ) ? 29 : 28;
		return false;
	}

	// Designed to take output from db_user_getlist in database.php
	function GetSums($users = array(), $start, $end = false, $resolution = 'day') {
		$this->stats = array();
		foreach ($users as $user) {
			$temp = new WodsStats();
			$temp->LoadSum($user['ID'], $start, $end, $resolution);
			$temp->mTitle = $user['first_name'].' '.$user['last_name'];
			$temp->mUserLastName = $user['last_name'];
			$temp->mUserFirstName = $user['first_name'];
			$this->stats[] = $temp;
		}
	}

	// Designed to take output from db_user_getlist in database.php
	function GetSingleSum($userid, $start, $end = false, $resolution = 'day') {
		$this->stats = array();
		$temp = new WodsStats();
		$temp->LoadSum($userid, $start, $end, $resolution);
		$user = db_user_getuserinfo($userid);
		$temp->mTitle = $user['first_name'].' '.$user['last_name'];
		$temp->mUserLastName = $user['last_name'];
		$temp->mUserFirstName = $user['first_name'];
		$this->stats[] = $temp;
	}

	function Rank($field, $order = 1) {
		if ($order) {
			for ($i = 0; $i < sizeof($this->stats); $i++)
				for ($c = 1; $c < sizeof($this->stats); $c++)
					if ($this->stats[$c]->$field < $this->stats[$c-1]->$field) {
						$temp = $this->stats[$c];
						$this->stats[$c] = $this->stats[$c-1];
						$this->stats[$c-1] = $temp;
					}
		} else {
			for ($i = 0; $i < sizeof($this->stats); $i++)
				for ($c = 1; $c < sizeof($this->stats); $c++)
					if ($this->stats[$c]->$field > $this->stats[$c-1]->$field) {
						$temp = $this->stats[$c];
						$this->stats[$c] = $this->stats[$c-1];
						$this->stats[$c-1] = $temp;
					}
		}
	}

	function AddStat($stat1, $stat2) {
		$fields = get_object_vars($stat2);
		foreach ($fields as $name => $val) {
			if (is_numeric($val))
				$stat1->$name += $stat2->$name;
			elseif (is_string($val))
				$stat1->$name = $stat1->$name . $stat2->$name;
		}
		return $stat1;
	}

	function Display($type = 'html', $limit = 0, $totaltitle = '') {
		if ($type == 'html') {
			$template = 'html.php';
		} else {
			die('Unknown Template');
		}
		if (count($this->stats) == 1) { // One Stat, No Total
			foreach($this->stats as $stat) {
				include('templates/wodsstats/'.$template);
			}
		} else { // do total 
			$i = 0;
			$total = new WodsStats();
			foreach($this->stats as $stat) {
					if ($limit != 0) {
					$i++;
					if ($i > $limit) break;
				}
				include('templates/wodsstats/'.$template);
				$total = $this->AddStat($total, $stat);
			}
			$total->Calculate();
			if ($totaltitle) {
				$total->mTitle = $totaltitle;
			} else {
				$total->mTitle = 'Total';
			}
			$stat = $total;
			include('templates/wodsstats/'.$template);
		}
	}
}

class DoWodsStats {
	// Valid Resolutions are day, week, month, year
	function GetSums($dealer, $date, $enddate = false) {
		global $link;
		if (!is_numeric($dealer)) die("DoWodsStats::GetStats : Dealer ID is not numeric");
		if (!is_numeric($date)) die("DoWodsStats::GetStats : Start Date is not timestamp");
		if ($enddate) {
			if (!is_numeric($enddate)) die("DoWodsStats::GetStats : End Date is not timestamp");
			if ($date > $enddate) die("DoSaleStats::GetStats : Start is after End Date");
		}
		// Build SQL Query
		$fields = $this->SQLFields('wodsstats',array('stat_id','user_id','stat_date'));
		$sql = "SELECT ";
		foreach ($fields as $key => $value) {
			$fields[$key] = "SUM(`".$value."`) AS `".$value."`";
		}
		$sql .= implode(", ",$fields);
		$sql .= " FROM `wodsstats` WHERE `user_id` = '".$dealer."'";
		$sql .= " AND `stat_date` >= '".$this->BuildSQLDate($date)."' AND `stat_date` <= '".$this->BuildSQLDate($enddate)."'";
		$result = mysql_query($sql, $link);
		if (!$result) die("DoWodsStats::GetStats : Query Error in '".$sql."'.");
		$return = $this->result2array($result);
		return $return;
	}

	/* private */ function SQLFields($table, $exclude = array()) {
		global $link;
		$result = mysql_query('SHOW COLUMNS FROM `'.$table.'`');
		$return = array();
		while($row = mysql_fetch_assoc($result)) {
			// Skip over exclude fields
			if (in_array($row['Field'],$exclude)) continue;
			$return[] = $row['Field'];
		}
		return $return;
	}

	/* private */ function BuildSQLDate ($timestamp) {
		return date('Y-m-d', $timestamp);
	}

	// Filler function to do what the db library already does
	// Makes it easier to convert later
	/* private */ function result2bigarray($result) {
		$return = array();
		while($row = mysql_fetch_assoc($result)) {
			$return[] = $this->TranslateArray($row);
		}
		return $return;
	}

	/* private */ function result2array($result) {
		$return = $this->TranslateArray(mysql_fetch_assoc($result));
		return $return;
	}

	/* private */ function TranslateArray($result, $reverse = false) {
		/* Since our database is read by the old code...
		 * and the database names are messed up
		 * we will perform the translation here
		 * until which time we can fix things
		 */
		// Translate Array into new Array
		return $result;
	}
}

?>
