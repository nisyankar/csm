using Moreum.HGO.Entities;
using System;
using System.ComponentModel;
using System.Drawing;
using System.Globalization;
using System.Text;
using System.Windows.Forms;
using Moreum.HGO.Client.Services;

namespace Moreum.HGO.Client.Controls
{
    public class PuantajCell : DataGridViewTextBoxCell
    {
        private const Double maxDailyHours = 23;

        protected override object GetFormattedValue(object value, Int32 rowIndex, ref DataGridViewCellStyle cellStyle, TypeConverter valueTypeConverter, TypeConverter formattedValueTypeConverter, DataGridViewDataErrorContexts context)
        {
            StringBuilder builder = new StringBuilder();

            Moreum.HGO.Entities.Puantaj puantaj = value as Moreum.HGO.Entities.Puantaj;
            if (puantaj != null)
            {
                if (puantaj.AltProjeID != puantaj.AsilGorevYeri) // which means temporary re-assignment.
                {
                    String gorevYeri = PuantajHelperCache.GetGorevYeri(puantaj.AltProjeID);

                    if (gorevYeri != null)
                    {
                        builder.AppendLine("Geçici görev :" + gorevYeri);
                    }
                }

                Vardiya vardiya = PuantajHelperCache.GetVardiya(puantaj.VardiyaID);

                if (vardiya != null)
                {
                    builder.AppendLine(vardiya.VardiyaAdi);

                    if (puantaj.CalismaSuresi > 0)
                    {
                        builder.AppendLine(puantaj.CalismaSuresi + " saat");
                    }

                    if (!string.IsNullOrEmpty(puantaj.Aciklama))
                    {
                        builder.AppendLine(puantaj.Aciklama);
                    }
                }
            }

            base.ToolTipText = builder.ToString().Trim();

            return builder.ToString();
        }

        protected override Size GetPreferredSize(Graphics graphics, DataGridViewCellStyle cellStyle, Int32 rowIndex, Size constraintSize)
        {
            return new Size(30, 30);
        }

        public override void InitializeEditingControl(Int32 rowIndex, object initialFormattedValue, DataGridViewCellStyle dataGridViewCellStyle)
        {
            initialFormattedValue = this.Puantaj.CalismaSuresi.ToString();
            base.InitializeEditingControl(rowIndex, initialFormattedValue, dataGridViewCellStyle);
        }

        private static bool IsNumeric(char character)
        {
            return ((character >= '0') && (character <= '9'));
        }

        public override bool KeyEntersEditMode(KeyEventArgs e)
        {
            if (e.KeyCode == Keys.Space)
            {
                return false;
            }
            return base.KeyEntersEditMode(e);
        }

        protected override void OnMouseEnter(Int32 rowIndex)
        {
            base.OnMouseEnter(rowIndex);
            PuantajGrid parentGrid = base.DataGridView as PuantajGrid;
            if (((parentGrid != null) && parentGrid.AutoAssignMode) && (this.Puantaj != null))
            {
                parentGrid.Cursor = Cursors.Hand;
            }
            else
            {
                parentGrid.Cursor = Cursors.Default;
            }
        }

        protected override void OnMouseLeave(Int32 rowIndex)
        {
            base.OnMouseLeave(rowIndex);

            if (base.DataGridView != null)
            {
                base.DataGridView.Cursor = Cursors.Default;
            }
        }

