using Moreum.HGO.Entities;
using System;
using Moreum.HGO.Client.Services;

namespace Moreum.HGO.Client.Controls
{
    internal class PuantajCalculatorSabit : PuantajCalculator
    {
        public override void PerformCalculation(PersonelPuantaj personelPuantaj)
        {
            Int32 kadroluCalismaGun = 0;

            foreach (Puantaj puantaj in personelPuantaj)
            {
                if (puantaj != null)
                {
                    puantaj.ModDate = DateTime.Now;
                    puantaj.ModUser = HGOService.Service.SessionService.CurrentUser.Name;

                    kadroluCalismaGun++;

                    Vardiya vardiya = PuantajHelperCache.GetVardiya(puantaj.VardiyaID);

                    if (vardiya != null)
                    {
                        base.ToplamCalisma += puantaj.CalismaSuresi;

                        if ((vardiya.VardiyaTipi==VardiyaTipleri.GenelTatil) && puantaj.CalismaSuresi > 0)
                        {
                            base.BayramMesai += puantaj.CalismaSuresi;
                        }

                        if (vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin)
                        {
                            base.UcretsizSaat += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.Rapor))
                        {
                            base.RaporluSaat += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.HaftaTatili))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                            base.HaftaTatiliSaat += PuantajConstants.gunlukMesaiSaati;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.Istirahat))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                            base.HaftaTatiliSaat += PuantajConstants.gunlukMesaiSaati;
                            base.FazlaMesai += puantaj.CalismaSuresi;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.YillikIzin))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                            base.YillikIzin++;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.Arefe))
                        {
                            base.ToplamCalisma += 4;
                            base.YillikIzin = base.YillikIzin + 0.5;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.GenelTatil))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.UcretliMazeretIzni))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                        }
                    }
                }
            }

            Int32 kanuniIzinHakkiGun = kadroluCalismaGun / 7;
            Int32 calismaYukumluguGun = kadroluCalismaGun - kanuniIzinHakkiGun;
            Double calismaYukumluluguSaat = calismaYukumluguGun * 7.5;

            //if (base.ToplamCalisma >= calismaYukumluluguSaat)   02.12.2013
            //{
            //    //base.FazlaMesai = base.ToplamCalisma - calismaYukumluluguSaat;
            //    base.ToplamEksikSaat = 0;
            //    if (base.FazlaMesai > base.UcretsizSaat)
            //        base.UcretsizSaat = 0;
            //    else
            //        base.UcretsizSaat = base.UcretsizSaat - base.FazlaMesai;
            //}
            //else
            //{
            //    base.ToplamEksikSaat = calismaYukumluluguSaat - base.ToplamCalisma;
            //    //base.FazlaMesai = 0;
            //}


            if (base.UcretsizSaat > 0)
            {
                //base.FazlaMesai = base.ToplamCalisma - calismaYukumluluguSaat;
                base.ToplamEksikSaat = base.UcretsizSaat;
                
            }
            
            base.ToplamFazlaMesai = base.FazlaMesai + base.BayramMesai;
        }
    }
}

