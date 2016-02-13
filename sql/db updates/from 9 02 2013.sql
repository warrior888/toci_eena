alter table zadania_dnia add column id_wiersz serial primary key;

alter table zadania_dnia alter column data_wpisu set default now();

alter table zadania_dnia alter column active set default true;

