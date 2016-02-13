<?php
    require_once 'dal/DALDodatkoweOsoby.php';
    require_once 'bll/Logic.php';
    require_once 'bll/queries.php';
    
    class BLLDodatkoweOsoby extends Logic {

        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALDodatkoweOsoby();
        }
        
        public function getPersonList($personId, $active = false) {
            try {
                $result = $this->dataAccess->getPersons($personId);
                return $result;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getPersonList', '', $e);
            }
        }
        
        public function addPerson($personId, $params) {
            
            $addedPersonId = $params[Model::COLUMN_DODOS_ID_OSOBY_DOD];
            
            if($personId == $addedPersonId) {
                //TODO display some error message???
                LogManager::log(LOG_WARNING, '['.__CLASS__.'] PersonId and AddedPersonId are the same: '.$addedPersonId.' (in addPerson)');
                return false;
            }
            
            try {
                $daneOsobowe = new DALDaneOsobowe();
                if(! $daneOsobowe->get($addedPersonId)) {
                    //TODO display some error message???
                    LogManager::log(LOG_WARNING, '['.__CLASS__.'] Person with ID '.$addedPersonId.' not exists (in addPerson)');
                    return false;
                }
                    
                
                $personList = $this->getPersonList($personId);
                if(is_array($personList)) {
                    foreach ($personList['data'] as $p) {
                        if($p[Model::COLUMN_DODOS_ID_OSOBY_DOD] == $addedPersonId) {
                            return false;
                        }
                    }
                }
                return $this->dataAccess->addPerson($personId, $params);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in addPerson', '', $e);
            }
        }
        
        public function removePerson($personId, $params) {
            try {
                return $this->dataAccess->removePerson($personId, $params);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in addPerson', '', $e);
            }
        }
    }