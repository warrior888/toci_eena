UPDATE "telefon_inny" SET nazwa = '00' || nazwa 
WHERE 
substring(nazwa from 1 for 2) = '31' AND 
char_length(nazwa) >= 10
