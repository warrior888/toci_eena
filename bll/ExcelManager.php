<?php
    require_once 'Spreadsheet/Excel/Writer.php'; 

    // todo interface + strategy pattern wrapper (?) or factory ? for futurew newer version xls generator
    class ExcelManager
    {
        private $xlsName;
        private $worksheetDiskPath;
        private $xls = null;
        private $xlsReady = false;
        private $charsetMap;
        
        public function __construct($xlsName) {
            
            $this->xlsName = $xlsName;
            $this->charsetMap = array('±' => chr(185), '¶' => chr(156), '¼' => chr(159), '¦' => chr(140), '¬' => chr(143), '&#8364;' => chr(128));
        }
        
        public function getXls() {
            
            $result = $this->xls->close();
            //te wszystkie naglowki sa ok to tak na przyszlosc :)
            //header("Content-Type: application/force-download");
            //header("Content-Type: application/octet-stream");
            //header("Content-Type: application/download");
            //header('Content-Disposition: attachment; filename="Abfahrt.xls"');
            //header('Accept-Ranges: bytes');
            //header('Content-Transfer-Encoding: binary');
            //header('Content-Type: application/vnd.ms-excel');
            //echo file_get_contents($worksheetName);
            //header('Location: '.$worksheetName);

            if ($result instanceof PEAR_Error)
            {
                $errMsg = $result->getMessage();
                syslog(LOG_ERR, 'Xls generation failure: '.$errMsg);
                return false;
            }
            
            $this->xlsReady = true;
            return $this->worksheetDiskPath;
        }
        
        public function OutputToBrowser($name) {
            
            Spreadsheet_Excel_Writer::send($name.'.xls');
            echo file_get_contents($this->worksheetDiskPath);
            die();
        }
        
        public function addSheet(WorkSheetData $data) {
            
            if (is_null($this->xls)) {
                
                $this->xls = new Spreadsheet_Excel_Writer($this->getXlsPath());
            }
            //var_dump($data->getWorksheetName());
            $sheet = $this->xls->addWorksheet($data->getWorksheetName());
            $format = $this->xls->addFormat();
            
            $kolumna = array_fill(0, 14, 4);
            $key = 0;
            $row = 0;
            $headers = array_merge($data->getHeaders(), $data->getSuffixHeaders());
            
            if($data->getTitle() != null) {
                $sheet->write($row, 0, strtr($data->getTitle(), $this->charsetMap));
                $row++;
            }

            foreach ($headers as $header)
            {
                //key jest kolejna liczba od 0, wiec moze wskazywac kolumny :)
                $sheet->write($row, $key, strtr($header, $this->charsetMap), 0);//$format nie wiem co mozna dac w tym 4 parametrze ale to jest int a nie format
                if (($dlugosc = strlen($header)) > $kolumna[$key])
                    $kolumna[$key] = $dlugosc;
                    
                $key++;
            }
            
            $j = $row + 1; // nr kolejnego wiersza
            if (is_array($data->data))
            foreach ($data->data as $row)
            {
                $i = 0;
                foreach ($row as $index => $column)
                {
                    if (isset($headers[$index]))
                    {
                        $sheet->write($j, $i, strtr($column, $this->charsetMap), 0);//$format
                        if (($dlugosc = strlen($column)) > $kolumna[$i])
                            $kolumna[$i] = $dlugosc;
                            
                        $i++;
                    }
                }
                $j++;
            }
                
            foreach ($kolumna as $key => $length)
            {
                $sheet->setColumn(0, $key, $length, 0, 0, 0);
            }
            
            if ($footerInfo = $data->getFooterInfo()) {
                $j += 2;
                foreach ($footerInfo as $footer) {
                    
                    $sheet->write($j++, 1, strtr($footer, $this->charsetMap), 0);
                }
            }
            
            return $sheet;
        }
        
        private function getXlsPath() {
            
            $this->worksheetDiskPath = FileManager::getWorkSheetPath(md5($this->xlsName.date('Y')));
            
            if (false == $this->worksheetDiskPath)
            {
                throw new ExcelManagerException('Xls filename generation failure for '.$this->xlsName, ExcelManagerException::ERR_CODE_SERVER_ERROR, 'B³¹d tworzenia skoroszytu.');
            }
            
            return $this->worksheetDiskPath;
        }
    }
    
    class ExcelManagerException extends ProjectLogicException {}