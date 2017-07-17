<?php
// editest.php
require_once('database.php');
require_once('secure.php');

require_once('inc_content.php');
require_once('include/edi/edi.php');
require_once('include/edi/bo_shippingedi.php');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>EDI Test</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<form name="hi" method="post" action="editest.php">
<input name="mode" type="hidden" value="">
<button type="button" onclick="document.hi.mode.value = 'reset'; submit();">Reset Data Files</button><br />
<button type="button" onclick="document.hi.mode.value = 'go'; submit();">Run Tests</button><br />
<select name="choosefile">
<?php
	// go through each vendor (tracking the used file paths so we don't use the same one >1x)
	$vendor = new EdiVendor();
	$vendors = $vendor->GetAllVendors();
	$usedpaths = array();
	// here we go
	foreach($vendors as $thisvendor)
	{
		// has this path been checked yet?
		if(in_array($thisvendor->mVendorPath, $usedpaths) || $thisvendor->mVendorPath == '') continue;
		$usedpaths[] = $thisvendor->mVendorPath;
		//read the files to run one
		$dir = opendir($thisvendor->mVendorPath.'msg_rcvd/');
		while($name = readdir($dir))
		{
			if(substr($name, 0, 1) != '.' && !is_dir($thisvendor->mVendorPath.'msg_rcvd/'.$name))
			{
				?><option value="<?php= $thisvendor->mVendorPath.'msg_rcvd/'.$name ?>"><?php= $thisvendor->mVendorName ?>: <?php= $name ?></option>
				<?php
			}
		}
		closedir($dir);
		$dir = opendir($thisvendor->mVendorPath.'archive/');
		while ($name = readdir($dir))
		{
			if(substr($name, 0, 1) != '.')
			{
				if(is_dir($thisvendor->mVendorPath.'archive/'.$name))
				{
					$subdir = opendir($thisvendor->mVendorPath.'archive/'.$name);
					while ($name2 = readdir($subdir))
					{
						if(substr($name2, 0, 1) != '.' && !is_dir($thisvendor->mVendorPath.'archive/'.$name.'/'.$name2))
						{
							?><option value="<?php= $thisvendor->mVendorPath.'archive/'.$name.'/'.$name2 ?>"><?php= $thisvendor->mVendorName ?>: archive/<?php= $name.'/'.$name2 ?></option>
							<?php
						}
					}
				}
			}
		}
	}
