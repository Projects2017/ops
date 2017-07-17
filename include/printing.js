/*
 * boolean printLabel(string lines) - Prints a set of lines centered onto a thermal label printer via EPL2 using PMD Print Plugin.
 *                                      Returns true on success, false on failure.
 */
function printLabel (lines) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		try {
			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
			try {
				obNewAXComponent.printLabel(lines.toUpperCase());
				return true;
			} catch (e) {
				alert("There was an error printing the label.");
				return false;
			}
		} catch(e) {
			alert("You must install the PMDPrint plugin before you can print labels.");
			return false;
		}
	} else {
		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
		return false;
	}
}

/*
 * boolean printRaw(string lines) - Prints a set of lines centered onto a thermal label printer via EPL2 using PMD Print Plugin.
 *                                      Returns true on success, false on failure.
 */
function printRaw (lines) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		try {
			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
			try {
				obNewAXComponent.WriteEPL(lines);
				return true;
			} catch (e) {
				alert("There was an error printing the label.");
				return false;
			}
		} catch(e) {
			alert("You must install the PMDPrint plugin before you can print labels.");
			return false;
		}
	} else {
		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
		return false;
	}
}

/*
 * void setLabelPrinter() - Pops up a dialog to select the label printer to print to.
 */
function setLabelPrinter () {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		try {
			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
			obNewAXComponent.setLabelPrinter();
		} catch(e) {
			alert("You must install the PMDPrint plugin before you can print labels.");
		}
	} else {
		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
	}
}

/*
 * void printPage(message) - Prints the current page. Tries to use the plugin first, but falls back to presenting a dialog,
 *                           in the case of the printer dialog, the message is displayed before continuing.
 */
var WB_set = false
function printPage(message) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		try {
			if (!WB_set) {
				var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
				WB_code = "<OBJECT ID=\"WB\" WIDTH=0 HEIGHT=0 CLASSID=\"CLSID:8856F961-340A-11D0-A96B-00C04FD705A2\"></OBJECT>";
				document.body.insertAdjacentHTML('beforeEnd', WB_code);
				WB_set = true;
			}
			WB.ExecWB(6, 2); // Print without confirmation
		} catch (e) {
			window.print();
			if (message)
				alert(message);
		}
	} else {
		window.print(); // Fall back to asking them to print
		if (message)
			alert(message);
	}
}

/*
 * void printWalmart() - Prints the Walmart packing slip, based upon whether the order
 *                       is shipped to home or to store
 */
function printWalmart (orderId, shipDate, shipper, track, billingname, shiptoname , orderby, rcvdby, subtot, shipcost,
tax, total, tc, asn, wmpo, items) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		try {
			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
			if (obNewAXComponent.pluginVersion() < 2) {
				alert("You have an outdated PMDPrint Plugin, please update.");
				return false;
			}
			try {
				obNewAXComponent.WalmartInitialize(
					 orderId, // OrderId
					 shipDate, // ShipDate
					 shipper, // ShippedVia
					 track, // Tracking
					 billingname, // BillingName
					 shiptoname, // Ship To Name
					 orderby, // OrderedBy
					 rcvdby, // RecievedBy
					 subtot, // SubTotal
					 shipcost, // Shipping
					 tax, // Tax
					 total, // Total
					 tc, // TC
					 asn, // ASN
					 wmpo // Walmart PO# (Only used for Ship2Store)
				);
				for (var i in items)
				{
					obNewAXComponent.WalmartAddItem(items[i][0], items[i][1], items[i][2], items[i][3], items[i][4]);
				}
				obNewAXComponent.WalmartPrint();
				return true;
			} catch (e) {
				alert("There was an error printing.\nError Name:" + e.name + "\nMessage: "+e.message);
				return false;
			}
		} catch(e) {
			alert("You must install the PMDPrint plugin before you can print labels.");
			return false;
		}
	} else {
		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
		return false;
	}
}

/*
 * void printTarget() - Prints the Target packing slip, based upon whether the order
 *                       is amazon or target based
 *			items should have Shipmethod, Giftwrap, Quantity, Productid, Productnm, sku, upc, Returnmethod, gftmssg, asin
 */
function printTarget (orderId, amazon, altOrderId, warehouse, soldto, shipto, Gftmssg, Orderstatusmssg, items)
{
	if (navigator.appName == 'Microsoft Internet Explorer')
	{
		try
		{
			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
			if (obNewAXComponent.pluginVersion() < 2)
			{
				alert("You have an outdated PMDPrint Plugin, please update.");
				return false;
			}
			try
			{
				// TargetInitialize(String Orderid, Boolean Amazon, String AltOrderid, String Warehouse, 
            	//		String Soldto, String Shipto, String Gftmssg, String Orderstatusmssg)
				obNewAXComponent.TargetInitialize(
					 orderId, // OrderId
					 amazon, // Amazon true/false
					 altOrderId, // Alt Order Id
					 warehouse, // Warehouse
					 soldto, // Sold To
					 shipto, // Ship To
					 Gftmssg, // Gift Message
					 Orderstatusmssg // Order Status Message
				);
				for (var i in items)
				{
					// TargetAddItem(String Shipmethod, String Giftwrap, String Quantity, String Productid,
					//    String Productnm, String sku, String upc, String Returnmethod, String gftmssg, String asin)
					obNewAXComponent.TargetAddItem(items[i][0], items[i][1], items[i][2], items[i][3], items[i][4],items[i][5],items[i][6],items[i][7],items[i][8],items[i][9]);
				}
				obNewAXComponent.TargetPrint();
				return true;
			}
			catch (e)
			{
				alert("There was an error printing.\nError Name:" + e.name + "\nMessage: "+e.message);
				return false;
			}
		}
		catch(e)
		{
			alert("You must install the PMDPrint plugin before you can print labels.");
			return false;
		}
	}
	else
	{
		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
		return false;
	}
}

//<object ID='PMDPrint' name='PMDPrint' WIDTH=0 HEIGHT=0 CLASSID='CLSID:9CF0975F-43DB-3307-83FF-8E73172C723C'></object>