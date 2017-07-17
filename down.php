<?php
$name = "Users";
if (!$attntext) {
    # $attntext = "We are performing scheduled upgrades, please check back in 10-15 minutes.";
    $attntext = "Call Jerry or Gary for more info.";
} else {
    if (get_magic_quotes_gpc()) $attntext = stripslashes($attntext);
}
?>
<HTML>
<HEAD>
    <TITLE>RSS</TITLE>
</HEAD>
<BODY LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" BGCOLOR="#EDECDA">
<CENTER>
    <P>&nbsp;<P>&nbsp;<P>
        <!--<IMG SRC="/images/pmdani_sm2.gif" VALIGN=MIDDLE ALIGN=CENTER><P>-->

        <FONT FACE="ARIAL, HELVETICA, SANS-SERIF" SIZE="4">
            <?php if ($_SERVER['HTTP_HOST'] == 'login2.retailservicesystems.com'): ?>
            Our website is currently down for temporary maintenance.<P>Please accept our apologies for the inconvenience.<P>
        <?php endif; ?>
    <P>
        <?php if ($_SERVER['HTTP_HOST'] != 'login2.retailservicesystems.com'): ?>
    <TABLE BORDER=1 WIDTH=320 CELLPADDING=4><TR><TD BGCOLOR=#FFFFFF>
                <CENTER><B>Attention <?=$name ?>:</B><BR><?=$attntext ?></CENTER>
            </TD></TR></TABLE>
    <?php endif; ?>
    </FONT>
</CENTER>
</DIV>
</BODY>
</HTML>
