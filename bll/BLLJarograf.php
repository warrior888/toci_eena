<?php

    require_once 'bll/Logic.php';
    require_once 'bll/queries.php';
    require_once 'dal/DALJarograf.php';
    
    class BLLJarograf extends Logic {
        
        public function __construct() {
        
            parent::__construct();
            $this->dataAccess = new DALJarograf();
            
        }
        
        public function getJarografs($personId) {
            return $this->dataAccess->getJarografs($personId);
        }
        
        public function getClients($personId) {
            return $this->dataAccess->getClients($personId);
        }
        
        public function deleteJarograf(User $user, $filePath) {
            
            if($user->isAllowed(User::PRIV_KASOWANIE_REKORDU)) {
                unlink(FileManager::getTaxReadTarget($filePath));
                
                return $this->dataAccess->deleteJarograf($filePath);
            }
        }
        
        public function setReceived($personId, $year, User $user) {
            return $this->dataAccess->setReceived($personId, $year, date("Y-m-d H:i:s"), $user->getUserId());
        }
        
        public function checkReceived($personId, $year) {
            return $this->dataAccess->checkReceived($personId, $year);
        }
        
        public function getFile($personId, $year) {
            return $this->dataAccess->getFile($personId, $year);
        }
        
    }