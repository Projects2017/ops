using System;
using System.Collections.Generic;
using System.Text;
using System.Windows.Forms;
using System.Drawing;
using System.Drawing.Printing;

namespace PMDPrint
{
    public class TestForm : System.Windows.Forms.Form
    {
        private PrintDocument printDoc = new PrintDocument();
        private PrintDocument printDoc2 = new PrintDocument();

        public TestForm()
        {
            MenuItem fileMenuItem = new MenuItem("&File");
            MenuItem filePageSetupMenuItem = new MenuItem("Page Set&up...", new EventHandler(filePageSetupMenuItem_Click));
            MenuItem filePrintPreviewMenuItem = new MenuItem("Print Pre&view", new EventHandler(filePrintPreviewMenuItem_Click), Shortcut.CtrlZ);
            MenuItem filePrintMenuItem = new MenuItem("&Print...",
              new EventHandler(filePrintMenuItem_Click), Shortcut.CtrlP);

            fileMenuItem.MenuItems.Add(filePageSetupMenuItem);
            fileMenuItem.MenuItems.Add(filePrintPreviewMenuItem);
            fileMenuItem.MenuItems.Add(filePrintMenuItem);

            this.Menu = new MainMenu();
            this.Menu.MenuItems.Add(fileMenuItem);
            //printDoc.PrintPage += new PrintPageEventHandler(
            // 		printDoc_PrintPage1);
            //printDoc2.PrintPage += new PrintPageEventHandler(
            //      printDoc_PrintPage2);
        }

        // -------------- event handlers ---------------------------------
        private void filePrintMenuItem_Click(Object sender,
                EventArgs e)
        {
            WalmartPackingSlip slip = new WalmartPackingSlip();
            slip.DemoData();
            slip.Print();
            return;
            //printDoc.Print();
            //printDoc2.Print();
        }

        private void filePrintPreviewMenuItem_Click(Object sender,
                EventArgs e)
        {
            PrintPreviewDialog dlg = new PrintPreviewDialog();
            dlg.Document = printDoc;
            printDoc.PrintPage += new PrintPageEventHandler(
             		printDoc_PrintPage1);
            dlg.ShowDialog();
        }

        private void filePageSetupMenuItem_Click(Object sender,
                EventArgs e)
        {
        }

        private void RulerPage(Graphics g)
        {
            Font printFont = new Font("Courier New", 3);
            Font headerFont = new Font("Ariel", 16);
            PointF lineStart, lineEnd;
            StringFormat Xaxis = new StringFormat();
            int i = 0; // For iteration
            const float margin = 0;
            const float axisIndent = 6;
            const float interval = 2;
            const float length = 3;
            const float width = (float)0.10;
            const float pageHieght = 270;
            const float pageWidth = 202;

            g.PageUnit = GraphicsUnit.Millimeter;

            g.DrawString(g.PageUnit.ToString(), headerFont, Brushes.Black, new PointF(margin + (pageWidth / 2), margin + (pageHieght / 2)));

            g.DrawString("Y", headerFont, Brushes.Black, new PointF(margin + axisIndent, margin + (pageHieght / 2)));
            for (i = 1; (i * interval) + margin <= pageHieght; ++i)
            {
                lineStart = new PointF(margin, (i * interval) + margin);
                lineEnd = new PointF(length + margin, (i * interval) + margin);
                g.DrawLine(new Pen(Color.Black, width), lineStart, lineEnd);
                g.DrawString(((i * interval) + margin).ToString(), printFont,
                    Brushes.Black, new PointF(length + margin, (i * interval) - (interval / 2) + margin));
            }

            g.DrawString("X", headerFont, Brushes.Black, new PointF((pageWidth / 2) + margin, axisIndent + margin));
            for (i = 1; (i * interval) + margin < pageWidth; ++i)
            {
                lineStart = new PointF((i * interval) + margin, margin);
                lineEnd = new PointF((i * interval) + margin, length + margin);
                g.DrawLine(new Pen(Color.Black, width), lineStart, lineEnd);

                g.DrawString(((i * interval) + margin).ToString(), printFont,
                    Brushes.Black, new PointF(((i * interval) - (interval / 2)) + margin, length + margin));
            }
        }

        private void printDoc_PrintPage1(Object sender,
            PrintPageEventArgs e)
        {
            WalmartPackingSlip slip = new WalmartPackingSlip();
            slip.DemoData();
            slip.DrawPage1(e.Graphics);
            return;
        }
        private void printDoc_PrintPage2(Object sender,
        PrintPageEventArgs e)
        {
            WalmartPackingSlip slip = new WalmartPackingSlip();
            slip.DrawPage2(e.Graphics);
            return;
        }
        //-------------- end of event handlers -------------------------------


        [STAThread]
        static void Main()
        {
            Application.Run(new TestForm());
        }
    }
}
