DROP VIEW osoba_internet_pokaz;
CREATE VIEW osoba_internet_pokaz AS 
 SELECT d_o.id,
    d_o.imie,
    d_o.nazwisko,
    plec.nazwa AS plec,
    d_o.data_urodzenia,
    m_ur.nazwa AS miejscowosc_ur,
    d_o.miejscowosc,
    d_o.ulica,
    d_o.kod,
    d_o.telefon,
    d_o.komorka,
    d_o.inny_tel,
    d_o.email,
    d_o.wyksztalcenie,
    d_o.zawod,
    d_o.data_zgloszenia,
    charakter.nazwa AS charakter,
    d_o.data,
    d_o.ilosc_tyg,
    zrodlo.nazwa AS zrodlo
   FROM dane_internet d_o
   JOIN plec ON plec.id = d_o.id_plec
   JOIN miejscowosc m_ur ON m_ur.id = d_o.id_miejscowosc_ur
   JOIN charakter ON charakter.id = d_o.id_charakter
   JOIN zrodlo ON zrodlo.id = d_o.id_zrodlo;

ALTER TABLE osoba_internet_pokaz
  OWNER TO eena;