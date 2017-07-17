<?php
session_start();
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <head>
        <meta charset="utf-8" />
        <title>BoxDrop</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700&subset=all" rel="stylesheet" />
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet"> 
        <link href="/leaderboard/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="/leaderboard/plugins/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
		<link href="/leaderboard/css/bootstrap-select.min.css" rel="stylesheet" type="text/css" />
        <link href="/leaderboard/css/components.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="/leaderboard/css/plugins.css" rel="stylesheet" type="text/css" />
        <link href="/leaderboard/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="/leaderboard/css/default.css" rel="stylesheet" type="text/css" id="style_color" />
        <link href="/leaderboard/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
        <link href="/leaderboard/css/custom.css" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" href="favicon.ico" /> 
	</head>	
	
    <body class="recognition">
        <div class="page-wrapper">

            <div class="page-header navbar navbar-static-top"> 
			
				<div class="searchbox">
					<label>Search</label>
					<input class="searchinput" value="" type="text"/>
				</div>

                <div class="page-header-inner container">
					<a class="logo" href="http://www.myboxdrop.com/"><img src="/leaderboard/img/logo.jpg" alt="" /></a>
					
					<div class="menu-wrap">
						<ul class="menu" id="topMenu">
							<li class="<?phpif ($_REQUEST['id']==27) echo 'active ';?>nav-item"><a href="/leaderboard/">Leaderboard</a></li>
							<li class="<?phpif ($_REQUEST['id']==15) echo 'active ';?>nav-item"><a href="/leaderboard/events">Events</a></li>
							<li class="<?phpif ($_REQUEST['id']=='incentive_trip') echo 'active ';?>nav-item"><a href="/leaderboard/incentive_trip">Incentive Trip</a></li>
							<li class="<?phpif ($_REQUEST['id']==26) echo 'active ';?>nav-item"><a href="/leaderboard/resources">Resources</a></li>
							<li class="nav-item"><a href="/selectvendor.php">My Dealer Page</a></li>
						</ul>	
					</div>
					
					<div class="responsive-toggler">
					<i class="fa fa-bars"></i>
					</div>
					
					<div style="float:right;padding-top:35px;font-size:22px;" class="profileIcon"><a href="/leaderboard/profile" style="color:#111;"><i class="fa fa-user"></i></a></div>
					
					<div style="clear:both;"></div>
					
				</div>
			</div>
		</div>

