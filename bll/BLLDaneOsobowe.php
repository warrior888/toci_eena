<?php
    require_once 'dal/DALDaneOsobowe.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/BLLDaneInternet.php';
    require_once 'bll/BLLUprawnienia.php';
    require_once 'bll/Logic.php';
    require_once 'bll/mail.php';
    require_once 'bll/queries.php';
    require_once 'wsparcie/sms.php';
    
    class BLLDaneOsobowe extends Logic {

        const FIELD_ID       = 'id';
        
        const VALUE_CHANGE_COL   = 2;
        const EMP_DATA_SEPARATOR = '/';
        
        const MIN_SMS_LENGTH    = 40;
        
        protected $candidateColsMapping = array (
            Model::COLUMN_DOS_IMIE               => Model::COLUMN_DOS_ID_IMIE,
            Model::COLUMN_DOS_NAZWISKO           => Model::COLUMN_DOS_NAZWISKO,
            Model::COLUMN_DOS_PLEC               => Model::COLUMN_DOS_ID_PLEC,
            Model::COLUMN_DOS_DATA_URODZENIA     => Model::COLUMN_DOS_DATA_URODZENIA,
            Model::COLUMN_DOS_ULICA              => Model::COLUMN_DOS_ULICA,
            Model::COLUMN_DOS_KOD                => Model::COLUMN_DOS_KOD,
            Model::COLUMN_DOS_WYKSZTALCENIE      => Model::COLUMN_DOS_ID_WYKSZTALCENIE,
            Model::COLUMN_DOS_ZAWOD              => Model::COLUMN_DOS_ID_ZAWOD,
            Model::COLUMN_DOS_CHARAKTER          => Model::COLUMN_DOS_ID_CHARAKTER,
            Model::COLUMN_DOS_DATA               => Model::COLUMN_DOS_DATA,
            Model::COLUMN_DOS_ILOSC_TYG          => Model::COLUMN_DOS_ILOSC_TYG,
            Model::COLUMN_DOS_ZRODLO             => Model::COLUMN_DOS_ID_ZRODLO,
            Model::COLUMN_DOS_MIEJSCOWOSC        => Model::COLUMN_DOS_ID_MIEJSCOWOSC,
            Model::COLUMN_DOS_MIEJSCOWOSC_UR     => Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR,
        );
        
        protected $smsSent = false;
        
        protected $bllUprawnienia;
        
        public function __construct() {
            
            parent::__construct();
            $this->dataAccess = new DALDaneOsobowe();
            $this->bllUprawnienia = new BLLUprawnienia();
        }
        
        public function getEditData ($personId) {

            $result = $this->dataAccess->getEditData($personId);
            if ($result[Model::RESULT_FIELD_ROWS_COUNT] < 1)
                throw new LogicNotFoundException('Data for '.$personId.' not found');
                    
            $dataRow = $result[Model::RESULT_FIELD_DATA];
            
            $daneDodatkowe = new BLLDaneDodatkowe(false);
            //additional info full data actually an overkill here
            //when doing update we query for current additional columns list, where we have all necessary metadata, and
            //it is anyway rather unavoidable. therefore either the getAdditionalInfo result should be simplified or simpler method 
            //used here
            $additionalData = $daneDodatkowe->getSimpleAdditionalInfo($personId);
            $dataRow = array_merge($dataRow, $additionalData[Model::RESULT_FIELD_DATA]);
            
            //this if should make no sense
            if (!isset($dataRow[self::FIELD_ID]))
                throw new LogicServerErrorException('Data for '.$personId.' are invalid '.var_export($dataRow, true));
            
            $result[Model::RESULT_FIELD_DATA] = $dataRow;
                    
            return $result;
        }
        
        public function get($personId) {
            
            try {
                return $this->dataAccess->get($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in get', '', $e);
            }
        }
        
        public function getSmsHistory ($personId) {

            $result = $this->dataAccess->getSmsHistory($personId);
            
            return $result;
        }
        
        //if update is only for status - skip person update, in case no data is for insert/update of person true is coming from dal
        /**
        * @desc Set person - add or update.
        */
        public function setPerson ($data) {
             
            try {

                $isUpdate = isset($data[Model::COLUMN_DOS_ID]);
                //currently we add no additional person info right away, more exactly we add no person info, however we do not add that for questioraire source ...
                $personId = $this->dataAccess->setPerson($data);
                $daneDodatkowe = new BLLDaneDodatkowe(false);
                //deleting any missing data when update - only when value explicitly sent as null, when not sent assuimng some partial comparison replaces
                $addResult = $daneDodatkowe->setAdditionalInfo($personId, $data);
                
                // set metadata if any comming along
                if (isset($data[Model::COLUMN_MDO_DANE])) {
                    
                    $daneDodatkowe->setMetaData($personId, $data[Model::COLUMN_MDO_DANE]);
                }

                if (false === $isUpdate) {
                    
                    // phones existence add/update logic
                    if (!empty($data[Model::COLUMN_DIN_TELEFON])) {
                    
                        $this->dataAccess->setPhone($personId, $data[Model::COLUMN_DIN_TELEFON]);
                    }
                    
                    if (!empty($data[Model::COLUMN_DIN_KOMORKA])) {
                    
                        $this->dataAccess->setCell($personId, $data[Model::COLUMN_DIN_KOMORKA]);
                    }
                    
                    if (!empty($data[Model::COLUMN_DIN_EMAIL])) {
                    
                        $this->dataAccess->setEmail($personId, $data[Model::COLUMN_DIN_EMAIL]);
                    }
                    
                    $data[BLLDaneDodatkowe::HAS_COMPANY] = BLLDaneDodatkowe::BOOL_FALSE;
                    $result = ($personId > 0) && $addResult && $this->dataAccess->setStatus($personId, $data[Model::COLUMN_STT_ID_STATUS]);
                    
                    return $result ? $personId : $result;
                } else {
                    
                    $result = ($personId > 0) && $addResult;
                    
                    if (isset($data[Model::COLUMN_STT_ID_STATUS])) {
                        
                        $result = $result && $this->dataAccess->setStatus($personId, $data[Model::COLUMN_STT_ID_STATUS]); 
                    }
                    
                    return $result ? $personId : $result;
                }

                
            } catch (DBConflictDataException $e) {
                throw new LogicConflictDataException('Duplicate setPerson operation attempt', '', $e);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setPerson', '', $e);
            }
        }
        
        public function deletePerson ($personId) {
            
            try {
                return $this->dataAccess->deletePerson($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in deletePerson', '', $e);
            }
        }
        
        /**
        * @desc Add a consultant - person contact entry to history, refresh latest contact
        */
        public function setContact ($personId, $consultantId, $date) {
            
            try {
                return $this->dataAccess->setContact($personId, $consultantId, $date);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in addContact', '', $e);
            }
        }
        
        public function getNextEmployer ($personId) {
            
            $result = $this->dataAccess->getEmployerInfo($personId);
            return $result;
        }
        
        public function setPersonCandidateData ($candidateId, $data, $consultantId, $welcomeSmsData = null) {
            
            $personId = $this->dataAccess->setPersonFromCandidate($candidateId, $consultantId);
            $this->dataAccess->setStatus($personId, $data[Model::RESULT_FIELD_DATA][Model::COLUMN_STT_ID_STATUS]);
            
            $this->setPersonCandidateExtraData($personId, $candidateId);
            $this->dataAccess->setAdditionalDataFromCandidate($personId, $candidateId);
            
            if (!empty($data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_TELEFON])) {
                    
                $this->dataAccess->setPhone($personId, $data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_TELEFON]);
            }
            
            if (!empty($data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA])) {
            
                $this->dataAccess->setCell($personId, $data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA]);
            }
            
            if (!empty($data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_INNY_TEL])) {
            
                $this->dataAccess->setExtraPhone($personId, $data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_INNY_TEL]);
            }
            
            if (!empty($data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_EMAIL])) {
            
                $this->dataAccess->setEmail($personId, $data[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_EMAIL]);
            }
            
            //kontakt ?
            
            // kwalifikacja do sms
            if (is_array($welcomeSmsData) && strlen($welcomeSmsData[Model::COLUMN_SKA_TRESC]) > self::MIN_SMS_LENGTH && strlen($welcomeSmsData[Model::COLUMN_DIN_KOMORKA]) == 9) {
                
                // call sms send logic
                $sms = new Sms();
                $sms->MasowySms(array($welcomeSmsData[Model::COLUMN_DIN_KOMORKA]), $welcomeSmsData[Model::COLUMN_SKA_TRESC]);
                // TODO powyzsza metoda ssie
                $this->smsSent = true;
            }
            
            // todo delete from candidate
            $bllDaneInternet = new BLLDaneInternet();
            $result = $bllDaneInternet->delete($candidateId);
            
            return $personId;
        }
        
        public function updatePersonCandidateData ($personId, $candidateId, $colsList) {

            if (sizeof($colsList) == 0)
                throw new LogicBadDataException('No columns to update listed on mapping, provided list: '.var_export($colsList, true), 'Nie zlecono ¿adnych zmian.');
                
            $modelColsMatch = array();
            foreach ($colsList as $colName) {
                
                if (isset($this->candidateColsMapping[$colName]))
                    $modelColsMatch[$colName] = $this->candidateColsMapping[$colName];
            }
            
            $personResult = true;
            if (sizeof($modelColsMatch)) {
                try {
                    $personResult = $this->dataAccess->updatePersonCandidateData($personId, $candidateId, $modelColsMatch);
                } catch (DBException $e) {
                    throw new LogicServerErrorException('['.__CLASS__.'] Data access error in updatePersonCandidateData', '', $e);
                }
            } 
            
            //intersect columns to make only additional remain
            // We call internet additional data for columns, as those columns are in question now
            $daneDodatkowe = new BLLDaneDodatkowe(true);
            $addColsDict = $daneDodatkowe->getAdditionalsDictList(true);
            
            $changedCols = array();
            $colsList = array_flip($colsList);
            
            foreach ($addColsDict as $key => $addCol) {
                
                if (isset($colsList[$addCol[Model::COLUMN_DICT_NAZWA]])) {
                    
                    $changedCols[$addCol[Model::COLUMN_DICT_NAZWA]] = $addCol[Model::COLUMN_DICT_NAZWA];
                }
            }
            
            if (sizeof($changedCols) > 0)
                $personResult = $personResult && $this->setAdditionalInfoFromCandidate($personId, $candidateId, $changedCols);
                
            return $personResult;
                
        }
        
        /**
        * @desc Copy chosen candidate info to person info table
        */
        public function setAdditionalInfoFromCandidate ($personId, $candidateId, $columnsList) {
            
            //get candidate data for columns, then choose modified ones
            $daneDodatkowe = new BLLDaneDodatkowe(true);
            $candidateAdditionalData = $daneDodatkowe->getSimpleAdditionalInfo($candidateId);
                        
            $updateData = array_intersect_key($candidateAdditionalData[Model::RESULT_FIELD_DATA], $columnsList);
                        
            //update chosen person data
            $daneDodatkoweOsoba = new BLLDaneDodatkowe(false);
            return $daneDodatkoweOsoba->setAdditionalInfo($personId, $updateData); 
            
            /*try {
                return $this->dataAccess->setAdditionalInfoFromCandidate($personId, $candidateInfoId, $personInfoId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setAdditionalInfoFromCandidate', '', $e);
            } */
        }
        
        public function getNameSurname ($personId) {
            
            try {
                return $this->dataAccess->getNameSurname($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getNameSurname', '', $e);
            }
        }
        
        public function getPhones ($personId) {
            
            try {
                return $this->dataAccess->getPhones($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getPhones', '', $e);
            }
        }
        
        public function setPhone ($personId, $phone, $id = null, $allowDelete = false) {
            
            try {
                if (true === $allowDelete && $phone < 1) {
                    
                    return $this->dataAccess->deletePhone($personId, $id);
                } else {
                
                    return $this->dataAccess->setPhone($personId, $phone, $id);
                }
            } catch (DBException $e) {//TODO query exc for constraint ?
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setPhone', '', $e);
            }
        }
        
        public function getExtraPhones ($personId) {
            
            try {
                return $this->dataAccess->getExtraPhones($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getPhones', '', $e);
            }
        }
        
        public function setExtraPhone ($personId, $phone, $id = null, $allowDelete = false) {
            
            try {
                if (true === $allowDelete && $phone < 1) {
                    
                    return $this->dataAccess->deleteExtraPhone($personId, $id);
                } else {
                
                    return $this->dataAccess->setExtraPhone($personId, $phone, $id);
                }
            } catch (DBException $e) {//TODO query exc for constraint ?
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setPhone', '', $e);
            }
        }
        
        public function getCellPhone ($personId) {
            
            try {
                return $this->dataAccess->getCellPhone($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getCell', '', $e);
            }
        }
        
        public function setCell ($personId, $phone, $oldCell = null, $allowDelete = false) {
            
            try {
                if (true === $allowDelete && $phone < 1) {
                    
                    return $this->dataAccess->deleteCell($personId);
                } else {
                    
                    return $this->dataAccess->setCell($personId, $phone, $oldCell);
                }
            } catch (DBException $e) {//TODO query exc for constraint ?
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setCell', '', $e);
            }
        }
        
        public function getEmail ($personId) {
            
            try {
                return $this->dataAccess->getEmail($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getEmail', '', $e);
            }
        }
        
        public function setEmail ($personId, $email, $oldEmail = null, $allowDelete = false) {
            
            try {
                if (true === $allowDelete && !$email) {
                    
                    return $this->dataAccess->deleteEmail($personId);
                } else {
                    
                    return $this->dataAccess->setEmail($personId, $email, $oldEmail);
                }
            } catch (DBException $e) {//TODO query exc for constraint ?
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setEmail', '', $e);
            }
        }
        
        public function getSkillsList ($personId) {
            
            try {
                $result = $this->dataAccess->getSkillsList($personId);
                $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                $hasSkills = $bllDaneDodatkowe->getAdditionalInfoByName($personId, BLLDaneDodatkowe::HAS_SKILLS);
                $result[Model::RESULT_FIELD_METADATA][BLLDaneDodatkowe::HAS_SKILLS] = 
                isset($hasSkills[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC]) ? 
                $hasSkills[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC] : null;
                
                return $result;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getSkillsList', '', $e);
            }
        }
        
        public function setSkillsList ($personId, $skillsList) {
            
            if (!is_array($skillsList) || !sizeof($skillsList))
                throw new LogicBadDataException('Empty skills list provided for set');
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                
            try {
                $result = $this->dataAccess->setSkillsList($personId, $skillsList);
                $addInfo = $bllDaneDodatkowe->setAdditionalInfoRow($personId, $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_SKILLS), BLLDaneDodatkowe::BOOL_TRUE);
                
                return ($result && $addInfo); 
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setSkillsList', '', $e);
            }
        }
        
        public function deleteSkill ($personId, $skillId) {
            
            try {
                return $this->dataAccess->deleteSkill($personId, $skillId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in deleteSkill', '', $e);
            }
        }
        
        public function getDrivingLicenses ($personId) {
            
            try {
                $result = $this->dataAccess->getDrivingLicenses($personId);
                $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                $hasLicense = $bllDaneDodatkowe->getAdditionalInfoByName($personId, BLLDaneDodatkowe::HAS_DRIVING_LICENSE);
                $result[Model::RESULT_FIELD_METADATA][BLLDaneDodatkowe::HAS_DRIVING_LICENSE] = 
                isset($hasLicense[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC]) ? 
                $hasLicense[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC] : null;
                
                return $result;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getDrivingLicenses', '', $e);
            }
        }
        
        public function setDrivingLicenseList ($personId, $licensesList) {
            
            if (!is_array($licensesList) || !sizeof($licensesList))
                throw new LogicBadDataException('Empty licenses list provided for set');
                
            $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                
            try {
                $result = $this->dataAccess->setDrivingLicenseList($personId, $licensesList);
                $addInfo = $bllDaneDodatkowe->setAdditionalInfoRow($personId, $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE), BLLDaneDodatkowe::BOOL_TRUE);
                
                return ($result && $addInfo);
            } catch (DBInvalidDataException $e) {
                throw new LogicBadDataException('['.__CLASS__.'] Data access wrong data error in setDrivingLicenseList', '', $e);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setDrivingLicenseList', '', $e);
            }
        }
        
        public function deleteDrivingLicense ($personId, $rowId) {
            
            try {
                return $this->dataAccess->deleteDrivingLicense($personId, $rowId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in deleteDrivingLicense', '', $e);
            }
        }
        
        public function getLanguages ($personId) {
            
            try {
                $languages = $this->dataAccess->getLanguages($personId);
                if (is_null($languages))
                    return null;

                $languages[Model::RESULT_FIELD_DATA] = $this->indexResultById($languages[Model::RESULT_FIELD_DATA], Model::COLUMN_ZNJ_ID_JEZYK);
                
                return $languages;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getLanguages', '', $e);
            }
        }
        
        /**
        * @desc Set language - add new or update. 
        * If update - remove confirmed entry (unless confirmedId provided)
        * set has language bool to true always
        * @param int personId
        * @param array languages
        */
        public function setLanguages ($personId, $languages) {
            
            //in theory we could implement a chain of responsibility transaction heap for set of db operations, that's why sp below
            try {
                
                return $this->dataAccess->setLanguages($personId, $languages);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setLanguage', '', $e);
            }
        }
        
        /**
        * @desc get a list of person employment declared history
        * @param int personId
        * @return array employmentHistory or null
        */
        public function getFormerEmployers ($personId) {
            
            try {
                $result = $this->dataAccess->getFormerEmployers($personId);
                $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                $hasEmp = $bllDaneDodatkowe->getAdditionalInfoByName($personId, BLLDaneDodatkowe::HAS_EMP_HISTORY);
                $result[Model::RESULT_FIELD_METADATA][BLLDaneDodatkowe::HAS_EMP_HISTORY] = 
                isset($hasEmp[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC]) ? 
                $hasEmp[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DDO_WARTOSC] : null;
                
                return $result;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getFormerEmployers', '', $e);
            }
        }
        
        /**
        * @desc Add or update former employer
        * @param int personId
        * @param string country
        * @param string city
        * @param string name of firm
        * @param string department
        * @param string position
        * @param string period
        * @param string agencyName
        * @param int occupation id
        * @param optional int existing id for update
        */
        public function setFormerEmployer ($personId, $country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId, $currentId = null) {
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
            $formerEmployer = implode(self::EMP_DATA_SEPARATOR, array($country, $city, $firmName, $department, $position, $period));
            
            try {    
                $result = $this->dataAccess->setFormerEmployer($personId, $formerEmployer, $country, $city, $firmName, $agencyName, $occupationId, $currentId);
                $addInfo = $bllDaneDodatkowe->setAdditionalInfoRow($personId, $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_EMP_HISTORY), BLLDaneDodatkowe::BOOL_TRUE);
                
                return ($result && $addInfo);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setLanguage', '', $e);
            }
        }
        
        public function deleteFormerEmployer ($personId, $empId) {
            
            try {
                return $this->dataAccess->deleteFormerEmployer($personId, $empId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in deleteFormerEmployer', '', $e);
            }
        }
        
        /**
        * @desc Set former employer from candidates data - add new row(s) or update
        * @param int personId
        * @param array newEmploymentsList
        * @param optional int currentEmploymentId
        */
        public function setFormerEmployerFromCandidate ($personId, $newEmployment, $currentEmploymentId = null) {

            try {
                
                    return $this->dataAccess->setFormerEmployerFromCandidate(
                        $newEmployment, 
                        (is_null($currentEmploymentId) ? $personId : $currentEmploymentId)
                    );
                
                //    return $this->dataAccess->setFormerEmployerFromCandidate($newEmployment, $currentEmploymentId);
                
            } catch (DBInvalidDataException $e) {
                throw new LogicBadDataException('['.__CLASS__.'] Data access wrong data error in setFormerEmployerFromCandidate', '', $e);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setFormerEmployerFromCandidate', '', $e);
            }
        }
        
        public function IsSmsSent () {
            
            return $this->smsSent;
        }
        
        public function setPersonCandidateExtraData ($personId, $candidateId) {
            
            $this->dataAccess->setLanguagesFromCandidate($personId, $candidateId);
            $this->dataAccess->setDrivingLicenseFromCandidate($personId, $candidateId);
            $this->dataAccess->setSkillsFromCandidate($personId, $candidateId);
            $this->dataAccess->setFormerEmploymentFromCandidate($personId, $candidateId);
        }
        
        public function sendWelcomeEmail ($personId) {
            
            // get person email or fail
            $emailData = $this->getEmail($personId);
            $email = $emailData[Model::RESULT_FIELD_DATA][0][Model::COLUMN_EMA_NAZWA];
            // get person with getNameSurname or get, we need cons id
            $person = $this->get($personId);
            
            $consultantData = $this->bllUprawnienia->getUser($person[Model::RESULT_FIELD_DATA][Model::COLUMN_DOS_ID_KONSULTANT]);
            
            $idFirmaFilia = $consultantData[Model::RESULT_FIELD_DATA][0][Model::COLUMN_UPR_ID_FIRMA_FILIA];
            // get the right src email address from firma_filia
            
            $firmaFilia = $this->bllUprawnienia->getFirmaFilia($idFirmaFilia);
            $emailOut = $firmaFilia[Model::RESULT_FIELD_DATA][0][Model::COLUMN_FIF_EMAIL];
            $mail = new MailSend();
            
            $mailContent =  'Witaj '.$person[Model::RESULT_FIELD_DATA][Model::COLUMN_DOS_IMIE].' '.$person[Model::RESULT_FIELD_DATA][Model::COLUMN_DOS_NAZWISKO].',

Bardzo dziêkujemy za przybycie na rozmowê kwalifikacyjn± i zaprezentowanie naszemu konsultantowi swojego doœwiadczenia zawodowego i kwalifikacji.
Odwiedzaj czêsto dzia³ Najnowsze oferty pracy na naszej stronie www.eena.pl. Je¶li zainteresowa³a Ciê praca na danym stanowisku i spe³niasz postawione wymagania, podpowiedz swoj± gotowo¶æ wyjazdu konsultantowi klikaj±c w przycisk Aplikuj. Tak± mo¿liwo¶æ maj± wy³±cznie kandydaci, którzy zaktualizowali w naszej bazie swój adres email i polski numer telefonu komórkowego.

Informujemy, ¿e bêdziemy mogli skontaktowaæ siê wy³±cznie z wybranymi kandydatami spe³niaj±cymi oczekiwania pracodawcy.

Wiêcej informacji na temat firmy E&A,  kultury organizacyjnej, a tak¿e wartoœci którymi siê kierujemy znajdziesz na stronie www.eena.pl oraz na ulotce dostêpnej w naszych biurach.
Ulotka w wersji elektronicznej dostêpna tutaj [http://www.eena.pl/posrednictwo_pracy.html]

Je¿eli wiadomoœæ trafi³a do katalogu spam, prosimy dodaæ adres e-mailowy do kategorii zaufanych nadawców by w przysz³oœci mogli pañstwo mieæ natychmiastowy dostêp do wysy³anej przez nas korespondencji.


Pozdrawiamy serdecznie,
Agencja zatrudnienia E&A Sp. z o.o.

* Niniejsza wiadomoœæ zosta³a wygenerowana automatycznie, prosimy na ni± nie odpowiadaæ.';
            
            $mail->DodajOdbiorca($email);
            $mail->WyslijMail('E&A - podziêkowanie', $mailContent, $emailOut, $emailOut);
        }
        
        public function SetAbfahrtSent($personId) {
            try {
                return $this->dataAccess->setAbfahrtSent($personId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in setLanguage', '', $e);
            }
        }
    }