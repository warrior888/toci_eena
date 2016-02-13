<?php
	set_include_path(get_include_path().PATH_SEPARATOR.'../../');
    
    define('TEST_STATUS_OK', "OK. \n");
    define('TEST_STATUS_FAIL', "FAIL. \n");
    
    require_once 'conf.php';
	require_once 'dal.php';

	abstract class IntegrationTest {
        
        protected $dal;
        protected $msg;
        
        public function __construct () {
            
            
        }
        
        abstract public function setUp();
        abstract public function cleanUp();
        abstract public function runTests();
        
        public function run() {
            
            $this->setUp();
            $result = $this->runTests();
            $result = $this->cleanUp() && $result;
            
            return $result;
        }
        
        public function endSingleTest($status, $testName) {
            
            if ($status)
                echo $this->msg.' '.$testName.' '.TEST_STATUS_OK;
            else
                echo $this->msg.' '.$testName.' '.TEST_STATUS_FAIL;
                
            return $status;
        }
    }
