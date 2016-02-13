<?php

    require_once 'dal/DALKlient.php';
    require_once 'bll/Logic.php';
    
    class BLLKlient extends Logic {
        
        
        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALKlient();
        }
        
        public function getAll () {
            
            try {
                
                return $this->dataAccess->getAll();
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAll', '', $e);
            }
        }
    }