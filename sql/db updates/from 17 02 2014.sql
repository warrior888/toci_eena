alter table zatrudnienie add column id_rozklad_jazdy_wyjazd integer references rozklad_jazdy(id);

alter table zatrudnienie add column id_rozklad_jazdy_powrot integer references rozklad_jazdy(id);

alter table zatrudnienie add column id_msc_powrot integer references msc_odjazdu(id);



update zatrudnienie set id_rozklad_jazdy_wyjazd = 
(select  zatrudnienie_odjazd.id_rozklad_jazdy from zatrudnienie_odjazd where id_zatrudnienie = zatrudnienie.id);

create or replace view grupa_na_powrot as
SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, telefon_kom.nazwa AS komorka, 
zatrudnienie.data_powrotu, coalesce(msc_odjazdu.nazwa, '--------') AS msc_odjazdu, msc_biura.nazwa AS biuro, rozklad_jazdy.id_przewoznik
   FROM dane_osobowe
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba   
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
--   LEFT JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
--   LEFT JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazdu = msc_odjazdu.id
   LEFT JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_powrot = rozklad_jazdy.id
   LEFT JOIN msc_odjazdu ON zatrudnienie.id_msc_powrot = msc_odjazdu.id
  WHERE zatrudnienie.id_status = ANY (ARRAY[1, 2])
  ORDER BY dane_osobowe.id;

create or replace view abfahrt as 
SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, 
zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, (klient.nazwa_alt::text || ', '::text) || oddzialy_klient.nazwa::text AS nazwa, 
msc_biura.nazwa AS biuro, uprawnienia.imie_nazwisko, zatrudnienie.id_wakat, msc_odjazdu.nazwa AS msc_odjazdu, 
przewoznik.nazwa AS przewoznik, klient.id_firma, dokumenty.nip
   FROM imiona
   JOIN dane_osobowe ON imiona.id = dane_osobowe.id_imie
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
   JOIN przewoznik ON rozklad_jazdy.id_przewoznik = przewoznik.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN klient ON zatrudnienie.id_klient = klient.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN uprawnienia ON uprawnienia.id = zatrudnienie.id_pracownik
   LEFT JOIN dokumenty ON dane_osobowe.id = dokumenty.id
  WHERE zatrudnienie.id_status = 5
  ORDER BY dane_osobowe.id;

create or replace view zestawienie_wyjazd as
SELECT dane_osobowe.id, dane_osobowe.id AS nazwa, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, 
zatrudnienie.data_wyjazdu, msc_odjazdu.nazwa AS msc_odjazd, msc_biura.nazwa AS msc_biuro, uprawnienia.imie_nazwisko, 
rozklad_jazdy.id_przewoznik, zatrudnienie.id_pracownik
   FROM dane_osobowe
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN rozklad_jazdy ON zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN uprawnienia ON zatrudnienie.id_pracownik = uprawnienia.id;

create or replace view grupa_zatrudnienie as 
 SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, dane_osobowe.ulica, dane_osobowe.kod, miejscowosc.nazwa AS miejscowosc, telefon_kom.nazwa AS komorka, zatrudnienie.data_wyjazdu, zatrudnienie.data_powrotu, msc_odjazdu.nazwa AS msc_odjazdu, msc_biura.nazwa AS biuro, rozklad_jazdy.id_przewoznik, zatrudnienie.id_status, zatrudnienie.id_bilet, klient.nazwa_alt AS klient, zatrudnienie.id_klient, klient.id_panstwo_pos
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
  ORDER BY dane_osobowe.id;