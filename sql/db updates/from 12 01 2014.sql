drop view wakat_kandydaci;

create or replace view wakat_kandydaci as
SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, plec.nazwa as plec, 
            dane_osobowe.data_urodzenia, dokumenty.pass_nr, dokumenty.nip, bank.nazwa AS bank, 
            uprawnienia.imie_nazwisko, status.nazwa AS status, decyzja.nazwa as decyzja, 
            zatrudnienie.ilosc_tyg, wakat.id as id_wakat, zatrudnienie.id_decyzja, zatrudnienie.id_oddzial
            FROM dane_osobowe
            JOIN plec ON dane_osobowe.id_plec = plec.id
            LEFT JOIN dokumenty ON dane_osobowe.id = dokumenty.id
            JOIN zatrudnienie ON zatrudnienie.id_osoba = dane_osobowe.id
            JOIN wakat on wakat.id = zatrudnienie.id_wakat
            JOIN uprawnienia ON zatrudnienie.id_pracownik = uprawnienia.id
            JOIN status ON zatrudnienie.id_status = status.id
            JOIN decyzja ON zatrudnienie.id_decyzja = decyzja.id
            LEFT JOIN bank ON dokumenty.id_bank = bank.id;