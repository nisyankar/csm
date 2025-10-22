using Moreum.HGO;
using System;
using System.Collections.Generic;
using Moreum.HGO.Entities;

namespace Moreum.HGO.Client.Controls
{
    internal abstract class PuantajCalculator
    {
        private PersonelPuantaj m_PersonelPuantaj;
        private Dictionary<VardiyaTipleri, double> m_VardiyaCalismaSaat;

        private double m_BayramMesai;
        private double m_HaftaTatilMesai;
        private double m_FazlaMesai;
        private double m_RaporluSaat;
        private double m_ToplamCalisma;
        private double m_ToplamEksikSaat;
        private double m_ToplamFazlaMesai;
        private double m_UcretsizSaat;
        private double m_HaftaTatilSaat;
        private double m_YillikIzin;
        private double m_UcretliIzin;
        private double m_UcretliRapor;
        private double m_UcretsizDogumIzni;
        private double m_EkGelir;
        private double m_Ayniyat;

        protected PuantajCalculator()
        {
        }

        internal static PuantajCalculator Create(PersonelPuantaj personelPuantaj, PuantajHesapMetodu hesapMetodu)
        {
            PuantajCalculator calculator;

            switch (hesapMetodu)
            {
                case PuantajHesapMetodu.Aylik:
                    calculator = new PuantajCalculatorAylik();
                    break;
                case PuantajHesapMetodu.Haftalik:
                    calculator = new PuantajCalculatorHaftalik();
                    break;
                case PuantajHesapMetodu.Sabit:
                    calculator = new PuantajCalculatorSabit();
                    break;
                default:
                    throw new Exception(string.Format("Cannot use the specified calculation method : '{0}'", hesapMetodu));
            }

            //System.Diagnostics.Debug.WriteLine("PuantajCalculator a girildi. ");
            calculator.SetPersonelPuantaj(personelPuantaj);
            return calculator;
        }

        public abstract void PerformCalculation(PersonelPuantaj personelPuantaj);

        private void SetPersonelPuantaj(PersonelPuantaj personelPuantaj)
        {
            this.m_PersonelPuantaj = personelPuantaj;

            if (this.m_PersonelPuantaj != null)
            {
                this.CalculateCalismaSaat();
                this.PerformCalculation(this.m_PersonelPuantaj);

                if (this.m_PersonelPuantaj.DevirFazlaMesai != null)
                {
                this.FazlaMesai += this.m_PersonelPuantaj.DevirFazlaMesai.DevirSuresi;
                this.ToplamFazlaMesai += this.m_PersonelPuantaj.DevirFazlaMesai.DevirSuresi;
                }

                if (this.m_PersonelPuantaj.DevirEksikSaat != null)
                {
                    this.m_ToplamEksikSaat += this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi;
                    this.m_ToplamCalisma = this.m_ToplamCalisma - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi;

                    if (this.m_FazlaMesai > 0 && (this.m_FazlaMesai -this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi) >= 0)
                    {
                        this.m_FazlaMesai = this.m_FazlaMesai - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi;
                    }
                    else if (this.m_FazlaMesai > 0 && this.m_FazlaMesai - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi< 0)
                    {
                        this.m_FazlaMesai = 0;
                    }

                    if (this.ToplamFazlaMesai > 0 && (this.ToplamFazlaMesai - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi) >= 0)
                    {
                        this.ToplamFazlaMesai = this.ToplamFazlaMesai - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi;
                    }
                    else if (this.ToplamFazlaMesai > 0 && this.ToplamFazlaMesai - this.m_PersonelPuantaj.DevirEksikSaat.DevirSuresi < 0)
                    {
                        this.ToplamFazlaMesai = 0;
                    }
                }

                if (this.m_PersonelPuantaj.EkGelir != null)
                    this.EkGelir += this.m_PersonelPuantaj.EkGelir.DevirSuresi;

                if (this.m_PersonelPuantaj.EkGelir2 != null)
                {
                    this.Ayniyat += this.m_PersonelPuantaj.EkGelir2.DevirSuresi;
                }
                
            }
        }

        //Personele ait puantajlardaki vardiyalar? tiplerine göre ay?rarak say?lar? kadar gunluk mesai saati eklenerek
        //vardiya çal??ma saatini hesaplayan method.
        private void CalculateCalismaSaat()
        {
            m_VardiyaCalismaSaat = new Dictionary<VardiyaTipleri, double>();

            foreach (Puantaj puantaj in m_PersonelPuantaj)
            {
                if (puantaj != null && puantaj.VardiyaID != 0)
                {
                    Vardiya vardiya = PuantajHelperCache.GetVardiya(puantaj.VardiyaID);

                    // Eger vardiyaTipi dictionaryde varsa gunluk mesai saati o tipe eklenir. yoksa dictionaryde o tip 
                    //yarat?larak eklenir.
                    if (vardiya != null)
                    {
                        if (m_VardiyaCalismaSaat.ContainsKey(vardiya.VardiyaTipi))
                            m_VardiyaCalismaSaat[vardiya.VardiyaTipi] += PuantajConstants.gunlukMesaiSaati;
                        else
                            m_VardiyaCalismaSaat.Add(vardiya.VardiyaTipi, PuantajConstants.gunlukMesaiSaati);

                        isEmpty = false;
                    }
                }
            }
        }

