<?php
require("database.php");
require("secure.php");
require('include/mcpclass.php');
$mcp = new McpControl();

$adminmode = false;
$extrahref = "";
$extrahrefsingle = "";
if (isset($_GET['admin'])&&$_GET['admin'] == 1&&secure_is_admin()) {
    $adminmode = true;
    $extrahref = "&admin=1";
    $extrahrefsingle = "?admin=1";
}

$mcp_id = $_GET['id'];
if (!is_numeric($mcp_id)) {
    throw new Exception("Non numeric ID");
}

if ((isset($_GET['method']) && $_GET['method'] = "update")) {
    $args = array();
    $errors = '';
    $return = true;
    if (isset($_POST['starttime']) 
            && is_string($_POST['starttime'])) {
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',$_POST['starttime'])===1) {
            $args['starttime'] = $_POST['starttime'];
        } else {
            $errors .= "Start Time MUST be in HH:MM:SS format!\n";
            $return = false;
        }
    }
    if (isset($_POST['stoptime'])
            && is_string($_POST['starttime'])) {
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',$_POST['stoptime'])===1) {
            $args['stoptime'] = $_POST['stoptime'];
        } else {
            $errors .= "Stop Time MUST be in HH:MM:SS format!\n";
            $return = false;
        }
    }
    if (isset($_POST['actionlist'])) {
        $args['actionlist'] = $_POST['actionlist'];
    }
    
    // Only process if changes were passed in.
//    echo "<pre>"; print_r($args); echo "</pre>";
//    echo "<pre>"; print_r($errors); echo "</pre>";
//    echo "<pre>"; print_r($_POST); echo "</pre>";
//    exit(0);
    if ($return && $args) {
        if (secure_is_dealer()) {
            $return = $mcp->updateControl($userid, $mcp_id, $args);
        } elseif (secure_is_admin()) {
            $return = $mcp->updateControl(null, $mcp_id, $args);
        }
    }
    if ($return) {
        header("Location: clmcp.php".$extrahrefsingle);
    } else {
        header("Location: clmcp_edit.php?id=".$mcp_id."&errors=".urlencode($errors).$extrahref);
    }
    exit(0);
}
// $userid = 13;
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>
    <?php require('menu.php'); ?>
    <h1>CraigsList Posting Control</h1>
    <?php $mcplist = $mcp->getControls($userid, $mcp_id); ?>
    <?php if (!isset($mcplist[0])): ?>
        No such device!
        <?php exit(0); ?>
    <?php endif; ?>
    <?php $mcpitem = $mcplist[0]; ?>
    <?php /* echo "<pre>"; print_r($mcpitem); echo "</pre>"; */ ?>
    <form action="clmcp_edit.php?method=update&id=<?php echo $mcp_id.$extrahref; ?>" method="post" enctype="multipart/form-data">
        <table border="0" cellspacing="1" cellpadding="4" width="500">
            <tr><td class="fat_black_12" align="right" width="30%">Name: </td><td width="70%"><?php echo htmlentities($mcpitem['name']); ?></td></tr>
            <tr><td class="fat_black_12" align="right" width="30%">Start Time: </td><td width="70%"><input type="text" size="20" maxlength="" name="starttime" id="starttime" value="<?php echo htmlentities($mcpitem['starttime']); ?>"> (HH:MM:SS)</td></tr>
            <tr><td class="fat_black_12" align="right" width="30%">Stop Time: </td><td width="70%"><input type="text" size="20" maxlength="" name="stoptime" id="stoptime" value="<?php echo htmlentities($mcpitem['stoptime']); ?>"> (HH:MM:SS)</td></tr>
            <tr><td class="fat_black_12" align="right" width="30%">Action List: </td><td width="70%"><input type="text" size="20" maxlength="" name="actionlist" id="actionlist" value="<?php echo htmlentities($mcpitem['actionlist']); ?>"></td></tr>
            <tr>
                <td width="30%">&nbsp;</td>
                <td width="70%">
                    <div>
                        <input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update">	
                    </div>	
                </td>
	    </tr>
            <tr>
                <td width="30%">&nbsp;</td>
                <td width="70%">
                    <div>
                        [<a href="clmcp.php">Return to MCP Controls List</a>]
                    </div>	
                </td>
	    </tr>
        </table>
    </form>
</body>
</html>