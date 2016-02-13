

INSERT INTO strefa_odjazdu (nazwa) VALUES ('I strefa');
INSERT INTO strefa_odjazdu (nazwa) VALUES ('II strefa');

CREATE TABLE forma_platnosci
(
  id serial NOT NULL,
  nazwa character varying(30) NOT NULL,
  CONSTRAINT forma_platnosci_pkey PRIMARY KEY (id)
);

INSERT INTO forma_platnosci (nazwa) VALUES ('gotówka');
INSERT INTO forma_platnosci (nazwa) VALUES ('przelew');
INSERT INTO forma_platnosci (nazwa) VALUES ('voucher');
INSERT INTO forma_platnosci (nazwa) VALUES ('koordynatorski');
INSERT INTO forma_platnosci (nazwa) VALUES ('za³o¿enie jednostronne');

CREATE TABLE stan_realizacji
(
  id serial NOT NULL,
  nazwa character varying(30) NOT NULL,
  CONSTRAINT stan_realizacji_pkey PRIMARY KEY (id)
);

INSERT INTO stan_realizacji (nazwa) VALUES ('wp³acono');
INSERT INTO stan_realizacji (nazwa) VALUES ('rezygnacja');
INSERT INTO stan_realizacji (nazwa) VALUES ('anulowano');
INSERT INTO stan_realizacji (nazwa) VALUES ('faktura');
INSERT INTO stan_realizacji (nazwa) VALUES ('za³atwione');
INSERT INTO stan_realizacji (nazwa) VALUES ('bezp³atny przejazd');


ALTER TABLE zatrudnienie ADD COLUMN id_forma_platnosci integer NULL;

ALTER TABLE zatrudnienie ADD CONSTRAINT "zatrudnienie.zatrudnienie_id_forma_platnosci" 
   FOREIGN KEY (id_forma_platnosci) REFERENCES forma_platnosci (id)
   ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE zatrudnienie ADD COLUMN id_ticket_state integer NULL;

ALTER TABLE zatrudnienie ADD CONSTRAINT "zatrudnienie.zatrudnienie_id_ticket_state" 
   FOREIGN KEY (id_forma_platnosci) REFERENCES stan_realizacji (id)
   ON UPDATE NO ACTION ON DELETE NO ACTION;


ALTER TABLE zatrudnienie ADD COLUMN data_realizacji date NULL;