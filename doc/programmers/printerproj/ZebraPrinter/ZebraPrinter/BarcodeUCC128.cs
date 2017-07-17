using System;
using System.Drawing;

namespace PMDPrint
{
    class BarcodeUCC128
    {
        /* Width of as single bar in the code */
        public float barWidth = 0;

        /* Height of the barcode */
        public float barcodeHeight = 0;

        public string content = "";

        /* The graphics interface we should draw on */
        protected Graphics g;

        /* When the class is initialized, we want the graphics interface */
        public BarcodeUCC128(Graphics g)
        {
            this.g = g;
        }

        public float FinalWidth()
        {
            int finalCount = 11 * (content.Length / 2);
            finalCount += 11 * 4;
            return finalCount * barWidth;
        }

        /* Print UCC128 Barcode
         * Starting Point is where the barcode starts, a leading area of 10*barWidth will be colored white for Quiet Area.
         * Minimum barWidth is 7.5 mils (0.0075" 0.19mm)
         */
        public void Draw(PointF start)
        {
            float current = start.X;
            int i = 0;
            String pair = "";
            int checksum = 0;
            int pos = 1;
            String number = content;


            // Leading Quiet Space
            printBarcodeSegment(g, new PointF(current - (barWidth * 10), start.Y), "0000000000", barWidth, barcodeHeight);
            // START C code
            current = printBarcodeSegment(g, new PointF(current, start.Y), Code128Pattern(105), barWidth, barcodeHeight);
            checksum += 105; // Add but don't incriment pos
            // FNC 1 code
            current = printBarcodeSegment(g, new PointF(current, start.Y), Code128Pattern(102), barWidth, barcodeHeight);
            checksum += 102 * pos;
            ++pos;
            // Split String Into Pairs
            i = 0;
            while (i < number.Length)
            {
                pair = number.Substring(i, 2);
                i += 2;
                // Print Current Pair
                current = printBarcodeSegment(g, new PointF(current, start.Y), Code128Pattern(int.Parse(pair)), barWidth, barcodeHeight);
                checksum += int.Parse(pair) * pos;
                ++pos;
            }
            // Checksum ( Modulus 103 )
            checksum = checksum % 103;
            current = printBarcodeSegment(g, new PointF(current, start.Y), Code128Pattern(checksum), barWidth, barcodeHeight);
            // STOP Code
            current = printBarcodeSegment(g, new PointF(current, start.Y), Code128Pattern(106), barWidth, barcodeHeight);

            // Ending Quiet Space
            printBarcodeSegment(g, new PointF(current, start.Y), "0000000000", barWidth, barcodeHeight);
        }

