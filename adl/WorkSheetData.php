<?php

    class WorkSheetData {
        
        private $headers;
        private $suffixEmptyHeaders;
        public $data;
        private $worksheetName;
        private $footerInfo;
        private $title;
        
        public function __construct($headers, $worksheetName, $footerInfo, $data = null, $suffixEmptyHeaders = array(), $title = null) {
            
            $this->headers = $headers;
            $this->suffixEmptyHeaders = $suffixEmptyHeaders;
            $this->data = $data;
            $this->worksheetName = $worksheetName;
            $this->footerInfo = $footerInfo;
            $this->title = $title;
        }
        
        public function getHeaders() {
            
            return $this->headers;
        }
        
        public function getSuffixHeaders() {
            
            return $this->suffixEmptyHeaders;
        }
        
        public function getWorksheetName() {
            
            return $this->worksheetName;
        }
        
        public function getFooterInfo() {
            
            return $this->footerInfo;
        }
        
        public function getTitle() {
            
            return $this->title;
        }
    }