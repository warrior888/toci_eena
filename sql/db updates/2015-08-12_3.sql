DROP VIEW grupa_na_powrot ;
CREATE OR REPLACE VIEW grupa_na_powrot AS 
 SELECT dane_osobowe.id,
    dane_osobowe.imie,
    dane_osobowe.nazwisko,
    dane_osobowe.data_urodzenia,
    telefon_kom.nazwa AS komorka,
    zatrudnienie.data_powrotu,
    COALESCE(msc_odjazdu.nazwa, '--------'::character varying) AS msc_odjazdu,
    msc_biura.nazwa AS biuro,
    rozklad_jazdy.id_przewoznik,
    miejsca_docelowe.nazwa AS miejsce_docelowe,
    osoby_kontaktowe.osoba AS osoba_kontaktowa,
    (SELECT nazwa FROM telefon_inny WHERE telefon_inny.id = dane_osobowe.id LIMIT 1) AS telefon_inny
   FROM dane_osobowe
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
   LEFT JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_powrot = rozklad_jazdy.id
   LEFT JOIN msc_odjazdu ON zatrudnienie.id_msc_powrot = msc_odjazdu.id
   LEFT JOIN miejsca_docelowe ON zatrudnienie.id_miejsca_docelowe = miejsca_docelowe.id
   LEFT JOIN osoby_kontaktowe ON zatrudnienie.id_osoby_kontaktowe = osoby_kontaktowe.id
  WHERE zatrudnienie.id_status = ANY (ARRAY[1, 2])
  ORDER BY dane_osobowe.id;