using System;
using System.Drawing.Printing;
using System.Text.RegularExpressions;
using System.Windows.Forms;
using Microsoft.Win32;

namespace PMDPrint
{
    public class LabelPrinter
    {
        // Label Properties
        private const string lineFeed = "\n";
        private const int vertMargin = 19; // px
        private const int sideMargin = 100; // px
        private const int labelLength = 812; // px (4" * 203dpi)
        private const int labelWidth = 1280; // px (6" * 203dpi) used to be 1280
        private const Boolean landscape = true; // Affects how the variables above are used.

        private struct LabelFont
        {
            public int Number;
            public int Multiplier;

            public LabelFont(int Number, int Multiplier)
            {
                this.Number = Number;
                this.Multiplier = Multiplier;
            }

            public int Width()
            {
                return FontWidth(Number) * Multiplier;
            }

            public int Height()
            {
                return FontHeight(Number) * Multiplier;
            }
        }

        public static string PrintLabel(string input)
        {
            // functionality stolen from VBScript version
            // Variable Declarations
            String output;

            output = lineFeed;
            output = "R0,0" + lineFeed; // Reset the Reference Point
            output += "N" + lineFeed;

            // Debuging Boxes
            output += Box(2, 2, labelLength - (vertMargin * 2) - 2, labelWidth - (sideMargin * 2) - 2, 2);
            output += Box(0, 0, 20, 10, 2);

            // Print Text
            output += CenterText(input);

            // set to print one
            output += "P1" + lineFeed;
            return output;
        }

        private static string CenterText(string Text)
        {
            String output = "";
            int maxLen = 0;
            int middleOfLabel = 0;
            int centerOfLabel = 0;
            int curpos = 0;
            int lineOffset = 0;
            int centerOffset = 0;
            LabelFont font;
            string[] lines = Text.Split(new Char[] { '\n' });

            // Find the Maximum Length of a line
            for (int i = 0; i < lines.Length; i++)
            {
                maxLen = Math.Max(maxLen, lines[i].Length);
            }
            font = SelectFont(maxLen, lines.Length);

            //labelLength - (vertMargin * 2), labelWidth - (sideMargin * 2) /2
            middleOfLabel = (labelLength - (vertMargin * 2)) / 2;
            centerOfLabel = (labelWidth - (sideMargin * 2)) / 2;
            lineOffset = font.Height() * lines.Length / 2;
            curpos = middleOfLabel + lineOffset;
            for (int i = 0; i < lines.Length; i++)
            {
                centerOffset = (lines[i].Length * font.Width()) / 2;
                //MessageBox.Show("Center Offset is: " + centerOffset
                //    + "\n" + "CenterOfLabel: " + centerOfLabel);
                centerOffset = centerOfLabel - centerOffset;

                output += PrintText(lines[i], font, centerOffset, curpos);
                //output += "A" + curpos + "," + centerOffset + "," + "1" + ","
                //    + font.Number + ","
                //    + font.Multiplier + "," + font.Multiplier + ","
                //    + "N" + ",\"" + lines[i] + "\"" + lineFeed;
                curpos -= font.Height(); // Move down to the next line.
            }
            return output;
        }

        private static string PrintText(string text, LabelFont font, int x, int y)
        {
            int i;
            if (landscape) // If we're dealing with landscape, we need to change our coords.
            {
                // Switch x and y
                i = y;
                y = x;
                x = i;
            }

            x += sideMargin;
            y += vertMargin;

            return "A" + x + "," + y + "," + (landscape ? "1" : "0") + ","
                    + font.Number + ","
                    + font.Multiplier + "," + font.Multiplier + ","
                    + "N" + ",\"" + EscapeString(text) + "\"" + lineFeed;
        }

        private static string EscapeString(string text)
        {
            return Regex.Replace(text, "([^0-9A-Za-z ])", "\\$1");
        }


        private static string Box(int x, int y, int length, int width, int lineWidth)
        {
            String output = "";
            int i;
            if (landscape) // If we're dealing with landscape, we need to change our coords.
            {
                // Switch x and y
                i = y;
                y = x;
                x = i;
                // Switch lenth and width
                i = length;
                length = width;
                width = i;
            }
            x = x + sideMargin;
            y = y + vertMargin;
            output += "X" + x + "," + y + "," + lineWidth;
            x = x + width;
            y = y + length;
            output += "," + x + "," + y + lineFeed;
            return output;
        }

