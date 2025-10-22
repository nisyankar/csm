using System;
using System.Collections.Generic;
using System.Text;
using System.Windows.Forms;
using Moreum.HGO.Client.Services;

namespace Moreum.HGO.Client.Controls
{
    public class PuantajColumn : DataGridViewColumn
    {
        DateTime m_Date;

        public PuantajColumn(DateTime oneDayDate)
            : base(new PuantajCell())
        {
            m_Date = oneDayDate;
            this.Name = "day" + m_Date.Day.ToString();
            this.HeaderText = m_Date.Day.ToString();
            this.HeaderCell.Style.Alignment = DataGridViewContentAlignment.BottomCenter;
            this.HeaderCell.ToolTipText = m_Date.ToString("D");
            if (m_Date.Date == HGOService.Service.CurrentDate.Date)
            {
                DataGridViewColumnHeaderCell headerCell = this.HeaderCell;
                headerCell.ToolTipText = headerCell.ToolTipText + Environment.NewLine + "(Bugün)";
            }
        }

        public DateTime Date
        {
            get
            {
                return m_Date;
            }
        }
    }
}
