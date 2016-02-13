<?php

    require_once '../conf.php';
    require_once 'ui/PartialsUI.php';
    require_once 'ui/CommonConsts.php';
    require_once 'adl/Person.php';
    require_once 'adl/Candidate.php';
    require_once 'bll/validationUtils.php';
    require_once 'bll/BLLDaneInternet.php';
    
    class OsobaView extends View
    {
        const FORM_PERSON_UPDATE_COMPARE            = 'updateCompare';
        const SESSION_NEW_DATA_SET                  = 'newDataSet_%s'; // %s in case user for some stupid reason clashed on 2 different tabs filling a new person
        const FORM_HIDDEN_DUPLICATE_PERSON_ID       = 'duplicatePersonId';
        
        protected $genericValidations = array(
            1 => '/^tak|nie$/', //bool
            2 => '/^\d{1,}$/', //int
            3 => '/^\w{1,}$/', //string
            4 => FormValidator::REGEX_DATA, //date
            5 => '', //daterange
        );
        
        //for add new duplicate person data choice
        protected $confRadioChoice = array (
            Model::COLUMN_DOS_IMIE            => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_NAZWISKO        => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_PLEC            => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_DATA_URODZENIA  => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_ULICA           => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_KOD             => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_WYKSZTALCENIE   => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_ZAWOD           => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_CHARAKTER       => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_DATA            => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_ILOSC_TYG       => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_ZRODLO          => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_MIEJSCOWOSC     => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_MIEJSCOWOSC_UR  => Partials::CHOICE_LEFT,
            Model::COLUMN_DOS_NR_OBUWIA       => Partials::CHOICE_LEFT,
        );
        
        protected $rowLabels = array (
            Model::COLUMN_DOS_ID               => 'ID',
            Model::COLUMN_DOS_IMIE             => 'Imiê',
            Model::COLUMN_DOS_NAZWISKO         => 'Nazwisko',
            Model::COLUMN_DOS_PLEC             => 'P³eæ',
            Model::COLUMN_DOS_DATA_URODZENIA   => 'Data urodzenia',
            Model::COLUMN_DOS_ULICA            => 'Ulica',
            Model::COLUMN_DOS_KOD              => 'Kod',
            Model::COLUMN_DOS_WYKSZTALCENIE    => 'Wykszta³cenie',
            Model::COLUMN_DOS_ZAWOD            => 'Zawód',
            Model::COLUMN_DOS_CHARAKTER        => 'Charakter',
            Model::COLUMN_DOS_ILOSC_TYG        => 'Ilo¶æ tygodni',
            Model::COLUMN_DOS_DATA             => 'Data wyjazdu',
            Model::COLUMN_DOS_DATA_ZGLOSZENIA  => 'Data zg³oszenia',
            Model::COLUMN_DOS_ZRODLO           => '¬ród³o',
            Model::COLUMN_DOS_MIEJSCOWOSC      => 'Miejscowo¶æ',
            Model::COLUMN_DOS_MIEJSCOWOSC_UR   => 'Miejscowo¶æ urodzenia',
            Model::COLUMN_DOS_NR_OBUWIA        => 'Nr obuwia',
        );
        
        /**
        * @desc upon compare submission remap cols and data to be updated - not gender but gender id etc
        */
        protected $compColsMapping = array(
            Model::COLUMN_DOS_IMIE              => Model::COLUMN_DOS_ID_IMIE,
            Model::COLUMN_DOS_PLEC              => Model::COLUMN_DOS_ID_PLEC,
            Model::COLUMN_DOS_WYKSZTALCENIE     => Model::COLUMN_DOS_ID_WYKSZTALCENIE,
            Model::COLUMN_DOS_ZAWOD             => Model::COLUMN_DOS_ID_ZAWOD,
            Model::COLUMN_DOS_CHARAKTER         => Model::COLUMN_DOS_ID_CHARAKTER,
            Model::COLUMN_DOS_ZRODLO            => Model::COLUMN_DOS_ID_ZRODLO,
            Model::COLUMN_DOS_MIEJSCOWOSC       => Model::COLUMN_DOS_ID_MIEJSCOWOSC,
            Model::COLUMN_DOS_MIEJSCOWOSC_UR    => Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR,
        );
        
        public function __construct () {
            
            parent::__construct();
            $idOsoba = Utils::PodajIdOsoba(false);
            $this->person = new Person($idOsoba);
            // TODO consider extending partials rather than encapsulating
            $this->partials = new Partials($this->person);
                        
            $this->actionList = array(
                
            );
            
            //different depending on id - might be as well edit or add for person
            
            if ($this->person->getPersonId() < 1) {
                //add priv required
                $this->actionList[Partials::FORM_PERSON_SUBMIT] = User::PRIV_DODAWANIE_REKORDU;
            } else {
                
                $this->actionList[Partials::FORM_PERSON_SUBMIT] = User::PRIV_EDYCJA_REKORDU;
            }
            
            $this->actionList[self::FORM_PERSON_UPDATE_COMPARE] = User::PRIV_EDYCJA_REKORDU;
            
            //this section quite redundant with registration comparison
            $addInfo = $this->person->getLogicDaneDodatkowe()->getAdditionalsDictList(true);
            
            foreach ($addInfo as $key => $value) {
                
                $this->rowLabels[$value[Model::COLUMN_DICT_NAZWA]] = $value[Model::COLUMN_DDL_NAZWA_WYSWIETLANA];
                $this->confRadioChoice[$value[Model::COLUMN_DICT_NAZWA]] = Partials::CHOICE_LEFT;
            }
        }
        
        public function viewAddEditDataForm () {
            
            //4 scenarios possible:
                //- regular add - no incoming data
                //- add from candidate - candidate id to read data
                //- edit existing data- get data for id
                //- replace existing data on collision
                
            $html = '';
            $isDuplicate = false;
            
            $personData = $this->getEmptyPersonData();
            $errMsgs = array();
            
            if (isset($_GET[CommonConsts::QUERY_STRING_NEW_ID]) && (int)$_GET[CommonConsts::QUERY_STRING_NEW_ID] > 0) {
                
                $newId = (int)$_GET[CommonConsts::QUERY_STRING_NEW_ID];
                $candidate = new Candidate($newId);
            } else {
                
                $newId = null;
                $candidate = null;
            }
            
            // Regular new person add (potential duplicate may occur)
            if (isset($_POST[Partials::FORM_PERSON_SUBMIT])) {
                
                // validate incoming data, remap them, attempt to add/update
                $this->formValidator = new FormValidator($this->getRegexpPersonDataValidators(), $this->getCallbacksPersonDataValidators(), $this->getPersonDataRequiredFields());
                
                $validated = $this->formValidator->validate($this->addEmptyNonFormData($_POST));
                $personData = $validated[FormValidator::VALIDATION_RESULT];
                $errMsgs = $validated[FormValidator::VALIDATION_ERRORS];
                $personData[Partials::FORM_PERSON_FIELD_SURNAME] = ucfirst(strtolower($personData[Partials::FORM_PERSON_FIELD_SURNAME]));
                
                if (sizeof($errMsgs) == 0) {
                    //remap from form to db fields
                    $personDbMapData = $this->remapFields($personData);
                    //candidate metadata
                    if ($newId) {
                        
                        $this->person->setCandidateId($newId);
                        $personDbMapData[Model::COLUMN_MDO_DANE] = $candidate->getMetaData();
                    }
                    
                    try {
    
                        $this->person->setPerson($personDbMapData, !empty($personData[Partials::FORM_PERSON_FIELD_CONTACT]));
                    } catch (LogicConflictDataException $e) {
                        //do we want that logged ? this ain't error we would like to log ...
                        //LogManager::log(LOG_ERR, '['.__FILE__.'] Error - duplicate person data: '.$e->getMessage(), $e);
                        $html .= 'Kandydat ju¿ jest w systemie. <br />Poni¿ej mo¿liwo¶æ porównania danych istniej±cych i nowo wprowadzonych.<br />';
                        $isDuplicate = true;
                        SessionManager::set(sprintf(self::SESSION_NEW_DATA_SET, $this->person->getPersonId()), $personDbMapData);
                    }
                    
                    if ($isDuplicate === false && $this->person->getPersonId() > 0) {
                        //TODO in case newid set delete on registrations ?
                        
                        //if is insert, else referer 302 - there is no f5 risk of having the same referer, as this logic is used in iframe
                        return self::postSuccessfull('/edit/przetwarzaj_dane_osobowe.php?id_os='.$this->person->getPersonId().'&edytuj_osobe=1');
                    }
                }
            }
            
            // Duplicate compare update
            if (isset($_POST[self::FORM_PERSON_UPDATE_COMPARE])) {
                
                $duplicatePersonId = (int)$_POST[self::FORM_HIDDEN_DUPLICATE_PERSON_ID];
                if ($duplicatePersonId < 1) {
                    
                    throw new ViewBadRequestException('Required duplicate person id missing in viewAddEditDataForm.');
                } else if (($personNewData = SessionManager::get(sprintf(self::SESSION_NEW_DATA_SET, $duplicatePersonId))) == null) {
                    
                    throw new ViewNotFoundException('Session data not found for '.$duplicatePersonId.' in viewAddEditDataForm');
                }
                
                $updateList = array(Model::COLUMN_DOS_ID => $duplicatePersonId);
                foreach ($this->confRadioChoice as $columnName => $defaultChoice) {
                    
                    $choice = (int)$_POST[$columnName];
                    
                    if ($choice == Partials::CHOICE_RIGHT) {
                        
                        if (isset($this->compColsMapping[$columnName]))
                            $updateList[$this->compColsMapping[$columnName]] = $personNewData[$this->compColsMapping[$columnName]];
                        else
                            $updateList[$columnName] = $personNewData[$columnName];
                    }
                }
                
                //candidate metadata
                if ($newId) {
                
                    $this->person->setCandidateId($newId);
                    $updateList[Model::COLUMN_MDO_DANE] = $candidate->getMetaData();
                }
                
                if (sizeof($updateList) > 1)
                    $this->person->setPerson($updateList, !empty($updateList[Partials::FORM_PERSON_FIELD_CONTACT]));
                else
                    $this->person = new Person($duplicatePersonId);
                     
                SessionManager::delete(sprintf(self::SESSION_NEW_DATA_SET, $this->person->getPersonId()));
                   
                return self::postSuccessfull('/edit/przetwarzaj_dane_osobowe.php?id_os='.$this->person->getPersonId().'&edytuj_osobe=1');
            }
            
            // New person incoming from registration, fill a new person add form
            if (isset($_GET[CommonConsts::QUERY_STRING_NEW_ID]) && $_SERVER['REQUEST_METHOD'] == 'GET') {
                
                $newId = (int)$_GET[CommonConsts::QUERY_STRING_NEW_ID];
                if ($newId > 0) {
                    
                    $candidate = new Candidate($newId);
                    $dbData = $candidate->getPersonData();
                    
                    $personData = $this->remapInternetData($dbData[Model::RESULT_FIELD_DATA]);
                    $personData[Partials::FORM_PERSON_FIELD_QUESTIONAIRE] = QUESTIONAIRE_INTERNET;
                    
                    $html .= 'Telefon(y): '.$dbData[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_TELEFON].', '.$dbData[Model::RESULT_FIELD_DATA][Model::COLUMN_DIN_KOMORKA];
                }
                
            }
            
            // Section of forms - either add new person (with registration data option), compare to new input or regular update of 'system' record
            if ($this->person->getPersonId() < 1) {
                
                // in case telephones exist in array add them to hiddens
                $html .= $this->partials->getAddUpdatePersonForm($personData, $errMsgs);
            } else if (true === $isDuplicate) {

                $systemData = $this->person->getPersonData();
                
                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                $html .= $this->htmlControls->_AddHidden(self::FORM_HIDDEN_DUPLICATE_PERSON_ID, self::FORM_HIDDEN_DUPLICATE_PERSON_ID, $this->person->getPersonId());
                $html .= $this->partials->getComparePersonDataForm($systemData[Model::RESULT_FIELD_DATA], $this->remapFields($personData), 
                    array('Kolumna', 'System', 'Wybór', 'Nowa warto¶æ'), $this->confRadioChoice, $this->rowLabels, self::FORM_PERSON_UPDATE_COMPARE, self::FORM_PERSON_UPDATE_COMPARE);
                $html .= $this->addFormSuf();
            } else {
                
                //TODO pure edit for update form
            }
            
            return $html;
        }
        
        public function run () {
            
            //entire page html through here, in time based on module style
            $html = $this->viewAddEditDataForm();
            
            return $html;
        }
        
        private function getEmptyPersonData () {
            
            return array(
                Partials::FORM_PERSON_FIELD_ID => $this->person->getPersonId(),
                Partials::FORM_PERSON_FIELD_NAME => null,
                //Partials::FORM_PERSON_FIELD_NAME_ID => 0,
                Partials::FORM_PERSON_FIELD_SURNAME => null,
                Partials::FORM_PERSON_FIELD_GENDER => null,
                //Partials::FORM_PERSON_FIELD_GENDER_ID => 0,
                Partials::FORM_PERSON_FIELD_BIRTH_DATE => null,
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE => null,
                //Partials::FORM_PERSON_FIELD_BIRTH_PLACE_ID => 0,
                Partials::FORM_PERSON_FIELD_CITY => null,
                //Partials::FORM_PERSON_FIELD_CITY_ID => 0,
                Partials::FORM_PERSON_FIELD_STREET => null,
                Partials::FORM_PERSON_FIELD_POSTAL_CODE => null,
                Partials::FORM_PERSON_FIELD_EDUCATION => null,
                Partials::FORM_PERSON_FIELD_PROFESSION => null,
                Partials::FORM_PERSON_FIELD_PROFESSION_ID => 0,
                Partials::FORM_PERSON_FIELD_CONSULTANT => $this->getUser()->getFullUserName(),
                Partials::FORM_PERSON_FIELD_LAST_CONTACT => $this->person->today,
                Partials::FORM_PERSON_FIELD_STATUS => STATUS_NOWY,
                //Partials::FORM_PERSON_FIELD_STATUS_ID => ID_STATUS_NOWY,
                Partials::FORM_PERSON_FIELD_DATE_REPORTED => $this->person->today,
                Partials::FORM_PERSON_FIELD_JOB_NATURE => null,
                Partials::FORM_PERSON_FIELD_DEPARTURE_DATE => null,
                Partials::FORM_PERSON_FIELD_WEEKS_COUNT => null,
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE => null,
                Partials::FORM_PERSON_FIELD_INFO_SOURCE => null,
                Partials::FORM_PERSON_FIELD_SHOE_SIZE => null,
                Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID => 0,
            );
        }
        
        /**
        * @desc Add data to new person submitted data list, which are not form input/submitted, like date reported
        * @param array current submitted data
        */
        private function addEmptyNonFormData($currentData) {
            
            $currentData[Partials::FORM_PERSON_FIELD_CONSULTANT] = $this->getUser()->getFullUserName();
            $currentData[Partials::FORM_PERSON_FIELD_CONSULTANT_ID] = $this->getUser()->getUserId();
            $currentData[Partials::FORM_PERSON_FIELD_LAST_CONTACT] = $this->person->today;
            $currentData[Partials::FORM_PERSON_FIELD_STATUS] = STATUS_NOWY;
            $currentData[Partials::FORM_PERSON_FIELD_DATE_REPORTED] = $this->person->today;
            $currentData[Partials::FORM_PERSON_FIELD_STATUS_ID] = ID_STATUS_NOWY;
            
            return $currentData;
        }
        
        private function getRegexpPersonDataValidators () {
            
            $regexpValidators = array(
                Partials::FORM_PERSON_FIELD_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_NAME => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_NAME_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_SURNAME => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_GENDER => '/Kobieta|Mê¿czyzna/',
                Partials::FORM_PERSON_FIELD_GENDER_ID => '/1|2/',
                Partials::FORM_PERSON_FIELD_BIRTH_DATE => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_CITY => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_CITY_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_STREET => '/^[\s,\S,\D,\d]{5,80}$/',
                Partials::FORM_PERSON_FIELD_POSTAL_CODE => '/^\d{2}-\d{3}$/',
                Partials::FORM_PERSON_FIELD_EDUCATION => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_EDUCATION_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_PROFESSION => '/^\D{3,80}$/',
                Partials::FORM_PERSON_FIELD_PROFESSION_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_CONSULTANT => '/(.*)/',
                Partials::FORM_PERSON_FIELD_CONSULTANT_ID => '/^\d{1,3}$/',
                Partials::FORM_PERSON_FIELD_LAST_CONTACT => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_CONTACT => '/|on/',
                Partials::FORM_PERSON_FIELD_STATUS => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_STATUS_ID => '/^\d{1,2}$/',
                Partials::FORM_PERSON_FIELD_DATE_REPORTED => '/(.*)/',
                Partials::FORM_PERSON_FIELD_JOB_NATURE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_JOB_NATURE_ID => '/^\d{1,2}$/',
                Partials::FORM_PERSON_FIELD_DEPARTURE_DATE => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_WEEKS_COUNT => '/^[1-9]{1}\d{0,2}$/',
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE_ID => '/^\d{1,3}$/',
                Partials::FORM_PERSON_FIELD_INFO_SOURCE => '/^\D{1,50}$/',
                Partials::FORM_PERSON_FIELD_SHOE_SIZE => '/^\d{2}$/',
                Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID => '/^\d{1,3}$/',
                
                Partials::FORM_PERSON_FIELD_PHONE => FormValidator::REGEX_PHONE,
                Partials::FORM_PERSON_FIELD_CELL => FormValidator::REGEX_CELL,
                Partials::FORM_PERSON_FIELD_EMAIL => FormValidator::REGEX_EMAIL,
            );
            
            $addElements = $this->person->getLogicDaneDodatkowe()->getAdditionalsDictList(true);

            foreach ($addElements as $addElement)
            {
                //assured above
                if (true === $addElement[FormValidator::ADDITIONALS_KEY_EDYCJA])
                {
                    $regexpValidators[$addElement[FormValidator::ADDITIONALS_KEY_NAZWA]] = $this->genericValidations[$addElement[FormValidator::ADDITIONALS_KEY_ID_TYP]];
                }
            }
            
            return $regexpValidators;
        }
        
        private function getPersonDataRequiredFields () {
            
            $regexpValidators = array(
                Partials::FORM_PERSON_FIELD_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_NAME => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_NAME_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_SURNAME => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_GENDER => '/Kobieta|Mê¿czyzna/',
                Partials::FORM_PERSON_FIELD_GENDER_ID => '/1|2/',
                Partials::FORM_PERSON_FIELD_BIRTH_DATE => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_CITY => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_CITY_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_STREET => '/^[\s,\S,\D,\d]{5,80}$/',
                Partials::FORM_PERSON_FIELD_POSTAL_CODE => '/^\d{2}-\d{3}$/',
                Partials::FORM_PERSON_FIELD_EDUCATION => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_EDUCATION_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_PROFESSION => '/^\D{3,80}$/',
                Partials::FORM_PERSON_FIELD_PROFESSION_ID => '/^\d{1,5}$/',
                Partials::FORM_PERSON_FIELD_CONSULTANT => '/(.*)/',
                Partials::FORM_PERSON_FIELD_CONSULTANT_ID => '/^\d{1,3}$/',
                Partials::FORM_PERSON_FIELD_LAST_CONTACT => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_CONTACT => '/|on/',
                Partials::FORM_PERSON_FIELD_STATUS => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_STATUS_ID => '/^\d{1,2}$/',
                Partials::FORM_PERSON_FIELD_DATE_REPORTED => '/(.*)/',
                Partials::FORM_PERSON_FIELD_JOB_NATURE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_JOB_NATURE_ID => '/^\d{1,2}$/',
                Partials::FORM_PERSON_FIELD_DEPARTURE_DATE => FormValidator::REGEX_DATA,
                Partials::FORM_PERSON_FIELD_WEEKS_COUNT => '/^[1-9]{1}\d{0,2}$/',
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE => '/^\D{3,50}$/',
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE_ID => '/^\d{1,3}$/',
                Partials::FORM_PERSON_FIELD_INFO_SOURCE => '/^\D{1,50}$/',
                Partials::FORM_PERSON_FIELD_SHOE_SIZE => '/^\d{2}$/',
                Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID => '/^\d{1,3}$/',
            );
            
            $addElements = $this->person->getLogicDaneDodatkowe()->getAdditionalsDictList(true);

            foreach ($addElements as $addElement)
            {
                //assured above
                if (true === $addElement[FormValidator::ADDITIONALS_KEY_EDYCJA])
                {
                    $regexpValidators[$addElement[FormValidator::ADDITIONALS_KEY_NAZWA]] = $this->genericValidations[$addElement[FormValidator::ADDITIONALS_KEY_ID_TYP]];
                }
            }
            
            return $regexpValidators;
        }
        
        private function getCallbacksPersonDataValidators () {
            
            return array(
                Partials::FORM_PERSON_FIELD_BIRTH_DATE           => array('ValidationUtils', 'validateDatePast'),
                Partials::FORM_PERSON_FIELD_DEPARTURE_DATE       => array('ValidationUtils', 'validateDateFuture'),
            );
        }
        
        /**
        * @desc Switch the keys present in array to the keys expected by business logic and dal logic.
        * @param array submitted data
        */
        private function remapFields ($submittedData) {
            
            $mapping = array(
                Partials::FORM_PERSON_FIELD_ID => Model::COLUMN_DOS_ID,
                Partials::FORM_PERSON_FIELD_NAME => Model::COLUMN_DOS_IMIE,
                Partials::FORM_PERSON_FIELD_NAME_ID => Model::COLUMN_DOS_ID_IMIE,
                Partials::FORM_PERSON_FIELD_SURNAME => Model::COLUMN_DOS_NAZWISKO,
                Partials::FORM_PERSON_FIELD_GENDER => Model::COLUMN_DOS_PLEC,
                Partials::FORM_PERSON_FIELD_GENDER_ID => Model::COLUMN_DOS_ID_PLEC,
                Partials::FORM_PERSON_FIELD_BIRTH_DATE => Model::COLUMN_DOS_DATA_URODZENIA,
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE => Model::COLUMN_DOS_MIEJSCOWOSC_UR,
                Partials::FORM_PERSON_FIELD_BIRTH_PLACE_ID => Model::COLUMN_DOS_ID_MIEJSCOWOSC_UR,
                Partials::FORM_PERSON_FIELD_CITY => Model::COLUMN_DOS_MIEJSCOWOSC,
                Partials::FORM_PERSON_FIELD_CITY_ID => Model::COLUMN_DOS_ID_MIEJSCOWOSC,
                Partials::FORM_PERSON_FIELD_STREET => Model::COLUMN_DOS_ULICA,
                Partials::FORM_PERSON_FIELD_POSTAL_CODE => Model::COLUMN_DOS_KOD,
                Partials::FORM_PERSON_FIELD_EDUCATION => Model::COLUMN_DOS_WYKSZTALCENIE,
                Partials::FORM_PERSON_FIELD_EDUCATION_ID => Model::COLUMN_DOS_ID_WYKSZTALCENIE,
                Partials::FORM_PERSON_FIELD_PROFESSION => Model::COLUMN_DOS_ZAWOD,
                Partials::FORM_PERSON_FIELD_PROFESSION_ID => Model::COLUMN_DOS_ID_ZAWOD,
                Partials::FORM_PERSON_FIELD_CONSULTANT_ID => Model::COLUMN_DOS_ID_KONSULTANT,
                //Partials::FORM_PERSON_FIELD_STATUS => ,
                Partials::FORM_PERSON_FIELD_STATUS_ID => Model::COLUMN_STT_ID_STATUS,
                Partials::FORM_PERSON_FIELD_DATE_REPORTED => Model::COLUMN_DOS_DATA_ZGLOSZENIA,
                Partials::FORM_PERSON_FIELD_JOB_NATURE => Model::COLUMN_DOS_CHARAKTER,
                Partials::FORM_PERSON_FIELD_JOB_NATURE_ID => Model::COLUMN_DOS_ID_CHARAKTER,
                Partials::FORM_PERSON_FIELD_DEPARTURE_DATE => Model::COLUMN_DOS_DATA,
                Partials::FORM_PERSON_FIELD_WEEKS_COUNT => Model::COLUMN_DOS_ILOSC_TYG,
                //Partials::FORM_PERSON_FIELD_QUESTIONAIRE => Model::COLUMN_DOS_,
                Partials::FORM_PERSON_FIELD_QUESTIONAIRE_ID => Model::COLUMN_DOS_ID_ANKIETA,
                Partials::FORM_PERSON_FIELD_INFO_SOURCE => Model::COLUMN_DOS_ZRODLO,
                Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID => Model::COLUMN_DOS_ID_ZRODLO,
                Partials::FORM_PERSON_FIELD_SHOE_SIZE => Model::COLUMN_DOS_NR_OBUWIA,
                
                Partials::FORM_PERSON_FIELD_PHONE => Model::COLUMN_DIN_TELEFON,
                Partials::FORM_PERSON_FIELD_CELL => Model::COLUMN_DIN_KOMORKA,
                Partials::FORM_PERSON_FIELD_EMAIL => Model::COLUMN_DIN_EMAIL,
            );
            
            foreach ($mapping as $formKey => $dbKey) {
                
                $submittedData[$dbKey] = $submittedData[$formKey];
                unset($submittedData[$formKey]);
            }
            
            return $submittedData;
        }
        
        private function remapInternetData ($dbData) {
            
            $personData = $this->getEmptyPersonData();
                        
            $mapping = array(
                Model::COLUMN_DIN_IMIE              => Partials::FORM_PERSON_FIELD_NAME,
                Model::COLUMN_DIN_ID_IMIE           => Partials::FORM_PERSON_FIELD_NAME_ID,
                Model::COLUMN_DIN_NAZWISKO          => Partials::FORM_PERSON_FIELD_SURNAME,
                Model::COLUMN_DIN_PLEC              => Partials::FORM_PERSON_FIELD_GENDER,
                Model::COLUMN_DIN_ID_PLEC           => Partials::FORM_PERSON_FIELD_GENDER_ID,
                Model::COLUMN_DIN_DATA_URODZENIA    => Partials::FORM_PERSON_FIELD_BIRTH_DATE,
                //Model::COLUMN_DIN_MIEJSCOWOSC_UR    => Partials::FORM_PERSON_FIELD_BIRTH_PLACE,
                Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR => Partials::FORM_PERSON_FIELD_BIRTH_PLACE_ID,
                Model::COLUMN_DIN_MIEJSCOWOSC       => Partials::FORM_PERSON_FIELD_CITY,
                Model::COLUMN_DIN_ID_MIEJSCOWOSC    => Partials::FORM_PERSON_FIELD_CITY_ID,
                Model::COLUMN_DIN_ULICA             => Partials::FORM_PERSON_FIELD_STREET,
                Model::COLUMN_DIN_KOD               => Partials::FORM_PERSON_FIELD_POSTAL_CODE,
                Model::COLUMN_DIN_WYKSZTALCENIE     => Partials::FORM_PERSON_FIELD_EDUCATION,
                Model::COLUMN_DIN_ID_WYKSZTALCENIE  => Partials::FORM_PERSON_FIELD_EDUCATION_ID,
                Model::COLUMN_DIN_ZAWOD             => Partials::FORM_PERSON_FIELD_PROFESSION,
                Model::COLUMN_DIN_ID_ZAWOD          => Partials::FORM_PERSON_FIELD_PROFESSION_ID,
                //Partials::FORM_PERSON_FIELD_STATUS => ,
                Model::COLUMN_DIN_DATA_ZGLOSZENIA   => Partials::FORM_PERSON_FIELD_DATE_REPORTED,
                Model::COLUMN_DIN_CHARAKTER         => Partials::FORM_PERSON_FIELD_JOB_NATURE,
                Model::COLUMN_DIN_ID_CHARAKTER      => Partials::FORM_PERSON_FIELD_JOB_NATURE_ID,
                Model::COLUMN_DIN_DATA              => Partials::FORM_PERSON_FIELD_DEPARTURE_DATE,
                Model::COLUMN_DIN_ILOSC_TYG         => Partials::FORM_PERSON_FIELD_WEEKS_COUNT,
                //Partials::FORM_PERSON_FIELD_QUESTIONAIRE => Model::COLUMN_DOS_,
                Model::COLUMN_DIN_ZRODLO            => Partials::FORM_PERSON_FIELD_INFO_SOURCE,
                Model::COLUMN_DIN_ID_ZRODLO         => Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID,
                
                Model::COLUMN_DIN_TELEFON           => Partials::FORM_PERSON_FIELD_PHONE,
                Model::COLUMN_DIN_KOMORKA           => Partials::FORM_PERSON_FIELD_CELL,
                Model::COLUMN_DIN_EMAIL             => Partials::FORM_PERSON_FIELD_EMAIL,
            );
            
            foreach ($mapping as $dbKey => $formKey) {
                
                $personData[$formKey] = $dbData[$dbKey];
            }
            
            return $personData; 
        }
    }
    
    CommonUtils::SessionStart();

    try {
        $output = new OsobaView();

        if (!$output->getUser()->isLogged())
        {
            require 'logowanie.php';
            die();
        }
        else
        {
            $html = $output->run();
        }
    } catch (ViewException $e) {
    
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
        $html = CommonUtils::getViewExceptionMessage($e);
    } catch (Exception $e) {
        
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
        $html = CommonUtils::getServerErrorMsg();
    }

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    echo $html;
    echo '</body></html>';