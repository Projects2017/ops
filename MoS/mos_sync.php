<?php
$exec_time = time();
require("MoS_database.php");
require("MoS_admin_secure.php");
require("MoS_menu.php");
if (!secure_is_superadmin())
	die("Access Denied");

if ($_POST['sure'] != 'Y') {
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<h2>Sync Market Forms to RSS</h2>
	<h3 style="color: red">This will shutdown MoS while the sync takes place</h3><br>
	Are you sure? <input type="checkbox" name="sure" value="Y"><br>
	If so enter site Admin Password: <input type="password" name="apass"><br>
	Make New Forms Orderable? <select name="orderable">
		<option value="Y">Yes</option>
		<option value="N">No</option>
	</select><br>
	<input type="submit" value="Sync Database">
	</form>
	<?php
	exit();
} else {
	if ($_POST['apass'] != $admin_pass) die('Invalid Password');
	function cflush() {
		//for ($i = 0; $i < 1024;++$i) {
		//	print(' ');
		//}
		//print("\n");
		ob_flush();
		flush();
	}
	cflush();
	function exec_cmd($cmd) {
		//echo "Executing: ".$cmd."<br>";
		$result = `$cmd`;
		//echo nl2br($result);
		//echo "<br><br>";
		return $result;
	}

	if (file_exists($tmpdir.'sync.lock')) die('Sync Already in Progress');
	exec_cmd('touch '.$tmpdir.'sync.lock');
	sleep(3); // Wait for everyone's requests to finish...

	$mos_sql_login = $databaseuser;
	$mos_sql_host = $databasehost;
	$mos_sql_pass = $databasepass;
	$mos_sql_db = $databasename;

	echo "STARTED<BR><BR>\n";
	cflush();
	exec_cmd('rm '.$tmpdir.$MoS_MasterFTPDBFileGZ);
	exec_cmd('rm '.$tmpdir.$MoS_MasterFTPDBFile);
	$url = $MoS_MasterPath . "MoS_targzdb.php";
	//$ch = curl_init($url);
	//curl_setopt($ch, CURLOPT_TIMEOUT, 450); 
	//curl_setopt($ch, CURLOPT_HEADER, 0);
	//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//echo "Hitting URL: ".$url."<br>";
	echo "Telling Target to Dump Database<BR><BR>\n";
	cflush();
	$source = popen("curl ".$url,'r');
	$source = exec("curl ".$url." | mysql -u" . $mos_sql_login . " -p" . $mos_sql_pass . " -h".$mos_sql_host." " . $mos_sql_db);
	//$response = curl_exec($ch);
	//curl_close($ch);
	//echo "Response: <br><pre>".$response."</pre><br>";
	//$response = "DONE";
	//if ($response == "DONE") {
	//	echo "Database file created.<BR><BR>Retrieving file<BR><BR>";
	//	cflush();
	//	$cmd = "ncftpget -u " . $MoS_MasterFTPUser . " -p " . $MoS_MasterFTPPass . " " . $MoS_MasterFTPHost . " " . $tmpdir . " " . $MoS_MasterFTPPath . $MoS_MasterFTPDBFileGZ;
	//	exec_cmd($cmd);

	//	echo "File retrieved. Extracting<BR><BR>";
	//	cflush();
	//	$cmd = "gunzip " . $tmpdir.$MoS_MasterFTPDBFile;
	//	exec_cmd($cmd);
		
	//	$file_stat = exec_cmd('stat '.$tmpdir.$MoS_MasterFTPDBFile);
	//	if ($file_stat != "") {
	//		echo "File extracted. Importing into sql.<BR><BR>";
	//		cflush();
	//		$cmd = "mysql -u" . $mos_sql_login . " -p" . $mos_sql_pass . " -h".$mos_sql_host." " . $mos_sql_db . " < " . $tmpdir.$MoS_MasterFTPDBFile;
	//		exec_cmd($cmd);
	//		echo "SQL Import Complete. Setting New Forms Orderability<BR><BR>END.<BR><BR>";
	//		cflush();

			$sql = "SELECT forms.ID as fid, alloworder FROM forms";
			$query = mysql_query($sql);
			checkDBerror($sql);
			if ($_POST['orderable'] == 'Y') {
				$orderable = 'Y';
			} else {
				$orderable = 'N';
			}
			$saved = 0;
			$removed = 0;
			while ($results = mysql_fetch_Array($query, MYSQL_ASSOC))
			{
				$sql = "SELECT `form_id` FROM `MoS_form_access` WHERE `form_id` = ".$results['fid'];
				$query2 = mysql_query($sql);
				checkDBerror($sql);
				//echo $results['alloworder'];
				//if ($results['alloworder'] === 'Y') {
					++$saved;
					if (!mysql_num_rows($query2)) {
						$sql = "INSERT INTO `MoS_form_access` (`form_id`, `enabled`) VALUES (".$results['fid'].",'".$orderable."')";
						mysql_query($sql);
						checkDBerror($sql);
					}
				/*
				} else {
					++$removed;
					$sql = "DELETE FROM `MoS_form_access` WHERE `form_id` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `forms` WHERE `ID` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `MoS_forms` WHERE `ID` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `MoS_director` WHERE `form_id` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `form_access` WHERE `form` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `discount` WHERE `form_id` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
					$sql = "DELETE FROM `freight` WHERE `form_id` = ".$results['fid'];
					mysql_query($sql);
					checkDBerror($sql);
				}
				*/
			}
			
			//$sql = "SELECT forms.ID as fid FROM forms WHERE alloworder != 'Y'";
			//$query = mysql_query($sql);
			//checkDBerror($sql);
			
			echo "Saved: ".$saved."<BR>\n";
			echo "Removed: ". $removed ."<BR>\n";
			echo "DONE";
		//}
		//else {
		//	echo "File not extracted properly.<BR><BR>END.";
		//	exit;
		//}
	//}
	//else {
	//	echo "Could not create database file on remote server.<BR>";
	//	exit;
	//}

	exec_cmd('rm '.$tmpdir.'sync.lock');
	$end_time = time();
	echo "<BR><BR>Total Run Time: ".($end_time - $exec_time)." Seconds<BR>";
}
?>
</body>
</html>
