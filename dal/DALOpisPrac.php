<?php

/**
* @desc Questions:
* 
*   - multiple clients description, or dedicated ?
*   - per client or filiae description ?
*/
    require_once 'Model.php';
    
    class DALOpisPrac extends Model {
        
        const OP_TYPE_INTERNAL          = 1;
        const OP_TYPE_EXTERNAL          = 2;
        const OP_TYPE_SHORTENED         = 3;
        
        const SOURCE_EDITOR             = 1;
        const SOURCE_RAW                = 2;
        
        public function __construct () {
            
            parent::__construct();
        }
        
        public function get ($id, $type) {
            
            $_id = $this->dal->escapeInt($id);
            $_type = $this->dal->escapeInt($type);
            
            $query = 'select * from '.Model::TABLE_OPIS_PRAC.' where '.Model::COLUMN_OPR_ID.' = '.$_id.' and '.Model::COLUMN_OPR_TYP.' = '.$_type;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result[0], $rowsCount);
        }
        
        public function set ($id, $type, $source, $desc) {
            
            $_id = $this->dal->escapeInt($id);
            $_type = $this->dal->escapeInt($type);
            $_source = $this->dal->escapeInt($source);
            $_desc = $this->dal->escapeString($desc);
            
            $testResult = $this->get($id, $type);
            
            if ($testResult !== null) {
                
                // update
                $query = 'update '.Model::TABLE_OPIS_PRAC.' set '.Model::COLUMN_OPR_OPIS.' = \''.$_desc.'\', '.Model::COLUMN_OPR_ZRODLO.' = '.$_source.' where '.
                Model::COLUMN_OPR_ID.' = '.$_id.' and '.Model::COLUMN_OPR_TYP.' = '.$_type;
            } else {
                
                // insert
                $query = 'insert into '.Model::TABLE_OPIS_PRAC.' ('.Model::COLUMN_OPR_ID.', '.Model::COLUMN_OPR_TYP.', '.Model::COLUMN_OPR_ZRODLO.', '.Model::COLUMN_OPR_OPIS.') 
                values ('.$_id.', '.$_type.', '.$_source.', \''.$_desc.'\');';
            }
            
            $result = $this->dal->pgQuery($query);
            
            return $result;
        }
        
        public function delete ($id, $type) {
            
            $_id = $this->dal->escapeInt($id);
            $_type = $this->dal->escapeInt($type);
            
            $query = 'delete from '.Model::TABLE_OPIS_PRAC.' where '.Model::COLUMN_OPR_ID.' = '.$_id.' and '.Model::COLUMN_OPR_TYP.' = '.$_type;
            
            return $this->dal->pgQuery($query);
        }
        
        public function getAll ($type = null, $id = null) {
            
            $_type = $this->dal->escapeInt($type);
            $_id = $this->dal->escapeInt($id);
            
            $query = 'select '.Model::TABLE_OPIS_PRAC.'.*, 
            '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' || \', \' || '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' as '.Model::COLUMN_KLN_NAZWA.'
            from '.Model::TABLE_OPIS_PRAC.' join '.Model::TABLE_ODDZIALY_KLIENT.' on '
            .Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID.' = '.Model::TABLE_OPIS_PRAC.'.'.Model::COLUMN_OPR_ID.
            ' join '.Model::TABLE_KLIENT.' on '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_ID.' = '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID_KLIENT;
            
            if ($_type !== null || $_id !== null) {
                
                $query .= ' where ';
                
                if ($_type) {
                    
                    $query .= Model::COLUMN_OPR_TYP.' = '.$_type;
                    
                    if ($_id) {
                        
                        $query .= ' and '.Model::COLUMN_OPR_ID.' = '.$_id;
                    }
                } else {
                    
                    $query .= Model::COLUMN_OPR_ID.' = '.$_id;
                }
            }
                
            $query .= ' order by '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' asc';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getCompensation ($type) {
            
            $_type = $this->dal->escapeInt($type);
            
            $query = 'select '.Model::TABLE_ODDZIALY_KLIENT.'.*, 
            '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' || \', \' || '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' as '.Model::COLUMN_KLN_NAZWA.' from '.Model::TABLE_KLIENT.' join '.Model::TABLE_ODDZIALY_KLIENT.' on '.
            Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_ID.' = '.Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID_KLIENT
            .' left join '.Model::TABLE_OPIS_PRAC.' on '
            .Model::TABLE_ODDZIALY_KLIENT.'.'.Model::COLUMN_ODK_ID.' = '.Model::TABLE_OPIS_PRAC.'.'.Model::COLUMN_OPR_ID.
            ' and '.Model::TABLE_OPIS_PRAC.'.'.Model::COLUMN_OPR_TYP.' = '.$_type.
            ' where '.Model::TABLE_OPIS_PRAC.'.'.Model::COLUMN_OPR_ID.' is null order by '.Model::TABLE_KLIENT.'.'.Model::COLUMN_KLN_NAZWA.' asc';
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
    }