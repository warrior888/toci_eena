<?php
    
    require_once 'test.setup.php';
    #the test
    require_once 'bll/definicjeKlas.php';
    require_once 'adl/Candidate.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'dal.php';
    
    class RegistrationPersonTest extends IntegrationTest {
        
        protected $testId;
        
        protected $addColsList = array();
        protected $addDefaults = array();
        
        protected $defaultInt = 123;
        protected $defaultBool = 'nie';
        protected $defaultString = 'dupa';
    
        public function setUp () {
            
            $this->msg = __CLASS__;
            $this->dal = dal::getInstance();
        }
        
        public function cleanUp () {
            
            #cleanup
            $candidate = new Candidate($this->testId);
            $result = $candidate->deletePersonData();
            return $result;
        }
        
        protected function getExampleCandidateData() {
            
            $candidateData = array(
                Model::COLUMN_DIN_ID_IMIE           => 1,
                Model::COLUMN_DIN_NAZWISKO          => 'Zapart', 
                Model::COLUMN_DIN_ID_PLEC           => 1, 
                Model::COLUMN_DIN_DATA_URODZENIA    => '1984-08-08', 
                Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR => 1, 
                Model::COLUMN_DIN_ID_MIEJSCOWOSC    => 1, 
                Model::COLUMN_DIN_ULICA             => 'dghsffwgie', 
                Model::COLUMN_DIN_KOD               => '12-200', 
                Model::COLUMN_DIN_TELEFON           => 773214325, 
                Model::COLUMN_DIN_KOMORKA           => null, 
                Model::COLUMN_DIN_EMAIL             => null, 
                Model::COLUMN_DIN_ID_WYKSZTALCENIE  => 2, 
                Model::COLUMN_DIN_ID_ZAWOD          => 3, 
                Model::COLUMN_DIN_ID_CHARAKTER      => 3,
                Model::COLUMN_DIN_DATA              => '2012-10-02',
                Model::COLUMN_DIN_ILOSC_TYG         => 12,
                Model::COLUMN_DIN_ID_ZRODLO         => 3,
                Model::COLUMN_MDI_DANE              => array('dwiedupy' => 2),
                Model::COLUMN_PJI_ID_PRAWO_JAZDY    => array(ID_PRAWO_JAZDY_A, ID_PRAWO_JAZDY_B, ID_PRAWO_JAZDY_C),
                Model::COLUMN_JIN_ID_JEZYK          => array(array(Model::COLUMN_JIN_ID_JEZYK => ID_JEZYK_HOLENDERSKI, Model::COLUMN_JIN_ID_POZIOM => ID_POZIOM_SREDNI)),
            );
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            $this->addColsList = $bllDaneDodatkowe->getAdditionalsDictList(true);
            
            $this->addDefaults = array(
                Model::DD_ID_TYP_BOOL       => $this->defaultBool,
                Model::DD_ID_TYP_STRING     => $this->defaultString,
                Model::DD_ID_TYP_INT        => $this->defaultInt,
            );
            // Extend by additional data
            foreach ($this->addColsList as $ddlId => $ddItem) {
                
                $candidateData[$ddItem[Model::COLUMN_DICT_NAZWA]] = $this->addDefaults[$ddItem[Model::COLUMN_DDL_ID_TYP]];
            }
            
            return $candidateData;
        }
        
        private function testAddCandidate() {
            
            $candidate = new Candidate(null);
            
            $exampleData = $this->getExampleCandidateData();
            
            $this->testId = $candidate->setPersonData($exampleData);
            
            $candidateDataSet = $candidate->getPersonData();
            $candidateData = $candidateDataSet[Model::RESULT_FIELD_DATA];
            
            $examplesMatch = true;
            $bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            $dalDaneInternet = new DALDaneInternet();
            
            $metadata = $bllDaneDodatkowe->getMetaData($this->testId);
            
            if ($metadata) {

                if ($metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE]['dwiedupy'] != $exampleData[Model::COLUMN_MDI_DANE]['dwiedupy']) {
                    
                    var_dump(__FILE__.' Different: '.$metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE]['dwiedupy'] .' vs '. $exampleData[Model::COLUMN_MDI_DANE]['dwiedupy']); 
                    return $this->endSingleTest(false, 'testAddCandidate');
                }
            }
            
            $drivingLicenses = $dalDaneInternet->getDrivingLicenses($this->testId);
            $drivingLicenses = $drivingLicenses[Model::RESULT_FIELD_DATA];
            $inputLicenses = array_flip($exampleData[Model::COLUMN_PJI_ID_PRAWO_JAZDY]);
            
            if (sizeof($inputLicenses) !== sizeof($drivingLicenses)) {
                
                var_dump(__FILE__.' Different: driving license input vs saved array sizes');
                return $this->endSingleTest(false, 'testAddCandidate');
            }
            
            foreach ($drivingLicenses as $drivingLicense) {
                
                if (!isset($inputLicenses[$drivingLicense[Model::COLUMN_PJI_ID_PRAWO_JAZDY]])) {
                    
                    var_dump(__FILE__.' Different: '.$drivingLicense[Model::COLUMN_PJI_ID_PRAWO_JAZDY] .' vs none');
                    return $this->endSingleTest(false, 'testAddCandidate');
                }
            }
            
            // TODO add language proper add check       
            
            unset($exampleData[Model::COLUMN_MDI_DANE]);
            unset($exampleData[Model::COLUMN_PJI_ID_PRAWO_JAZDY]);
            unset($exampleData[Model::COLUMN_JIN_ID_JEZYK]);
            //test if a set returned has all data
            foreach ($exampleData as $exKey => $exValue) {

                if ($exValue != $candidateData[$exKey]) {
                    
                    var_dump(__FILE__.' Different: '.$exValue .' vs '. $candidateData[$exKey]);
                    return $this->endSingleTest(false, 'testAddCandidate');
                }
            }

            //test if a set returned all we added to it and eventually more does not need to be performed if above does not fail, as too much results are acceptable
            //in terms of eventuall software expansion
            
            return $this->endSingleTest(true, 'testAddCandidate');
        }
        
        public function runTests () {
            
            try {
                $result = $this->testAddCandidate();

                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
    }