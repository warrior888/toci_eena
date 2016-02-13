drop view grupa_na_wyjazd;
drop view rezerwacje_wyjazd;
drop view aktywny;
drop view grupa_zatrudnienie;


create or replace view grupa_zatrudnienie as 

 SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, dane_osobowe.ulica, dane_osobowe.kod, 
miejscowosc.nazwa AS miejscowosc, telefon_kom.nazwa AS komorka, zatrudnienie.data_wyjazdu, zatrudnienie.data_powrotu, 
msc_odjazdu.nazwa AS msc_odjazdu, msc_biura.nazwa AS biuro, rozklad_jazdy.id_przewoznik, zatrudnienie.id_status, zatrudnienie.id_bilet, 
klient.nazwa_alt as klient, zatrudnienie.id_klient, klient.id_panstwo_pos 
   FROM 
   dane_osobowe JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN zatrudnienie_odjazd ON zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
   JOIN rozklad_jazdy ON zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN miejscowosc ON dane_osobowe.id_miejscowosc = miejscowosc.id
   JOIN klient ON zatrudnienie.id_klient = klient.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
  ORDER BY dane_osobowe.id;

create or replace view aktywny as 
select grupa_zatrudnienie.id, imie, nazwisko, data_urodzenia, dokumenty.nip, data_wyjazdu, biuro, klient, data_powrotu, id_panstwo_pos 
	from 
	grupa_zatrudnienie join dokumenty on grupa_zatrudnienie.id = dokumenty.id 
	where id_status = 1;

create or replace view rezerwacje_wyjazd as 
 SELECT grupa_zatrudnienie.id, grupa_zatrudnienie.imie, grupa_zatrudnienie.nazwisko, grupa_zatrudnienie.data_urodzenia, 
grupa_zatrudnienie.ulica, grupa_zatrudnienie.kod, grupa_zatrudnienie.miejscowosc, grupa_zatrudnienie.komorka, 
grupa_zatrudnienie.data_wyjazdu, grupa_zatrudnienie.data_powrotu, grupa_zatrudnienie.msc_odjazdu, grupa_zatrudnienie.biuro, 
grupa_zatrudnienie.id_przewoznik, grupa_zatrudnienie.id_status, grupa_zatrudnienie.id_bilet, bilety.nazwa AS bilet, bilety.cena, 
email.nazwa AS email
   FROM grupa_zatrudnienie
   LEFT JOIN email ON grupa_zatrudnienie.id = email.id
   LEFT JOIN bilety ON grupa_zatrudnienie.id_bilet = bilety.id
  WHERE grupa_zatrudnienie.id_status = ANY (ARRAY[1, 5]);


create or replace view grupa_na_wyjazd as 

 SELECT grupa_zatrudnienie.id, grupa_zatrudnienie.imie, grupa_zatrudnienie.nazwisko, grupa_zatrudnienie.data_urodzenia, 
grupa_zatrudnienie.ulica, grupa_zatrudnienie.kod, grupa_zatrudnienie.miejscowosc, grupa_zatrudnienie.komorka, 
grupa_zatrudnienie.data_wyjazdu, grupa_zatrudnienie.data_powrotu, grupa_zatrudnienie.msc_odjazdu, grupa_zatrudnienie.biuro, 
grupa_zatrudnienie.id_przewoznik, grupa_zatrudnienie.id_status, grupa_zatrudnienie.id_bilet
   FROM grupa_zatrudnienie
  WHERE grupa_zatrudnienie.id_status = 5;

drop table raport_aktywny;

create table raport_aktywny (

	id serial primary key,
	nazwa varchar(100) not null
	-- id firma
	--id msc biura
);

insert into raport_aktywny (nazwa) values ('warriorr@poczta.fm');

insert into raport_aktywny (nazwa) values ('lmaassen@eena.nl');
insert into raport_aktywny (nazwa) values ('skoscielny@eena.pl');
insert into raport_aktywny (nazwa) values ('mczupala@eena.pl');