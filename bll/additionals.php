<?php

    require_once 'utilsbll.php';
    
    class AdditionalBool 
    {
        const HAS_SOFFI            = 'posiada_soffi';
        const HAS_COMPANY          = 'z_os_tow';
        const HAS_DRIVING_LICENSE  = 'posiada_pr_j';
        const HAS_FOREIGN_LANGUAGE = 'zna_jezyk';
        
        const BOOL_TRUE            = 'tak';
        const BOOL_FALSE           = 'nie';

        
        const CACHE_UN_KEY     = 'additionals';
        
        const EXIST_COLUMN_KEY = 'exists';
        
        protected $configurationTable = array('posiada_soffi', 'z_os_tow', 'posiada_pr_j', 'zna_jezyk');
        //protected $configurationTable = array(self::HAS_SOFFI, self::HAS_COMPANY, self::HAS_DRIVING_LICENSE, self::HAS_FOREIGN_LANGUAGE);
        protected $dataIds = array();
        protected $additionalsInfo = array();
        protected $dal;
        
        protected $idOsoba = null;
        
        public function __construct($idOsoba)
        {
            $this->idOsoba = $idOsoba;
            $this->dal = dal::getInstance();
            
            if (!($ids = PermanentCache::get(self::CACHE_UN_KEY)))
            {
                $idDb = $this->dal->PobierzDane('select id, nazwa from dane_dodatkowe_lista where nazwa in (\''.implode('\',\'', $this->configurationTable).'\');');
                foreach ($idDb as $item) {
                    
                    $ids[$item['nazwa']] = $item['id'];
                }
                
                PermanentCache::set(self::CACHE_UN_KEY, $ids);
            }

            $this->dataIds = $ids;
        }
        
        public function getAdditionalsInfo () {
            
            return $this->dataIds;
        }
        
        public function getSoffiInformation ()
        {            
            return $this->getBoolInformation($this->dataIds[self::HAS_SOFFI]); 
        }
        
        public function getCompanyInformation ()
        {
            return $this->getBoolInformation($this->dataIds[self::HAS_COMPANY]); 
        }
        
        public function getDrivingLicenseInformation ()
        {
            return $this->getBoolInformation($this->dataIds[self::HAS_DRIVING_LICENSE]);
        }
        
        public function getForeignLanguageInformation ()
        {
            return $this->getBoolInformation($this->dataIds[self::HAS_FOREIGN_LANGUAGE]);
        }
        
        public function setSoffiInformation ($forcedInformation = null)
        {
            return $this->setBoolInformation(self::HAS_SOFFI, 'select count(id) as '.self::EXIST_COLUMN_KEY.' from dokumenty where id = '.$this->idOsoba.' and character_length(nip) > 1;', $forcedInformation);
        }
        
        public function setCompanyInformation ($forcedInformation = null)
        {
            $this->setBoolInformation(self::HAS_COMPANY, 'select count(id) as '.self::EXIST_COLUMN_KEY.' from dodatkowe_osoby where id = '.$this->idOsoba.';', $forcedInformation);
        }
        
        public function setDrivingLicenseInformation ($forcedInformation = null)
        {
            $this->setBoolInformation(self::HAS_DRIVING_LICENSE, 'select count(id) as '.self::EXIST_COLUMN_KEY.' from pos_prawo_jazdy where id = '.$this->idOsoba.';', $forcedInformation);
        }
        
        public function setForeignLanguageInformation ($forcedInformation = null)
        {
            $this->setBoolInformation(self::HAS_FOREIGN_LANGUAGE, 'select count(id) as '.self::EXIST_COLUMN_KEY.' from znane_jezyki where id = '.$this->idOsoba.';', $forcedInformation);
        }
        
        protected function getBoolInformation ($boolId) 
        {
            $hasBoolDb = $this->dal->PobierzDane('select id, wartosc from dane_dodatkowe where id_osoba = '.$this->idOsoba.' and id_dane_dodatkowe_lista = '.$boolId.';');
            $hasBool = $hasBoolDb[0]['wartosc'];
            
            return $this->parseDbBool($hasBool);
        }
        
        /**
        * @desc insert or update db additional data information about the person for a conf key.
        * @param string confKey
        * @param sql query testing the value state i.e. (counting langs etc)
        * @param bool force - set without testing - when we are sure somebody has/does not have certain info
        */
        protected function setBoolInformation ($confKey, $existsTestQuery, $forcedInformation = null)
        {
            if ($forcedInformation === null)
            {
                $infoExistsDb = $this->dal->PobierzDane($existsTestQuery);
                $infoExists = $infoExistsDb[0][self::EXIST_COLUMN_KEY];
                
                if ($this->parseDbBool($infoExists))
                {
                    $dbValue = self::BOOL_TRUE;
                }
                else
                {
                    $dbValue = self::BOOL_FALSE;
                }
            }
            else
            {
                if ($forcedInformation === true)
                {
                    $dbValue = self::BOOL_TRUE;
                }
                else
                {
                    $dbValue = self::BOOL_FALSE;
                }
            }
            
            $infoExists = $this->dal->PobierzDane('select id, wartosc from dane_dodatkowe where id_osoba = '.$this->idOsoba.' and id_dane_dodatkowe_lista = '.$this->dataIds[$confKey].';', $rowsCount);
            if ($rowsCount == 1)
            {
                //update
                $result = false;
                if ($infoExists[0]['wartosc'] != $dbValue)
                    $result = $this->dal->pgQuery('update dane_dodatkowe set wartosc = \''.$dbValue.'\' where id = '.$infoExists[0]['id'].';');
            }
            else
            {
                //insert
                $result = $this->dal->pgQuery('insert into dane_dodatkowe (id_osoba, id_dane_dodatkowe_lista, wartosc) values ('.$this->idOsoba.', '.$this->dataIds[$confKey].', \''.$dbValue.'\');');
            }
            
            return $result;
        }
        
        private function parseDbBool ($dbBool)
        {
            if (is_null($dbBool))
                return null;
            if($dbBool >= 1 || $dbBool == self::BOOL_TRUE)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }