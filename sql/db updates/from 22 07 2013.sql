--- panstwo, miasto, klient, nazwa agencji

alter table poprzedni_pracodawca add column panstwo varchar(100);
alter table poprzedni_pracodawca add column miasto varchar(100);
alter table poprzedni_pracodawca add column klient varchar(200);
alter table poprzedni_pracodawca add column agencja varchar(200);

alter table poprzedni_pracodawca add column data date not null default now();

alter table poprzedni_pracodawca_ankieta add column agencja varchar(200);


drop view poprzedni_pracodawca_agencja;

create or replace view poprzedni_pracodawca_agencja as 
	select imie, nazwisko, data_urodzenia, poprzedni_pracodawca.agencja, poprzedni_pracodawca.panstwo, poprzedni_pracodawca.data, 
	poprzedni_pracodawca.nazwa from
	poprzedni_pracodawca join dane_osobowe on poprzedni_pracodawca.id = dane_osobowe.id;