        protected override void Paint(Graphics graphics, Rectangle clipBounds, Rectangle cellBounds, Int32 rowIndex, DataGridViewElementStates cellState, object value, object formattedValue, string errorText, DataGridViewCellStyle cellStyle, DataGridViewAdvancedBorderStyle advancedBorderStyle, DataGridViewPaintParts paintParts)
        {
            Color backColor;
            Vardiya vardiya = null;

            if (this.Puantaj == null)
            {
                backColor = SystemColors.GrayText;
            }
            else
            {
                vardiya = PuantajHelperCache.GetVardiya(this.Puantaj.VardiyaID);

                if (this.Selected)
                {
                    backColor = SystemColors.Highlight;
                }
                else if (vardiya == null)
                {
                    backColor = SystemColors.Window;
                }
                else
                {
                    backColor = Color.FromArgb(vardiya.GorunumRengi);
                }
            }

            using (Brush backgroundBrush = new SolidBrush(backColor))
            {
                graphics.FillRectangle(backgroundBrush, cellBounds);
            }

            if (this.Puantaj != null)
            {
                if (this.Puantaj.Tarih.DayOfWeek == DayOfWeek.Sunday)
                {
                    using (SolidBrush weekendBrush = new SolidBrush(Color.FromArgb(0x23, Color.Blue)))
                    {
                        graphics.FillRectangle(weekendBrush, cellBounds);
                    }
                }

                if (this.Puantaj.Tarih.Date == PuantajHelperCache.CachedServerDate.Date)
                {
                    using (SolidBrush todayBrush = new SolidBrush(Color.FromArgb(0x23, Color.Red)))
                    {
                        graphics.FillRectangle(todayBrush, cellBounds);
                    }
                }
            }

            if (vardiya != null)
            {
                this.WriteCentered(graphics, vardiya.VardiyaKodu, cellBounds, clipBounds, 0);
                if (!(vardiya.UcretliIzin && (this.Puantaj.CalismaSuresi == 0)))
                {
                    this.WriteCentered(graphics, this.Puantaj.CalismaSuresi.ToString(), cellBounds, clipBounds, 1);
                }
            }

            if (!((this.Puantaj == null) || string.IsNullOrEmpty(this.Puantaj.Aciklama)))
            {
                Int32 left = cellBounds.Left;
                Int32 top = cellBounds.Top;
                Point[] noteIndicatorTriangle = new Point[] { new Point(left, top), new Point(left + 5, top), new Point(left, top + 5) };
                graphics.FillPolygon(Brushes.Black, noteIndicatorTriangle);
            }

            if (this.Puantaj != null && this.Puantaj.AltProjeID != this.Puantaj.AsilGorevYeri)
            {
                using (SolidBrush geciciGorevBrush = new SolidBrush(Color.FromArgb(0x23, Color.Green)))
                {
                    Rectangle halfBounds = new Rectangle(cellBounds.X, cellBounds.Y + (cellBounds.Height / 2), cellBounds.Width, cellBounds.Height / 2);
                    graphics.FillRectangle(geciciGorevBrush, halfBounds);
                }
            }

            base.Paint(graphics, clipBounds, cellBounds, rowIndex, cellState, value, formattedValue, errorText, cellStyle, advancedBorderStyle, DataGridViewPaintParts.Focus | DataGridViewPaintParts.Border);
        }

        public override object ParseFormattedValue(object formattedValue, DataGridViewCellStyle cellStyle, TypeConverter formattedValueTypeConverter, TypeConverter valueTypeConverter)
        {
            Double tmpDuration;

            if (Double.TryParse(formattedValue.ToString(), out tmpDuration) && (tmpDuration <= maxDailyHours))
            {
                this.Puantaj.CalismaSuresi = tmpDuration;
            }

            return this.Puantaj;
        }

        public void Refresh()
        {
            //this.ReadOnly = (this.Puantaj == null) || (this.Puantaj.VardiyaID == 0);
            base.RaiseCellValueChanged(new DataGridViewCellEventArgs(base.ColumnIndex, base.RowIndex));
        }

        //protected override bool SetValue(Int32 rowIndex, object value)
        //{
        //    bool retVal = base.SetValue(rowIndex, value);
        //    this.Refresh();
        //    return retVal;
        //}

        private void WriteCentered(Graphics graphics, string text, Rectangle cellBounds, Rectangle clipBounds, Int32 line)
        {
            Font parentFont = base.DataGridView.Font;
            SizeF textSize = graphics.MeasureString(text, parentFont);
            Single textPos = (((cellBounds.Width - textSize.Width) / 2f) + cellBounds.Left) - 1f;
            graphics.DrawString(text, parentFont, SystemBrushes.WindowText, textPos, cellBounds.Top + (textSize.Height * line));
        }

        private static char DecimalSeparator
        {
            get
            {
                return CultureInfo.CurrentCulture.NumberFormat.CurrencyDecimalSeparator[0];
            }
        }

        public override Type FormattedValueType
        {
            get
            {
                return typeof(string);
            }
        }

        private Moreum.HGO.Entities.Puantaj Puantaj
        {
            get
            {
                return (base.Value as Moreum.HGO.Entities.Puantaj);
            }
        }

        public override Type ValueType
        {
            get
            {
                return typeof(Vardiya);
            }
        }
    }
}

