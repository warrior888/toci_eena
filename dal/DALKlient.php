<?php

    require_once 'Model.php';
    
    class DALKlient extends Model {
        
        const OP_TYPE_INTERNAL          = 1;
        const OP_TYPE_EXTERNAL          = 2;

        public function __construct () {
            
            parent::__construct();
        }
        
        public function getAll () {

            $query = 'select '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID.', 
            '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' || \', \' || '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' as '.Model::COLUMN_KLN_NAZWA.'
            from '.Model::TABLE_ODDZIALY_KLIENT.' join '.Model::TABLE_KLIENT.' on '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_ID.' = '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID_KLIENT;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function set ($clientData) {
            
            $escCallbacks = array (
                Model::COLUMN_KLN_ID_PANSTWO_POS      => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_KLN_ID_PANSTWO_EGZ      => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_KLN_ID_FIRMA            => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_KLN_ID                  => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_KLN_NAZWA               => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_KLN_NAZWA_ALT           => array($this->dal, Model::METHOD_ESCAPE_STRING),
                Model::COLUMN_KLN_ADRES               => array($this->dal, Model::METHOD_ESCAPE_STRING),
            );
            
            $_clientData = $this->escapeParamsList($escCallbacks, $clientData);
            
            $_clientId = $_clientData[Model::COLUMN_KLN_ID];
            
            if ($_clientId > 0) {
                
                //update
                $query = 'update '.Model::TABLE_KLIENT.' set '.$this->createSetClause($_clientData).' where '.Model::COLUMN_KLN_ID.' = '.$_clientId;
            } else {
                
                //insert
                $query = 'insert into '.Model::TABLE_KLIENT.$this->createInsertClause($_clientData);
            }
            
            return $this->dal->pgQuery($query);
        }
        
        public function getFirms() {
            
            $query = 'select * from '.Model::TABLE_FIRMA;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
    }