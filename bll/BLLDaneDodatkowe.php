<?php
    require_once 'dal/DALDaneDodatkoweInternet.php';
    require_once 'dal/DALDaneDodatkoweOsoba.php';
    require_once 'bll/Logic.php';
    require_once 'bll/utilsbll.php';
    
    class BLLDaneDodatkowe extends Logic {

        const FIELD_ID       = 'id';
        
        const CACHE_UN_KEY         = 'DaneDodatkowe';
        const CACHE_UN_KEY_IN_REG  = 'DaneDodatkoweFormularzRejestracji';
        
        const KEY_IDS              = 'ids';
        const KEY_INFO             = 'info';
        const KEY_INFO_BY_NAME     = 'infoByName';
        
        const HAS_SOFFI            = 'posiada_soffi';
        const HAS_COMPANY          = 'z_os_tow';
        const HAS_DRIVING_LICENSE  = 'posiada_pr_j';
        const HAS_FOREIGN_LANGUAGE = 'zna_jezyk';
        const HAS_EMP_HISTORY      = 'historia_zatrudnienia';
        const HAS_SKILLS           = 'ma_umiejetnosci';
        
        const BOOL_TRUE            = 'tak';
        const BOOL_FALSE           = 'nie';
        
        protected $dataIds;
        protected $dataInfo;
        protected $dataInfoByName;
        
        protected $addColumnsCacheKey = BLLDaneDodatkowe::CACHE_UN_KEY;
        protected $isInternet;
        
        //hardcoded for a reason
        //these data are kind of generic meta data about the other data, that are more precise and exist somewhere else
        //i.e. has soffi is a result of select on documents table etc
        protected $configurationTable = array(
            BLLDaneDodatkowe::HAS_SOFFI             => 1, 
            BLLDaneDodatkowe::HAS_COMPANY           => 1, 
            BLLDaneDodatkowe::HAS_DRIVING_LICENSE   => 1, 
            BLLDaneDodatkowe::HAS_FOREIGN_LANGUAGE  => 1,
            BLLDaneDodatkowe::HAS_EMP_HISTORY       => 1,
            BLLDaneDodatkowe::HAS_SKILLS            => 1,
        );
        
        public function __construct($isInternet = false) {
            
            parent::__construct();
            $this->isInternet = $isInternet;
            if (true === $isInternet) {
                
                $this->dataAccess = new DALDaneDodatkoweInternet();
                $this->addColumnsCacheKey = BLLDaneDodatkowe::CACHE_UN_KEY_IN_REG;
            } else {
                
                $this->dataAccess = new DALDaneDodatkoweOsoba();
                $this->addColumnsCacheKey = BLLDaneDodatkowe::CACHE_UN_KEY;
            }
                
            $info = array();
            $infoByName = array();
            //TODO !! overwrite cache as structure became backward incompatible - remove cache or temporary disable caching to regenerate
            if (true && !($cachedData = PermanentCache::get($this->addColumnsCacheKey))) {
                
                try {
                    $idDb = $this->dataAccess->getAdditionalDictionary(); 
                } catch (DBException $e) {
                    throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAdditionalDictionary', '', $e);
                }
                
                if ($idDb == null)
                    throw new LogicServerErrorException('Get additional info for cache returned no results');
                
                foreach ($idDb[Model::RESULT_FIELD_DATA] as $item) {
                    
                    $ids[$item[Model::COLUMN_DICT_NAZWA]] = $item[Model::COLUMN_DICT_ID];
                    $info[$item[Model::COLUMN_DICT_ID]] = $item;
                    $infoByName[$item[Model::COLUMN_DICT_NAZWA]] = $item;
                }
                
                PermanentCache::set($this->addColumnsCacheKey, array(self::KEY_IDS => $ids, self::KEY_INFO => $info, self::KEY_INFO_BY_NAME => $infoByName));
            } else {
                
                $ids = $cachedData[self::KEY_IDS];
                $info = $cachedData[self::KEY_INFO];
                $infoByName = $cachedData[self::KEY_INFO_BY_NAME];
            }

            $this->dataIds = $ids;
            $this->dataInfo = $info;
            //try to avoid requiring of this info
            $this->dataInfoByName = $infoByName;
        }
        
        public function isInternet() {
            
            return $this->isInternet;
        }
        
        /**
        * @desc Get all additional person info columns indexed by id, including non-edit allowed fields
        * @param bool get only column available for edit
        * @return array (ddl id => array(ddl id => , ddl name => , ddl id typ => ...))
        */
        public function getAdditionalsDictList ($onlyEdit = false) {
            
            if ($onlyEdit == false)
                return $this->dataInfo;
            else {
                
                $dataEditInfo = array();
                
                foreach ($this->dataInfo as $key => $data) {
                    
                    if ($data[Model::COLUMN_DDL_EDYCJA] === true) {
                        
                        $dataEditInfo[$key] = $data;
                    }
                }
                
                return $dataEditInfo;
            }
        }
        
        public function getAdditionalsIds () {
            
            return $this->dataIds;
        }
        
        /**
        * @desc get int id for info name.
        */
        public function getAdditionalInfoId ($infoName) {

            return isset($this->dataIds[$infoName]) ? $this->dataIds[$infoName] : null;
        }
        
        /**
        * @desc Set additional info for person and string info name.
        * @param int personId
        * @param string infoName //TODO change so that it is able to use id in case non edit columns updates can be easily modified
        * @param mixed value
        */
        public function setAdditionalInfoRowByName ($personId, $infoName, $value) {
            
            if (!isset($this->dataIds[$infoName]) || 1 > $this->dataIds[$infoName])
                throw new LogicServerErrorException('Additional info '.$infoName.' was not found during setAdditionalInfo.');
                
            $infoIndex = $this->dataIds[$infoName];
            
            if (isset($this->configurationTable[$infoName])) {
                
                //setting bool information
                if ($value)
                    $value = self::BOOL_TRUE;
                else
                    $value = self::BOOL_FALSE;
            }

            try {
                return $this->dataAccess->setAdditionalInfo ($personId, $infoIndex, $value);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setAdditionalInfo', '', $e);
            }
        }
        
        public function setAdditionalInfoRow ($personId, $infoId, $value) {
            
            if (!isset($this->dataInfo[$infoId]))
                throw new LogicBadDataException('Additional info id '.$infoId.' was not found during setAdditionalInfoRow.');
                
            if (!isset($this->dataInfo[$infoId][Model::COLUMN_DICT_NAZWA]))
                throw new LogicServerErrorException('Additional info id '.$infoId.' name was not found during setAdditionalInfoRow.');
                
            $infoName = $this->dataInfo[$infoId][Model::COLUMN_DICT_NAZWA];
            if (isset($this->configurationTable[$infoName])) {
                
                //setting bool information
                if ($value)
                    $value = self::BOOL_TRUE;
                else
                    $value = self::BOOL_FALSE;
            }

            try {
                return $this->dataAccess->setAdditionalInfo ($personId, $infoId, $value);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setAdditionalInfo', '', $e);
            }
        }
        
        public function deleteAdditionalInfo ($personId, $infoIndex) {
            
            try {
                return $this->dataAccess->deleteAdditionalInfo($personId, $infoIndex);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in deleteAdditionalInfo', '', $e);
            }
        }
        
        /**
        * @desc Return entire raw additional info list - pure db result
        */
        public function getAdditionalInfoData ($personId) {
            
            try {
                return $this->dataAccess->getAdditionalInfo($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAdditionalInfoData', '', $e);
            }
        }
        
        /**
        * @desc Get a raw additional info record.
        */
        public function getAdditionalInfoByName ($personId, $infoName) {
            
            if (!isset($this->dataIds[$infoName]))
                throw new LogicBadDataException('['.__CLASS__.'] Get data id by name fail in getAdditionalInfoByName for '.$infoName);
            
            try {
                return $this->dataAccess->getAdditionalInfoById($personId, $this->dataIds[$infoName]);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getFormerEmployers', '', $e);
            }
        }
        
        /**
        * @desc Return enhanced person additional information - form metadata extended person info allowed for arbitrary edit - no non edit data by default
        * uses dane dodatkowe get additional info
        */
        public function getAdditionalInfo ($personId, $onlyEdit = true) {
            
            //HINT - this method was redundant osoba/internet
            try {
                
                //the flag $onlyEdit is to get only column available for arbitrary edit
                $addInfo = $this->getAdditionalsDictList($onlyEdit);
                
                $personAddInfo = $this->getAdditionalInfoData($personId);
                $result = array();
                
                foreach ($personAddInfo[Model::RESULT_FIELD_DATA] as $infoRow) {
                    
                    if ((true === $onlyEdit && isset($addInfo[$infoRow[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA]])) || (false === $onlyEdit)) {

                        $addInfoItem = $addInfo[$infoRow[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA]];
                        $infoRow = array_merge($addInfoItem, $infoRow);
                        $result[$infoRow[Model::COLUMN_DICT_NAZWA]] = $infoRow;
                    } 
                }                
                
                return array (
                    Model::RESULT_FIELD_DATA          => $result,
                    Model::RESULT_FIELD_ROWS_COUNT    => sizeof($result),
                );
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAdditionalInfo', '', $e);
            }
        }
        
        public function getSimpleAdditionalInfo ($personId, $onlyEdit = true) {
            
            try {
                
                //the flag $onlyEdit is to get only column available for arbitrary edit
                $addInfo = $this->getAdditionalsDictList($onlyEdit);
                
                $personAddInfo = $this->getAdditionalInfoData($personId);
                $result = array();
                
                if ($personAddInfo[Model::RESULT_FIELD_ROWS_COUNT] > 0)
                foreach ($personAddInfo[Model::RESULT_FIELD_DATA] as $infoRow) {
                    
                    if ((true === $onlyEdit && isset($addInfo[$infoRow[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA]])) || (false === $onlyEdit)) {

                        $result[$addInfo[$infoRow[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA]][Model::COLUMN_DICT_NAZWA]] = $infoRow[Model::COLUMN_DDO_WARTOSC];
                    } 
                }                
                
                return array (
                    Model::RESULT_FIELD_DATA          => $result,
                    Model::RESULT_FIELD_ROWS_COUNT    => sizeof($result),
                );
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAdditionalInfo', '', $e);
            }
        }
        
        /**
        * @desc set person additional info (optionally remove missing data)
        * When $data key present set to null DELETE, 
        * otherwise delete when no key at all and delete missing true
        * @param int person id
        * @param array array(ddl col name => value, ....))
        */
        public function setAdditionalInfo ($personId, $data, $deleteMissing = false) {

            $addInfo = $this->getAdditionalsDictList(true);
            
            foreach ($addInfo as $ddlId => $ddlInfo) {
                
                if (array_key_exists($ddlInfo[Model::COLUMN_DICT_NAZWA], $data)) { // isset will cheat us here
                    
                    if ($data[$ddlInfo[Model::COLUMN_DICT_NAZWA]] === null) {
                        
                        //update operation indicates to remove value entirely
                        $this->deleteAdditionalInfo($personId, $ddlId);
                    } else {
                        
                        $this->setAdditionalInfoRow($personId, $ddlId, $data[$ddlInfo[Model::COLUMN_DICT_NAZWA]]);
                    }
                } else if ($deleteMissing === true) {
                    
                    $this->deleteAdditionalInfo($personId, $ddlId);
                }
            }
            
            return true;
        }
        
        /**
        * @desc get person meta data - a bunch of random not directly usable person information stored as serialized array
        * @param int person id
        */
        public function getMetaData ($personId) {
            
            try {
                return $this->dataAccess->getMetaData($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getMetaData', '', $e);
            }
        }
        
        /**
        * @desc set person meta data - a bunch of random not directly usable person information stored as serialized array
        * @param int person id
        */
        public function setMetaData ($personId, $metadata) {
            
            try {
                return $this->dataAccess->setMetaData($personId, $metadata);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setMetaData', '', $e);
            }
        }
    }