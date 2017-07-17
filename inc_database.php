<?php
// If page requires SSL, and we're not in SSL mode, 
// redirect to the SSL version of the page
if(isset($requireSSL) && $requireSSL && $_SERVER['SERVER_PORT'] != 443) {
   header("HTTP/1.1 301 Moved Permanently");
   header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
   exit();
}

// Register Globals Manually
//if (!ini_get('register_globals')) {
	foreach($_COOKIE AS $key => $value) {
		${$key} = $value;
	}
	
	foreach($_POST AS $key => $value) {
		${$key} = $value;
	}
	
	foreach($_GET AS $key => $value) {
		${$key} = $value;
	}
// }

function dist_centers_list(){
	$sql = "SELECT * FROM distribution_centers ORDER BY `name`";
	$query = mysql_query($sql);
	$arrDistCenters = array();
	while ($row = mysql_fetch_assoc($query)){
		$arrDistCenters[] = $row;
	}
	return $arrDistCenters;
}

/**
 * Check Login Username and Password
 * @param string $login
 * @param string $pass
 * @return int Login Id, 0 for failure
 */
function checkLogin($login, $pass) {
    // Handle Login


    $login = mysql_escape_string($login);
    $pass = mysql_escape_string($pass);
    $sql = "select `id` from login where username='".$login."' and password='".$pass."'";
    $query = mysql_query($sql);
    checkDBError($sql);
    if ($row = mysql_fetch_assoc($query)) {
        return $row['id'];
    } else {
        return false;
    }
}

/**
 * Creates Login Session with passed Login ID
 * @param int $login_id
 */
function createSession($login_id) {
    $login_id = mysql_escape_string($login_id);
    $ip = mysql_escape_string($_SERVER['REMOTE_ADDR']);
    if (!is_numeric($login_id))
        throw new Exception('Login ID must be numeric');
    $key = uniqid('');
    $sql = "INSERT INTO `login_session` (`key`,`login_id`,`ip`,`lastaccess`) VALUES ('";
    $sql .= mysql_escape_string($key);
    $sql .= "','".$login_id."','".$ip."',NOW())";
    $sql .= " ON DUPLICATE KEY UPDATE `key` = '".$key."',";
    $sql .= "`login_id` = '".$login_id."',";
    $sql .= "`ip` = '".$ip."',";
    $sql .= "`lastaccess` = NOW()";
    
    mysql_query($sql);
    checkDBerror($sql);
    $id = mysql_insert_id();
    
    log_this("Creating Session ID: ".$id." For Login ID: ".$login_id.".");
    
    // Setup Session Cookies
    $_COOKIE['pmd_session_id'] = $id;
    setcookie('pmd_session_id', $_COOKIE['pmd_session_id'], 0, "/");
    $_COOKIE['pmd_session_key'] = $key;
    setcookie('pmd_session_key', $_COOKIE['pmd_session_key'], 0, '/');
}

/**
 *
 * @return int Login ID, false if not in session
 */
function checkSession() {
    // First, check for expired sessions
    $sql = "SELECT `id` FROM `login_session` WHERE `lastaccess` <= DATE_SUB(NOW(), INTERVAL 10 HOUR)";
    $query = mysql_query($sql);
    checkDBerror($sql);
    while ($row = mysql_fetch_assoc($query)) {
        log_this("Expiring Session ID: ".$row['id']);
        $sql = "DELETE FROM `login_session` WHERE `id` = '".mysql_escape_string($row['id'])."'";
        mysql_query($sql);
        checkDBerror($sql);
    }

    if (isset($_COOKIE['pmd_session_id']) && isset($_COOKIE['pmd_session_key'])) {
        $id = mysql_escape_string($_COOKIE['pmd_session_id']);
        $key = mysql_escape_string($_COOKIE['pmd_session_key']);
    } else {
        $id = '';
        $key = '';
    }
    $sql = "SELECT `login_id`, `ip` FROM `login_session` WHERE `id` = '".$id."' AND `key` = '".$key."'";
    $query = mysql_query($sql);
    checkDBerror($sql);
    if ($row = mysql_fetch_assoc($query)) {
        // if ($row['ip'] == $_SERVER['REMOTE_ADDR']) {
            $sql = "UPDATE `login_session` SET `lastaccess` = NOW() WHERE `id` = '".$id."'";
            mysql_query($sql);
            checkDBerror($sql);
            return $row['login_id'];
        // } else {
        //    log_this("Session ".$id." check failed due to changed IP (expected IP was ".$row['ip'].")");
        //    return false;
        // }
    } else {
        log_this("Failed session check for Session ID: ".$id);
        return false;
    }
}

function clearSession() {
    if (isset($_COOKIE['pmd_session_id']) && isset($_COOKIE['pmd_session_key'])) {
        $id = mysql_escape_string($_COOKIE['pmd_session_id']);
        $key = mysql_escape_string($_COOKIE['pmd_session_key']);
    } else {
        $id = '';
        $key = '';
    }
    log_this("Explicitly Clearing Session ID: ".$id);
    $ip = mysql_escape_string($_SERVER['REMOTE_ADDR']);
    $sql = "DELETE FROM `login_session` WHERE `id` = '".$id."' AND `key` = '".$key."' AND `ip` = '".$ip."'";
    mysql_query($sql);
    checkDBerror($sql);
    unset($_COOKIE['pmd_session_id']);
    setcookie('pmd_session_id', '', time()-3600, "/");
    unset($_COOKIE['pmd_session_key']);
    setcookie('pmd_session_key', '', time()-3600, '/');
}

function addFailedIp($ip) {
    log_this("Failed login attempt from IP ".$ip);
    $ip = mysql_escape_string($ip);
    
    $sql = "INSERT INTO `ip_lockout` (`ip`,`attempts`,`lasttry`) VALUES ('".$ip."',1,NOW())
            ON DUPLICATE KEY UPDATE `attempts`=`attempts`+1, `lasttry` = NOW();";
    mysql_query($sql);
    checkDBError($sql);
}

function clearIpLockout($ip) {
    $ip = mysql_escape_string($ip);

    $sql = "DELETE FROM `ip_lockout` WHERE `ip` = '".$ip."'";
    mysql_query($sql);
    checkDBerror($sql);
}

function ipLockedOut($ip) {
    $ip = mysql_escape_string($ip);

    // First, check for expired IP lockouts.
    $sql = "DELETE FROM `ip_lockout` WHERE `lasttry` <= DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
    mysql_query($sql);
    checkDBerror($sql);

    $sql = "SELECT `lasttry` FROM `ip_lockout` WHERE `ip` = '".$ip."' AND `attempts` >= 3";
    $query = mysql_query($sql);
    checkDBerror($sql);

    if ($row = mysql_fetch_assoc($query)) {
        log_this("Failed login attempt due to IP lockout from IP ".$ip);
        return true;
    } else {
        return false;
    }
}

/**
 * Sets a note that can be retrieved on next page load.
 * Stores the notes in a cookie.
 *
 * @param string $note
 */
function setNote($note) {
    if (isset($_COOKIE['pmd_notes'])) {
        $existingNotes = explode(';lzj;s',$_COOKIE['pmd_notes']);
    } else {
        $existingNotes = array();
    }
    $existingNotes[] = $note;
    $_COOKIE['pmd_notes'] = implode(';lzj;s',$existingNotes);
    setcookie('pmd_notes', $_COOKIE['pmd_notes'], time()+3600, "/");

}

/**
 * Gets stored notes
 * will erase them from the cookies so that they can only be gotten once.
 *
 * @return array strings of notes
 */
function getNotes() {
    if (isset($_COOKIE['pmd_notes'])) {
        $existingNotes = explode(';lzj;s',$_COOKIE['pmd_notes']);
        setcookie('pmd_notes', null, time()-3600, '/');
        unset($_COOKIE['pmd_notes']);
    } else {
        $existingNotes = array();
    }
    return $existingNotes;
}

function getconfig($name, $prefix = "") 
{
	$sql = "SELECT value FROM ".$prefix."config WHERE name = '".$name."'";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	return $row['value'];
}

