<?php
require("database.php"); //allow database connection
require("secure.php");   //require user authentication
?>

<HTML><HEAD><TITLE>Retail Service Systems Utilities - Parts Request Form</TITLE>
<link rel="stylesheet" href="styles.css" type="text/css">
</HEAD>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<CENTER> <IMG SRC=./images/logo.gif></CENTER>
<center><table>
<tr>
<td>
</td>

<td>
<center>
<b><i><font face="Arial,Helvetica" size=-1>RSS PARTS REQUEST FORM</font></i></b>
<br><b><i><font face="Arial,Helvetica"><font size=-1>Direct questions to:<BR>
	Shelly Graham - Dealer Support - Damage and Claims Supervisor<BR>
	614-442-5645<BR>
	614-583-0606 (fax)<BR>
	<A HREF=mailto:shellygraham@pmdfurniture.com>shellygraham@pmdfurniture.com</A><BR>
</font></font></i></b>
</td>
</tr>
</table></center>

<p><form name=request enctype="multipart/form-data" method=post action=./util/parts_request_processor.php >
<hr>
<center>
<p>
<center><table WIDTH="75%" >
<tr>
<td WIDTH="30%">
<div align=right><font face="Arial,Helvetica"><font size=-1>Delear Name:&nbsp;</font></font></div>
</td>

<td><input TYPE="text" name=name_from_req value="" SIZE=30></td>
</tr>

<tr>
<td>
<div align=RIGHT><font face="Arial,Helvetica"><font size=-1>Your email
address:&nbsp;</font></font></div>
</td>

<td><input TYPE="text" name=email_from value="" SIZE=30 OnChange="redoto()"></select></td>
</tr>
</table></center>

<center>
<p>
<hr WIDTH="100%">
<p><b><font face="Arial,Helvetica">PMD PARTS REQUEST INFORMATION</font></b></center><br>

<table WIDTH="90%" >
<tr>



<td ALIGN=RIGHT><font face="Arial,Helvetica"><font size=-1>Vendor:</font></font></td>
<td>
<SELECT
 NAME='Vendor_req'>
<OPTION VALUE=''>Please choose Vendor
<OPTION VALUE='Acme'>Acme
<OPTION VALUE='Bernards'>Bernards
<OPTION VALUE='Boyd'>Boyd
<OPTION VALUE='Campbell Leather'>Campbell Leather
<OPTION VALUE='Coaster Futons'>Coaster Futons
<OPTION VALUE='Coaster Leather'>Coaster Leather
<OPTION VALUE='Corsicana'>Corsicana
<OPTION VALUE='Crown Mark'>Crown Mark
<OPTION VALUE='Guardsman'>Guardsman
<OPTION VALUE='Home Elegance '>Home Elegance
<OPTION VALUE='King Koil'>King Koil
<OPTION VALUE='Leather Bella'>Leather Bella
<OPTION VALUE='Mantua'>Mantua
<OPTION VALUE='Master Design'>Master Design
<OPTION VALUE='Progressive'>Progressive
<OPTION VALUE='Restonic'>Restonic
<OPTION VALUE='Royal Heritage'>Royal Heritage
<OPTION VALUE='Simmons'>Simmons
<OPTION VALUE='Standard'>Standard
<OPTION VALUE='Symbol'>Symbol
<OPTION VALUE='Symbol Futon'>Symbol Futon
<OPTION VALUE='United'>United
</SELECT>
</td>
</tr>

<tr>
<td ALIGN=RIGHT WIDTH="30%"><font face="Arial,Helvetica"><font size=-1>PO#</font></font></td>

<td><input TYPE="text" NAME="PO#_reqnum" SIZE=20 Value=""></td>
</tr>


<tr>
<td ALIGN=RIGHT><font face="Arial,Helvetica"><font size=-1>Item:</font></font></td>

<td><input TYPE="text" NAME="Item_req" SIZE=20

VALUE=""></td>
</tr>


<tr>
<TD ALIGN=RIGHT VALIGN=TOP><font face="Arial,Helvetica"><font size=-1>List of parts<BR>needed:<BR></font></font>

<td><textarea name="Parts_requested_req" rows=10 cols=60 wrap=physical></textarea>
</tr>

<tr>
<TD VALIGN=TOP ALIGN=RIGHT><font face="Arial,Helvetica"><font size=-1>Shipping Address:<BR></font></font>

<td><textarea name="Shipping_Address_req" rows=6 cols=50 wrap=physical></textarea>
</tr>


</table>
<P>


Fax Bill Of Lading - when noting shortages that are on the BOL and refused items due to damage <P>


            <input type=hidden name=send value=1>
            <input type=submit value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Submit Parts Request&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">

</body>
</html>
