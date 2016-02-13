<?php
    require_once 'dal/DALRemoteSourcesStats.php';
    
    class BLLRemoteSourcesStats extends Logic {
        
        const SOURCE_STARTPRACA = 'startpraca';
        const FIELD_LAST_ID = 'lastId';

        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALRemoteSourcesStats();
        }
        

        public function set ($source, $field, $value) {
            
            try {
                return $this->dataAccess->set($source, $field, $value);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in set', '', $e);
            }
        }
        
        public function get ($source, $field) {
            
            $result = $this->dataAccess->get($source, $field);
            return $result;
        }        
    }