function log_this_entry_now_394393() {
    // This will only log logged in actions. Who did it and what actions.
    $str = $GLOBALS['userid'];
    if (isset($_COOKIE['pmd_suuser']) && $_COOKIE['pmd_suuser']) {
        $str .= "@".$_COOKIE['pmd_suuser'];
    }
    $str .= ":s".$_COOKIE['pmd_session_id']." ";
    $str .= $_SERVER['REMOTE_ADDR'] . " ";
    $str .= $_SERVER["REQUEST_METHOD"] . " ";
    $str .= "\"" . $_SERVER['REQUEST_URI'] . "\" ";
    $str .= "\"" . $_SERVER['HTTP_USER_AGENT'] . "\" ";
    // Log it to syslog.
    openlog("pmdops", LOG_NDELAY , LOG_LOCAL5);
    syslog(LOG_INFO, $str);
    closelog(); // Closes the logging connection
}

function log_this($str) {
    // Log it to syslog.
//    openlog("pmdops", LOG_NDELAY , LOG_LOCAL5);
    if (isset($GLOBALS['userid'])&&$GLOBALS['userid']) {
        if (isset($_COOKIE['pmd_session_id'])&&is_numeric($_COOKIE['pmd_session_id'])) {
            $str = $GLOBALS['userid'].'@'.$_SERVER['REMOTE_ADDR'].'s'.$_COOKIE['pmd_session_id'].': '.$str;
        } else {
            $str = $GLOBALS['userid'].'@'.$_SERVER['REMOTE_ADDR'].': '.$str;
        }
    } else {
        $str = $_SERVER['REMOTE_ADDR'].': '.$str;
    }
    syslog(LOG_INFO, $str);
    closelog(); // Closes the logging connection
}

function MoS_includes_table($tableName) {
	if (substr($tableName,0,4) == 'BoL_') return false;
        if (substr($tableName,0,4) == 'edi_') return false;
        if (substr($tableName,0,3) == 'ch_') return false;
        if (substr($tableName,0,3) == 'tv_') return false;
        if (substr($tableName,0,3) == 'wm_') return false;
	if (substr($tableName,0,3) == 'sos') return false;
	if (substr($tableName,0,9) == 'exported_') return false;
	if (substr($tableName,0,5) == 'claim') return false;
	if (substr($tableName,0,12) == 'announcement') return false;
        if (substr($tableName,0,9) == 'exported_') return false;
        if (substr($tableName,0,10) == 'fieldvisit') return false;
        if (substr($tableName,0,9) == 'shipping_') return false;
	if ($tableName == 'salestats') return false;
        if ($tableName == 'wodsstats') return false;
	if ($tableName == 'orders') return false;
	if ($tableName == 'order_forms') return false;
	if ($tableName == 'mssgthread') return false;
	if ($tableName == 'message') return false;
	if ($tableName == 'form_changes') return false;
	if ($tableName == 'fee_payments') return false;
        if ($tableName == 'commercehub_counter') return false;
        if ($tableName == 'iso_country_codes') return false;
        if ($tableName == 'wodstats') return false;
        if ($tableName == 'walmart_inventory') return false;
        if ($tableName == 'cell_providers') return false;

        if ($tableName == 'form_old') return false; // Shouldn't even really be there
        return true;
}

class DBField
{
	var $name;
	var $type;
	var $table;
	var $title;
	var $size;
	var $rows;
	var $maxlength;
	var $value;
	var $prefix;
	var $postfix;
	var $display;
	var $options;
	var $null;
        var $null_value;
	
  /* RDK removed overloaded constructor: PHP doesn't support overloading.  Upgrade to php generates error
	function DBField()
	{
		$name = "";
		$table = "";

		$this->display = true;
	}
  */

	function DBField($name, $table, $size, $value, $null = false)
	{
		if ($size == "") $size = 20;
		
		$this->display = true;
		$this->name = $name;
		$this->table = $table;
		$this->size = $size;
		$this->rows = 5;
		$this->value = $value;
		$this->title = ucwords($name);
		$this->null = $null;
                $this->null_value = '';
                $this->null_default = '';
		$this->options = array();
	}
	
	function display()
	{
		if (!$this->display) return;
		
		$prefix = str_replace("[TITLE]", $this->title, $this->prefix);
		echo $prefix;
		
		switch($this->type)
		{
			case "blob": ?>
<link rel="stylesheet" href="../styles.css" type="text/css">
<body bgcolor="#EDECDA" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<textarea cols="<?php= $this->size ?>" rows="<?php= $this->rows ?>" name="<?php= $this->name ?>"><?php= htmlentities($this->value) ?></textarea><?php
			break;
			
			case "date":
				if( $this->value != "" ) $time = strtotime( $this->value );
				else $time = strtotime( "now" ); ?>
				<input type="text" size="2" maxlength="2" name="<?php= $this->name ?>m" value="<?php= date( "m", $time ) ?>">/<input type="text" size="2" maxlength="2" name="<?php= $this->name ?>d" value="<?php= date( "d", $time ) ?>">/<input type="text" size="4" maxlength="4" name="<?php= $this->name ?>y" value="<?php= date( "y", $time ) ?>">
				<?php
			break;
			// This will only get called if manually set
			case "checkbox":
				?><input type="checkbox" name="<?php echo $this->name; ?>" id="<?php echo $this->name; ?>" value="Y"<?php if ($this->value == "Y") echo " CHECKED"; ?>><?php
			break;
			// This will only get called if manually set
			case "select":
				?>
				<SELECT id='<?php echo $this->name; ?>' name='<?php echo $this->name; ?>'>
					<?php foreach ($this->options as $option) { ?>
						<OPTION value='<?php echo htmlentities($option); ?>'<?php if ($this->value == $option) echo ' SELECTED'; ?>><?php echo htmlentities($option); ?></OPTION>
					<?php } ?>
				</SELECT>
				<?php
			break;
			// This will only get called if manually set
			// This is different in that the values are seperate from the option names
			case "selectval":
				?>
				<SELECT id='<?php echo $this->name; ?>' name='<?php echo $this->name; ?>'>
					<?php foreach ($this->options as $optionid => $option) { ?>
						<OPTION value='<?php echo htmlentities($optionid); ?>'<?php if ($this->value == $optionid) echo ' SELECTED'; ?>><?php echo htmlentities($option); ?></OPTION>
					<?php } ?>
				</SELECT>
				<?php
			break;
			
			default:
				if ($this->null) {
					$js = "if (document.getElementById('".$this->name."_null').checked) { document.getElementById('".$this->name."').disabled = false; document.getElementById('".$this->name."').value='".$this->null_default."'; } else { document.getElementById('".$this->name."').disabled = true; document.getElementById('".$this->name."').value='".$this->null_value."'; }";
					$js = htmlspecialchars($js);
				}
				?><input type="text" size="<?php echo $this->size ?>" maxlength="<?php echo $this->maxlength ?>" name="<?php echo $this->name ?>" id="<?php echo $this->name ?>" value="<?php echo is_null($this->value)?htmlentities($this->null_value):htmlentities($this->value) ?>"<?php if ($this->null) { ?> <?php if (is_null($this->value)) { echo "DISABLED"; } ?>><input type="checkbox" id="<?php echo $this->name ?>_null" name="<?php echo $this->name ?>_null" value="N" <?php if (!is_null($this->value)) { echo "CHECKED"; } ?> onchange="<?php echo $js; ?>" onpropertychange="<?php echo $js; ?>"><?php } else { ?>><?php }
			break;
		}
		echo $this->postfix."\r\n";
	}
}

function DBdisplayFields($fields)
{
	for ($c = 0; $c < count($fields); $c++)
		$fields[$c]->display();
}

function DBlistfields($table) {
	global $link;
	global $databasename;
	$result = mysql_list_fields($databasename, $table, $link);
	checkDBError("mysql_list_fields(".$table.")");

	$cols = mysql_num_fields($result);
	$fields = array();
	for ($c = 0; $c < $cols; $c ++) {
		$name = mysql_field_name($result, $c);
		$fields[] = $name;
	}
	return $fields;
}

function DBcreateFields($table, &$fields, $prefix, $postfix)
{
	global $link;
	global $databasename;

//	$fields = new Array();
	
	$result = mysql_list_fields($databasename, $table, $link);
	checkDBError();
	
	$cols = mysql_num_fields($result);
	
	for ($c = 0; $c < $cols; $c ++) {
		$name = mysql_field_name($result, $c);
		
		if ($name == "ID") continue;
		$flags = explode(' ', mysql_field_flags($result, $c));
		$null = true;
		if (in_array('not_null',$flags)) {
			$null = false;
		}
		
		$fields[] = new DBField($name, $table, "", "", $null);
		$fields[count($fields)-1]->prefix = $prefix;
		$fields[count($fields)-1]->postfix = $postfix;
		$fields[count($fields)-1]->type = mysql_field_type($result, $c);
	}
}


