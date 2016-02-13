alter table rozklad_jazdy add column active boolean default true;
alter table rozklad_jazdy add column przystanek text default '';

UPDATE kolumny_wyszukiwanie set id_typ = 3 where nazwa in ('telefon', 'telefon_inny');

UPDATE dane_osobowe set ilosc_tyg = null where ilosc_tyg = '';


--migracja danych dodatkowych

alter table dane_dodatkowe drop constraint "dane_dodatkowe_pkey";
alter table dane_dodatkowe add constraint "dane_dodatkowe_pkey" PRIMARY KEY (id_dane_dodatkowe_lista, wartosc, id_osoba);

alter table dane_dodatkowe alter column wartosc drop not null;

---insert into dane_dodatkowe (id_osoba, id_dane_dodatkowe_lista, wartosc) select id, 1, null from dane_osobowe where id not in (select id_osoba from dane_dodatkowe where id_dane_dodatkowe_lista = 1);

--skasowac/upowszechnic nie w soffi ? (2)
--skasowac/upowszechnic nie w pos prawo jazdy ? (4)
--skasowac//upowszechnic nie w zna jezyk ? (5)
--co z rozmowa kwalifikacyjna ?

drop view ankieta_holandia;

create or replace view ankieta_holandia as 
SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, plec.nazwa AS plec, 
dane_osobowe.data_urodzenia, m_ur.nazwa AS msc_ur, miejscowosc.nazwa AS msc, dane_osobowe.ulica, dane_osobowe.kod,
wyksztalcenie.nazwa as wyksztalcenie, zawod.nazwa as zawod, email.nazwa as email, 
telefon.nazwa AS telefon, telefon_kom.nazwa AS tel_kom, dokumenty.pass_nr AS paszport, dokumenty.data_waznosci, 
dokumenty.nip AS sofi, bank.nazwa AS bank, bank.swift, dokumenty.nr_konta AS konto, dane_osobowe.nr_obuwia, 
(klient.nazwa::text || ', '::text) || oddzialy_klient.nazwa::text AS klient, zatrudnienie.data_wyjazdu, 
zatrudnienie.ilosc_tyg, msc_biura.nazwa AS biuro, zatrudnienie.id_status
   FROM dane_osobowe
   JOIN imiona ON dane_osobowe.id_imie = imiona.id
   JOIN plec ON dane_osobowe.id_plec = plec.id
   join wyksztalcenie on dane_osobowe.id_wyksztalcenie = wyksztalcenie.id
   join zawod on dane_osobowe.id_zawod = zawod.id 
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

  ORDER BY dane_osobowe.id;
  
  drop view ankieta_nowy;
  
  create or replace view ankieta_nowy as 
  SELECT DISTINCT ON (dane_osobowe.id) dane_osobowe.id, imiona.nazwa AS imie, dane_osobowe.nazwisko, plec.nazwa AS plec, 
  dane_osobowe.data_urodzenia, m_ur.nazwa AS msc_ur, miejscowosc.nazwa AS msc, dane_osobowe.ulica, dane_osobowe.kod, 
  wyksztalcenie.nazwa as wyksztalcenie, zawod.nazwa as zawod, email.nazwa as email,  
  telefon.nazwa AS telefon, telefon_kom.nazwa AS tel_kom, dokumenty.pass_nr AS paszport, dokumenty.data_waznosci, 
  dokumenty.nip AS sofi, bank.nazwa AS bank, bank.swift, dokumenty.nr_konta AS konto, dane_osobowe.nr_obuwia, '----'::text AS klient, 
  dane_osobowe.data as data_wyjazdu, dane_osobowe.ilosc_tyg, '----'::text AS biuro, stat.id_status 
   FROM dane_osobowe
   JOIN imiona ON dane_osobowe.id_imie = imiona.id
   JOIN plec ON dane_osobowe.id_plec = plec.id
   join wyksztalcenie on dane_osobowe.id_wyksztalcenie = wyksztalcenie.id
   join zawod on dane_osobowe.id_zawod = zawod.id 
   JOIN stat ON dane_osobowe.id = stat.id
   JOIN status ON stat.id_status = status.id
   JOIN miejscowosc m_ur ON dane_osobowe.id_miejscowosc_ur = m_ur.id
   JOIN miejscowosc ON dane_osobowe.id_miejscowosc = miejscowosc.id
   LEFT JOIN dokumenty ON dokumenty.id = dane_osobowe.id
   LEFT JOIN bank ON bank.id = dokumenty.id_bank
   LEFT JOIN telefon ON telefon.id = dane_osobowe.id
   LEFT JOIN telefon_kom ON telefon_kom.id = dane_osobowe.id
   LEFT JOIN telefon_inny ON telefon_inny.id = dane_osobowe.id
   LEFT JOIN email ON email.id = dane_osobowe.id
  ORDER BY dane_osobowe.id;
  
  
  ---poprzedni pracodawca
  alter table poprzedni_pracodawca alter column id_branza drop not null;
  alter table poprzedni_pracodawca_ankieta alter column id_branza drop not null;
  
  
  set join_collapse_limit = 8;
  set from_collapse_limit = 8;
  
  
  explain
  select * from (
    (select pos_prawo_jazdy.id, pos_prawo_jazdy.id as osoba_id, prawo_jazdy.nazwa as prawo_jazdy from pos_prawo_jazdy join prawo_jazdy on pos_prawo_jazdy.id_prawka = prawo_jazdy.id where (lower(prawo_jazdy.nazwa) like lower('b')) order by id asc) 
    as a0, 
    (select stat.id, stat.id as osoba_id, status.nazwa as status from stat join status on stat.id_status = status.id where  not (lower(status.nazwa) like lower('wyj%') or lower(status.nazwa) like lower('nie%') or lower(status.nazwa) like lower('akt%')) order by id asc) 
    as a1  
    
    , 
    (select distinct kontakt.id as osoba_id, kontakt.id, data as ostatni_kontakt from kontakt where  not (data between '2011-07-01' and '2011-07-01') order by id asc) 
    as a2 
    
    , 
    (select telefon_kom.id, telefon_kom.id as osoba_id, nazwa as komorka from telefon_kom where (lower(nazwa) like lower('5%') or lower(nazwa) like lower('6%') or lower(nazwa) like lower('7%') or lower(nazwa) like lower('8%')) order by id asc) 
    as a3  
    
    , 
    (select znane_jezyki.id, znane_jezyki.id as osoba_id, jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where (lower(jezyki.nazwa) like lower('ang%')) and (lower(poziomy.nazwa) like lower('pod%')) order by id asc) 
    as a4  
    
    , 
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, null as wzrost from dane_osobowe left join dane_dodatkowe on dane_osobowe.id = dane_dodatkowe.id_osoba where dane_dodatkowe.id_dane_dodatkowe_lista = 1 and (wartosc between '163' and '200' or wartosc is null) order by id asc) 
    as a5 
    ,
     
    (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and (wartosc like 'nie') order by id asc) 
    as a6  
     
    , 
    (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as rozmowa from dane_dodatkowe where id_dane_dodatkowe_lista = 6 and (wartosc like 'tak') order by id asc) 
    as a7 
    
    , 
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, imiona.nazwa as imie, dane_osobowe.nazwisko, plec.nazwa as plec, dane_osobowe.data_urodzenia, miejscowosc.nazwa as miejscowosc, dane_osobowe.kod, dane_osobowe.data_zgloszenia, charakter.nazwa as charakter_pracy, dane_osobowe.data from dane_osobowe join plec on dane_osobowe.id_plec=plec.id join charakter on dane_osobowe.id_charakter=charakter.id join imiona on dane_osobowe.id_imie=imiona.id join miejscowosc on dane_osobowe.id_miejscowosc=miejscowosc.id where (lower(plec.nazwa) like lower('k%')) and (dane_osobowe.data_urodzenia between '1976-01-01' and '1989-12-31') and (lower(dane_osobowe.kod) like lower('32%') or lower(dane_osobowe.kod) like lower('33%') or lower(dane_osobowe.kod) like lower('34%') or lower(dane_osobowe.kod) like lower('4%') or lower(dane_osobowe.kod) like lower('5%') or lower(dane_osobowe.kod) like lower('63%') or lower(dane_osobowe.kod) like lower('67%') or lower(dane_osobowe.kod) like lower('68%') or lower(dane_osobowe.kod) like lower('98%')) and (lower(charakter.nazwa) like lower('sta%')) and (dane_osobowe.data between '2011-06-01' and '2011-07-08') order by id asc) 
     
    as a8 ) where a0.id = a1.id and a0.id = a2.id and a0.id = a3.id and a0.id = a4.id and a0.id = a5.id and a0.id = a6.id and a0.id = a7.id and a0.id = a8.id ;
  
  
  set geqo_effort = 9;
  
  
  
  
  
  
  
  
  
  
  
  
  
  explain
  select count(*) from (
    (select pos_prawo_jazdy.id, pos_prawo_jazdy.id as osoba_id, prawo_jazdy.nazwa as prawo_jazdy from pos_prawo_jazdy join prawo_jazdy on pos_prawo_jazdy.id_prawka = prawo_jazdy.id where (lower(prawo_jazdy.nazwa) like lower('b')) order by id asc) 
    as a0 
    
    join 
    (select stat.id, stat.id as osoba_id, status.nazwa as status from stat join status on stat.id_status = status.id where  not (lower(status.nazwa) like lower('wyj%') or lower(status.nazwa) like lower('nie%') or lower(status.nazwa) like lower('akt%')) order by id asc) 
    as a1 on a0.id = a1.id 
    
    join 
    (select distinct kontakt.id as osoba_id, kontakt.id, data as ostatni_kontakt from kontakt where  not (data between '2011-07-01' and '2011-07-01') order by id asc) 
    as a2 on a0.id = a2.id 
    
    join 
    (select telefon_kom.id, telefon_kom.id as osoba_id, nazwa as komorka from telefon_kom where (lower(nazwa) like lower('5%') or lower(nazwa) like lower('6%') or lower(nazwa) like lower('7%') or lower(nazwa) like lower('8%')) order by id asc) 
    as a3 on a0.id = a3.id 
    
    join 
    (select znane_jezyki.id, znane_jezyki.id as osoba_id, jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where (lower(jezyki.nazwa) like lower('ang%')) and (lower(poziomy.nazwa) like lower('pod%')) order by id asc) 
    as a4 on a0.id = a4.id 
    
    join 
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, null as wzrost from dane_osobowe left join dane_dodatkowe on dane_osobowe.id = dane_dodatkowe.id_osoba where dane_dodatkowe.id_dane_dodatkowe_lista = 1 and (wartosc between '163' and '200' or wartosc is null) order by id asc) 
    as a5 on a0.id = a5.id
    
    join 
    (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and (wartosc like 'nie') order by id asc) 
    as a6 on a0.id = a6.id 
     
    join 
    (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as rozmowa from dane_dodatkowe where id_dane_dodatkowe_lista = 6 and (wartosc like 'tak') order by id asc) 
    as a7 on a0.id = a7.id
    
    join 
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, imiona.nazwa as imie, dane_osobowe.nazwisko, plec.nazwa as plec, dane_osobowe.data_urodzenia, miejscowosc.nazwa as miejscowosc, dane_osobowe.kod, dane_osobowe.data_zgloszenia, charakter.nazwa as charakter_pracy, dane_osobowe.data from dane_osobowe join plec on dane_osobowe.id_plec=plec.id join charakter on dane_osobowe.id_charakter=charakter.id join imiona on dane_osobowe.id_imie=imiona.id join miejscowosc on dane_osobowe.id_miejscowosc=miejscowosc.id where (lower(plec.nazwa) like lower('k%')) and (dane_osobowe.data_urodzenia between '1976-01-01' and '1989-12-31') and (lower(dane_osobowe.kod) like lower('32%') or lower(dane_osobowe.kod) like lower('33%') or lower(dane_osobowe.kod) like lower('34%') or lower(dane_osobowe.kod) like lower('4%') or lower(dane_osobowe.kod) like lower('5%') or lower(dane_osobowe.kod) like lower('63%') or lower(dane_osobowe.kod) like lower('67%') or lower(dane_osobowe.kod) like lower('68%') or lower(dane_osobowe.kod) like lower('98%')) and (lower(charakter.nazwa) like lower('sta%')) and (dane_osobowe.data between '2011-06-01' and '2011-07-08') order by id asc) 
     
    as a8 on a0.id = a8.id
    ) ;
    
    explain
    select * from (
    (select pos_prawo_jazdy.id, pos_prawo_jazdy.id as osoba_id, prawo_jazdy.nazwa as prawo_jazdy from pos_prawo_jazdy join prawo_jazdy on pos_prawo_jazdy.id_prawka = prawo_jazdy.id where (lower(prawo_jazdy.nazwa) like lower('b%')) order by id asc) as a0 
    join 
    (select stat.id, stat.id as osoba_id, status.nazwa as status from stat join status on stat.id_status = status.id where  not (lower(status.nazwa) like lower('wyj%') or lower(status.nazwa) like lower('nie%') or lower(status.nazwa) like lower('akt%')) order by id asc) as a1 on a0.id = a1.id 
    join 
    (select distinct kontakt.id as osoba_id, kontakt.id, data as ostatni_kontakt from kontakt where  not (data between '2011-07-01' and '2011-07-01') order by id asc) as a2 on a1.id = a2.id 
    join 
    (select telefon_kom.id, telefon_kom.id as osoba_id, nazwa as komorka from telefon_kom where (lower(nazwa) like lower('5%') or lower(nazwa) like lower('6%') or lower(nazwa) like lower('7%') or lower(nazwa) like lower('8%')) order by id asc) as a3 on a2.id = a3.id join (select znane_jezyki.id, znane_jezyki.id as osoba_id, jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where (lower(jezyki.nazwa) like lower('ang%')) and (lower(poziomy.nazwa) like lower('pod%')) order by id asc) as a4 on a3.id = a4.id 
    join 
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, imiona.nazwa as imie, dane_osobowe.nazwisko, plec.nazwa as plec, dane_osobowe.data_urodzenia, miejscowosc.nazwa as miejscowosc, dane_osobowe.kod, uprawnienia.nazwa_uzytkownika as konsultant, dane_osobowe.data_zgloszenia, charakter.nazwa as charakter_pracy, dane_osobowe.data from dane_osobowe join plec on dane_osobowe.id_plec=plec.id join uprawnienia on dane_osobowe.id_konsultant=uprawnienia.id join charakter on dane_osobowe.id_charakter=charakter.id join imiona on dane_osobowe.id_imie=imiona.id join miejscowosc on dane_osobowe.id_miejscowosc=miejscowosc.id where (lower(plec.nazwa) like lower('K%')) and (dane_osobowe.data_urodzenia between '1976-01-01' and '1989-12-31') and (lower(dane_osobowe.kod) like lower('32%') or lower(dane_osobowe.kod) like lower('33%') or lower(dane_osobowe.kod) like lower('34%') or lower(dane_osobowe.kod) like lower('4%') or lower(dane_osobowe.kod) like lower('5%') or lower(dane_osobowe.kod) like lower('63%') or lower(dane_osobowe.kod) like lower('67%') or lower(dane_osobowe.kod) like lower('68%') or lower(dane_osobowe.kod) like lower('98%')) and (lower(uprawnienia.nazwa_uzytkownika) like lower('%')) and (lower(charakter.nazwa) like lower('sta%')) and (dane_osobowe.data between '2011-06-01' and '2011-07-08') order by id asc) as a5 on a4.id = a5.id 
    join     
    (select dane_osobowe.id, dane_osobowe.id as osoba_id, null as wzrost from dane_osobowe left join dane_dodatkowe on dane_osobowe.id = dane_dodatkowe.id_osoba where dane_dodatkowe.id_dane_dodatkowe_lista = 1 and (wartosc between '163' and '200' or wartosc is null) order by id asc) as a6 on a5.id = a6.id 
    join 
    (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and (wartosc like 'nie') order by id asc) as a7 on a6.id = a7.id join (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as rozmowa from dane_dodatkowe where id_dane_dodatkowe_lista = 6 and (wartosc like 'tak') order by id asc) as a8 on a7.id = a8.id);