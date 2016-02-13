drop table sms_kandydat ;

create table sms_kandydat (
	id serial primary key,
	id_firma_filia integer REFERENCES firma_filia(id),
	warunek text,
	tresc text not null
);


create table ustawienia_administracyjne (
	kod varchar(30) primary key,
	tresc text
);


-- select floor((current_date - data_urodzenia) / 365.25), (current_date - data_urodzenia) / 365.25 from dane_internet;
-- select (current_date - data_urodzenia) / 365.25 from dane_internet;

insert into firma_filia (nazwa) values ('Rzeszów');


insert into sms_kandydat (id_firma_filia, warunek, tresc) values ((select id from firma_filia where nazwa = 'Opole'), 
'floor((current_date - data_urodzenia) / 365.25) <= 50', 
'E&A Agencja Zatrudnienia, zapraszamy na rozmowe kwalifikacyjna: Opole, ul.Kollataja 3/1, od pon. - pt. 8:00-18:00, sob. 9:00 - 14:00, tel. 774513860');

insert into sms_kandydat (id_firma_filia, warunek, tresc) values ((select id from firma_filia where nazwa = 'Olesno'), 
'floor((current_date - data_urodzenia) / 365.25) <= 50', 
'E&A Agencja Zatrudnienia, zapraszamy na rozmowe kwalifikacyjna: Olesno, ul.Armii Krajowej 7, od pon. - pt. 8:30-17:00, sob. 9:00 - 14:00, tel. 343504165');

insert into sms_kandydat (id_firma_filia, warunek, tresc) values ((select id from firma_filia where nazwa = 'Racibórz'), 
'floor((current_date - data_urodzenia) / 365.25) <= 50', 
'“E&A Agencja Zatrudnienia, zapraszamy na rozmowe kwalifikacyjna: Racibórz, ul.Batorego 10, od pon. - pt. 8:30-17:00, sob. 9:00 - 14:00, tel. 324149961');

insert into sms_kandydat (id_firma_filia, warunek, tresc) values ((select id from firma_filia where nazwa = 'Gliwice'), 
'floor((current_date - data_urodzenia) / 365.25) <= 50', 
'“E&A Agencja Zatrudnienia, zapraszamy na rozmowe kwalifikacyjna: Gliwice, ul. Okopowa 10, od pon. - pt. 8:30-17:00, sob. 9:00 - 14:00, tel. 322312736');

insert into sms_kandydat (id_firma_filia, warunek, tresc) values ((select id from firma_filia where nazwa = 'Rzeszów'), 
'floor((current_date - data_urodzenia) / 365.25) <= 50', 
'“E&A Agencja Zatrudnienia, zapraszamy na rozmowe kwalifikacyjna: Rzeszów, ul.Pilsudskiego 31, II pietro, pok.218, od pon.-pt.8:30-17:00, sob. 9:00-14:00, tel 177876140');



insert into ustawienia_administracyjne  (kod, tresc) values ('send_welcome_sms', 'b:1;');

create or replace view sms_powitanie as
select kody_rejestracja_filia.kod, sms_kandydat.warunek, sms_kandydat.tresc, kody_rejestracja_filia.id_firma_filia 
from sms_kandydat join kody_rejestracja_filia on sms_kandydat.id_firma_filia = kody_rejestracja_filia.id_firma_filia;