function buildInsertQuery($table, $values = null, $requirenull = false)
{
	global $link;
	global $databasename;
	
	$sql = "Insert into $table values ( NULL, ";
	
	$fields = mysql_list_fields($databasename, $table, $link);
	$numCols = mysql_num_fields($fields);

	for ($i=1; $i < $numCols; $i++){
		$name1 =  mysql_field_name($fields, $i);
		$namenull = $name1.'_null';
		if (isset($values) && $values) {
			if ((!$requirenull)||isset($values[$namenull])) {
				$isnull = $values[$namenull];
			} else {
				$isnull = 'N';
			}
			$value = isset($values[$name1]) ? $values[$name1] : '';
		} else {
			// Old Style Pull From Globals
			global $$namenull;
			if ((!$requirenull)||isset($$namenull)) {
				$isnull = $$namenull;
			} else {
				$isnull = 'N';
			}
			global $$name1;
			$value = $$name1;
		}
		if (is_null($value)) $isnull = 'Y';
		$flags = explode(" ",mysql_field_flags($fields, $i));
		if (!in_array('not_null',$flags) && ($isnull != 'N')) {
			$sql.= "NULL";
		} else {
			$sql.= "'$value'";
		}
		if ($i != $numCols - 1) $sql.= ", ";
		else $sql.= " );";
	}
	
	return $sql;
}

function profilePhoto($photoURL){
	if (empty($photoURL) || !file_exists($_SERVER['DOCUMENT_ROOT']."/images/users/".$photoURL)){
		return "/images/users/noimage.jpg";
	} else {
		return "/images/users/".$photoURL;
	}
}

function buildUpdateQuery($table, $condition)
{
	global $link;
	global $ID;
	global $databasename;
	
	$sql = "Update `$table` set ";
	
	$fields = mysql_list_fields($databasename, $table, $link);
	$numCols = mysql_num_fields($fields);

	for ($i=1; $i < $numCols; $i++){
		$name1 =  mysql_field_name($fields, $i);
		$namenull = $name1.'_null';
		if ($name1 != "ID")
		{
			global $$name1;
			global $$namenull;
			$isnull = $$namenull;
			$value = $$name1;
			$flags = explode(" ",mysql_field_flags($fields, $i));
			if (!in_array('not_null',$flags) && ($isnull != 'N')) {
				$sql.= "`$name1` = NULL";
			} else {
				$sql.= "`$name1` = '$value'";
			}
		
			if ($i != $numCols - 1) $sql.= ", ";
			else $sql.= " where $condition";
		}
	}
	
	return $sql;
}

function buildUpdateQuery2($table, $condition)
{
	global $link;
	global $ID;
	global $databasename;
	
	$first = true;
	$sql = "Update $table set ";
	
	$fields = mysql_list_fields($databasename, $table, $link);
	$numCols = mysql_num_fields($fields);

	for ($i=1; $i < $numCols; $i++){
		$name1 =  mysql_field_name($fields, $i);
		if ($name1 != "ID")
		{
			global $$name1;
			$value = $$name1;

			if ($value != "") {
				if (!$first) $sql .= ", ";
				$sql .= "$name1 = '$value'";
				$first = false;
			}
			if ($i == $numCols - 1) $sql.= " where $condition";
		}
	}
	
	return $sql;
}

function assignFieldsToVars($query)
{
	$numCols = mysql_num_fields($query);
	
	if ($result = mysql_fetch_array($query))
	{
		checkDBError();
	
		for ($i=0; $i < $numCols; $i++){
			$name1 = mysql_field_name($query, $i);
			global $$name1;
			$$name1 = $result[$i];
		}
	}
}

function assignFieldsToVars2($query, $result)
{

	$numCols = mysql_num_fields($query);
	
		checkDBError();
	
		for ($i=0; $i < $numCols; $i++){
			$name1 = mysql_field_name($query, $i);
		
			global $$name1;
			$$name1 = $result[$i];
		}
}

// $blocking = if on error do you exit?
function checkDBError($sql = 0, $blocking = true, $file = '', $line = 0)
{
    global $debug;
    if ($debug) {
        $debug .= $sql."<br />\n";
    }
	if (mysql_error() != "") { 
		echo mysql_error();
		if (!$sql) {
			unset($sql);
			global $sql; 
		}
		echo "<br>".$sql;
		if ($file) 
			echo "<br> File ".$file." Line: ".$line;
		else {
			$backtrace = nl2br(dbg_get_backtrace(2));
			echo "<br>".$backtrace;
		}
		if ($blocking) exit;
		return mysql_error()."\n".$sql;
	}
	return false;
}

// builds backtrace message
function dbg_get_backtrace($irreleventFirstEntries) {
	$s = '';
	$MAXSTRLEN = 64;
	$traceArr = debug_backtrace();
	for ($i = 0; $i < $irreleventFirstEntries; $i++)
		array_shift($traceArr);
	$tabs = sizeof($traceArr) - 1;
	foreach($traceArr as $arr) {
		$tabs -= 1;
		if (isset($arr['class']))
			$s .= $arr['class'] . '.';
		$args = array();
		if (!empty($arr['args']))
			foreach($arr['args'] as $v) {
				if (is_null($v))
					$args[] = 'null';
				elseif (is_array($v))
					$args[] = 'Array['.sizeof($v).']';
				elseif (is_object($v))
					$args[] = 'Object:'. get_class($v);
				elseif (is_bool($v))
					$args[] = $v ? 'true' : 'false';
				else {
					$v = (string)@$v;
					$str = htmlspecialchars(substr($v, 0, $MAXSTRLEN));
					if (strlen($v) > $MAXSTRLEN)
						$str .= '...';
					$args[] = '"' . $str . '"';
				}
			}
		$s .= $arr['function'] . '(' . implode(', ', $args) . ')';
		$Line = (isset($arr['line']) ? $arr['line']: 'unknown');
		$File = (isset($arr['file']) ? $arr['file']: 'unknown');
		$s .= sprintf(' # line %4d, file: %s', $Line, $File, $File);
		$s .= "\n";
	}
	return $s;
}

function insertCommas($number)
{
	$blah = 0;
	if ($number < 0)
		$negative = "-";
	$number = abs($number);
	for ($c = strlen($number) - 1; $c >= 0; $c--)
	{
		$blah++;
		
		if ($blah == 3 && $c != 0)
		{
			$beginning = substr($number, 0, $c);
			$end = substr($number, $c, 100);
			$number = $beginning.",".$end;
			$blah = 0;
		}
	}
	$number = $negative.$number;
	return $number;
}

