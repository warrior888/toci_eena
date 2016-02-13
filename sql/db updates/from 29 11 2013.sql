drop view poprzedni_pracodawca_agencja;

create or replace view poprzedni_pracodawca_agencja as 
 SELECT dane_osobowe.id, dane_osobowe.plec, dane_osobowe.imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia, poprzedni_pracodawca.agencja, poprzedni_pracodawca.panstwo, 
poprzedni_pracodawca.data, poprzedni_pracodawca.nazwa
   FROM poprzedni_pracodawca
   JOIN dane_osobowe ON poprzedni_pracodawca.id = dane_osobowe.id;