<?php
    require_once 'bll/LogManager.php';

    class Authorization 
    {
        const AUTHORIZATION = 'x-Authorization';
        
        const EXPIRATION_INTERVAL = 900;
        
        // clientId/timestamp/haszcz
        private $pattern = '%d/%d/%s';
                                  // params glued, timestamp, id, haszcz
        private $verificationPattern = '%s%d%d%s';
        
        private $secrets = array(
        
            1       => '1dwhvgwrhewvbwrgver23y13i6njgklwrn3129rit3rjceauktrq3buralenekstg574t23u23irahioealsw',
            2       => 'cdkgjoweipgh4674393ugkdsngjldfgakdbgsidgjskdfhnsdfkbn35u768ugwriughskdvndsjlgh4t7y428',
        );
        
        private $queryParams;
        
        private $paramsOrder;
        
        private $headers;
        
        private $authorizationClientId;
        
        private $authTimeDifference;
        
        private $requestAuthorization;
        
        private $hash;
        
        /**
        * @desc 
        * @param array query params array
        * @param array of request headers
        */
        public function __construct($queryParams, $headers, $paramsOrder = array()) {
            
            $this->queryParams = $queryParams;
            $this->headers = $headers;
            $this->setParamsOrder($paramsOrder);
        } 
        
        public function setParamsOrder($paramsOrder) {
            
            $this->paramsOrder = $paramsOrder;
        }
        
        public function getClientId() {
        
        	return $this->authorizationClientId;
        }
        
        public function getAuthTimeDifference() {
        
        	return $this->authTimeDifference;
        }
        
        public function getRequestAuthorization() {
        	
        	return $this->requestAuthorization;
        }
        
        public function getGeneratedHash() {
        
        	return $this->hash;
        }
        
        public function Authorize() {

            if (!isset($this->headers[self::AUTHORIZATION])) {
                
                return false;
            }
            
            $this->requestAuthorization = $this->headers[self::AUTHORIZATION];
            
            sscanf($this->requestAuthorization, $this->pattern, $this->authorizationClientId, $timestamp, $signing);
            
            if (!isset($this->secrets[$this->authorizationClientId])) {
                
                return false;
            }
            
            $currentTime = time();
            
            $this->authTimeDifference = $currentTime - $timestamp;

            if ($this->authTimeDifference > self::EXPIRATION_INTERVAL) {
                
                return false;
            }
            
            $secret = $this->secrets[$this->authorizationClientId];
            
            $paramValues = '';
            foreach ($this->paramsOrder as $orderItem) {
                
                if (isset($this->queryParams[$orderItem]))
                    $paramValues .= $this->queryParams[$orderItem];
            }
            
            $fullStr = sprintf($this->verificationPattern, $paramValues, $timestamp, $this->authorizationClientId, $secret);
            
            $this->hash = md5($fullStr);
            
            return $this->hash == $signing;
            //var_dump($clientId, $timestamp, $signing);
            //var_dump($this->headers);
        }
        
        public function getAuthorization($clientId) {
            
            $timestamp = time();

            return sprintf(self::AUTHORIZATION.': '.$this->pattern, $clientId, $timestamp, $this->getHash($timestamp, $clientId));
        }
        
        private function getHash ($timestamp, $clientId) {
            
            $secret = $this->secrets[$clientId];
            
            $paramValues = '';
            foreach ($this->paramsOrder as $orderItem) {
                
                if (isset($this->queryParams[$orderItem]))
                    $paramValues .= $this->queryParams[$orderItem];
            }
            
            $fullStr = sprintf($this->verificationPattern, $paramValues, $timestamp, $clientId, $secret);
            
            $hash = md5($fullStr);
            
            return $hash;
        }
    }