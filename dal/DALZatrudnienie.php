<?php

    require_once 'Model.php';
    
    class DALZatrudnienie extends Model {

        public function __construct () {
            
            parent::__construct();
        }
        
        public function set($data) {
            
            $escCallbacks = array (
                Model::COLUMN_ZTR_ID                   => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_OSOBA             => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_KLIENT            => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_ODDZIAL           => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_WAKAT             => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_STATUS            => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ILOSC_TYG            => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_DECYZJA           => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_MSC_ODJAZD        => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_MSC_POWROT        => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_BILET             => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_ID_PRACOWNIK         => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_ZTR_DATA_WYJAZDU         => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_ZTR_DATA_POWROTU         => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_ZTR_DATA_WPISU           => array($this->dal, Model::METHOD_ESCAPE_STRING),
                'id_miejsca_docelowe'                  => array($this->dal, Model::METHOD_ESCAPE_INT),
                'id_osoby_kontaktowe'                  => array($this->dal, Model::METHOD_ESCAPE_INT),
                'id_forma_platnosci'                   => array($this->dal, Model::METHOD_ESCAPE_INT),
                'id_ticket_state'                      => array($this->dal, Model::METHOD_ESCAPE_INT),
            );
            
            $idStatus = $data[Model::COLUMN_ZTR_ID_STATUS];
            $_data = $this->escapeParamsList($escCallbacks, $data);
            
            if($_data['id_ticket_state'] != 0) {
                $_data['data_realizacji'] = date("Y-m-d");
            }
            
            if ($idStatus == ID_STATUS_NIEODPOWIEDNI && !isset($data[Model::COLUMN_NPO_POWOD])) {
                
                $reasons = $this->get($_data[Model::COLUMN_ZTR_ID]);
                // should be exception; anyway do not update
                if (is_null($reasons))
                {
                    return false;
                }
            }
            
            $notRequired = array(Model::COLUMN_ZTR_ID_MSC_ODJAZD, Model::COLUMN_ZTR_ID_BILET, Model::COLUMN_ZTR_ID_MSC_POWROT, 
                Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD, Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT);
            
            //$_data = $this->removeNotRequiredEmpty($_data, $notRequired);
            
            $_id = isset($_data[Model::COLUMN_ZTR_ID]) ? $_data[Model::COLUMN_ZTR_ID] : null;
            if ($_id) {
                
                $query = 'update '.Model::TABLE_ZATRUDNIENIE.' set '.$this->createSetClause($_data).' where '.
                Model::COLUMN_ZTR_ID.' = '.$_id.';';
            } else {
                
                $_id = $_data[Model::COLUMN_ZTR_ID] = $this->getNextZatrudnienieId();
                $query = 'insert into '.Model::TABLE_ZATRUDNIENIE.' '.$this->createInsertClause($_data).';';
            }
            
            /*$query .= 'delete from '.Model::TABLE_ZATRUDNIENIE_ODJAZD.' where id_zatrudnienie = '.$_id.';';
            
            if (isset($data[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD])) {
                
                $_idRozkladJazdy = (int)$data[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY];
                $rozkladQuery = 'insert into '.Model::TABLE_ZATRUDNIENIE_ODJAZD.' (id_zatrudnienie, id_rozklad_jazdy) values ('.$_id.', '.$_idRozkladJazdy.');';
                
                $query .= $rozkladQuery;
            }*/
            
            // todo? update to stat ?

            if ($this->dal->pgQuery($query)) {
                
                if (isset($data[Model::COLUMN_NPO_POWOD]))
                {
                    $escCallbacks = array (
                        Model::COLUMN_ZTR_ID                   => array($this->dal, Model::METHOD_ESCAPE_INT),
                        Model::COLUMN_ZTR_ID_OSOBA             => array($this->dal, Model::METHOD_ESCAPE_INT),
                        Model::COLUMN_ZTR_ID_PRACOWNIK         => array($this->dal, Model::METHOD_ESCAPE_INT),
                        Model::COLUMN_NPO_POWOD                => array($this->dal, Model::METHOD_ESCAPE_STRING),
                    );
                    
                    $_npoData = $this->escapeParamsList($escCallbacks, $data[Model::COLUMN_NPO_POWOD]);
                    
                    $this->setUnsuitableReason($_npoData);
                }
                
                return $_id;
            }
                
            return 0;
        }
        
        public function get($id) {
            
            $id = (int)$id;
            
            if (!$id)
                return null;
                
            $selectColumns = array(
            
                Model::COLUMN_ZTR_ID,
                Model::COLUMN_ZTR_ID_OSOBA,
                Model::COLUMN_ZTR_ID_KLIENT,
                Model::COLUMN_ZTR_ID_ODDZIAL,
                Model::COLUMN_ZTR_ID_WAKAT,
                Model::COLUMN_ZTR_ID_STATUS,
                Model::COLUMN_ZTR_DATA_WYJAZDU,
                Model::COLUMN_ZTR_ILOSC_TYG,
                Model::COLUMN_ZTR_DATA_POWROTU,
                Model::COLUMN_ZTR_DATA_WPISU,
                Model::COLUMN_ZTR_ID_DECYZJA,
                Model::COLUMN_ZTR_ID_MSC_ODJAZD,
                Model::COLUMN_ZTR_ID_MSC_POWROT,
                Model::COLUMN_ZTR_ID_BILET,
                Model::COLUMN_ZTR_ID_PRACOWNIK,
                Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD,
                Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT,
                'id_miejsca_docelowe',
                'id_osoby_kontaktowe',
                'id_forma_platnosci',
                'id_ticket_state'
            );

            //select zatrudnienie.id,zatrudnienie.id_osoba,zatrudnienie.id_klient, zatrudnienie.id_oddzial,zatrudnienie.id_wakat,
            //zatrudnienie.id_status,zatrudnienie.data_wyjazdu, zatrudnienie.ilosc_tyg,zatrudnienie.data_powrotu,
            //zatrudnienie.data_wpisu,zatrudnienie.id_decyzja, zatrudnienie.id_msc_odjazd,zatrudnienie.id_bilet,
            //zatrudnienie.id_pracownik,uprawnienia.imie_nazwisko from zatrudnienie join uprawnienia on uprawnienia.id = zatrudnienie.id_pracownik where zatrudnienie.id =
            
            foreach ($selectColumns as $key => $value) {
                
                $selectColumns[$key] = Model::TABLE_ZATRUDNIENIE.'.'.$value;
            }
            
            $select = implode(', ', $selectColumns);
            
            $query = 'select '.$select.', '.Model::TABLE_UPRAWNIENIA.'.'.Model::COLUMN_UPR_IMIE_NAZWISKO
            .', t1.'.Model::COLUMN_RJA_ID_PRZEWOZNIK.' as '.Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD.',
            t2.'.Model::COLUMN_RJA_ID_PRZEWOZNIK.' as '.Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT.'
            from '
            .Model::TABLE_ZATRUDNIENIE.' join '.Model::TABLE_UPRAWNIENIA.' on '.
            Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID_PRACOWNIK.' = '.Model::TABLE_UPRAWNIENIA.'.'.Model::COLUMN_UPR_ID.'  
            left join '.Model::TABLE_ROZKLAD_JAZDY.' t1 on '.
            Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD.' = t1.'.Model::COLUMN_RJA_ID.
            ' left join '.Model::TABLE_ROZKLAD_JAZDY.' t2 on '.
            Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT.' = t2.'.Model::COLUMN_RJA_ID.
            ' where '.Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID.' = '.$id;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function delete ($id) {
            
            $_id = $this->dal->escapeInt($id);
            $query = 'delete from '.Model::TABLE_ZATRUDNIENIE.' where '.Model::COLUMN_ZTR_ID.' = '.$_id;
            
            return $result = $this->dal->pgQuery($query);
        }
        
        public function getByVacatIdPersonId ($personId, $vacatId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_vacatId = $this->dal->escapeInt($vacatId);
            
            $query = 'select * from '.Model::TABLE_ZATRUDNIENIE.' where '.Model::COLUMN_ZTR_ID_OSOBA.' = '.$_personId.' and '.
            Model::COLUMN_ZTR_ID_WAKAT.' = '.$_vacatId;
  
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getKandydatKontakt ($komorka, $email) {
            
            $_komorka = $this->dal->escapeString($komorka);
            $_email = $this->dal->escapeString($email);
            
            $query = 'select * from '.Model::VIEW_ZNAJDZ_KANDYDAT_KONTAKT.' where '.Model::COLUMN_ZKK_KOMORKA.' = \''.$_komorka.'\' 
            and '.Model::COLUMN_ZKK_EMAIL.' = \''.$_email.'\'';
  
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getEmployPretendents ($vacatId, $decisionId) {
            
            $_vacatId = $this->dal->escapeInt($vacatId);
            $_decisionId = $this->dal->escapeInt($decisionId);
            
            $query = 'select * from '.Model::VIEW_WAKAT_KANDYDACI.' where '.Model::COLUMN_WKA_ID_WAKAT.' = '.$_vacatId.' and '.
            Model::COLUMN_WKA_ID_DECYZJA.' = '.$_decisionId;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        protected function getNextZatrudnienieId () {
            
            $result = $this->dal->PobierzDane('select nextval(\''.Model::TABLE_ZATRUDNIENIE.'_id_seq\') as id;');
            $_id = $result[0]['id'];
            
            return $_id;
        }
        
        protected function setUnsuitableReason($data) {

            // data should come escaped from public set method
            // insert a row to nieodpowiedni_powod table
            
            $params = array(
                Model::COLUMN_NPO_ID_DANE_OSOBOWE        => $data[Model::COLUMN_ZTR_ID_OSOBA],
                Model::COLUMN_NPO_ID_UPRAWNIENIA         => $data[Model::COLUMN_ZTR_ID_PRACOWNIK],
                Model::COLUMN_NPO_ID_ZATRUDNIENIE        => $data[Model::COLUMN_ZTR_ID],
                Model::COLUMN_NPO_POWOD                  => $data[Model::COLUMN_NPO_POWOD],
            );
            
            $query = 'insert into '.Model::TABLE_NIEODPOWIEDNI_POWOD.$this->createInsertClause($params);
            
            return $this->dal->pgQuery($query);
        }
        
        public function getUnsuitableReasonByEmpId($idZatrudnienie) {
            
            $_idZatrudnienie = $this->dal->escapeInt($idZatrudnienie);
            
            $query = 'select * from '.Model::TABLE_NIEODPOWIEDNI_POWOD.' where '.Model::COLUMN_NPO_ID_ZATRUDNIENIE.' = '.$_idZatrudnienie;

            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getStatsCompanies($startDate, $endDate) {
            $query = "SELECT DISTINCT klient.nazwa || ', ' || (adres_biuro.nazwa::text || ', '::text) || msc_biura.nazwa::text || ', ' || panstwo.nazwa AS nazwa
                        FROM umowa_ewidencja
                        INNER JOIN zatrudnienie ON zatrudnienie.id = umowa_ewidencja.id_wakat 
                        INNER JOIN klient ON klient.id = zatrudnienie.id_klient
                        INNER JOIN panstwo ON klient.id_panstwo_egz = panstwo.id
                        INNER JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
                        INNER JOIN adres_biuro ON oddzialy_klient.adres_biuro = adres_biuro.id
                        INNER JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                        INNER JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
                        WHERE (umowa_ewidencja.data BETWEEN '$startDate' AND '$endDate')
                        ";

            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getStatsPersons($startDate, $endDate) {
            $query = "SELECT DISTINCT dane_osobowe.imie || ' ' || dane_osobowe.nazwisko || ', ' || dane_osobowe.ulica || ', ' || dane_osobowe.kod  || ' ' || miejscowosc.nazwa AS osoba,
                        klient.nazwa || ', ' || (adres_biuro.nazwa::text || ', '::text) || msc_biura.nazwa::text || ', ' || panstwo.nazwa AS podmiot,
                        data_wyjazdu, data_powrotu                        
                    FROM umowa_ewidencja
                    INNER JOIN zatrudnienie ON zatrudnienie.id = umowa_ewidencja.id_wakat 
                    INNER JOIN klient ON klient.id = zatrudnienie.id_klient
                    INNER JOIN panstwo ON klient.id_panstwo_egz = panstwo.id
                    INNER JOIN oddzialy_klient ON zatrudnienie.id_oddzial = oddzialy_klient.id
                    INNER JOIN adres_biuro ON oddzialy_klient.adres_biuro = adres_biuro.id
                    INNER JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                    INNER JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
                    INNER JOIN dane_osobowe ON dane_osobowe.id = zatrudnienie.id_osoba
                    INNER JOIN miejscowosc ON miejscowosc.id = dane_osobowe.id_miejscowosc
                    WHERE (umowa_ewidencja.data BETWEEN '$startDate' AND '$endDate') 
                    ORDER BY data_wyjazdu
                    ";
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getTicketDataFilter() {
            
        }
        
        public function setTicketDataFilter($params) {
            $_data[Model::COLUMN_ZDK_DANE_ZAPYTANIA] = $this->dal->escapeString(serialize($params[Model::COLUMN_ZDK_DANE_ZAPYTANIA]));
            
            $result = $this->getDailyTasksFilters($_idKonsultant);
            
            $isUpdate = !is_null($result);

            if ($isUpdate) 
            {
                $query = 'update '.Model::TABLE_ZADANIA_DNIA_KONSULTANT.' set '.$this->createSetClause($_data).' where '.
                Model::COLUMN_ZDK_ID_UPRAWNIENIA.' = '.$_idKonsultant;
            }
            else
            {
                $query = 'insert into '.Model::TABLE_ZADANIA_DNIA_KONSULTANT.$this->createInsertClause($_data);
            }

            return $this->dal->pgQuery($query);
        }


        public function getTicketData($id) {
            $id = (int)$id;
            
            $sql = "SELECT z.id,
                        imiona.nazwa AS imie,
                        dane_osobowe.nazwisko,
                        dane_osobowe.data_urodzenia,
                        z.id_oddzial,
                        z.id_pracownik,
                        z.id_osoba,
                        z.data_wpisu,
                        bilety.nazwa AS bilet,
                        bilety.id_przewoznik,
                        forma_platnosci.id AS forma_platnosci_id,
                        forma_platnosci.nazwa AS forma_platnosci,
                        z.data_wyjazdu,
                        z.data_powrotu,
                        msc_odjazdu.nazwa AS msc_odjazdu,
                        strefy.strefa_id,
                        msc_powrotu.nazwa AS msc_powrotu,
                        rozklad_jazdy_wyjazd.godzina AS wyjazd_godzina,
                        rozklad_jazdy_wyjazd.przystanek AS wyjazd_przystanek,
                        rozklad_jazdy_powrot.godzina_powrotu AS powrot_godzina,
                        rozklad_jazdy_powrot.przystanek AS powrot_przystanek,
                        miejsca_docelowe.nazwa AS miejsce_docelowe,
                        msc_przyjazdu.nazwa AS miasto_docelowe,

                        (SELECT godzina
                            FROM rozklad_jazdy 
                            WHERE id_przewoznik = rozklad_jazdy_wyjazd.id_przewoznik 
                                AND id_msc_odjazdu = miejsca_docelowe.msc_odjazdu_id AND dzien = rozklad_jazdy_wyjazd.dzien LIMIT 1) AS godzina_wyjazdu_powrot,

                                    (SELECT godzina_powrotu 
                            FROM rozklad_jazdy 
                            WHERE id_przewoznik = rozklad_jazdy_wyjazd.id_przewoznik 
                                AND id_msc_odjazdu = miejsca_docelowe.msc_odjazdu_id AND dzien = rozklad_jazdy_wyjazd.dzien LIMIT 1) AS godzina_przyjazdu,

                        dokumenty.pass_nr,
                        dokumenty.data_waznosci,
                        klient.nazwa AS klient,
                        przewoznik.nazwa AS przewoznik

                    FROM Zatrudnienie z 
                    
                    INNER JOIN dane_osobowe ON dane_osobowe.Id = z.id_osoba
                    INNER JOIN imiona ON imiona.id = dane_osobowe.id_imie
                    INNER JOIN bilety ON bilety.id = z.id_bilet
                    LEFT JOIN forma_platnosci ON forma_platnosci.id = z.id_forma_platnosci
                    INNER JOIN msc_odjazdu ON msc_odjazdu.id = z.id_msc_odjazd
                    LEFT JOIN msc_odjazdu AS msc_powrotu ON msc_powrotu.id = z.id_msc_powrot
                    INNER JOIN rozklad_jazdy AS rozklad_jazdy_wyjazd ON rozklad_jazdy_wyjazd.id = id_rozklad_jazdy_wyjazd
                    LEFT JOIN rozklad_jazdy AS rozklad_jazdy_powrot ON rozklad_jazdy_powrot.id = id_rozklad_jazdy_powrot
                        LEFT JOIN miejsca_docelowe ON miejsca_docelowe.id = id_miejsca_docelowe
                        LEFT JOIN msc_odjazdu AS msc_przyjazdu ON msc_przyjazdu.id = miejsca_docelowe.msc_odjazdu_id
                        LEFT JOIN przewoznik ON przewoznik.id = rozklad_jazdy_wyjazd.id_przewoznik

                        LEFT JOIN dokumenty ON dokumenty.id = z.id_osoba
                    INNER JOIN klient ON klient.id = z.id_klient
                    INNER JOIN strefy ON strefy.msc_odjazdu_id = msc_odjazdu.id AND strefy.przewoznik_id = przewoznik.id

                    WHERE z.Id = $id";
         
            $result = $this->dal->PobierzDane($sql, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
            
        }
    }