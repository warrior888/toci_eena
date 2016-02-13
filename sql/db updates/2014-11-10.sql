CREATE TABLE raport_aktywny_pl
(
  id serial NOT NULL,
  nazwa character varying(100) NOT NULL,
  CONSTRAINT raport_aktywny_pl_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE raport_aktywny_pl
  OWNER TO eena;


INSERT INTO raport_aktywny_pl(
            id, nazwa)
    VALUES (1, 'skoscielny@eena.pl')
    , (2, 'mczupala@eena.pl')
    , (3, 'skadziela@eena.nl');