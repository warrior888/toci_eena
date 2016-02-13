drop view grupa_na_wyjazd;
drop view rezerwacje_wyjazd;
drop view grupa_zatrudnienie;

create or replace view  grupa_zatrudnienie as 

SELECT dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia,
dane_osobowe.ulica,  dane_osobowe.kod, miejscowosc.nazwa as miejscowosc, 
telefon_kom.nazwa AS komorka, zatrudnienie.data_wyjazdu, zatrudnienie.data_powrotu,
msc_odjazdu.nazwa AS msc_odjazdu, msc_biura.nazwa AS biuro, rozklad_jazdy.id_przewoznik, zatrudnienie.id_status,
zatrudnienie.id_bilet
   FROM imiona
   JOIN dane_osobowe ON imiona.id = dane_osobowe.id_imie
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN zatrudnienie_odjazd ON zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
   JOIN rozklad_jazdy ON zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN miejscowosc on dane_osobowe.id_miejscowosc = miejscowosc.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
  ORDER BY dane_osobowe.id;

create or replace view  grupa_na_wyjazd as 

SELECT * from grupa_zatrudnienie where id_status = 5;

create or replace view rezerwacje_wyjazd as
select grupa_zatrudnienie.*, bilety.nazwa as bilet, bilety.cena, email.nazwa as email 
from grupa_zatrudnienie 
left join email on grupa_zatrudnienie.id = email.id
left join bilety on grupa_zatrudnienie.id_bilet = bilety.id
where id_status in (1, 5);

  
alter table widoki alter column sql type text;  

  update widoki set sql = 'select id, imie, nazwisko, data_urodzenia, komorka, data_wyjazdu, data_powrotu, msc_odjazdu, biuro, id_przewoznik from grupa_na_wyjazd' where id = 1;
  update widoki set nag = 'Id|Imiê|Nazwisko|Data urodzenia|Telefon|Data wyjazdu|Data powrotu|Miejsce wsiadania|Biuro|PrzewoŸnik' where id = 1;


