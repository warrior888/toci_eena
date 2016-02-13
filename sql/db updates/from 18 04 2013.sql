drop view abfahrt;

create or replace view abfahrt as 
 SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, zatrudnienie.data_wyjazdu, 
zatrudnienie.ilosc_tyg, (klient.nazwa_alt::text || ', '::text) || oddzialy_klient.nazwa::text AS nazwa, msc_biura.nazwa AS biuro, uprawnienia.imie_nazwisko, 
zatrudnienie.id_wakat, msc_odjazdu.nazwa AS msc_odjazdu, przewoznik.nazwa AS przewoznik, klient.id_firma
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
  WHERE zatrudnienie.id_status = 5
  ORDER BY dane_osobowe.id;



alter table bilety add column id_przewoznik integer references przewoznik(id) not null default 1;

insert into bilety (nazwa, cena, id_przewoznik) select nazwa, cena, 2 from bilety where id_przewoznik = 1;

insert into bilety (nazwa, cena, id_przewoznik) select nazwa, cena, 3 from bilety where id = 1;

-- check, migrtion
-- obustronny 4

update zatrudnienie set id_bilet = 10 where id in (select id_zatrudnienie from zatrudnienie_odjazd where id_rozklad_jazdy in (select id from rozklad_jazdy where id_przewoznik = 2)) and id_bilet = 2;

-- brak 250
update zatrudnienie set id_bilet = 6 where id in (select id_zatrudnienie from zatrudnienie_odjazd where id_rozklad_jazdy in (select id from rozklad_jazdy where id_przewoznik = 2)) and id_bilet = 1;

-- jednostronny  350
update zatrudnienie set id_bilet = 7 where id in (select id_zatrudnienie from zatrudnienie_odjazd where id_rozklad_jazdy in (select id from rozklad_jazdy where id_przewoznik = 2)) and id_bilet = 3;

--- zalozenie obu
update zatrudnienie set id_bilet = 8 where id in (select id_zatrudnienie from zatrudnienie_odjazd where id_rozklad_jazdy in (select id from rozklad_jazdy where id_przewoznik = 2)) and id_bilet = 5;

--- zalozenie jedn
update zatrudnienie set id_bilet = 9 where id in (select id_zatrudnienie from zatrudnienie_odjazd where id_rozklad_jazdy in (select id from rozklad_jazdy where id_przewoznik = 2)) and id_bilet = 4;
