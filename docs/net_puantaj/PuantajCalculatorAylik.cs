using Moreum.HGO.Entities;
using System;
using Moreum.HGO.Client.Services;

namespace Moreum.HGO.Client.Controls
{
    internal class PuantajCalculatorAylik : PuantajCalculator
    {
        public override void PerformCalculation(PersonelPuantaj personelPuantaj)
        {
            Int32 kadroluCalismaGun = 0;

            Double bayram = 0;

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

                        //if (vardiya.UcretliIzin && puantaj.CalismaSuresi > 0)
                        //{
                        //    base.BayramMesai += puantaj.CalismaSuresi;
                        //}

                        if (vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin)
                        {
                            base.UcretsizSaat += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.Rapor))
                        {
                            base.RaporluSaat += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                            //base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati; //04.03.2013 de kaldýrýldý!
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.HaftaTatili))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                            base.HaftaTatiliSaat += PuantajConstants.gunlukMesaiSaati;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.YillikIzin))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                            base.YillikIzin++;
                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.Arefe))
                        {
                            base.ToplamCalisma += 4;
                            base.YillikIzin=base.YillikIzin+0.5;
                        }
                        else if (vardiya.VardiyaTipi == VardiyaTipleri.GenelTatil)
                        {
                            //30.04.2010 daki istek uzerine deðiþtirilmiþtir.
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;

                            if (puantaj.CalismaSuresi >0 )
                            {
                                bayram += puantaj.CalismaSuresi;
                                //rapor += puantaj.CalismaSuresi;
                            }

                        }
                        else if ((vardiya.VardiyaTipi == VardiyaTipleri.UcretliMazeretIzni))
                        {
                            base.ToplamCalisma += PuantajConstants.gunlukMesaiSaati;
                        }
                    }
                }
            }

            kadroluCalismaGun = (Int32)((kadroluCalismaGun) - ((base.UcretsizSaat / 7.5) + (base.RaporluSaat / 7.5))); //04.03.2013 de eklendi!
            Int32 kanuniIzinHakkiGun = kadroluCalismaGun / 7;
            Int32 calismaYukumluguGun = kadroluCalismaGun - kanuniIzinHakkiGun;
            Double calismaYukumluluguSaat = calismaYukumluguGun * 7.5;

            if (base.ToplamCalisma >= calismaYukumluluguSaat)
            {
                base.FazlaMesai = ((base.ToplamCalisma-bayram) - calismaYukumluluguSaat);
                base.BayramMesai = bayram;
                base.ToplamEksikSaat = 0;
                // 03.06.2013
                //if (base.FazlaMesai > base.UcretsizSaat)
                //{
                //    base.UcretsizSaat = base.FazlaMesai ;
                //}
                //else
                //    base.UcretsizSaat = base.UcretsizSaat - base.FazlaMesai;
            }
            else
            {
                base.ToplamEksikSaat = calismaYukumluluguSaat - base.ToplamCalisma;
                base.FazlaMesai = 0;
                base.BayramMesai = 0;
            }

            base.ToplamFazlaMesai = base.FazlaMesai + base.BayramMesai ;
        }
    }
}

