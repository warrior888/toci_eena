<?php

    require_once 'Model.php';
    
    class DALDokumenty extends Model {

        public function __construct () {
            
            parent::__construct();
        }
        
        public function get($id) {
            
            $_id = $this->dal->escapeInt($id);
            
            $query = 'select * from '.Model::TABLE_DOKUMENTY.' where '.Model::COLUMN_DOK_ID.' = '.$_id;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function delete($userId)
        {
        	//$this->dal->pgQuery($this->dbDelete->Delete(Model::TABLE_DOKUMENTY, ' where id = '.$userId));      // GRANDE TODO
        	$this->dal->pgQuery(' DELETE FROM '.Model::TABLE_DOKUMENTY.' where id = '.$userId);// FIXME
        }
        
        public function set ($idOsoba, $passNumber, $expiryDate, $nipNl, $bankId, $accountNr) {
            
            $_idOsoba = $this->dal->escapeInt($idOsoba);
            $_passNumber = $this->dal->escapeString($passNumber);
            $_expiryDate = $this->dal->escapeString($expiryDate);
            $_nipNl = $this->dal->escapeString($nipNl);
            $_bankId = $this->dal->escapeInt($bankId);
            $_accountNr = $this->dal->escapeString($accountNr);
            
            $dataList = array(
            
                Model::COLUMN_DOK_ID                => $_idOsoba,
                Model::COLUMN_DOK_PASS_NR           => $_passNumber,
                Model::COLUMN_DOK_DATA_WAZNOSCI     => $_expiryDate,
                Model::COLUMN_DOK_NIP               => $_nipNl,
                Model::COLUMN_DOK_ID_BANK           => $_bankId,
                Model::COLUMN_DOK_NR_KONTA          => $_accountNr,
            );
            
                        
            $testQuery = 'select '.Model::COLUMN_DOK_ID.' from '.Model::TABLE_DOKUMENTY.' where '.Model::COLUMN_DOK_ID.' = '.$_idOsoba;
            $result = $this->dal->PobierzDane($testQuery, $rowsCount);
            $id = null;
            
            if ($rowsCount === 1) {
                
                $id = $result[0][Model::COLUMN_DOK_ID];
            }
            
            if ($id !== null) {
                
                // update
                $setQuery = 'update '.Model::TABLE_DOKUMENTY.' set '.$this->createSetClause($dataList).' where '.Model::COLUMN_DOK_ID.' = '.$_idOsoba;
            } else {
                
                // insert
                $setQuery = 'insert into '.Model::TABLE_DOKUMENTY.' '.$this->createInsertClause($dataList).';';
            }
            
            return $this->dal->pgQuery($setQuery);
        }
    }