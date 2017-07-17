using System;
using System.Drawing;

/* Update log:
 * 4/18: fixed 3 of 9, twice - Jason
 */

namespace PMDPrint
{
    class Barcode3of9
    {
        /* Multiplier of how big thick bars are in compared to thin bars */
        public int thickMultiplier = 2;

        /* Width of as single bar in the code */
        public float barWidth = 0;

        /* Height of the barcode */
        public float barcodeHeight = 0;

        /* Full Width of Barcode
         * Ignored if barWidth is set and is only the minimum.
         */
        public float barcodeWidth = 0;

        public string content = "";

        protected Brush lastBrush;

        /* The graphics interface we should draw on */
        protected Graphics g;

        /* When the class is initialized, we want the graphics interface */
        public Barcode3of9(Graphics g)
        {
            this.g = g;
        }

        public float FinalWidth()
        {
            // get final character width

            int nTemp = content.Length + 2;

            // add message

            return nTemp * ((3 * barWidth * thickMultiplier) + (7 * barWidth));
        }

        /* Print UCC128 Barcode
         * Starting Point is where the barcode starts, a leading area of 10*barWidth will be colored white for Quiet Area.
         * Minimum barWidth is 7.5 mils (0.0075" 0.19mm)
         */
        public void Draw(PointF start)
        {
            /*
            System.Net.WebClient Client = new System.Net.WebClient ();
            Image code = new Bitmap(Client.OpenRead("http://jason.pmddealer.com/samples/code39.php?code=" + content));
            g.DrawImage(code, start);
            code.Dispose();
            return;
             */

            float current = start.X;
            lastBrush = Brushes.White;
            String number = content;
            // START code
            current = printBarcodeSegment(g, new PointF(current, start.Y), "0100101000", barWidth, barcodeHeight);
            // Split String Into Pairs
            foreach (char x in number)
            {
                // Print Current Pair
                current = printBarcodeSegment(g, new PointF(current, start.Y), Pattern3of9(x), barWidth, barcodeHeight);
            }
            // END code
            current = printBarcodeSegment(g, new PointF(current, start.Y), "0100101000", barWidth, barcodeHeight);
        }

        /* Convert Code to Barcode Pattern */
        private String Pattern3of9(char c)
        {
            String csCharPattern;

            switch (c)
            {
                    /* Fix applied 4/18 by Jason
                     * WHY where there 10 digits, it's supposed to be 9 digits
                     */
                case '1':
                    csCharPattern = "1001000010";
                    break;
                case '2':
                    csCharPattern = "0011000010";
                    break;
                case '3':
                    csCharPattern = "1011000000";
                    break;
                case '4':
                    csCharPattern = "0001100010";
                    break;
                case '5':
                    csCharPattern = "1001100000";
                    break;
                case '6':
                    csCharPattern = "0011100000";
                    break;
                case '7':
                    csCharPattern = "0001001010";
                    break;
                case '8':
                    csCharPattern = "1001001000";
                    break;
                case '9':
                    csCharPattern = "0011001000";
                    break;
                case '0':
                    csCharPattern = "0001101000";
                    break;
                case 'A':
                    csCharPattern = "1000010010";
                    break;
                case 'B':
                    csCharPattern = "0010010010";
                    break;
                case 'C':
                    csCharPattern = "1010010000";
                    break;
                case 'D':
                    csCharPattern = "0000110010";
                    break;
                case 'E':
                    csCharPattern = "1000110000";
                    break;
                case 'F':
                    csCharPattern = "0010110000";
                    break;
                case 'G':
                    csCharPattern = "0000011010";
                    break;
                case 'H':
                    csCharPattern = "1000011000";
                    break;
                case 'I':
                    csCharPattern = "0010011000";
                    break;
                case 'J':
                    csCharPattern = "0000111000";
                    break;
                case 'K':
                    csCharPattern = "1000000110";
                    break;
                case 'L':
                    csCharPattern = "0010000110";
                    break;
                case 'M':
                    csCharPattern = "1010000100";
                    break;
                case 'N':
                    csCharPattern = "0000100110";
                    break;
                case 'O':
                    csCharPattern = "1000100100";
                    break;
                case 'P':
                    csCharPattern = "0010100100";
                    break;
                case 'Q':
                    csCharPattern = "0000001110";
                    break;
                case 'R':
                    csCharPattern = "1000001100";
                    break;
                case 'S':
                    csCharPattern = "0010001100";
                    break;
                case 'T':
                    csCharPattern = "0000101100";
                    break;
                case 'U':
                    csCharPattern = "1100000010";
                    break;
                case 'V':
                    csCharPattern = "0110000010";
                    break;
                case 'W':
                    csCharPattern = "1110000000";
                    break;
                case 'X':
                    csCharPattern = "0100100010";
                    break;
                case 'Y':
                    csCharPattern = "1100100000";
                    break;
                case 'Z':
                    csCharPattern = "0110100000";
                    break;
                case '-':
                    csCharPattern = "0100001010";
                    break;
                case '.':
                    csCharPattern = "1100001000";
                    break;
                case ' ':
                    csCharPattern = "0110001000";
                    break;
                case '*':
                    csCharPattern = "0100101000";
                    break;
                case '$':
                    csCharPattern = "0101010000";
                    break;
                case '/':
                    csCharPattern = "0101000100";
                    break;
                case '+':
                    csCharPattern = "0100010100";
                    break;
                case '%':
                    csCharPattern = "0001010100";
                    break;
                default:
                    csCharPattern = "";
                    break;
            }

            return csCharPattern;
        }

        private float printBarcodeSegment(Graphics g, PointF start, String code, float barWidth, float codeHieght)
        {
            float current = start.X;
            Brush curb;
            float width = 0;
            foreach (char i in code)
            {
                // Flip flop between the colors
                if (lastBrush == Brushes.Black)
                    curb = Brushes.White;
                else
                    curb = Brushes.Black;

                if (i == '1')
                    width = barWidth * thickMultiplier;
                else
                    width = barWidth;

                g.DrawLine(new Pen(curb, width), new PointF(current + (width / 2), start.Y), new PointF(current + (width / 2), start.Y + codeHieght));
                current += width;
                lastBrush = curb;
            }
            // Return where we ended up
            return current;
        }
    }
}
