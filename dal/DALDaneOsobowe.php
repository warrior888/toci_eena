<?php

    require_once 'Model.php';
    
    class DALDaneOsobowe extends Model {

        public function __construct () {
            
            parent::__construct();
        }
        
        public function get($personId) {
            
            $_personId = (int)$personId;

            $query = 'select * from '.Model::TABLE_DANE_OSOBOWE.' WHERE id = '.$_personId;
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            $dataRow = array_shift($result);
            
            return $this->formatDataOutput($dataRow, $rowsCount);
        }
        
        public function getEditData ($personId) {
            
            $_personId = (int)$personId;

            $query = 'select * from '.self::VIEW_EDYCJA_OSOBY.' WHERE id = '.$_personId;
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            $dataRow = array_shift($result);
            
            /**
            * @desc Internet data view has different names for the same columns as system data, one has to be replaced
            * System data have worse names for cols, therefore these are replaced
            */

            if (isset($dataRow['msc_zam'])) {
                
                $dataRow['miejscowosc'] = $dataRow['msc_zam'];
                unset($dataRow['msc_zam']);
            }
            
            if (isset($dataRow['msc_ur'])) {
                
                $dataRow['miejscowosc_ur'] = $dataRow['msc_ur'];
                unset($dataRow['msc_ur']);
            }
            
            return $this->formatDataOutput($dataRow, $rowsCount);
        }
        
        public function getSmsHistory ($personId) {
            
            $_personId = (int)$personId;

            $query = 'SELECT uprawnienia.imie_nazwisko AS konsultant,
                        wysylka_sms.tresc,
                        wysylka_sms.telefon,
                        wysylka_sms.data,
                        wysylka_sms.status
                       FROM wysylka_sms
                       JOIN uprawnienia ON wysylka_sms.id_konsultant = uprawnienia.id
                       JOIN dane_osobowe ON wysylka_sms.id_dane_osobowe = dane_osobowe.id
                       JOIN imiona ON dane_osobowe.id_imie = imiona.id
                       WHERE id_dane_osobowe = ' . $_personId . ' ORDER BY data DESC';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function setPerson ($personData) {
            
            
            $_personId = null;
            if (isset($personData[Model::COLUMN_DOS_ID])) {
                
                $_personId = $this->dal->escapeInt($personData[Model::COLUMN_DOS_ID]);
                unset($personData[Model::COLUMN_DOS_ID]);
            } else {
                
                $doubles = $this->getPersonDouble($personData[Model::COLUMN_DOS_IMIE], $personData[Model::COLUMN_DOS_NAZWISKO], $personData[Model::COLUMN_DOS_DATA_URODZENIA]);
                if ($doubles)
                    throw new DBConflictDataException($doubles[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DOS_ID], 
                        'Duplicate data found in db in setPerson for '.$personData[Model::COLUMN_DOS_IMIE].', 
                        '.$personData[Model::COLUMN_DOS_NAZWISKO].', '.$personData[Model::COLUMN_DOS_DATA_URODZENIA]);
            }
            //format query iterating
            
            $escCallbacks = array (
                Model::COLUMN_DOS_ID_IMIE             => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_IMIE                => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_NAZWISKO            => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ID_PLEC             => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_PLEC                => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_DATA_URODZENIA      => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR   => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_ID_MIEJSCOWOSC      => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_MIEJSCOWOSC         => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ULICA               => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_KOD                 => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ID_WYKSZTALCENIE    => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_ID_ZAWOD            => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_ID_KONSULTANT       => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_DATA_ZGLOSZENIA     => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ID_CHARAKTER        => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_DATA                => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_DOS_ILOSC_TYG           => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_ID_ANKIETA          => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_ID_ZRODLO           => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_DOS_NR_OBUWIA           => array($this->dal, Model::METHOD_ESCAPE_INT),                
            );
            
            $_personData = $this->escapeParamsList($escCallbacks, $personData);
            
            //nothing to insert or update, success
            if (!sizeof($_personData))
                return $_personId;
            
            if ($_personId > 0) {
                //parse update
                $query = 'update '.Model::TABLE_DANE_OSOBOWE.' set ';
                
                $setClause = array();
                foreach ($_personData as $column => $value) {
                    
                    if (is_int($value)) {
                        
                        $setClause[] = $column.'='.$value;
                    } else {
                        
                        $setClause[] = $column.'=\''.$value.'\'';
                    }
                }
                
                $query .= implode(',', $setClause);
                $query .= ' where '.Model::COLUMN_DOS_ID.' = '.$_personId;
            } else {
                
                $query = 'insert into '.Model::TABLE_DANE_OSOBOWE.' ';
                
                $_personId = $this->getNextPersonId();
                
                // TODO insert protected method usage here
                $setCols = array(Model::COLUMN_DOS_ID);
                $setValues = array($_personId);
                foreach ($_personData as $column => $value) {
                    
                    $setCols[] = $column;
                    if (is_int($value)) {
                        
                        $setValues[] = $value;
                    } else {
                        
                        $setValues[] = '\''.$value.'\'';
                    }
                }
                
                $query .= '('.implode(',', $setCols).') values ('.implode(',', $setValues).')';
            }
            
            $this->dal->pgQuery($query);
            
            return $_personId;
        }
        
        /**
        * @desc Delete a person (with all dependant data !)
        */
        public function deletePerson ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'delete from '.Model::TABLE_DANE_OSOBOWE.' where '.Model::COLUMN_DOS_ID.' = '.$_personId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Seek for the same person with surname name and birthdate
        */
        public function getPersonDouble ($name, $surname, $birthdate) {
            
            $_name = $this->dal->escapeString($name);
            $_surname = $this->dal->escapeString($surname);
            $_birthdate = $this->dal->escapeString($birthdate);
            
            $query = 'select '.Model::COLUMN_DOS_ID.' from '.Model::TABLE_DANE_OSOBOWE.' where '.Model::COLUMN_DOS_DATA_URODZENIA.' =  \''.$_birthdate.'\'
                and lower('.Model::COLUMN_DOS_NAZWISKO.') = lower(\''.$_surname.'\')
                and lower('.Model::COLUMN_DOS_IMIE.') = lower(\''.$_name.'\');';
                
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        //WHY is status secluded anyway ??
        public function setStatus ($personId, $statusId) {
            
             $_personId = $this->dal->escapeInt($personId);
             $_statusId = $this->dal->escapeInt($statusId);
            
            //not excluding to sp, as this will probably be merged to main table
            $select = 'select * from '.Model::TABLE_STAT.' where '.Model::COLUMN_STT_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($select, $rowsStatus);
            
            if ($rowsStatus < 1) {
                
                //insert
                $query = 'insert into '.Model::TABLE_STAT.' ('.Model::COLUMN_STT_ID.', '.Model::COLUMN_STT_ID_STATUS.') values ('.$_personId.', '.$_statusId.');';
            } else {
                
                //update
                $query = 'update '.Model::TABLE_STAT.' set '.Model::COLUMN_STT_ID_STATUS.' = '.$_statusId.' where '.Model::COLUMN_STT_ID.' = '.$_personId;
            }
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc 
        */
        public function setContact ($personId, $consultantId, $date) {
            
            //allowing date param is quite nonsense, this will be hidden anyway
            //insert new record each time to history, refresh contact rekord
            $_personId = $this->dal->escapeInt($personId);
            $_consultantId = $this->dal->escapeInt($consultantId);
            $_date = $this->dal->escapeString($date);
            
            $select = 'select * from '.Model::TABLE_KONTAKT.' where '.Model::COLUMN_KON_ID.' = '.$_personId;
            $result = $this->dal->PobierzDane($select, $rowsContact);
            
            if ($rowsContact < 1) {
                
                //insert
                $contactQuery = 'insert into '.Model::TABLE_KONTAKT.' ('.Model::COLUMN_KON_ID.', '.Model::COLUMN_KON_ID_KONSULTANT.', '.Model::COLUMN_KON_DATA.') 
                values ('.$_personId.', '.$_consultantId.', \''.$_date.'\');';
            } else {
                
                //update
                $contactQuery = 'update '.Model::TABLE_KONTAKT.' set '.Model::COLUMN_KON_ID_KONSULTANT.' = '.$_consultantId.', '.Model::COLUMN_KON_DATA.' = \''.$_date.'\'
                where '.Model::COLUMN_KON_ID.' = '.$_personId;
            }
            
            $contactHistoryQuery = 'insert into '.Model::TABLE_KONTAKT_HISTORIA.' ('.Model::COLUMN_KON_ID.', '.Model::COLUMN_KON_ID_KONSULTANT.') 
            values ('.$_personId.', '.$_consultantId.');';
            
            $contactResult = $this->dal->pgQuery($contactQuery);
            $historyResult = $this->dal->pgQuery($contactHistoryQuery);
            
            return ($contactResult && $historyResult);
        }
        
        public function getEmployerInfo ($personId) {
            
            $_personId = (int)$personId;
            
            $query = 'select '.Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID.' as rekord, '.Model::COLUMN_ZTR_ID_KLIENT.', 
            '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_ID_PANSTWO_POS.', '.Model::COLUMN_ZTR_ID_ODDZIAL.' as oddzial, '.Model::COLUMN_ZTR_ID_WAKAT.' as wakat 
            from '.Model::TABLE_ZATRUDNIENIE.' join '.Model::TABLE_KLIENT.' on '.Model::TABLE_ZATRUDNIENIE.'.'.Model::COLUMN_ZTR_ID_KLIENT.' = '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_ID.' 
            where '.Model::COLUMN_ZTR_ID_OSOBA.' = '.$_personId.' 
            and '.Model::COLUMN_ZTR_ID_STATUS.' = '.Model::STATUS_ID_WYJEZDZAJACY.' and '.Model::COLUMN_ZTR_DATA_WYJAZDU.' >= \''.$this->dzis.'\' order by '.Model::COLUMN_ZTR_DATA_WYJAZDU.' limit 1;';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
            
            return array_shift($result);
        }
        
        /**
        * @desc Get the name and surname of a person.
        * @param int personId
        */
        public function getNameSurname ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::COLUMN_DOS_IMIE.','.Model::COLUMN_DOS_NAZWISKO.' from '.
            Model::TABLE_DANE_OSOBOWE.' where '.Model::COLUMN_DOS_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Region of contact data (telefon, telefon_kom, email tables
        */
        
        /**
        * @desc get list of phones for person
        * @return array dataOutput or null
        */
        public function getPhones ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::COLUMN_TEL_NAZWA.', '.Model::COLUMN_TEL_ID_WIERSZ.' from '.Model::TABLE_TELEFON.' where '.Model::COLUMN_TEL_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set phone. insert or update
        * @param int personId
        * @param int phone
        * @param int rowId
        * @return bool success
        */
        public function setPhone ($personId, $phone, $id = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_phone = $this->dal->escapeInt($phone);
            $_id = $this->dal->escapeInt($id);
            
            if ($_id) {
                //update
                $query = 'update '.Model::TABLE_TELEFON.' set '.Model::COLUMN_TEL_NAZWA.' = '.$_phone.' where '.Model::COLUMN_TEL_ID.' = '.$_personId.' and '.Model::COLUMN_TEL_ID_WIERSZ.' = '.$_id;
                return $this->dal->pgQuery($query);
            }
            
            $query = 'insert into '.Model::TABLE_TELEFON.' ('.Model::COLUMN_TEL_ID.', '.Model::COLUMN_TEL_NAZWA.') values ('.$_personId.', '.$_phone.');';
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc delete person phone
        */
        public function deletePhone ($personId, $id) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_id = $this->dal->escapeInt($id);
            
            $query = 'delete from '.Model::TABLE_TELEFON.' where '.Model::COLUMN_TEL_ID.' = '.$_personId.' and '.Model::COLUMN_TEL_ID_WIERSZ.' = '.$_id;
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc get a person cell phone. By design and sms communication enforcement, only one cell is possible
        */
        public function getCellPhone ($personId) {
            
            $_personId = (int)$personId;
            
            $query = 'select '.Model::COLUMN_TEK_NAZWA.', '.Model::COLUMN_TEK_ID_WIERSZ.' from '.Model::TABLE_TELEFON_KOM.' where '.Model::COLUMN_TEK_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc set a person cell phone (for replace old cell is provided)
        */
        public function setCell ($personId, $phone, $oldPhone = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_phone = $this->dal->escapeInt($phone);
            
            if (is_null($oldPhone)) {

                $selectQuery = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_TELEFON_KOM.' where '.Model::COLUMN_DICT_ID.' = '.$_personId;
                
                $exPhoes = $this->dal->PobierzDane($selectQuery, $existingPhonesCount);
                    
                if ($existingPhonesCount > 0) {
                    
                    $oldPhone = $exPhoes[0][Model::COLUMN_DICT_NAZWA];
                }
            }
            
            $_oldPhone = $this->dal->escapeInt($oldPhone);
            
            if ($_oldPhone > 0 && $oldPhone !== null) {
                //update
                $query = 'update '.Model::TABLE_TELEFON_KOM.' set '.Model::COLUMN_TEK_NAZWA.' = \''.$_phone.'\' where '.Model::COLUMN_TEK_ID.' = '.$_personId;
                
                return $this->dal->pgQuery($query);
            }
            
            $query = 'insert into '.Model::TABLE_TELEFON_KOM.' ('.Model::COLUMN_TEK_ID.', '.Model::COLUMN_TEK_NAZWA.') values ('.$_personId.', \''.$_phone.'\');';
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc delete person cell
        */
        public function deleteCell ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'delete from '.Model::TABLE_TELEFON_KOM.' where '.Model::COLUMN_TEK_ID.' = '.$_personId;
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc get list of extra phones for person
        * @return array dataOutput or null
        */
        public function getExtraPhones ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::COLUMN_TEI_NAZWA.', '.Model::COLUMN_TEI_ID_WIERSZ.' from '.Model::TABLE_TELEFON_INNY.' where '.Model::COLUMN_TEI_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
            
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set extra phone. insert or update
        * @param int personId
        * @param int phone
        * @param int rowId
        * @return bool success
        */
        public function setExtraPhone ($personId, $phone, $id = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_phone = $this->dal->escapeString($phone);
            $_id = $this->dal->escapeInt($id);
            
            if ($_id > 0 && $id !== null) {
                //update
                $query = 'update '.Model::TABLE_TELEFON_INNY.' set '.Model::COLUMN_TEI_NAZWA.' = \''.$_phone.'\' where '.Model::COLUMN_TEI_ID.' = '.$_personId.' and '.Model::COLUMN_TEI_ID_WIERSZ.' = '.$_id;
                return $this->dal->pgQuery($query);
            }
            
            $query = 'insert into '.Model::TABLE_TELEFON_INNY.' ('.Model::COLUMN_TEI_ID.', '.Model::COLUMN_TEI_NAZWA.') values ('.$_personId.', \''.$_phone.'\');';
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc delete person extra phone
        */
        public function deleteExtraPhone ($personId, $id) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_id = $this->dal->escapeInt($id);
            
            $query = 'delete from '.Model::TABLE_TELEFON_INNY.' where '.Model::COLUMN_TEI_ID.' = '.$_personId.' and '.Model::COLUMN_TEI_ID_WIERSZ.' = '.$_id;
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc By design (send operation reasons) only one email is set
        */
        public function getEmail ($personId) {
            
            $_personId = (int)$personId;
            
            $query = 'select '.Model::COLUMN_EMA_NAZWA.', '.Model::COLUMN_EMA_ID_WIERSZ.' from '.Model::TABLE_EMAIL.' where '.Model::COLUMN_EMA_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc set a person email (for replace old cell is provided)
        */
        public function setEmail ($personId, $email, $oldEmail = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_email = $this->dal->escapeString($email);
            $_oldEmail = $this->dal->escapeString($oldEmail);
            
            if ($oldEmail !== null) {
                //update
                $query = 'update '.Model::TABLE_EMAIL.' set '.Model::COLUMN_EMA_NAZWA.' = \''.$_email.'\' where '.Model::COLUMN_EMA_ID.' = '.$_personId.' and '.Model::COLUMN_EMA_NAZWA.' = \''.$_oldEmail.'\'';
                return $this->dal->pgQuery($query);
            }
            
            $query = 'insert into '.Model::TABLE_EMAIL.' ('.Model::COLUMN_EMA_ID.', '.Model::COLUMN_EMA_NAZWA.') values ('.$_personId.', \''.$_email.'\');';
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc delete person email
        */
        public function deleteEmail ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'delete from '.Model::TABLE_EMAIL.' where '.Model::COLUMN_EMA_ID.' = '.$_personId;
            return $this->dal->pgQuery($query);
        }
        
        public function getSkillsList ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::TABLE_UMIEJETNOSCI_OSOB.'.'.Model::COLUMN_UMO_ID_WIERSZ.', 
            '.Model::TABLE_UMIEJETNOSCI_OSOB.'.'.Model::COLUMN_UMO_ID_UMIEJETNOSC.', 
            '.Model::TABLE_UMIEJETNOSC.'.'.Model::COLUMN_DICT_NAZWA.'  
            from '.Model::TABLE_UMIEJETNOSCI_OSOB.' join '.Model::TABLE_UMIEJETNOSC.' on 
            '.Model::TABLE_UMIEJETNOSC.'.'.Model::COLUMN_DICT_ID.' = '.Model::TABLE_UMIEJETNOSCI_OSOB.'.'.Model::COLUMN_UMO_ID_UMIEJETNOSC.' 
            where '.Model::TABLE_UMIEJETNOSCI_OSOB.'.'.Model::COLUMN_UMO_ID.' = '.$_personId.' order by '.Model::TABLE_UMIEJETNOSC.'.'.Model::COLUMN_DICT_NAZWA.' asc';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set person skills. Method verifies the skills set uniqueness, there is not necessary to verify it earlier. 'Avoid duplicates fail' insert strategy used
        */
        public function setSkillsList ($personId, $skillsList) {
            
            if (!is_array($skillsList) || !sizeof($skillsList))
                return false;
                
            $_personId = $this->dal->escapeInt($personId);
            $valuesClause = array();
            
            foreach ($skillsList as $skillId) {
                
                $_skillId = $this->dal->escapeInt($skillId);
                $valuesClause[$_skillId] = '('.$_personId.', '.$_skillId.')';
            }
            
            $query = 'insert into '.Model::TABLE_UMIEJETNOSCI_OSOB.' ('.Model::COLUMN_UMO_ID.', '.Model::COLUMN_UMO_ID_UMIEJETNOSC.')
            select f1.id, f1.id_umiejetnosc from 
            (
                values 
                '.implode(',', $valuesClause).'
            ) as f1 (id, id_umiejetnosc) left join '.Model::TABLE_UMIEJETNOSCI_OSOB.' f2 using ('.Model::COLUMN_UMO_ID.', '.Model::COLUMN_UMO_ID_UMIEJETNOSC.') 
            where f2.id is null;';
            
            return $this->dal->pgQuery($query);
        }
        
        public function deleteSkill ($personId, $skillId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_skillId = $this->dal->escapeInt($skillId);
            
            $query = 'delete from '.Model::TABLE_UMIEJETNOSCI_OSOB.' where '.Model::COLUMN_UMO_ID_WIERSZ.' = '.$_skillId.' and '.
            Model::COLUMN_UMO_ID.' = '.$_personId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Set person skills. Method verifies the skills set uniqueness, there is not necessary to verify it earlier. 'Avoid duplicates fail' insert strategy used
        * @param int personId
        * @param array licensesList - simple array of license ids
        */
        public function setDrivingLicenseList ($personId, $licensesList) {
            
            if (!is_array($licensesList) || !sizeof($licensesList))
                return false;
                
            $_personId = $this->dal->escapeInt($personId);
            $valuesClause = array();
            
            foreach ($licensesList as $licenseId) {
                
                $_licenseId = $this->dal->escapeInt($licenseId);
                $valuesClause[$_licenseId] = '('.$_personId.', '.$_licenseId.')';
            }
            
            $query = 'insert into '.Model::TABLE_POS_PRAWO_JAZDY.' ('.Model::COLUMN_PPJ_ID.', '.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.')
            select f1.id, f1.id_prawka from 
            (
                values 
                '.implode(',', $valuesClause).'
            ) as f1 (id, id_prawka) left join '.Model::TABLE_POS_PRAWO_JAZDY.' f2 using ('.Model::COLUMN_PPJ_ID.', '.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.') 
            where f2.id is null;';
            
            return $this->dal->pgQuery($query);
        }
        
        public function getDrivingLicenses ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_NAZWA.', '.Model::TABLE_POS_PRAWO_JAZDY.'.'.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.', 
            '.Model::TABLE_POS_PRAWO_JAZDY.'.'.Model::COLUMN_PPJ_ID_WIERSZ.' 
            from '.Model::TABLE_PRAWO_JAZDY.' join '.Model::TABLE_POS_PRAWO_JAZDY.' 
            on '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_ID.' = '.Model::TABLE_POS_PRAWO_JAZDY.'.'.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.' 
            where '.Model::TABLE_POS_PRAWO_JAZDY.'.'.Model::COLUMN_PPJ_ID.' = '.$_personId.' 
            order by '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_NAZWA.' asc;';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function deleteDrivingLicense ($personId, $rowId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_rowId = $this->dal->escapeInt($rowId);
            
            $query = 'delete from '.Model::TABLE_POS_PRAWO_JAZDY.' where '.Model::COLUMN_PPJ_ID_WIERSZ.' = '.$_rowId.' and '.
            Model::COLUMN_PPJ_ID.' = '.$_personId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Get the list of languages declared by the person together with confirmations
        * @param int personId
        */
        public function getLanguages ($personId) {
            
            //consider migration of confirmed langs data to a main table (when the information is above 50 %, currently 16%)
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select f1.'.Model::COLUMN_ZNJ_ID_JEZYK.', f1.'.Model::COLUMN_ZNJ_ID_POZIOM.', f1.'.Model::COLUMN_ZNJ_ID_ZNANY_JEZYK.', 
            t1.'.Model::COLUMN_DICT_NAZWA.' as '.Model::COLUMN_JEZ_JEZYK.', t2.'.Model::COLUMN_DICT_NAZWA.' as '.Model::COLUMN_JEZ_POZIOM.', 
            f2.'.Model::COLUMN_ZJE_DATA.', f3.'.Model::COLUMN_UPR_IMIE_NAZWISKO.'
            from '.Model::TABLE_ZNANE_JEZYKI.' f1 join '.Model::TABLE_JEZYKI.' t1 on f1.'.Model::COLUMN_ZNJ_ID_JEZYK.' = t1.'.Model::COLUMN_DICT_ID.' 
            join '.Model::TABLE_POZIOMY.' t2 on f1.'.Model::COLUMN_ZNJ_ID_POZIOM.' = t2.'.Model::COLUMN_DICT_ID.'
            left join '.Model::TABLE_ZATWIERDZONE_JEZYKI.' f2 on f1.'.Model::COLUMN_ZNJ_ID_ZNANY_JEZYK.' = f2.'.Model::COLUMN_ZJE_ID_ZNANY_JEZYK.'
            left join '.Model::TABLE_UPRAWNIENIA.' f3 on f2.'.Model::COLUMN_ZJE_ID_KONSULTANT.' = f3.'.Model::COLUMN_UPR_ID.'
            where f1.'.Model::COLUMN_ZNJ_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set languages to the db for a given list
        * @param int personId
        * @param array laguages collection
        */
        public function setLanguages ($personId, $languages) {
            
            if (!is_array($languages) || !sizeof($languages))
                throw new DBInvalidDataException('Set languages operation without any language');
                
            $_personId = $this->dal->escapeInt($personId);
            
            $_languageIds = array();
            $_levelIds = array();
            $_confirmerIds = array();
            
            foreach ($languages as $language) {
                
                $_languageIds[] = $this->dal->escapeInt($language[Model::COLUMN_ZNJ_ID_JEZYK]);
                $_levelIds[] = $this->dal->escapeInt($language[Model::COLUMN_ZNJ_ID_POZIOM]);
                $_confirmerIds[] = $this->dal->escapeInt(isset($language[Model::COLUMN_ZJE_ID_KONSULTANT]) ? $language[Model::COLUMN_ZJE_ID_KONSULTANT] : 0);
            }
            
            $query = 'select setLanguages('.$_personId.', ARRAY['.implode(',', $_languageIds).'], ARRAY['.implode(',', $_levelIds).'], 
            ARRAY['.implode(',', $_confirmerIds).']);';
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Delete known language entry for id
        * @param int langId
        */
        public function deleteLanguage ($langId) {
            
            $_langId = $this->dal->escapeInt($langId);
            $query = 'delete from '.Model::TABLE_ZNANE_JEZYKI.' where '.Model::COLUMN_ZNJ_ID_ZNANY_JEZYK.' = '.$_langId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc method sets a confirmed language information - who, when confirmed the declared knowld=edge of which language
        * @param array Lang ids list - uniqueness-verified inside
        * @param int userId
        */
        public function setLangConfirmedList ($langIdList, $userId) {
            
            if (!is_array($langIdList) || !sizeof($langIdList))
                return false;
                
            $_userId = $this->dal->escapeInt($userId);
            $valuesClause = array();
            
            foreach ($langIdList as $langId) {
                
                $_langId = $this->dal->escapeInt($langId);
                $valuesClause[$_langId] = '('.$_langId.', '.$_userId.', \''.$this->dzis.'\')';
            }
            
            $query = 'insert into '.Model::TABLE_ZATWIERDZONE_JEZYKI.' ('.Model::COLUMN_ZJE_ID_ZNANY_JEZYK.', '.
            Model::COLUMN_ZJE_ID_KONSULTANT.', '.Model::COLUMN_ZJE_DATA.')
            select f1.id_znany_jezyk, f1.id_konsultant, f1.data::date from 
            (
                values 
                '.implode(',', $valuesClause).'
            ) as f1 (id_znany_jezyk, id_konsultant, data) left join '.Model::TABLE_ZATWIERDZONE_JEZYKI.' f2 using ('.Model::COLUMN_ZJE_ID_ZNANY_JEZYK.') 
            where f2.id is null;';

            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Delete lang confirmed entries for a list of langs
        * @param array langIds
        */
        public function deleteLangConfirmedList ($langIds) {
            
            if (!is_array($langIds) || !sizeof($langIds))
                return false;
            
            $_langIds = array();
            foreach ($langIds as $langId) {
                
                $_langId = $this->dal->escapeInt($langId);
                $_langIds[$_langId] = $_langId;
            }
            $query = 'delete from '.Model::TABLE_ZATWIERDZONE_JEZYKI.' where '.Model::COLUMN_ZJE_ID_ZNANY_JEZYK.' in ('.implode(',', $_langIds).');';
            
            return $this->dal->pgQuery($query);
        }
        
        /// poprzedni_pracodawca - operations
        /**
        * @desc Get list of former employers
        * @param int personId
        */
        public function getFormerEmployers ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            //f1.'.Model::COLUMN_PPR_PANSTWO.', f1.'.Model::COLUMN_PPR_MIASTO.', f1.'.Model::COLUMN_PPR_KLIENT.',
            $query = 'select f1.'.Model::COLUMN_PPR_ID.', f1.'.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.', 
            f1.'.Model::COLUMN_PPR_NAZWA.', f1.'.Model::COLUMN_PPR_AGENCJA.', f1.'.Model::COLUMN_PPR_ID_WIERSZ.',  
            f1.'.Model::COLUMN_PPR_ID_ODDZIALY_KLIENT.', t1.'.Model::COLUMN_DICT_NAZWA.' as '.Model::COLUMN_GRU_GRUPA_ZAWODOWA.'
            from '.Model::TABLE_POPRZEDNI_PRACODAWCA.' f1 
            join '.Model::TABLE_ZAWOD.' t1 on f1.'.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.' = t1.'.Model::COLUMN_DICT_ID.' 
            where f1.'.Model::COLUMN_PPR_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set former employer of a person.
        * @param string former employer
        * @param int occupation id
        * @param oprional int current row id for update
        */
        public function setFormerEmployer ($personId, $formerEmployer, $country, $city, $firmName, $agencyName, $occupationId, $currentId = null) {
            
            //bll additional bool in bll
            $_personId = $this->dal->escapeInt($personId);
            $_occupationId = $this->dal->escapeInt($occupationId);
            $_formerEmployer = $this->dal->escapeString($formerEmployer);
            $_country = $this->dal->escapeString($country);
            $_city = $this->dal->escapeString($city);
            $_firmName = $this->dal->escapeString($firmName);
            $_agencyName = $this->dal->escapeString($agencyName);
            
            if ($currentId === null) {
                $query = 'insert into '.Model::TABLE_POPRZEDNI_PRACODAWCA.' (
                '.Model::COLUMN_PPR_ID.', '.Model::COLUMN_PPR_NAZWA.', '.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.', 
                '.Model::COLUMN_PPR_PANSTWO.', '.Model::COLUMN_PPR_MIASTO.', '.Model::COLUMN_PPR_KLIENT.', 
                '.Model::COLUMN_PPR_AGENCJA.') 
                values ('.$_personId.', \''.$_formerEmployer.'\', '.$_occupationId.', \''.$_country.'\', \''.$_city.'\', 
                \''.$_firmName.'\', \''.$_agencyName.'\');';
            } else {
                $_currentId = $this->dal->escapeInt($currentId);
                $query = 'update '.Model::TABLE_POPRZEDNI_PRACODAWCA.' set 
                '.Model::COLUMN_PPR_NAZWA.' = \''.$_formerEmployer.'\', 
                '.Model::COLUMN_PPR_PANSTWO.' = \''.$_country.'\', 
                '.Model::COLUMN_PPR_MIASTO.' = \''.$_city.'\', 
                '.Model::COLUMN_PPR_KLIENT.' = \''.$_firmName.'\', 
                '.Model::COLUMN_PPR_AGENCJA.' = \''.$_agencyName.'\', 
                '.Model::COLUMN_PPR_DATA.' = \''.$this->dzis.'\', 
                '.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.' = '.$_occupationId.' 
                where '.Model::COLUMN_PPR_ID_WIERSZ.' = '.$_currentId.' and '.Model::COLUMN_PPR_ID.' = '.$_personId.';';
            }
            
            return $this->dal->pgQuery($query);
        }
        
        public function deleteFormerEmployer ($personId, $rowId) {
            
            $_rowId = $this->dal->escapeInt($rowId);
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'delete from '.Model::TABLE_POPRZEDNI_PRACODAWCA.' where '.Model::COLUMN_PPR_ID_WIERSZ.' = '.$_rowId.' and '.
            Model::COLUMN_PPR_ID.' = '.$_personId.';';
            return $this->dal->pgQuery($query);
        }
        
        public function getFormerEmployments($dateFrom, $dateTo) {
            
            $_dateFrom = $this->dal->escapeString($dateFrom);
            $_dateTo = $this->dal->escapeString($dateTo);
            
            $query = 'select * from '.Model::VIEW_POPRZEDNI_PRACODAWCA_AGENCJA.' 
            	where '.Model::COLUMN_PPR_DATA.' between \''.$_dateFrom.'\' and \''.$_dateTo.'\' 
            	and '.Model::COLUMN_PPR_PANSTWO.' = \'Holandia\'';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function setPersonFromCandidate ($candidateId, $userId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_userId = $this->dal->escapeInt($userId);
            
            $_personId = $this->getNextPersonId();
            
            $query = 'insert into '.Model::TABLE_DANE_OSOBOWE.' ('.Model::COLUMN_DOS_ID.', '.Model::COLUMN_DOS_ID_IMIE.', '.Model::COLUMN_DOS_NAZWISKO.', '.Model::COLUMN_DOS_ID_PLEC.', 
            '.Model::COLUMN_DOS_DATA_URODZENIA.', '.Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR.', '.Model::COLUMN_DOS_ID_MIEJSCOWOSC.', '.Model::COLUMN_DOS_ULICA.', '.Model::COLUMN_DOS_KOD.', 
            '.Model::COLUMN_DOS_ID_WYKSZTALCENIE.', '.Model::COLUMN_DOS_ID_ZAWOD.', '.Model::COLUMN_DOS_ID_KONSULTANT.', '.Model::COLUMN_DOS_DATA_ZGLOSZENIA.', '.Model::COLUMN_DOS_ID_CHARAKTER.', 
            '.Model::COLUMN_DOS_DATA.', '.Model::COLUMN_DOS_ILOSC_TYG.', '.Model::COLUMN_DOS_ID_ANKIETA.', '.Model::COLUMN_DOS_ID_ZRODLO.') 
            select '.$_personId.', '.Model::COLUMN_DIN_ID_IMIE.', '.Model::COLUMN_DIN_NAZWISKO.', '.Model::COLUMN_DIN_ID_PLEC.', '.Model::COLUMN_DIN_DATA_URODZENIA.', '.Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR.', 
            '.Model::COLUMN_DIN_ID_MIEJSCOWOSC.', '.Model::COLUMN_DIN_ULICA.', '.Model::COLUMN_DIN_KOD.', '.Model::COLUMN_DIN_ID_WYKSZTALCENIE.', '.Model::COLUMN_DIN_ID_ZAWOD.', '.$_userId.', 
            '.Model::COLUMN_DIN_DATA_ZGLOSZENIA.', '.Model::COLUMN_DIN_ID_CHARAKTER.', '.Model::COLUMN_DIN_DATA.', '.Model::COLUMN_DIN_ILOSC_TYG.', '.Model::COLUMN_DIN_ID_ANKIETA.', 
            '.Model::COLUMN_DIN_ID_ZRODLO.' from '.Model::TABLE_DANE_INTERNET.' where '.Model::COLUMN_DIN_ID.' = '.$_candidateId;
            
            $result = $this->dal->pgQuery($query);
            
            return $_personId;
        }
        
        public function setLanguagesFromCandidate ($personId, $candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_personId = $this->dal->escapeInt($personId);
            
            // TODO join to get a diff data - specific case of remote data source input compare
            $query = 'insert into '.Model::TABLE_ZNANE_JEZYKI.' ('.Model::COLUMN_ZNJ_ID.', '.Model::COLUMN_ZNJ_ID_JEZYK.', '.Model::COLUMN_ZNJ_ID_POZIOM.') 
            select '.$_personId.', '.Model::COLUMN_JIN_ID_JEZYK.', '.Model::COLUMN_JIN_ID_POZIOM.' from '.Model::TABLE_JEZYKI_INTERNET.' where '.Model::COLUMN_JIN_ID.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        public function setDrivingLicenseFromCandidate ($personId, $candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_personId = $this->dal->escapeInt($personId);
            
            // TODO join to get a diff data - specific case of remote data source input compare
            $query = 'insert into '.Model::TABLE_POS_PRAWO_JAZDY.' ('.Model::COLUMN_PPJ_ID.', '.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.') 
            select '.$_personId.', '.Model::COLUMN_PJI_ID_PRAWO_JAZDY.' from '.Model::TABLE_PRAWO_JAZDY_INTERNET.' where '.Model::COLUMN_PJI_ID.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        public function setSkillsFromCandidate ($personId, $candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'insert into '.Model::TABLE_UMIEJETNOSCI_OSOB.' ('.Model::COLUMN_UMO_ID.', '.Model::COLUMN_UMO_ID_UMIEJETNOSC.') 
            select '.$_personId.', '.Model::COLUMN_UOI_ID_UMIEJETNOSC.' from '.Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.' where '.Model::COLUMN_UOI_ID.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        public function setFormerEmploymentFromCandidate ($personId, $candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'insert into '.Model::TABLE_POPRZEDNI_PRACODAWCA.' ('.Model::COLUMN_PPR_ID.', '.Model::COLUMN_PPR_NAZWA.', '.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPR_AGENCJA.') 
            select '.$_personId.', '.Model::COLUMN_PPA_NAZWA.', '.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPA_AGENCJA.' 
            from '.Model::TABLE_POPRZEDNI_PRAC_ANKIETA.' where '.Model::COLUMN_PPA_ID.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        public function setAdditionalDataFromCandidate ($personId, $candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'insert into '.Model::TABLE_DANE_DODATKOWE.' ('.Model::COLUMN_DDO_ID_OSOBA.', '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.', '.Model::COLUMN_DDO_WARTOSC.') 
            select '.$_personId.', '.Model::COLUMN_DDA_ID_DANE_DODATKOWE_LISTA.', '.Model::COLUMN_DDA_WARTOSC.' 
            from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' where '.Model::COLUMN_DDA_ID_OSOBA.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Switch employment data for chosen rows or add new employment(s) - either array and int or 2 ints
        * @param mixed (int,array) newEmploymentId
        * @param int currentId - either system id or person id
        * @throws DBException
        * @return bool result
        */
        public function setFormerEmployerFromCandidate ($newEmploymentId, $currentId) {
            
            if (is_array($newEmploymentId) && is_numeric($currentId)) {
                //add new employments
                $_personId = $this->dal->escapeInt($currentId);
                $_newEmploymentIds = array();
                foreach ($newEmploymentId as $employmentId) {
                    
                     $_employmentId = $this->dal->escapeInt($employmentId);
                     if ($_employmentId)
                        $_newEmploymentIds[] = $_employmentId;
                }
                
                if (sizeof($_newEmploymentIds) && $_personId) {
                    $query = 'insert into '.Model::TABLE_POPRZEDNI_PRACODAWCA.' ('.Model::COLUMN_PPR_ID.', 
                    '.Model::COLUMN_PPR_NAZWA.', '.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPR_AGENCJA.') 
                    select '.$_personId.', '.Model::COLUMN_PPA_NAZWA.', '.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPA_AGENCJA.'
                    from '.Model::TABLE_POPRZEDNI_PRAC_ANKIETA.' where '.Model::COLUMN_PPA_ID_WIERSZ.' in ('.implode(',', $_newEmploymentIds).');';
                    
                    return $this->dal->pgQuery($query);
                } else {
                    throw new DBInvalidDataException('Set former employers with improper array and int '.var_export($_newEmploymentIds, true).', '.$_personId);
                }
            }
            if (is_numeric($newEmploymentId) && is_numeric($currentId)) {
                //switch 2 employments
                $_newEmploymentId = $this->dal->escapeInt($newEmploymentId);
                $_currentEmploymentId = $this->dal->escapeInt($currentId);
                
                if ($_newEmploymentId && $_currentEmploymentId) {
                    
                    $query = 'update '.Model::TABLE_POPRZEDNI_PRACODAWCA.' set '.Model::COLUMN_PPR_NAZWA.' = f1.'.Model::COLUMN_PPA_NAZWA.', 
                    '.Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA.' = f1.'.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPR_AGENCJA.' = f1.'.Model::COLUMN_PPA_AGENCJA.' 
                    from '.Model::TABLE_POPRZEDNI_PRAC_ANKIETA.' f1 
                    where '.Model::TABLE_POPRZEDNI_PRACODAWCA.'.'.Model::COLUMN_PPR_ID_WIERSZ.' = '.$_currentEmploymentId.'
                    and f1.'.Model::COLUMN_PPA_ID_WIERSZ.' = '.$_newEmploymentId;
                    
                    return $this->dal->pgQuery($query);
                } else {
                    throw new DBInvalidDataException('Set former employers with improper integers '.$_newEmploymentId.', '.$_currentEmploymentId);
                }
            }
            
            throw new DBInvalidDataException('Set former employers with improper data (expected either 1 array or 2 integers)');
        }
        
        //TODO remove
        /*public function setAdditionalInfoFromCandidate ($personId, $candidateInfoId, $personInfoId = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_candidateInfoId = $this->dal->escapeInt($candidateInfoId);
            
            if (!is_null($personInfoId)) {
                
                $_personInfoId = $this->dal->escapeInt($personInfoId);
                //update
                $query = 'update '.Model::TABLE_DANE_DODATKOWE.' 
                set '.Model::COLUMN_DDO_WARTOSC.' = '.Model::TABLE_DANE_DODATKOWE_ANKIETA.'.'.Model::COLUMN_DDO_WARTOSC.' 
                from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' 
                where '.Model::TABLE_DANE_DODATKOWE_ANKIETA.'.'.Model::COLUMN_DDA_ID.' = '.$_candidateInfoId.
                ' and '.Model::TABLE_DANE_DODATKOWE.'.'.Model::COLUMN_DDO_ID.' = '.$_personInfoId;
            } else {
                
                //insert
                $query = 'insert into '.Model::TABLE_DANE_DODATKOWE.' 
                ('.Model::COLUMN_DDO_ID_OSOBA.', '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.', '.Model::COLUMN_DDO_WARTOSC.') 
                select '.$_personId.', '.Model::COLUMN_DDA_ID_DANE_DODATKOWE_LISTA.', '.Model::COLUMN_DDA_WARTOSC.' 
                from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' where '.Model::COLUMN_DDA_ID.' = '.$_candidateInfoId;
            }
            
            return $this->dal->pgQuery($query);
        }*/
        
        /**
        * @desc run update on dane osobowe with the data from dane internet
        * @throws DBException, DBQueryErrorException
        * @return bool success, null on no data
        */
        public function updatePersonCandidateData ($personId, $candidateId, $colsList) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            $colsSet = array();
            foreach ($colsList as $colName) {
                
                $colsSet[] = $colName.' = '.Model::TABLE_DANE_INTERNET.'.'.$colName;
            }
            
            $query = 'update '.Model::TABLE_DANE_OSOBOWE.' set '.implode(',', $colsSet).' from '.Model::TABLE_DANE_INTERNET.' where '.
            Model::TABLE_DANE_OSOBOWE.'.'.Model::COLUMN_DOS_ID.' = '.$_personId.' and '.Model::TABLE_DANE_INTERNET.'.'.Model::COLUMN_DIN_ID.' = '.$_candidateId;
            
            $result = $this->dal->pgQuery($query);
            $affRows = pg_affected_rows($result);
            if ($affRows > 1)
                LogManager::log(LOG_WARNING, '['.__CLASS__.'] Query affected '.$affRows.' in updatePersonCandidateData : '.$query);
            
            return (bool)$affRows;
        }
        
        public function getScannerDocuments($personId, $scannerDocId = null)
        {
            $_personId = $this->dal->escapeInt($personId);
            $_scannerDocId = $this->dal->escapeInt($scannerDocId);
            
            $query = 'select * from '.Model::TABLE_DOKUMENTY_SKAN.' where '.Model::COLUMN_DSK_ID_DANE_OSOBOWE.' = '.$_personId;
            
            if ($_scannerDocId > 0)
            {
                $query .= ' and '.Model::COLUMN_DSK_ID_LISTA_DOKUMENTY_SKAN.' = '.$scannerDocId;
            }

            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        protected function getNextPersonId () {
            
            $result = $this->dal->PobierzDane('select nextval(\''.Model::TABLE_DANE_OSOBOWE.'_id_seq\') as id;');
            $_personId = $result[0]['id'];
            
            return $_personId;
        }
        
        public function setAbfahrtSent($personId) {
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'UPDATE '.Model::TABLE_DANE_OSOBOWE.' SET isAbfahrtSent = TRUE WHERE id = '.$_personId;
            return $this->dal->pgQuery($query);
        }
    }