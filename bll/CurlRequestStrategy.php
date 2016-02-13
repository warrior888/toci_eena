<?php

    require_once 'bll/RequestStrategy.php';

    class CurlRequestStrategy extends RequestStrategy {
        
        public function QueryUrl($url, $timeout, $requestMethod = RequestStrategy::REQUEST_METHOD_GET, $headers = array(), $params = array()) {
            
            //TODO FIXME HACK
            //return file_get_contents('D:/projects/svn/eena/devel/api/startpraca.pl.xml');

            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, RequestStrategy::CONNECT_TIMEOUT);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            //curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            //TODO implement post, so far not needed
            
            $result = curl_exec($ch);
            $errorNumber = curl_errno($ch);
            
            if ($errorNumber > 0) {
                
                $error = curl_error($ch);
                throw new LogicServerErrorException('Curl request error: '.$error, '');
            }
            
            curl_close($ch);
            
            return $result;
        }
    }