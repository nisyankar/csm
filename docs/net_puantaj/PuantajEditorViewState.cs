using System;
using System.Text;
using System.Xml;

namespace Moreum.HGO.Client.Controls
{
    [Serializable]
    public class PuantajEditorViewState : IXMLSerializationSupport
    {
        private int m_AltProjeID;
        private DateTime m_MonthDate;

        private PuantajEditorViewState()
        {
        }

        public PuantajEditorViewState(int altProjeID, DateTime monthDate)
        {
            this.m_AltProjeID = altProjeID;
            DateTime trimmedMonthDate = new DateTime(monthDate.Year, monthDate.Month, 1);
            this.m_MonthDate = trimmedMonthDate;
        }

        public int AltProjeID
        {
            get
            {
                return this.m_AltProjeID;
            }
        }

        public DateTime MonthDate
        {
            get
            {
                return this.m_MonthDate;
            }
        }

        internal static PuantajEditorViewState NewFromXML(string xmlData)
        {
            PuantajEditorViewState retVal = null;

            if (!String.IsNullOrEmpty(xmlData))
            {
                System.Xml.XmlDocument tmpDocument = new XmlDocument();
                tmpDocument.LoadXml(xmlData);

                retVal = new PuantajEditorViewState();
                retVal.XMLParse(tmpDocument.DocumentElement);
            }

            return retVal;
        }

        public string GetXML()
        {
            StringBuilder tmpXML = new StringBuilder();
            XmlWriter writer = XmlWriter.Create(tmpXML);
            XMLCompile(writer, false);
            writer.Close();

            return tmpXML.ToString();
        }

        #region IXMLSerializationSupport Members

        public string XMLNodeName
        {
            get
            {
                return "puantajParams";
            }
        }

        public void XMLCompile(System.Xml.XmlWriter writer, bool complete)
        {
            writer.WriteStartElement(this.XMLNodeName);
            writer.WriteElementString("altProjeID", this.AltProjeID.ToString());
            writer.WriteElementString("monthDate", this.MonthDate.ToString("u"));
            writer.WriteEndElement();
        }

        public void XMLParse(System.Xml.XmlNode node)
        {
            if (node == null)
                throw new ArgumentNullException("node");

            foreach (XmlNode m_Node in node.ChildNodes)
            {
                switch (m_Node.Name)
                {
                    case "altProjeID":
                        m_AltProjeID = Int32.Parse(m_Node.InnerText);
                        break;
                    case "monthDate":
                        m_MonthDate = DateTime.Parse(m_Node.InnerText);
                        break;
                }
            }
        }

        #endregion
    }
}

