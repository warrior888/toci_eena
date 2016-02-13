
create table opis_prac (

	id_oddzialy_klient integer references oddzialy_klient(id) not null, 
	typ integer not null default 1,
    zrodlo integer,
	opis text,
    primary key(id_oddzialy_klient, typ)
);

-- alter table opis_prac add column zrodlo integer;

drop view umowa cascade;

create or replace view umowa as 
SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.id_plec, dane_osobowe.data_urodzenia, dane_osobowe.ulica, dane_osobowe.kod, m1.nazwa AS msc, 
m2.nazwa AS msc_ur, wyksztalcenie.nazwa AS wyksztalcenie, uprawnienia.imie_nazwisko AS konsultant, zatrudnienie.id AS id_zatrudnienie, zatrudnienie.id_klient, 
zatrudnienie.id_oddzial, zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg, zawod.nazwa AS stanowisko, 
(adres_biuro.nazwa::text || ', '::text) || msc_biura.nazwa::text AS biuro, 0 AS id_panstwo, zatrudnienie.id_wakat, opis_prac.opis
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
   LEFT JOIN opis_prac on oddzialy_klient.id = opis_prac.id_oddzialy_klient and typ = 2
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
        select into test klient.id_panstwo_pos, klient.id, klient.nazwa || ', ' || klient.adres as adres_klient from klient where klient.id = result.id_klient;
        result.id_panstwo := test.id_panstwo_pos;
        
        IF test.id_panstwo_pos = id_p_pos_const THEN
                --podmiana adresu klienta dla posrednictwa z polski
                result.biuro := test.adres_klient;
        ELSE
                result.biuro := 'E&A Logistiek bv, ' || result.biuro;
        END IF;

        RETURN result;
END;
$$ LANGUAGE plpgsql;