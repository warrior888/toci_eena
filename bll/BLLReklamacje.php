<?php

    require_once 'bll/Logic.php';
    require_once 'bll/queries.php';
    require_once 'dal/DALReklamacje.php';
    require_once 'dal/DALBiura.php';
    
    /**
     * @property DALReklamacje $dataAccess
     */
    class BLLReklamacje extends Logic {
        
        protected $dalBiura;


        public function __construct() {
        
            parent::__construct();
            $this->dataAccess = new DALReklamacje();
            $this->dalBiura = new DALBiura();
        }
        
        public function getOffices() {
            return $this->dalBiura->getOffices();
        }
        
        public function getComplaints($personId, $active) {
            return $this->dataAccess->getComplaints($personId, $active);
        }
        
        public function addComplaint($personId, $date, $problem, $id_konsult, $id_biuro) {
            return $this->dataAccess->addComplaint($personId, $date, $problem, $id_konsult, $id_biuro);
        }
        
        public function addAnswer($complaintId, $answer) {
            if($this->dataAccess->getComplaint($complaintId) == null) {
                LogManager::log(LOG_ERR, "Complain with ID = $complaintId not exists");
                return false;
            }
            
            return $this->dataAccess->addAnswer($complaintId, $answer);
        }
        
    }