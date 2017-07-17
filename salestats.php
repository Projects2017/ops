<?php

class SaleStats {
	var $mTitle;
	var $mStatId;
	var $mUserId;
	var $mUserLastName;
	var $mUserFirstName;
	var $mDate;
        var $mCL = true;
        var $mShowNumRecords = false;
        var $mNumRecords = 0;
	var $mMattressCalls = 0;
	var $mMattressApts = 0;
	var $mMattressAptsPercent = 0;
	var $mMattressShow = 0;
	var $mMattressShowPercent = 0;
	var $mMattressSold = 0;
	var $mMattressSoldPercent = 0;
	var $mMattressRetail = 0;
	var $mMattressProfit = 0;
	var $mMattressProfitSale = 0;
	var $mMattressGrossMargin = 0;
	var $mMattressCallsPerSale = 0;
	var $mMattressProfitPerCall = 0;
	var $mMattressProfitPercentBusiness = 0;
	var $mEntryFurnitureCalls = 0;
	var $mEntryFurnitureApts = 0;
    var $mEntryFurnitureAptsPercent = 0;
    var $mEntryFurnitureShow = 0;
    var $mEntryFurnitureShowPercent = 0;
    var $mEntryFurnitureSold = 0;
    var $mEntryFurnitureSoldPercent = 0;
    var $mEntryFurnitureRetail = 0;
    var $mEntryFurnitureProfit = 0;
    var $mEntryFurnitureProfitSale = 0;
    var $mEntryFurnitureGrossMargin = 0;
    var $mEntryFurnitureCallsPerSale = 0;
    var $mEntryFurnitureProfitPerCall = 0;
    var $mEntryFurnitureProfitPercentBusiness = 0;
	var $mSignsCalls = 0;
    var $mSignsApts = 0;
    var $mSignsAptsPercent = 0;
    var $mSignsShow = 0;
    var $mSignsShowPercent = 0;
    var $mSignsSold = 0;
    var $mSignsSoldPercent = 0;
    var $mSignsRetail = 0;
    var $mSignsProfit = 0;
    var $mSignsProfitSale = 0;
    var $mSignsGrossMargin = 0;
    var $mSignsCallsPerSale = 0;
    var $mSignsProfitPerCall = 0;
    var $mSignsProfitPercentBusiness = 0;
    var $mInternetCalls = 0;
    var $mInternetApts = 0;
    var $mInternetAptsPercent = 0;
    var $mInternetShow = 0;
    var $mInternetShowPercent = 0;
    var $mInternetSold = 0;
    var $mInternetSoldPercent = 0;
    var $mInternetRetail = 0;
    var $mInternetProfit = 0;
    var $mInternetProfitSale = 0;
    var $mInternetGrossMargin = 0;
    var $mInternetCallsPerSale = 0;
    var $mInternetProfitPerCall = 0;
    var $mInternetProfitPercentBusiness = 0;
        var $mCLCalls = 0;
    var $mCLApts = 0;
    var $mCLAptsPercent = 0;
    var $mCLShow = 0;
    var $mCLShowPercent = 0;
    var $mCLSold = 0;
    var $mCLSoldPercent = 0;
    var $mCLRetail = 0;
    var $mCLProfit = 0;
    var $mCLProfitSale = 0;
    var $mCLGrossMargin = 0;
    var $mCLCallsPerSale = 0;
    var $mCLProfitPerCall = 0;
    var $mCLProfitPercentBusiness = 0;
	var $mBeddingTotalCalls = 0;
    var $mBeddingTotalApts = 0;
    var $mBeddingTotalAptsPercent = 0;
    var $mBeddingTotalShow = 0;
    var $mBeddingTotalShowPercent = 0;
    var $mBeddingTotalSold = 0;
    var $mBeddingTotalSoldPercent = 0;
    var $mBeddingTotalRetail = 0;
    var $mBeddingTotalProfit = 0;
    var $mBeddingTotalProfitSale = 0;
    var $mBeddingTotalGrossMargin = 0;
    var $mBeddingTotalCallsPerSale = 0;
    var $mBeddingTotalProfitPerCall = 0;
    var $mBeddingTotalProfitPercentBusiness = 0;
	var $mBedroomCalls = 0;
    var $mBedroomApts = 0;
    var $mBedroomAptsPercent = 0;
    var $mBedroomShow = 0;
    var $mBedroomShowPercent = 0;
    var $mBedroomSold = 0;
    var $mBedroomSoldPercent = 0;
    var $mBedroomRetail = 0;
    var $mBedroomProfit = 0;
    var $mBedroomProfitSale = 0;
    var $mBedroomGrossMargin = 0;
    var $mBedroomCallsPerSale = 0;
    var $mBedroomProfitPerCall = 0;
    var $mBedroomProfitPercentBusiness = 0;
	var $mLivingRoomCalls = 0;
    var $mLivingRoomApts = 0;
    var $mLivingRoomAptsPercent = 0;
    var $mLivingRoomShow = 0;
    var $mLivingRoomShowPercent = 0;
    var $mLivingRoomSold = 0;
    var $mLivingRoomSoldPercent = 0;
    var $mLivingRoomRetail = 0;
    var $mLivingRoomProfit = 0;
    var $mLivingRoomProfitSale = 0;
    var $mLivingRoomGrossMargin = 0;
    var $mLivingRoomCallsPerSale = 0;
    var $mLivingRoomProfitPerCall = 0;
    var $mLivingRoomProfitPercentBusiness = 0;
	var $mDiningRoomCalls = 0;
    var $mDiningRoomApts = 0;
    var $mDiningRoomAptsPercent = 0;
    var $mDiningRoomShow = 0;
    var $mDiningRoomShowPercent = 0;
    var $mDiningRoomSold = 0;
    var $mDiningRoomSoldPercent = 0;
    var $mDiningRoomRetail = 0;
    var $mDiningRoomProfit = 0;
    var $mDiningRoomProfitSale = 0;
    var $mDiningRoomGrossMargin = 0;
    var $mDiningRoomCallsPerSale = 0;
    var $mDiningRoomProfitPerCall = 0;
    var $mDiningRoomProfitPercentBusiness = 0;
	var $mFurnitureSignsCalls = 0;
    var $mFurnitureSignsApts = 0;
    var $mFurnitureSignsAptsPercent = 0;
    var $mFurnitureSignsShow = 0;
    var $mFurnitureSignsShowPercent = 0;
    var $mFurnitureSignsSold = 0;
    var $mFurnitureSignsSoldPercent = 0;
    var $mFurnitureSignsRetail = 0;
    var $mFurnitureSignsProfit = 0;
    var $mFurnitureSignsProfitSale = 0;
    var $mFurnitureSignsGrossMargin = 0;
    var $mFurnitureSignsCallsPerSale = 0;
    var $mFurnitureSignsProfitPerCall = 0;
    var $mFurnitureSignsProfitPercentBusiness = 0;
    var $mFurnitureInternetCalls = 0;
    var $mFurnitureInternetApts = 0;
    var $mFurnitureInternetAptsPercent = 0;
    var $mFurnitureInternetShow = 0;
    var $mFurnitureInternetShowPercent = 0;
    var $mFurnitureInternetSold = 0;
    var $mFurnitureInternetSoldPercent = 0;
    var $mFurnitureInternetRetail = 0;
    var $mFurnitureInternetProfit = 0;
    var $mFurnitureInternetProfitSale = 0;
    var $mFurnitureInternetGrossMargin = 0;
    var $mFurnitureInternetCallsPerSale = 0;
    var $mFurnitureInternetProfitPerCall = 0;
    var $mFurnitureInternetProfitPercentBusiness = 0;
        var $mFurnitureCLCalls = 0;
    var $mFurnitureCLApts = 0;
    var $mFurnitureCLAptsPercent = 0;
    var $mFurnitureCLShow = 0;
    var $mFurnitureCLShowPercent = 0;
    var $mFurnitureCLSold = 0;
    var $mFurnitureCLSoldPercent = 0;
    var $mFurnitureCLRetail = 0;
    var $mFurnitureCLProfit = 0;
    var $mFurnitureCLProfitSale = 0;
    var $mFurnitureCLGrossMargin = 0;
    var $mFurnitureCLCallsPerSale = 0;
    var $mFurnitureCLProfitPerCall = 0;
    var $mFurnitureCLProfitPercentBusiness = 0;
	var $mFurnitureTotalCalls = 0;
    var $mFurnitureTotalApts = 0;
    var $mFurnitureTotalAptsPercent = 0;
    var $mFurnitureTotalShow = 0;
    var $mFurnitureTotalShowPercent = 0;
    var $mFurnitureTotalSold = 0;
    var $mFurnitureTotalSoldPercent = 0;
    var $mFurnitureTotalRetail = 0;
    var $mFurnitureTotalProfit = 0;
    var $mFurnitureTotalProfitSale = 0;
    var $mFurnitureTotalGrossMargin = 0;
    var $mFurnitureTotalCallsPerSale = 0;
    var $mFurnitureTotalProfitPerCall = 0;
    var $mFurnitureTotalProfitPercentBusiness = 0;
	var $mBusinessTotalCalls = 0;
    var $mBusinessTotalApts = 0;
    var $mBusinessTotalAptsPercent = 0;
    var $mBusinessTotalShow = 0;
    var $mBusinessTotalShowPercent = 0;
    var $mBusinessTotalSold = 0;
    var $mBusinessTotalSoldPercent = 0;
    var $mBusinessTotalRetail = 0;
    var $mBusinessTotalProfit = 0;
    var $mBusinessTotalProfitSale = 0;
    var $mBusinessTotalGrossMargin = 0;
    var $mBusinessTotalCallsPerSale = 0;
    var $mBusinessTotalProfitPerCall = 0;
	/* private */ var $mDoSaleStats;

