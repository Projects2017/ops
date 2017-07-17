<?php
require("database.php");
require("secure.php");
// User ID
//$userid = 332; // 332 has results
$pw = 'gobucks1';
$un = 'ops';
$url = "http://clapp.thedailyzen.net/api/ops_cl_emails.php?DealerID=".$userid."&un=".$un."&pw=".$pw;
$contents = @file_get_contents($url);
$result = json_decode($contents);
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>
    <?php require('menu.php'); ?>
    <h1>CraigsList Accounts</h1>
    <table border="0" cellspacing="0" cellpadding="5" width="760">
        <thead>
            <tr bgcolor="#CCCC99">
                <th class="fat_black_12">Region</th>
                <th class="fat_black_12">Market</th>
                <th class="fat_black_12">Email</th>
                <th class="fat_black_12">Password</th>
                <th class="fat_black_12">Phone</th>
                <th class="fat_black_12">Days2Use</th>
                <th class="fat_black_12">Note</th>
                <th class="fat_black_12">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$result || $result->error): ?>
            <tr bgcolor="#FFFFFF">
                <td class="text_12" colspan="8" align="center">An error has been encountered connecting to the CraigsList Management System.<br />Please contact <a href="mailto:support@retailservicesystems.com">support@retailservicesystems.com</a>.</td>
            </tr>
            <?php else: ?>
                <?php if (!$result->rowcount): ?>
                <tr bgcolor="#FFFFFF">
                    <td class="text_12" colspan="8" align="center">No CraigsList Accounts on Record</td>
                </tr>
                <?php else: ?>
                <?php foreach ($result->rows as $email): ?>
                <tr bgcolor="#FFFFFF">
                    <td class="text_12"><?php echo htmlentities($email->Region); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Market); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Email); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Password); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Phone); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Days2Use); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Note); ?></td>
                    <td class="text_12"><?php echo htmlentities($email->Status); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <pre>
        <?php /* print_r($result); */ ?>
    </pre>
</body>
</html>
