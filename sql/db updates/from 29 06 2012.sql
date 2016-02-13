
--to se poczeka
alter table zatrudnienie add column id_rozklad_jazdy integer REFERENCES rozklad_jazdy(id) default null;

-- to se poczeka
UPDATE zatrudnienie set id_rozklad_jazdy = zatrudnienie_odjazd.id_rozklad_jazdy from zatrudnienie_odjazd where zatrudnienie_odjazd.id_zatrudnienie = zatrudnienie.id;


--to tez nie dziala, jak zadziala wylac id wiersz ?
alter table telefon_kom add constraint un_tel_kom_osoba_id unique(id);


alter table uprawnienia add column id_firma_filia integer references firma_filia(id);

update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Opole') where id in (5, 6, 10, 28);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Racibórz') where id in (2);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Gliwice') where id in (21);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Olesno') where id in (7);


drop view zestawienie_wyjazd ;

create or replace view zestawienie_wyjazd as
select dane_osobowe.id, dane_osobowe.id as nazwa, imie, nazwisko, data_urodzenia, zatrudnienie.data_wyjazdu, msc_odjazdu.nazwa as msc_odjazd, msc_biura.nazwa as msc_biuro, uprawnienia.imie_nazwisko, rozklad_jazdy.id_przewoznik, zatrudnienie.id_pracownik from dane_osobowe join zatrudnienie on dane_osobowe.id = zatrudnienie.id_osoba join zatrudnienie_odjazd on zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie 
join rozklad_jazdy on zatrudnienie_odjazd.id_rozklad_jazdy = rozklad_jazdy.id 
join msc_odjazdu on zatrudnienie.id_msc_odjazd = msc_odjazdu.id 
join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id 
JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id 
join uprawnienia on zatrudnienie.id_pracownik = uprawnienia.id ;

where rozklad_jazdy.id_przewoznik = 2;

--ammend to privs

update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Opole') where id in (31, 29, 17);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Olesno') where id in (22);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Racibórz') where id in (24);
update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Gliwice') where id in (26, 30);

update uprawnienia set id_firma_filia = (select id from firma_filia where nazwa = 'Opole') where aktywny = false or id_firma_filia is null;