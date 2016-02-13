DROP VIEW aktywny;
DROP VIEW rezerwacje_wyjazd;
DROP VIEW grupa_na_wyjazd;
DROP VIEW grupa_zatrudnienie;

CREATE OR REPLACE VIEW grupa_zatrudnienie AS 
 SELECT dane_osobowe.id,
    dane_osobowe.imie,
    dane_osobowe.nazwisko,
    dane_osobowe.data_urodzenia,
    dane_osobowe.ulica,
    dane_osobowe.kod,
    miejscowosc.nazwa AS miejscowosc,
    telefon_kom.nazwa AS komorka,
    zatrudnienie.data_wyjazdu,
    zatrudnienie.data_powrotu,
    msc_odjazdu.nazwa AS msc_odjazdu,
    msc_biura.nazwa AS biuro,
    rozklad_jazdy.id_przewoznik,
    zatrudnienie.id_status,
    zatrudnienie.id_bilet,
    klient.nazwa_alt AS klient,
    zatrudnienie.id_klient,
    klient.id_panstwo_pos,
    miejsca_docelowe.nazwa AS miejsce_docelowe,
    osoby_kontaktowe.osoba  AS osoba_kontaktowa
   FROM dane_osobowe
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN miejscowosc ON dane_osobowe.id_miejscowosc = miejscowosc.id
   JOIN klient ON zatrudnienie.id_klient = klient.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
   LEFT JOIN miejsca_docelowe ON zatrudnienie.id_miejsca_docelowe = miejsca_docelowe.id
   LEFT JOIN osoby_kontaktowe ON zatrudnienie.id_osoby_kontaktowe = osoby_kontaktowe.id
  ORDER BY dane_osobowe.id;

ALTER TABLE grupa_zatrudnienie
  OWNER TO eena;

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
    grupa_zatrudnienie.osoba_kontaktowa
   FROM grupa_zatrudnienie
  WHERE grupa_zatrudnienie.id_status = 5;

ALTER TABLE grupa_na_wyjazd
  OWNER TO eena;


CREATE OR REPLACE VIEW rezerwacje_wyjazd AS 
 SELECT DISTINCT ON (grupa_zatrudnienie.id) grupa_zatrudnienie.id,
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
    bilety.nazwa AS bilet,
    bilety.cena,
    email.nazwa AS email
   FROM grupa_zatrudnienie
   LEFT JOIN email ON grupa_zatrudnienie.id = email.id
   LEFT JOIN bilety ON grupa_zatrudnienie.id_bilet = bilety.id
  WHERE grupa_zatrudnienie.id_status = ANY (ARRAY[1, 5])
  ORDER BY grupa_zatrudnienie.id;

ALTER TABLE rezerwacje_wyjazd
  OWNER TO eena;


CREATE OR REPLACE VIEW aktywny AS 
 SELECT grupa_zatrudnienie.id,
    grupa_zatrudnienie.imie,
    grupa_zatrudnienie.nazwisko,
    grupa_zatrudnienie.data_urodzenia,
    dokumenty.nip,
    grupa_zatrudnienie.data_wyjazdu,
    grupa_zatrudnienie.biuro,
    grupa_zatrudnienie.klient,
    grupa_zatrudnienie.data_powrotu,
    grupa_zatrudnienie.id_panstwo_pos
   FROM grupa_zatrudnienie
   JOIN dokumenty ON grupa_zatrudnienie.id = dokumenty.id
  WHERE grupa_zatrudnienie.id_status = 1;

ALTER TABLE aktywny
  OWNER TO eena;  