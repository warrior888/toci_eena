create table nieodpowiedni_powod (

	id serial primary key,
	id_dane_osobowe integer references dane_osobowe(id) not null,
	id_uprawnienia integer references uprawnienia(id) not null,
	data timestamp not null default now(),
	id_zatrudnienie integer references zatrudnienie(id) , -- on update cascade on delete cascade
	powod text not null
);

create view zwolniony as 
	select dane_osobowe.id, dane_osobowe.nazwisko, dane_osobowe.imie, dane_osobowe.data_urodzenia, 
	zatrudnienie.data_powrotu, zatrudnienie.id_status, klient.nazwa as klient, oddzialy_klient.nazwa as oddzial, 
	nieodpowiedni_powod.powod, zatrudnienie.id_pracownik, nieodpowiedni_powod.id_uprawnienia,
	u1.imie_nazwisko as konsultant_zatrudnienie, u2.imie_nazwisko as konsultant_powod
from dane_osobowe join zatrudnienie on dane_osobowe.id = zatrudnienie.id_osoba 
join nieodpowiedni_powod on zatrudnienie.id = nieodpowiedni_powod.id_zatrudnienie
join klient on zatrudnienie.id_klient = klient.id
join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id
join uprawnienia u1 on zatrudnienie.id_pracownik = u1.id
join uprawnienia u2 on nieodpowiedni_powod.id_uprawnienia = u2.id;

insert into widoki (nazwa, sql, nag) values ('Zadania dnia', 'select * from zadania_dnia_filtr', '');
insert into widoki (nazwa, sql, nag) values ('Zwolniony', 'select * from zwolniony', '');

alter table widoki_edit alter column gdzie type varchar(50);

insert into widoki_edit (id_widoku, co, gdzie, nazwa) values (5, '', 'data_powrotu', 'Data powrotu');
insert into widoki_edit (id_widoku, co, gdzie, nazwa) values (5, '%', 'konsultant_zatrudnienie', 'Konsultant zatrudniaj¹cy');


create or replace view grupa_na_powrot as 
 SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, 
telefon_kom.nazwa AS komorka, zatrudnienie.data_powrotu, msc_odjazdu.nazwa AS msc_odjazdu, msc_biura.nazwa AS biuro, 
rozklad_jazdy.id_przewoznik
   FROM imiona
   JOIN dane_osobowe ON imiona.id = dane_osobowe.id_imie
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN zatrudnienie_odjazd ON zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
   JOIN rozklad_jazdy ON zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   LEFT JOIN telefon_kom ON dane_osobowe.id = telefon_kom.id
  WHERE zatrudnienie.id_status in (1, 2)
  ORDER BY dane_osobowe.id;