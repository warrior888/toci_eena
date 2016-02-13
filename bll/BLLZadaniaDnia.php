<?php
    require_once 'dal/DALZadaniaDnia.php';
    require_once 'bll/Logic.php';
    require_once 'bll/queries.php';
    
    class BLLZadaniaDnia extends Logic {

        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALZadaniaDnia();
        }
        
        public function getActiveTaskList($personId) {
            return $this->getTaskList($personId, true);
        }
        
        public function getResolvedTaskList($personId) {
            return $this->getTaskList($personId, false);
        }
        
        public function getTaskList ($personId, $active = false) {
            try {
                $result = $this->dataAccess->getTaskList($personId, $active);
                return $result;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSkillsList', '', $e);
            }
        }
        
        public function setTask($personId, $params) {
            try {
                return $this->dataAccess->setTask($personId, $params);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in set', '', $e);
            }
        }
    }