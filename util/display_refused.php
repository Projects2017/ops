<HTML><HEAD><TITLE>PMD Furniture Dealer Utilities - Refused Claim Online Viewer</TITLE>
<link rel="stylesheet" href="../styles.css" type="text/css">
</HEAD>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<CENTER> <IMG SRC=/images/logo.gif><P>
<H3>Refused Claims Forms Submitted</H3><BR>

<TABLE BORDER='1' CELLPADDING='1' CELLSPACING='1' VALIGN=TOP ALIGN=CENTER VALIGN=TOP WIDTH=785>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=200 BGCOLOR=#CCCC99><a href="<?php $_SERVER['PHP_SELF'] ?>?order=name_from"><b>name_from:</b></a></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=150 BGCOLOR=#CCCC99><a href="<?php $_SERVER['PHP_SELF'] ?>?order=vendor"><b>Vendor:</b></a></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=25 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=PO">PO#:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=100 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=trucking_company">Truckin_company:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=75 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=carton_damage">Carton_damage:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=55 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=timestamp">Date/Time<BR>Submitted:</a></b></TH>





<?php
include ('display_functions.inc.php');
$records = loadcsv(array('name_from','email_from','vendor','PO','trucking_company','carton_damage','items_refused','description','email_on','timestamp'),'../docs/refused.csv');
if ($_GET[order]) {
   if ($_GET[order] == 'order_date')
      $records = matrixSort($records, $_GET[order],1);
   else
      $records = matrixSort($records, $_GET[order]);
}

  foreach ($records as $record) {
      echo "<TR><TD WIDTH=200><B>".$record['name_from']."</B></TD><TD WIDTH=150>".$record['vendor']."</TD><TD WIDTH=25>".$record['PO']."</TD><TD WIDTH=100>".$record['trucking_company']."</TD><TD WIDTH=75>".$record['carton_damage']."</TD><TD WIDTH=55>".$record['timestamp']."</TD></TR>";
      echo "<TR><TD WIDTH=780 COLSPAN=8 ALIGN=LEFT>".$record['items_refused']."<BR><P>&nbsp;</P></TD></TR>\n";
      echo "<TR><TD WIDTH=780 COLSPAN=8 ALIGN=LEFT>".$record['description']."<BR><P>&nbsp;</P></TD></TR>\n";
 }

?>

</CENTER>

</TABLE>



</BODY></HTML>