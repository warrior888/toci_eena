



---help select ;)
select ddl.id, (select dd.wartosc from dane_dodatkowe dd where dd.id_dane_dodatkowe_lista = ddl.id and dd.id_osoba = 9400) as wartosc from dane_dodatkowe_lista ddl where ddl.id_typ = 1 order by ddl.id;



select * from (SELECT id_osoba, group_concat('|' || id_dane_dodatkowe_lista || ':' || wartosc || '|') as wartosc from dane_dodatkowe group by id_osoba) as d1 where d1.wartosc like '%4:tak%' and d1.wartosc like '%5:tak%' and d1.wartosc like '%|6:tak%' and not d1.wartosc like '%|2:%' and d1.wartosc like '%|10:tak%' ;

