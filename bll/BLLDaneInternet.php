<?php
    require_once 'dal/DALDaneInternet.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/Logic.php';
    
    class BLLDaneInternet extends Logic {

        const FIELD_ID       = 'id';
        
        protected $extraDataMapping = array();
        
        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALDaneInternet();
            
            $this->extraDataMapping = array(
            
                Model::TABLE_PRAWO_JAZDY_INTERNET           =>      array($this->dataAccess, 'setDrivingLicenses'),
                Model::TABLE_UMIEJETNOSCI_OSOB_INTERNET     =>      array($this->dataAccess, 'setSkills'),
                Model::TABLE_JEZYKI_INTERNET                =>      array($this->dataAccess, 'setLanguages'),
                Model::TABLE_POPRZEDNI_PRAC_ANKIETA         =>      array($this->dataAccess, 'setFormerEmployments'),
            );
        }
        
        public function get ($candidateId) {

            try {
                $result = $this->dataAccess->get($candidateId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in dane internet get', '', $e);
            }
            
            if ($result[Model::RESULT_FIELD_ROWS_COUNT] < 1)
                throw new LogicNotFoundException('Internet data for '.$this->personId.' not found');
                
            $dataRow = $result[Model::RESULT_FIELD_DATA];
            
            $daneDodatkowe = new BLLDaneDodatkowe(true);
            $additionalData = $daneDodatkowe->getSimpleAdditionalInfo($candidateId);
            //$metadata = $daneDodatkowe->getMetaData($candidateId);
            $dataRow = array_merge($dataRow, $additionalData[Model::RESULT_FIELD_DATA]);

            if (!isset($dataRow[self::FIELD_ID]))
                throw new LogicServerErrorException('Internet data for '.$this->personId.' are invalid '.var_export($dataRow, true));
                
            $result[Model::RESULT_FIELD_DATA] = $dataRow;
            
            return $result;
        }
        
        public function set ($dataList, $extraData = array()) {
            
            try {
                
                $candidateId = $this->dataAccess->set($dataList);
                $daneDodatkowe = new BLLDaneDodatkowe(true);
                $addResult = $daneDodatkowe->setAdditionalInfo($candidateId, $dataList);
                
                foreach ($extraData as $tableName => $data) {
                    
                    if (isset($this->extraDataMapping[$tableName])) {
                        
                        call_user_func($this->extraDataMapping[$tableName], $candidateId, $extraData[$tableName]);
                    }
                }

                //set meta data if any
                if (isset($dataList[Model::COLUMN_MDI_DANE])) {
                    
                    $daneDodatkowe->setMetaData($candidateId, $dataList[Model::COLUMN_MDI_DANE]);
                }
                
                if (isset($dataList[Model::COLUMN_PJI_ID_PRAWO_JAZDY])) {
                    
                    $this->dataAccess->setDrivingLicenses($candidateId, $dataList[Model::COLUMN_PJI_ID_PRAWO_JAZDY]);
                }
                
                if (isset($dataList[Model::COLUMN_JIN_ID_JEZYK])) {
                    // tak, indexowanie id jezyk listy jezykow to niezly kal
                    $this->dataAccess->setLanguages($candidateId, $dataList[Model::COLUMN_JIN_ID_JEZYK]);
                }
                
                return ($addResult && $candidateId) ? $candidateId : $addResult;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in set', '', $e);
            }
        }
        
        public function delete ($candidateId) {
            
            try {
                return $this->dataAccess->delete($candidateId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in delete', '', $e);
            }
        }
        
        public function getSkillsCompensation ($personId, $candidateId) {
            
            try {
                return $this->dataAccess->getSkillsCompensation($personId, $candidateId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSkillsCompensation', '', $e);
            }
        }
        
        public function getDrivingLicenseCompensation ($personId, $candidateId) {
            
            try {
                return $this->dataAccess->getDrivingLicenseCompensation($personId, $candidateId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSkillsCompensation', '', $e);
            }
        }
        
        public function getLanguagesCompensation ($personId, $candidateId) {
            
            try {
                $languages = $this->dataAccess->getLanguagesCompensation($personId, $candidateId);
                if (is_null($languages))
                    return null;
                    
                $languages[Model::RESULT_FIELD_DATA] = $this->indexResultById($languages[Model::RESULT_FIELD_DATA], Model::COLUMN_JIN_ID_JEZYK);
                
                return $languages;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getLanguagesCompensation', '', $e);
            }
        }
        
        /**
        * @desc get a list of person employment declared history
        * @param int candidateId
        * @return array employmentHistory or null
        */
        public function getFormerEmployers ($candidateId) {
            
            try {
                return $this->dataAccess->getFormerEmployers($candidateId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getFormerEmployers', '', $e);
            }
        }
        
        /**
        * @desc Get sms message to send for registered candidate
        */
        public function getWelcomeSmsMessage($data) {
            
            // check global allowance setting
            $bllDaneSlownikowe = new BLLDaneSlownikowe() ;
            $sendSms = $bllDaneSlownikowe->getSendSmsAdmSetting();
            
            if (true === $sendSms) {

                // check if sms message applies, get custom rules for candidate's origin
                // check custom rules
                return $this->dataAccess->getSmsMessage($data[Model::COLUMN_DIN_ID], $data[Model::COLUMN_DIN_KOD]);
            }
            
            return null;
        }
        
        public function getSmsMessages () {
            
            try {
                return $this->dataAccess->getSmsMessages();
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSmsMessages', '', $e);
            }
        }
        
        public function setSmsMessage ($id, $message) {
            
            try {
                return $this->dataAccess->setSmsMessage($id, $message);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setSmsMessage', '', $e);
            }
        }
    }