?>
</select>&nbsp;<button type="button" onclick="document.hi.mode.value = 'choose'; submit();">Run This File</button><br />
<button type="button" onclick="document.hi.mode.value = 'facheck'; submit();">Check FA</button><br />
<button type="button" onclick="document.hi.mode.value = 'pctest'; submit();">Check PC (cancellation)</button><br />
<button type="button" onclick="document.hi.mode.value = 'tvinventory'; submit();">Run Target Inventory</button><br />
</form><?php
if(isset($_POST['mode']))
{	
	if($_POST['mode'] == 'pctest')
	{
		// go through each vendor (tracking the used file paths so we don't use the same one >1x)
		$vendor = new EdiVendor();
		$vendors = $vendor->GetAllVendors();
		$usedpaths = array();
		// here we go
		foreach($vendors as $thisvendor)
		{
			// has this path been checked yet?
			if(in_array($thisvendor->mVendorPath, $usedpaths)) continue;
			$usedpaths[] = $thisvendor->mVendorPath;
			//read the files to run one
			$files = array();
			$dir = opendir($thisvendor->mVendorPath.'msg_rcvd/pc');
			while($name = readdir($dir))
			{
				if(substr($name, 0, 1) != '.')
				$files[] = $name;
			}
			$proc = array();
			foreach($files as $thisfile)
			{
				$doc = fopen($thisvendor->mVendorPath.'msg_rcvd/pc/'.$thisfile, 'r');
				$readdata = fread($doc, filesize($thisvendor->mVendorPath.'msg_rcvd/pc/'.$thisfile));
				fclose($doc);
				$proc = array('data' => $readdata, 'filename' => $thisfile);
				$test = new Edi($proc);
				$confirm = $test->Confirm();
				$process = $test->Process();
				unset($test);
				unset($confirm);
				unset($process);
			}
		}
	}
	if($_POST['mode'] == 'tvinventory')
	{
		// we run the Target inventory
		// first setting the vendor properly
		$EdiVendor->LoadFromName('targettest');
		$edi = new EdiIB();
		$edi->Process();
	}
	if($_POST['mode'] == 'choose')
	{
		$doc = fopen($_POST['choosefile'], 'r');
		$readdata = fread($doc, filesize($_POST['choosefile']));
		fclose($doc);
		if(strlen($readdata) != 0 && !is_null($readdata))
		$proc = array('data' => $readdata, 'filename' => basename($_POST['choosefile']));
		$test = new Edi($proc);
		$confirm = $test->Confirm();
		$process = $test->Process();
	}

	if($_POST['mode'] == 'go')
	{
		// go through each vendor (tracking the used file paths so we don't use the same one >1x)
		$vendor = new EdiVendor();
		$vendors = $vendor->GetAllVendors();
		$usedpaths = array();
		// here we go
		foreach($vendors as $thisvendor)
		{
			// has this path been checked yet?
			if(in_array($thisvendor->mVendorPath, $usedpaths)) continue;
			$usedpaths[] = $thisvendor->mVendorPath;
			//read the files to run one

			$files = array();
			$dir = opendir($thisvendor->mVendorPath.'msg_rcvd/');
			while($name = readdir($dir))
			{
				if(substr($name, 0, 1) != '.' && !is_dir($thisvendor->mVendorPath.'msg_rcvd/'.$name))
				$files[] = $name;
			}
			$proc = array();
			foreach($files as $thisfile)
			{
				$doc = fopen($thisvendor->mVendorPath.'msg_rcvd/'.$thisfile, 'r');
				$readdata = fread($doc, filesize($thisvendor->mVendorPath.'msg_rcvd/'.$thisfile));
				fclose($doc);
				if(strlen($readdata) != 0 && !is_null($readdata))
				$proc[] = array('data' => $readdata, 'filename' => $thisfile);
			}
			foreach($proc as $testdata)
			{
				$test = new Edi($testdata);
				$confirm = $test->Confirm();
				$process = $test->Process();
			}
		}
	}
	else if($_POST['mode'] == 'reset')
	{
		// go through each vendor (tracking the used file paths so we don't use the same one >1x)
		$vendor = new EdiVendor();
		$vendors = $vendor->GetAllVendors();
		$usedpaths = array();
		// here we go
		foreach($vendors as $thisvendor)
		{
			// has this path been checked yet?
			if(in_array($thisvendor->mVendorPath, $usedpaths)) continue;
			$usedpaths[] = $thisvendor->mVendorPath;
			//read the files to run one

			$files = array();
			$dir = opendir($thisvendor->mVendorPath.'msg_tosend/');
			while($name = readdir($dir))
			{
				if(substr($name, 0, 1) != '.' && !is_dir($thisvendor->mVendorPath.'msg_tosend/'.$name)) unlink($edi->mWalmartEdiPath.'msg_tosend/'.$name);
			}
			$dir = opendir($thisvendor->mVendorPath.'archive/');
			while($name = readdir($dir))
			{
				if(substr($name, 0, 1) != '.')
				{
					if(is_dir($thisvendor->mVendorPath.'archive/'.$name))
					{
						$subdir = opendir($thisvendor->mVendorPath.'archive/'.$name.'/');
						while($name2 = readdir($subdir))
						{
							if(substr($name2, 0, 1) != '.')
							unlink($thisvendor->mVendorPath.'archive/'.$name.'/'.$name2);
						}
						rmdir($thisvendor->mVendorPath.'archive/'.$name);
					}
					else
					{
						unlink($thisvendor->mVendorPath.'archive/'.$name);
					}
				}
			}
		}
	}
	else if($_POST['mode'] == 'facheck')
	{
		// go through each vendor (tracking the used file paths so we don't use the same one >1x)
		$vendor = new EdiVendor();
		$vendors = $vendor->GetAllVendors();
		$usedpaths = array();
		// here we go
		foreach($vendors as $thisvendor)
		{
			// has this path been checked yet?
			if(in_array($thisvendor->mVendorPath, $usedpaths)) continue;
			$usedpaths[] = $thisvendor->mVendorPath;
			//read the files to run one
			$files = array();
			$dir = opendir($thisvendor->mVendorPath.'archive/'.date('Ym'));
			while($name = readdir($dir))
			{
				if(substr($name, 0, 7) == $thisvendor->mTypeCode.'_997') $files[] = $name;
			}
			$proc = array();
			foreach($files as $thisfile)
			{
				$doc = fopen($thisvendor->mVendorPath.'archive/'.date('Ym').'/'.$thisfile, 'r');
				$readdata = fread($doc, filesize($thisvendor->mVendorPath.'archive/'.date('Ym').'/'.$thisfile));
				fclose($doc);
				$proc[] = array('data' => $readdata, 'filename' => $thisfile);
			}
			foreach($proc as $testdata)
			{
				$test = new Edi($testdata);
				$process = $test->Process();
			}
		}
	}
}
?></body>
</html>