function sendmail($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {
	if ($GLOBALS['outmail_override']) {
		// Override... this is a testing site
		$body = "";
		$body .= "To: ".$to."\r\n";
		if (!is_null($additional_headers)) {
			$body .= "Additional Headers:\r\n";
			$body .= $additional_headers."\r\n";
		}
		if (!is_null($additional_parameters)) {
			$body .= "Additional Parameters\r\n";
			$body .= $additional_parameters."\r\n";
		}
		$message = $body.$message;
		$to = $GLOBALS['outmail_override'];
		$subject = "Dev: ".$subject;
		$additional_headers = "From: noreply@retailservicesystems.com";
		$additional_parameters = null;
	} 
	if (is_null($additional_headers)) {
		return mail($to, $subject, $message);
	} elseif (is_null($additional_parameters)) {
		return mail($to, $subject, $message, $additional_headers);
	} else {
		return mail($to, $subject, $message, $additional_headers, $additional_parameters);
	}
}

function makeThisLookLikeMoney($val)
{
	if (stristr($val, "$")) return $val;
	
	$money = number_format(abs($val), 2, '.', ',');
	if ($val < 0) {
		$money = "(-$" . $money . ")";
	} else {
		$money = "$" . $money;
	}
	return $money;
}

function db_user_getuserinfo($id, $infotype = 0) {
	mysql_escape_string($id);
	if (!$infotype) {
		$sql = "select * from users where ID= '".$id."'";
	} elseif (is_array($infotype)) {
		foreach($infotype as $x)
			$x = mysql_escape_string($x);
		$infotype = implode("`, `",$infotype);
		$infotype = "`".$infotype."`";
		$sql = "select ". $infotype ." from users where ID='".$id."'";
	} else {
		$sql = "select `". mysql_escape_string($infotype) ."` from users where ID = '".$id."'";
	}
	$query = mysql_query($sql);
	checkDBError($sql);
	if (!mysql_num_rows($query))
		return 0;
	$bigarray = mysql_fetch_array($query, MYSQL_BOTH);
	mysql_free_result($query);
	if (count($bigarray) == 2)
		$bigarray = $bigarray[0];
	return $bigarray;
}

function db_user_fullname($id) {
	$name = db_user_getuserinfo($id, 'last_name');
	$name = explode(", ", $name);
	if (count($name) == 2) {
		$name = $name[1].' '.$name[0];
	} elseif (count($name) == 1) {
		$name = $name[0];
	} else { // Unable to parse 3 or more
		$name = implode(", ", $name);
	}
	return $name;
}

function db_order_get_volume($po_id) {
	$cubic_ft = 0;
	$sql = "SELECT snapshot_items.cubic_ft, orders.qty FROM orders " .
		"INNER JOIN snapshot_items ON " .
		"orders.item = snapshot_items.id " .
		"WHERE orders.po_id = $po_id";
	$query = mysql_query($sql);
	checkDBError($sql);
	while ($row = mysql_fetch_array($query, MYSQL_BOTH)) {
		$cubic_ft += $row['qty'] * $row['cubic_ft'];
	}
	return $cubic_ft;
}

function escapearray($array) {
	if (!is_array($array)) return $array;
	$output = array();
	foreach($array as $key => $val) {
		if (is_array($val)) $val = escapearray($val);
		$val = mysql_escape_string($val);
		$output[$key] = $val;
	}
	return $output;
}

function db_user_checkinfilter($dealer_id, $filters = array()) {
    $filters['dealer_id'] = $dealer_id;
    $result = db_user_filterlist($filters);
    if (is_array($result) && count($result)) {
        return true;
    } else {
        return false;
    }
}

function db_user_filterlist($filters = array()) {
	$defaults = array(
			'team' => array( 'value' => '*', 'type' => 'text', 'fieldname' => 'team'),
			'disabled' => array( 'value' => 'N', 'type' => 'YN', 'fieldname' => 'disabled'),
			'manager' => array( 'value' => '*', 'type' => 'text', 'fieldname' => 'manager'),
			'division' => array( 'value' => '*', 'type' => 'text', 'fieldname' => 'division'),
			'level' => array( 'value' => '*', 'type' => 'text', 'fieldname' => 'level'),
			'nonPMD' => array( 'value' => 'N', 'type' => 'YN', 'fieldname' => 'nonPMD'),
			'email' => array( 'value' => '', 'type' => 'special'),
			'state' => array( 'value' => '', 'type' => 'text', 'fieldname' => 'state'),
			'wodsable' => array( 'value' => '*', 'type' => 'YN', 'fieldname' => 'wodsable'),
                        'dealer_type' => array( 'value' => '*', 'type' => 'special'),
                        'dealer_id' => array('value' => '*', 'type' => 'text', 'fieldname' => 'ID'),
		);
	// Set Defaults, where Filter is not present or Filter is NULL
	foreach ($defaults as $k => $v) {
		if (!isset($filters[$k])||is_null($filters[$k]))
			$filters[$k] = $v['value'];
	}
	$where = '';

	// Special Cases...
	if ($filters['email'] != '*' && $filters['email'] != '') {
		if ($where) {
			$where .= 'AND ';
		} else {
			$where .= 'WHERE ';
		}
		$where .= "(`users`.`email` LIKE '%".mysql_escape_string($filters['email'])."%' ";
		$where .= "OR `users`.`email2` LIKE '%".mysql_escape_string($filters['email'])."%' ";
		$where .= "OR `users`.`email3` LIKE '%".mysql_escape_string($filters['email'])."%') ";
	}

	if ($filters['state'] == '') {
		$filters['state'] = '*';
	}

        if (isset($filters['dealer_type'])) {
            if ($filters['dealer_type'] == 'F' || $filters['dealer_type'] == 'L') {
                if ($where) {
			$where .= 'AND ';
		} else {
			$where .= 'WHERE ';
		}
                $where .= "`users`.`dealer_type` = '".mysql_escape_string($filters['dealer_type'])."' ";
            }
        }
	
	foreach ($filters as $k => $v) {
		// Remove *'d filters, we don't really care about those...
		// Also, remove special types, they are processed before this loop
		// If there is no default, there is no filter for it...
		if ($v == '*'||(!$defaults[$k])||$defaults[$k]['type'] == 'special')
			continue;

		// Add Joining for Filters
		if ($where) {
			$where .= 'AND ';
		} else {
			$where .= 'WHERE ';
		}

		// Filter Based on Type
		if ($defaults[$k]['type'] == 'text') {
			if ($v[0] == "+") {
				$v = ltrim($v, "+");
				$sign = ">=";
			} elseif ($v[0] == "-") {
				$v = ltrim($v, "-");
				$sign = "<=";
			} elseif (($v[0] == "!")&&(strlen($v) > 1)) {
				$v = ltrim($v, "!");
				$sign = "!=";
			} elseif ($v[0] == "=") {
				$v = ltrim($v, "=");
				$sign = "=";
			} else {
				$sign = "=";
			}
			$where .= "`".$defaults[$k]['fieldname']."` ".$sign." '".$v."' ";
		} elseif ($defaults[$k]['type'] == 'YN') {
			if ($v == 'Y') {
				$where .= "`".$defaults[$k]['fieldname']."` = 'Y' ";
			} elseif ($v == 'N') {
				$where .= "`".$defaults[$k]['fieldname']."` != 'Y' ";
			}
		}
	}

	$sql = 'SELECT * '
	     . ' FROM `users`';
	$sql .= $where;
	$sql .= ' ORDER BY `last_name`';
	$query = mysql_query($sql);
	checkDBError($sql);
	$bigarray = array();
	while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$row['id'] = $row['ID']; // I hate caps
		$bigarray[] = $row;
	}
   
    return $bigarray;
}

function db_user_getlist($team = '*', $show_inactive = 0, $manager = "*", $division = "*", $level = "*", $nonPMD = "N", $wodsable = "*", $dealer_type = '*') {
	$filters = array(
			'team' => $team,
			'disabled' => $show_inactive ? 'Y':'N',
			'manager' => $manager,
			'division' => $division,
			'level' => $level,
			'nonPMD' => $nonPMD,
			'wodsable' => $wodsable,
                        'dealer_type' => $dealer_type
		);
	$array = db_user_filterlist($filters);
	return $array;
}

// Vendor Logins
function db_vendor_getlist() {
	$sql = 'SELECT `id` , `name` '
	     . ' FROM `vendor` ORDER BY `name`';
	$query = mysql_query($sql);
	checkDBError($sql);

	$bigarray = array();

	while ($row = mysql_fetch_array($query, MYSQL_BOTH)) {
		$bigarray[] = $row;
	}
   
    return $bigarray;
}

// Vendor list by passed type
// same as db_vendor_getlist, only add a type filter
function db_vendor_getlist_thistype($type)
{
	$sql = "SELECT id, name FROM vendor WHERE type = '$type' ORDER BY name";
	$que = mysql_query($sql);
	checkDBerror($sql);
	$bigarray = array();
	while($row = mysql_fetch_assoc($que))
	{
		$bigarray[] = $row;
	}
	return $bigarray;
}

// Vendor Names
function db_vendors_getlist() {
	$sql = 'SELECT `id` , `name` '
	     . ' FROM `vendors` ORDER BY `name`';
	$query = mysql_query($sql);
	checkDBError($sql);

	$bigarray = array();

	while ($row = mysql_fetch_array($query, MYSQL_BOTH)) {
		$bigarray[] = $row;
	}
   
    return $bigarray;
}

function db_forms_getlist($vendor = 0) {
	$sql = "SELECT `ID`, `name` FROM `forms`";
	if ($vendor != 0 && is_numeric($vendor)) {
		$sql .= " WHERE `vendor` = '".$vendor."'";
	}
	$sql .= " ORDER BY `name`";
	$result = mysql_query($sql);
	checkDBerror($sql);
	return db_result2array($result);
}

function db_vendor_getinfo($id, $infotype = 0) {
	mysql_escape_string($id);
	if (!$infotype) {
		$sql = "select * from vendor where ID= '".$id."'";
	} elseif (is_array($infotype)) {
		foreach($infotype as $x)
			$x = mysql_escape_string($x);
		$infotype = implode("`, `",$infotype);
		$infotype = "`".$infotype."`";
		$sql = "select ". $infotype ." from vendor where ID=$ID";
	} else {
		$sql = "select `". mysql_escape_string($infotype) ."` from vendor where ID = '".$id."'";
	}
	$query = mysql_query($sql);
	checkDBError($sql);
	if (!mysql_num_rows($query))
		return 0;
	$bigarray = mysql_fetch_array($query, MYSQL_BOTH);
	mysql_free_result($query);
	if (count($bigarray) == 2)
		$bigarray = $bigarray[0];
	return $bigarray;
}

