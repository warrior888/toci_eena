ALTER TABLE zatrudnienie
   ADD COLUMN id_miejsca_docelowe integer;

ALTER TABLE zatrudnienie
  ADD CONSTRAINT "zatrudnienie.zatrudnienie_id_miejsca_docelowe" FOREIGN KEY (id_miejsca_docelowe) REFERENCES miejsca_docelowe (id)
   ON UPDATE NO ACTION ON DELETE NO ACTION;



ALTER TABLE zatrudnienie
   ADD COLUMN id_osoby_kontaktowe integer;

ALTER TABLE zatrudnienie
  ADD CONSTRAINT "zatrudnienie.zatrudnienie_id_osoby_kontaktowe" FOREIGN KEY (id_osoby_kontaktowe) REFERENCES osoby_kontaktowe (id)
   ON UPDATE NO ACTION ON DELETE NO ACTION; 