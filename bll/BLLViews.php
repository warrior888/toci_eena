<?php
    require_once 'bll/ExcelManager.php'; 
    require_once 'dal/DALKlient.php'; 
    require_once 'adl/WorkSheetData.php'; 

    class BLLViews extends Logic
    {
        private $headers;
        private $abfahrtFooters = array(
            1 => array('Zawarte na powy�szej li�cie dane osobowe firma E&A uitzendbureau z siedzib� Emma Goldmanweg 8h, 5032 MN TILBURG', 'otrzyma�a na podstawie obowi�zuj�cej umowy o wsp�pracy i gwarantuje, i� s� one przetwarzane zgodnie z', 'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z p�n. zm.'),
            2 => array('Zawarte na powy�szej li�cie dane osobowe firma T-interim z siedzib� Stationsstraat 120, 2800 Mechelen', 'otrzyma�a na podstawie obowi�zuj�cej umowy o wsp�pracy i gwarantuje, i� s� one przetwarzane zgodnie z', 'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z p�n. zm.')
        );
        
        private $depRetFooters = array(
            1 => array('Zawarte na powy�szej li�cie dane osobowe firma So�tysik Reisen Sp. z o.o. z siedzib� ul.Magnolii 16, 44-152 Gliwice', 
                'otrzyma�a na podstawie obowi�zuj�cej umowy o wsp�pracy i gwarantuje, i� s� one przetwarzane zgodnie z',
                'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z p�n. zm.'),
            2 => array('Zawarte na powy�szej li�cie dane osobowe firma Przedsi�biorstwo Transportowe Arnold z siedzib� ul. Budowlanych 6,',
                '45-205 Opole otrzyma�a na podstawie obowi�zuj�cej umowy o wsp�pracy i gwarantuje, i� s� one przetwarzane zgodnie', 
                'z wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z p�n. zm.'),
            5 => array('Zawarte na powy�szej li�cie dane osobowe firma So�tysik Reisen Sp. z o.o. z siedzib� ul.Magnolii 16, 44-152 Gliwice', 
                'otrzyma�a na podstawie obowi�zuj�cej umowy o wsp�pracy i gwarantuje, i� s� one przetwarzane zgodnie z',
                'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z p�n. zm.')
        );
        
        private $names = array(
            1 => 'wyjazd',
            2 => 'powrot',
            3 => 'abfahrt',
            5 => 'zwolniony',
        );
        
        private $xlsManager;
        
        public function __construct() {
            
        }
        
        public function getXlsForView($viewId, $data, $headers, $carrierId, $suffixEmptyHeaders = array(), $title = null) {
            
            $this->headers = $headers;
            $workSheetDataCollection = array();
            
            switch($viewId) {
                
                case 1: // wyj
                    $workSheet = $this->getDepartureXls($data, $viewId, $carrierId, $suffixEmptyHeaders, $title);
                    break;
                case 2:
                    $workSheet = $this->getReturnXls($data, $viewId, $carrierId, $title);
                    break;
                case 3: //abf 
                    $workSheet = $this->getAbfahrtXls($data);
                    break;
                case 5: //zwolniony
                    $workSheet = $this->getXls($data, $this->headers, 'zwolnieni', $this->names[5]);
                    break;
                case 10: //rozliczenie biletow
                    $workSheet = $this->getXls($data, $this->headers, 'Rozliczenie biletow', 'rozliczenie_biletow');
                    break;
            }       
            
            return $workSheet;
        }
        
        public function getXlsForData($data, $headers, $sheetName, $footer = null) {
            
            return $this->getXls($data, $headers, $sheetName, 'commonsheet', $footer);
        }
        
        public function Output($name) {
            
            $this->xlsManager->OutputToBrowser($name);
        }
        // todo one generall util for many tabbed/sheeted xls
        private function getXls($data, $headers, $sheetName, $xlsInternalName, $footer = null, $suffixEmptyHeaders = array(), $title = null) {
            
            $workSheetData = new WorkSheetData($headers, $sheetName, $footer, $data, $suffixEmptyHeaders, $title);

            $this->xlsManager = new ExcelManager($xlsInternalName);

            $this->xlsManager->addSheet($workSheetData);

            return $this->xlsManager->getXls();
        }
        
        private function getDepartureXls($data, $viewId, $carrierId, $suffixEmptyHeaders = array(), $title = null) {
            
            return $this->getXls($data, $this->headers, 'lista', $this->names[$viewId], $this->depRetFooters[$carrierId], $suffixEmptyHeaders, $title);
        }
        
        private function getReturnXls($data, $viewId, $carrierId, $title = null) {
            
            return $this->getDepartureXls($data, $viewId, $carrierId, array(), $title);
        }
        
        private function getAbfahrtXls($data) {
            
            $dalKlient = new DALKlient();
            $firmsResponse = $dalKlient->getFirms();
            $firms = array();
            
            foreach ($firmsResponse[Model::RESULT_FIELD_DATA] as $row) {
                
                $firms[$row['id']] = $row['nazwa'];
            }
            
            $workSheetDataCollection = array();
            
            // we need to rely on id firma here, per each we need to create a separate object, footer different, headers the same, worksheet name - office name, 
            // data - collections per each id biuro
            
            foreach ($data as $row) {
                
                $idFirma = $row['id_firma'];
                
                if (!isset($workSheetDataCollection[$idFirma])) {
                    
                    $workSheetData = new WorkSheetData($this->headers, $firms[$idFirma], $this->abfahrtFooters[$idFirma]);
                    
                    $workSheetDataCollection[$idFirma] = $workSheetData;
                }
                
                $workSheetDataItem = $workSheetDataCollection[$idFirma];
                
                $workSheetDataItem->data[] = $row;
            }
            
            $this->xlsManager = new ExcelManager('abfahrt');
            
            foreach ($workSheetDataCollection as $workSheetDataElement) {
                
                $this->xlsManager->addSheet($workSheetDataElement);
            }
            
            return $this->xlsManager->getXls();
        }
    }