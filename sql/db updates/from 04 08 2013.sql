drop table raport_agencja_pp;

create table raport_agencja_pp (

	id serial primary key,
	nazwa varchar(100) not null
);

insert into raport_agencja_pp (nazwa) values ('gdeelen@eena.nl');
insert into raport_agencja_pp (nazwa) values ('mhooijschuur@eena.nl');
insert into raport_agencja_pp (nazwa) values ('edrontmann@eena.nl');
insert into raport_agencja_pp (nazwa) values ('rlieffers@eena.nl');
insert into raport_agencja_pp (nazwa) values ('warriorr@poczta.fm');


insert into zrodlo (nazwa, widoczne) values ('Adwords', false);