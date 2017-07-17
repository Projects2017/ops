<?php
// openform.php
// script to determine what action to take with an open order
// may be view shipping actions or add new one(s)
if(!$_POST)
{
	die("This script requires info to be POST'd in.");
}

//die(var_dump($_POST));

if($_POST['chosen']=='multi')
{ // multiple POs were picked
	if($_POST['multi_buttontype'] == 'bol')
	{
    	header('Location: multiaddbol.php?ids='.$_POST['checkedorders']);
    	exit();
	}
	else if($_POST['multi_buttontype'] == 'picktix')
	{
		// we're going to be checking the database to find out if any of these POs have already had a picktix made
		// if so, we'll leave them off the list of POs passed
		// if the number of good POs becomes 1, we'll send over to the addbol.php page
		// add the requires
		require_once('../database.php');
		$duallogin = 1;
		require_once("../vendorsecure.php");
		if (!$vendorid)
			require_once("../secure.php");
		$pos_array = explode(';',$_POST['checkedorders']);
		$pos_str = str_replace(';',',',$_POST['checkedorders']);
		$sql = "SELECT ID, po FROM BoL_queue WHERE picktix_printed = 0 AND po IN ($pos_str)";
		$que = mysql_query($sql);
		checkdberror($sql);
		$retcount = mysql_num_rows($que);
		while($return = mysql_fetch_assoc($que))
		{
			$goods[] = $return['po'];
		}
		if($retcount>1)
		{
			$goodlist = implode(';',$goods);
			header('Location: multiaddbol.php?ids='.$goodlist.'&viewonly');
			exit();
		}
		else
		{
			header('Location: addbol.php?id='.$goods[0].'&viewonly'.($_POST['edi']==1 ? "&edi=1" : ''));
			exit();
		}
	}
}

if(!$_POST['id'])
{
	$po_id = $_POST[$_POST['chosen']];
	$po_source = $_POST['source_'.$_POST['chosen']];
}
else
{
	$po_id = $_POST['id'];
}

if($_POST['addbol'])
{
	header('Location: addbol.php?id='.$po_id.'&source='.$po_source.($_POST['edi']==1 ? "&edi=1" : ''));
}
else if($_POST['addcred'])
{
	header('Location: addcredit.php?id='.$po_id.'&source='.$po_source.($_POST['edi']==1 ? "&edi=1" : ''));
}
else if($_POST['viewbol'])
{
	header('Location: viewbol.php?id='.$_POST['chosen'].'&source='.$po_source);
}
else
{
	header('Location: viewcredit.php?id='.$_POST['chosen'].'&source='.$po_source);
}
?>