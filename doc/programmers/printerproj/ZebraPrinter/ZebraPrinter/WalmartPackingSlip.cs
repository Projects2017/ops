using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Printing;
using System.Windows.Forms;

namespace PMDPrint
{
    class WalmartPackingSlip
    {
        /* Variables Covering Order */
        public String OrderId = "";
        public String ShipDate = "";
        public String ShippedVia = "";
        public String Tracking = "";
        public String BillingName = "";
        public String ShipToName = "";
        public String OrderedByAddr = "";
        public String ShippedToAddr = "";
        private List<String[]> Items = new List<String[]>();
        public String SubTotal = "";
        public String Shipping = "";
        public String Tax = "";
        public String Total = "";
        public String TC = "";
        public String ASN = "";
        public String WalmartPO = "";
        public String VendorInfo = ""; // BoL/Soflex PO#

        /* Internal Variables for Printing */
        private PrintDocument page1 = new PrintDocument();
        private PrintDocument page2 = new PrintDocument();

        public void Print()
        {
            page1.PrintPage += new PrintPageEventHandler(
                  PrintPage1);
            page2.PrintPage += new PrintPageEventHandler(
                  PrintPage2);
            page1.Print();
            page2.Print();
            // If Ship to Store, Print Label too!
            if (ASN.Length > 0)
            {
                String pName = null;
                pName = LabelPrinter.FindPrinter();
                if (pName != null) RawPrinterHelper.SendStringToPrinter(pName, PrintLabel());
            }
        }

        private void PrintPage1(Object sender, PrintPageEventArgs e)
        {
            DrawPage1(e.Graphics);
            return;
        }
        private void PrintPage2(Object sender, PrintPageEventArgs e)
        {
            DrawPage2(e.Graphics);
            return;
        }
        
        public void DemoData()
        {
            OrderId = "2677031471571";  
            ShipDate = "12/24/2002";
            ShippedVia = "UPS Ground";
            Tracking = "1Z853AE2034368972";
            ShipToName = "Amy Abad";
            BillingName = "Amy C. Abad";
            OrderedByAddr = "121 A Street\nSan Francisco, CA 94121\nUSA";
            ShippedToAddr = "7000 Marina Blvd\nBrisbane, CA 94005\nUSA";
            SubTotal = "109.23"; 
            Shipping = "6.11";
            Tax = "9.01";
            Total = "124.35";
            TC = "6402048697665599570453";
            //ASN =  "00000100150343024603";
            ASN = "";
            EmptyItems();
            AddItem("2", "0007628165510", "Easy Bake Oven", "14.73", "29.46");
            AddItem("1", "0002724259516", "Sony CD Walkman with Car Kit D-E756CK", "79.77", "79.77");
        }
        
        public void DumpData()
        {
            MessageBox.Show(
                "Stuff\n" +
                OrderId + "\n" +
                ShipDate + "\n" +
                ShippedVia  + "\n" +
                Tracking  + "\n" +
                BillingName + "\n" +
                OrderedByAddr + "\n" +
                ShipToName + "\n" +
                ShippedToAddr + "\n" +
                SubTotal + "\n" +
                Shipping + "\n" +
                Tax + "\n" +
                Total + "\n" +
                TC  + "\n" +
                ASN + "\n"
                );
        }
        /**
         * Adds Items to the Packing Slip
         */
        public void AddItem(String Qty, String UPC, String Description, String UnitPrice, String Amount)
        {
            Items.Add(new string[5] { Qty, UPC, Description, UnitPrice, Amount });
        }

        /**
         * Removes all saved items
         */
        public void EmptyItems()
        {
            Items.Clear();
        }

