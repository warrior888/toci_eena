<?php
    require_once 'ParserStartPraca.php';
    require_once 'bll/BLLDaneInternet.php';

    class DataManager 
    {
        const DATA_SOURCE_STARTPRACA = 'ParserStartPraca';
        
        protected $parserList = array (
            DataManager::DATA_SOURCE_STARTPRACA => DataManager::DATA_SOURCE_STARTPRACA,
        );        
        
        protected $parser; //IParser
        
        public function __construct (IParser $dataSource) {
            
            if (!isset($this->parserList[get_class($dataSource)])) {
                
                //throw exception
            }
            
            $this->parser = $dataSource;
        }
        
        public function parse ($receivedData) {
            
            $parsedData = $this->parser->getDataList($receivedData);
            // add to db - dane internet
            $bllDaneInternet = new BLLDaneInternet();
            
            $listIds = array();
            foreach ($parsedData as $parsedRow) {
                
                $currentId = $bllDaneInternet->set($parsedRow);
                // if current id < 1 operation fail, but this can only happen when sql wrong or db down, so exception will fire anyway
                if ($currentId > 0)
                    $listIds[] = $currentId;
            }
            
            // save last id for startpraca
            $this->parser->importSuccessfull();
            
            return $listIds;
        }
    }    