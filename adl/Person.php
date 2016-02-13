<?php
    require_once 'adl/Adl.php';
    require_once 'adl/Candidate.php';
    require_once 'bll/BLLDaneOsobowe.php';
    require_once 'bll/BLLZadaniaDnia.php';
    require_once 'bll/BLLDodatkoweOsoby.php';
    require_once 'bll/BLLScans.php';
    require_once 'bll/BLLJarograf.php';
    
    class Person extends Adl {
        
        //potential full data collection fields
        const FIELD_PHONES                      = 'phones';
        const FIELD_EXTRA_PHONES                = 'extraPhones';
        const FIELD_CELL                        = 'cell';
        const FIELD_EMAIL                       = 'email';
        const FIELD_LANGUAGES                   = 'languages';
        const FIELD_FORMER_EMPLOYERS            = 'getFormerEmployers';
        
        const COMPENSATION_TYPE_SKILLS             = 'skills';
        const COMPENSATION_TYPE_DRIVING_LICENSE    = 'drivingLicense';
        
        protected $compensationTypes2BllResp = array(
            Person::COMPENSATION_TYPE_SKILLS              => 'setSkillsList',
            Person::COMPENSATION_TYPE_DRIVING_LICENSE     => 'setDrivingLicenseList',
        );
        
        protected $personId;
        protected $candidateId;
        protected $bllDaneDodatkowe;
        protected $bllZadaniaDnia;
        protected $bllDodatkoweOsoby;
        protected $bllScans;
        protected $bllJarograf;
        
        //personId should come as validated int from view
        public function __construct($personId) {
            
            $this->personId = (int)$personId;
            parent::__construct(new BLLDaneOsobowe());
            //TODO person existence check is a good idea
        }
        
        /**
        * @desc get additional data logic
        * @return BLLDaneDodatkowe additional data logic
        */
        public function getLogicDaneDodatkowe () {
            
            if ($this->bllDaneDodatkowe == null)
                $this->bllDaneDodatkowe = new BLLDaneDodatkowe(false);
            
            return $this->bllDaneDodatkowe; //type BLLDaneDodatkowe
        }
        
        /**
         * @var BLLZadaniaDnia
         */
        public function getLogicZadaniaDnia () {
            if ($this->bllZadaniaDnia == null)
                $this->bllZadaniaDnia = new BLLZadaniaDnia();
            
            return $this->bllZadaniaDnia;
        }
        
        /**
         * @var BLLDodatkoweOsoby
         */
        public function getLogicDodatkoweOsoby () {
            if ($this->bllDodatkoweOsoby == null)
                $this->bllDodatkoweOsoby = new BLLDodatkoweOsoby();
        
            return $this->bllDodatkoweOsoby;
        }
        
        /**
         * @var BLLScans
         */
        public function getLogicScans () {
            if ($this->bllScans == null)
                $this->bllScans = new BLLScans();
        
            return $this->bllScans;
        }
        
        /**
         * @var BLLJarograf
         */
        public function getLogicJarograf () {
            if ($this->bllJarograf == null)
                $this->bllJarograf = new BLLJarograf();
        
            return $this->bllJarograf;
        }
        
        public function getPersonId () {
        	
        	return $this->personId;
        }
        
        public function setCandidateId ($candidateId) {
            
            $this->candidateId = $candidateId;
        }
        
        public function getPersonData () {
            
            $this->data = $this->logic->getEditData($this->personId);
            return $this->data;
        }
        
        public function setPerson ($personData, $addContact = true) {
            
            try {
                                                                     
                $this->personId = $this->logic->setPerson($personData);
                
                if ($this->candidateId) {
                    
                    /// pass the db data over - languages, driving licenses etc
                    $this->logic->setPersonCandidateExtraData($this->personId, $this->candidateId);
                }
            } catch (LogicConflictDataException $e) {
                
                $this->personId = $e->getInnerException()->getConflictId();
                throw $e;
            }
            
            if ($addContact) {
                
                $this->logic->setContact($this->personId, $personData[Model::COLUMN_KON_ID_KONSULTANT], $this->today);
            }
            
            return $this->personId;
        }
        
        public function deletePerson () {
            
            return $this->logic->deletePerson($this->personId);
        }
        
        //
        public function setPersonFromCandidate (Candidate $candidate, $userId, $sendWelcomeSms = false) {
                        
            $welcomeSmsData = null;
            // test code, qualification query
            if (true === $sendWelcomeSms) {
                
                $welcomeSmsData = $candidate->getWelcomeSmsData();
            }
            
            $data = $candidate->getPersonData();
            // data - status nowy
            $data[Model::RESULT_FIELD_DATA][Model::COLUMN_STT_ID_STATUS] = ID_STATUS_NOWY;
            
            $this->personId = $this->logic->setPersonCandidateData($candidate->getCandidateId(), $data, $userId, $welcomeSmsData);
            
            return $this->personId;
        }
        
        public function IsSmsSent () {
            
            return $this->logic->IsSmsSent();
        }
        
        public function getSmsHistory() {
            return $this->logic->getSmsHistory($this->personId);
        }


        public function updatePersonCandidateData ($candidateId, $changeCols) {
            
        	$colsApproved = array();
        	foreach ($changeCols as $colName => $colValue) {
        		
        		$colValue = (int)$colValue;
        		if ($colValue == BLLDaneOsobowe::VALUE_CHANGE_COL)
        			$colsApproved[] = $colName;
        	}
        	
        	if (count($colsApproved)) {
        		
	            $result = $this->logic->updatePersonCandidateData($this->personId, $candidateId, $colsApproved);
	            if ($result)
	                return $this->getPersonData();
        	}
                
            return false;
        }
        
        public function getPhones () {
            
            if (isset($this->extraData[self::FIELD_PHONES]))
                return $this->extraData[self::FIELD_PHONES];
                
            $phones = $this->logic->getPhones($this->personId);
            if ($phones[Model::RESULT_FIELD_ROWS_COUNT] === 0)
                $this->extraData[self::FIELD_PHONES] = null;
            else
                $this->extraData[self::FIELD_PHONES] = $phones[Model::RESULT_FIELD_DATA];
            
            return $this->extraData[self::FIELD_PHONES];
        }
        
        public function setPhone ($phone, $rowId = null, $allowDelete = false) {
            
            $phone = (int)$phone;
            
            $result = $this->logic->setPhone($this->personId, $phone, $rowId, $allowDelete);
            if ($result)
                $this->getPhones();
                
            return $result;
        }
        
        public function getExtraPhones () {
            
            if (isset($this->extraData[self::FIELD_EXTRA_PHONES]))
                return $this->extraData[self::FIELD_EXTRA_PHONES];
                
            $phones = $this->logic->getExtraPhones($this->personId);
            if ($phones[Model::RESULT_FIELD_ROWS_COUNT] === 0)
                $this->extraData[self::FIELD_EXTRA_PHONES] = null;
            else
                $this->extraData[self::FIELD_EXTRA_PHONES] = $phones[Model::RESULT_FIELD_DATA];
            
            return $this->extraData[self::FIELD_EXTRA_PHONES];
        }
        
        public function setExtraPhone ($phone, $rowId = null, $allowDelete = false) {
            
            $result = $this->logic->setExtraPhone($this->personId, $phone, $rowId, $allowDelete);
            if ($result)
                $this->getExtraPhones();
                
            return $result;
        }
        
        public function getCell () {
            
            if (isset($this->extraData[self::FIELD_CELL]))
                return $this->extraData[self::FIELD_CELL];
            
            $cells = $this->logic->getCellPhone($this->personId);
            if ($cells === null)
                $this->extraData[self::FIELD_CELL] = null;
            else
                $this->extraData[self::FIELD_CELL] = $cells[Model::RESULT_FIELD_DATA][0];
            
            return $this->extraData[self::FIELD_CELL];
        }
        
        public function setCell ($newCell, $currentCell = null, $allowDelete = false) {
            
            $newCell = (int)$newCell;
            
            $result = $this->logic->setCell($this->personId, $newCell, $currentCell, $allowDelete);
            if ($result)
                $this->getCell();
                
            return $result;
        }
        
        public function getEmail () {
            
            if (isset($this->extraData[self::FIELD_EMAIL]))
                return $this->extraData[self::FIELD_EMAIL];
            
            $email = $this->logic->getEmail($this->personId);
            if ($email === null)
                $this->extraData[self::FIELD_EMAIL] = null;
            else
                $this->extraData[self::FIELD_EMAIL] = $email[Model::RESULT_FIELD_DATA][0];
            
            return $this->extraData[self::FIELD_EMAIL];
        }
        
        public function setEmail ($newEmail, $currentEmail = null, $allowDelete = false) {
                       
            $result = $this->logic->setEmail($this->personId, $newEmail, $currentEmail, $allowDelete);
            if ($result)
                $this->getEmail();
                
            return $result;
        }
        
        public function getLanguages () {
            
            if (isset($this->extraData[self::FIELD_LANGUAGES]))
                return $this->extraData[self::FIELD_LANGUAGES];
                
            $languages = $this->logic->getLanguages($this->personId);
            if ($languages === null)
                $this->extraData[self::FIELD_LANGUAGES] = null;
            else
                $this->extraData[self::FIELD_LANGUAGES] = $languages[Model::RESULT_FIELD_DATA];
            
            return $this->extraData[self::FIELD_LANGUAGES];
        }
        
        /**
        * @desc Set person languages with optional confirmer for a set of at least one lang
        * @param array languages
        */
        public function setLanguages ($languages) {
            
            return $this->logic->setLanguages($this->personId, $languages);
        }
        
        /**
        * @desc Set a data set compensation (i.e. add 2 new values to a set of values)
        * @param string compensation type
        * @param int personId
        * @param array compensation ids
        */
        public function setCompensation ($compensationType, $personId, $compensationIds) {
            
            if (!isset($this->compensationTypes2BllResp[$compensationType]))
                throw new LogicBadDataException('Asked for non existing compensation type: '.$compensationType.' in setCompensation');
                
            $result = $this->logic->{$this->compensationTypes2BllResp[$compensationType]}($personId, $compensationIds);
            return $result;
        }
        
        public function getFormerEmployers () {
            
            if (isset($this->extraData[self::FIELD_FORMER_EMPLOYERS]))
                return $this->extraData[self::FIELD_FORMER_EMPLOYERS];
                        
            $result = $this->logic->getFormerEmployers($this->personId); //getExtraData(self::FIELD_FORMER_EMPLOYERS, array(($this->personId)));
            
            if (isset($result[Model::RESULT_FIELD_DATA]) && sizeof($result[Model::RESULT_FIELD_DATA])) {
                
                $dataArray = array();
                
                foreach ($result[Model::RESULT_FIELD_DATA] as $row) {
                    
                    $dataArray[$row[Model::COLUMN_PPR_ID_WIERSZ]] = $row;
                }
                
                $result[Model::RESULT_FIELD_DATA] = $dataArray;
            }
            
            $this->extraData[self::FIELD_FORMER_EMPLOYERS] = $result;
            
            return $this->extraData[self::FIELD_FORMER_EMPLOYERS];
        }
        
        public function getFormerEmployer ($rowId) {
            
            if (!isset($this->extraData[self::FIELD_FORMER_EMPLOYERS]))
                $this->getFormerEmployers();
                
            if (isset($this->extraData[self::FIELD_FORMER_EMPLOYERS][Model::RESULT_FIELD_DATA][$rowId]))
                return $this->extraData[self::FIELD_FORMER_EMPLOYERS][Model::RESULT_FIELD_DATA][$rowId];
                
            return null;
        }
        
        public function setFormerEmployer ($country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId, $currentId = null) {
            
            return $this->logic->setFormerEmployer($this->personId, $country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId, $currentId);
        }
        
        public function deleteFormerEmployer ($empId) {
            
            return $this->logic->deleteFormerEmployer($this->personId, $empId);
        }
        
        /**
        * @desc set former employer from db candidates data
        * @param array newEmploymentsList - one or more ids to be copied to employments list
        * @param optional int currentEmploymentId - for data replace for one employment
        */
        public function setFormerEmployerFromCandidate ($newEmployment, $currentEmploymentId = null) {
            
            return $this->logic->setFormerEmployerFromCandidate($this->personId, $newEmployment, $currentEmploymentId);
        }
        
        public function deleteSkill ($skillId) {
            
            return $this->logic->deleteSkill($this->personId, $skillId);
        }
        
        public function deleteDrivingLicense ($rowId) {
            
            return $this->logic->deleteDrivingLicense($this->personId, $rowId);
        }
        
        /**
        * @desc Get info for user input altering. This will not work from what I now know
        * TODO FIXME either throw out or make it work
        */
        public function getAdditionalInfo () {
                        
            return $this->getExtraData('getAdditionalInfo', array(($this->personId)));
        }
        
        public function setAdditionalInfoFromCandidate ($candidateInfoId, $personInfoId = null) {
            
            $result = $this->logic->setAdditionalInfoFromCandidate($this->personId, $candidateInfoId, $personInfoId);
            if ($result)
                $result = $this->getAdditionalInfo();
                
            return $result;
        }
        
        public function getNextEmployer () {
            
            return $this->logic->getNextEmployer($this->personId);
        }
    }
    