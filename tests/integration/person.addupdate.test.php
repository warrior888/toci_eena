<?php

//TODO a jednak php unit, test integracyjne, klasa z set upem i rozne warianty ...
//tablica idkow do usuniecia dla clean upa
    require_once 'test.setup.php';
    #the test
    require_once 'bll/BLLDaneOsobowe.php';
    require_once 'dal.php';
    
    class PersonAddUpdateTest extends IntegrationTest {

        protected $newIdsList = array();
        protected $addColsList = array();
        protected $addDefaults = array();
        protected $defaultInt = 123;
        protected $defaultBool = 'nie';
        protected $defaultString = 'dupa';
        
        protected $param = array(
                    Model::COLUMN_DOS_ID_IMIE               => '1',
                    Model::COLUMN_DOS_IMIE                  => 'Adam', //id i tak triggerem to podmieni ..
                    Model::COLUMN_DOS_NAZWISKO              => 'fnwdebuigek',
                    Model::COLUMN_DOS_ID_PLEC               => '1',
                    Model::COLUMN_DOS_PLEC                  => 'Mê¿czyzna',
                    Model::COLUMN_DOS_DATA_URODZENIA        => '2001-11-01',
                    Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR     => '1', 
                    Model::COLUMN_DOS_ID_MIEJSCOWOSC        => '2',  
                    Model::COLUMN_DOS_ULICA                 => 'bbbbbb', 
                    Model::COLUMN_DOS_KOD                   => '21-123', 
                    Model::COLUMN_DOS_ID_WYKSZTALCENIE      => '1', 
                    Model::COLUMN_DOS_ID_ZAWOD              => '1',  
                    Model::COLUMN_DOS_ID_KONSULTANT         => 1,  
                    Model::COLUMN_DOS_DATA_ZGLOSZENIA       => '2012-02-02',  
                    Model::COLUMN_DOS_ID_CHARAKTER          => '1',
                    Model::COLUMN_DOS_DATA                  => '2012-12-12',
                    Model::COLUMN_DOS_ILOSC_TYG             => '2',
                    Model::COLUMN_DOS_ID_ANKIETA            => '1',
                    Model::COLUMN_DOS_ID_ZRODLO             => '1',
                    Model::COLUMN_DOS_NR_OBUWIA             => '43',
                    Model::COLUMN_STT_ID_STATUS             => '4',
                    Model::COLUMN_MDO_DANE                  => array('dupa' => 1),
                );
        
        public function setUp () {
            
            $this->msg = __CLASS__;
            $this->dal = dal::getInstance();
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
            $this->addColsList = $bllDaneDodatkowe->getAdditionalsDictList(true);
            
            $this->addDefaults = array(
                Model::DD_ID_TYP_BOOL       => $this->defaultBool,
                Model::DD_ID_TYP_STRING     => $this->defaultString,
                Model::DD_ID_TYP_INT        => $this->defaultInt,
            );
            // Extend by additional data
            foreach ($this->addColsList as $ddlId => $ddItem) {
                
                $this->param[$ddItem[Model::COLUMN_DICT_NAZWA]] = $this->addDefaults[$ddItem[Model::COLUMN_DDL_ID_TYP]];
            }
        }
        
        /**
        * @desc set a new person with additional data
        */
        protected function addNewRecord ($id = null) {
            
            $bllDaneOs = new BLLDaneOsobowe();
   
            if ($id) {
                
                $this->param[Model::COLUMN_DOS_ID] = $id;
            }
            
            $newId = $bllDaneOs->setPerson(
                $this->param
            );
            
            $bllDaneOs->setContact($newId, 1, date('Y-m-d'));

            $this->newIdsList[$newId] = $newId;
            
            return $newId;
        }
        
        private function testAddNew() {
            
            $newId = $this->addNewRecord();
            
            if ($newId < 1)
                return false;
            
            $dalDaneOs = new BLLDaneOsobowe();
                
            $addedRecord = $dalDaneOs->getEditData($newId);
            
            if (!$addedRecord)
                return $this->endSingleTest(false, 'testAddNew');
            
            $contacts = $this->dal->PobierzDane('select * from kontakt where id = '.$newId, $rowsCount);
            $contactsHistory = $this->dal->PobierzDane('select * from kontakt_historia where id = '.$newId, $rowsHistoryCount);
            
            if (strpos($contactsHistory[0]['data'], $contacts[0]['data'])) {
                
                var_dump(__FILE__.' Different: '.$contacts[0]['data'] .' vs '. $contactsHistory[0]['data']); 
                return $this->endSingleTest(false, 'testAddNew');
            }
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
            
            $metadata = $bllDaneDodatkowe->getMetaData($newId);
            
            if ($metadata) {

                if ($metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDO_DANE]['dupa'] != $this->param[Model::COLUMN_MDO_DANE]['dupa']) {
                    
                    var_dump(__FILE__.' Different: '.$metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDO_DANE]['dupa'] .' vs '. $this->param[Model::COLUMN_MDO_DANE]['dupa']); 
                    return $this->endSingleTest(false, 'testAddNew');
                }
            }
            
            //check both regular and additional data
            foreach ($addedRecord[Model::RESULT_FIELD_DATA] as $column => $value) {
                
                if (isset($this->param[$column])) {

                    if ($this->param[$column] != $value) {
                        var_dump(__FILE__.' Different: '.$this->param[$column] .' vs '. $value);
                        return $this->endSingleTest(false, 'testAddNew');
                    }
                }
            }
            //check additional data from the opposite approach, causing eventually double check
            foreach ($this->addColsList as $ddlId => $addCol) {
            
                $column = $addCol[Model::COLUMN_DICT_NAZWA];
                if (isset($this->param[$column])) {
                    
                    if ($addedRecord[Model::RESULT_FIELD_DATA][$column] != $this->param[$column]) {
                        
                        var_dump(__FILE__.' Different: '.$this->param[$column] .' vs '. $addedRecord[Model::RESULT_FIELD_DATA][$column]);
                        return $this->endSingleTest(false, 'testAddNew');
                    }
                }
            }
            
            return $this->endSingleTest(true, 'testAddNew');
        }
        
        public function cleanUp () {
            
            #cleanup
            $dalDaneOs = new DALDaneOsobowe();
            
            $result = true;
            foreach ($this->newIdsList as $newId) {
                
                $result = $dalDaneOs->deletePerson($newId) && $result;
            }
            
            return $result;
        }
        
        public function runTests () {
            
            try {
                $result = $this->testAddNew();
                //$result = $this->testAddDefaults() && $result;
                //$result = $this->testAddDefaults(false) && $result;
                
                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
    }