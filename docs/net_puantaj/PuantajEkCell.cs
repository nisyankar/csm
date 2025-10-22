using System;
using System.ComponentModel;
using System.Drawing;
using System.Windows.Forms;
using Moreum.HGO.Entities;


namespace Moreum.HGO.Client.Controls
{
    public class PuantajEkCell : DataGridViewTextBoxCell
    {
        protected override object GetFormattedValue(object value, int rowIndex, ref DataGridViewCellStyle cellStyle, TypeConverter valueTypeConverter, TypeConverter formattedValueTypeConverter, DataGridViewDataErrorContexts context)
        {
            String retVal = String.Empty;

            if (this.PuantajEk != null)
            {
                retVal = this.PuantajEk.DevirSuresi.ToString();
            }


            base.ToolTipText = retVal;
            return retVal;
        }

        protected override Size GetPreferredSize(Graphics graphics, DataGridViewCellStyle cellStyle, int rowIndex, Size constraintSize)
        {
            return new Size(40, 30);
        }

        public override void InitializeEditingControl(int rowIndex, object initialFormattedValue, DataGridViewCellStyle dataGridViewCellStyle)
        {
            if (this.PuantajEk != null)
                initialFormattedValue = this.PuantajEk.DevirSuresi.ToString();

            base.InitializeEditingControl(rowIndex, initialFormattedValue, dataGridViewCellStyle);
        }

        public override object ParseFormattedValue(object formattedValue, DataGridViewCellStyle cellStyle, TypeConverter formattedValueTypeConverter, TypeConverter valueTypeConverter)
        {
            Double tmpDuration;

            if (Double.TryParse(formattedValue.ToString(), out tmpDuration) && tmpDuration >= 0 && tmpDuration < 3000)
            {
                this.PuantajEk.DevirSuresi = tmpDuration;
            }

            return this.PuantajEk;
        }

        public override Type ValueType
        {
            get
            {
                return typeof(PuantajEk);
            }
        }

        private PuantajEk PuantajEk
        {
            get
            {
                return base.Value as PuantajEk;
            }
        }
    }
}

