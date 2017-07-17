<HTML><HEAD><TITLE>PMD Furniture Dealer Utilities - Damage Claim Online Viewer</TITLE>
<link rel="stylesheet" href="/styles.css" type="text/css">
</HEAD>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<TABLE WIDTH=100% ALIGN=LEFT VALIGN=TOP>
	<TR>
		<TD COLSPAN=2>
			<CENTER>
			<P><IMG SRC=/images/logo.gif></P>
			<P><H3>Damage and Claims Forms Editor</H3></P>
			</CENTER>

		</TD>
	</TR>
	<TR><TD COLSPAN=2 ALIGN=CENTER>

		<TABLE BORDER=1 CELLPADDING=1 CELLSPACING=15 VALIGN=TOP ALIGN=CENTER><TR><TD>

				<TABLE BORDER=1>
				<TR><TD>Name_from:: </B></TD><TD><input SIZE=10 TYPE="text" name=name_from value="<?php $record['name_from'] ?>"</TD></TR>
				<TR><TD>Email_from: </B></TD><TD><input SIZE=10 TYPE="text" name=email_from value="<?php $record['email_from'] ?>"></TD></TR>
				<TR><TD>Vendor: </B></TD><TD><input SIZE=10 TYPE="text" Name="Vendor" value="<?php $record['Vendor'] ?>"></TD></TR>
				<TR><TD>PO#: </B></TD><TD><input SIZE=10 TYPE="text" NAME="PO" Value="<?php $record['"PO"'] ?>"></TD></TR>
				<TR><TD>Order_date: </B></TD><TD><input SIZE=10 TYPE="text" NAME="Order_date" VALUE="<?php $record['"Order_date"'] ?>"></TD></TR>
				</TABLE>

		</TD><TD>

				<TABLE BORDER=1>
				<TR><TD>Item: </B></TD><TD><input SIZE=10 TYPE="text" NAME="Item"  VALUE="<?php $record['"Item"'] ?>"></TD></TR>
				<TR><TD>Trucking: </B></TD><TD><input SIZE=10 TYPE="text" NAME="Trucking company" VALUE="<?php $record['Trucking'] ?>"></TD></TR>
				<TR><TD>Carton_damage: </B></TD><TD><input SIZE=10 TYPE="text" NAME="Carton_damage" VALUE="<?php $record['Carton_damage'] ?> "></TD></TR>
				<TR><TD>Date Submitted: </B><TD>Timestamp here<?php $record['timestamp'] ?></TD></TR>
				</TABLE>

		</TD></TR></TABLE>

	</TD></TR>
	<TR>
		<TD ALIGN=RIGHT VALIGN=TOP><B>Description of Damage:</B></TD><TD><textarea name="Description_of_damage" rows=10 cols=60 wrap=physical></textarea></TD>
	</TR><TR>
		<!-- note to will: what if make default assignment something like Unassigned and default date_assigned the date of the creation timestamp -->
		<TD ALIGN=RIGHT><B>Assigned to:</B> <TD><?php $record['Vendor'] ?> on <?php $record['date_assigned'] ?></TD>
	</TR><TR>

		<TD ALIGN=RIGHT VALIGN=TOP><B>Assign this claim to:</B></TD><TD>
				<TABLE>
					<TR><TD ALIGN=RIGHT><INPUT TYPE=RADIO NAME="employee_assignment" VALUE="Shelly Graham"></TD><TD>Shelly Graham</TD></TR>
					<TR><TD ALIGN=RIGHT><INPUT TYPE=RADIO NAME="employee_assignment" VALUE="Gary Davis"></TD><TD>Gary Davis</TD></TR>
					<TR><TD ALIGN=RIGHT><INPUT TYPE=RADIO NAME="employee_assignment" VALUE="<?php $record['Vendor'] ?>"></TD><TD><?php $record['Vendor'] ?></TD></TR>
				</TABLE>
		</TD>
	</TR><TR>
		<TD ALIGN=RIGHT VALIGN=TOP><B>Action Taken:</B> </TD><TD><TEXTAREA NAME=action_taken ROWS=10 COLS=60 WRAP=PHYSICAL></TEXTAREA></TD>
	</TR><TR>
		<TD ALIGN=RIGHT VALIGN=TOP><B>Comments:</B> </TD><TD><TEXTAREA NAME=comments ROWS=10 COLS=60 WRAP=PHYSICAL></TEXTAREA></TD>
	</TR><TR>
		<TD ALIGN=RIGHT><B>Delete Record?:</B> </TD><TD><INPUT TYPE=CHECKBOX NAME=delete_record> <I><FONT COLOR=RED>WARNING: This will permanently delete this record, it will not be retrievable!</FONT></I></TD>
	</TR><TR>
		<TD>&nbsp;</TD><TD<input type=submit value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Submit Changes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"></TD>
	</TR>
</TABLE>

</TD></TR></TABLE>
<P>&nbsp;<BR>&nbsp;<BR></P>

</BODY></HTML>