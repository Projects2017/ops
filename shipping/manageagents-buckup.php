<?php
// manageagents.php
// This page manages the shipping agents in the system
// can add/delete/modify
// admins+ only
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
    require_once("../secure.php");
require_once('inc_shipping.php');
if(!secure_is_admin())
{
    die('Unauthorized access.');
}
// see if we're in viewmode
if($_COOKIE['viewmode'])
{
    $viewmode = true;
    setcookie('viewmode','',time()-2);
}
// sort through all the error fields and prep for display
if($_COOKIE['agentErr'])
{
    $badfields = explode(',',$_COOKIE['badFields']);
    foreach($_COOKIE as $key => $cookies)
    {
        if(substr($key, 0, 9)=='agentErr_')
        {
            $badfield[substr($key, 9)] = stripslashes($_COOKIE[$key]);
            setcookie($key,time()-2);
        }
    }
    $agentErr = $_COOKIE['agentErr'];
    setcookie('agentErr','',time()-2);
}
if($_COOKIE['agentMsg'])
{
    $agentMsg = $_COOKIE['agentMsg'];
    setcookie('agentMsg','',time()-2);
}
$modmode = $_COOKIE['agentModNumber'] ? $_COOKIE['agentModNumber'] : false;
$delmode = $_COOKIE['agentDel'] ? $_COOKIE['agentDel'] : false;
if($modmode)
{
    setcookie('agentMod',1);
}
else
{
    setcookie('agentMod','',time()-2);
    setcookie('agentModNumber','',time()-2);
}
if(!$delmode)
{
    setcookie('agentDel','',time()-2);
}
setcookie('badFields','',time()-2);
// basic header for the page

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Shipping Agents Management</title>
    <meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/shipping.css" rel="stylesheet">
    <script src="shipping.js" language="javascript" type="text/javascript"></script>
</head>
<body>
<? include_once("../menu.php"); ?>
<!-- Shipping Agent Management -->
<?
/*
				setcookie('newAgentErr',$oldcookie."<br />Postal code must be a five-digit code");
			setcookie('agentErr','There are problems with the entry you made. Fix it.');
			setcookie('badFields',implode(',',$errs));
*/
?><p class="title">Shipping Agents Management<br />
<form name="agentmgt" id="agentmgt" action="do_manageagents.php" method="post">
    <? if($delmode) { ?><input type="hidden" name="verified" value="1"><? } ?>
    <table border="0" width="80%" align="center">
        <? if($agentErr)
        {
            ?><tr><td colspan="2" align="center" style="color: red; font-weight: bold"><?= $agentErr ?></td></tr><? } else if($agentMsg)
        {
            ?><tr><td colspan="2" align="center" style="font-weight: bold"><?= $agentMsg ?></td></tr><? }
        ?>
        <tr>
            <td align="left">Mode:</td><td align="left"><span id="agent_select"<? if(!$modmode && !$delmode) { ?> style="visibility: hidden"<? } ?>>
Select Agent:&nbsp;<select id="select_agent" name="select_agent" onchange="submit();">
<option value="n">---</option>
                        <?
                        $sql = "SELECT sa.ID as saID, sn.last_name as uname FROM shipping_agents sa INNER JOIN snapshot_users sn ON sa.snapshot_userid = sn.ID";
                        $que = mysql_query($sql);
                        checkdberror($sql);
                        while($result = mysql_fetch_assoc($que))
                        {
                            ?><option value="<?= $result['saID'] ?>"<? if($result['saID']==$modmode || $result['saID']==$delmode) echo ' selected="selected"'; ?>><?= ($result['saID']+1000) ?>: <?= $result['uname'] ?></option>
                            <?
                        }?>
