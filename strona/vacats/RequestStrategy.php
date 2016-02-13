<?php

    /**
    * @desc Http request strategy api
    */
    abstract class RequestStrategy {
        
        const REQUEST_METHOD_GET                = 'GET';
        const REQUEST_METHOD_POST               = 'POST';
        
        const CONNECT_TIMEOUT                   = 2; //seconds
        
        abstract public function QueryUrl ($url, $timeout, $requestMethod = self::REQUEST_METHOD_GET, $headers = array(), $params = array()); 
    }