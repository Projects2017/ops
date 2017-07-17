<!DOCTYPE html>
<html class="">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    
    <title>boxdropbigboard</title>
    
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="./files/viewer.css">
    <link rel="stylesheet" type="text/css"
          href="./fonts/jura.css">
    <link rel="stylesheet" type="text/css" href="./fonts/ea3a6b6c96.css">
</head>
<body>
	<div id="SITE_BACKGROUND" class="siteBackground"
		style="position: absolute; top: 0px; height: 100%; width: 100%; z-index:-100;">
		<div id="SITE_BACKGROUND_current_mainPage"
			style="top: 0; height: 100%; width: 100%; background-color: rgba(255, 193, 128, 1); display:; position: fixed;"
			class="siteBackgroundcurrent">
			<div id="SITE_BACKGROUND_currentImage_mainPage"
				style='position: absolute; top: 0px; height: 100%; width: 100%; background-image: url("images/bg_blue.jpg"); background-size: cover; background-position: center top; background-repeat: no-repeat;'
				></div>
		</div>
	</div>
	<div id='container_main'>
		<div style='width:100%; height:135px; margin:0px; background-color:black;'>	
			<br style='clear:both;'/>
			<div style="width: 977px; height: 84px; position: relative; margin:15px  auto 0px auto;"  >
			
				<div  style='border: 2px solid rgba(255, 255, 255, 1);  background-color: rgba(0, 136, 203, 1); border-radius: 5px; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.6); height:84px;'>
					<div  style="top: 21px; left: 290px; width: 496px; position: relative; height: 36px;" class="s3" >
                        
                        <div class="navigation">
                        	<ul>
                        		<?php
                        			$q = mysql_query("SELECT * FROM cms_menu_items WHERE depth = 0 ORDER BY menu_order");
                        			while($row0 = mysql_fetch_array($q)){                        			                        		

										if (!empty($row0['cms_page_id'])){
											echo "<li><a href='/big-board/index.php?id=".$row0['cms_page_id']."'>".$row0['menu_text']."</a>";
										} else {
											echo "<li><a href='".$row0['menu_link']."'>".$row0['menu_text']."</a>";
										}

	                            	   $q1 = mysql_query("SELECT * FROM cms_menu_items WHERE depth = 1 AND parent_id = ".$row0['cms_menu_item_id']." ORDER BY menu_order");
	                            	   if (mysql_num_rows($q1)>0) echo "<ul>\n";
	                            	   while($row1 = mysql_fetch_array($q1)){
		                            	   	if (!empty($row1['cms_page_id'])){
		                            	   		echo "<li><a href='/big-board/index.php?id=".$row1['cms_page_id']."'>".$row1['menu_text']."</a></li>";
		                            	   	} else {
		                            	   		echo "<li><a href='".$row1['menu_link']."'>".$row1['menu_text']."</a></li>";
		                            	   	}
										}
	                            	   if (mysql_num_rows($q1)>0) echo "</ul>";
									?>
	                            	</li>
                        		
                        		<?php
                        		} ?>



                        		
                            </ul>
                        </div>
					</div> 


                    <!-- Logo Elements -->
					<div
						style="top: 5px; left: 9px; width: 271px; height: 72px; position: absolute; visibility: inherit;"
						data-exact-height="72" data-content-padding-horizontal="4"
						data-content-padding-vertical="4" title="boxdrop-1.jpg"
						class="s4" id="ichp3ycb">
						<div class="s4_left s4_shd"></div>
						<div class="s4_right s4_shd"></div>
						<div style="width: 267px; height: 68px;" id="ichp3ycblink"
							class="s4link">
							<div style="width: 267px; height: 68px; position: relative;"
								id="ichp3ycbimg" class="s4img">
								<div class="s4imgpreloader" id="ichp3ycbimgpreloader"></div>
								<img id="ichp3ycbimgimage" alt=""
									src="./files/a69938_6ba7e2cc0d5d42e6be2feb1d3951521e.jpg"
									style="width: 267px; height: 68px; object-fit: cover;">
							</div>
						</div>
					</div>
					<div
						style="top: 6px; left: 9px; width: 268px; height: 72px; position: absolute; visibility: inherit;"
						data-exact-height="72" data-content-padding-horizontal="0"
						data-content-padding-vertical="0" title="" class="s5"
						id="ichp3ycc">
						<div style="width: 268px; height: 72px;" id="ichp3ycclink"
							class="s5link">
							<div style="width: 268px; height: 72px; position: relative;"
								id="ichp3yccimg" class="s5img">
								<div class="s5imgpreloader" id="ichp3yccimgpreloader"></div>
								<img id="ichp3yccimgimage" alt=""
									src="./files/a69938_8f15ad6383694b0d8ffd29e5053f7d6c.png"
									style="width: 268px; height: 72px; object-fit: cover;">
							</div>
						</div>
					</div>
					<div
						style="top: 4px; left: 8px; width: 271px; height: 73px; position: absolute; visibility: inherit;"
						data-exact-height="73" data-content-padding-horizontal="0"
						data-content-padding-vertical="0" title="" class="s5"
						id="ichp3ycd"
						data-reactid=".0.$SITE_ROOT.$desktop_siteRoot.$SITE_HEADER.1.1.$ichp3ybq.1.$ichp3ycd">
						<div style="width: 271px; height: 73px;" id="ichp3ycdlink"
							class="s5link"
							data-reactid=".0.$SITE_ROOT.$desktop_siteRoot.$SITE_HEADER.1.1.$ichp3ybq.1.$ichp3ycd.0">
							<div style="width: 271px; height: 73px; position: relative;"
								id="ichp3ycdimg" class="s5img"
								data-reactid=".0.$SITE_ROOT.$desktop_siteRoot.$SITE_HEADER.1.1.$ichp3ybq.1.$ichp3ycd.0.0">
								<div class="s5imgpreloader" id="ichp3ycdimgpreloader"
									data-reactid=".0.$SITE_ROOT.$desktop_siteRoot.$SITE_HEADER.1.1.$ichp3ybq.1.$ichp3ycd.0.0.0"></div>
								<img id="ichp3ycdimgimage" alt=""
									src="./files/a69938_d7f36024707d45809dad10e624396d8e.png"
									style="width: 271px; height: 73px; object-fit: cover;"
									data-reactid=".0.$SITE_ROOT.$desktop_siteRoot.$SITE_HEADER.1.1.$ichp3ybq.1.$ichp3ycd.0.0.$image">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
