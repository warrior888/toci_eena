create or replace view grupa_na_powrot as 
SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, 
telefon_kom.nazwa AS komorka, zatrudnienie.data_powrotu, msc_odjazdu.nazwa AS msc_odjazdu, msc_biura.nazwa AS biuro, 
rozklad_jazdy.id_przewoznik
   FROM dane_osobowe 
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN zatrudnienie_odjazd ON zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
   JOIN rozklad_jazdy ON zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
  WHERE zatrudnienie.id_status = ANY (ARRAY[1, 2]) 
  ORDER BY dane_osobowe.id;

create or replace view rezerwacje_wyjazd as 
 SELECT distinct on (grupa_zatrudnienie.id) grupa_zatrudnienie.id, grupa_zatrudnienie.imie, grupa_zatrudnienie.nazwisko, grupa_zatrudnienie.data_urodzenia, 
grupa_zatrudnienie.ulica, grupa_zatrudnienie.kod, grupa_zatrudnienie.miejscowosc, grupa_zatrudnienie.komorka, grupa_zatrudnienie.data_wyjazdu, 
grupa_zatrudnienie.data_powrotu, grupa_zatrudnienie.msc_odjazdu, grupa_zatrudnienie.biuro, grupa_zatrudnienie.id_przewoznik, 
grupa_zatrudnienie.id_status, grupa_zatrudnienie.id_bilet, bilety.nazwa AS bilet, bilety.cena, email.nazwa AS email
   FROM grupa_zatrudnienie
   LEFT JOIN email ON grupa_zatrudnienie.id = email.id
   LEFT JOIN bilety ON grupa_zatrudnienie.id_bilet = bilety.id
  WHERE grupa_zatrudnienie.id_status = ANY (ARRAY[1, 5]);


alter table wakat add column widoczne_www boolean default false;

create or replace view wakat_strona as 
	select wakat.id, wakat.id_klient, wakat.id_oddzial, wakat.data_wyjazdu, wakat.ilosc_tyg, wakat.data_wpisu, klient.nazwa as klient, 
	zawod.nazwa AS stanowisko, panstwo.nazwa as panstwo, msc_biura.nazwa AS biuro, oddzialy_klient.nazwa as oddzial, opis_prac.opis 
from wakat join klient on wakat.id_klient = klient.id join oddzialy_klient on wakat.id_oddzial = oddzialy_klient.id 
	join zawod on oddzialy_klient.stanowisko = zawod.id join panstwo on klient.id_panstwo_egz = panstwo.id 
	join miejscowosc_biuro on oddzialy_klient.id_biuro = miejscowosc_biuro.id join msc_biura on miejscowosc_biuro.id_msc_biuro = msc_biura.id
	join opis_prac on oddzialy_klient.id = opis_prac.id_oddzialy_klient 
where 
	opis_prac.typ = 3 and wakat.widoczne_www = true;