// $table = table we're copying [a] row(s) in
// $id = id of record if single, otherwise value to match on
// $single = if true, only one record will be copied and row to match id against will be assumed auto_incriment
//           if false, repcolumn is required, and will match id against repcolumn to figure out what needs to be copied
// [$repcolumn] = column to be changed during copy
// [$newid] = new value of column when changed
function db_copy_row($table, $single, $id, $repcolumn = 0, $newid = 0) {
	global $basedir;
	// Get table column info
	$result = mysql_query("SHOW COLUMNS FROM `".mysql_escape_string($table)."`");
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tableinfo[] = $line;
		if ($line["Extra"] == "auto_increment")
		   $autocol = $line["Field"];
	}
	mysql_free_result($result);
	unset ($result);
	unset($line);
	// Two new variables are now present, $autocol and $tableinfo
	$return = array();
	if ($single)
	    $sql = "SELECT * FROM `".mysql_escape_string($table)."` WHERE `".mysql_escape_string($autocol)."` = '".mysql_escape_string($id)."'";
	else
		$sql = "SELECT * FROM `".mysql_escape_string($table)."` WHERE `".mysql_escape_string($repcolumn)."` = '".mysql_escape_string($id)."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$oldid = $line[$autocol];
		unset($line[$autocol]);
		if ($newid) {
			$line[$repcolumn] = $newid;
		}
		$sql = "INSERT INTO `".mysql_escape_string($table)."`";
		$columnlist = "(";
		$valuelist = "(";
		foreach ($line as $column => $value) {
			$columnlist .= "`".mysql_escape_string($column)."`,";
			if (is_null($value)) {
				$valuelist .= "NULL,";
			} else {
				$valuelist .= "'".mysql_escape_string($value)."',";
			}
		}
		$columnlist = substr($columnlist, 0, strlen($columnlist) - 1).")";
		$valuelist = substr($valuelist, 0, strlen($valuelist) - 1).")";
		$sql = $sql." ".$columnlist." VALUES ".$valuelist;
		$result2 = mysql_query($sql);
		// Copy Image and Thumbnail
		if ($table == 'form_items') {
			if (file_exists($basedir."photos/".$oldid.".jpg")) 
				copy($basedir."photos/".$oldid.".jpg", $basedir."photos/".mysql_insert_id().".jpg");
			if (file_exists($basedir."photos/t".$oldid.".jpg"))
				copy($basedir."photos/t".$oldid.".jpg", $basedir."photos/t".mysql_insert_id().".jpg");
		}
		$return[] = array("oldid" => $oldid, "newid" => mysql_insert_id());
		// mysql_free_result($result2); Aparently insert's don't generate results
	}
	mysql_free_result($result);
	return $return;
}

function stock_status($id) {
	static $cache = array();
	if (!$id) {
		$sql = "SELECT * FROM `stock_status` ORDER BY `stock_status`.`order`";
		$result = mysql_query($sql);
		$stock = array();
		while ($line2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$line2['style'] = stock_buildstyle($line2);
			$line[] = $line2;
		}
		mysql_free_result($result);
	} else {
		if (isset($cache[$id])) return $cache[$id]; // If the result is cached, use that instead
		$sql = "SELECT * FROM stock_status WHERE id = '".mysql_escape_string($id)."'";
		$result = mysql_query($sql);
		$stock = array();
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		$line['style'] = stock_buildstyle($line);
		$cache[$id] = $line;
	}
	return $line;
}

function stock_block($id) {
	$temp = stock_status($id);
	if (!isset($temp['block_order'])) return false;
	if ($temp['block_order'] == 'Y') {
		return true;
	} else {
		return false;
	}
}

function db_result2array($result) {
	$return = array();
	while ($row = mysql_fetch_assoc($result)) {
		$return[] = $row;
	}
	return $return;
}

function stock_buildstyle($line) {
	$style = "";
	$style .= "color: ".$line['color']."; ";
	if ($line['italic'] == "Y") {
		$style .= "font-style: italic; ";
	}
	if ($line['bold'] == "Y") {
		$style .= "font-weight: bold; ";
	}
	if ($line['underline'] == "Y") {
		$style .= "text-decoration: underline;";
	}
	return $style;
}

function teams_list() {
	return array('A','B','C','D','E','F','G');
}

function division_list() {
    return array('1','2');
}

function managers_list() {
	$sql = "SELECT * FROM managers ORDER BY `order`";
	$query = mysql_query($sql);
	$managers = array();
	while ($row = mysql_fetch_assoc($query)) {
		$managers[] = $row;
	}
	return $managers;
}

function manager_name() {
	return getconfig('mangertext');
}

function js_array($name, $arr, $isnew = true) {
	// DO NOT PASS AN ARRAY THAT REFERENCES ITSELF (i.e. $_GLOBALS)
	//   you will cause an ininite Loop involving recursion == BAD!

	// Creating an array in Javascript with one element doesn't work.
	// Why? Because it interprets the element as the length of the
	// array.  The person who made that decision in Javascript should
	// be fired. -- Eric Anholt
	$return = false;
	if ($isnew) {
		$return .= "var ";
	}
	$return .= $name." = new Array();\n";
	foreach ($arr as $id => $key) {
		if (is_array($key)) {
			$return .= js_array($name.'["'.addslashes($id).'"]',$key,false);
		} else {
			$return .= $name.'["'.addslashes($id).'"] = "'.addslashes($key)."\";\n";
		}
	}
	return $return;
}

function vendor_address($vendorid) {
	if (!is_numeric($vendorid)) die("Invalid Vendor ID");
	$sql = "select vendors.* from forms left join vendors on vendors.ID=forms.vendor where forms.ID=".$vendorid;
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_assoc($query)) {
		$return = '';
		if($result['address'] != "") { $return .= $result['address']."<br>".$result['city'].", ".$result['state'].". ".$result['zip']."\n"; }
		if($result['phone'] != "") { $return .= "PH # ".$result['phone']."\n"; }
		if($result['fax'] != "") { $return .= "FAX # ".$result['fax']; }
	}
}


// Type may be D for Dealer or V for Vendor
function vendor_access($type, $id, $vendorid) {
	if (!is_numeric($id)) die("Invalid ID");
	if (!is_numeric($vendorid)) die("Invalid Vendor ID");
	if (secure_is_admin()) return true;
	if ($type == 'D') {
		$sql = "select id from form_access where form_access.user = '".$id."' AND form_access.form ='".$vendorid."'";
		$result = mysql_query($sql);
		checkDBError($sql);
		if (!mysql_num_rows($result)) {
			return false;
		} else {
			return true;
		}
	} elseif ($type == 'V') {
		$sql = "select id from vendor_access where user = '".$id."' AND vendor ='".$vendorid."'";
		$result = mysql_query($sql);
		checkDBError($sql);
		if (!mysql_num_rows($result)) {
			return false;
		} else {
			return true;
		}
	} else {
		die("vendor_access::Invalid Type of Access");
	}
}

