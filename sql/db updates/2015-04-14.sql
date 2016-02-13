ALTER TABLE rozklad_jazdy ADD COLUMN godzina_powrotu character varying(5) NULL ;

ALTER TABLE msc_odjazdu ADD COLUMN strefa_id integer NOT NULL DEFAULT 1;
ALTER TABLE msc_odjazdu ADD CONSTRAINT msc_odjazdu_id_strefa_fkey FOREIGN KEY (strefa_id)
	REFERENCES strefa_odjazdu (id) MATCH SIMPLE
	ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE msc_odjazdu ADD COLUMN panstwo_id integer NOT NULL DEFAULT 2;
ALTER TABLE msc_odjazdu ADD CONSTRAINT msc_odjazdu_id_panstwo_fkey FOREIGN KEY (panstwo_id)
	REFERENCES panstwo (id) MATCH SIMPLE
	ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE miejsca_docelowe ADD COLUMN msc_odjazdu_id integer NULL;
ALTER TABLE miejsca_docelowe ADD CONSTRAINT miejsca_docelowe_id_msc_odjazdu_fkey FOREIGN KEY (msc_odjazdu_id)
	REFERENCES msc_odjazdu (id) MATCH SIMPLE
	ON UPDATE NO ACTION ON DELETE NO ACTION;