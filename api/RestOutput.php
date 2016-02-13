<?php

    require_once 'IOutput.php';

    class RestOutput implements IOutput
    {
        public function __construct() {
            
        }
        
        private $errHeaders = array(
    
            400    => 'HTTP/1.1 400',
            401    => 'HTTP/1.1 401',
            403    => 'HTTP/1.1 403',
            404    => 'HTTP/1.1 404',
            500    => 'HTTP/1.1 500',
        );
        
        public function RenderOutput($data, $format, $isAuthorized = false) {

            if (!($isAuthorized === true))
            {
                $this->makeDataTest($data); 
            }
            $data = $this->convertEncoding($data);   
            
            
            switch ($format)
            {
                case 'json':
                    header('Content-encoding: UTF-8');
                    header('Content-type: application/json');
                    echo json_encode($data);
                    break;
                case 'xml':
                    header('Content-encoding: UTF-8');
                    header('Content-type: text/xml');
                    echo $this->renderXml($data);
                    break;
                default: 
                    $this->ExitError(400);
            }
        }
        
        public function ExitError ($code) {
            
            if (!isset($this->errHeaders[$code])) {
                
                header($this->errHeaders[400]);
                die();
            }
            
            header($this->errHeaders[$code]);
            die();
        }
        
        protected function renderXml($array) {
            
            $result = '';
            $records = '';
            
            foreach ($array as $item) {
                
                $row = '';
                foreach ($item as $key => $value) {
                    
                    $row .= '<'.$key.'>'.$value.'</'.$key.'>'; //iconv('ISO-8859-2', 'UTF-8', )
                }
                
                $records .= '<result>'.$row .'</result>';
            }
            
            $result .= '<results>'.$records .'</results>';
            
            return '<?xml version="1.0" encoding="UTF-8"?>'.$result;
        }
        
        private function convertEncoding ($data) {
            
            foreach ($data as $key => $item) {
                
                if (is_array($item)) {
                    
                    $data[$key] = $this->convertEncoding($data[$key]);
                } else {
                    //$data[$key] = utf8_encode($item);
                    //$data[$key] = iconv('ISO-8859-2', 'UTF-8', $item);
                    $data[$key] = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-2');
                }
            }
            
            return $data;
        }
        
        private function makeDataTest (&$data) {
            
            foreach ($data as $key => $item) {
                
                if (is_array($item)) {
                    
                    $this->makeDataTest($data[$key]);
                } else {
                    
                    $data[$key] = mb_substr($item, 0, 3) . '...';
                }
            }
        }
    }