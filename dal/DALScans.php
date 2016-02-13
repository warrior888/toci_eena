<?php
    require_once 'Model.php';

    class DALScans extends Model
    {
        public function getScannerDocumentTypes()
        {
            $query = 'SELECT * FROM '.Model::TABLE_LISTA_DOKUMENTY_SCAN;
        
            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $result;
        }
    }