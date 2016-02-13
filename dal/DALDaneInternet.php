<?php

    require_once 'dal/Model.php';
    require_once 'dal/DALDaneDodatkowe.php';
    
    class DALDaneInternet extends Model {
        
        const REG_FORM_TYPE_INTERNET  = 2;

        public function __construct () {
            
            parent::__construct();
        }
        
        public function get ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);

            $query = 'select * from '.Model::VIEW_OSOBA_INTERNET.' WHERE '.Model::COLUMN_DIN_ID.' = '.$_personId;
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput(array_shift($result), $rowsCount);
        }
        
        public function set ($dataList) {
            
            $stringEscCallback = array($this->dal, 'escapeString');
            $intEscCallback = array($this->dal, 'escapeInt');
            
            $configuration = array(
                Model::COLUMN_DIN_ID                    => $intEscCallback,
                Model::COLUMN_DIN_ID_IMIE               => $intEscCallback,
                Model::COLUMN_DIN_IMIE                  => $stringEscCallback,
                Model::COLUMN_DIN_NAZWISKO              => $stringEscCallback,
                Model::COLUMN_DIN_ID_PLEC               => $intEscCallback,
                Model::COLUMN_DIN_DATA_URODZENIA        => $stringEscCallback,
                Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR     => $intEscCallback,
                Model::COLUMN_DIN_ID_MIEJSCOWOSC        => $intEscCallback,
                Model::COLUMN_DIN_MIEJSCOWOSC           => $stringEscCallback,
                Model::COLUMN_DIN_MIEJSCOWOSC_UR        => $stringEscCallback,
                Model::COLUMN_DIN_ULICA                 => $stringEscCallback,
                Model::COLUMN_DIN_KOD                   => $stringEscCallback,
                Model::COLUMN_DIN_TELEFON               => $intEscCallback,
                Model::COLUMN_DIN_KOMORKA               => $intEscCallback,
                Model::COLUMN_DIN_EMAIL                 => $stringEscCallback,
                Model::COLUMN_DIN_ID_WYKSZTALCENIE      => $intEscCallback,
                Model::COLUMN_DIN_ID_ZAWOD              => $intEscCallback,
                Model::COLUMN_DIN_ZAWOD                 => $stringEscCallback,
                Model::COLUMN_DIN_ID_CHARAKTER          => $intEscCallback,
                Model::COLUMN_DIN_DATA                  => $stringEscCallback,
                Model::COLUMN_DIN_ILOSC_TYG             => $intEscCallback,
                Model::COLUMN_DIN_ID_ZRODLO             => $intEscCallback,
                Model::COLUMN_DIN_SOURCE                => $intEscCallback,
            );
            
            $_dataList = $this->escapeParamsList($configuration, $dataList);
            
            if (!isset($dataList[Model::COLUMN_DIN_ID])) {
                
                $seqNext = $this->dal->PobierzDane('select nextval(\'dane_internet_id_seq\') as seq;');
                $newId = (int)$seqNext[0]['seq'];
                //implode from what we have, model consts

                $setCols = array(Model::COLUMN_DIN_ID, Model::COLUMN_DIN_DATA_ZGLOSZENIA);
                $setValues = array($newId, '\''.$this->dzis.'\'');
                foreach ($_dataList as $column => $value) {
                    
                    $setCols[] = $column;
                    if (is_int($value)) {
                        
                        $setValues[] = $value;
                    } else {
                        
                        $setValues[] = '\''.$value.'\'';
                    }
                }
                
                $query = 'insert into '.Model::TABLE_DANE_INTERNET.' ('.implode(',', $setCols).') values ('.implode(',', $setValues).')';                
            } else {
                
                throw new Exception('Dane internet update unimplemented');
            }
            
            $this->dal->pgQuery($query);
            
            return $newId;
        }
        
        /**
        * @desc Remove the candidate record from db
        */
        public function delete ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);

            $query = 'delete from '.Model::TABLE_DANE_INTERNET.' WHERE '.Model::COLUMN_DIN_ID.' = '.$_personId;
            return $this->dal->pgQuery($query);
        }
        
        public function getSkills ($candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            $query = 'select * from '.Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.' where '.Model::COLUMN_UOI_ID.' = '.$_candidateId;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc Get a set difference of a set of skills a person has attached and a set of skills the same person declared in the internet
        */
        public function getSkillsCompensation ($personId, $candidateId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            //join umiejetnosc
            $query = 'select '.Model::TABLE_UMIEJETNOSC.'.'.Model::COLUMN_DICT_NAZWA.', '.
            Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.'.'.Model::COLUMN_UMO_ID_UMIEJETNOSC.
            ' from '.Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.
            ' join '.Model::TABLE_UMIEJETNOSC.' on '.
            Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.'.'.Model::COLUMN_UMO_ID_UMIEJETNOSC.' = '.Model::TABLE_UMIEJETNOSC.'.'.Model::COLUMN_DICT_ID.
            ' where '.
            Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.'.'.Model::COLUMN_UMO_ID.' = '.$_candidateId.' and '.
            Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.'.'.Model::COLUMN_UMO_ID_UMIEJETNOSC.' not in 
            (select '.Model::COLUMN_UMO_ID_UMIEJETNOSC.' from '.Model::TABLE_UMIEJETNOSCI_OSOB.' where '.Model::COLUMN_UMO_ID.' = '.$_personId.');';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get a set difference of a set of licenses a person has attached and a set of licenses the same person declared in the internet
        */
        public function getDrivingLicenseCompensation ($personId, $candidateId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            //join umiejetnosc
            $query = 'select '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_NAZWA.', '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID_PRAWO_JAZDY.
            ' from '.Model::TABLE_PRAWO_JAZDY_INTERNET.
            ' join '.Model::TABLE_PRAWO_JAZDY.' on '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID_PRAWO_JAZDY.' = '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_ID.
            ' where '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID.' = '.$_candidateId.' and '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID_PRAWO_JAZDY.' not in 
            (select '.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.' from '.Model::TABLE_POS_PRAWO_JAZDY.' where '.Model::COLUMN_PPJ_ID.' = '.$_personId.');';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get a set difference of a set of licenses a person has attached and a set of licenses the same person declared in the internet
        */
        public function getLanguagesCompensation ($personId, $candidateId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_candidateId = $this->dal->escapeInt($candidateId);

            $query = 'select t1.'.Model::COLUMN_DICT_NAZWA.' as jezyk, t2.'.Model::COLUMN_DICT_NAZWA.' as poziom, 
            f1.'.Model::COLUMN_JIN_ID_JEZYK.', f1.'.Model::COLUMN_JIN_ID_POZIOM.' 
            from '.Model::TABLE_JEZYKI_INTERNET.' f1 
            join '.Model::TABLE_JEZYKI.' t1 on f1.'.Model::COLUMN_JIN_ID_JEZYK.' = t1.'.Model::COLUMN_DICT_ID.' 
            join '.Model::TABLE_POZIOMY.' t2 on f1.'.Model::COLUMN_JIN_ID_POZIOM.' = t2.'.Model::COLUMN_DICT_ID.
            ' where f1.'.Model::COLUMN_JIN_ID.' = '.$_candidateId.' and f1.'.Model::COLUMN_JIN_ID_JEZYK.' not in 
            (select f2.'.Model::COLUMN_ZNJ_ID_JEZYK.' from '.Model::TABLE_ZNANE_JEZYKI.' f2 
            where f2.'.Model::COLUMN_ZNJ_ID.' = '.$_personId.' and f2.'.Model::COLUMN_ZNJ_ID_JEZYK.' = f1.'.Model::COLUMN_JIN_ID_JEZYK.' 
            and f2.'.Model::COLUMN_ZNJ_ID_POZIOM.' = f1.'.Model::COLUMN_JIN_ID_POZIOM.');';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get list of former employers
        * @param int personId
        */
        public function getFormerEmployers ($candidateId) {
            
            $candidateId = $this->dal->escapeInt($candidateId);
            
            $query = 'select f1.'.Model::COLUMN_PPA_ID.', f1.'.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.', f1.'.Model::COLUMN_PPA_NAZWA.', 
            f1.'.Model::COLUMN_PPA_ID_WIERSZ.', t1.'.Model::COLUMN_DICT_NAZWA.' as '.Model::COLUMN_GRU_GRUPA_ZAWODOWA.'
            from '.Model::TABLE_POPRZEDNI_PRAC_ANKIETA.' f1 join '.Model::TABLE_ZAWOD.' t1 
            on f1.'.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.' = t1.'.Model::COLUMN_DICT_ID.' 
            where f1.'.Model::COLUMN_PPA_ID.' = '.$candidateId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set a list of driving licenses
        * @param int id assigned to candidate
        * @param array list of driving licenses ids
        */
        public function setDrivingLicenses ($candidateId, $drivingLicensesIds) {
            
            if (!is_array($drivingLicensesIds) || sizeof($drivingLicensesIds) < 1)
                return;
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $query = 'insert into '.Model::TABLE_PRAWO_JAZDY_INTERNET.' ('.Model::COLUMN_PJI_ID.', '.Model::COLUMN_PJI_ID_PRAWO_JAZDY.') values ';
            
            $values = array();
            
            foreach ($drivingLicensesIds as $drivingLicensesId) {
                
                $licenseId = (int)$drivingLicensesId;
                if ($licenseId > 0) {
                    
                    $values[] = '('.$_candidateId.', '.$licenseId.')';
                }
            }
            
            if (sizeof($values) > 0) {
                
                $query .= implode(',', $values);
                return $this->dal->pgQuery($query);
            }
        }
        
        public function getDrivingLicenses ($candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            //join umiejetnosc
            $query = 'select '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_NAZWA.', '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID_PRAWO_JAZDY.
            ' from '.Model::TABLE_PRAWO_JAZDY_INTERNET.
            ' join '.Model::TABLE_PRAWO_JAZDY.' on '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID_PRAWO_JAZDY.' = '.Model::TABLE_PRAWO_JAZDY.'.'.Model::COLUMN_DICT_ID.
            ' where '.
            Model::TABLE_PRAWO_JAZDY_INTERNET.'.'.Model::COLUMN_PJI_ID.' = '.$_candidateId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set list of internet skills 
        * @param int id of candidate
        * @param array list of skills ids
        */
        public function setSkills ($candidateId, $skillsIds) {
            
            if (!is_array($skillsIds) || sizeof($skillsIds) < 1)
                return;
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $query = 'insert into '.Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET.' ('.Model::COLUMN_UOI_ID.', '.Model::COLUMN_UOI_ID_UMIEJETNOSC.') values ';
            
            $values = array();
            
            foreach ($skillsIds as $skillsId) {
                
                $skillId = (int)$skillsId;
                if ($skillId > 0) {
                    
                    $values[] = '('.$_candidateId.', '.$skillId.')';
                }
            }
            
            if (sizeof($values) > 0) {
                
                $query .= implode(',', $values);
                return $this->dal->pgQuery($query);
            }
        }
        
        /**
        * @desc set candidate's languages
        * @param int id of candidate
        * @param array list of language id + language level
        */
        public function setLanguages ($candidateId, $languagesIds) {
            
            if (!is_array($languagesIds) || sizeof($languagesIds) < 1)
                return;
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $query = 'insert into '.Model::TABLE_JEZYKI_INTERNET.' ('.Model::COLUMN_JIN_ID.', '.Model::COLUMN_JIN_ID_JEZYK.', '.Model::COLUMN_JIN_ID_POZIOM.') values ';
            
            $values = array();
            
            foreach ($languagesIds as $languagesId) {
                
                $langId = (int)$languagesId[Model::COLUMN_JIN_ID_JEZYK];
                $levelId = (int)$languagesId[Model::COLUMN_JIN_ID_POZIOM];
                
                if ($langId > 0 && $levelId > 0) {
                    
                    $values[] = '('.$_candidateId.', '.$langId.', '.$levelId.')';
                }
            }
            
            if (sizeof($values) > 0) {
                
                $query .= implode(',', $values);
                return $this->dal->pgQuery($query);
            }
        }
        
        /**
        * @desc set a list of former employments
        * @param int id of candidate
        * @param array list of employments
        */
        public function setFormerEmployments ($candidateId, $employmentsList) {
            
            if (!is_array($employmentsList) || sizeof($employmentsList) < 1)
                return;
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $query = 'insert into '.Model::TABLE_POPRZEDNI_PRAC_ANKIETA.' ('.Model::COLUMN_PPA_ID.', '.Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA.', '.Model::COLUMN_PPA_NAZWA.') values ';
            
            $values = array();
            
            foreach ($employmentsList as $employment) {
                
                $occGroupId = (int)$employment[Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA];
                $text = (int)$employment[Model::COLUMN_PPA_NAZWA];
                
                if ($occGroupId > 0 && $text) {
                    
                    $values[] = '('.$_candidateId.', '.$occGroupId.', \''.$text.'\')';
                }
            }
            
            if (sizeof($values) > 0) {
                
                $query .= implode(',', $values);
                return $this->dal->pgQuery($query);
            }
        }
        
        
        public function getSmsMessage ($candidateId, $postCode) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_postCode = $this->dal->escapeString($postCode);
            
            $query = 'select * from '.Model::VIEW_SMS_POWITANIE.' where '.Model::COLUMN_KRF_KOD.' = \''.$_postCode.'\'';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($result && is_array($result)) {
                
                $where = $result[0][Model::COLUMN_SKA_WARUNEK];
                $query = 'select '.Model::COLUMN_DIN_ID.' from '.Model::TABLE_DANE_INTERNET.' where '.Model::COLUMN_DIN_ID.' = '.$_candidateId.
                ' and '.$where;
                
                $personRecord = $this->dal->PobierzDane($query, $recordsCount);
                
                if ($personRecord && is_array($personRecord)) {
                    
                    return $result[0][Model::COLUMN_SKA_TRESC];
                }
                
                return null;
            }
            
            return null;
        }
        
        public function getSmsMessages () {
            
            $query = 'select * from '.Model::TABLE_SMS_KANDYDAT;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount, Model::COLUMN_SKA_ID);
        }
        
        public function setSmsMessage ($id, $message) {
            
            $_id = $this->dal->escapeInt($id);
            $_message = $this->dal->escapeString($message);
            
            $query = 'update '.Model::TABLE_SMS_KANDYDAT.' set '.Model::COLUMN_SKA_TRESC.' = \''.$_message.'\' where '.Model::COLUMN_SKA_ID.' = '.$_id;
            
            return $this->dal->pgQuery($query);
        }
    }