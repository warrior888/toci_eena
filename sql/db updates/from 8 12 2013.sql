insert into decyzja (nazwa) values ('Aplikuj¹cy');

drop view znajdz_kandydat_kontakt;

create or replace view znajdz_kandydat_kontakt as
select telefon_kom.id, telefon_kom.nazwa as komorka, email.nazwa as email 
from telefon_kom join email on telefon_kom.id = email.id;