        public double BayramMesai
        {
            get
            {
                if (m_PersonelPuantaj.ManuelBayramMesai.ModifiedManually)
                    return m_PersonelPuantaj.ManuelBayramMesai.DevirSuresi;
                else
                    return this.m_BayramMesai;
            }
            protected set
            {
                this.m_BayramMesai = value;
            }
        }

        public double FazlaMesai
        {
            get
            {
                if (m_PersonelPuantaj.ManuelFazlaMesai.ModifiedManually)
                    return m_PersonelPuantaj.ManuelFazlaMesai.DevirSuresi;
                else
                    return this.m_FazlaMesai;
            }
            protected set
            {
                this.m_FazlaMesai = value;
            }
        }

        public double RaporluSaat
        {
            get
            {
                if (m_PersonelPuantaj.ManuelRaporluSaat.ModifiedManually)
                    return m_PersonelPuantaj.ManuelRaporluSaat.DevirSuresi;
                else
                    return this.m_RaporluSaat;
            }
            protected set
            {
                this.m_RaporluSaat = value;
            }
        }

        public double ToplamCalisma
        {
            get
            {
                if (m_PersonelPuantaj.ManuelToplamCalisma.ModifiedManually)
                    return m_PersonelPuantaj.ManuelToplamCalisma.DevirSuresi;
                else
                    return this.m_ToplamCalisma;
            }
            protected set
            {
                this.m_ToplamCalisma = value;
            }
        }

        public double ToplamEksikSaat
        {
            get
            {
                if (m_PersonelPuantaj.ManuelToplamEksikSaat.ModifiedManually)
                    return m_PersonelPuantaj.ManuelToplamEksikSaat.DevirSuresi;
                else
                    return this.m_ToplamEksikSaat;
            }
            protected set
            {
                this.m_ToplamEksikSaat = value;
            }
        }

        public double ToplamFazlaMesai
        {
            get
            {
                if (m_PersonelPuantaj.ManuelToplamFazlaMesai.ModifiedManually)
                    return m_PersonelPuantaj.ManuelToplamFazlaMesai.DevirSuresi;
                else
                    return this.m_ToplamFazlaMesai;
            }
            protected set
            {
                this.m_ToplamFazlaMesai = value;
            }
        }

        public double UcretsizSaat
        {
            get
            {
                if (m_PersonelPuantaj.ManuelUcretsizSaat.ModifiedManually)
                    return m_PersonelPuantaj.ManuelUcretsizSaat.DevirSuresi;
                else
                    return this.m_UcretsizSaat;
            }
            protected set
            {
                this.m_UcretsizSaat = value;
            }
        }

        public double HaftaTatiliSaat
        {
            get
            {
                return this.m_HaftaTatilSaat;
            }
            protected set
            {
                this.m_HaftaTatilSaat = value;
            }
        }

        public double YillikIzin
        {
            get
            {
                return this.m_YillikIzin;
            }
            protected set
            {
                this.m_YillikIzin = value;
            }
        }

        public double UcretliIzin
        {
            get
            {
                return this.m_UcretliIzin;
            }
            protected set
            {
                this.m_UcretliIzin = value;
            }
        }

        public double HaftaTatilMesai
        {
            get
            {
                return this.m_HaftaTatilMesai;
            }
            protected set
            {
                this.m_HaftaTatilMesai = value;
            }
        }

        public double EkGelir
        {
            get
            {
                return this.m_EkGelir;
            }
            protected set
            {
                this.m_EkGelir = value;
            }
        }

        public double Ayniyat
        {
            get
            {
                return this.m_Ayniyat;
            }
            protected set
            {
                this.m_Ayniyat = value;
            }
        }

        public double UcretsizDogumIzni
        {
            get
            {
                return this.m_UcretsizDogumIzni;
            }
            protected set
            {
                this.m_UcretsizDogumIzni = value;
            }
        }

        public double UcretliRapor
        {
            get
            {
                return this.m_UcretliRapor;
            }
            protected set
            {
                this.m_UcretliRapor = value;
            }
        }

        public Dictionary<VardiyaTipleri, double> VardiyaCalismaSaat
        {
            get
            {
                return this.m_VardiyaCalismaSaat;
            }
            private set
            {
                this.m_VardiyaCalismaSaat = value;
            }
        }

        private Boolean isEmpty = true;
        public Boolean Empty
        {
            get
            {
                return isEmpty;
            }
            set
            {
                isEmpty = value;
            }
        }

    }
}

