using System;
using System.Collections.Generic;
using System.Text;
using Moreum.HGO.Entities;
using Moreum.HGO.Client.Services;
using Moreum.HGO.ObjectRepository;

namespace Moreum.HGO.Client.Controls
{
    internal static class PuantajHelperCache
    {
        private static Dictionary<Int32, Vardiya> m_VardiyaCache;
        private static Dictionary<Int32, String> m_GorevYeriCache;

        internal static Vardiya GetVardiya(Int32 vardiyaID)
        {
            Vardiya retVal = null;

            if (m_VardiyaCache == null)
            {
                RefreshVardiyaCache();
            }

            if (vardiyaID > 0)
            {
                m_VardiyaCache.TryGetValue(vardiyaID, out retVal);
            }

            return retVal;
        }

        internal static Vardiya GetVardiya(String vardiyaKodu)
        {
            Vardiya retVal = null;

            if (m_VardiyaCache == null)
            {
                RefreshVardiyaCache();
            }

            foreach (Vardiya oneVardiya in m_VardiyaCache.Values)
            {
                if (oneVardiya.VardiyaKodu == vardiyaKodu)
                {
                    retVal = oneVardiya;
                    break;
                }
            }

            return retVal;
        }

        internal static String GetGorevYeri(Int32 altProjeID)
        {
            String retVal = null;

            if (m_GorevYeriCache == null)
            {
                m_GorevYeriCache = new Dictionary<int, string>();
            }

            if (altProjeID > 0 && m_GorevYeriCache.ContainsKey(altProjeID))
            {
                retVal = m_GorevYeriCache[altProjeID];
            }
            else
            {
                AltProje altProje = HGOService.Service.GetEntity(typeof(AltProje), altProjeID) as AltProje;

                if (altProje != null)
                {
                    Proje proje = HGOService.Service.GetEntity(typeof(Proje), altProje.ProjeID) as Proje;

                    if (proje == null)
                        throw new Exception("AltProje without parent Proje. Possible database corruption.");

                    retVal = proje.Ad + "\\" + altProje.Ad;

                    m_GorevYeriCache.Add(altProjeID, retVal);
                }
            }

            return retVal;
        }

        internal static void RefreshVardiyaCache()
        {
            if (m_VardiyaCache == null)
                m_VardiyaCache = new Dictionary<int, Vardiya>();
            else
                m_VardiyaCache.Clear();

            Entity[] allVardiyaEntities = HGOService.Service.FindEntities(typeof(Vardiya));

            foreach (Vardiya oneVardiya in allVardiyaEntities)
            {
                m_VardiyaCache.Add(oneVardiya.ID, oneVardiya);
            }
        }

        private static DateTime? m_CachedServerDate;
        internal static DateTime CachedServerDate
        {
            get
            {
                if (!m_CachedServerDate.HasValue)
                {
                    RefreshCachedServerDate();
                }

                return m_CachedServerDate.Value;
            }
        }

        internal static void RefreshCachedServerDate()
        {
            m_CachedServerDate = HGOService.Service.CurrentDate;
        }
    }
}
