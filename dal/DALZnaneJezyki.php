<?php

    require_once 'Model.php';
    
    class DALZnaneJezyki extends Model {

        public function __construct () {
            
            parent::__construct();
        }
        
        public function get ($personId) 
        {
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select 
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_ID.',
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_JEZYK.',
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_POZIOM.',
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_ID_JEZYK.',
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_ID_POZIOM.',
            '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_ID_ZNANY_JEZYK.',
            '.Model::TABLE_ZATWIERDZONE_JEZYKI.'.'.Model::COLUMN_ZJE_DATA.',
            '.Model::TABLE_ZATWIERDZONE_JEZYKI.'.'.Model::COLUMN_ZJE_ID_KONSULTANT.'
            from '.Model::TABLE_ZNANE_JEZYKI.'
            left join  '.Model::TABLE_ZATWIERDZONE_JEZYKI.' on '.Model::TABLE_ZNANE_JEZYKI.'.'.Model::COLUMN_ZNJ_ID_ZNANY_JEZYK.' = '.Model::TABLE_ZATWIERDZONE_JEZYKI.'.'.Model::COLUMN_ZJE_ID_ZNANY_JEZYK.'
            where '.Model::COLUMN_ZNJ_ID.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getDifference ($personId) 
        {
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select * from '.Model::TABLE_JEZYKI.' where '.Model::COLUMN_DICT_ID.' not in (select '.Model::COLUMN_ZNJ_ID_JEZYK.' from '.Model::TABLE_ZNANE_JEZYKI.' where '.Model::COLUMN_ZNJ_ID.' = '.$_personId.')';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function set ($personId, $languageId, $levelId, $id = null) 
        {
            if (is_null($id))
            {
                // update
            }
            else
            {
                // insert
            }
        }
        
        protected function setConfirmedLanguage ()
        {
            
        }
        
        protected function deleteConfirmedLanguage() 
        {
            
        }
    }