        private static LabelFont SelectFont(int maxLength, int numLines)
        {
            int wFontNum, hFontNum, fontNum, mult, widthMax, heightMax, i;
            int[] mods;

            // Figure out the maximum width of a char in pixels we can handle
            widthMax = labelWidth - (2 * sideMargin);
            widthMax = widthMax / maxLength;
            heightMax = labelLength - (2 * vertMargin);
            heightMax = heightMax / numLines;

            // Calculate Maximum Font Size Based on Width
            mods = new int[5];
            wFontNum = 1;
            mods[4] = widthMax % FontWidth(5); // % = modulus
            mods[3] = widthMax % FontWidth(4);
            mods[2] = widthMax % FontWidth(3);
            mods[1] = widthMax % FontWidth(2);
            mods[0] = widthMax % FontWidth(1);
            for (i = 4; i > 0; i--)
            {
                if (mods[i] < mods[i - 1]) wFontNum = i;
            }

            // Calculate Maximum Font Size Based on Height
            mods = new int[5];
            hFontNum = 1;
            mods[4] = heightMax % FontHeight(5); // % = modulus
            mods[3] = heightMax % FontHeight(4);
            mods[2] = heightMax % FontHeight(3);
            mods[1] = heightMax % FontHeight(2);
            mods[0] = heightMax % FontHeight(1);
            for (i = 4; i > 0; i--)
            {
                if (mods[i] < mods[i - 1]) hFontNum = i;
            }

            if (hFontNum < wFontNum)
            {
                fontNum = hFontNum;
                decimal multDouble = heightMax / FontHeight(hFontNum);
                mult = (int)Math.Floor(multDouble);
            }
            else if (hFontNum > wFontNum)
            {
                fontNum = wFontNum;
                decimal multDouble = widthMax / FontWidth(wFontNum);
                mult = (int)Math.Floor(multDouble);
            }
            else // hFontNum == wFontNum
            {
                decimal heightDouble, widthDouble;
                heightDouble = heightMax / FontHeight(hFontNum);
                widthDouble = widthMax / FontWidth(wFontNum);
                mult = Math.Min((int)Math.Floor(heightDouble), (int)Math.Floor(widthDouble));
            }
            mult = Math.Min(mult, 6);
            //mult = (int)widthMax / (8 + 4 * wFontNum);
            return new LabelFont(wFontNum, mult);
        }

        private static int FontHeight(int fontNum)
        {
            switch (fontNum)
            {
                case 1: return 12 + 2; // + 2 (for edges)
                case 2: return 16 + 2;
                case 3: return 20 + 2;
                case 4: return 24 + 2;
                case 5: return 48 + 2;
                default: return 0;
            }
        }

        private static int FontWidth(int fontNum)
        {
            switch (fontNum)
            {
                case 1: return 8 + 2; // + 2 (for edges)
                case 2: return 10 + 2;
                case 3: return 12 + 2;
                case 4: return 14 + 2;
                case 5: return 32 + 2;
                default: return 0;
            }
        }

        public static string SetPrinter()
        {
            String pName;
            pName = AskForPrinter();
            RegistryKey localMachine = Registry.CurrentUser;
            localMachine = localMachine.OpenSubKey("SOFTWARE");
            localMachine = localMachine.OpenSubKey("PMD");
            localMachine = localMachine.OpenSubKey("PMDPrint", true);
            localMachine.SetValue("LabelPrinter", pName);
            localMachine.Flush();
            localMachine.Close();
            return pName;
        }

        public static string FindPrinter()
        {
            String pName;
            RegistryKey localMachine = Registry.CurrentUser;
            PrintDocument pd = new PrintDocument();
            Boolean valid = false;
            localMachine = localMachine.OpenSubKey("SOFTWARE");
            localMachine = localMachine.OpenSubKey("PMD");
            localMachine = localMachine.OpenSubKey("PMDPrint");
            pName = localMachine.GetValue("LabelPrinter").ToString();
            localMachine.Close();
            if (pName.Length == 0)
            {
                valid = true;
            }
            // Verify Printer's Existance
            do
            {
                if (valid == true)
                    pName = SetPrinter();
                pd.PrinterSettings.PrinterName = pName;
                if (pd.PrinterSettings.IsValid)
                    valid = true;
                else
                    valid = false;
            } while (valid == false);
            return pName;
        }

        public static string AskForPrinter()
        {
            // Allow the user to select a printer.
            MessageBox.Show("Please select your label printer.");
            PrintDialog pd = new PrintDialog();
            pd.AllowCurrentPage = false;
            pd.AllowPrintToFile = false;
            pd.AllowSelection = false;
            pd.AllowSomePages = false;
            pd.PrinterSettings = new PrinterSettings();
            if (DialogResult.OK == pd.ShowDialog())
            {
                RawPrinterHelper.SendStringToPrinter(
                      pd.PrinterSettings.PrinterName,
                      "qa" + lineFeed); // Tell printer to AutoSense Labels
                return pd.PrinterSettings.PrinterName;
            }
            else
            {
                return null;
            }
        }
    }
}
