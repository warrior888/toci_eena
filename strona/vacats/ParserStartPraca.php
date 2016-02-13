<?php
    require_once 'IParser.php';
    require_once 'bll/BLLRemoteSourcesStats.php';
    require_once 'bll/validationUtils.php';

    /**
    * @desc Parse startpraca format
    */
    class ParserStartPraca implements IParser
    {
        const STARTPRACA_SOURCE             = 3;
        const STARTPRACA_INFO_SOURCE_ID     = INFO_SOURCE_ID_INTERNET_WORK_PORTAL;
        
        const TAG_RESULT            = 'result';
        const TAG_PEOPLE            = 'people';
        
        const FIELD_DIN_COLUMN                = 'column';
        const FIELD_DIN_COLUMN_MAXLENGTH      = 'length';
        
        const FIELD_QUES_DIN_COLUMN = 'column';
        const FIELD_QUES_MAPPING    = 'mapping';
        const FIELD_QUES_CALLBACK   = 'callback';
        const FIELD_QUES_VALIDATION = 'validation';
        const FIELD_QUES_INFO_TYPE  = 'info_type';
        
        const FIELD_QUES_TYPE_SINGLE_INFO       = 'si';
        const FIELD_QUES_TYPE_MULTIPLE_INFO     = 'mi';
        
        const ATTRIBUTE_ID          = 'id';
        const ATTRIBUTE_TYPE        = 'type';
        const ATTRIBUTE_QUESTION    = 'question';
        
        const ATTRIBUTE_CONTACT_GSM             = 'GSM';
        const ATTRIBUTE_CONTACT_INNY            = 'inny';
        const ATTRIBUTE_CONTACT_EMAIL           = 'email';
        
        protected $oldLastId, $newLastId;
        
        protected $colsMapping = array(
        
            'surname'               => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_NAZWISKO, self::FIELD_DIN_COLUMN_MAXLENGTH => 30),
            'name'                  => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_IMIE, self::FIELD_DIN_COLUMN_MAXLENGTH => 20),
            'birthDate'             => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_DATA_URODZENIA, self::FIELD_DIN_COLUMN_MAXLENGTH => 10),
            'education'             => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_ID_WYKSZTALCENIE, self::FIELD_DIN_COLUMN_MAXLENGTH => 25), //Model::COLUMN_DIN_WYKSZTALCENIE,
            'profession'            => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_ZAWOD, self::FIELD_DIN_COLUMN_MAXLENGTH => 200),
            'city'                  => array(self::FIELD_DIN_COLUMN => Model::COLUMN_DIN_MIEJSCOWOSC, self::FIELD_DIN_COLUMN_MAXLENGTH => 30),
        );
        
        protected $attrColsMapping = array(
            
            ParserStartPraca::ATTRIBUTE_CONTACT_GSM     => Model::COLUMN_DIN_KOMORKA,
            ParserStartPraca::ATTRIBUTE_CONTACT_INNY    => Model::COLUMN_DIN_TELEFON,
            ParserStartPraca::ATTRIBUTE_CONTACT_EMAIL   => Model::COLUMN_DIN_EMAIL,
        );
        
        protected $questionsMapping = array(
        
            1028        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_MIEJSCOWOSC_UR, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO), 
            1018        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_ULICA, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO), 
            1019        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_KOD, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO, self::FIELD_QUES_VALIDATION => array('StartpracaValidations', 'validatePostCode')), 
            1020        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_DATA, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO, self::FIELD_QUES_VALIDATION => array('StartpracaValidations', 'validateDepartureDate')), 
            1021        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_ILOSC_TYG, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO, self::FIELD_QUES_VALIDATION => array('StartpracaValidations', 'validateWeeksCount')), 
            1022        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_DIN_ID_CHARAKTER, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO, 
            
                self::FIELD_QUES_MAPPING => array(
                    
                    3189    => ID_CHARAKTER_STALA,
                    3190    => ID_CHARAKTER_URLOP,
                    3191    => ID_CHARAKTER_WAKACJE,
                )),
                
            1023        => array(self::FIELD_QUES_DIN_COLUMN => Model::DDL_COLUMN_WZROST, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_SINGLE_INFO, self::FIELD_QUES_VALIDATION => array('StartpracaValidations', 'validateHeight')),
            1024        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_PJI_ID_PRAWO_JAZDY, self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_MULTIPLE_INFO,
                self::FIELD_QUES_MAPPING => array(
                    
                    3192    => ID_PRAWO_JAZDY_A,
                    3193    => ID_PRAWO_JAZDY_A1,
                    3194    => ID_PRAWO_JAZDY_B,
                    3195    => ID_PRAWO_JAZDY_B1,
                    3196    => ID_PRAWO_JAZDY_BE,
                    3197    => ID_PRAWO_JAZDY_C,
                    3198    => ID_PRAWO_JAZDY_C1,
                    3199    => ID_PRAWO_JAZDY_C1E,
                    3200    => ID_PRAWO_JAZDY_CE,
                    3201    => ID_PRAWO_JAZDY_D,
                    3202    => ID_PRAWO_JAZDY_D1,
                    3203    => ID_PRAWO_JAZDY_D1E,
                    3204    => ID_PRAWO_JAZDY_DE,
                    3205    => ID_PRAWO_JAZDY_T,
                )),                                                                                                                                                                       
            1025        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_JIN_ID_JEZYK, Model::COLUMN_JIN_ID_JEZYK => ID_JEZYK_ANGIELSKI,
                self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_MULTIPLE_INFO, self::FIELD_QUES_CALLBACK => array('ParserStartPraca', 'getLanguage'), 
                self::FIELD_QUES_MAPPING => array(
                    3207 => ID_POZIOM_PODSTAWOWY,
                    3208 => ID_POZIOM_SREDNI,
                    3209 => ID_POZIOM_DOBRY,
                    3210 => ID_POZIOM_BARDZO_DOBRY,
                )),
            1026        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_JIN_ID_JEZYK, Model::COLUMN_JIN_ID_JEZYK => ID_JEZYK_HOLENDERSKI,
                self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_MULTIPLE_INFO, self::FIELD_QUES_CALLBACK => array('ParserStartPraca', 'getLanguage'), 
                self::FIELD_QUES_MAPPING => array(
                    3212 => ID_POZIOM_PODSTAWOWY,
                    3213 => ID_POZIOM_SREDNI,
                    3214 => ID_POZIOM_DOBRY,
                    3215 => ID_POZIOM_BARDZO_DOBRY,
                )),
            1027        => array(self::FIELD_QUES_DIN_COLUMN => Model::COLUMN_JIN_ID_JEZYK, Model::COLUMN_JIN_ID_JEZYK => ID_JEZYK_NIEMIECKI,
                self::FIELD_QUES_INFO_TYPE => self::FIELD_QUES_TYPE_MULTIPLE_INFO, self::FIELD_QUES_CALLBACK => array('ParserStartPraca', 'getLanguage'), 
                self::FIELD_QUES_MAPPING => array(
                    3217 => ID_POZIOM_PODSTAWOWY,
                    3218 => ID_POZIOM_SREDNI,
                    3219 => ID_POZIOM_DOBRY,
                    3220 => ID_POZIOM_BARDZO_DOBRY,
                )),
        );
        
        public function __construct($lastId) {
            
            $this->oldLastId = $lastId;
        }
        
        public function getDataList($receivedData) {
            
            $dataXml = simplexml_load_string($receivedData);
            
            if (is_null($dataXml->{self::TAG_RESULT}) || empty($dataXml->children()->{self::TAG_PEOPLE}))
                return array();
                
            $people = $dataXml->children()->children();
            $result = array();
            $lastId = $this->oldLastId;
            
            foreach($people as $person) {
                
                $row = array();
                $attrs = $person->attributes();
                if (isset($attrs[self::ATTRIBUTE_ID])) {
                    
                    if ($lastId < $attrs[self::ATTRIBUTE_ID])
                        $lastId = (int)$attrs[self::ATTRIBUTE_ID];
                        
                    if ($lastId <= $this->oldLastId)
                        continue;
                } else {
                    
                    //in case ids did not show up frequently continue will be harmfull - we will loose those records
                    //it is also not severe enough to stop the flow, so just log an incident
                    LogManager::log(LOG_WARNING, 'missing id for startpraca record '.trim(iconv('UTF-8', 'ISO-8859-2', (string)$person->surname)));
                }
                
                
                foreach ($this->colsMapping as $localKey => $dbKeyData) {
                    
                    $dbKey = $dbKeyData[self::FIELD_DIN_COLUMN];
                    $dbKeyMaxLength = $dbKeyData[self::FIELD_DIN_COLUMN_MAXLENGTH];
                    $row[$dbKey] = substr(trim(iconv('UTF-8', 'ISO-8859-2', (string)$person->{$localKey})), 0, $dbKeyMaxLength);
                }
                
                if (empty($row[Model::COLUMN_DIN_ZAWOD])) {
                    
                    $row[Model::COLUMN_DIN_ID_ZAWOD] = 1;
                }

                //contacts
                if (!is_null($person->contacts)) {
                    
                    $contacts = $person->contacts->children();
                    
                    foreach ($contacts as $contact) {
                        
                        $attrs = $contact->attributes();

                        if (isset($attrs[self::ATTRIBUTE_TYPE])) {
                            
                            $type = (string)$attrs[self::ATTRIBUTE_TYPE];
                            if (isset($this->attrColsMapping[$type])) {
                                
                                $contactCandidate = $this->getLastDigits((string)$contact, 9);
                                if ($contactCandidate)
                                    $row[$this->attrColsMapping[$type]] = $contactCandidate;
                            }
                        }
                    }
                }
                
                // cv url to metadata
                $cvUrl = !empty($person->cvUrl) ? (string)$person->cvUrl : null;
                
                if ($cvUrl && preg_match('/^\/fileDL\.php\?file=([\d]{1,})$/', $cvUrl)) {
                    
                    $row[Model::COLUMN_MDI_DANE] = array('StartPraca_'.Model::COLUMN_MD_DANE_CVURL => STARTPRACA_REQUEST_URL.$cvUrl);
                }
                
                // add each record appriopriate source here
                $row[Model::COLUMN_DIN_SOURCE] = self::STARTPRACA_SOURCE;
                $row[Model::COLUMN_DIN_ID_ZRODLO] = self::STARTPRACA_INFO_SOURCE_ID;
                $row[Model::COLUMN_DIN_ID_WYKSZTALCENIE] = ($row[Model::COLUMN_DIN_ID_WYKSZTALCENIE] > WYKSZTALCENIE_ID_WYZSZE) ? 
                    WYKSZTALCENIE_ID_GIMNAZJALNE : (int)$row[Model::COLUMN_DIN_ID_WYKSZTALCENIE];
                    
                /// extras test
                if (!empty($person->extras)) {
                    
                    if (!empty($person->extras->test)) {
                        
                        $tests = $person->extras->test->children();
                        foreach ($tests as $test) {
                            
                            $attrs = $test->attributes();
                            
                            $questionId = (int)$attrs[self::ATTRIBUTE_QUESTION];
                            $questionValue = (string)$test;
                            $questionValue = trim(iconv('UTF-8', 'ISO-8859-2', $questionValue));
                            
                            if (isset($this->questionsMapping[$questionId])) {
                                
                                $mapping = $this->questionsMapping[$questionId];
                                $column = $mapping[self::FIELD_QUES_DIN_COLUMN];
                                $infoType = $mapping[self::FIELD_QUES_INFO_TYPE]; //single info, like street, or multiple, like drv license
                                
                                if (isset($mapping[self::FIELD_QUES_MAPPING])) {
                                    // value comes from our mapping of their dict values
                                    $questionValue = isset($mapping[self::FIELD_QUES_MAPPING][$questionValue]) ? $mapping[self::FIELD_QUES_MAPPING][$questionValue] : null;
                                } else if (isset($mapping[self::FIELD_QUES_VALIDATION])) {
                                    // verifying if the data is correct    
                                    $questionValue = call_user_func($mapping[self::FIELD_QUES_VALIDATION], $questionValue);
                                }
                                
                                if (isset($mapping[self::FIELD_QUES_CALLBACK])) {
                                    // a calback exists that formats the data right
                                    $row[$column] = call_user_func($mapping[self::FIELD_QUES_CALLBACK], $mapping, isset($row[$column]) ? $row[$column] : null, $questionId, $questionValue);
                                } else if (null !== $questionValue) {

                                    if ($infoType == self::FIELD_QUES_TYPE_MULTIPLE_INFO) {
                                        
                                        if (isset($row[$column])) {
                                            
                                            $row[$column][] = $questionValue;
                                        } else {
                                            
                                            $row[$column] = array($questionValue);
                                        }
                                    } else {
                                        
                                        $row[$column] = $questionValue;
                                    }
                                }
                                    
                                //var_dump($column, $questionValue, $row[$column]);
                                //echo "\n\n";
                            }
                        }
                    }
                }
                
                //var_dump($row);
                $result[] = $row;
            }
            
            $this->newLastId = $lastId;
            
            return $result;
        }
        
        public function importSuccessfull () {
            
            //save new last id
            $bllRemSources = new BLLRemoteSourcesStats();
            if ($this->oldLastId < $this->newLastId)
                return $bllRemSources->set(BLLRemoteSourcesStats::SOURCE_STARTPRACA, BLLRemoteSourcesStats::FIELD_LAST_ID, $this->newLastId);
            return true;
        }
        
        
        ///private parser specific validatiors
        
        private function getLastDigits ($subject, $digitsNumber) {
            
            $stringLength = strlen($subject);
            if ($stringLength == 0 || !$subject) {
                
                return null;
            }
            
            $result = '';
            
            for ($i = ($stringLength - 1); $i >= 0; $i--) {
                
                $candidate = ord($subject[$i]);
                
                if ($candidate >= 48 && $candidate <= 57) {
                    
                    $result .= $subject[$i];
                }
                
                if (strlen($result) >= $digitsNumber)
                    break;
            }
            
            if (strlen($result) < $digitsNumber)
                return null;
                
            return strrev($result);
        }
        
        protected static function getLanguage ($mapping, $currentResult, $questionId, $questionValue) {
            
            if (null === $currentResult)
                $currentResult = array();
                
            $currentResult[] = array(Model::COLUMN_JIN_ID_JEZYK => $mapping[Model::COLUMN_JIN_ID_JEZYK], Model::COLUMN_JIN_ID_POZIOM => $questionValue);
            
            return $currentResult;
        }
    }
    
    class StartpracaValidations 
    {
        public static function validateWeeksCount($weeksCount) {
            
            if (true !== ValidationUtils::validateInt($weeksCount) || $weeksCount < 0)
                return 0;
                
            return (int)$weeksCount > 99 ? 99 : (int)$weeksCount;
        }
        
        public static function validateHeight($height) {
            
            if (true !== ValidationUtils::validateInt($height) || $height < 0)
                return 0;
                
            return (int)$height > 250 ? 0 : (int)$height;
        }
        
        public static function validateDepartureDate ($date) {
            
            if (true !== ValidationUtils::validateDateFuture($date))
                return null;
                
            return $date;
        }
        
        public static function validatePostCode($code) {
            
            $_code = (int)$code;
            if (strlen($_code) == 5) {
                
                return substr($_code, 0, 2) . '-' . substr($_code, 2);
            }
            
            if (strlen($code) == 6) {
                
                return $code;
            }
       
            return null;
        }
    }