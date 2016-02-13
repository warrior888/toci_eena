CREATE OR REPLACE VIEW aktywny AS 
 SELECT grupa_zatrudnienie.id,
    grupa_zatrudnienie.imie,
    grupa_zatrudnienie.nazwisko,
    grupa_zatrudnienie.data_urodzenia,
    CASE WHEN dokumenty.nip is NULL THEN CAST('' as  varchar(9)) ELSE dokumenty.nip END,
    grupa_zatrudnienie.data_wyjazdu,
    grupa_zatrudnienie.biuro,
    grupa_zatrudnienie.klient,
    grupa_zatrudnienie.data_powrotu,
    grupa_zatrudnienie.id_panstwo_pos
   FROM grupa_zatrudnienie
   LEFT JOIN dokumenty ON grupa_zatrudnienie.id = dokumenty.id
  WHERE grupa_zatrudnienie.id_status = 1;

ALTER TABLE aktywny
  OWNER TO eena;