	function __construct() {
		$this->SaleStats();
	}

	function SaleStats() {
		$this->mDoSaleStats = new DoSaleStats();
	}

	function GetDates($date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($enddate)) die("SaleStats::GetDates : End Date is not timestamp");
		if ($date > $enddate) die("SaleStats::GetDates : Start is after End Date");
		if ($enddate) {
			if (!is_numeric($enddate)) die("SaleStats::GetDates : End Date is not timestamp");
			if ($date > $enddate) die("SaleStats::GetDates : Start is after End Date");
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
				die("SaleStats::LoadSum : Unknown Resolution ".$resolution.".");
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
				die("SaleStats::LoadSum : Unknown Resolution ".$resolution.".");
			}
		}
		return array('date' => $date, 'enddate' => $enddate);
	}

	function LoadSum($user_id, $date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($user_id)) die("SaleStats::LoadSum : Invalid Datatype for user_id (".gettype($user_id).")");
		$dates = $this->GetDates($date, $enddate, $resolution);
		$date = $dates['date'];
		$enddate = $dates['enddate'];
		// Retrive values from database
		$stats = $this->mDoSaleStats->GetSums($user_id,$date,$enddate);
		foreach ($stats as $key => $val) { // Set Nulls to 0
			if (is_null($val)) {
				$stats[$key] = 0;
			}
		}
                $this->mNumRecords = $stats['num_records'];
		$this->mMattressCalls = $stats["mattress_calls"];
		$this->mMattressApts = $stats["mattress_appts"];
		$this->mMattressShow = $stats["mattress_show"];
		$this->mMattressSold = $stats["mattress_sold"];
		$this->mMattressRetail = $stats["mattress_retail"];
		$this->mMattressProfit = $stats["mattress_profit"];
		$this->mEntryFurnitureCalls = $stats["entryfurniture_calls"];
		$this->mEntryFurnitureApts = $stats["entryfurniture_appts"];
		$this->mEntryFurnitureShow = $stats["entryfurniture_show"];
		$this->mEntryFurnitureSold = $stats["entryfurniture_sold"];
		$this->mEntryFurnitureRetail = $stats["entryfurniture_retail"];
		$this->mEntryFurnitureProfit = $stats["entryfurniture_profit"];
		$this->mSignsCalls = $stats["bedding_signs_calls"];
		$this->mSignsApts = $stats["bedding_signs_appts"];
		$this->mSignsShow = $stats["bedding_signs_show"];
		$this->mSignsSold = $stats["bedding_signs_sold"];
		$this->mSignsRetail = $stats["bedding_signs_retail"];
		$this->mSignsProfit = $stats["bedding_signs_profit"];
		$this->mInternetCalls = $stats["bedding_internet_calls"];
		$this->mInternetApts = $stats["bedding_internet_appts"];
		$this->mInternetShow = $stats["bedding_internet_show"];
		$this->mInternetSold = $stats["bedding_internet_sold"];
		$this->mInternetRetail = $stats["bedding_internet_retail"];
		$this->mInternetProfit = $stats["bedding_internet_profit"];
                $this->mCLCalls = $stats["bedding_craigslist_calls"];
		$this->mCLApts = $stats["bedding_craigslist_appts"];
		$this->mCLShow = $stats["bedding_craigslist_show"];
		$this->mCLSold = $stats["bedding_craigslist_sold"];
		$this->mCLRetail = $stats["bedding_craigslist_retail"];
		$this->mCLProfit = $stats["bedding_craigslist_profit"];
		$this->mBedroomCalls = $stats["bedroom_calls"];
		$this->mBedroomApts = $stats["bedroom_appts"];
		$this->mBedroomShow = $stats["bedroom_show"];
		$this->mBedroomSold = $stats["bedroom_sold"];
		$this->mBedroomRetail = $stats["bedroom_retail"];
		$this->mBedroomProfit = $stats["bedroom_profit"];
		$this->mLivingRoomCalls = $stats["living_calls"];
		$this->mLivingRoomApts = $stats["living_appts"];
		$this->mLivingRoomShow = $stats["living_show"];
		$this->mLivingRoomSold = $stats["living_sold"];
		$this->mLivingRoomRetail = $stats["living_retail"];
		$this->mLivingRoomProfit = $stats["living_profit"];
		$this->mDiningRoomCalls = $stats["dining_calls"];
		$this->mDiningRoomApts = $stats["dining_appts"];
		$this->mDiningRoomShow = $stats["dining_show"];
		$this->mDiningRoomSold = $stats["dining_sold"];
		$this->mDiningRoomRetail = $stats["dining_retail"];
		$this->mDiningRoomProfit = $stats["dining_profit"];
		$this->mFurnitureSignsCalls = $stats["furniture_signs_calls"];
		$this->mFurnitureSignsApts = $stats["furniture_signs_appts"];
		$this->mFurnitureSignsShow = $stats["furniture_signs_show"];
		$this->mFurnitureSignsSold = $stats["furniture_signs_sold"];
		$this->mFurnitureSignsRetail = $stats["furniture_signs_retail"];
		$this->mFurnitureSignsProfit = $stats["furniture_signs_profit"];
                $this->mFurnitureInternetCalls = $stats["furniture_internet_calls"];
		$this->mFurnitureInternetApts = $stats["furniture_internet_appts"];
		$this->mFurnitureInternetShow = $stats["furniture_internet_show"];
		$this->mFurnitureInternetSold = $stats["furniture_internet_sold"];
		$this->mFurnitureInternetRetail = $stats["furniture_internet_retail"];
		$this->mFurnitureInternetProfit = $stats["furniture_internet_profit"];
		$this->mFurnitureCLCalls = $stats["furniture_craigslist_calls"];
		$this->mFurnitureCLApts = $stats["furniture_craigslist_appts"];
		$this->mFurnitureCLShow = $stats["furniture_craigslist_show"];
		$this->mFurnitureCLSold = $stats["furniture_craigslist_sold"];
		$this->mFurnitureCLRetail = $stats["furniture_craigslist_retail"];
		$this->mFurnitureCLProfit = $stats["furniture_craigslist_profit"];
                if ($this->mCLCalls > 0 || $this->mCLApts > 0
                        || $this->mCLShow > 0 || $this->mCLSold > 0
                        || $this->mCLRetail > 0 || $this->mCLProfit > 0
                        || $this->mFurnitureCLCalls > 0 || $this->mFurnitureCLApts > 0
                        || $this->mFurnitureCLShow > 0 || $this->mFurnitureCLSold > 0
                        || $this->mFurnitureCLRetail > 0 || $this->mFurnitureCLProfit > 0) {
                $this->mCL = true;
                }
		$this->Calculate();
	}

	function Calculate() {
		// Mattresses Row
		$this->mMattressAptsPercent = $this->percent($this->divide($this->mMattressApts, $this->mMattressCalls),0);
		$this->mMattressShowPercent = $this->percent($this->divide($this->mMattressShow, $this->mMattressApts),1);
		$this->mMattressSoldPercent = $this->percent($this->divide($this->mMattressSold, $this->mMattressShow),1);
		$this->mMattressProfitSale = round($this->divide($this->mMattressProfit, $this->mMattressSold),2);
		$this->mMattressGrossMargin = $this->percent($this->divide($this->mMattressProfit, $this->mMattressRetail), 1);
		$this->mMattressCallsPerSale = round($this->divide($this->mMattressCalls, $this->mMattressSold),2);
		$this->mMattressProfitPerCall = round($this->divide($this->mMattressProfit, $this->mMattressCalls),2);
		// Entry Furniture Row
		$this->mEntryFurnitureAptsPercent = $this->percent($this->divide($this->mEntryFurnitureApts, $this->mEntryFurnitureCalls),0);
        $this->mEntryFurnitureShowPercent = $this->percent($this->divide($this->mEntryFurnitureShow, $this->mEntryFurnitureApts),1);
        $this->mEntryFurnitureSoldPercent = $this->percent($this->divide($this->mEntryFurnitureSold, $this->mEntryFurnitureShow),1);
        $this->mEntryFurnitureProfitSale = round($this->divide($this->mEntryFurnitureProfit, $this->mEntryFurnitureSold),2);
		$this->mEntryFurnitureGrossMargin = $this->percent($this->divide($this->mEntryFurnitureProfit, $this->mEntryFurnitureRetail), 1);
        $this->mEntryFurnitureCallsPerSale = round($this->divide($this->mEntryFurnitureCalls, $this->mEntryFurnitureSold),2);
        $this->mEntryFurnitureProfitPerCall = round($this->divide($this->mEntryFurnitureProfit, $this->mEntryFurnitureCalls),2);
		// Mattress Signs Row
		$this->mSignsAptsPercent = $this->percent($this->divide($this->mSignsApts, $this->mSignsCalls),0);
        $this->mSignsShowPercent = $this->percent($this->divide($this->mSignsShow, $this->mSignsApts),1);
        $this->mSignsSoldPercent = $this->percent($this->divide($this->mSignsSold, $this->mSignsShow),1);
		$this->mSignsGrossMargin = $this->percent($this->divide($this->mSignsProfit, $this->mSignsRetail), 1);
        $this->mSignsProfitSale = round($this->divide($this->mSignsProfit, $this->mSignsSold),2);
        $this->mSignsCallsPerSale = round($this->divide($this->mSignsCalls, $this->mSignsSold),2);
        $this->mSignsProfitPerCall = round($this->divide($this->mSignsProfit, $this->mSignsCalls),2);
        // Mattress Internet Row
		$this->mInternetAptsPercent = $this->percent($this->divide($this->mInternetApts, $this->mInternetCalls),0);
        $this->mInternetShowPercent = $this->percent($this->divide($this->mInternetShow, $this->mInternetApts),1);
        $this->mInternetSoldPercent = $this->percent($this->divide($this->mInternetSold, $this->mInternetShow),1);
		$this->mInternetGrossMargin = $this->percent($this->divide($this->mInternetProfit, $this->mInternetRetail), 1);
        $this->mInternetProfitSale = round($this->divide($this->mInternetProfit, $this->mInternetSold),2);
        $this->mInternetCallsPerSale = round($this->divide($this->mInternetCalls, $this->mInternetSold),2);
        $this->mInternetProfitPerCall = round($this->divide($this->mInternetProfit, $this->mInternetCalls),2);
                // Mattress Craigs List Row
		$this->mCLAptsPercent = $this->percent($this->divide($this->mCLApts, $this->mCLCalls),0);
		$this->mCLShowPercent = $this->percent($this->divide($this->mCLShow, $this->mCLApts),1);
		$this->mCLSoldPercent = $this->percent($this->divide($this->mCLSold, $this->mCLShow),1);
		$this->mCLGrossMargin = $this->percent($this->divide($this->mCLProfit, $this->mCLRetail), 1);
		$this->mCLProfitSale = round($this->divide($this->mCLProfit, $this->mCLSold),2);
		$this->mCLCallsPerSale = round($this->divide($this->mCLCalls, $this->mCLSold),2);
		$this->mCLProfitPerCall = round($this->divide($this->mCLProfit, $this->mCLCalls),2);
		// Bedding Totals
		$this->mBeddingTotalCalls = $this->mMattressCalls + $this->mEntryFurnitureCalls + $this->mSignsCalls + $this->mInternetCalls + $this->mCLCalls;
		$this->mBeddingTotalApts = $this->mMattressApts + $this->mEntryFurnitureApts + $this->mSignsApts + $this->mInternetApts + $this->mCLApts;
		$this->mBeddingTotalAptsPercent = $this->percent($this->divide($this->mBeddingTotalApts, $this->mBeddingTotalCalls), 0);
		$this->mBeddingTotalShow = $this->mMattressShow + $this->mEntryFurnitureShow + $this->mSignsShow + $this->mInternetShow + $this->mCLShow;
		$this->mBeddingTotalShowPercent = $this->percent($this->divide($this->mBeddingTotalShow, $this->mBeddingTotalApts), 0);
		$this->mBeddingTotalSold = $this->mMattressSold + $this->mEntryFurnitureSold + $this->mSignsSold + $this->mInternetSold + $this->mCLSold;
		$this->mBeddingTotalSoldPercent = $this->percent($this->divide($this->mBeddingTotalSold, $this->mBeddingTotalShow), 0);
		$this->mBeddingTotalRetail = $this->mMattressRetail + $this->mEntryFurnitureRetail + $this->mSignsRetail + $this->mInternetRetail + $this->mCLRetail;
		$this->mBeddingTotalProfit = $this->mMattressProfit + $this->mEntryFurnitureProfit + $this->mSignsProfit + $this->mInternetProfit + $this->mCLProfit;
		$this->mBeddingTotalGrossMargin = $this->percent($this->divide($this->mBeddingTotalProfit, $this->mBeddingTotalRetail), 1);
		$this->mBeddingTotalProfitSale = round($this->divide($this->mBeddingTotalProfit, $this->mBeddingTotalSold), 2);
		$this->mBeddingTotalCallsPerSale = round($this->divide($this->mBeddingTotalCalls, $this->mBeddingTotalSold), 2);
		$this->mBeddingTotalProfitPerCall = round($this->divide($this->mBeddingTotalProfit, $this->mBeddingTotalCalls), 2);
		// Bedroom Row
		$this->mBedroomAptsPercent = $this->percent($this->divide($this->mBedroomApts, $this->mBedroomCalls),0);
		$this->mBedroomShowPercent = $this->percent($this->divide($this->mBedroomShow, $this->mBedroomApts),1);
		$this->mBedroomSoldPercent = $this->percent($this->divide($this->mBedroomSold, $this->mBedroomShow),1);
		$this->mBedroomGrossMargin = $this->percent($this->divide($this->mBedroomProfit, $this->mBedroomRetail), 1);
		$this->mBedroomProfitSale = round($this->divide($this->mBedroomProfit, $this->mBedroomSold),2);
		$this->mBedroomCallsPerSale = round($this->divide($this->mBedroomCalls, $this->mBedroomSold),2);
		$this->mBedroomProfitPerCall = round($this->divide($this->mBedroomProfit, $this->mBedroomCalls),2);
		// Living Room Row
		$this->mLivingRoomAptsPercent = $this->percent($this->divide($this->mLivingRoomApts, $this->mLivingRoomCalls),0);
		$this->mLivingRoomShowPercent = $this->percent($this->divide($this->mLivingRoomShow, $this->mLivingRoomApts),1);
		$this->mLivingRoomSoldPercent = $this->percent($this->divide($this->mLivingRoomSold, $this->mLivingRoomShow),1);
		$this->mLivingRoomGrossMargin = $this->percent($this->divide($this->mLivingRoomProfit, $this->mLivingRoomRetail), 1);
		$this->mLivingRoomProfitSale = round($this->divide($this->mLivingRoomProfit, $this->mLivingRoomSold),2);
		$this->mLivingRoomCallsPerSale = round($this->divide($this->mLivingRoomCalls, $this->mLivingRoomSold),2);
		$this->mLivingRoomProfitPerCall = round($this->divide($this->mLivingRoomProfit, $this->mLivingRoomCalls),2);
		// Dining Room Row
		$this->mDiningRoomAptsPercent = $this->percent($this->divide($this->mDiningRoomApts, $this->mDiningRoomCalls),0);
		$this->mDiningRoomShowPercent = $this->percent($this->divide($this->mDiningRoomShow, $this->mDiningRoomApts),1);
		$this->mDiningRoomSoldPercent = $this->percent($this->divide($this->mDiningRoomSold, $this->mDiningRoomShow),1);
		$this->mDiningRoomGrossMargin = $this->percent($this->divide($this->mDiningRoomProfit, $this->mDiningRoomRetail), 1);
		$this->mDiningRoomProfitSale = round($this->divide($this->mDiningRoomProfit, $this->mDiningRoomSold),2);
		$this->mDiningRoomCallsPerSale = round($this->divide($this->mDiningRoomCalls, $this->mDiningRoomSold),2);
		$this->mDiningRoomProfitPerCall = round($this->divide($this->mDiningRoomProfit, $this->mDiningRoomCalls),2);
		// Furniture Signs Row
		$this->mFurnitureSignsAptsPercent = $this->percent($this->divide($this->mFurnitureSignsApts, $this->mFurnitureSignsCalls),0);
		$this->mFurnitureSignsShowPercent = $this->percent($this->divide($this->mFurnitureSignsShow, $this->mFurnitureSignsApts),1);
		$this->mFurnitureSignsSoldPercent = $this->percent($this->divide($this->mFurnitureSignsSold, $this->mFurnitureSignsShow),1);
		$this->mFurnitureSignsGrossMargin = $this->percent($this->divide($this->mFurnitureSignsProfit, $this->mFurnitureSignsRetail), 1);
		$this->mFurnitureSignsProfitSale = round($this->divide($this->mFurnitureSignsProfit, $this->mFurnitureSignsSold),2);
		$this->mFurnitureSignsCallsPerSale = round($this->divide($this->mFurnitureSignsCalls, $this->mFurnitureSignsSold),2);
		$this->mFurnitureSignsProfitPerCall = round($this->divide($this->mFurnitureSignsProfit, $this->mFurnitureSignsCalls),2);
		// Furniture Internet Row
		$this->mFurnitureInternetAptsPercent = $this->percent($this->divide($this->mFurnitureInternetApts, $this->mFurnitureInternetCalls),0);
		$this->mFurnitureInternetShowPercent = $this->percent($this->divide($this->mFurnitureInternetShow, $this->mFurnitureInternetApts),1);
		$this->mFurnitureInternetSoldPercent = $this->percent($this->divide($this->mFurnitureInternetSold, $this->mFurnitureInternetShow),1);
		$this->mFurnitureInternetGrossMargin = $this->percent($this->divide($this->mFurnitureInternetProfit, $this->mFurnitureInternetRetail), 1);
		$this->mFurnitureInternetProfitSale = round($this->divide($this->mFurnitureInternetProfit, $this->mFurnitureInternetSold),2);
		$this->mFurnitureInternetCallsPerSale = round($this->divide($this->mFurnitureInternetCalls, $this->mFurnitureInternetSold),2);
		$this->mFurnitureInternetProfitPerCall = round($this->divide($this->mFurnitureInternetProfit, $this->mFurnitureInternetCalls),2);
                // Furniture Craigs List Row
		$this->mFurnitureCLAptsPercent = $this->percent($this->divide($this->mFurnitureCLApts, $this->mFurnitureCLCalls),0);
		$this->mFurnitureCLShowPercent = $this->percent($this->divide($this->mFurnitureCLShow, $this->mFurnitureCLApts),1);
		$this->mFurnitureCLSoldPercent = $this->percent($this->divide($this->mFurnitureCLSold, $this->mFurnitureCLShow),1);
		$this->mFurnitureCLGrossMargin = $this->percent($this->divide($this->mFurnitureCLProfit, $this->mFurnitureCLRetail), 1);
		$this->mFurnitureCLProfitSale = round($this->divide($this->mFurnitureCLProfit, $this->mFurnitureCLSold),2);
		$this->mFurnitureCLCallsPerSale = round($this->divide($this->mFurnitureCLCalls, $this->mFurnitureCLSold),2);
		$this->mFurnitureCLProfitPerCall = round($this->divide($this->mFurnitureCLProfit, $this->mFurnitureCLCalls),2);
		// Furniture Total
		$this->mFurnitureTotalCalls = $this->mBedroomCalls + $this->mLivingRoomCalls + $this->mDiningRoomCalls + 
				$this->mFurnitureSignsCalls + $this->mFurnitureInternetCalls + $this->mFurnitureCLCalls;
		$this->mFurnitureTotalApts = $this->mBedroomApts + $this->mLivingRoomApts + $this->mDiningRoomApts + 
				$this->mFurnitureSignsApts + $this->mFurnitureInternetApts + $this->mFurnitureCLApts;
		$this->mFurnitureTotalAptsPercent = $this->percent($this->divide($this->mFurnitureTotalApts, $this->mFurnitureTotalCalls), 0);
		$this->mFurnitureTotalShow = $this->mBedroomShow + $this->mLivingRoomShow + $this->mDiningRoomShow +
				$this->mFurnitureSignsShow + $this->mFurnitureInternetShow + $this->mFurnitureCLShow;
		$this->mFurnitureTotalShowPercent = $this->percent($this->divide($this->mFurnitureTotalShow, $this->mFurnitureTotalApts), 0);
		$this->mFurnitureTotalSold = $this->mBedroomSold + $this->mLivingRoomSold + $this->mDiningRoomSold + 
				$this->mFurnitureSignsSold + $this->mFurnitureInternetSold + $this->mFurnitureCLSold;
		$this->mFurnitureTotalSoldPercent = $this->percent($this->divide($this->mFurnitureTotalSold, $this->mFurnitureTotalShow), 0);
		$this->mFurnitureTotalRetail = $this->mBedroomRetail + $this->mLivingRoomRetail + $this->mDiningRoomRetail + 
				$this->mFurnitureSignsRetail + $this->mFurnitureInternetRetail + $this->mFurnitureCLRetail;
		$this->mFurnitureTotalProfit = $this->mBedroomProfit + $this->mLivingRoomProfit + $this->mDiningRoomProfit + 
				$this->mFurnitureSignsProfit + $this->mFurnitureInternetProfit + $this->mFurnitureCLProfit;
		$this->mFurnitureTotalProfitSale = round($this->divide($this->mFurnitureTotalProfit, $this->mFurnitureTotalSold), 2);
		$this->mFurnitureTotalGrossMargin = $this->percent($this->divide($this->mFurnitureTotalProfit, $this->mFurnitureTotalRetail), 1);
		$this->mFurnitureTotalCallsPerSale = round($this->divide($this->mFurnitureTotalCalls, $this->mFurnitureTotalSold), 2);
		$this->mFurnitureTotalProfitPerCall = round($this->divide($this->mFurnitureTotalProfit, $this->mFurnitureTotalCalls), 2);
		// Business Total
		$this->mBusinessTotalCalls = $this->mBeddingTotalCalls + $this->mFurnitureTotalCalls;
		$this->mBusinessTotalApts = $this->mBeddingTotalApts + $this->mFurnitureTotalApts;
		$this->mBusinessTotalAptsPercent = $this->percent($this->divide($this->mBusinessTotalApts, $this->mBusinessTotalCalls), 0);
		$this->mBusinessTotalShow = $this->mBeddingTotalShow + $this->mFurnitureTotalShow;
		$this->mBusinessTotalShowPercent = $this->percent($this->divide($this->mBusinessTotalShow, $this->mBusinessTotalApts), 0);
		$this->mBusinessTotalSold = $this->mBeddingTotalSold + $this->mFurnitureTotalSold;
		$this->mBusinessTotalSoldPercent = $this->percent($this->divide($this->mBusinessTotalSold, $this->mBusinessTotalShow), 0);
		$this->mBusinessTotalRetail = $this->mBeddingTotalRetail + $this->mFurnitureTotalRetail;
		$this->mBusinessTotalProfit = $this->mBeddingTotalProfit + $this->mFurnitureTotalProfit;
		$this->mBusinessTotalProfitSale = round($this->divide($this->mBusinessTotalProfit, $this->mBusinessTotalSold), 2);
		$this->mBusinessTotalGrossMargin = $this->percent($this->divide($this->mBusinessTotalProfit, $this->mBusinessTotalRetail), 1);
		$this->mBusinessTotalCallsPerSale = round($this->divide($this->mBusinessTotalCalls, $this->mBusinessTotalSold), 2);
		$this->mBusinessTotalProfitPerCall = round($this->divide($this->mBusinessTotalProfit, $this->mBusinessTotalCalls), 2);
		// Profit as % of Business (Dependent on other calcs, thus done last)
		$this->mMattressProfitPercentBusiness = $this->percent($this->divide($this->mMattressProfit, $this->mBusinessTotalProfit), 1);
		$this->mEntryFurnitureProfitPercentBusiness = $this->percent($this->divide($this->mEntryFurnitureProfit, $this->mBusinessTotalProfit), 1);
		$this->mSignsProfitPercentBusiness = $this->percent($this->divide($this->mSignsProfit, $this->mBusinessTotalProfit), 1);
		$this->mInternetProfitPercentBusiness = $this->percent($this->divide($this->mInternetProfit, $this->mBusinessTotalProfit), 1);
		$this->mCLProfitPercentBusiness = $this->percent($this->divide($this->mCLProfit, $this->mBusinessTotalProfit), 1);
		$this->mBeddingTotalProfitPercentBusiness = $this->percent($this->divide($this->mBeddingTotalProfit, $this->mBusinessTotalProfit), 1);
		$this->mBedroomProfitPercentBusiness = $this->percent($this->divide($this->mBedroomProfit, $this->mBusinessTotalProfit), 1);
		$this->mLivingRoomProfitPercentBusiness = $this->percent($this->divide($this->mLivingRoomProfit, $this->mBusinessTotalProfit), 1);
		$this->mDiningRoomProfitPercentBusiness = $this->percent($this->divide($this->mDiningRoomProfit, $this->mBusinessTotalProfit), 1);
		$this->mFurnitureSignsProfitPercentBusiness = $this->percent($this->divide($this->mFurnitureSignsProfit, $this->mBusinessTotalProfit), 1);
		$this->mFurnitureInternetProfitPercentBusiness = $this->percent($this->divide($this->mFurnitureInternetProfit, $this->mBusinessTotalProfit), 1);
		$this->mFurnitureCLProfitPercentBusiness = $this->percent($this->divide($this->mFurnitureCLProfit, $this->mBusinessTotalProfit), 1);
		$this->mFurnitureTotalProfitPercentBusiness = $this->percent($this->divide($this->mFurnitureTotalProfit, $this->mBusinessTotalProfit), 1);
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

        static function inCLBeta($user) {
            if (!is_numeric($user)) die("salestats.php: inCLBeta requires numeric input.");
            $sql = "SELECT `clbeta`, `dealer_type` FROM `users` WHERE `ID` = '".$user."'";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['clbeta'] == 'Y') {
                    if ($row['dealer_type'] == 'F' || $row['dealer_type'] == 'B') {
                        return true;
                    }
                }
            }
            return false;
        }
        
        static function isFranchise($user) {
            if (!is_numeric($user)) die("salestats.php: inFranchise requires numeric input.");
            $sql = "SELECT `dealer_type` FROM `users` WHERE `ID` = '".$user."'";
            $result = mysql_query($sql);
            checkDBerror($sql);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['dealer_type'] == 'F' || $row['dealer_type'] == 'B') {
                    return true;
                }
            }
            return false;
        }
}

