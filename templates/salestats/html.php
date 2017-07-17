					<p><b><?php echo $stat->mTitle; ?></b><?php if ($stat->mShowNumRecords): ?> (<?php echo $stat->mNumRecords; ?> <?php if ($stat->mNumRecords != 1): ?>Entries<?php else: ?>Entry<?php endif; ?>)<?php endif; ?></p>
					<table width="790" border="0" cellpadding="5" cellspacing="0">
					  <tr bgcolor="#CCCC99"> 
						<td class="fat_black_12"><p>&nbsp;</p></td>
						<td class="fat_black_12"><p align="right">Calls</p></td>
						<td class="fat_black_12"><p align="right">Appts</p></td>
						<td class="fat_black_12"><p align="right">%</p></td>
						<td class="fat_black_12"><p align="right">Show</p></td>
						<td class="fat_black_12"><p align="right">%</p></td>
						<td class="fat_black_12"><p align="right">Sold</p></td>
						<td class="fat_black_12"><p align="right">%</p></td>
						<td class="fat_black_12"><p align="right">Retail</p></td>
						<td class="fat_black_12"><p align="right">Profit</p></td>
						<td class="fat_black_12"><p align="right">Profit/Sale</p></td>
						<td class="fat_black_12"><p align="right">Gross Margin</p></td>
						<td class="fat_black_12"><p align="right">Calls per Sale</p></td>
						<td class="fat_black_12"><p align="right">$ per Call</p></td>
						<td class="fat_black_12"><p align="right">Profit as % of Business</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Mattress</p></td>
						<td><p align="right"><?php echo $stat->mMattressCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mMattressShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mMattressSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mMattressRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mMattressProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mMattressProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mMattressCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mMattressProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mMattressProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Entry Furniture</p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mEntryFurnitureRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mEntryFurnitureProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mEntryFurnitureProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mEntryFurnitureProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mEntryFurnitureProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Mattress Signs</p></td>
						<td><p align="right"><?php echo $stat->mSignsCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mSignsShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mSignsSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mSignsCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mSignsProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Mattress Internet</p></td>
						<td><p align="right"><?php echo $stat->mInternetCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mInternetShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mInternetSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mInternetRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mInternetProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mInternetProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mInternetCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mInternetProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mInternetProfitPercentBusiness; ?>%</p></td>
					  </tr>
                                          <?php if ($stat->mCL) { ?>
                                          <tr bgcolor="#FFFFFF">
						<td><p align="right">Mattress CL Beta</p></td>
						<td><p align="right"><?php echo $stat->mCLCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mCLApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mCLAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mCLShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mCLShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mCLSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mCLSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mCLRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mCLProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mCLProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mCLGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mCLCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mCLProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mCLProfitPercentBusiness; ?>%</p></td>
					  </tr>
                                          <?php } ?>
					  <tr bgcolor="#FFFFFF"> 
						<td class="totalRow"><p align="right"><b>Bedding Total</b></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalCalls; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalApts; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalAptsPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalShow; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalShowPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalSold; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalSoldPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mBeddingTotalRetail); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mBeddingTotalProfit); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mBeddingTotalProfitSale); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalGrossMargin; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalCallsPerSale; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mBeddingTotalProfitPerCall); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mBeddingTotalProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Bedroom sets</p></td>
						<td><p align="right"><?php echo $stat->mBedroomCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBedroomShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBedroomSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBedroomRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBedroomProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBedroomProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBedroomCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBedroomProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mBedroomProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Living Room sets</p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mLivingRoomRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mLivingRoomProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mLivingRoomProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mLivingRoomProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mLivingRoomProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Dining Room</p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mDiningRoomRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mDiningRoomProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mDiningRoomProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mDiningRoomProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mDiningRoomProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Furniture Signs</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureSignsRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureSignsProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureSignsProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureSignsProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureSignsProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right">Furniture Internet</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureInternetRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureInternetProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureInternetProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureInternetProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureInternetProfitPercentBusiness; ?>%</p></td>
					  </tr>
                                          <?php if ($stat->mCL) { ?>
                                          <tr bgcolor="#FFFFFF">
						<td><p align="right">Furniture CL Beta</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLpts; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureCLRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureCLProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureCLProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureCLProfitPerCall); ?></p></td>
						<td><p align="right"><?php echo $stat->mFurnitureCLProfitPercentBusiness; ?>%</p></td>
					  </tr>
                                          <?php } ?>
					  <tr bgcolor="#FFFFFF"> 
						<td class="totalRow"><p align="right"><b>Furniture Total</b></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalCalls; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalApts; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalAptsPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalShow; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalShowPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalSold; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalSoldPercent; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureTotalRetail); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureTotalProfit); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureTotalProfitSale); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalGrossMargin; ?>%</p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalCallsPerSale; ?></p></td>
						<td class="totalRow"><p align="right"><?php echo makeThisLookLikeMoney($stat->mFurnitureTotalProfitPerCall); ?></p></td>
						<td class="totalRow"><p align="right"><?php echo $stat->mFurnitureTotalProfitPercentBusiness; ?>%</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF"> 
						<td><p align="right"><b>Business Total</b></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalCalls; ?></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalApts; ?></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalAptsPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalShow; ?></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalShowPercent; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalSold; ?></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalSoldPercent; ?>%</p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBusinessTotalRetail); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBusinessTotalProfit); ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBusinessTotalProfitSale); ?></p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalGrossMargin; ?>%</p></td>
						<td><p align="right"><?php echo $stat->mBusinessTotalCallsPerSale; ?></p></td>
						<td><p align="right"><?php echo makeThisLookLikeMoney($stat->mBusinessTotalProfitPerCall); ?></p></td>
						<td><p>&nbsp;</p></td>
					  </tr>
					</table>
					<br>
