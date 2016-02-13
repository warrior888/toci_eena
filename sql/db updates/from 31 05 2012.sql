
-- select id_osoba, count(distinct id_oddzial) as liczba from zatrudnienie group by id_osoba order by liczba desc;

--  4222 
-- 10737 
--  6408 

-- todo tell them to modify nazwa alt to include branch info like wheter it is a farming or magazine etc
-- , oddzialy_klient.nazwa_alt as branza 

select klient.nazwa as klient, panstwo.nazwa as panstwo, msc_biura.nazwa as miasto, zawod.nazwa as zawod, oddzialy_klient.id as id_oddzial from
	zatrudnienie join klient on zatrudnienie.id_klient = klient.id
		join panstwo on klient.id_panstwo_egz = panstwo.id
	    join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id
		join zawod on oddzialy_klient.stanowisko = zawod.id
		join miejscowosc_biuro on oddzialy_klient.id_biuro = miejscowosc_biuro.id
			join msc_biura on miejscowosc_biuro.id_msc_biuro = msc_biura.id
	where zatrudnienie.id_osoba = 6408;


select sum(ilosc_tyg::int), id_oddzial from zatrudnienie where id_osoba = 4222 group by id_oddzial;

select id_osoba, id_oddzial from zatrudnienie where id_status = 4 group by id_osoba, id_oddzial order by id_osoba;


--koniec testowych sql i, ten alter ponizej ok
---BEGIN MODIFICATIONS




CREATE OR REPLACE FUNCTION pc_chartoint(chartoconvert character varying)
  RETURNS integer AS
$BODY$
SELECT CASE WHEN trim($1) SIMILAR TO '[0-9]+' 
        THEN CAST(trim($1) AS integer) 
    ELSE NULL END;

$BODY$
  LANGUAGE 'sql' IMMUTABLE STRICT;


UPDATE zatrudnienie set ilosc_tyg = 0 where ilosc_tyg = '' or ilosc_tyg = '-';

drop view ankieta_holandia;
drop view abfahrt;
drop view umowa cascade;

  
alter table zatrudnienie alter column ilosc_tyg type integer USING pc_chartoint(ilosc_tyg);


alter TABLE poprzedni_pracodawca add column id_oddzialy_klient integer references oddzialy_klient(id) default null;

---that is it, no more changes, rest only left out of didactic reason

--ALTER table zatrudnienie alter column ilosc_tyg SET DEFAULT 0;
--alter table zatrudnienie alter column ilosc_tyg type varchar(3);


--- !!!!!!!!!!!!!!!!!!! below unnecessary any more

--alter table zatrudnienie rename column ilosc_tyg to ilosc_tygodni;
--alter table zatrudnienie add column ilosc_tyg integer;
--UPDATE zatrudnienie set ilosc_tygodni = 0 where ilosc_tygodni = '' or ilosc_tygodni = '-';
--update zatrudnienie set ilosc_tyg = ilosc_tygodni::int;

--todo recreate views before drop here
--ankieta_holandia
--abfahrt
--umowa
--podajdaneumowa(integer)



