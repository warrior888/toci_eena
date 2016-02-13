CREATE OR REPLACE VIEW v_bilety AS 
 SELECT z.id_osoba AS id,
    ' ' || CAST(z.id_oddzial AS TEXT) || CAST(z.id_pracownik AS TEXT)|| CAST(z.id AS TEXT) || CAST(z.id_osoba AS TEXT) AS nr_rezerwacji,
    imiona.nazwa AS imie,
    dane_osobowe.nazwisko,
    dane_osobowe.data_urodzenia,
    z.data_wyjazdu,
    przewoznik.nazwa AS przewoznik,
    1 AS cena,
    forma_platnosci.nazwa AS forma_platnosci,
    u.imie_nazwisko AS pracownik,
    sr.nazwa AS stan_realizacji,
    z.data_realizacji,
    z.data_wpisu,
    z.id_oddzial,
    z.id_pracownik AS pracownikId,   
    z.id_osoba,
    z.id_ticket_state AS stateId,
    bilety.nazwa AS bilet,
    forma_platnosci.id AS paymentFormId,
    z.data_powrotu,
    msc_odjazdu.nazwa AS msc_odjazdu,
    msc_odjazdu.strefa_id,
    msc_powrotu.nazwa AS msc_powrotu,
    rozklad_jazdy_wyjazd.godzina AS wyjazd_godzina,
    rozklad_jazdy_wyjazd.przystanek AS wyjazd_przystanek,
    rozklad_jazdy_powrot.godzina_powrotu AS powrot_godzina,
    rozklad_jazdy_powrot.przystanek AS powrot_przystanek,
    miejsca_docelowe.nazwa AS miejsce_docelowe,
    msc_przyjazdu.nazwa AS miasto_docelowe,
    ( SELECT rozklad_jazdy.godzina
           FROM rozklad_jazdy
          WHERE rozklad_jazdy.id_przewoznik = rozklad_jazdy_wyjazd.id_przewoznik AND rozklad_jazdy.id_msc_odjazdu = miejsca_docelowe.msc_odjazdu_id AND rozklad_jazdy.dzien = rozklad_jazdy_wyjazd.dzien
         LIMIT 1) AS godzina_wyjazdu_powrot,
    ( SELECT rozklad_jazdy.godzina_powrotu
           FROM rozklad_jazdy
          WHERE rozklad_jazdy.id_przewoznik = rozklad_jazdy_wyjazd.id_przewoznik AND rozklad_jazdy.id_msc_odjazdu = miejsca_docelowe.msc_odjazdu_id AND rozklad_jazdy.dzien = rozklad_jazdy_wyjazd.dzien
         LIMIT 1) AS godzina_przyjazdu,
    dokumenty.pass_nr,
    dokumenty.data_waznosci,
    klient.nazwa AS klient,
    przewoznik.id AS id_przewoznik,
    przewoznik.id AS carrierId
    
   FROM zatrudnienie z
   JOIN dane_osobowe ON dane_osobowe.id = z.id_osoba
   JOIN imiona ON imiona.id = dane_osobowe.id_imie
   JOIN bilety ON bilety.id = z.id_bilet
   JOIN uprawnienia u ON u.id = z.id_pracownik
   LEFT JOIN forma_platnosci ON forma_platnosci.id = z.id_forma_platnosci
   LEFT JOIN stan_realizacji sr ON sr.id = z.id_ticket_state
   JOIN msc_odjazdu ON msc_odjazdu.id = z.id_msc_odjazd
   LEFT JOIN msc_odjazdu msc_powrotu ON msc_powrotu.id = z.id_msc_powrot
   JOIN rozklad_jazdy rozklad_jazdy_wyjazd ON rozklad_jazdy_wyjazd.id = z.id_rozklad_jazdy_wyjazd
   LEFT JOIN rozklad_jazdy rozklad_jazdy_powrot ON rozklad_jazdy_powrot.id = z.id_rozklad_jazdy_powrot
   LEFT JOIN miejsca_docelowe ON miejsca_docelowe.id = z.id_miejsca_docelowe
   LEFT JOIN msc_odjazdu msc_przyjazdu ON msc_przyjazdu.id = miejsca_docelowe.msc_odjazdu_id
   LEFT JOIN przewoznik ON przewoznik.id = rozklad_jazdy_wyjazd.id_przewoznik
   LEFT JOIN dokumenty ON dokumenty.id = z.id_osoba
   JOIN klient ON klient.id = z.id_klient;


ALTER TABLE v_bilety
  OWNER TO eena;

insert into widoki (nazwa, sql, nag) VALUES ('Rozliczenie biletow', 'select * from v_bilety', '''');

INSERT INTO widoki_edit(
            id_widoku, co, gdzie, nazwa)
VALUES (10, '%', 'paymentFormId' , 'Forma p³atno¶ci'),
(10, '%', 'stateId' , 'Stan Realizacji'),
(10, '', 'pracownikId' , 'Wystawca'),
(10, '2015-02-01', 'data_wyjazdu' , 'Data wyjazdu'),
(10, '2015-09-25', 'data_powrotu' , 'Data powrotu'),
(10, '%', 'carrierId' , 'Przewo¼nik');