// Create Thumbnail
function createThumb($filename) {
	global $basedir;
	global $thumbconverter; // 'gd1','gd2','imagemagick'

	$orig = $basedir."photos/".$filename;
	$target = $basedir."photos/t".$filename;
	$newwidth = 90;

	if (file_exists($orig)) {
		if ($thumbconverter == 'gd1' || $thumbconverter == 'gd2') {
			$im = ImageCreateFromJPEG($orig);
			$imagehw = GetImageSize($orig);

			$oldwidth = $imagehw[0];
			$oldheight = $imagehw[1];
			$newheight = $oldheight / ($oldwidth / $newwidth);
		
			/* Begin GD 1 Code */
			if ($thumbconverter == 'gd1') {
				$thumbim = ImageCreate($newwidth, $newheight);
				ImageCopyResized($thumbim, $im, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
			}
			/* End GD 1 Code */

			/* GD 2 Code */
			if ($thumbconverter == 'gd2') {
				$thumbim = ImageCreateTruecolor($newwidth, $newheight);
				ImageCopyResampled($thumbim, $im, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
			}
			/* End GD 2 Code */

			ImageJPEG($thumbim, $target, 80); //quality 0-100

			ImageDestroy($im);
			ImageDestroy($thumbim);
		} elseif ($thumbconverter == 'imagemagick') {
			exec('convert '.escapeshellarg($orig).' -colorspace rgb -thumbnail '.escapeshellarg($newwidth).' '.escapeshellarg($target));
                        exec('mogrify -colorspace rgb '.escapeshellarg($orig));
		} else {
			//echo "No Path!\n";
		}
	}
}

function viewpo_getmin($minimum) {
	$min_pieces = explode(":::", $minimum);
	$formatted = "false";
	$min_type = "";
	$raw_min = "";
	if (count($min_pieces) == 2 && $min_pieces[1] != "") {
		$raw_min = $min_pieces[1];
		$formatted = "true";
		$min_type = $min_pieces[0];
		if ($min_pieces[0] == "D") {
			$minimum = "$" . number_format($min_pieces[1], 2, '.', ',');
		}
		else {
			$minimum = $min_pieces[1] . " Pieces";
		}
	}
	return array('text' => $minimum,
		'type' => $min_type,
		'minimum' => $raw_min,
		'formatted' => $formatted);
}

function build_str($query_array) {
	$query_string = array();
	foreach ($query_array as $k => $v) {
		if (get_magic_quotes_gpc()) {
			$k = stripslashes($k);
			$v = stripslashes($v);
		}
		$query_string[] = urlencode($k).'='.urlencode($v);
	}
	return join('&', $query_string);
}

function rewrite_url($query_array) {
	parse_str($_SERVER['QUERY_STRING'],$query);
	foreach($query_array as $k => $v) {
		$query[urlencode($k)] = urlencode($v);
	}
	return build_str($query);
}

function checkbox2boolean($yn) {
	if (strtolower($yn) == 'y') return true;
	else return false;
}

function discountMatch($qty, $tierstr) {
        $stack = compileDiscount($tierstr);
        foreach ($stack as $row) {
            $applicable = false;
            if ($row['to'] == '0' && $row['from'] == '0') {
                $applicable = true;
            } elseif ($row['from'] <= $qty) {
                if (($row['to'] >= $qty)||($row['to'] == '0')) {
                    $applicable = true;
                }
            }
            if ($applicable) {
                $discount = $row['value'];
            }
        }
        return $discount;
}

/** Usable only on discountMatch result
 *
 * @param <type> $price price of item
 * @param <type> $discount discount %/$
 */
function discountCalc($price, $discount, $qty = 1, $seats = 0) {
    	$price = str_replace("$", "", $price);
        if ($price == 0)
            return 0;
	// Parse out the field...
	$amount = 0;
	$type = '$';

        if (strpos($discount, '%s$')) {
            $parts = explode('%s$', $discount);
            if ($seats) {
                $discount = 's$'.$parts[1];
            } else {
                $discount = $parts[0].'%';
            }
        }
        
	if (is_numeric($discount)) {
		// Just a #######
		$amount = $discount;
		$type = '$';
	} elseif (substr($discount,0,1) == '$'&&is_numeric(substr($discount,1))) {
		// Is $######.##
		$amount = substr($discount,1);
		$type = '$';
	} elseif (substr($discount,0,2) == 'i$'&&is_numeric(substr($discount,2))) {
		// Is i$######.##
		$amount = substr($discount,2);
		$type = 'i';
	} elseif (substr($discount,0,2) == 's$'&&is_numeric(substr($discount,2))) {
		// Is s$######.##
		$amount = substr($discount,2);
		$type = 's';
	} elseif (substr($discount,-1) == '%'&&is_numeric(substr($discount,0,strlen($discount)-1))) {
		// Is ####%
		$amount = substr($discount,0,strlen($discount)-1);
		$type = '%';
	} elseif (substr($discount,0,2) == '($'&&is_numeric(substr($discount,2,strlen($discount)-3))) {
		// Is ($####.##) Negative Monetary
		$amount = '-'.substr($discount,2,strlen($discount)-3);
		$type = '$';
	} elseif (substr($discount,0,3) == '(-$'&&is_numeric(substr($discount,3,strlen($discount)-4))) {
		// Is (-$####.##) Negative Monetary
		$amount = '-'.substr($discount,3,strlen($discount)-4);
		$type = '$';
	} elseif (substr($discount,0,2) == '-$'&&is_numeric(substr($discount,2,strlen($discount)-2))) {
		// Is -$####.## Negative Monetary
		$amount = '-'.substr($discount,3,strlen($discount)-2);
		$type = '$';
        }
        
        

	// Do the math...
	$return = $price;
	if ($type == '$') {
		$return = $price - $amount;
	} elseif ($type == '%') {
		$discount = $price * ($amount / 100);
		$return = $price - $discount;
	} elseif ($type == 'i') {
                $return = $price - ($amount * $qty);
        } elseif ($type == 's') {
                $return = $price - ($amount * $seats);
        }
	return $return;
}

/** Usable only on discountMatch result
 *
 * @param <type> $price price of item
 * @param <type> $discount discount %/$
 */
function discountToPercent($price, $discount, $qty = 1, $seats = 0) {
    	$price = str_replace("$", "", $price);
        if ($price == 0)
            return 0;
	// Parse out the field...
	$amount = 0;
	$type = '$';
	if (is_numeric($discount)) {
		// Just a #######
		$amount = $discount;
		$type = '$';
	} elseif (substr($discount,0,1) == '$'&&is_numeric(substr($discount,1))) {
		// Is $######.##
		$amount = substr($discount,1);
		$type = '$';
	} elseif (substr($discount,0,2) == 'i$'&&is_numeric(substr($discount,2))) {
		// Is i$######.##
		$amount = substr($discount,2);
		$type = 'i';
	} elseif (substr($discount,0,2) == 's$'&&is_numeric(substr($discount,2))) {
		// Is s$######.##
		$amount = substr($discount,2);
		$type = 's';
	} elseif (substr($discount,-1) == '%'&&is_numeric(substr($discount,0,strlen($discount)-1))) {
		// Is ####%
		$amount = substr($discount,0,strlen($discount)-1);
		$type = '%';
	} elseif (substr($discount,0,2) == '($'&&is_numeric(substr($discount,2,strlen($discount)-3))) {
		// Is ($####.##) Negative Monetary
		$amount = '-'.substr($discount,2,strlen($discount)-3);
		$type = '$';
	} elseif (substr($discount,0,3) == '(-$'&&is_numeric(substr($discount,3,strlen($discount)-4))) {
		// Is (-$####.##) Negative Monetary
		$amount = '-'.substr($discount,3,strlen($discount)-4);
		$type = '$';
	} elseif (substr($discount,0,2) == '-$'&&is_numeric(substr($discount,2,strlen($discount)-2))) {
		// Is -$####.## Negative Monetary
		$amount = '-'.substr($discount,3,strlen($discount)-2);
		$type = '$';
        }

	// Do the math...
	if ($type == '$') {
		return round(($amount / $price)*100,4);
	} elseif ($type == 'i') {
                return round(($amount / ($price * $qty)*100),4);
        } elseif ($type == 's') {
                return round(($amount / ($price * $seats)*100),4);
        } elseif ($type == '%') {
		return $amount;
	} else {
                return 0;
        }
}

function calcDiscount($amount, $qty, $discount, $seats = 0) {
    return $amount - calcItemDiscount($amount, $qty, $discount, $seats);
}

/** This will return the new discounted price
 * Keep in sync with JavaScript method in common.js
 */
function calcitemdiscount($price, $qty, $discount, $seats = 0) {
        $match = discountMatch($qty,$discount);
        //$percent = discountToPercent($price, $match);
        return discountCalc($price, $match, $qty, $seats);
        //$price - ($price * ($percent/100));
//	$price = str_replace("$", "", $price);
//	// Parse out the field...
//	$amount = 0;
//	$type = '$';
//	if (is_numeric($discount)) {
//		// Just a #######
//		$amount = $discount;
//		$type = '$';
//	} elseif (substr($discount,0,1) == '$'&&is_numeric(substr($discount,1))) {
//		// Is $######.##
//		$amount = substr($discount,1);
//		$type = '$';
//	} elseif (substr($discount,-1) == '%'&&is_numeric(substr($discount,0,strlen($discount)-1))) {
//		// Is ####%
//		$amount = substr($discount,0,strlen($discount)-1);
//		$type = '%';
//	} elseif (substr($discount,0,2) == '($'&&is_numeric(substr($discount,2,strlen($discount)-3))) {
//		// Is ($####.##) Negative Monetary
//		$amount = '-'.substr($discount,2,strlen($discount)-3);
//		$type = '$';
//	} elseif (substr($discount,0,3) == '(-$'&&is_numeric(substr($discount,3,strlen($discount)-4))) {
//		// Is (-$####.##) Negative Monetary
//		$amount = '-'.substr($discount,3,strlen($discount)-4);
//		$type = '$';
//	} elseif (substr($discount,0,2) == '-$'&&is_numeric(substr($discount,2,strlen($discount)-2))) {
//		// Is -$####.## Negative Monetary
//		$amount = '-'.substr($discount,3,strlen($discount)-2);
//		$type = '$';
//        }
//
//	// Do the math...
//	$return = $price;
//	if ($type == '$') {
//		$return = $price - $amount;
//	} elseif ($type == '%') {
//		$discount = $price * ($amount / 100);
//		$return = $price - $discount;
//	}
//	return $return;
}

function resortheaders($form_id, $prefix = '') {
	$sql = "SELECT `header_order` FROM `".$prefix."forms` WHERE `ID` = '".$form_id."'";
	$result = mysql_query($sql);
	checkDBError($sql);
	$result = mysql_fetch_assoc($result);
	if ($result['header_order'] != 'manual') {
		switch ($result['header_order']) {
			case 'decending': 
				$order = 0;
				break;
			case 'ascending': // Pass thru
			default:
				$order = 1;
				break;
		}
		
		$sql = "SELECT `header`, `ID`, `display_order` FROM `".$prefix."form_headers` WHERE `form` = '".$form_id."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$headers = array();
		while($row = mysql_fetch_assoc($query)) {
			$headers[] = $row;
		}
		$headers = compare_array($headers, 'header', 'numalpha', $order);
		
		$i = 0;
		foreach ($headers as $head) {
			++$i;
			if ($i != $head['display_order']) {
				$sql = "UPDATE `".$prefix."form_headers` SET `display_order` = '".$i."' WHERE `ID` = '".$head['ID']."'";
				mysql_query($sql);
				checkdberror($sql);
			}
		}
		 
		//$sql1 = "SET @c := 0";
		//$sql2 = "UPDATE `form_headers` SET `display_order` = (SELECT @c := @c + 1) WHERE `form` = '".$form_id."' ORDER BY `header` ".$order;
		//mysql_query($sql1);
		//checkDBerror($sql1);
		//mysql_query($sql2);
		//checkDBerror($sql2);
		snapshot_update('form', $form_id);
		return 0; // Tell them we sorted and did snapshot_update
	} else {
		return -1; // Tell them we did not do a snapshot_update
	}
}


 /** order = 0 for descending, 1 for ascending
  ** column = column to sort by
  ** type = function compare_type to use
  **      i.e. auto, numalpha
  **/
function compare_array($array,$column, $type, $dir) {
	if ($dir) {
		for ($i = 0; $i < sizeof($array); $i++)
			for ($c = 1; $c < sizeof($array); $c++)
				if (call_user_func('compare_'.$type, $array[$c][$column], $array[$c-1][$column])) {
					$temp = $array[$c];
					$array[$c] = $array[$c-1];
					$array[$c-1] = $temp;
				}
	} else {
		for ($i = 0; $i < sizeof($array); $i++)
			for ($c = 1; $c < sizeof($array); $c++)
				if (!call_user_func('compare_'.$type, $array[$c][$column], $array[$c-1][$column])) {
					$temp = $array[$c];
					$array[$c] = $array[$c-1];
					$array[$c-1] = $temp;
				}
	}
	ksort($array);
	return $array;
}

function compare_auto($first, $second) {
	if ($first < $second)
		return true;
	else
		return false;
}

function save_to_db($dbarray) {
	if (!is_array($dbarray)) die("save_to_db: Array Argument Required");
	foreach ($dbarray as $table => $rows) {
		if (!is_array($rows)) die("save_to_db: Array Elements are required to be arrays");
		foreach ($rows as $line) {
			if (!is_array($line)) die("save_to_db: Lines are required to be arrays");
			$update_columns = array();
			$insert_data_columns = array();
			$insert_value_columns = array();
			foreach($line as $column => $value) {
				$update_columns[] = "`".mysql_escape_string($column)."` = '".mysql_escape_string($value)."'";
				$insert_data_columns[] = mysql_escape_string($column);
				$insert_value_columns[] = mysql_escape_string($value);
			}
			$update = "UPDATE `".mysql_escape_string($table)."` SET ";
			$update .= implode(", ",$update_columns);
			if ($line['id']) {
				$update .= " WHERE `id` = '".mysql_escape_string($line['id'])."'";
			} elseif ($line['ID']) {
				$update .= " WHERE `ID` = '".mysql_escape_string($line['ID'])."'";
			} else {
				die("save_to_db: unable to find primary key on table `".mysql_escape_string($table)."`");
			}
			$insert = "INSERT INTO `".mysql_escape_string($table)."` (`".implode("`, `", $insert_data_columns)."`) VALUES ('".implode("', '",$insert_value_columns)."')";
			//$sql = $insert." ON DUPLICATE KEY ".$update;
			@mysql_query($insert);
			if (mysql_errno() == 1062) {
				mysql_query($update);
				checkDBerror($update);
			} else {
				checkDBerror($insert);
			}
		}
	}
}

function compare_numalpha($first, $second) {
	//$regex = "^([0-9]+(\.[0-9]+)?)";
	//$regs = array();
	//ereg($regex, $first, $regs);
	//$firstnum = $regs[1];
	//$regs = array();
	//ereg($regex, $second, $regs);
	//$secondnum = $regs[1];
	//unset($regs);
	$firstnum = floatval($first);
	$secondnum = floatval($second);
	
	if ($firstnum == $secondnum) {
		if ($first < $second)
			$return = true;
		else
			$return = false;
	} elseif ($firstnum == 0) {
		$return = false;
	} elseif ($secondnum == 0) {
		$return = true;
	} elseif ($firstnum < $secondnum) {
		$return = true;
	} else {
		$return = false;
	}
	//echo "$first < $second ($firstnum < $secondnum) = ".print_r($return, true)."<br>\n";
	return $return;
}

function MoS_checkip($ip) {
	global $MoS_clients;
	foreach ($MoS_clients as $name => $client) {
		if ($ip == $client['IP']) {
			return $name;
		}
	}
	return false;
}

function MoS_siteinfo($name) {
	global $MoS_clients;
	if (isset($MoS_clients[$name])) {
		return $MoS_clients[$name];
	} else {
		return array(); // Equiv to False
	}
}

function getPref($name) {
	global $gloginid, $MoS_enabled;
	$name = mysql_escape_string($name);
	if ($MoS_enabled) {
		$sql = "SELECT `content` FROM `MoS_login_prefs` WHERE `login_id` = '".$gloginid."' AND `name` = '".$name."'";
	} else {
		$sql = "SELECT `content` FROM `login_prefs` WHERE `login_id` = '".$gloginid."' AND `name` = '".$name."'";
	}
	$result = mysql_query($sql);
	checkDBerror($sql);
	if ($result = mysql_fetch_assoc($result)) {
		$content = $result['content'];
	} else {
		$content = null;
	}
	
	if ($content == "-btrue") $content = true;
	elseif ($content == "-bfalse") $content = false;
	elseif (substr($content,0,2) == "--") $content = substr($content, 1);
	
	return $content;
}

function setPref($name, $content) {
	global $gloginid, $MoS_enabled;
	$name = mysql_escape_string($name);
	if (is_null($content)) {
		if ($MoS_enabled) {
			$sql = "DELETE FROM `MoS_login_prefs` WHERE `login_id` = '".$gloginid."' AND `name` = '".$name."'";
		} else {
			$sql = "DELETE FROM `login_prefs` WHERE `login_id` = '".$gloginid."' AND `name` = '".$name."'";
		}
		mysql_query($sql);
		checkdberror($sql);
	} else {
		if ($content === true) $content = "-btrue";
		elseif ($content === false) $content = "-bfalse";
		elseif (substr($content,0,1) == "-") $content = "-".mysql_escape_string($content);
		else $content = mysql_escape_string($content);
		if ($MoS_enabled) {
			$sql = "REPLACE `MoS_login_prefs` SET `login_id` = '".$gloginid."', `name` = '".$name."', `content` = '".$content."'";
		} else {
			$sql = "REPLACE `login_prefs` SET `login_id` = '".$gloginid."', `name` = '".$name."', `content` = '".$content."'";
		}
		mysql_query($sql);
		checkdberror($sql);
	}
}

function compareArrays($dbArray=array(), $csvArray=array()){/*doesn't work with array of arrays*/
			//this function will take two arrays passed as variables, compare, and return matching, left non-matching and right non-matching lists.
			//Validate:
			if(!is_array($dbArray) || !is_array($csvArray)){return false;};
				$dbArray=array_unique($dbArray);
				$csvArray=array_unique($csvArray);
				$identical=array_intersect($dbArray, $csvArray);
				
			return array('identical'=>$identical,'added'=>array_diff($csvArray, $dbArray),'discarded'=>array_diff($dbArray, $identical)
			);
		}

/** Takes string, compiles array of arrays with order, from, to, value keys.
 * !!!Keep in sync with JavaScript method in common.js!!!
 * i.e.
 * from: 0:5:25%;6:35%
 * to: array(array('from' => 0, 'to' => 5, 'value' => '25%'),array('from' => '6','to' => 0, 'value' => 35%'))
 */
function compileDiscount($string) {
    // Decompile String into an array
    $stack = explode(";",$string);
    $order = 0;
    $new_stack = array();
    foreach ($stack as $id => $discount) {
        $temp = explode(":",$discount);
        $new_discount = array();
        if (count($temp) == 1) {
            $new_discount['from'] = '0';
            $new_discount['to'] = '0';
            $new_discount['value'] = $temp[0];
            $new_discount['order'] = $order;
        } elseif (count($temp) == 2) {
            $new_discount['from'] = $temp[0];
            $new_discount['to'] = '0';
            $new_discount['value'] = $temp[1];
            $new_discount['order'] = $order;
        } else {
            $new_discount = array();
            $new_discount['from'] = $temp[0];
            $new_discount['to'] = $temp[1];
            $new_discount['value'] = $temp[2];
            $new_discount['order'] = $order;
        }
        $new_stack[] = $new_discount;
        ++$order;
    }
    return $new_stack;
}

/**
 *  Takes array, and turns it into a string, reverse of compileDiscount
 *  uses order of array, does not obey 'order' key. (generally it's redundent
 *  in this situation anyway)
 */
function decompileDiscount($source) {
    $stack = array();
    foreach ($source as $row) {
        if ($row['to'] == 0 && $row['from'] == 0) {
            $stack[] = $row['value'];
        } elseif ($row['to'] == 0) {
            $stack[] = $row['from'].":".$row['value'];
        } else {
            $stack[] = $row['from'].":".$row['to'].":".$row['value'];
        }
    }
    return implode(";", $stack);
}

/** Loads Discount & Freight Values
 * i.e. loadDiscount('freight',array("vendor_id" => $ID),"vendor")
 *
 * @
 * @param string $type freight|discount type of value to load
 * @param array $keys of where for load
 * @param string $level user|vendor|form|form_item to load
 */
function loadDiscount($type, $keys, $level) {
    if ($type != 'discount' && $type != 'freight' && $type != 'markup' && $type != 'tier')
        die('LoadDiscount: Type must be either discount, freight, tier or markup');
    if ($type == 'markup' && $level != 'user')
        die('LoadDiscount: When type is markup, level must be user.');
    if ($type == 'tier' && $level != 'user')
        die('LoadDiscount: When type is tier, level must be user.');
    if (!is_array($keys)) die ("loadDiscount: Keys are not an array.");
    $sql = "SELECT `from`, `to`, `".$type."` FROM `".$level."_".$type."`";
    $where = array();
    foreach ($keys as $field => $id) {
        if (!is_numeric($id)) die ("loadDiscount: ".$field." is not numeric.");
        $where[] = "`".$field."` = '".$id."' ";
    }
    $sql .= " WHERE ".implode("AND ",$where)."ORDER BY `order` ASC";
    $query = mysql_query($sql);
    checkDBerror($sql);
    $stack = array();
    while ($row = mysql_fetch_assoc($query)) {
        $row['value'] = $row[$type];
        unset($row[$type]);
        $stack[] = $row;
    }
    return decompileDiscount($stack);
}

/** Saves Discount & Freight Values
 * i.e. loadDiscount('freight',array("vendor_id" => $ID),"vendor")
 *
 * @
 * @param string $type freight|discount type of value to load
 * @param array $keys of where for load
 * @param string $level user|vendor|form|form_item to load
 */
function saveDiscount($content, $type, $keys, $level) {
    if (!is_array($keys)) die ("saveDiscount: Keys is not an array.");

    if (loadDiscount($type, $keys, $level) == $content) {
        // Nothing to do here, Discounts are identical
        return;
    }

    $where = array();
    foreach ($keys as $field => $id) {
        if (!is_numeric($id)) die ("saveDiscount: ".$field." is not numeric.");
        $where[] = "`".$field."` = '".$id."' ";
    }
    $where = implode("AND ",$where);

    $columns = array();
    foreach ($keys as $field => $id) {
        $columns[] = "`".$field."`";
    }
    $columns = implode(", ",$columns);

    $ids = array();
    foreach ($keys as $field => $id) {
        $ids[] = "'".$id."'";
    }
    $ids = implode(", ",$ids);

    $stack = compileDiscount($content);

    // Remove Old Discount Set
    deleteDiscount($type, $keys, $level);

    // Add New Discount Set
    $sql = "INSERT INTO `".$level."_".$type."` (".$columns.",`order`,`from`,`to`,`".$type."`) VALUES";
    foreach ($stack as $row) {
        if ($type == 'markup') {
            if (substr($row['value'],-1,1) == "%" && is_numeric(substr($row['value'],0,strlen($row['value'])-1))) {
                $row['value'] = substr($row['value'],0,strlen($row['value'])-1);
            }
        }
        // echo "<pre>".print_r($row,true)."</pre>";
        $sql .= " (".$ids.",'".$row['order']."','".$row['from']."','".$row['to']."','".$row['value']."'),";
    }
    $sql[strlen($sql)-1] = ';'; // change last char to a space
    mysql_query($sql);
    checkDBerror($sql);
}

/** Removes Freight & Discount Entries
 *
 * @param string $type freight|discount type of value to load
 * @param array $keys of where for load
 * @param string $level user|vendor|form|form_item to load
 */
function deleteDiscount($type, $keys, $level) {
    $where = array();
    foreach ($keys as $field => $id) {
        if (!is_numeric($id)) die ("deleteDiscount: ".$field." is not numeric.");
        $where[] = "`".$field."` = '".$id."' ";
    }
    $where = implode("AND ",$where);

    // Remove Old Discount Set
    $sql = "DELETE FROM `".$level."_".$type."` WHERE ".$where;
    mysql_query($sql);
    checkDBerror($sql);
}

function copyDbEntry($fromTable, $toTable, $where_field, $where, $exclude_fields = array()) {
    	$sql = "SELECT * FROM `".$fromTable."` WHERE `".$where_field."` = '".$where."'";
        $result = mysql_query($sql);
        checkDBerror($sql, true, __FILE__, __LINE__);
        while ($line = mysql_fetch_assoc($result)) {
            foreach ($exclude_fields as $field) {
                unset($line[$field]);
            }
            $keys_assemble = "`".implode("`, `", array_keys($line))."`";
            $assemble = "'".implode("', '",$line)."'";
            $sql = "INSERT INTO `".$toTable."` (".$keys_assemble.") VALUES (".$assemble.")";
            mysql_query($sql);
            checkDBerror($sql, true, __FILE__, __LINE__);
        }
}

function secure_is_franchisee() {
    global $userid;
    if (secure_is_vendor())
        return false;
    if (secure_is_admin())
        return true;
    if (!is_numeric($userid))
        return false;
    $sql = "SELECT `dealer_type` FROM `users` WHERE `ID` = '".$userid."'";
    $query = mysql_query($sql);
    checkDBerror($sql);
    if ($user = mysql_fetch_assoc($query)) {
        if ($user['dealer_type'] == 'B' || $user['dealer_type'] == 'F') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function secure_is_licensee() {
    global $userid;
    if (secure_is_vendor())
        return false;
    if (secure_is_admin())
        return true;
    if (!is_numeric($userid))
        return false;
    $sql = "SELECT `dealer_type` FROM `users` WHERE `ID` = '".$userid."'";
    $query = mysql_query($sql);
    checkDBerror($sql);
    if ($user = mysql_fetch_assoc($query)) {
        if ($user['dealer_type'] == 'B' || $user['dealer_type'] == 'L') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
