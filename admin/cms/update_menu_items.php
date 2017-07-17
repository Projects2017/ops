<?php
require("../database.php");
require("../secure.php");

$strJSON = $_POST['json'];
$arrJSON = json_decode(stripslashes($strJSON));

$intLevel0Count = 1;
foreach ($arrJSON as $objLevel0){
	$arrLevel1 = $objLevel0->children;
	if ($arrLevel1 && count($arrLevel1)>0){
		$intLevel1Count = 1;
		foreach ($arrLevel1 as $objLevel1){
			$arrLevel2 = $objLevel1->children;
			if ($arrLevel2 && count($arrLevel2)>0){
				$intLevel2Count = 1;
				foreach ($arrLevel2 as $objLevel2){
					$arrLevel3 = $objLevel2->children;
					if ($arrLevel3 && count($arrLevel3)>0){
						$intLevel3Count = 1;
						foreach ($arrLevel3 as $objLevel3){
							$arrLevel4 = $objLevel3->children;
							if ($arrLevel4 && count($arrLevel4)>0){
								$intLevel4Count = 1;
								foreach ($arrLevel4 as $objLevel4){
									mysql_query("UPDATE cms_menu_items SET parent_id=".$objLevel3->id.", `menu_order`=".$intLevel4Count.", depth=4 WHERE cms_menu_item_id=".$objLevel4->id.";");
									$intLevel4Count++;
								}
							}
							mysql_query("UPDATE cms_menu_items SET parent_id=".$objLevel2->id.", `menu_order`=".$intLevel3Count.", depth=3 WHERE cms_menu_item_id=".$objLevel3->id.";");
							$intLevel3Count++;
						}
					}
					mysql_query("UPDATE cms_menu_items SET parent_id=".$objLevel1->id.", `menu_order`=".$intLevel2Count.", depth=2 WHERE cms_menu_item_id=".$objLevel2->id.";");
					$intLevel2Count++;
				}
			}
			mysql_query("UPDATE cms_menu_items SET parent_id=".$objLevel0->id.", `menu_order`=".$intLevel1Count.", depth=1 WHERE cms_menu_item_id=".$objLevel1->id.";");
			$intLevel1Count++;
		}
	}
	mysql_query("UPDATE cms_menu_items SET parent_id=0, `menu_order`=".$intLevel0Count.", depth=0 WHERE cms_menu_item_id=".$objLevel0->id.";");
	$intLevel0Count++;
}
