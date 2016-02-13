<?php

    require_once '../conf.php';
    require_once 'adl/Person.php';
    
    class SmsHistoryView extends View {
        
        public static function instantiate() {
            return new SmsHistoryView();
        }
        
        public function __construct () {
            parent::__construct();
            $idOsoba = Utils::PodajIdOsoba();
            $this->person = new Person($idOsoba);
            
        }

        public function run() {
            $historyData = $this->person->getSmsHistory();
            $personData = $this->person->getPersonData();
            $html = $this->viewHistory($historyData, $personData[Model::RESULT_FIELD_DATA]);
            $html .= $this->htmlControls->_AddSubmit(User::PRIV_NONE_REQUIRED, 'Zamknij', 'Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            return $html;
        }
        
        private function viewHistory($smsData, $personData) {
            $result = "<h2>Historia wysy³ki sms dla: {$personData['imie']} {$personData['nazwisko']}</h2>";
            
            $table = new HtmlTable();
            $table->addTableCss('wide');
            $table->setHeader(array('Lp.', 'Nr telefonu', 'Tre¶æ', 'Data', 'Konsultant', 'Status'));
            if ($smsData) {
                $lp =1;
                foreach ($smsData[Model::RESULT_FIELD_DATA] as $row) {
                    $table->addRow(array($lp,
                                    $row['telefon'],
                                    $row['tresc'],
                                    $row['data'],
                                    $row['konsultant'],
                                    $row['status'])
                            );
                    $lp++;
                }
        
                $result .= $table->__toString();
            } else {
                $result .= "<h4>Brak wys³anych sms'ów'</h4>";
            }

            return $result;
        }
    }
    
    $output = SmsHistoryView::instantiate();
    $output->execute();
