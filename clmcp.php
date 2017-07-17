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

if ((isset($_GET['method']) && $_GET['method'] = "update")) {
    $mcp_id = $_GET['id'];
    if (!is_numeric($mcp_id)) {
        throw new Exception("Non numeric ID");
    }
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'start') {
            $mcp->updateControl($userid, $mcp_id, array('paused' => 0, 'override' => 0));
        } elseif ($_GET['action'] == 'stop') {
            $mcp->updateControl($userid, $mcp_id, array('paused' => 1, 'override' => 0));
        }
    }
    header("Location: clmcp.php".$extrahrefsingle);
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
    <?php $mcplist = $mcp->getControls($adminmode?null:$userid); ?>
    <?php /* echo "<pre>"; print_r($mcplist); echo "</pre>"; */ ?>
    <?php if ($adminmode): ?>
    [<a href="clmcp.php">Dealer Mode</a>]
    <?php elseif (secure_is_admin()): ?>
    [<a href="clmcp.php?admin=1">Admin Mode</a>]
    <?php endif; ?>
    <table class="sortable" id="mcplist" border="0" cellspacing="0" cellpadding="5" width="90%">
      <tr bgcolor="#fcfcfc"> 
        <?php if ($adminmode): ?>
        <th class="fat_black_12" align="left">Dealer</td>
        <?php endif; ?>
        <th class="fat_black_12">Name</td>
        <th class="fat_black_12" align="center">Start Time</td>
        <th class="fat_black_12" align="center">End Time</td>
        <th class="fat_black_12" align="right">Action List</td>
        <th class="fat_black_12" align="center">Actions</td>
      </tr>
      <?php foreach ($mcplist as $mcpitem): ?>
      <tr <?php if ($mcpitem['paused']): ?> bgcolor="#CCCCCC"<?php elseif ($mcpitem['override']): ?> bgcolor="#EFC2C6"<?php endif; ?>>
        <?php if ($adminmode): ?>
          <?php $dealer = db_user_getuserinfo($mcpitem['dealerid'], array('last_name','first_name')); ?>
          <td class="text_12"><?php echo htmlentities($dealer['last_name'].', '.$dealer['first_name']); ?></td>
        <?php endif; ?>
        <td class="text_12"><?php echo htmlentities($mcpitem['name']); ?></td>
        <?php if ($mcpitem['paused']): ?>
        <td class="text_12" align="center" colspan="2">Never Starts</td>
        <?php elseif ($mcpitem['override']): ?>
        <td class="text_12" align="center" colspan="2">Never Stops</td>
        <?php else: ?>
        <td class="text_12" align="center"><?php echo htmlentities($mcpitem['starttime']); ?></td>
        <td class="text_12" align="center"><?php echo htmlentities($mcpitem['stoptime']); ?></td>
        <?php endif; ?>
        <td class="text_12" align="right"><?php echo htmlentities($mcpitem['actionlist']); ?></td>
        <td class="text_12" align="center"<?php if (!$mcpitem['active']): ?> bgcolor="#CCCCCC"<?php endif; ?>>[<a href="/clmcp_edit.php?id=<?php echo $mcpitem['id']; ?><?php echo $extrahref; ?>">Edit</a>] [<a href="/clmcp.php?method=update&id=<?php echo $mcpitem['id']; ?>&action=start<?php echo $extrahref; ?>">Start</a>] [<a href="/clmcp.php?method=update&id=<?php echo $mcpitem['id']; ?>&action=stop<?php echo $extrahref; ?>">Stop</a>]</td>
      </tr>
      <?php endforeach; ?>
    </table>
    <br />
    <br />
    The Master Control Panel (MCP) is now used to control all posting on the devices. The MCP should be running on the devices at all times, including during non-posting hours. All updates to interval, posting hours, etcetera should now be made from the MCP User Interface here.<br />
    <br />
    <b>Edit:</b>  Controls the function of the MCP and give instructions to each computer.
    <ul>
        <li>Start Time : The time at which normal posting should begin for this area. (eastern time zone)</li>
        <li>Stop Time : The time at which normal posting should end for this area. (eastern time zone)</li>
        <li>Action List : A set of instructions for the device. Multiple instructions must be separated with a colon. Valid instructions are p, r, and n (post, renew, and repost) followed by a number indicating the interval (in minutes) to wait before executing the next instruction in the list. The posting scripts will follow each of the instructions in Action List, then check back with MPC on the server once those instructions are completed. For example:
            <ul>
                <li>P10 will post one ad then wait approximately 10 min interval before checking back with MCP for new instructions</li>
                <li>r12 will renew one ad then wait 12 min before checking back with MCP for new instructions</li>
                <li>n20 will repost the last ad on the page (must be on "active" listing) then wait 20 min</li>
                <li>p10:r10 will post one ad, wait for 10 minutes, then renew one ad and wait 10 minutes before checking back with MCP for new instructions</li>
            </ul>
        </li>
    </ul>
    <b>Stop:</b>  Stops the computer from running the MCP script.<br />
    <br />
    <b>Start:</b>  Restarts the computer running the MCP script.
</body>
</html>