</select>
</span></td>
        </tr>
        <tr>
            <td rowspan="2" align="left"><input type="radio" name="agentmode" value="add" onclick="setAgentMode('add');"<? if(!$modmode && !$delmode) { ?> checked="checked"<? } ?>>&nbsp;Add Agent<br />
                <input type="radio" name="agentmode" value="modify" onclick="setAgentMode('modify');"<? if($modmode) { ?> checked="checked"<? 	} ?>>&nbsp;Modify Agent<br />
                <input type="radio" name="agentmode" value="delete" onclick="setAgentMode('delete');"<? if($delmode) { ?> checked="checked"<? } ?>>&nbsp;Delete Agent<br />
                <input type="radio" name="agentmode" value="view" onclick="setAgentMode('view');"<? if($viewmode) { ?> checked="checked"<? } ?>>&nbsp;View Agents
            </td>
            <?
            if($modmode || $delmode)
            {
                $targid = $modmode ? $modmode : $delmode;
                $sql = "SELECT * FROM shipping_agents WHERE ID = $targid";
                $que = mysql_query($sql);
                checkdberror($sql);
                $res = mysql_fetch_assoc($que);
                $sql = "SELECT last_name, address, address2, city, state, zip, phone FROM snapshot_users WHERE ID = {$res['snapshot_userid']}";
                $que = mysql_query($sql);
                checkdberror($sql);
                $result = mysql_fetch_assoc($que);
            }
            if(!$viewmode)
            {
            ?><td align="left"><span id="agent_fields"><input type="text" name="last_name" size="30" value="<? if($agentErr) { echo $badfield['last_name']; } else if($result) { echo $result['last_name']; } else { ?>[Agent Name]<? } ?>"><br />
<input type="text" name="address" size="40" value="<? if($agentErr) { echo $badfield['address']; } else if($result) { echo $result['address']; } else { ?>[Address]<? } ?>"><br />
<input type="text" name="address2" size="40" value="<? if($agentErr) { echo $badfield['address2']; } else if($result) { echo ($result['address2']!='' ? $result['address2'] : "[Address cont'd]"); } else { ?>[Address cont'd]<? } ?>"><br />
<input type="text" name="city" size="30" value="<? if($agentErr) { echo $badfield['city']; } else if($result) { echo $result['city']; } else { ?>[City]<? } ?>">&nbsp;<input type="text" name="state" size="4" value="<? if($agentErr) { echo $badfield['state']; } else if($result) { echo $result['state']; } else { ?>[ST]<? } ?>">&nbsp;<input type="text" name="zip" size="12" value="<? if($agentErr) { echo $badfield['zip']; } else if($result) { echo $result['zip']; } else { ?>[PostalCode]<? } ?>"><br />
<input type="text" name="phone" size="30" value="<? if($agentErr) { echo $badfield['phone']; } else if($result) { echo ($result['phone']!='' ? $result['phone'] : '[Phone]'); } else { ?>[Phone]<? } ?>"></td>
        </tr>
        <tr>
            <td align="left"><span id="agent_submit"><input type="submit" name="submit_form" value="<? if($modmode) { echo "Save Changes to this Agent"; } else if($delmode) { echo 'Delete This Agent'; } else { echo "Add Shipping Agent"; } ?>"><? if($modmode || $delmode) { ?>&nbsp;&nbsp;<input type="submit" name="cancel_form" value="Cancel <? echo $modmode ? "Changes" : "Delete"; ?>"><? } ?></span></td>
        </tr>
        <? }
        else
        {
        // viewmode
        // start up the viewing table
        ?><td><table border="0">
                <tr><td colspan="6" align="center">View Shipping Agents</td></tr>
                <tr><td align="left">Agent ID</td><td align="left">Name</td><td align="left">City</td><td align="left">State</td><td align="left">Zip</td><td align="left">Phone #</td></tr>
                <?
                // get all the data we need
                $sql = "SELECT sa.ID AS agentID, last_name, city, state, zip, phone FROM snapshot_users users INNER JOIN shipping_agents sa ON sa.snapshot_userid = users.ID";
                $que = mysql_query($sql);
                checkdberror($sql);
                while($result = mysql_fetch_assoc($que))
                {
                    ?><tr><td align="left"><?= $result['agentID']+1000 ?></td><td align="left"><?= $result['last_name'] ?></td><td align="left"><?= $result['city']; ?></td><td align="left"><?= $result['state'] ?></td><td align="left"><?= $result['zip'] ?></td><td align="left"><?= $result['phone'] ?></td></tr>
                    <?
                }
                ?><tr><td colspan="6" align="center"><button onclick="csvExport();">Export to CSV</button></td></tr>
                <input type="hidden" id="csvexport" name="csvexport" value=""><?
                } ?>
            </table>
</form>
</body>
</html>