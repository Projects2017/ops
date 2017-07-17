<?php
function exec_cmd($cmd) {
	//echo "\nExecuting: ".$cmd."<br>\n";
	$result = `$cmd`;
	//echo nl2br($result);
	//echo "<br><br>\n";
	return $result;
}
require('database.php');

if (!MoS_checkip($_SERVER['REMOTE_ADDR'])) {
	die("-- ERROR");
}

$sql = "SHOW TABLES;";
$query = mysql_query($sql);
checkDBerror($sql);
$tables = array();
while ($table = mysql_fetch_array($query, MYSQL_NUM)) {
        if (MoS_includes_table($table[0])) {
            $tables[] = $table[0];
        }
}

$exec_line = "rm -f ".$tmpdir.$MoS_MasterFTPDBFile;
exec_cmd($exec_line);
$exec_line = "rm -f ".$tmpdir.$MoS_MasterFTPDBFileGZ;//
exec_cmd($exec_line);

$exec_line = "mysqldump -u" . $databaseuser . " -p" . $databasepass . " -c ".$databasename." -h ".$databasehost." --add-drop-table --tables ";
$exec_line .= implode(' ',$tables);
passthru($exec_line);
//$exec_line .= " > ".$tmpdir.$MoS_MasterFTPDBFile;
//exec_cmd($exec_line);

//echo $exec_line . "<BR><BR>";

//$exec_line = "gzip ".$tmpdir.$MoS_MasterFTPDBFile;

//exec_cmd($exec_line);

//$exec_line = "chmod 777 ".$tmpdir.$MoS_MasterFTPDBFile;

//exec_cmd($exec_line);

echo "\n-- ####DONE#### --\n";

?>
