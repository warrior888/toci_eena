<?php

    require_once 'dal/DALDaneSlownikowe.php';
    require_once 'bll/Logic.php';
    
    class BLLDaneSlownikowe extends Logic {
        
        const ADM_SEND_WELCOME_SMS  = 'send_welcome_sms';

        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALDaneSlownikowe();
        }
        
        //non cached context data
        public function getSkillsDifference ($personId, $skillFilter = null) {
            
            try {
                return $this->dataAccess->getSkillsDifference($personId, $skillFilter);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSkillsDifference', '', $e);
            }
        }
        
        public function getLicensesDifference ($personId) {
            
            try {
                return $this->dataAccess->getLicensesDifference($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getLicensesDifference', '', $e);
            }
        }
        
        public function getSourcesDifference ($sourceId = null) {
            
            try {
                return $this->dataAccess->getSourcesDifference($sourceId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSourcesDifference', '', $e);
            }
        }
        
        public function getGenders() {
            
            return array(
                0 => array(Model::COLUMN_DICT_ID => ID_PLEC_MEZCZYZNA, Model::COLUMN_DICT_NAZWA => 'Mê¿czyzna'),
                1 => array(Model::COLUMN_DICT_ID => ID_PLEC_KOBIETA, Model::COLUMN_DICT_NAZWA => 'Kobieta'),
            );
        }
        
        //cached non context data, TODO refactor redundancy
        public function getNamesList() {
            
            //try to get from cache, otherwise get from db and cache
            $cachedNames = $this->getDictFromCache(Model::TABLE_IMIONA);
            
            if ($cachedNames)
                return $cachedNames;
                
            $result = $this->dataAccess->getNamesList();
            $this->setDictInCache(Model::TABLE_IMIONA, $result);
            
            return $result;
        }
        
        public function getCitiesList() {
            
            //try to get from cache, otherwise get from db and cache
            $cachedNames = $this->getDictFromCache(Model::TABLE_MIEJSCOWOSC);
            
            if ($cachedNames)
                return $cachedNames;
                
            $result = $this->dataAccess->getCitiesList();
            $this->setDictInCache(Model::TABLE_MIEJSCOWOSC, $result);
            
            return $result;
        }
        
        public function getEducationsList() {
            
            //try to get from cache, otherwise get from db and cache
            $cachedNames = $this->getDictFromCache(Model::TABLE_WYKSZTALCENIE);
            
            if ($cachedNames)
                return $cachedNames;
                
            $result = $this->dataAccess->getEducationsList();
            $this->setDictInCache(Model::TABLE_WYKSZTALCENIE, $result);
            
            return $result;
        }
        
        public function getJobNaturesList() {
            
            //try to get from cache, otherwise get from db and cache
            $cachedNames = $this->getDictFromCache(Model::TABLE_CHARAKTER);
            
            if ($cachedNames)
                return $cachedNames;
                
            $result = $this->dataAccess->getJobNaturesList();
            $this->setDictInCache(Model::TABLE_CHARAKTER, $result);
            
            return $result;
        }
        
        public function getQuestionairesList() {
            
            //try to get from cache, otherwise get from db and cache
            return $this->getDictionary(Model::TABLE_ANKIETA, array($this->dataAccess, 'getQuestionairesList'));
        }
        
        public function getFiliaeList () {
            
            return $this->getDictionary(Model::TABLE_FIRMA_FILIA, array($this->dataAccess, 'getFiliaeList'));
        }
        
        public function getBanksList () {
            
            return $this->getDictionary(Model::TABLE_BANK, array($this->dataAccess, 'getBanksList'));
        }
        
        public function getLanguageLevelsList () {
            
            return $this->getDictionary(Model::TABLE_POZIOMY, array($this->dataAccess, 'getLanguageLevelsList'));
        }
        
        public function getCountriesList () {
            
            return $this->getDictionary(Model::TABLE_PANSTWO, array($this->dataAccess, 'getCountriesList'));
        }
        
        public function getSendSmsAdmSetting () {
            
            try {
                
                return $this->dataAccess->getAdministrationSetting(self::ADM_SEND_WELCOME_SMS);
            } catch (DBException $e) {
                
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSendSmsAdmSetting', '', $e);
            }
        }
    }