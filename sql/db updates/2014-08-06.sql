-- Table: miejsca_docelowe

-- DROP TABLE osoby_kontaktowe;

CREATE TABLE osoby_kontaktowe
(
  id serial NOT NULL,
  id_miejscowosc_biuro integer NOT NULL,
  osoba character varying(150) NOT NULL,
  CONSTRAINT osoby_kontaktowe_pkey PRIMARY KEY (id),
  CONSTRAINT osoby_kontaktowe_id_miejscowosc_biuro_fkey FOREIGN KEY (id_miejscowosc_biuro)
      REFERENCES miejscowosc_biuro (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE osoby_kontaktowe
  OWNER TO eena;