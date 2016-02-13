<?php 

    require_once 'VacatsParser.php';
    require_once 'CurlRequestStrategy.php';
    require_once 'RequestStrategy.php';

    class VacatsClient {
        
        const CLIENT_ID = 2;
        const CLIENT_SECRET = 'cdkgjoweipgh4674393ugkdsngjldfgakdbgsidgjskdfhnsdfkbn35u768ugwriughskdvndsjlgh4t7y428';
        
        const VACATS_URL = VACATS_REQUEST_URL;
        const FIELD_ID = 'id';
        
        const REQUEST_TIMEOUT = 5;
        
        public function query($id = null) {
            
            //run request with suitable strategy, then forward results to manager
            $data = $this->callDataSource(new CurlRequestStrategy(), $id);
            
            $dataManager = new VacatsParser();
            //this saves startpraca data in db
            return $dataManager->getDataList($data);
        }
        
        protected function callDataSource (RequestStrategy $requestStrategy, $id = null) {
            
            $queryUrl = self::VACATS_URL;
            $queryString = 'ext=json';
            
            if (!is_null($id)) {
                
                $queryString .= '&' . self::FIELD_ID.'='.$id;
            }
            
            $queryUrl .= $queryString;
            
            $pattern = '%d/%d/%s';
            $timestamp = time();
        
            $authorization = sprintf('x-Authorization: '.$pattern, self::CLIENT_ID, $timestamp, $this->getHash($timestamp, self::CLIENT_ID, self::CLIENT_SECRET, $queryString));

            return $requestStrategy->QueryUrl($queryUrl, self::REQUEST_TIMEOUT, RequestStrategy::REQUEST_METHOD_GET, array($authorization));
        }
        
        private function getHash ($timestamp, $clientId, $secret, $queryString) {
        
            $verificationPattern = '%s%d%d%s';
            parse_str($queryString, $queryParams);
            
            $paramsOrder = array(
                'ext',
                'id',
            );
            
            $paramValues = '';
            foreach ($paramsOrder as $orderItem) {
                
                if (isset($queryParams[$orderItem]))
                    $paramValues .= $queryParams[$orderItem];
            }
            
            $fullStr = sprintf($verificationPattern, $paramValues, $timestamp, $clientId, $secret);
            //echo $fullStr."\n";
            $hash = md5($fullStr);
            
            return $hash;
        }
    }