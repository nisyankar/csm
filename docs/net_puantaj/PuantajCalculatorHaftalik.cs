using Moreum.HGO.Entities;
using System;
using Moreum.HGO.Client.Services;

namespace Moreum.HGO.Client.Controls
{
    internal class PuantajCalculatorHaftalik : PuantajCalculator
    {
        public override void PerformCalculation(PersonelPuantaj personelPuantaj)
        {
            base.FazlaMesai = 0;
            base.UcretsizSaat = 0;
            base.ToplamCalisma = 0;
            base.BayramMesai = 0;
            base.HaftaTatilMesai = 0;
            base.UcretsizDogumIzni = 0;

            Double haftaFiiliCalisma = 0;
            Double haftaCalismaYukumlulugu = 0;
            Double haftaCalismaYukumluluguNet = 0;
            Double US = 0;
            Double gunCalismaYukumlulugu = 0;
            Double tatilMesai = 0;
            Vardiya vardiya = null;

            for (int index = 0; index < personelPuantaj.Count; index++)
            {
                DateTime thatDay = new DateTime(personelPuantaj.MonthDate.Year, personelPuantaj.MonthDate.Month, index + 1);

                Puantaj puantaj = personelPuantaj[index];

                Boolean workDay = true;

                gunCalismaYukumlulugu = PuantajConstants.gunlukMesaiSaati;

                if (puantaj != null)
                {
                    vardiya = PuantajHelperCache.GetVardiya(puantaj.VardiyaID);

                    puantaj.ModDate = DateTime.Now;
                    puantaj.ModUser = HGOService.Service.SessionService.CurrentUser.Name;

                    if (vardiya != null)
                    {
                        workDay = (vardiya.VardiyaTipi != VardiyaTipleri.UcretliMazeretIzni) && (vardiya.VardiyaTipi != VardiyaTipleri.Rapor) &&
                            (vardiya.VardiyaTipi != VardiyaTipleri.GenelTatil) &&
                            (vardiya.VardiyaTipi != VardiyaTipleri.HaftaTatili) && (vardiya.VardiyaTipi != VardiyaTipleri.YillikIzin);

                        if (vardiya.VardiyaTipi == VardiyaTipleri.HaftaTatili)
                        {
                            base.HaftaTatiliSaat += PuantajConstants.gunlukMesaiSaati;
                        }

                        if (vardiya.VardiyaTipi == VardiyaTipleri.YillikIzin)
                        {
                            base.YillikIzin++;
                        }

                        if ((vardiya.VardiyaTipi == VardiyaTipleri.Arefe))
                        {
                            //base.ToplamCalisma += 4;
                            base.YillikIzin = base.YillikIzin + 0.5;
                        }

                        if (vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin && vardiya.VardiyaKodu == "ÜDÝ" )
                        {
                            base.UcretsizDogumIzni++;
                        }

                        if (vardiya.VardiyaKodu == "UMI" || vardiya.VardiyaKodu == "SÝ")
                        {
                            base.UcretliIzin++;
                        }

                        if (vardiya.VardiyaKodu == "ÜR")
                        {
                            base.UcretliRapor++;
                        }

                        gunCalismaYukumlulugu = vardiya.GetCalismaYukumlulugu(thatDay.DayOfWeek);
                        if (gunCalismaYukumlulugu > PuantajConstants.gunlukMesaiSaati)
                            gunCalismaYukumlulugu = PuantajConstants.gunlukMesaiSaati;

                        if (vardiya.UcretliIzin)
                        {
                            if (workDay)
                                tatilMesai -= PuantajConstants.gunlukMesaiSaati;

                            workDay = false;

                            if ((HGOService.Service.CurrentDomainID == HGOService.bireyselDomainID || HGOService.Service.CurrentDomainID == HGOService.deneyimDomainID) && (vardiya.VardiyaTipi == VardiyaTipleri.HaftaTatili))
                                base.HaftaTatilMesai += puantaj.CalismaSuresi;
                            else
                                base.BayramMesai += puantaj.CalismaSuresi;
                        }
                        else
                        {
                            if (vardiya.VardiyaTipi == VardiyaTipleri.Rapor || vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin)
                            {
                                //Rapor & UcretsizIzin overrides Sundays.
                                workDay = true;

                                if (vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin)
                                {
                                    US += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                                }
                                else
                                {
                                    base.RaporluSaat += PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi;
                                }
                            }
                            else if (puantaj.CalismaSuresi < gunCalismaYukumlulugu)
                            {
                                US += gunCalismaYukumlulugu - puantaj.CalismaSuresi; // reference only.
                                //base.RaporluSaat += gunCalismaYukumlulugu - puantaj.CalismaSuresi; // reference only.
                            }
                        }

                        base.ToplamCalisma += puantaj.CalismaSuresi; // reference only.

                        if (workDay)
                        {
                            if (vardiya.VardiyaTipi != VardiyaTipleri.Rapor && vardiya.VardiyaTipi != VardiyaTipleri.UcretsizIzin)
                                haftaFiiliCalisma += puantaj.CalismaSuresi;
                            else if (vardiya.VardiyaTipi == VardiyaTipleri.UcretsizIzin)
                            {
                                haftaFiiliCalisma = haftaFiiliCalisma - (PuantajConstants.gunlukMesaiSaati - puantaj.CalismaSuresi);
                            }
                        }
                        else
                        {
                            tatilMesai += puantaj.CalismaSuresi;
                        }
                    }
                }

                if (workDay)
                {
                    haftaCalismaYukumlulugu += gunCalismaYukumlulugu;

                    if (vardiya != null && puantaj != null)
                        haftaCalismaYukumluluguNet += gunCalismaYukumlulugu;
                }

                #region Haftalik Hesaplama

                if (vardiya != null && ((vardiya.VardiyaTipi == VardiyaTipleri.HaftaTatili) || thatDay.Day == personelPuantaj.Count))
                {
                    Double haftalikCalismaToplami;
                    Double haftalikCalismaToplamiNet;

                    haftalikCalismaToplami = haftaFiiliCalisma - haftaCalismaYukumlulugu;
                    haftalikCalismaToplamiNet = haftaFiiliCalisma - haftaCalismaYukumluluguNet;

                    if (haftalikCalismaToplamiNet > 0)
                    {
                        base.FazlaMesai += haftalikCalismaToplamiNet;
                    }
                    else
                    {
                        base.ToplamEksikSaat += Math.Abs(haftalikCalismaToplamiNet);

                        base.UcretsizSaat += Math.Abs(haftalikCalismaToplamiNet);
                    }

                    //Ýþe hafta ortasýnda girmiþ yada iþten hafta sonuna doðru çýkmýþ personelin ToplamEksikmSaat inin hesaplanmas?nda kullan?l?r.
                    base.ToplamEksikSaat += haftaCalismaYukumlulugu - haftaCalismaYukumluluguNet;

                    //Add tatilMesai after everything else so they will not be counted against missing hours.
                    //base.ToplamFazlaMesai += tatilMesai;

                    haftaCalismaYukumlulugu = 0;
                    haftaCalismaYukumluluguNet = 0;
                    haftaFiiliCalisma = 0;
                    tatilMesai = 0;
                    US = 0;
                }
                #endregion
            }

            base.ToplamFazlaMesai = base.FazlaMesai + base.BayramMesai;

            //base.FazlaMesai = base.FazlaMesai - base.BayramMesai;

        }
    }
}

