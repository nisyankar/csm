using Moreum.HGO;
using Moreum.HGO.Client.Services;
using Moreum.HGO.Entities;
using Moreum.HGO.ObjectRepository;
using System;
using System.Collections.Generic;
using System.Windows.Forms;

namespace Moreum.HGO.Client.Controls
{
    public class PuantajGrid : DataGridView
    {
        private Boolean m_AutoAssignMode;

        public PuantajGrid()
        {
            base.AllowUserToAddRows = false;
            base.AllowUserToDeleteRows = false;
            base.AllowUserToOrderColumns = false;
            base.AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.AllCells;
            base.AutoSizeRowsMode = DataGridViewAutoSizeRowsMode.AllCells;
            base.SelectionMode = DataGridViewSelectionMode.ColumnHeaderSelect;
        }

        protected override void OnCellBeginEdit(DataGridViewCellCancelEventArgs e)
        {
            DataGridViewCell cell = this[e.ColumnIndex, e.RowIndex];

            if (cell is PuantajCell)
            {
                Puantaj testPuantaj = cell.Value as Puantaj;
                e.Cancel = (testPuantaj == null || testPuantaj.VardiyaID == 0);
            }

            base.OnCellBeginEdit(e);
        }

        protected override void OnCellMouseMove(DataGridViewCellMouseEventArgs e)
        {
            base.OnCellMouseMove(e);

            DataGridViewSelectionMode newMode = base.SelectionMode;

            if ((e.ColumnIndex == -1) && (e.RowIndex != -1))
            {
                newMode = DataGridViewSelectionMode.RowHeaderSelect;
            }

            if ((e.RowIndex == -1) && (e.ColumnIndex != -1))
            {
                newMode = DataGridViewSelectionMode.ColumnHeaderSelect;
            }

            if (newMode != base.SelectionMode)
            {
                DataGridViewSelectedCellCollection savedSelection = base.SelectedCells;

                try
                {
                    base.SelectionMode = newMode;
                }
                catch (InvalidOperationException)
                {
                    return;
                }
                catch (System.ComponentModel.InvalidEnumArgumentException)
                {
                    return;
                }

                foreach (DataGridViewCell savedSelectionCell in savedSelection)
                {
                    savedSelectionCell.Selected = true;
                }
            }
        }

        protected override void OnEditingControlShowing(DataGridViewEditingControlShowingEventArgs e)
        {
            base.OnEditingControlShowing(e);
        }

        public Boolean AutoAssignMode
        {
            get
            {
                return this.m_AutoAssignMode;
            }
            set
            {
                this.m_AutoAssignMode = value;
            }
        }

        public PuantajTotalCell[] SelectedTotalCells
        {
            get
            {
                List<PuantajTotalCell> selectedTotalCells = new List<PuantajTotalCell>();

                foreach (DataGridViewCell selectedCell in base.SelectedCells)
                {
                    PuantajTotalCell totalCell = selectedCell as PuantajTotalCell;
                    if (totalCell != null)
                    {
                        selectedTotalCells.Add((PuantajTotalCell)selectedCell);
                    }
                }
                return selectedTotalCells.ToArray();
            }
        }

        public PuantajCell[] SelectedPuantajCells
        {
            get
            {
                List<PuantajCell> selectedPuantajCells = new List<PuantajCell>();

                foreach (DataGridViewCell selectedCell in base.SelectedCells)
                {
                    PuantajCell puantajCell = selectedCell as PuantajCell;
                    if (puantajCell != null)
                    {
                        Puantaj puantaj = selectedCell.Value as Puantaj;
                        if ((puantaj != null) && (puantaj.VardiyaID != 0))
                        {
                            selectedPuantajCells.Add((PuantajCell)selectedCell);
                        }
                    }
                }
                return selectedPuantajCells.ToArray();
            }
        }

        public PuantajTotalCell[] SelectedPuantajTotalCells
        {
            get
            {
                List<PuantajTotalCell> selectedPuantajTotalCells = new List<PuantajTotalCell>();

                foreach (DataGridViewCell selectedCell in base.SelectedCells)
                {
                    PuantajTotalCell puantajTotalCell = selectedCell as PuantajTotalCell;
                    if (puantajTotalCell != null)
                    {
                        selectedPuantajTotalCells.Add((PuantajTotalCell)selectedCell);                        
                    }
                }
                return selectedPuantajTotalCells.ToArray();
            }
        }
    }
}