create or replace view ankieta_holandia as 
SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, plec.nazwa AS plec, dane_osobowe.data_urodzenia, m_ur.nazwa AS msc_ur, miejscowosc.nazwa AS msc, dane_osobowe.ulica, dane_osobowe.kod, wyksztalcenie.nazwa AS wyksztalcenie, zawod.nazwa AS zawod, email.nazwa AS email, telefon.nazwa AS telefon, telefon_kom.nazwa AS tel_kom, dokumenty.pass_nr AS paszport, dokumenty.data_waznosci, dokumenty.nip AS sofi, bank.nazwa AS bank, bank.swift, dokumenty.nr_konta AS konto, dane_osobowe.nr_obuwia, (klient.nazwa::text || ', '::text) || oddzialy_klient.nazwa::text AS klient, zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, msc_biura.nazwa AS biuro, zatrudnienie.id_status
   FROM dane_osobowe
   JOIN imiona ON dane_osobowe.id_imie = imiona.id
   JOIN plec ON dane_osobowe.id_plec = plec.id
   JOIN wyksztalcenie ON dane_osobowe.id_wyksztalcenie = wyksztalcenie.id
   JOIN zawod ON dane_osobowe.id_zawod = zawod.id
   JOIN zatrudnienie ON zatrudnienie.id_osoba = dane_osobowe.id
   JOIN status ON zatrudnienie.id_status = status.id
   JOIN klient ON klient.id = zatrudnienie.id_klient
   JOIN oddzialy_klient ON oddzialy_klient.id = zatrudnienie.id_oddzial
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN miejscowosc m_ur ON dane_osobowe.id_miejscowosc_ur = m_ur.id
   JOIN miejscowosc ON dane_osobowe.id_miejscowosc = miejscowosc.id
   LEFT JOIN dokumenty ON dokumenty.id = dane_osobowe.id
   LEFT JOIN bank ON bank.id = dokumenty.id_bank
   LEFT JOIN telefon ON telefon.id = dane_osobowe.id
   LEFT JOIN telefon_kom ON telefon_kom.id = dane_osobowe.id
   LEFT JOIN telefon_inny ON telefon_inny.id = dane_osobowe.id
   LEFT JOIN email ON email.id = dane_osobowe.id
  WHERE zatrudnienie.id_status = ANY (ARRAY[1, 5])
  ORDER BY dane_osobowe.id;
    

create or replace view abfahrt as 
SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, (klient.nazwa_alt::text || ', '::text) || oddzialy_klient.nazwa::text AS nazwa, msc_biura.nazwa AS biuro, uprawnienia.imie_nazwisko, zatrudnienie.id_wakat, msc_odjazdu.nazwa AS msc_odjazdu, przewoznik.nazwa AS przewoznik
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


--ref rejestracje dedykowane oddzial

create or replace view umowa as 
SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.id_plec, dane_osobowe.data_urodzenia, 
dane_osobowe.ulica, dane_osobowe.kod, m1.nazwa AS msc, m2.nazwa AS msc_ur, wyksztalcenie.nazwa AS wyksztalcenie, 
uprawnienia.imie_nazwisko AS konsultant, zatrudnienie.id AS id_zatrudnienie, zatrudnienie.id_klient, 
zatrudnienie.id_oddzial, zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, zawod.nazwa AS stanowisko, 
(adres_biuro.nazwa::text || ', '::text) || msc_biura.nazwa::text AS biuro, 0 AS id_panstwo, zatrudnienie.id_wakat
   FROM dane_osobowe
   JOIN miejscowosc m1 ON dane_osobowe.id_miejscowosc = m1.id
   JOIN miejscowosc m2 ON dane_osobowe.id_miejscowosc_ur = m2.id
   JOIN wyksztalcenie ON dane_osobowe.id_wyksztalcenie = wyksztalcenie.id
   JOIN zatrudnienie ON dane_osobowe.id = zatrudnienie.id_osoba
   JOIN uprawnienia ON zatrudnienie.id_pracownik = uprawnienia.id
   JOIN msc_odjazdu ON zatrudnienie.id_msc_odjazd = msc_odjazdu.id
   JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
   JOIN adres_biuro ON oddzialy_klient.adres_biuro = adres_biuro.id
   JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
   JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
   JOIN zawod ON oddzialy_klient.stanowisko = zawod.id
  WHERE zatrudnienie.id_status = 5
  ORDER BY dane_osobowe.id;
  
CREATE OR REPLACE FUNCTION podajdaneumowa(osoba_id integer) RETURNS umowa
    AS $$DECLARE
        result umowa;
        id_p_pos_const integer;
        test record;
BEGIN
        id_p_pos_const := 2;
        select into result * from umowa where id = osoba_id;
        select into test klient.id_panstwo_pos, klient.id_panstwo_egz, klient.id, klient.nazwa || ', ' || klient.adres as adres_klient from klient where klient.id = result.id_klient;
        result.id_panstwo := test.id_panstwo_egz;
        
        IF test.id_panstwo_pos = id_p_pos_const THEN
                --podmiana adresu klienta dla posrednictwa z polski
                result.biuro := test.adres_klient;
        ELSE
                result.biuro := 'E&A Logistiek bv, ' || result.biuro;
        END IF;

        RETURN result;
END;
$$ LANGUAGE plpgsql;


---alter table zatrudnienie drop column ilosc_tygodni;