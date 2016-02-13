<?php

    require_once 'bll/Logic.php';
    require_once 'bll/queries.php';
    require_once 'dal/DALScans.php';
    
    class BLLScans extends Logic {
        
        public function __construct() {
        
            parent::__construct();
            $this->dataAccess = new DALDaneOsobowe();
            $this->dalScans = new DALScans();
        }
        
        public function getStanTypes() {
        	return $this->dalScans->getScannerDocumentTypes();
        }
        
        public function getPersonScans($personId) {
            return $this->dataAccess->getScannerDocuments($personId);
        }
        
    }