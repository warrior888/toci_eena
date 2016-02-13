-- View: ankieta_holandia

DROP VIEW ankieta_holandia;

CREATE OR REPLACE VIEW ankieta_holandia AS 
 SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id,
    imiona.nazwa AS imie,
    dane_osobowe.nazwisko,
    plec.nazwa AS plec,
    dane_osobowe.data_urodzenia,
    m_ur.nazwa AS msc_ur,
    miejscowosc.nazwa AS msc,
    dane_osobowe.ulica,
    dane_osobowe.kod,
    wyksztalcenie.nazwa AS wyksztalcenie,
    zawod.nazwa AS zawod,
    email.nazwa AS email,
    telefon.nazwa AS telefon,
    telefon_kom.nazwa AS tel_kom,
    dokumenty.pass_nr AS paszport,
    dokumenty.data_waznosci,
    dokumenty.nip AS sofi,
    bank.nazwa AS bank,
    bank.swift,
    dokumenty.nr_konta AS konto,
    dane_osobowe.nr_obuwia,
    (klient.nazwa::text || ', '::text) || oddzialy_klient.nazwa::text AS klient,
    zatrudnienie.data_wyjazdu,
    zatrudnienie.ilosc_tyg,
    msc_biura.nazwa AS biuro,
    msc_biura.id AS biuro_id,
    zatrudnienie.id_status
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

ALTER TABLE ankieta_holandia
  OWNER TO eena;