class SaleStatsQuery {
	var $stats = array();

	function GetDates($date, $enddate = false, $resolution = 'day') {
		if (!is_numeric($enddate)) die("SaleStats::GetDates : End Date is not timestamp");
		if ($date > $enddate) die("SaleStats::GetDates : Start is after End Date");
		if ($enddate) {
			if (!is_numeric($enddate)) die("SaleStats::GetDates : End Date is not timestamp");
			if ($date > $enddate) die("SaleStats::GetDates : Start is after End Date");
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
				die("SaleStats::LoadSum : Unknown Resolution ".$resolution.".");
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
				die("SaleStats::LoadSum : Unknown Resolution ".$resolution.".");
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
	function GetSums($users = array(), $start, $end = false, $resolution = 'day', $hideNoEntries = false) {
		$this->stats = array();
		foreach ($users as $user) {
			$temp = new SaleStats();
			$temp->LoadSum($user['ID'], $start, $end, $resolution);
			$temp->mTitle = $user['first_name'].' '.$user['last_name'];
			$temp->mUserLastName = $user['last_name'];
			$temp->mUserFirstName = $user['first_name'];
                        if ($hideNoEntries && $temp->mNumRecords == 0)
                                continue;
			$this->stats[] = $temp;
		}
	}

	// Designed to take output from db_user_getlist in database.php
	function GetSingleSum($userid, $start, $end = false, $resolution = 'day') {
		$this->stats = array();
		$temp = new SaleStats();
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

	function Display($type = 'html', $limit = 0, $totaltitle = '', $showentry = false) {
		if ($type == 'html') {
			$template = 'html.php';
		} else {
			die('Unknown Template');
		}
		if (count($this->stats) == 1) { // One Stat, No Total
			foreach($this->stats as $stat) {
                                $stat->mShowNumRecords = $showentry;
				include('templates/salestats/'.$template);
			}
		} else { // do total 
			$i = 0;
			$total = new SaleStats();
			foreach($this->stats as $stat) {
					if ($limit != 0) {
					$i++;
					if ($i > $limit) break;
				}
                                $stat->mShowNumRecords = $showentry;
				include('templates/salestats/'.$template);
				$total = $this->AddStat($total, $stat);
			}
			$total->Calculate();
			if ($totaltitle) {
				$total->mTitle = $totaltitle;
			} else {
				$total->mTitle = 'Total';
			}
			$stat = $total;
			include('templates/salestats/'.$template);
		}
	}
}

class DoSaleStats {
	// Valid Resolutions are day, week, month, year
	function GetSums($dealer, $date, $enddate = false) {
		global $link;
		if (!is_numeric($dealer)) die("DoSaleStats::GetStats : Dealer ID is not numeric");
		if (!is_numeric($date)) die("DoSaleStats::GetStats : Start Date is not timestamp");
		if ($enddate) {
			if (!is_numeric($enddate)) die("DoSaleStats::GetStats : End Date is not timestamp");
			if ($date > $enddate) die("DoSaleStats::GetStats : Start is after End Date");
		}
		// Build SQL Query
		$fields = $this->SQLFields('salestats',array('stat_id','user_id','stat_date'));
		$sql = "SELECT COUNT(`stat_id`) as `num_records`,";
		foreach ($fields as $key => $value) {
			$fields[$key] = "SUM(`".$value."`) AS `".$value."`";
		}
		$sql .= implode(", ",$fields);
		$sql .= " FROM `salestats` WHERE `user_id` = '".$dealer."'";
		$sql .= " AND `stat_date` >= '".$this->BuildSQLDate($date)."' AND `stat_date` <= '".$this->BuildSQLDate($enddate)."'";
		$result = mysql_query($sql, $link);
		if (!$result) die("DoSaleStats::GetStats : Query Error in '".$sql."'.");
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
		$return = array();
		if (!$reverse) { // If forward...
			foreach ($result as $key => $value) {
				if (substr($key,0,4) == 'ads_') {
					$return['mattress_'.substr($key,4)] = $value;
				} elseif (substr($key,0,9) == 'babycase_') {
					$return['entryfurniture_'.substr($key,9)] = $value;
				} elseif (substr($key,0,3) == 'cg_') {
					$return['furniture_'.substr($key,3)] = $value;
				} else {
					$return[$key] = $value;
				}
			}
		} else { // If reverse...
			foreach ($result as $key => $value) {
				if (substr($key,0,9) == 'mattress_') {
					$return['ads_'.substr($key,9)] = $value;
				} elseif (substr($key,0,15) == 'entryfurniture_') {
					$return['babycase_'.substr($key,15)] = $value;
				} elseif (substr($key,0,10) == 'furniture_') {
					$return['cg_'.substr($key,10)] = $value;
				} else {
					$return[$key] = $value;
				}
			}
		}
		return $return;
	}
}

?>
