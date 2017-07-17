using System;
using System.Runtime.InteropServices;

namespace PMDPrint
{
    // need to make an interface to make it visible as an ActiveX Control
    public interface AxInterface
    {
        void WriteEPL(string rawLines);
        void printLabel(string lines);
        void setLabelPrinter();
        void WalmartPrint();
        void WalmartInitialize(String OrderId, String ShipDate, String ShippedVia, String Tracking, String BillingName, String ShipToName, String OrderedBy,
            String RecievedBy, String SubTotal, String Shipping, String Tax, String Total, String TC, String ASN, String WalmartPO);
        void WalmartAddItem(String Qty, String UPC, String Description, String UnitPrice, String Amount);
        int pluginVersion();
    }

    // Class to print to Zebra label printers
    // on either network share, LPT, or COM
    // adapted from http://blogs.lessthandot.com/index.php/DesktopDev/MSTech/VBNET/printing-to-a-zebra-printer-from-vb-net
    [ClassInterface(ClassInterfaceType.AutoDispatch)]
    public class PMDPrint : AxInterface
    {
        // public methods
        WalmartPackingSlip WalmartSlip = new WalmartPackingSlip();

        // writes a command to the printer
        public void WriteEPL(string rawLines)
        {
            String pName = null;
            pName = LabelPrinter.FindPrinter();
            if (rawLines != null && pName != null) RawPrinterHelper.SendStringToPrinter(pName, rawLines);
        }

        public void printLabel(string lines)
        {
            String output;
            output = LabelPrinter.PrintLabel(lines);
            //MessageBox.Show(output);
            WriteEPL(output);
        }

        public void setLabelPrinter()
        {
            LabelPrinter.SetPrinter();
        }

        public void WalmartPrint()
        {
            if (WalmartSlip == null)
            {
                throw new Exception("WalmartInitialize must be called before WalmartPrint.");
            }
            WalmartSlip.Print();
        }

        public void WalmartInitialize(String OrderId, String ShipDate, String ShippedVia, String Tracking, String BillingName, String ShipToName, String OrderedBy,
            String RecievedBy, String SubTotal, String Shipping, String Tax, String Total, String TC, String ASN, String WalmartPO)
        {
            WalmartSlip.OrderId = OrderId;
            WalmartSlip.ShipDate = ShipDate;
            WalmartSlip.ShippedVia = ShippedVia;
            WalmartSlip.Tracking = Tracking;
            WalmartSlip.BillingName = BillingName;
            WalmartSlip.ShipToName = ShipToName;
            WalmartSlip.OrderedByAddr = OrderedBy;
            WalmartSlip.ShippedToAddr = RecievedBy;
            WalmartSlip.SubTotal = SubTotal;
            WalmartSlip.Shipping = Shipping;
            WalmartSlip.Tax = Tax;
            WalmartSlip.Total = Total;
            WalmartSlip.TC = TC;
            WalmartSlip.ASN = ASN;
            WalmartSlip.WalmartPO = WalmartPO;
            WalmartSlip.EmptyItems();
        }

        public void WalmartAddItem(String Qty, String UPC, String Description, String UnitPrice, String Amount)
        {
            if (WalmartSlip == null)
            {
                throw new Exception("WalmartInitialize must be called before WalmartAddLine.");
            }
            WalmartSlip.AddItem(Qty, UPC, Description, UnitPrice, Amount);
        }

        public int pluginVersion()
        {
            return 2;
        }
    }
}
