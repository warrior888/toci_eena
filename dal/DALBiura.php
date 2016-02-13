<?php
    require_once 'Model.php';

    class DALBiura extends Model
    {
        public function getOffices()
        {
            $query = 'SELECT * FROM '.Model::TABLE_BIURA .
                    ' ORDER BY ' . Model::COLUMN_BIURA_NAME . ' ASC';
        
            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $result;
        }
    }