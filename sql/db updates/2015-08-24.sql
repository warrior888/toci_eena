CREATE OR REPLACE VIEW zestawienie_powrot AS 
 SELECT dane_osobowe.id,
    dane_osobowe.id AS nazwa,
    dane_osobowe.imie,
    dane_osobowe.nazwisko,
    dane_osobowe.data_urodzenia,
    zatrudnienie.data_powrotu,
    msc_odjazdu.nazwa AS msc_powrot,
    msc_biura.nazwa AS msc_biuro,
    uprawnienia.imie_nazwisko,
    rozklad_jazdy.id_przewoznik,
    zatrudnienie.id_pracownik
   FROM dane_osobowe
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_powrot = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN uprawnienia ON zatrudnienie.id_pracownik = uprawnienia.id;

ALTER TABLE zestawienie_wyjazd
  OWNER TO eena;