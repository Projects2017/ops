<?php
// do_manageagents.php
// script to manage user agents
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');
// we're going to start with the add function
// first, i need to see what we have
//var_dump($_POST);
switch($_POST['agentmode'])
{
	case 'add':
		// adding a new user to the db
		// first we need to validate the data
		$errs = Array();
		if($_POST['last_name']=='' || $_POST['last_name']=='[Agent Name]')
		{
			// nothing's been entered
			$errs[] = 'last_name';
			$errortxt[] = 'Agent Name is invalid';
		}
		if($_POST['address']=='' || $_POST['address']=='[Address]')
		{
			// address is invalid
			$errs[] = 'address';
			$errortxt[] = 'Address is invalid';
		}
		if($_POST['city']=='' || $_POST['city']=='[City]')
		{
			// city is invalid
			$errs[] = 'city';
			$errortxt[] = 'City is invalid';
		}
		if($_POST['state']=='' || $_POST['state']=='[ST]' || strlen($_POST['state'])>2 || strlen($_POST['state'])<2)
		{
			// state is invalid
			$errs[] = 'state';
			$errortxt[] = 'State must be a two-character code';
		}
		if($_POST['zip']=='' || $_POST['zip']=='[PostalCode]' || strlen($_POST['zip'])>5 || strlen($_POST['zip'])<5)
		{
			// zip is invalid
			$errs[] = 'zip';
			$errortxt[] = 'Postal code must be a five-digit code';
		}
		if(count($errs)>0)
		{
			// there've been errors, we need to post back and reset the form
			setcookie('agentErr','There are problems with the entry you made. Fix it.<br />'.implode('<br />',$errortxt));
			setcookie('badFields',implode(',',$errs));
			// set cookies for the current values
			setcookie('agentErr_last_name',stripslashes($_POST['last_name']));
			setcookie('agentErr_address',stripslashes($_POST['address']));
			setcookie('agentErr_address2',stripslashes($_POST['address2']));
			setcookie('agentErr_city',stripslashes($_POST['city']));
			setcookie('agentErr_state',stripslashes($_POST['state']));
			setcookie('agentErr_zip',stripslashes($_POST['zip']));
			setcookie('agentErr_phone',stripslashes($_POST['phone']));
			header('Location: manageagents.php');
			exit();
		}
		// everything's good, time to add them
		// going to strip slashes now
		foreach($_POST as $key => $value)
		{
			$$key = stripslashes($value);
		}
		$sql = "INSERT INTO snapshot_users (last_name, address, address2, city, state, zip, phone) VALUES ('$last_name', '$address', '".($address2=="[Address cont'd]" ? '' : $address2)."', '$city', '".strtoupper($state)."', '$zip', '".($phone=='[Phone]' ? '' : $phone)."')";
		$que = mysql_query($sql);
		checkdberror($sql);
		$snID = mysql_insert_id();
		$sql = "INSERT INTO shipping_agents (snapshot_userid) VALUES ($snID)";
		$que = mysql_query($sql);
		checkdberror($sql);
		setcookie('agentMsg','New Shipping Agent '.$last_name.' added successfully.');
		header('Location: manageagents.php');
		break;

	case 'modify':
		//var_dump($_POST);
		if(!$_COOKIE['agentMod'])
		{
			setcookie('agentModNumber',$_POST['select_agent']);
			header('Location: manageagents.php');
			exit();
		}
		else if($_POST['select_agent']=='n' || $_POST['select_agent'] != $_COOKIE['agentModNumber'])
		{
			setcookie('agentModNumber','',time()-2);
			header('Location: manageagents.php');
			exit();
		}
		else
		{
			setcookie('agentMod','',time()-2);
			// see if we're cancelling the modification
			if($_POST['cancel_form'])
			{
				// just go back to the start
				setcookie('agentModNumber','',time()-2);
				header('Location: manageagents.php');
			}
			else
			{
				// first we need to validate the data
				$errs = Array();
				if($_POST['last_name']=='' || $_POST['last_name']=='[Agent Name]')
				{
					// nothing's been entered
					$errs[] = 'last_name';
					$errortxt[] = 'Agent Name is invalid';
				}
				if($_POST['address']=='' || $_POST['address']=='[Address]')
				{
					// address is invalid
					$errs[] = 'address';
					$errortxt[] = 'Address is invalid';
				}
				if($_POST['city']=='' || $_POST['city']=='[City]')
				{
					// city is invalid
					$errs[] = 'city';
					$errortxt[] = 'City is invalid';
				}
				if($_POST['state']=='' || $_POST['state']=='[ST]' || strlen($_POST['state'])>2 || strlen($_POST['state'])<2)
				{
					// state is invalid
					$errs[] = 'state';
					$errortxt[] = 'State must be a two-character code';
				}
				if($_POST['zip']=='' || $_POST['zip']=='[PostalCode]' || strlen($_POST['zip'])>5 || strlen($_POST['zip'])<5)
				{
					// zip is invalid
					$errs[] = 'zip';
					$errortxt[] = 'Postal code must be a five-digit code';
				}
				if(count($errs)>0)
				{
					// there've been errors, we need to post back and reset the form
					setcookie('agentErr','There are problems with the entry you made. Fix it.<br />'.implode('<br />',$errortxt));
					setcookie('badFields',implode(',',$errs));
					// set cookies for the current values
					setcookie('agentErr_last_name',stripslashes($_POST['last_name']));
					setcookie('agentErr_address',stripslashes($_POST['address']));
					setcookie('agentErr_address2',stripslashes($_POST['address2']));
					setcookie('agentErr_city',stripslashes($_POST['city']));
					setcookie('agentErr_state',stripslashes($_POST['state']));
					setcookie('agentErr_zip',stripslashes($_POST['zip']));
					setcookie('agentErr_phone',stripslashes($_POST['phone']));
					header('Location: manageagents.php');
					exit();
				}
				// everything's good, time to add them
				// going to strip slashes now
				foreach($_POST as $key => $value)
				{
					$$key = stripslashes($value);
				}
				$sql = "INSERT INTO snapshot_users (last_name, address, address2, city, state, zip, phone) VALUES ('$last_name', '$address', '".($address2=="[Address cont'd]" ? '' : $address2)."', '$city', '".strtoupper($state)."', '$zip', '".($phone=='[Phone]' ? '' : $phone)."')";
				$que = mysql_query($sql);
				checkdberror($sql);
				$snID = mysql_insert_id();
				$sql = "UPDATE shipping_agents SET snapshot_userid = $snID WHERE ID = $select_agent";
				$que = mysql_query($sql);
				checkdberror($sql);
				setcookie('agentMsg','Shipping Agent '.$last_name.' modified successfully.');
				setcookie('agentModNumber','',time()-2);
				header('Location: manageagents.php');
			}
		}

		break;

	case 'delete':
		if(!$_POST['verified'])
		{
			// if we haven't verified the delete, go back and do so
			setcookie('agentDel',$_POST['select_agent']);
			header('Location: manageagents.php');
			exit();
		}
		else if($_POST['select_agent']=='n' || $_POST['cancel_form'] || $_POST['select_agent'] != $_COOKIE['agentDel'])
		{
			setcookie('agentDel','',time()-2);
			header('Location: manageagents.php');
			exit();
		}
		else
		{
			// verified the delete, so let's do so
			$sql = "DELETE FROM shipping_agents WHERE ID = '{$_COOKIE['agentDel']}'";
			$que = mysql_query($sql);
			checkdberror($sql);
			setcookie('agentDel','',time()-2);
			setcookie('agentMsg','Shipping Agent '.$_POST['last_name'].' deleted successfully.');
			header('Location: manageagents.php');
			exit();
		}
		break;
	
	case 'view':
		if($_POST['csvexport'])
		{
			// build the csv strings
			$csvout = '"Agent ID","Name","City","State","Zip","Phone #"'."\n";
			// get all the data we need
			$sql = "SELECT sa.ID AS agentID, last_name, city, state, zip, phone FROM snapshot_users users INNER JOIN shipping_agents sa ON sa.snapshot_userid = users.ID";
			$que = mysql_query($sql);
			checkdberror($sql);
			while($result = mysql_fetch_assoc($que))
			{
				$csvout .= "\"{$result['agentID']}\",\"{$result['last_name']}\",\"{$result['city']}\",\"{$result['state']}\",\"{$result['zip']}\",\"{$result['phone']}\"\n";
			}
			//$filetarget = fopen('../doc/Shipping Agents.csv',"a");
			//fwrite($filetarget, $csvout);
			//fclose($filetarget);
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="Shipping Agents.csv"');
			echo $csvout;
		}
		else
		{
			setcookie('viewmode',1);
			header('Location: manageagents.php');
			exit();
		}
		break;

	default:
		header('Location: manageagents.php');
		break;
}
?>