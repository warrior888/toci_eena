<?php
    require_once 'bll/ExcelManager.php'; 
    require_once 'bll/mail.php'; 
    require_once 'dal/DALWyjazd.php'; 
    require_once 'dal/DALDaneSlownikowe.php'; 
    require_once 'dal/DALDaneOsobowe.php'; 
    require_once 'adl/WorkSheetData.php'; 

    class BLLBalances extends Logic
    {
        const LAST_ACTIVES_LIST_SENT = 'last_actives_list_sent';
        const LAST_FORMER_EMPLOYMENTS_LIST_SENT = 'last_fe_list_sent';
        
        const SRODA = 3;
        
        const DURATION_NOT_SENT = 604800;
        
        private $xlsManager;
        private $headers = array('id' => 'Id', 'imie' => 'Imie', 'nazwisko' => 'Nazwisko', 'data_urodzenia' => 'Data urodzenia', 
        'nip' => 'Sofinumer', 'data_wyjazdu' => 'Data wyjazdu', 'biuro' => 'Biuro', 'klient' => 'Klient', 'data_powrotu' => 'Data powrotu'
        );
        
        public function __construct() {
            
            parent::__construct();
        }
        
        public function SendActivesNotification() {
            
            // check when last sent comparing to current date; send if wednesday (always, regardless) 
            // or after wednesday and not sent for longer than a week
            $dalActives = new DALWyjazd();
            $dalDicts = new DALDaneSlownikowe();
            
            //dzien w zakresie 0-6 (1 - poniedzialek, 0 - niedziela)
            $todayTime = time();
            $dzien = strftime('%w', $todayTime);
            $today = date('Y-m-d', $todayTime);
            
            $lastSent = $dalDicts->getAdministrationSetting(self::LAST_ACTIVES_LIST_SENT);
            $lastSentTimestamp = !is_null($lastSent) ? strtotime($lastSent) : 0;
            
            if ($dzien == self::SRODA || self::DURATION_NOT_SENT < ($todayTime - $lastSentTimestamp)) { // LUB pozniej, ale poprzednio wyslano ponad tydzien temu
                
                $path = $this->getActivesXls($today); // worksheet full disk path
                $recipients = $dalDicts->getActivesReportRecipients();
                
                $mail = new MailSend();
                // add attachment, add recipients
                
                foreach ($recipients[Model::RESULT_FIELD_DATA] as $row) {
                    
                    $mail->DodajOdbiorca($row[Model::COLUMN_DICT_NAZWA]);
                }
                
                $mail->DodajZalacznik($path, 'actives.xls');
                $result = $mail->WyslijMail('Actives list.', 'Regards, E&A.');
                
                
                //only with PL 
                $path = $this->getActivesXls($today, true); // worksheet full disk path
                $recipients = $dalDicts->getActivesReportRecipientsPL();

                $mail = new MailSend();

                foreach ($recipients[Model::RESULT_FIELD_DATA] as $row) 
                {
                    $mail->DodajOdbiorca($row[Model::COLUMN_DICT_NAZWA]);
                }

                $mail->DodajZalacznik($path, 'actives.xls');
                $result = $mail->WyslijMail('Actives list PL.', 'Regards, E&A.');

                $dalDicts->setAdministrationSetting(self::LAST_ACTIVES_LIST_SENT, $today);
            }
        }
        
        public function SendFormerEmploymentsSummary() {
            
            $todayTime = time();
            $weekAgoTime = $todayTime - 604800;
            $dzien = strftime('%w', $todayTime);
            $today = date('Y-m-d', $todayTime);
            $weekAgo = date('Y-m-d', $weekAgoTime);
            
            $dalDicts = new DALDaneSlownikowe();
            
            $lastSent = $dalDicts->getAdministrationSetting(self::LAST_FORMER_EMPLOYMENTS_LIST_SENT);
            $lastSentTimestamp = !is_null($lastSent) ? strtotime($lastSent) : 0;
            
            if ($dzien == self::SRODA || self::DURATION_NOT_SENT < ($todayTime - $lastSentTimestamp)) { // LUB pozniej, ale poprzednio wyslano ponad tydzien temu
                
                $path = $this->getFormerEmploymentsXls($lastSent ? $lastSent : $weekAgo, $today); // worksheet full disk path
                $recipients = $dalDicts->getAgenciesRecipients();
                
                $mail = new MailSend();
                // add attachment, add recipients
                
                foreach ($recipients[Model::RESULT_FIELD_DATA] as $row) {
                    
                    $mail->DodajOdbiorca($row[Model::COLUMN_DICT_NAZWA]);
                }
                
                $mail->DodajZalacznik($path, 'formeremployments.xls');
                $result = $mail->WyslijMail('Former employments agencies list.', 'Regards, E&A.');

                $dalDicts->setAdministrationSetting(self::LAST_FORMER_EMPLOYMENTS_LIST_SENT, $today);
            }
        }
        
        protected function getActivesXls ($date, $onlyPL = false) {
            
            $dalActives = new DALWyjazd();
            $dalDicts = new DALDaneSlownikowe();
            
            $activesResult = $dalActives->getActiveList($date);
            $countriesList = $dalDicts->getCountriesList();
            if ($onlyPL) {
                $countriesList[Model::RESULT_FIELD_DATA] = array(array(Model::COLUMN_DICT_ID => 2, Model::COLUMN_DICT_NAZWA => "Polska"));
            }
            
            $worksheets = array();
            
            foreach ($countriesList[Model::RESULT_FIELD_DATA] as $country)
            {
                $worksheets[$country[Model::COLUMN_DICT_ID]] = new WorkSheetData($this->headers, $country[Model::COLUMN_DICT_NAZWA], null, array());
            }
            
            foreach ($activesResult[Model::RESULT_FIELD_DATA] as $active)
            {
                if($onlyPL && $active['id_panstwo_pos'] != 2)  //PL
                {
                    continue;
                }
                $worksheets[$active['id_panstwo_pos']]->data[] = $active;
            }
            
            $this->xlsManager = new ExcelManager($onlyPL ? 'AktywniPL' : 'Aktywni');
            foreach ($worksheets as $worksheet)
            {
                $this->xlsManager->addSheet($worksheet);
            }
            
            return $this->xlsManager->getXls();
        }
        
        protected function getFormerEmploymentsXls($dateFrom, $dateTo) {
            
            $dalDaneOsobowe = new DALDaneOsobowe();
            
            $agencjePopPracData = $dalDaneOsobowe->getFormerEmployments($dateFrom, $dateTo);
            
            $agencjePopPrac = $agencjePopPracData[Model::RESULT_FIELD_DATA];
            foreach ($agencjePopPrac as $key => $agencja)
            {
                list ($country, $city, $firmName, $department, $position, $period) = explode('/', $agencja['nazwa']);
                $agencjePopPrac[$key]['country'] = $country;
                $agencjePopPrac[$key]['city'] = $city;
                $agencjePopPrac[$key]['firmName'] = $firmName;
                $agencjePopPrac[$key]['department'] = $department;
                $agencjePopPrac[$key]['position'] = $position;
                $agencjePopPrac[$key]['period'] = $period;
            }
            
            $agencjePopPracData[Model::RESULT_FIELD_DATA] = $agencjePopPrac;
            
            $this->xlsManager = new ExcelManager('Agencje');
            $worksheet = new WorkSheetData(
                array(
                	'id' => 'Id', 'plec' => 'Gender', 'data_urodzenia' => 'Date of birth', 'agencja' => 'Work agency', 'data' => 'Date', 
                	'country' => 'Country', 'city' => 'City', 'firmName' => 'Firm name', 'department' => 'Department', 'position' => 'Position', 
                	'period' => 'Period'
                ), 
                'Agencje', null, $agencjePopPracData[Model::RESULT_FIELD_DATA]);
            $this->xlsManager->addSheet($worksheet);
            
            return $this->xlsManager->getXls();
        }
        
        // on purpose of test of a generated xls
        /*public function Output($name) {
            
            $this->xlsManager->OutputToBrowser($name);
        }*/
    }