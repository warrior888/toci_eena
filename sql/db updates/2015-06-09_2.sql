DROP VIEW osoba_internet;

CREATE OR REPLACE VIEW osoba_internet AS 
 SELECT d_o.id,
    d_o.id_imie,
    d_o.imie,
    d_o.nazwisko,
    d_o.id_plec,
    plec.nazwa AS plec,
    d_o.data_urodzenia,
    d_o.id_miejscowosc_ur,
    d_o.id_miejscowosc,
    d_o.miejscowosc,
    d_o.ulica,
    d_o.kod,
    d_o.telefon,
    d_o.komorka,
    d_o.inny_tel,
    d_o.email,
    d_o.id_wyksztalcenie,
    d_o.wyksztalcenie,
    d_o.id_zawod,
    d_o.zawod,
    d_o.data_zgloszenia,
    d_o.id_charakter,
    charakter.nazwa AS charakter,
    d_o.data,
    d_o.ilosc_tyg,
    d_o.id_ankieta,
    d_o.id_zrodlo,
    zrodlo.nazwa AS zrodlo,
    d_o.source
   FROM dane_internet d_o
   LEFT JOIN plec ON plec.id = d_o.id_plec
   LEFT JOIN charakter ON charakter.id = d_o.id_charakter
   JOIN zrodlo ON zrodlo.id = d_o.id_zrodlo;

ALTER TABLE osoba_internet
  OWNER TO eena;
