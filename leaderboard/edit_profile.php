<?php
require ("../database.php");
require ("../secure.php");
include ('../include/User.php');

	session_start();


if ($_POST){

	$user = User::getUserFromLogin($_COOKIE['pmd_session_id']);
	$ID = $user['ID'];
	if (!empty($_SESSION['upload_error'])){
		$_SESSION['upload_error'] = "";
	}

	if(isset($_FILES['photo']) && !empty($_FILES['photo']['name'])) {
	    $uploadfile = $_SERVER['DOCUMENT_ROOT']."/images/users/".$user['ID']."_".basename($_FILES['photo']['name']);

 		if ($_FILES['photo']['error'] == 1) {
		    $_SESSION['upload_error'] = "Profile image exceeds 2MB upload limit.";
		    Header("location: edit_profile.php");
			die();
	    }
    	    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadfile)) {
	       $photo = $user['ID']."_".basename($_FILES['photo']['name']);
	    } else {
	    }
	} else {
		$photo = $user['photo'];
	}
	
	$ID = $user['ID'];

	$phone = $_POST['phone'];
	$address = $_POST['address'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$snapshot = 0;

	/* Begin Snapshot modification */
	$sql = "SELECT `first_name`, `last_name`, `address`, `city`, `state`, `zip`, `phone`, `fax`, `address2`, `city2`, `state2`, `zip2`, `snapshot`, `snapshot2` , `photo` FROM users WHERE ID = '".$ID."'";
	$query = mysql_query($sql);
	checkDBError();
	if ($result = mysql_fetch_array($query)) {

		$snap1up = 0;
		$snap2up = 0;
		
		$first_name = $result['first_name'];
		$last_name = $result['last_name'];
		$fax = $result['fax'];

		// Do we update Snap 1?
		if ($address != $result['address']) {
			$snap1up = 1;
		}
		if ($phone != $result['phone']) {
			$snap1up = 1;
		}
		if ($city != $result['city']) {
			$snap1up = 1;
		}
		if ($state != $result['state']) {
			$snap1up = 1;
		}
		if ($zip != $result['zip']) {
			$snap1up = 1;
		}

		// Update Snap 1
		if ($snap1up) {
			$sql = "INSERT INTO snapshot_users (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `secondary`) VALUES (NULL, '".$ID."', '".$first_name."', '".$last_name."', '".$address."','', '".$city."', '".$state."', '".$zip."', '".$phone."', '".$fax."', '".$email."', 'N')";
			mysql_query($sql);
			$snapshot = mysql_insert_id();
			checkDBError();
		} else {
			$snapshot = $result['snapshot'];
		}

	}
	/* End Snapshot modification */

	$strUpdate = "UPDATE users SET
		phone = '".$_POST['phone']."',
		address = '".$_POST['address']."',
		city = '".$_POST['city']."',
		state = '".$_POST['state']."',
		zip = '".$_POST['zip']."',
		photo = '".$photo."',
		snapshot = '".$snapshot."'
	WHERE ID = '".$_POST['user_id']."';";

	mysql_query($strUpdate); 


} else {
}

$user = User::getUserFromLogin($_COOKIE['pmd_session_id']);

include 'includes/header.php';

# get User Info for logged in user

?>

<div class="container">
	<div class="page-content maincontent">
		<div class="col-md-12"><div class="page-title"><h1>EDIT PROFILE</h1></div></div>

		<?php
		if ($_POST) echo '<br><BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-info-circle"></i> <B>PROFILE UPDATED.</B><bR><BR>';

		if (!empty($_SESSION['upload_error'])){
			echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style='color:red;'>".$_SESSION['upload_error']."</font><br><br>";
		}
		?>
	
		<div class="col-md-7">
		
			<div class="content">
				
				<form method="POST" enctype="multipart/form-data">
				
				<input type="hidden" name="user_id" value="<?php=$user[ID]?>"/>
				
				<label>
				Phone Number
				</label>
				
				<input type="text" name="phone" id="phone" class="form-control" value="<?php=$user[phone]?>"/>
				
				<br>

				<label>
				Address
				</label>
				
				<input type="text" name="address" id="address" class="form-control" value="<?php=$user[address]?>"/>
				
				<br>

				<div class="row">
					<div class="col-sm-4">
					<label>
					City
					</label>
			
					<input type="text" name="city" id="city" class="form-control" value="<?php=$user[city]?>"/>
					</div>

					<div class="col-sm-4">
					<label>
					State
					</label>
			
					<input type="text" name="state" id="state" class="form-control" value="<?php=$user[state]?>"/>
					</div>

					<div class="col-sm-4">
					<label>
					ZIP
					</label>
		
					<input type="text" name="zip" id="zip" class="form-control" value="<?php=$user[zip]?>"/>
					</div>
				</div>
				
				<br>
				
				<div class="row">
				
					<div class="col-sm-12">

					<label>
					Photo (Only to Replace)
					</label>
				
					<input type="file" name="photo" id="photo" class="form-control"/>
					
					</div>
					
				</div>
				
				<br>
				
				<button class="btn btn-success">Update Profile</button>
				
				<br><br>
				
				</form>
				
			</div>
	
		</div>

		<!--<div class="col-md-4 sidebar">
			<div class="widgetbox">
				<div class="cntn">
					<p><img style="margin-left:-25px" src="img/icon_dashboard.jpg" alt="" /></p>
					<div class="line1">Incentive Trip</div>
					<div class="line2">January 9-13, 2017</div>
					<a href="javascript:void(0)" class="btn greenbtn">View Details</a>
				</div>
			</div>
		
			<div class="widgetbox">
				<div class="cntn">
					<p><img src="img/icon_graphs.jpg" alt="" /></p>
					<div class="line1">Thanksgiving Week Contest</div>
					<div class="line2">11/21 - 11/28 on all Nature Sleep sales.</div>
					<a href="javascript:void(0)" class="btn greenbtn">Register Here</a>
				</div>
			</div>
		</div>-->

	</div>
</div>

<?php include 'includes/footer.php';?>