        /* Convert Code to Barcode Pattern */
        private String Code128Pattern(int c)
        {
            String csCharPattern;

            switch (c)
            {
                case 0:
                    csCharPattern = "11011001100";
                    break;
                case 1:
                    csCharPattern = "11001101100";
                    break;
                case 2:
                    csCharPattern = "11001100110";
                    break;
                case 3:
                    csCharPattern = "10010011000";
                    break;
                case 4:
                    csCharPattern = "10010001100";
                    break;
                case 5:
                    csCharPattern = "10001001100";
                    break;
                case 6:
                    csCharPattern = "10011001000";
                    break;
                case 7:
                    csCharPattern = "10011000100";
                    break;
                case 8:
                    csCharPattern = "10001100100";
                    break;
                case 9:
                    csCharPattern = "11001001000";
                    break;
                case 10:
                    csCharPattern = "11001000100";
                    break;
                case 11:
                    csCharPattern = "11000100100";
                    break;
                case 12:
                    csCharPattern = "10110011100";
                    break;
                case 13:
                    csCharPattern = "10011011100";
                    break;
                case 14:
                    csCharPattern = "10011001110";
                    break;
                case 15:
                    csCharPattern = "10111001100";
                    break;
                case 16:
                    csCharPattern = "10011101100";
                    break;
                case 17:
                    csCharPattern = "10011100110";
                    break;
                case 18:
                    csCharPattern = "11001110010";
                    break;
                case 19:
                    csCharPattern = "11001011100";
                    break;
                case 20:
                    csCharPattern = "11001001110";
                    break;
                case 21:
                    csCharPattern = "11011100100";
                    break;
                case 22:
                    csCharPattern = "11001110100";
                    break;
                case 23:
                    csCharPattern = "11101101110";
                    break;
                case 24:
                    csCharPattern = "11101001100";
                    break;
                case 25:
                    csCharPattern = "11100101100";
                    break;
                case 26:
                    csCharPattern = "11100100110";
                    break;
                case 27:
                    csCharPattern = "11101100100";
                    break;
                case 28:
                    csCharPattern = "11100110100";
                    break;
                case 29:
                    csCharPattern = "11100110010";
                    break;
                case 30:
                    csCharPattern = "11011011000";
                    break;
                case 31:
                    csCharPattern = "11011000110";
                    break;
                case 32:
                    csCharPattern = "11000110110";
                    break;
                case 33:
                    csCharPattern = "10100011000";
                    break;
                case 34:
                    csCharPattern = "10001011000";
                    break;
                case 35:
                    csCharPattern = "10001000110";
                    break;
                case 36:
                    csCharPattern = "10110001000";
                    break;
                case 37:
                    csCharPattern = "10001101000";
                    break;
                case 38:
                    csCharPattern = "10001100010";
                    break;
                case 39:
                    csCharPattern = "11010001000";
                    break;
                case 40:
                    csCharPattern = "11000101000";
                    break;
                case 41:
                    csCharPattern = "11000100010";
                    break;
                case 42:
                    csCharPattern = "10110111000";
                    break;
                case 43:
                    csCharPattern = "10110001110";
                    break;
                case 44:
                    csCharPattern = "10001101110";
                    break;
                case 45:
                    csCharPattern = "10111011000";
                    break;
                case 46:
                    csCharPattern = "10111000110";
                    break;
                case 47:
                    csCharPattern = "10001110110";
                    break;
                case 48:
                    csCharPattern = "11101110110";
                    break;
                case 49:
                    csCharPattern = "11010001110";
                    break;
                case 50:
                    csCharPattern = "11000101110";
                    break;
                case 51:
                    csCharPattern = "11011101000";
                    break;
                case 52:
                    csCharPattern = "11011100010";
                    break;
                case 53:
                    csCharPattern = "11011101110";
                    break;
                case 54:
                    csCharPattern = "11101011000";
                    break;
                case 55:
                    csCharPattern = "11101000110";
                    break;
                case 56:
                    csCharPattern = "11100010110";
                    break;
                case 57:
                    csCharPattern = "11101101000";
                    break;
                case 58:
                    csCharPattern = "11101100010";
                    break;
                case 59:
                    csCharPattern = "11100011010";
                    break;
                case 60:
                    csCharPattern = "11101111010";
                    break;
                case 61:
                    csCharPattern = "11001000010";
                    break;
                case 62:
                    csCharPattern = "11110001010";
                    break;
                case 63:
                    csCharPattern = "10100110000";
                    break;
                case 64:
                    csCharPattern = "10100001100";
                    break;
                case 65:
                    csCharPattern = "10010110000";
                    break;
                case 66:
                    csCharPattern = "10010000110";
                    break;
                case 67:
                    csCharPattern = "10000101100";
                    break;
                case 68:
                    csCharPattern = "10000100110";
                    break;
                case 69:
                    csCharPattern = "10110010000";
                    break;
                case 70:
                    csCharPattern = "10110000100";
                    break;
                case 71:
                    csCharPattern = "10011010000";
                    break;
                case 72:
                    csCharPattern = "10011000010";
                    break;
                case 73:
                    csCharPattern = "10000110100";
                    break;
                case 74:
                    csCharPattern = "10000110010";
                    break;
                case 75:
                    csCharPattern = "11000010010";
                    break;
                case 76:
                    csCharPattern = "11001010000";
                    break;
                case 77:
                    csCharPattern = "11110111010";
                    break;
                case 78:
                    csCharPattern = "11000010100";
                    break;
                case 79:
                    csCharPattern = "10001111010";
                    break;
                case 80:
                    csCharPattern = "10100111100";
                    break;
                case 81:
                    csCharPattern = "10010111100";
                    break;
                case 82:
                    csCharPattern = "10010011110";
                    break;
                case 83:
                    csCharPattern = "10111100100";
                    break;
                case 84:
                    csCharPattern = "10011110100";
                    break;
                case 85:
                    csCharPattern = "10011110010";
                    break;
                case 86:
                    csCharPattern = "11110100100";
                    break;
                case 87:
                    csCharPattern = "11110010100";
                    break;
                case 88:
                    csCharPattern = "11110010010";
                    break;
                case 89:
                    csCharPattern = "11011011110";
                    break;
                case 90:
                    csCharPattern = "11011110110";
                    break;
                case 91:
                    csCharPattern = "11110110110";
                    break;
                case 92:
                    csCharPattern = "10101111000";
                    break;
                case 93:
                    csCharPattern = "10100011110";
                    break;
                case 94:
                    csCharPattern = "10001011110";
                    break;
                case 95:
                    csCharPattern = "10111101000";
                    break;
                case 96:
                    csCharPattern = "10111100010";
                    break;
                case 97:
                    csCharPattern = "11110101000";
                    break;
                case 98:
                    csCharPattern = "11110100010";
                    break;
                case 99:
                    csCharPattern = "10111011110";
                    break;
                case 100:
                    csCharPattern = "10111101110";
                    break;
                case 101:
                    csCharPattern = "11101011110";
                    break;
                case 102:
                    csCharPattern = "11110101110";
                    break;
                case 103:
                    csCharPattern = "11010111100";
                    break;
                case 104:
                    csCharPattern = "11010010000";
                    break;
                case 105:
                    csCharPattern = "11010011100";
                    break;
                case 106: // STOP
                    csCharPattern = "1100011101011";
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
            foreach (char i in code)
            {
                if (i == '1')
                    g.DrawLine(new Pen(Brushes.Black, barWidth + 1), new PointF((float)current + (barWidth / 2), start.Y), new PointF((float)current + (barWidth / 2), start.Y + codeHieght));
                else
                    g.DrawLine(new Pen(Brushes.White, barWidth + 1), new PointF((float)current + (barWidth / 2), start.Y), new PointF((float)current + (barWidth / 2), start.Y + codeHieght));
                current += barWidth;
            }
            // Return where we ended up
            return current;
        }
    }
}
