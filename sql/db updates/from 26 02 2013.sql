delete from telefon_kom where character_length(nazwa) != 9;

drop index telefon_kom_fkey;

select nazwa from klient where id_panstwo_egz != id_panstwo_pos;