
create table zrodla_danych_zdalne (
                
                id serial primary key,
                zrodlo text, --zrodlo pochodzenia, moze wystepowac wielokrotnie, ale wraz z kolumna pole musi byc unikatowe
                pole text, --typ przechowywanej danej
                wartosc text --aka blob, any arbitrary value (object serilaization unadviced ;) )
            );
        
alter table zrodla_danych_zdalne add constraint zrodla_danych_zdalne_zrodlo_pole_key unique(zrodlo, pole);


create or replace view osoba_internet as 
    SELECT d_o.id, d_o.id_imie, d_o.imie, d_o.nazwisko, d_o.id_plec, plec.nazwa AS plec, d_o.data_urodzenia, d_o.id_miejscowosc_ur, d_o.id_miejscowosc, d_o.miejscowosc, d_o.ulica, d_o.kod, d_o.telefon, d_o.komorka, d_o.email, d_o.id_wyksztalcenie, d_o.wyksztalcenie, d_o.id_zawod, d_o.zawod, d_o.data_zgloszenia, d_o.id_charakter, charakter.nazwa AS charakter, d_o.data, d_o.ilosc_tyg, d_o.id_ankieta, d_o.id_zrodlo, zrodlo.nazwa AS zrodlo, d_o.source
   FROM dane_internet d_o
   left JOIN plec ON plec.id = d_o.id_plec
   left JOIN charakter ON charakter.id = d_o.id_charakter
   JOIN zrodlo ON zrodlo.id = d_o.id_zrodlo;
            
            
create table metadane_osobowe (
    id_osoba integer unique references dane_osobowe(id) on update cascade on delete cascade,
    dane text not null
);


create table metadane_internetowe (
    id_osoba integer unique references dane_internet(id) on update cascade on delete cascade,
    dane text not null
);