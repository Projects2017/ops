<HTML><HEAD><TITLE>PMD Furniture Dealer Utilities - Vendor Shortage Claim Online Viewer</TITLE>
<link rel="stylesheet" href="../styles.css" type="text/css">
</HEAD>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<CENTER> <IMG SRC=/images/logo.gif><P>
<H3>Vendor Shortage Claims Forms Submitted</H3><BR>

<TABLE BORDER='1' CELLPADDING='1' CELLSPACING='1' VALIGN=TOP ALIGN=CENTER VALIGN=TOP WIDTH=785>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=230 BGCOLOR=#CCCC99><a href="<?php $_SERVER['PHP_SELF'] ?>?order=name_from"><b>Name:</b></a></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=195 BGCOLOR=#CCCC99><a href="<?php $_SERVER['PHP_SELF'] ?>?order=vendor"><b>Vendor:</b></a></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=40 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=PO">PO#:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=40 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=Credit_or_Backorder">Credit/Backorder:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=135 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=trucking_company">Trucking_company:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=20 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=PO">SHORTED_ON_ORIG:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=20 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=PO">SHORTED_ON_PACKING_LIST:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=75 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=timestamp">Date/Time<BR>Submitted:</a></b></TH>





<?php
include ('display_functions.inc.php');
$records = loadcsv(array('name_from','email_from','vendor','trucking_company','PO','Credit_or_Backorder','items_shorted','SHORTED_ON_ORIG_PO','SHORTED_ON_PACKING_LIST','email_on','timestamp'),'../docs/shortages.csv');
if ($_GET[order]) {
   if ($_GET[order] == 'order_date')
      $records = matrixSort($records, $_GET[order],1);
   else
      $records = matrixSort($records, $_GET[order]);
}

  foreach ($records as $record) {
      echo "<TR>
      <TD WIDTH=230><B><A HREF=mailto:".$record['email_from']."subject=re: PMD Shortage Claim PO#".$record['PO'].">".$record['name_from']."</A></B></TD>
      <TD WIDTH=195>".$record['vendor']."</TD>
      <TD WIDTH=40>".$record['PO']."</TD>
      <TD WIDTH=40>".$record['Credit_or_Backorder']."</TD>
      <TD WIDTH=135>".$record['trucking_company']."</TD>
      <TD WIDTH=20>".$record['SHORTED_ON_ORIG_PO']."</TD>
      <TD WIDTH=20>".$record['SHORTED_ON_PACKING_LIST']."</TD>
      <TD WIDTH=75>".$record['timestamp']."</TD></TR>";
      echo "<TR><TD WIDTH=780 COLSPAN=8 ALIGN=LEFT>".$record['items_shorted']."<BR><P>&nbsp;</P></TD></TR>\n";

 }

?>

</CENTER>

</TABLE>



</BODY></HTML>