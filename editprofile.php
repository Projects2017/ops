<?php
require("database.php");
require("secure.php");

// echo "blah";
$user = $userid; // Just trust it... secure.php sets it.
if (!is_numeric($user))
    die("NOT LOGGED IN!");

$sql = "SELECT * FROM `users` WHERE `ID` = '".$user."'";
$query = mysql_query($sql);
checkDBerror($sql);
while ($row = mysql_fetch_assoc($query)) {
    $result = $row;
}
?>
<html>
    <head>
        <title>RSS User Profile Edit</title>
        <link rel="stylesheet" href="../styles.css" type="text/css">
    </head>
    <body>
        <?php require("menu.php"); ?>
        <br />
        <form action="editprofile.php" method="post">
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr><td colspan="2" class="orderTDheading">Dealer Profile</td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Dealer Name: </td><td width="70%" class="text_12"><?php= htmlentities($result['last_name']) ?></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Location: </td><td width="70%" class="text_12"><?php= htmlentities($result['first_name']) ?></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Phone: </td><td width="70%"><input type="text" size="20" maxlength="" name="phone" id="phone" value="<?php= htmlentities($result['phone']) ?>"><input type="hidden" name="old_phone" value="<?php= htmlentities($result['phone']) ?>"></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Cell Phone: </td><td width="70%"><input type="text" size="20" maxlength="" name="cell_phone" id="cell_phone" value="<?php= htmlentities($result['cell_phone']) ?>"><input type="hidden" name="old_cell_phone" value="<?php= htmlentities($result['cell_phone']) ?>"></td></tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Cell Phone Provider:</td>
                    <td width="70%"><select name="cell_provider">
                            <?php
                            // set the initial cell provider info based upon what's in the db currently
                            require_once('include/cellprovider.class.php');
                            $provider = new cellProvider($result['cell_provider']);
                            // grab all the providers from the db and set up the select options, choosing the current one
                            $sql = "SELECT code, name FROM cell_providers ORDER BY name";
                            $que = mysql_query($sql);
                            checkDBerror($sql);
                            while($res = mysql_fetch_assoc($que)) {
                                ?><option value="<?php= htmlentities($res['code']) ?>"<?php if($provider->getCode() == $res['code']) echo ' selected'; ?>><?php= htmlentities($res['name']) ?></option>
                            <?php
                            }
                            ?></select><input type="hidden" name="old_cell_provider" value="<?php= htmlentities($provider->getCode()) ?>"></td>
                </tr>
                <tr><td class="fat_black_12" align="right" width="30%">Fax: </td><td width="70%"><input type="text" size="20" maxlength="" name="fax" id="fax" value="<?php= htmlentities($result['fax']) ?>"><input type="hidden" name="old_fax" value="<?php= htmlentities($result['fax']) ?>"></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Email: </td><td width="70%"><input type="text" size="20" maxlength="" name="email" id="email" value="<?php= htmlentities($result['email']) ?>"><input type="hidden" name="old_email" value="<?php= htmlentities($result['email']) ?>"></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Email (2nd): </td><td width="70%"><input type="text" size="20" maxlength="" name="email2" id="email2" value="<?php= htmlentities($result['email2']) ?>"><input type="hidden" name="old_email2" value="<?php= htmlentities($result['email2']) ?>"></td></tr>
                <tr><td class="fat_black_12" align="right" width="30%">Email (3rd): </td><td width="70%"><input type="text" size="20" maxlength="" name="email3" id="email3" value="<?php= htmlentities($result['email3']) ?>"><input type="hidden" name="old_email3" value="<?php= htmlentities($result['email3']) ?>"></td></tr>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Login<!--s--></td>
                    <!--<td class="orderTDheading" width="30%"><span class="text_12">[<a href="editpermissions.php?newuser=true">Add User</a>]</span></td>-->
                </tr>
                <tr>
                    <td class="fat_black_12" width="30%">Username</td>
                    <td class="fat_black_12" width="40%">Password</td>
                </tr>
                <?php
                $sql = "SELECT `id`, `username`, `password` FROM `login` WHERE `relation_id` = '".$user."' AND `type` != 'V'";
                $query = mysql_query($sql);
                checkDBerror($sql);
                while ($row = mysql_fetch_assoc($query)) {
                    $row['key'] = uniqid("");
                    ?>
                <tr>
                    <td class="text_12" width="30%"><?php= htmlentities($row['username']) ?><input type="hidden" name="old_password<?php=$row['id']?>" value="<?php=$row['key']?>"></td>
                    <td class="text_12" width="40%"><input type="password" name="password<?php=$row['id']?>" value="<?php=$row['key']?>"></td>
                    <!--<td class="text_12" width="30%">[<a href="editpermissions.php?ID=<?php= $row['id'] ?>">Permissions</a>] [<a href="editpermissions.php?ID=<?php= $row['id'] ?>&action=delete">Delete</a>]</td>-->
                </tr>
                <?php
                }
                ?>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500" class="userAddressTable">
                <tr>
                    <td colspan="2" class="orderTDheading">Primary Address</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Address:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['address'])?></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">City:</td>

                    <td class="text_12" width="70%"><?php= htmlentities($result['city'])?></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">State:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['state'])?></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Zip Code:</td>

                    <td class="text_12" width="70%"><?php= htmlentities($result['zip'])?></td>
                </tr>
                <?php if ($result['address2']): ?>
                <tr>
                    <td colspan="2" class="orderTDheading">Secondary Address</td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Address:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['address2'])?></td>

                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">City:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['city2'])?></td>
                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">State:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['state2'])?></td>

                </tr>
                <tr>
                    <td align="right" class="fat_black_12" width="30%">Zip Code:</td>
                    <td class="text_12" width="70%"><?php= htmlentities($result['zip2'])?></td>
                </tr>
                <?php endif; ?>
            </table>
            <table border="0" cellspacing="1" cellpadding="4" width="500">
                <tr>
                    <td width="30%">&nbsp;</td>
                    <td width="70%">
                        <div>
                            <input type="submit" name="action" style="background-color:#CA0000;color:white" value="Submit Changes">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>