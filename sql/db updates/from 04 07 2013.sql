drop table zadania_dnia_konsultant ;
drop view zadania_dnia_filtr ;


---zapis danych o konsultancie w kontekscie jego zadan dnia
create table zadania_dnia_konsultant (

	id_uprawnienia integer references uprawnienia(id) primary key,
	dane_zapytania text --- serializowana tablica parametrow dla dalowej metody, i formularza webowego
);

---widok do zadan dnia
---dodac komorke ?
drop view zadania_dnia_filtr ;

create or replace view zadania_dnia_filtr as 
select dane_osobowe.id, dane_osobowe.nazwisko, dane_osobowe.imie, zadania_dnia.data, zadania_dnia.problem, uprawnienia.imie_nazwisko, 
zadania_dnia.active, 
zadania_dnia.data_wpisu, zadania_dnia.id_konsultant, zadania_dnia.id_wiersz
from dane_osobowe 
join zadania_dnia on dane_osobowe.id = zadania_dnia.id
join uprawnienia on zadania_dnia.id_konsultant = uprawnienia.id;


create table audyt_log (
	id serial primary key,
	id_uprawnienia integer references uprawnienia(id) not null,
	data timestamp not null default now(),
	zapytanie text
);