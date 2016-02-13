<?php

    require_once 'dal/Model.php';
    
    class DALDaneSlownikowe extends Model {
        
        //context, non cached dicts - a compensation list for a given person
        public function getSkillsDifference ($personId, $skillFilter = null) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_skillFilter = $this->dal->escapeString($skillFilter);
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_UMIEJETNOSC.' 
            where '.Model::COLUMN_DICT_ID.' not in 
            (select '.Model::COLUMN_UMO_ID_UMIEJETNOSC.' from '.Model::TABLE_UMIEJETNOSCI_OSOB.' where '.Model::COLUMN_UMO_ID.' = '.$_personId.')';
            
            if ($_skillFilter) 
                $query .= ' and lower('.Model::COLUMN_DICT_NAZWA.') like lower(\'%'.$_skillFilter.'%\')';
                
            $query .= ' order by '.Model::COLUMN_DICT_NAZWA.' asc;';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getLicensesDifference ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_PRAWO_JAZDY.' 
            where '.Model::COLUMN_DICT_ID.' not in 
            (select '.Model::COLUMN_PPJ_ID_PRAWO_JAZDY.' from '.Model::TABLE_POS_PRAWO_JAZDY.' where '.Model::COLUMN_PPJ_ID.' = '.$_personId.') 
            order by '.Model::COLUMN_DICT_NAZWA.' asc;';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get list of sources a person can choose from, potentially append to results a source a person has chosen that is not available on the list any more.
        * @param optional int source Id a person might have chosen before that should be appended to result set
        */
        public function getSourcesDifference ($sourceId = null) {
            
            $_sourceId = $this->dal->escapeInt($sourceId);

            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_ZRODLO.' 
            where '.Model::COLUMN_TZR_WIDOCZNE.' = true';
            
            if ($_sourceId > 0)
                $query .= ' or '.Model::COLUMN_DICT_ID.' = '.$_sourceId.' order by '.Model::COLUMN_DICT_NAZWA.';';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        //simple dicts, cached, TODO refactor redundancy
        public function getNamesList() {
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_IMIONA.' order by  '.Model::COLUMN_DICT_NAZWA;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getCitiesList() {
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_MIEJSCOWOSC.' order by  '.Model::COLUMN_DICT_NAZWA;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getEducationsList() {
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_WYKSZTALCENIE.' order by  '.Model::COLUMN_DICT_NAZWA;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getJobNaturesList() {
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.Model::TABLE_CHARAKTER.' order by  '.Model::COLUMN_DICT_NAZWA;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getUsersList($active = true) {
            
            $query = 'select '.Model::COLUMN_UPR_ID.', '.Model::COLUMN_UPR_IMIE_NAZWISKO.' as nazwa from '.Model::TABLE_UPRAWNIENIA.' where '.Model::COLUMN_UPR_AKTYWNY.' = '.($active ? 'true' : 'false').' order by '.Model::COLUMN_UPR_IMIE_NAZWISKO.';';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        // todo throw all those methods away, table will come from above 
        public function getQuestionairesList() {
            
            return $this->getDictionaryData(Model::TABLE_ANKIETA);
        }
        
        public function getFiliaeList() {
            
            return $this->getDictionaryData(Model::TABLE_FIRMA_FILIA);
        }
        
        public function getCountriesList() {
            
            return $this->getDictionaryData(Model::TABLE_PANSTWO);
        }
        
        public function getActivesReportRecipients () {
            
            return $this->getDictionaryData(Model::TABLE_RAPORT_AKTYWNY);
        }
        
        public function getActivesReportRecipientsPL () {
            
            return $this->getDictionaryData(Model::TABLE_RAPORT_AKTYWNY_PL);
        }
        
        public function getAgenciesRecipients () {
            
            return $this->getDictionaryData(Model::TABLE_RAPORT_AGENCJA_PP);
        }
        
        public function getBanksList () {
            
            return $this->getDictionaryData(Model::TABLE_BANK);
        }
        
        public function getLanguageLevelsList() {
            
            return $this->getDictionaryData(Model::TABLE_POZIOMY);
        }
        
        public function getAdministrationSetting ($settingName) {
            
            $_settingName = $this->dal->escapeString($settingName);
            
            $query = 'select * from '.Model::TABLE_USTAWIENIA_ADMINISTRACYJNE.' where '.Model::COLUMN_UAD_KOD.' = \''.$_settingName.'\'';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount > 0)
                return unserialize($result[0][Model::COLUMN_UAD_TRESC]); // unescape ?!
                
            return null;
        }
        
        public function setAdministrationSetting($settingName, $settingValue) {
            
            $_settingName = $this->dal->escapeString($settingName);
            $_settingValue = serialize($this->dal->escapeString($settingValue));
            
            $present = $this->getAdministrationSetting($settingName);
            
            if (is_null($present)) {
                
                // insert
                $query = 'insert into '.Model::TABLE_USTAWIENIA_ADMINISTRACYJNE.' 
                	('.Model::COLUMN_UAD_KOD.', '.Model::COLUMN_UAD_TRESC.') values (\''.$_settingName.'\', \''.$_settingValue.'\');'; 
            } else {
                
                //update
                $query = 'update '.Model::TABLE_USTAWIENIA_ADMINISTRACYJNE.' set '.Model::COLUMN_UAD_TRESC.' = \''.$_settingValue.'\' where '.Model::COLUMN_UAD_KOD.' = \''.$_settingName.'\'';
            }
            
            return $this->dal->pgQuery($query);
        }
        
        public function getPaymentForms()
        {
            $query = 'select id, nazwa from forma_platnosci order by nazwa asc';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
            
            array_unshift($result, array('id' => 0, 'nazwa' => '---'));
            
            return $this->formatDataOutput($result, $recordsCount);
        }

        public function getTicketStates()
        {
            $query = 'select id, nazwa from stan_realizacji order by nazwa asc';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
            
            array_unshift($result, array('id' => 0, 'nazwa' => '---'));
            
            return $this->formatDataOutput($result, $recordsCount);
        }

        
        public function getCarriers()
        {
            $query = 'select id, nazwa from przewoznik order by nazwa asc';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
            
            array_unshift($result, array('id' => 0, 'nazwa' => '---'));
                
            return $this->formatDataOutput($result, $recordsCount);
        }


        public function setPostCode () {
            
            // todo W przyszlosci przeniesc tu obsluge zapytan z kody pocztowe rejestracje.php
        }
    }