alter table firma_filia add column email varchar(50);

update firma_filia set email = 'olesno@eena.pl' where id = 1;
update firma_filia set email = 'raciborz@eena.pl' where id = 2;
update firma_filia set email = 'opole@eena.pl' where id = 3;
update firma_filia set email = 'gliwice@eena.pl' where id = 4;
update firma_filia set email = 'rzeszow@eena.pl' where id = 5;