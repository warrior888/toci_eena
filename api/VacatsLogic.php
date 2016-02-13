<?php

    require_once 'dal/DALWakaty.php';
    require_once 'bll/Logic.php';
    
    class VacatsLogic extends Logic {
        
        public function __construct() {
            
            parent::__construct();
            
            $this->dataAccess = new DALWakaty();
        }
        
        public function getAll() {
            
            try {
            	$result = $this->dataAccess->getAll();
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAll', '', $e);
            }
            
            return $result[Model::RESULT_FIELD_DATA];
        }
        
        public function get($id) {
            
            try {
            	$result = $this->dataAccess->getPageVacat($id);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAll', '', $e);
            }
            
            return $result[Model::RESULT_FIELD_DATA];
        }
    }