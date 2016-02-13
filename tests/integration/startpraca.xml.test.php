<?php

//TODO a jednak php unit, test integracyjne, klasa z set upem i rozne warianty ...
//tablica idkow do usuniecia dla clean upa
    require_once 'test.setup.php';
    #the test
    require_once 'dal/DALDaneInternet.php';
    require_once 'api/StartPracaSniper.php';
    require_once 'dal.php';
    
    class StartPracaXmlTest extends IntegrationTest {
        
        protected $listIds = array();

        public function setUp () {
            
            $this->msg = __CLASS__;
            $this->dal = dal::getInstance();
        }
        
        private function testAddData() {
      
            $sniper = new StartPracaSniper();
            $this->listIds = $sniper->query();
            
            return sizeof($this->listIds) > 0;
        }
        
        public function cleanUp () {
            
            #cleanup
            $bllDaneInternet = new BLLDaneInternet();
            
            $result = true;
            foreach ($this->listIds as $candidateId) {
                
                $result = $result && $bllDaneInternet->delete($candidateId);
            }
            
            return $result;
        }
        
        public function runTests () {
            
            try {
                $result = $this->testAddData();
                
                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
    }