--insert into tabela_wyszukiwanie (nazwa, ranga) values ('odebrany', 2);

insert into kolumny_wyszukiwanie (nazwa, id_typ, id_tabela_wyszukiwanie, kolejnosc, naglowek) values ('odebrany', 1, 19, 55, 'Odebrany jarograf');

alter table dane_internet add column miejscowosc_ur varchar(30);

CREATE OR REPLACE FUNCTION updateDIDenormalized() RETURNS trigger AS $$
declare
    --imie_id integer;
begin

    IF (NEW.id_imie is not null) THEN
        --we have an id, set name for it
        select into NEW.imie nazwa from imiona where id = NEW.id_imie;
    ELSIF (NEW.imie is not null) THEN
        --we do not have id, try to get it
        select into NEW.id_imie id from imiona where lower(nazwa) = lower(NEW.imie);
    END IF;
    
    IF (NEW.id_miejscowosc is not null) THEN
        --we have an id, set city for it
        select into NEW.miejscowosc nazwa from miejscowosc where id = NEW.id_miejscowosc;
    ELSIF (NEW.miejscowosc is not null) THEN
        --we do not have id, try to get it
        select into NEW.id_miejscowosc id from miejscowosc where lower(nazwa) = lower(NEW.miejscowosc);
    END IF;
    
    IF (NEW.id_miejscowosc_ur is not null) THEN
        --we have an id, set city for it
        select into NEW.miejscowosc_ur nazwa from miejscowosc where id = NEW.id_miejscowosc_ur;
    ELSIF (NEW.miejscowosc_ur is not null) THEN
        --we do not have id, try to get it
        select into NEW.id_miejscowosc_ur id from miejscowosc where lower(nazwa) = lower(NEW.miejscowosc_ur);
    END IF;
    
    IF (NEW.id_wyksztalcenie is not null) THEN
        --we have an id, set education for it
        select into NEW.wyksztalcenie nazwa from wyksztalcenie where id = NEW.id_wyksztalcenie;
    ELSIF (NEW.wyksztalcenie is not null) THEN
        --we do not have id, try to get it
        select into NEW.id_wyksztalcenie id from wyksztalcenie where lower(nazwa) = lower(NEW.wyksztalcenie);
    END IF;
    
    IF (NEW.id_zawod is not null) THEN
        --we have an id, set profession for it
        select into NEW.zawod nazwa from zawod where id = NEW.id_zawod;
    ELSIF (NEW.zawod is not null) THEN
        --we do not have id, try to get it
        select into NEW.id_zawod id from zawod where lower(nazwa) = lower(NEW.zawod);
    END IF;
    
    RETURN NEW;
end;
$$ LANGUAGE plpgsql;