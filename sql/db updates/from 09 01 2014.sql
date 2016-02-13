drop view abfahrt;

create or replace view abfahrt as
SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, 
zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, (klient.nazwa_alt::text || ', '::text) || oddzialy_klient.nazwa::text AS nazwa, 
msc_biura.nazwa AS biuro, uprawnienia.imie_nazwisko, zatrudnienie.id_wakat, msc_odjazdu.nazwa AS msc_odjazdu, 
przewoznik.nazwa AS przewoznik, klient.id_firma, dokumenty.nip
   FROM imiona
   JOIN dane_osobowe ON imiona.id = dane_osobowe.id_imie
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN zatrudnienie_odjazd ON zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
   JOIN rozklad_jazdy ON zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id
   JOIN przewoznik ON rozklad_jazdy.id_przewoznik = przewoznik.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN klient ON zatrudnienie.id_klient = klient.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN uprawnienia ON uprawnienia.id = zatrudnienie.id_pracownik
   LEFT JOIN dokumenty on dane_osobowe.id = dokumenty.id 
  WHERE zatrudnienie.id_status = 5
  ORDER BY dane_osobowe.id;
  
  
drop view wakat_strona;
  
create or replace view wakat_strona as 
	select wakat.id, wakat.id_klient, wakat.id_oddzial, wakat.data_wyjazdu, wakat.ilosc_tyg, wakat.data_wpisu, klient.nazwa as klient, 
	zawod.nazwa AS stanowisko, panstwo.nazwa as panstwo, msc_biura.nazwa AS biuro, oddzialy_klient.nazwa as oddzial, opis_prac.opis,  
	oddzialy_klient.stawka 
from wakat join klient on wakat.id_klient = klient.id join oddzialy_klient on wakat.id_oddzial = oddzialy_klient.id 
	join zawod on oddzialy_klient.stanowisko = zawod.id join panstwo on klient.id_panstwo_egz = panstwo.id 
	join miejscowosc_biuro on oddzialy_klient.id_biuro = miejscowosc_biuro.id join msc_biura on miejscowosc_biuro.id_msc_biuro = msc_biura.id
	join opis_prac on oddzialy_klient.id = opis_prac.id_oddzialy_klient 
where 
	opis_prac.typ = 3 and wakat.widoczne_www = true;