DROP VIEW grupa_na_wyjazd;
CREATE OR REPLACE VIEW grupa_na_wyjazd AS 
 SELECT grupa_zatrudnienie.id,
    grupa_zatrudnienie.imie,
    grupa_zatrudnienie.nazwisko,
    grupa_zatrudnienie.data_urodzenia,
    grupa_zatrudnienie.ulica,
    grupa_zatrudnienie.kod,
    grupa_zatrudnienie.miejscowosc,
    grupa_zatrudnienie.komorka,
    grupa_zatrudnienie.data_wyjazdu,
    grupa_zatrudnienie.data_powrotu,
    grupa_zatrudnienie.msc_odjazdu,
    grupa_zatrudnienie.biuro,
    grupa_zatrudnienie.id_przewoznik,
    grupa_zatrudnienie.id_status,
    grupa_zatrudnienie.id_bilet,
    grupa_zatrudnienie.miejsce_docelowe,
    grupa_zatrudnienie.osoba_kontaktowa,
    grupa_zatrudnienie.przystanek,
    grupa_zatrudnienie.godz_odjazdu
   FROM grupa_zatrudnienie
  WHERE grupa_zatrudnienie.id_status = 5;