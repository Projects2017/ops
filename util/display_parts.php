<HTML><HEAD><TITLE>PMD Furniture Dealer Utilities - Parts Requests Online Viewer</TITLE>
<link rel="stylesheet" href="../styles.css" type="text/css">
</HEAD>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<CENTER> <IMG SRC=/images/logo.gif><P>
<H3>Parts Requests Submitted</H3><BR>

<TABLE BORDER='1' CELLPADDING='1' CELLSPACING='1' VALIGN=TOP ALIGN=CENTER VALIGN=TOP WIDTH=780>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=225 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=name_from">Name:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=225 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=Vendor">Vendor:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=65 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=PO">PO#:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=200 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=Item">Item:</a></b></TH>
<TH VALIGN=TOP ALIGN=CENTER WIDTH=65 BGCOLOR=#CCCC99><b><a href="<?php $_SERVER['PHP_SELF'] ?>?order=timestamp">Date<BR>Submitted:</a></b></TH>
<?php
include ('display_functions.inc.php');

$records = loadcsv(array('name_from','email_from','Vendor','PO','Item','Parts_requested','Shipping_Address','emailon','timestamp'),'../docs/parts.csv');



if ($_GET[order]) {
   if ($_GET[order] == 'order_date')
      $records = matrixSort($records, $_GET[order],1);
   else
      $records = matrixSort($records, $_GET[order]);
}

  foreach ($records as $record) {
echo "<TR><TD WIDTH=225><B>".$record['name_from']."</B></TD><TD WIDTH=225>".$record['Vendor']."</TD><TD WIDTH=65>".$record['PO']."</TD><TD WIDTH=200>".$record['Item']."</TD><TD WIDTH=65>".$record['timestamp']."</TD></TR>";
echo "<TR><TD WIDTH=780 COLSPAN=5 ALIGN=LEFT>".$record['Parts_requested']."<BR></TD></TR>";
echo "<TR><TD WIDTH=780 COLSPAN=5 ALIGN=LEFT>".$record['Shipping_Address']."<BR><P>&nbsp;</P></TD></TR>";
 }

?>

</CENTER>

</TABLE>

