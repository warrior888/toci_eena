alter table semantyka alter column ustalenia type text;

drop view raport_dzienny;

alter table reklamacje alter column problem type text;

alter table reklamacje alter column odpowiedz type text;

create view raport_dzienny as 
 SELECT od.nazwisko, imiona.nazwa AS imie, od.data_urodzenia, reklamacje.data, reklamacje.problem, uprawnienia.imie_nazwisko, reklamacje.id_msc_biura AS id_biuro
   FROM dane_osobowe od
   JOIN imiona ON imiona.id = od.id_imie
   JOIN reklamacje ON reklamacje.id = od.id
   JOIN uprawnienia ON reklamacje.id_konsultant = uprawnienia.id
  WHERE reklamacje.odpowiedz IS NULL
  ORDER BY od.nazwisko;
