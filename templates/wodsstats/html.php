					<p><b><?php echo $stat->mTitle; ?></b></p>
					<table width="790" border="0" cellpadding="5" cellspacing="0">
					  <tr bgcolor="#CCCC99">
						<td>&nbsp;</td> 
						<td class="fat_black_12"><p align="right"># Out</p></td>
						<td class="fat_black_12"><p align="right">Show</p></td>
						<td class="fat_black_12"><p align="right">%</p></td>
						<td class="fat_black_12"><p align="right">Sold</p></td>
						<td class="fat_black_12"><p align="right">%</p></td>
						<td class="fat_black_12"><p align="right">Retail</p></td>
						<td class="fat_black_12"><p align="right">Profit</p></td>
						<td class="fat_black_12"><p align="right">Profit/Sale</p></td>
						<td class="fat_black_12"><p align="right">Gross<br />Margin</p></td>
						<td class="fat_black_12"><p align="right">Show per<br />Sale</p></td>
						<td class="fat_black_12"><p align="right">$ Per<br />Show</p></td>
						<td class="fat_black_12"><p align="right">Profit as<br />% of<br />Business</p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF">
						<td class="fat_black_12" bgcolor="#CCCC99">Inserts</td> 
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsOut; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsShow; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsSold; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsSoldPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mInsertsRetail); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mInsertsProfit); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mInsertsProfitPerSale); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mInsertsGrossMargin.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mInsertsShowPerSale, 1); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mInsertsDollarsPerShow); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mInsertsProfitAsPercOfBusiness, 0).'%'; ?></p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF">
						<td class="fat_black_12" bgcolor="#CCCC99">Signs</td> 
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsOut; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsShow; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsSold; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsSoldPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsRetail); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsProfit); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsProfitPerSale); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mSignsGrossMargin.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mSignsShowPerSale, 1); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mSignsDollarsPerShow); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mSignsProfitAsPercOfBusiness, 0).'%'; ?></p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF">
						<td class="fat_black_12" bgcolor="#CCCC99">Repeat Custs</td> 
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsOut; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsShow; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsSold; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsSoldPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mRepeatsRetail); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mRepeatsProfit); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mRepeatsProfitPerSale); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mRepeatsGrossMargin.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mRepeatsShowPerSale, 1); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mRepeatsDollarsPerShow); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mRepeatsProfitAsPercOfBusiness, 0).'%'; ?></p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF">
						<td class="fat_black_12" bgcolor="#CCCC99">Others</td> 
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersOut; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersShow; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersSold; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersSoldPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mOthersRetail); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mOthersProfit); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mOthersProfitPerSale); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mOthersGrossMargin.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mOthersShowPerSale, 1); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mOthersDollarsPerShow); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mOthersProfitAsPercOfBusiness, 0).'%'; ?></p></td>
					  </tr>
					  <tr bgcolor="#FFFFFF">
						<td class="fat_black_12" bgcolor="#CCCC99" style="font-weight: bold">WODS Total</td> 
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalOut; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalShow; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalSold; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalSoldPerc.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mTotalRetail); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mTotalProfit); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mTotalProfitPerSale); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo $stat->mTotalGrossMargin.'%'; ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mTotalShowPerSale, 1); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo makeThisLookLikeMoney($stat->mTotalDollarsPerShow); ?></p></td>
						<td class="fat_black_12"><p align="right"><?php echo number_format($stat->mTotalProfitAsPercOfBusiness, 0).'%'; ?></p></td>
					  </tr>
					</table>
					<br>
