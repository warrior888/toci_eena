
alter table uprawnienia add column wygasa date;



update uprawnienia set wygasa = '2010-09-25' where aktywny = true;
