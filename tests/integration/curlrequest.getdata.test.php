<?php

    require_once 'test.setup.php';
    #the test
    require_once 'bll/CurlRequestStrategy.php';
    
    class CurlRequestGetDataTest extends IntegrationTest {

        public function setUp () {
            
            $this->msg = __CLASS__;
        }
        
        private function testGetData() {
      
            $curlStrategy = new CurlRequestStrategy();
            
            $result = $curlStrategy->QueryUrl('http://yogi/', 5);
            
            //to assume success check if at least open close html section is there
            return strpos($result, '<html>') !== false && strlen($result) > 12;
        }
        
        public function cleanUp () {
            
            #cleanup
            return true;
        }
        
        public function runTests () {
            
            try {
                $result = $this->testGetData();
                
                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
    }