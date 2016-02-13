<?php
    
    require_once 'Model.php';
    
    //readfile('Model.php');
    
    class DALUprawnienia extends Model {
        
        protected $setEscCallbacks = array();
        
        public function __construct () {
            
            parent::__construct();
        }
        
        public function getUser ($id) {
            
            $_id = $this->dal->escapeInt($id);
            
            $query = 'select * from '.Model::TABLE_UPRAWNIENIA.' where '.Model::COLUMN_UPR_ID.' = '.$_id.';';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount !== 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getUserByLogin ($login, $password) {
            
            $_login = $this->dal->escapeString($login);
            $_password = md5($password);
            
            $query = 'select * from '.Model::TABLE_UPRAWNIENIA.' where '.Model::COLUMN_UPR_NAZWA_UZYTKOWNIKA.' = \''.
            $_login.'\' and '.Model::COLUMN_UPR_HASLO.' = \''.$_password.'\';';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount !== 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function setUser ( $userData) { //array
            
            $this->setEscCallbacks = array (
                Model::COLUMN_UPR_ID                  => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_IMIE_NAZWISKO       => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_UPR_NAZWA_UZYTKOWNIKA   => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_UPR_HASLO               => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_UPR_ADRES_EMAIL         => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_UPR_LICZBA_REKORDOW     => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DODAWANIE_REKORDU   => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DODAWANIE_KWERENDY  => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DODAWANIE_ZETTLA    => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_EDYCJA_REKORDU      => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_EDYCJA_GRUPOWA      => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_KASOWANIE_REKORDU   => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DRUK_UMOWY          => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DRUK_LISTY          => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DRUK_ROZLICZENIA    => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DRUK_ANKIETY        => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_DRUK_BILETU         => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_EMAIL               => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_MASOWY_EMAIL        => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_MASOWY_SMS          => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_ZMIANA_UPRAWNIEN    => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_UPR_AKTYWNY             => array($this->dal, Model::METHOD_ESCAPE_BOOL),
                Model::COLUMN_UPR_WYGASA              => array($this->dal, Model::METHOD_ESCAPE_STRING),
            );
            
            $_userData = $this->escapeParamsList($this->setEscCallbacks, $userData);
            
            if (isset($_userData[Model::COLUMN_UPR_HASLO]))
                $_userData[Model::COLUMN_UPR_HASLO] = md5($_userData[Model::COLUMN_UPR_HASLO]);
            
            $setClause = $this->createSetClause($_userData);
            
            if (isset($_userData[Model::COLUMN_UPR_ID])) {
                
                $query = 'update '.Model::TABLE_UPRAWNIENIA.' set '.$setClause.' where '.Model::COLUMN_UPR_ID.'='.$_userData[Model::COLUMN_UPR_ID];
            } else {
                //TODO - is this working ? nope ?
                $query = 'insert into '.Model::TABLE_UPRAWNIENIA.' set '.$setClause;
            }
            
            $result = $this->dal->pgQuery($query);
            
            if ($result)
                return $this->castArray($_userData);
                
            return null;
        }
        
        public function getFirmaFilia ($idFirmaFilia) {
            
            $_idFirmaFilia = $this->dal->escapeInt($idFirmaFilia);
            
            $query = 'select * from '.Model::TABLE_FIRMA_FILIA.' where '.Model::COLUMN_FIF_ID.' = '.$_idFirmaFilia.';';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount !== 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
    }