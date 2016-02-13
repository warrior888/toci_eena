CREATE TABLE strefa_odjazdu
(
  id serial NOT NULL,
  nazwa character varying(30) NOT NULL,
  CONSTRAINT strefa_odjazdu_pkey PRIMARY KEY (id)
);