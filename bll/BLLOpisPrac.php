<?php

    require_once 'dal/DALOpisPrac.php';
    require_once 'bll/Logic.php';
    
    class BLLOpisPrac extends Logic {
        
        
        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALOpisPrac();
        }
        
        public function getAll($type = null, $id = null, $compensation = false) {
            
            try {
                if (false === $compensation)
                    return $this->dataAccess->getAll($type, $id);
                else
                    return $this->dataAccess->getCompensation($type);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getAll', '', $e);
            }
        }
        
        public function set ($id, $type, $source, $desc) {
            
            try {
                
                return $this->dataAccess->set($id, $type, $source, $desc);
            } catch (DBException $e) {
                
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in set', '', $e);
            }
        }
        
        public function get ($id, $type) {
            
            try {
                
                return $this->dataAccess->get($id, $type);
            } catch (DBException $e) {
                
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in get', '', $e);
            }
        }
        
        public function delete ($clientId, $type) {
            
            try {
                
                return $this->dataAccess->delete($clientId, $type);
            } catch (DBException $e) {
                
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in delete', '', $e);
            }
        }
    }