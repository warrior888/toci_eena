<?php

    require_once 'dal/DALUprawnienia.php';
    require_once 'bll/Logic.php';

    class BLLUprawnienia extends Logic {
        
        public function __construct () {
            
            parent::__construct();
            $this->dataAccess = new DALUprawnienia();
        }
        
        public function getUserByLogin ($login, $password) {
            
            $userData = $this->dataAccess->getUserByLogin($login, $password);
            
            if (!$userData || $userData[Model::RESULT_FIELD_ROWS_COUNT] !== 1)
                throw new LogicNotFoundException('User '.$login.' not found for a given password');
                
            return $userData;
        }
        
        public function setUser ($userData) {
            
            try {
                return $this->dataAccess->setUser($userData);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setUser', '', $e);
            }
        }
        
        public function getUser ($userId) {
            
            try {
                return $this->dataAccess->getUser($userId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getUser', '', $e);
            }
        }
        
        public function getFirmaFilia ($idFirmaFilia) {
            
            try {
                return $this->dataAccess->getFirmaFilia($idFirmaFilia);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getFirmaFilia', '', $e);
            }
        }
    }