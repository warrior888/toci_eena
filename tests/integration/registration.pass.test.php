<?php
    
    require_once 'test.setup.php';
    #the test
    require_once 'bll/definicjeKlas.php';
    require_once 'adl/Candidate.php';
    require_once 'adl/Person.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/BLLDaneOsobowe.php';
    require_once 'dal.php';
    
    class RegistrationPassTest extends IntegrationTest {
        
        protected $testId;
        protected $testPersonId;
        
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
            $person = new Person($this->testPersonId);
            
            $result = $candidate->deletePersonData();
            $result = $result && $person->deletePerson();
            
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
                Model::COLUMN_DIN_KOD               => '49-200', 
                Model::COLUMN_DIN_TELEFON           => 773214325, 
                Model::COLUMN_DIN_KOMORKA           => 664825878, 
                Model::COLUMN_DIN_EMAIL             => 'john@condon', 
                Model::COLUMN_DIN_ID_WYKSZTALCENIE  => 2, 
                Model::COLUMN_DIN_ID_ZAWOD          => 3, 
                Model::COLUMN_DIN_ID_CHARAKTER      => 3,
                Model::COLUMN_DIN_DATA              => '2012-10-02',
                Model::COLUMN_DIN_ILOSC_TYG         => 12,
                Model::COLUMN_DIN_ID_ZRODLO         => 3,
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
        
        protected function getExampleCandidateExtraData () {
            
            return array(
            
                Model::TABLE_PRAWO_JAZDY_INTERNET           => array(1, 2, 4, 6, 7),
                Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET     => array(6, 7, 12, 15, 23, 45, 57),
                Model::TABLE_JEZYKI_INTERNET                => array(
                    array(Model::COLUMN_JIN_ID_JEZYK => 1, Model::COLUMN_JIN_ID_POZIOM => 2),
                    array(Model::COLUMN_JIN_ID_JEZYK => 2, Model::COLUMN_JIN_ID_POZIOM => 3),
                    array(Model::COLUMN_JIN_ID_JEZYK => 4, Model::COLUMN_JIN_ID_POZIOM => 1),
                ),
                Model::TABLE_POPRZEDNI_PRAC_ANKIETA         => array(
                    array(Model::COLUMN_PPA_ID_GRUPA_ZAWODOWA => 1, Model::COLUMN_PPA_NAZWA => 'dsfasffsdwr feqgrrfade'),
                )
            );
        }
        
        private function testAddCandidate() {
            
            $candidate = new Candidate(null);
            $person = new Person(null);
            
            $exampleData = $this->getExampleCandidateData();
            
            $this->testId = $candidate->setPersonData($exampleData, $this->getExampleCandidateExtraData());
            
            $candidateDataSet = $candidate->getPersonData();
            $candidateData = $candidateDataSet[Model::RESULT_FIELD_DATA];
            
            $examplesMatch = true;
            $bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            
            
            //test if a set returned has all data
            foreach ($exampleData as $exKey => $exValue) {

                if ($exValue != $candidateData[$exKey]) {
                    
                    var_dump(__FILE__.' Different: '.$exValue .' vs '. $candidateData[$exKey]);
                    return $this->endSingleTest(false, 'testAddCandidate');
                }
            }
            
            //todo test if a set returned all we added to it and eventually more does not need to be performed if above does not fail, as too much results are acceptable
            //in terms of eventuall software expansion
            $dalDaneInternet = new DALDaneInternet();
            $umiejetnosci = $dalDaneInternet->getSkills($this->testId);
            var_dump($umiejetnosci);
            
            //todo forward data to regular db
            $this->testPersonId = $person->setPersonFromCandidate($candidate, 1, true);
            
            //todo check data
            
            //todo check sms send
            
            return $this->endSingleTest(true, 'testPassCandidate');
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