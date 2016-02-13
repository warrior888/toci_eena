-- Table: miejsca_docelowe

-- DROP TABLE miejsca_docelowe;

CREATE TABLE miejsca_docelowe
(
  id serial NOT NULL,
  id_miejscowosc_biuro integer NOT NULL,
  nazwa character varying(150) NOT NULL,
  CONSTRAINT miejsca_docelowe_pkey PRIMARY KEY (id),
  CONSTRAINT miejsca_docelowe_id_miejscowosc_biuro_fkey FOREIGN KEY (id_miejscowosc_biuro)
      REFERENCES miejscowosc_biuro (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE miejsca_docelowe
  OWNER TO eena;