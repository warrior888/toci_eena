CREATE TABLE strefy
(
  id serial NOT NULL,
  msc_odjazdu_id int NOT NULL,
  przewoznik_id int NOT NULL,
  strefa_id int NOT NULL,
  CONSTRAINT strefy_pkey PRIMARY KEY (id),
  CONSTRAINT "strefy.msc_odjazdu_id" FOREIGN KEY (msc_odjazdu_id) REFERENCES msc_odjazdu (id) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT "strefy.przewoznik_id" FOREIGN KEY (przewoznik_id) REFERENCES przewoznik (id) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT "strefy.strefa_id" FOREIGN KEY (strefa_id) REFERENCES strefa_odjazdu (id) ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE UNIQUE INDEX strefy_uniq ON strefy (msc_odjazdu_id, przewoznik_id);