<?php

    require_once 'DataManager.php';
    require_once 'ParserStartPraca.php';
    require_once 'bll/BLLRemoteSourcesStats.php';
    require_once 'bll/CurlRequestStrategy.php';

    /**
    * @desc specific startpraca class, out of api chain
    */
    class StartPracaSniper {
        
        const STARTPRACA_URL = STARTPRACA_REQUEST_URL;
        const STARTPRACA_SECRET = 'Z4w1R0w4N1e';
        
        const QUERY_PARAM_FROM          = 'from';
        const QUERY_PARAM_TIMESTAMP     = 'ts';
        const QUERY_PARAM_CHECKSUM      = 'cs';

        const REQUEST_TIMEOUT = 20;
        
        protected $lastId = 0;
        
        public function query() {
            
            //run request with suitable strategy, then forward results to manager
            $data = $this->callDataSource(new CurlRequestStrategy());
            
            $dataManager = new DataManager(new ParserStartPraca($this->lastId));
            //this saves startpraca data in db
            return $dataManager->parse($data);
        }
        
        /**
        * @desc call a data source with a most suitable strategy
        */
        protected function callDataSource (RequestStrategy $requestStrategy) {
            
            //get last id, format query url, 
            $bllRemSources = new BLLRemoteSourcesStats();
            
            $lastIdResponse = $bllRemSources->get(BLLRemoteSourcesStats::SOURCE_STARTPRACA, BLLRemoteSourcesStats::FIELD_LAST_ID);
            if ($lastIdResponse !== null) {
                
                $this->lastId = (int)$lastIdResponse[Model::RESULT_FIELD_DATA][Model::COLUMN_ZDZ_WARTOSC];
            }
            //var_dump($this->lastId);
            //$this->lastId = 115283;
            
            $timestamp = time();
            //var_dump($timestamp, $this->lastId);
            
            //$this->lastId = 472568;
            
            $checksum = md5($this->lastId.$timestamp.self::STARTPRACA_SECRET);
            
            $queryUrl = self::STARTPRACA_URL.'?'.self::QUERY_PARAM_FROM.'='.$this->lastId.'&'.self::QUERY_PARAM_TIMESTAMP.'='.$timestamp.'&'.self::QUERY_PARAM_CHECKSUM.'='.$checksum;
//echo $queryUrl."\n\n";
//die();
            return $requestStrategy->QueryUrl($queryUrl, self::REQUEST_TIMEOUT);
        }
    }