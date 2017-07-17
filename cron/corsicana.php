#!/usr/bin/php
<?php
//this file is called by a cron job that checks on a regular interval to send
//Corsicana order XML files over to their SFTP for processing

//require("database.php"); 
require(dirname(dirname(__FILE__)).'/config.default.php');
require(dirname(dirname(__FILE__)).'/config.inc.php');

# Make defines from config, and unset config vars.
define('MERCHANT_LOGIN_ID',$config_merchant_login_id);
define('MERCHANT_TRANSACTION_KEY',$config_merchant_transaction_id);
define('MERCHANT_SANDBOX', $config_merchant_sandbox);
unset($config_merchant_login_id);
unset($config_merchant_transaction_id);
unset($config_merchant_sandbox);

// check to see if the global boolean flag is turned on
if ($GLOBALS['corsicana_processXML'] == false)
	return;

//declare variables
//$user = ""
//$password = ""

//kick off the function
sendXMLCorsicana();
//function to submit files via SFTP
function sendXMLCorsicana() {
	$basedir = dirname(dirname(__FILE__));
	
	$descriptorspec = array(
		0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
	);
	// $pipes now looks like this:
	// 0 => writeable handle connected to child stdin
	// 1 => readable handle connected to child stdout
	
	// Any error output will be appended to /tmp/error-output.txt
	$cwd = '/tmp';
	$env = array('Are you sure you want to continue connecting (yes/no)?' => 'yes'); //unused
	
	//array to answer the prompts - password, yes to continue, and change local directory
	$cases = array (
		array (0 => "password:", 1 => "PASSWORD"),
		array (0 => "yes/no)?", 1 => "YESNO"),
		array (0 => "Hello, I'm freeFTPd 1.0", 1 => "LCD"),
	);
	
	//$pathtofile = dirname(__FILE__)."/docs/XML/486.xml";
	
	//actually connect call using "expect" function - this allows passing in password
	$process = fopen("expect://sftp 6145830650@orders.corsicanabedding.com","r");	
	
	// Flag to know we're still running.
	$running = true;
	
	while ($running) {
		switch (expect_expectl ($process, $cases)) {
			//check stdout and respond
			case "PASSWORD":
				fwrite ($process, "6145830650\n");
				break;
			
			case "YESNO":
				fwrite ($process, "yes\n");
				break;
			
			case "LCD":
				fwrite ($process, "lcd ".$basedir."/doc/corsicana/\n");
				//fwrite ($process, "lcd /var/www/vhosts/pmddealer.com/httpdocs/doc/corsicana/\n");
				sleep(2);
				fwrite($process, "cd /ToCorsicanaBedding\n");
				sleep(2);
				
				//here is where we actually put the files on the ftp, so we'll take
				//everything in the /docs/Corsicana folder
				
				//set the directory
				$TrackDir=opendir($basedir."/doc/corsicana/");
				
				while ($file = readdir($TrackDir)) {
					while (false !== ($file = readdir($TrackDir))) {
						if ($file == "." || $file == ".."  || $file == "processed") {
							//do nothing 
						} else {
							fwrite($process, "put ".$file."\n");
							sleep(5); //5 second pause should be more than plenty
							//echo $basedir . $file;
							rename($basedir."/doc/corsicana/" . $file,$basedir."/doc/corsicana/processed/" . $file);
						} 
					}
				}
				closedir($TrackDir);
				unset($TrackDir);
				unset($file);
				fwrite($process, "quit\n");
				$running = false;
				break;
			
			default:
				break;
		}
	}
	fclose($process);
}
?>
