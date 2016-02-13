<?php
    require_once 'adl/Adl.php';
    require_once 'bll/BLLDaneInternet.php';
    
    class Candidate extends Adl {
        
        const COMPENSATION_TYPE_SKILLS             = 'skills';
        const COMPENSATION_TYPE_DRIVING_LICENSE    = 'drivingLicense';
        
        const FIELD_LANGUAGES                      = 'languages';
        const FIELD_FORMER_EMPLOYERS               = 'getFormerEmployers';
        
        protected $compensationTypes2BllResp = array(
            Candidate::COMPENSATION_TYPE_SKILLS              => 'getSkillsCompensation',
            Candidate::COMPENSATION_TYPE_DRIVING_LICENSE     => 'getDrivingLicenseCompensation',
        );

        protected $candidateId;
        protected $bllDaneDodatkowe;
        
        //personId should come as validated int from view
        public function __construct($candidateId) {
            
            //parent::__construct();
            $this->candidateId = (int)$candidateId;
            parent::__construct(new BLLDaneInternet());
        }
        
        /**
        * @desc get additional data logic
        * @return BLLDaneDodatkowe additional data logic
        */
        public function getLogicDaneDodatkowe () {
            
            if ($this->bllDaneDodatkowe == null)
                $this->bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            
            return $this->bllDaneDodatkowe; //type BLLDaneDodatkowe
        }
        
        public function getPersonData () {
            
            if (is_array($this->data))
                return $this->data;
                
            $result = $this->logic->get($this->candidateId);
            
            if ($result[Model::RESULT_FIELD_ROWS_COUNT] > 0)
                $this->data = $result;
            
            return $this->data;
        }
        
        public function setPersonData ($dataList, $extraData = array()) {
            
            $newId = $this->logic->set($dataList, $extraData);
             if ($newId > 0) {
                 
                 $this->candidateId = $newId;
                 return $this->candidateId;
             }
        }
        
        public function deletePersonData () {
            
            $result = $this->logic->delete($this->candidateId);
            if ($result) {
                
                $this->candidateId = null;
                $this->data = null;
            }
            
            return $result;
        }
        
        public function getCandidateId () {
        	
        	return $this->candidateId;
        }
         
        /**
        * @desc Get candidate phone, there can be at most one
        */
        public function getCandidatePhone () {
            
            if (!$this->data)
                $this->getPersonData();
                
            if ($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_TELEFON])
                return $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_TELEFON];
                
            return null;
        }
        
        public function getCandidateCell () {
            
            if (!$this->data)
                $this->getPersonData();
                
            if ($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA])
                return $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA];
                
            return null;
        }

        public function getCandidateExtraPhone () {
            
            if (!$this->data)
                $this->getPersonData();
                
            if ($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_INNY_TEL])
                return $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_INNY_TEL];
                
            return null;
        }
        
        public function getCandidateEmail () {
            
            if (!$this->data)
                $this->getPersonData();
                
            if ($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_EMAIL])
                return $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_EMAIL];
                
            return null;
        }
        
        public function getCompensation ($compensationType, $personId) {
            
            if (!isset($this->compensationTypes2BllResp[$compensationType]))
                throw new LogicBadDataException('Asked for non existing compensation type: '.$compensationType.' in getCompensation');
            
            $this->data[$compensationType] = null;    
            $result = $this->logic->{$this->compensationTypes2BllResp[$compensationType]}($personId, $this->candidateId);
            if ($result) {
                $this->data[$compensationType] = $result[Model::RESULT_FIELD_DATA];
            }
            
            return $this->data[$compensationType];
        }
        
        public function getLanguagesCompensation ($personId) {
            
            if (!isset($this->extraData[self::FIELD_LANGUAGES])) {
                $result = $this->logic->getLanguagesCompensation($personId, $this->candidateId);
                if (!is_null($result))
                    $this->extraData[self::FIELD_LANGUAGES] = $result[Model::RESULT_FIELD_DATA];
                else
                    $this->extraData[self::FIELD_LANGUAGES] = null;
            }
            
            return $this->extraData[self::FIELD_LANGUAGES];
        }
        
        public function getFormerEmployers () {
                        
            return $this->getExtraData(self::FIELD_FORMER_EMPLOYERS, array(($this->candidateId)));
        }
        
        public function getAdditionalInfo () {
                        
            return $this->getExtraData('getAdditionalInfo', array(($this->candidateId)));
        }
        
        public function getMetaData () {
            
            $metadata = $this->getLogicDaneDodatkowe()->getMetaData($this->candidateId);
            
            //TODO use and add if above ?
            //if ($metadata)
            //    $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE] = $metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE];
            
            return $metadata[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE];
        }
        
        public function getWelcomeSmsData() {
            
            if (!isset($this->data[Model::COLUMN_DIN_KOD])) {
                
                $this->getPersonData();
            }
            
            $message = $this->logic->getWelcomeSmsMessage($this->data[Model::RESULT_FIELD_DATA]);
            
            if ($message && !empty($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA]) && strlen($this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA]) == 9) {
                
                // those data come from db and therefore are considered valid
                // phone, message
                return array(Model::COLUMN_DIN_KOMORKA => $this->data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA], Model::COLUMN_SKA_TRESC => $message);
            }
            
            return null;
        }
    }
    