
--email field widening as systemallows more than internet questionaire
--sequence begin
drop view osoba_internet;
drop view osoba_internet_pokaz;

alter table dane_internet alter column email type varchar(50);

create or replace view osoba_internet as 
SELECT d_o.id, d_o.id_imie, d_o.imie, d_o.nazwisko, d_o.id_plec, plec.nazwa AS plec, d_o.data_urodzenia, d_o.id_miejscowosc_ur, d_o.id_miejscowosc, d_o.miejscowosc, d_o.ulica, d_o.kod, d_o.telefon, d_o.komorka, d_o.email, d_o.id_wyksztalcenie, d_o.wyksztalcenie, d_o.id_zawod, d_o.zawod, d_o.data_zgloszenia, d_o.id_charakter, charakter.nazwa AS charakter, d_o.data, 
d_o.ilosc_tyg, d_o.id_ankieta, d_o.id_zrodlo, zrodlo.nazwa AS zrodlo, d_o.source
   FROM dane_internet d_o
   JOIN plec ON plec.id = d_o.id_plec
   JOIN charakter ON charakter.id = d_o.id_charakter
   JOIN zrodlo ON zrodlo.id = d_o.id_zrodlo;
   
--- imie miejscowosc wyksztalcenie zawod
   
create or replace view osoba_internet_pokaz as 
SELECT d_o.id, d_o.imie, d_o.nazwisko, plec.nazwa AS plec, d_o.data_urodzenia, m_ur.nazwa AS miejscowosc_ur, d_o.miejscowosc, d_o.ulica, d_o.kod, d_o.telefon, 
d_o.komorka, d_o.email, d_o.wyksztalcenie, d_o.zawod, d_o.data_zgloszenia, charakter.nazwa AS charakter, d_o.data, d_o.ilosc_tyg, zrodlo.nazwa AS zrodlo
   FROM dane_internet d_o
   JOIN plec ON plec.id = d_o.id_plec
   JOIN miejscowosc m_ur ON m_ur.id = d_o.id_miejscowosc_ur
   JOIN charakter ON charakter.id = d_o.id_charakter
   JOIN zrodlo ON zrodlo.id = d_o.id_zrodlo;
   
--sequence end