        public void DrawPage1(Graphics g)
        {
            Font printFont = new Font("Courier New", 3);
            Font headerFont = new Font("Ariel", 16);
            StringFormat Xaxis = new StringFormat();

            g.PageUnit = GraphicsUnit.Millimeter;

            try
            {
                // Logos
                //Image WalmartLogo = new Bitmap("C:\\Program Files\\PMDPrint\\Walmart Logo.JPG");
                Image WalmartLogo = new Bitmap("C:\\Program Files\\PMDPrint\\wm-logo-bw.tif");
                g.DrawImage(WalmartLogo, 10, 9, 55F, 26);
                WalmartLogo.Dispose();
                WalmartLogo = new Bitmap("C:\\Program Files\\PMDPrint\\walmart_com.jpg");
                g.DrawImage(WalmartLogo, 122, 20.5f, 63, 8);
                WalmartLogo.Dispose();
            }
            catch (Exception x)
            {
                MessageBox.Show("Walmart Header Images are missing, please reinstall plugin.");
                throw;
            }
            // Shipment Summary Text Bar (80% gray background)
            g.FillRectangle(new SolidBrush(Color.FromArgb(255, 51, 51, 51)), (float)9.5, 33, 182, 5);
            g.DrawString("Shipment Summary", new Font("Helvetica", 12, FontStyle.Bold), Brushes.White, new RectangleF(11, 33, 100, 5));


            // Addresses Area
            g.DrawString("Ordered by:", new Font("Helvetica", 10, FontStyle.Bold), Brushes.Black, new RectangleF((float)11.5, 42, 82, 4));
            g.DrawString("Received by:", new Font("Helvetica", 10, FontStyle.Bold), Brushes.Black, new RectangleF((float)93.5, 42, 82, 4));

            // == Gray Headers behind Bounding Boxes ==
            Brush headerBrush = new SolidBrush(Color.FromArgb(255, 204, 204, 204));
            // Behind Second Header Row (Item Level Headers)
            g.FillRectangle(headerBrush, (float)9.7, 78, (float)181.8, 5);
            // Bottom Box (next to totals)
            g.FillRectangle(headerBrush, (float)9.7, 154, (float)123.8, 17);

            // == Bounding Boxes ==
            Pen boxPen = new Pen(Brushes.Black, (float)0.2);
            // Make Bounding Box around all Order Info (except addresses)
            g.DrawRectangle(boxPen, (float)9.7, 68, (float)181.8, 103);
            // Make Bounding Box Around Qty
            g.DrawRectangle(boxPen, (float)9.7, 78, (float)12.5, 76);
            // Make Bounding Box Around Item (UPC)
            g.DrawRectangle(boxPen, (float)22.2, 78, (float)31.8, 76);
            // Make Bounding Box Around Description
            g.DrawRectangle(boxPen, 54, 78, (float)79.5, 76);
            // Make Bounding Box Around Unit Price
            g.DrawRectangle(boxPen, (float)133.5, 78, (float)28.5, 93);
            // Bounding Box is already present for Amount
            // Line below Subtotal
            g.DrawLine(boxPen, (float)133.5, (float)158.25, (float)191.5, (float)158.25);
            // Line below Shipping
            g.DrawLine(boxPen, (float)133.5, (float)162.5, (float)191.5, (float)162.5);
            // Line below Tax
            g.DrawLine(boxPen, (float)133.5, (float)166.75, (float)191.5, (float)166.75);

            // Bottom Bounding Box (Gray space)
            g.DrawRectangle(boxPen, (float)9.7, 154, (float)181.8, 17);

            // Text Centering Format
            StringFormat StringStyle = new StringFormat();
            StringStyle.Alignment = StringAlignment.Near;
            StringStyle.LineAlignment = StringAlignment.Center;

            Font orderFont = new Font("Helvetica", 9);

            float shipDatePos = g.MeasureString(ShipDate, orderFont).Width + 2.4f;
            float shippedPos = g.MeasureString(ShippedVia, orderFont).Width + 2.4f;
            float trackPos = 0f;
            if (Tracking.Length > 0)
            {
                trackPos = g.MeasureString(Tracking, orderFont).Width + 2.4f;
            }

            if (shipDatePos < 32.2f)
                shipDatePos = 32.2f;
            if (shippedPos < 34.8f)
            {
                if (trackPos > 0)
                    shippedPos = 34.8f;
                else
                    shippedPos = 77.8f;
            }
            if (trackPos < 43f && trackPos != 0)
                trackPos = 43f;

            RectangleF ShipDateBox = new RectangleF(9.7f, 68, shipDatePos, 10);
            RectangleF ShippedBox = new RectangleF(ShipDateBox.X + ShipDateBox.Width, 68, shippedPos, 10);
            RectangleF TrackBox = new RectangleF(ShippedBox.X + ShippedBox.Width, 68, trackPos, 10);
            RectangleF OrderIdBox = new RectangleF(TrackBox.X + TrackBox.Width, 68, 191.5f - (TrackBox.X + TrackBox.Width), 10);

            //float finalShippedPos = shipDatePos + 9.7f + 1.2f;
            //float finalTrackPos = finalShippedPos + shippedPos;

            // Behind First Header Row (Order Level Headers)
            g.FillRectangle(headerBrush, (float)9.7, 68, (float)181.8, 5);

            // Make Bounding Box Around Shipment Date
            g.DrawRectangle(boxPen, ShipDateBox.X, ShipDateBox.Y, ShipDateBox.Width, ShipDateBox.Height);
            //g.DrawRectangle(boxPen, (float)9.7, 68, (float)32.2, 10);
            // Make Bounding Box Around Shipping Via
            g.DrawRectangle(boxPen, ShippedBox.X, ShippedBox.Y, ShippedBox.Width, ShippedBox.Height);
            //g.DrawRectangle(boxPen, (float)41.9, 68, (float)34.8, 10);
            // Make Bounding Box Around Tracking #
            if (trackPos > 0)
                g.DrawRectangle(boxPen, TrackBox.X, TrackBox.Y, TrackBox.Width, TrackBox.Height);
            //g.DrawRectangle(boxPen, (float)76.7, 68, 45, 10);
            // Make Bounding Box Around Order #
            g.DrawRectangle(boxPen, OrderIdBox.X, OrderIdBox.Y, OrderIdBox.Width, OrderIdBox.Height);
            //g.DrawRectangle(boxPen, (float)121.7, 68, (float)69.8, 10);
            
            // Reduce Box for Header Text
            ShipDateBox.Height -= 5;
            ShippedBox.Height -= 5;
            TrackBox.Height -= 5;
            OrderIdBox.Height -= 5;

            // Create Margins
            ShipDateBox.X += 1.2f;
            ShipDateBox.Width -= 2.4f;
            ShippedBox.X += 1.2f;
            ShippedBox.Width -= 2.4f;
            TrackBox.X += 1.2f;
            TrackBox.Width -= 2.4f;
            OrderIdBox.X += 1.2f;
            OrderIdBox.Width -= 2.4f;

            // Draw in Order Header Strings
            g.DrawString("Shipment Date", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, ShipDateBox, StringStyle);
            g.DrawString("Shipped via", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, ShippedBox, StringStyle);
            if (trackPos > 0)
                g.DrawString("Tracking #", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, TrackBox, StringStyle);
            g.DrawString("Order #", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, OrderIdBox, StringStyle);

            ShipDateBox.Y += 5f;
            ShippedBox.Y += 5f;
            TrackBox.Y += 5f;
            OrderIdBox.Y += 5f;

            // Shipment date
            g.DrawString(ShipDate, orderFont, Brushes.Black, ShipDateBox, StringStyle);
            // Shipped via
            g.DrawString(ShippedVia, orderFont, Brushes.Black, ShippedBox, StringStyle);
            // Tracking #:
            if (trackPos > 0)
                g.DrawString(Tracking, orderFont, Brushes.Black, TrackBox, StringStyle);
            // Order #
            g.DrawString(OrderId, orderFont, Brushes.Black, OrderIdBox, StringStyle);

            // Draw in Item Header Strings
            g.DrawString("Qty.", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(9.7 + 1.2), 78, (float)19.1, 5), StringStyle);
            g.DrawString("Item (UPC)", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(22.2 + 1.2), 78, (float)31.8, 5), StringStyle);
            g.DrawString("Description", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(54 + 1.2), 78, (float)79.5, 5), StringStyle);
            g.DrawString("Unit Price", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(133.5 + 1.2), 78, (float)28.5, 5), StringStyle);
            g.DrawString("Amount", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(162 + 1.2), 78, (float)29.2, 5), StringStyle);

            g.DrawString("Subtotal", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(133.5 + 1.2), 154, (float)28.5, (float)4.25), StringStyle);
            g.DrawString("Shipping", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(133.5 + 1.2), (float)158.25, (float)28.5, (float)4.25), StringStyle);
            g.DrawString("Tax", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(133.5 + 1.2), (float)162.5, (float)28.5, (float)4.25), StringStyle);
            g.DrawString("Total", new Font("Helvetica", 9, FontStyle.Bold), Brushes.Black, new RectangleF((float)(133.5 + 1.2), (float)166.75, (float)28.5, (float)4.25), StringStyle);
            StringStyle.Alignment = StringAlignment.Center;

            // Barcode (TC#) 3of9
            Barcode3of9 tc = new Barcode3of9(g);
            tc.barcodeHeight = 10;
            tc.barWidth = (float)0.27;
            tc.thickMultiplier = 2;
            tc.content = TC;
            g.DrawString("TC #: " + tc.content, new Font("Times New Roman", 10), Brushes.Black, new RectangleF(15, 178, tc.FinalWidth(), 5), StringStyle);
            tc.Draw(new PointF(15, 183));

            // Barcode ASN (UCC-128)
            if (ASN.Length > 0)
            {
                BarcodeUCC128 asn = new BarcodeUCC128(g);
                asn.barcodeHeight = 10;
                asn.barWidth = (float)0.375;
                asn.content = ASN;
                String FormattedASN = ASN;
                if (FormattedASN.Length == 20)
                {
                    FormattedASN = FormattedASN.Insert(0, "(");
                    FormattedASN = FormattedASN.Insert(3, ") ");
                    FormattedASN = FormattedASN.Insert(6, " ");
                    FormattedASN = FormattedASN.Insert(14, " ");
                    FormattedASN = FormattedASN.Insert(24, " ");
                }
                g.DrawString("Wal-Mart Associate Scan ASN Below", new Font("Times New Roman", 10, FontStyle.Bold), Brushes.Black, new RectangleF(130, 172, asn.FinalWidth() + 2, 5), StringStyle);
                g.DrawString(FormattedASN, new Font("Times New Roman", 10), Brushes.Black, new RectangleF(132, 178, asn.FinalWidth(), 5), StringStyle);
                asn.Draw(new PointF(132, 183));
            }

            // Bottom Bar
            g.DrawLine(new Pen(Brushes.Black, (float)0.5), (float)12.5, 193, (float)191.5, 193);

            // Bottom Text
            headerFont = new Font("Helvetica", 10, FontStyle.Bold);
            printFont = new Font("Arial", 10);

            g.DrawString("Thank you for shopping at Walmart-com!", headerFont, Brushes.Black, new PointF(13, 197));

            g.DrawString("Didn't receive your entire order?", headerFont, Brushes.Black, new PointF(13, 203));

            String text = "You may receive your order in seperate shipments. If your entire order did not arrive in this shipment, you can track the status of your order at www.walmart.com/ordertrack.";
            g.DrawString(text, printFont, Brushes.Black, new RectangleF(13, 207, 171, 10));

            g.DrawString("Questions about your order?", headerFont, Brushes.Black, new PointF(13, (float)217.5));
            text = "Please visit www.walmart.com/ordertrack for details or contact us at www.walmart.com/contact.";
            g.DrawString(text, printFont, Brushes.Black, new RectangleF(13, (float)221.5, 171, 5));

            g.DrawString("Want to make a return?", headerFont, Brushes.Black, new PointF(13, (float)227.5));
            text = "If you are not satisfied with your order, please refer to the Returns Information on the back of this invoice or on a seperate sheet.";
            g.DrawString(text, printFont, Brushes.Black, new RectangleF(13, (float)231.5, 171, 10));

            // Dynamic Text
            Font addressFont = new Font("Helvetica", 10);

            // Address interval 4, max of 5 lines.
            // Ordered by:
            g.DrawString(BillingName + "\n" + OrderedByAddr, addressFont, Brushes.Black, new RectangleF((float)11.5, 46, 82, 20));

            // Received by:
            g.DrawString(ShipToName + "\n" + ShippedToAddr, addressFont, Brushes.Black, new RectangleF((float)93.5, 46, 82, 20));

            // Order interval 4, max lines of 17.  5.8 initial interval
            // Qty.
            StringStyle.Alignment = StringAlignment.Far;
            int i = 0;
            foreach (string[] item in Items) {
                if (i >= 17) // Only 17 Items fit!!!
                    break;
                g.DrawString(item[0], orderFont, Brushes.Black, new RectangleF((float)(10.9), (float)(83.8 + (i * 4)), (float)9.6, 4), StringStyle);
                g.DrawString(item[1], orderFont, Brushes.Black, new RectangleF((float)(22.2 + 1.2), (float)(83.8 + (i * 4)), (float)31.8, 4));
                g.DrawString(item[2], orderFont, Brushes.Black, new RectangleF((float)(55.2), (float)(83.8 + (i * 4)), (float)79.5, 4));
                g.DrawString(item[3], orderFont, Brushes.Black, new RectangleF((float)(134.7), (float)(83.8 + (i * 4)), (float)26.3, 4), StringStyle);
                g.DrawString(item[4], orderFont, Brushes.Black, new RectangleF((float)(163.2), (float)(83.8 + (i * 4)), (float)27.1, 4), StringStyle);
                ++i;
            }

            // Intervals of 4.25
            // Subtotal
            g.DrawString(SubTotal, orderFont, Brushes.Black, new RectangleF((float)(163.2), 154, (float)27.1, (float)4.25), StringStyle);

            // Shipping
            g.DrawString(Shipping, orderFont, Brushes.Black, new RectangleF((float)(163.2), (float)158.25, (float)27.1, (float)4.25), StringStyle);

            // Tax
            g.DrawString(Tax, orderFont, Brushes.Black, new RectangleF((float)(163.2), (float)162.5, (float)27.1, (float)4.25), StringStyle);

            // Total
            g.DrawString(Total, orderFont, Brushes.Black, new RectangleF((float)(163.2), (float)166.75, (float)27.1, (float)4.25), StringStyle);


            //g.
            //e.Graphics
        }

        public void DrawPage2(Graphics g)
        {
            g.PageUnit = GraphicsUnit.Millimeter;

            Font printFont = new Font("Courier New", 3);
            Font headerFont = new Font("Helvetica", (float)9.5, FontStyle.Bold);
            Font mainFont = new Font("Helvetica", (float)8.6);
            Font boldFont = new Font("Helvetica", (float)8.6, FontStyle.Bold);

            // Page 2 Text Bar
            Brush blue = Brushes.Blue;
            g.FillRectangle(blue, (float)17.5, 9, (float)168.8, (float)4.6);
            g.DrawString("How to Return or Exchange an Item", new Font("Helvetica", 10, FontStyle.Bold), Brushes.White, new RectangleF((float)18, (float)9.3, 100, 5));

            // Page 2 Main Text
            String text = "Your complete shopping satisfaction is our number one priority. If an item you order from Walmart.com does not meet your expectations, simply return it to us.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)18.5, (float)16.6, (float)176.8, 10));

            // Return an Item to a Wal-Mart Store

            text = "Return an Item to a Wal-Mart Store";
            g.DrawString(text, headerFont, blue, new RectangleF((float)18.5, (float)25.2, (float)160, 10));
            g.DrawString("1.", mainFont, blue, new RectangleF((float)25, (float)29.2, (float)160, 10));
            text = "Take the item with all original packaging and accessories plus the packing slip to Customer Service at your local Wal-Mart.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)29.2, (float)145, 20));
            g.DrawString("2.", mainFont, blue, new RectangleF((float)25, (float)36.2, (float)160, 10));
            text = "An associate will issue a refund to the original credit card or provide a store credit for the cost of the item and the sales tax, if applicable.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)36.2, (float)160, 20));
            g.DrawString("3.", mainFont, blue, new RectangleF((float)25, (float)43.2, (float)160, 10));
            text = "You will receive immediate credit for your return.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)43.2, (float)160, 20));

            // Return an Item by Mail

            text = "Return an Item by Mail";
            g.DrawString(text, headerFont, blue, new RectangleF((float)18.5, (float)47.8, (float)160, 10));
            g.DrawString("1.", mainFont, blue, new RectangleF((float)25, (float)51.8, (float)160, 10));
            text = "Go to Walmart.com and click on \"Return an Item\" at the bottom of the page.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)51.8, (float)160, 10));
            g.DrawString("2.", mainFont, blue, new RectangleF((float)25, (float)55.3, (float)160, 20));
            text = "If you are returning an item that you purchased, you will be asked to sign in and select the item from your list of items eligible for return.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)55.3, (float)160, 20));
            g.DrawString("3.", mainFont, blue, new RectangleF((float)25, (float)62.3, (float)160, 10));
            text = "If you are returning a gift, you will need to enter the \"Order Number\" and \"Shipped To\" name from this packing slip.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)62.3, (float)160, 10));
            g.DrawString("4.", mainFont, blue, new RectangleF((float)25, (float)65.8, (float)160, 10));
            text = "You will then be given a return label that you must print out and affix to the box.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)65.8, (float)160, 10));
            g.DrawString("5.", mainFont, blue, new RectangleF((float)25, (float)69.3, (float)160, 10));
            text = "Box the item securely. Enclose the packing slip plus all original packaging and accessories.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)69.3, (float)160, 10));
            g.DrawString("6.", mainFont, blue, new RectangleF((float)25, (float)72.8, (float)160, 30));
            text = "After your item has been received by our Returns Department, credit will be issued to the original method of payment for the amount of the item plus tax, less any shipping charges and adjustments, if appropriate. OR, if you have requested an exact item replacement, we will send out the replacement when the returned item is received.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)72.8, (float)160, 30));

            // Return Details

            text = "Return Details";
            g.DrawString(text, headerFont, blue, new RectangleF((float)18.5, (float)84.4, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)89.4, (float)1.2, (float)1.2);
            text = "All items must be returned in the original packaging with the packing slip. See \"Return Exceptions\" in our Returns Policy section.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)88.4, (float)160, 20));
            g.FillEllipse(blue, (float)25, (float)96.4, (float)1.2, (float)1.2);
            text = "If you were sent an                                                                     , we can either replace the exact item for you or issue a refund for any applicable shipping and gift wrapping charges, less any adjustments, if appropriate.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)95.4, (float)160, 20));
            text = "                               incorrect, damaged or defective product";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)95.3, (float)160, 20));
            g.FillEllipse(blue, (float)25, (float)103.4, (float)1.2, (float)1.2);
            text = "                              Some heavy or large items that are identified as oversized on their item description pages                                                               Please email us at help@walmart.com and give us your name, order number and email address. We'll contact you to coordinate the return.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)102.4, (float)145, 30));
            text = "Oversized Items:";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)102.3, (float)160, 30));
            text = "          must be returned via ground freight.";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)105.8, (float)160, 30));
            g.FillEllipse(blue, (float)25, (float)113.9, (float)1.2, (float)1.2);
            text = "Items purchased in a Wal-Mart store or a SAM'S Club cannot be returned or replaced by mail.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)112.9, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)117.4, (float)1.2, (float)1.2);
            text = "Items cannot be exchanged for another item by mail but may be exchanged at any Wal-Mart store. If you return items by mail, you will have the option to request either an exact item replacement or a refund that would be credited to the original method of payment.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)116.4, (float)160, 30));
            g.FillEllipse(blue, (float)25, (float)127.9, (float)1.2, (float)1.2);
            text = "                                                                          are not returnable or refundable.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)126.9, (float)160, 10));
            text = "Wal-Mart Store Gift Cards (Shopping Cards)";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)126.8, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)131.4, (float)1.2, (float)1.2);
            text = "                                                                                                       must be returned unopened.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)130.4, (float)160, 10));
            text = "CDs, DVDs, audiotapes, video games and computer software";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)130.3, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)134.9, (float)1.2, (float)1.2);
            text = "            must be returned unused and unmarked.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)133.9, (float)160, 10));
            text = "Books";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)133.8, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)138.4, (float)1.2, (float)1.2);
            text = "                                                                 must be returned by mail with any included software within 15 days of receipt.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)137.4, (float)145, 20));
            text = "Computer hardware and components";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)137.3, (float)145, 20));
            g.FillEllipse(blue, (float)25, (float)145.4, (float)1.2, (float)1.2);
            text = "         can be returned or exchanged at your local Wal-Mart Tire & Lube.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)144.4, (float)160, 10));
            text = "Tires";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)144.3, (float)160, 10));
            g.FillEllipse(blue, (float)25, (float)148.9, (float)1.2, (float)1.2);
            text = "                           must be unopened for return.";
            g.DrawString(text, mainFont, blue, new RectangleF((float)30, (float)147.9, (float)160, 10));
            text = "Contact Lenses";
            g.DrawString(text, boldFont, blue, new RectangleF((float)30, (float)147.8, (float)160, 10));
            // Page 2 Bottom line and text

            Pen boxPen = new Pen(blue, (float)0.3);
            g.DrawLine(boxPen, (float)17.5, (float)153, (float)186.3, (float)152);
            g.DrawString("Thank you for shopping at Walmart.com. We look forward to serving you in the future!", new Font("Helvetica", 11, FontStyle.Bold), blue, new RectangleF((float)18.5, (float)153.8, 200, 10));

            Pen boxPen2 = new Pen(Brushes.Gray, (float)0.15);
            g.DrawRectangle(boxPen2, (float)1, 176, (float)199, 85);
        }

        private String PrintLabel() {
        	String lines = "";
            lines += "R0,0\r\n";
            lines += "N\r\n";
            // Line X,Y,Run X,Run Y
            // Box H Start, V Start, Line Thick, H End, V End
            // ASCII X,Y,R(0),Font,H Multi,V Multi,N for Normal
            //var ASN = "00000100150320024503";
            //String ASN = "00000100150343024603";
            String From = "WALMART.COM\n1901 Highway 102 East\nBentonville, AR\n           72712-9998";
            //String To = "WAL-MART #02105\n13307 Midway Road\nFarmers Branch, TX\n                75244";
            //String VendorInfo = "BOL# 294399\nSoflex PO# 83883488";
            //String CustomerName = "Amy Van Vranken";
            //String CustomerOrder = "2677087255693";
            //String WMPO = "73815299";
            //String ProcessDate = "6/29/05";

            lines += "X40,20,20,800,1020\r\n"; // Box around the label
            lines += "LO40,250,745,20\r\n"; // Crossbar under addresses
            lines += "LO410,40,20,220\r\n"; // Vertical Bar between To and From
            lines += "LO40,400,745,20\r\n"; // Crossbar below vendor area
            lines += "LO40,600,745,20\r\n"; // Crossbar above bar code
            lines += "B180,660,0,0,3,23,230,B,\"" + ASN + "\"\r\n";
            lines += "\r\n";
            // SHIP FROM: text
            int i = 0;
            string[] parts;
            parts = From.Split('\n');
            lines += "A70,50,0,4,1,1,N,\"SHIP FROM:\"\r\n";
            foreach (string x in parts) {
	            ++i;
	            lines += "A70," + (50+(i*30)) + ",0,4,1,1,N,\"" + x + "\"\r\n"; 
            }
            // SHIP TO: text
            i = 0;
            string[] partsTo = ShippedToAddr.Split('\n');
            lines += "A440,50,0,4,1,1,N,\"SHIP TO:\"\r\n";
            foreach (string x in partsTo) {
	            ++i;
	            lines += "A440," + (50+(i*30)) + ",0,4,1,1,N,\"" + x + "\"\r\n"; 
            }

            // Vendor Info
            i = 0;
            string[] partsV = VendorInfo.Split('\n');
            foreach (string x in partsV) {
	            lines += "A70," + (280+(i*40)) + ",0,3,1,2,N,\"" + x + "\"\r\n"; 
	            ++i;
            }

            // Customer Name
            lines += "A70,430,0,3,1,2,N,\"Customer: " + ShipToName + "\"\r\n";
            // Customer Order
            lines += "A70,470,0,3,1,2,N,\"Customer Order #: " + OrderId + "\"\r\n";
            // WM.com PO#
            lines += "A70,510,0,3,1,2,N,\"WM.com PO #: " + WalmartPO + "\"\r\n";
            // Process Date
            lines += "A70,550,0,3,1,1,N,\"Processing Date: " + ShipDate + "\"\r\n";
            // Barcode Label
            lines += "A240,570,0,1,1,2,N,\"Wal-Mart Associate Scan ASN Below\"\r\n";

            // Finish Label
            lines += "P1\r\n";
            return lines;
        }
    }
}
