<?php
    require_once 'bll/queries.php';
    require_once 'bll/BLLDaneSlownikowe.php';
    require_once 'ui/UtilsUI.php';

    class Partials extends View {
        
        const PREFIX_ID             = 'id_';
        
        const FORM_PERSON_FIELD_ID                          = 'id';
        const FORM_PERSON_FIELD_NAME                        = 'name';
        const FORM_PERSON_FIELD_NAME_ID                     = 'name_id';
        const FORM_PERSON_FIELD_SURNAME                     = 'surname';
        const FORM_PERSON_FIELD_GENDER                      = 'gender';
        const FORM_PERSON_FIELD_GENDER_ID                   = 'gender_id';
        const FORM_PERSON_FIELD_BIRTH_DATE                  = 'birth_date';
        const FORM_PERSON_FIELD_BIRTH_PLACE                 = 'birth_place';
        const FORM_PERSON_FIELD_BIRTH_PLACE_ID              = 'birth_place_id';
        const FORM_PERSON_FIELD_CITY                        = 'city';
        const FORM_PERSON_FIELD_CITY_ID                     = 'city_id';
        const FORM_PERSON_FIELD_STREET                      = 'street';
        const FORM_PERSON_FIELD_POSTAL_CODE                 = 'postal_code';
        const FORM_PERSON_FIELD_EDUCATION                   = 'education';
        const FORM_PERSON_FIELD_EDUCATION_ID                = 'education_id';
        const FORM_PERSON_FIELD_PROFESSION                  = 'profession';
        const FORM_PERSON_FIELD_PROFESSION_ID               = 'profession_id';
        const FORM_PERSON_FIELD_CONSULTANT                  = 'consultant';
        const FORM_PERSON_FIELD_CONSULTANT_ID               = 'consultant_id';
        const FORM_PERSON_FIELD_STATUS                      = 'status';
        const FORM_PERSON_FIELD_STATUS_ID                   = 'status_id';
        const FORM_PERSON_FIELD_LAST_CONTACT                = 'last_contact';
        const FORM_PERSON_FIELD_CONTACT                     = 'contact';
        const FORM_PERSON_FIELD_DATE_REPORTED               = 'date_reported';
        const FORM_PERSON_FIELD_JOB_NATURE                  = 'job_nature';
        const FORM_PERSON_FIELD_JOB_NATURE_ID               = 'job_nature_id';
        const FORM_PERSON_FIELD_DEPARTURE_DATE              = 'departure_date';
        const FORM_PERSON_FIELD_WEEKS_COUNT                 = 'weeks_count';
        const FORM_PERSON_FIELD_QUESTIONAIRE                = 'questionaire';
        const FORM_PERSON_FIELD_QUESTIONAIRE_ID             = 'questionaire_id';
        const FORM_PERSON_FIELD_INFO_SOURCE                 = 'info_source';
        const FORM_PERSON_FIELD_INFO_SOURCE_ID              = 'info_source_id';
        const FORM_PERSON_FIELD_SHOE_SIZE                   = 'shoe_size';
        
        const FORM_PERSON_FIELD_PHONE                       = 'phone';
        const FORM_PERSON_FIELD_CELL                        = 'cell';
        const FORM_PERSON_FIELD_EMAIL                       = 'email';
        
        const FORM_PERSON_SUBMIT                            = 'addupdate';
        
        const CHOICE_LEFT   = 1;
        const CHOICE_RIGHT  = 2;
        
        protected $person;
        
        public function __construct (Person $person) {
            
            parent::__construct();
            $this->person = $person;
        }
        
        public function getNameSurnamePrt () {
            
            $nameSurnameData = $this->person->getExtraData('getNameSurname', array($this->person->getPersonId()));
            $nameSurname = $nameSurnameData[Model::RESULT_FIELD_DATA];
            
            return '<table><tr><td>Imie: </td><td>'.$nameSurname[0][Model::COLUMN_DOS_IMIE].'</td></tr><tr>
            <td>Nazwisko: </td><td>'.$nameSurname[0][Model::COLUMN_DOS_NAZWISKO].'</td></tr></table>';
        }
        
        /**
        * @desc Return html for add person or update person form. 
        * @param Array set of data to fill the form in while generating
        */
        public function getAddUpdatePersonForm ($data, $errMsgs = array()) {
            
            $brighterRow = 'oddRow';
            $darkerRow = 'evenRow';

            $isUpdate = false;
            $submitPriviledge = User::PRIV_DODAWANIE_REKORDU;
            $addElementsData = $data;
            
            if (0 < $data[self::FORM_PERSON_FIELD_ID]) {
                $isUpdate = true;
                $submitPriviledge = User::PRIV_EDYCJA_REKORDU;
            }
            
            $result = $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            //this means the data are comming from internet table
            if (isset($data[self::FORM_PERSON_FIELD_PHONE])) {
                
                $result .= $this->htmlControls->_AddHidden(self::FORM_PERSON_FIELD_PHONE, self::FORM_PERSON_FIELD_PHONE, $data[self::FORM_PERSON_FIELD_PHONE]);
            }
            
            if (isset($data[self::FORM_PERSON_FIELD_CELL])) {
                
                $result .= $this->htmlControls->_AddHidden(self::FORM_PERSON_FIELD_CELL, self::FORM_PERSON_FIELD_CELL, $data[self::FORM_PERSON_FIELD_CELL]);
            }
            
            if (isset($data[self::FORM_PERSON_FIELD_EMAIL])) {
                
                $result .= $this->htmlControls->_AddHidden(self::FORM_PERSON_FIELD_EMAIL, self::FORM_PERSON_FIELD_EMAIL, $data[self::FORM_PERSON_FIELD_EMAIL]);
            }
            //end of internet data contacts
            
            $result .= '<table class="gridTable" border="0" cellspacing="0">';
            //could be person -> get id ? 
            $result .= $this->htmlControls->_AddHidden(self::PREFIX_ID.self::FORM_PERSON_FIELD_ID, self::FORM_PERSON_FIELD_ID, $data[self::FORM_PERSON_FIELD_ID]);
            
            $result .= '<tr class="'.$brighterRow.'"><td>ID:</td><td>'.$data[self::FORM_PERSON_FIELD_ID].'</td></tr>';
            
            //juz nie przymykamy oko na odczyt danych ze slownika z bazy ...
            $bllDicts = new BLLDaneSlownikowe();
            $namesData = $bllDicts->getNamesList();
            $namesList = ($namesData && $namesData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $namesData[Model::RESULT_FIELD_DATA] : array();
            
            $result .= '<tr class="'.$darkerRow.'"><td>Imiê:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_NAME, self::PREFIX_ID.self::FORM_PERSON_FIELD_NAME, $namesList, 
                    $data[self::FORM_PERSON_FIELD_NAME], self::FORM_PERSON_FIELD_NAME_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_NAME, self::FORM_PERSON_FIELD_NAME_ID))
                    , '', 'genericWidth').'</td></tr>';
                    
            $result .= '<tr class="'.$brighterRow.'"><td>Nazwisko:</td><td>'.
                $this->htmlControls->_AddTextbox(self::FORM_PERSON_FIELD_SURNAME, self::PREFIX_ID.self::FORM_PERSON_FIELD_SURNAME, $data[self::FORM_PERSON_FIELD_SURNAME], 30, 30, '', 
                    'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_SURNAME]) ? $errMsgs[self::FORM_PERSON_FIELD_SURNAME] : null).'</td></tr>';
            
            $result .= '<tr class="'.$darkerRow.'"><td>P³eæ:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_GENDER, self::PREFIX_ID.self::FORM_PERSON_FIELD_GENDER, $bllDicts->getGenders(), 
                    $data[self::FORM_PERSON_FIELD_GENDER], self::FORM_PERSON_FIELD_GENDER_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_GENDER, self::FORM_PERSON_FIELD_GENDER_ID)), '', 'genericWidth').'</td></tr>';
                
            $result .= '<tr class="'.$brighterRow.'"><td>Data urodzenia:</td><td>'.
                $this->htmlControls->_AddDatebox(self::FORM_PERSON_FIELD_BIRTH_DATE, self::PREFIX_ID.self::FORM_PERSON_FIELD_BIRTH_DATE, $data[self::FORM_PERSON_FIELD_BIRTH_DATE], 10, 
                    10, 'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_BIRTH_DATE]) ? $errMsgs[self::FORM_PERSON_FIELD_BIRTH_DATE] : null).'</td></tr>';
            
            $citiesData = $bllDicts->getCitiesList();
            $citiesList = ($citiesData && $citiesData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $citiesData[Model::RESULT_FIELD_DATA] : array();
            
            $result .= '<tr class="'.$darkerRow.'"><td>Miejsce urodzenia:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_BIRTH_PLACE, self::PREFIX_ID.self::FORM_PERSON_FIELD_BIRTH_PLACE, $citiesList, 
                    $data[self::FORM_PERSON_FIELD_BIRTH_PLACE], self::FORM_PERSON_FIELD_BIRTH_PLACE_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_BIRTH_PLACE, self::FORM_PERSON_FIELD_BIRTH_PLACE_ID)), '', 'genericWidth').'</td></tr>';
                
            $result .= '<tr class="'.$brighterRow.'"><td>Miejscowo¶æ:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_CITY, self::PREFIX_ID.self::FORM_PERSON_FIELD_CITY, $citiesList, $data[self::FORM_PERSON_FIELD_CITY], 
                    self::FORM_PERSON_FIELD_CITY_ID, false, self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_CITY, self::FORM_PERSON_FIELD_CITY_ID)), 
                    '', 'genericWidth').'</td></tr>';
                
            $result .= '<tr class="'.$darkerRow.'"><td>Ulica:</td><td>'.
                $this->htmlControls->_AddTextbox(self::FORM_PERSON_FIELD_STREET, self::PREFIX_ID.self::FORM_PERSON_FIELD_STREET, $data[self::FORM_PERSON_FIELD_STREET], 50, 30, 
                    'onChange="sprawdz_ulica(this);"', 'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_STREET]) ? $errMsgs[self::FORM_PERSON_FIELD_STREET] : null).'</td></tr>';
                
            $result .= '<tr class="'.$brighterRow.'"><td>Kod:</td><td>'.
                $this->htmlControls->_AddPostCodebox(self::FORM_PERSON_FIELD_POSTAL_CODE, self::PREFIX_ID.self::FORM_PERSON_FIELD_POSTAL_CODE, 
                    $data[self::FORM_PERSON_FIELD_POSTAL_CODE], 'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_POSTAL_CODE]) ? $errMsgs[self::FORM_PERSON_FIELD_POSTAL_CODE] : null)
                    .'</td></tr>';
            
            $educationData = $bllDicts->getEducationsList();
            $educationList = ($educationData && $educationData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $educationData[Model::RESULT_FIELD_DATA] : array();
                
            $result .= '<tr class="'.$darkerRow.'"><td>Wykszta³cenie:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_EDUCATION, self::PREFIX_ID.self::FORM_PERSON_FIELD_EDUCATION, $educationList, 
                    $data[self::FORM_PERSON_FIELD_EDUCATION], self::FORM_PERSON_FIELD_EDUCATION_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_EDUCATION, self::FORM_PERSON_FIELD_EDUCATION_ID)), '', 'genericWidth').'</td></tr>';
                
            $result .= '<tr class="'.$brighterRow.'"><td>Zawód:</td><td>'.
                $this->htmlControls->GroupChoiceControl("Wybierz", "wybor_gr", self::FORM_PERSON_FIELD_PROFESSION, "txt_gr_zaw", $data[self::FORM_PERSON_FIELD_PROFESSION], 
                    self::FORM_PERSON_FIELD_PROFESSION_ID, "hid_gr_zaw", $data[self::FORM_PERSON_FIELD_PROFESSION_ID], 
                    '../prawa_strona/wybor_grupy_zaw.php', "Grupyzawodowe", 'genericWidth', 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_PROFESSION, self::FORM_PERSON_FIELD_PROFESSION_ID))).'</td></tr>';
                    
            $result .= '<tr class="'.$darkerRow.'"><td>Konsultant:</td><td>'.$data[self::FORM_PERSON_FIELD_CONSULTANT].'</td></tr>';
            
            $result .= '<tr class="'.$brighterRow.'"><td>Ostatni kontakt:</td><td>'.$data[self::FORM_PERSON_FIELD_LAST_CONTACT].'</td></tr>';
            
            $result .= '<tr class="'.$darkerRow.'"><td>'.
                $this->htmlControls->_AddCheckbox(self::FORM_PERSON_FIELD_CONTACT, self::PREFIX_ID.self::FORM_PERSON_FIELD_CONTACT, !empty($data[self::FORM_PERSON_FIELD_CONTACT]), 
                    JsEvents::ONBLUR.'="blur();"', 'Kontakt', 1).'</td><td>Status: '.$data[self::FORM_PERSON_FIELD_STATUS].'</td></tr>';
                
            $result .= '<tr class="'.$brighterRow.'"><td>Data zg³oszenia:</td><td>'.$data[self::FORM_PERSON_FIELD_DATE_REPORTED].'</td></tr>';
            
            $jobNatureData = $bllDicts->getJobNaturesList();
            $jobNatureList = ($jobNatureData && $jobNatureData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $jobNatureData[Model::RESULT_FIELD_DATA] : array();
            
            $result .= '<tr class="'.$darkerRow.'"><td>Charakter pracy:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_JOB_NATURE, self::PREFIX_ID.self::FORM_PERSON_FIELD_JOB_NATURE, $jobNatureList, 
                    $data[self::FORM_PERSON_FIELD_JOB_NATURE], self::FORM_PERSON_FIELD_JOB_NATURE_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_JOB_NATURE, self::FORM_PERSON_FIELD_JOB_NATURE_ID)), '', 'genericWidth').'</td></tr>';
                    
            $result .= '<tr class="'.$brighterRow.'"><td>Data wyjazdu:</td><td>'.
                $this->htmlControls->_AddDatebox(self::FORM_PERSON_FIELD_DEPARTURE_DATE, self::PREFIX_ID.self::FORM_PERSON_FIELD_DEPARTURE_DATE, $data[self::FORM_PERSON_FIELD_DEPARTURE_DATE], 
                    10, 10, 'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_DEPARTURE_DATE]) ? $errMsgs[self::FORM_PERSON_FIELD_DEPARTURE_DATE] : null).'</td></tr>';
                
            $result .= '<tr class="'.$darkerRow.'"><td>Ilo¶æ tygodni:</td><td>'.
                $this->htmlControls->_AddNumberbox(self::FORM_PERSON_FIELD_WEEKS_COUNT, self::PREFIX_ID.self::FORM_PERSON_FIELD_WEEKS_COUNT, $data[self::FORM_PERSON_FIELD_WEEKS_COUNT], 2,
                    3, 'onblur="sprawdz_tygodnie(this);"', 'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_WEEKS_COUNT]) ? $errMsgs[self::FORM_PERSON_FIELD_WEEKS_COUNT] : null).'</td></tr>';
            
            $questionaireData = $bllDicts->getQuestionairesList();
            $questionaireList = ($questionaireData && $questionaireData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $questionaireData[Model::RESULT_FIELD_DATA] : array();
                    
            $result .= '<tr class="'.$brighterRow.'"><td>Ankieta:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_QUESTIONAIRE, self::PREFIX_ID.self::FORM_PERSON_FIELD_QUESTIONAIRE, $questionaireList, 
                    $data[self::FORM_PERSON_FIELD_QUESTIONAIRE], self::FORM_PERSON_FIELD_QUESTIONAIRE_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_QUESTIONAIRE, self::FORM_PERSON_FIELD_QUESTIONAIRE_ID)), '', 'genericWidth').'</td></tr>';
                    
            $sourcesData = $bllDicts->getSourcesDifference($data[self::FORM_PERSON_FIELD_INFO_SOURCE_ID]);
            $sourcesList = ($sourcesData && $sourcesData[Model::RESULT_FIELD_ROWS_COUNT] > 0) ? $sourcesData[Model::RESULT_FIELD_DATA] : array();
            
            $result .= '<tr class="'.$darkerRow.'"><td>¬ród³o informacji:</td><td>'.
                $this->htmlControls->_AddSelect(self::FORM_PERSON_FIELD_INFO_SOURCE, self::PREFIX_ID.self::FORM_PERSON_FIELD_INFO_SOURCE, $sourcesList, 
                    $data[self::FORM_PERSON_FIELD_INFO_SOURCE], self::FORM_PERSON_FIELD_INFO_SOURCE_ID, false, 
                    self::glueErrorValues($errMsgs, array(self::FORM_PERSON_FIELD_INFO_SOURCE, self::FORM_PERSON_FIELD_INFO_SOURCE_ID)), '', 'genericWidth').'</td></tr>';
                    
            $result .= '<tr class="'.$brighterRow.'"><td>Rozmiar obuwia:</td><td>'.
                $this->htmlControls->_AddTextbox(self::FORM_PERSON_FIELD_SHOE_SIZE, self::PREFIX_ID.self::FORM_PERSON_FIELD_SHOE_SIZE, $data[self::FORM_PERSON_FIELD_SHOE_SIZE], 2, 20, '', 
                    'genericWidth', isset($errMsgs[self::FORM_PERSON_FIELD_SHOE_SIZE]) ? $errMsgs[self::FORM_PERSON_FIELD_SHOE_SIZE] : null).'</td></tr>';
                        
            $addElements = $this->person->getLogicDaneDodatkowe()->getAdditionalsDictList(true);
            
            // this should be a partial too 
            $result .= UtilsUI::formAdditionalData($this->htmlControls, $addElements, $addElementsData, $errMsgs);
            
            $result .= '<tr><td>'.$this->htmlControls->_AddSubmit($submitPriviledge, self::FORM_PERSON_SUBMIT, self::PREFIX_ID.self::FORM_PERSON_SUBMIT, 'Zatwierd¼', '', 'genericWidth');
            
            $result .= '</table>';
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        public function getComparePersonDataForm ($personData, $candidateData, $headers, $defaultChoices, $labels, $submitName, $submitId) {
            
            $tableHtml = new HtmlTable();
            //$tableHtml->addTableCss('left_float');
            $tableHtml->setHeader($headers);
            foreach ($labels as $key => $value) {
                
                $rowArray = array();
                if (isset($candidateData[$key])) {
                    
                    if (!isset($personData[$key]))
                        $personData[$key] = '-';
                    
                    $rowArray[] = $labels[$key];
                    $rowArray[] = '<label for="'.$key.self::CHOICE_LEFT.'">'.self::escapeOutput($personData[$key]).'</label>';
                    
                    if (isset($defaultChoices[$key])) {
                        
                        $rowArray[] = '<input id="'.$key.self::CHOICE_LEFT.'" type="radio" name="'.$key.'" value="'.self::CHOICE_LEFT.'" '.($defaultChoices[$key] == self::CHOICE_LEFT ? 'checked="checked"' : '').'>
                        <input id="'.$key.self::CHOICE_RIGHT.'" type="radio" name="'.$key.'" value="'.self::CHOICE_RIGHT.'" '.($defaultChoices[$key] == self::CHOICE_RIGHT ? 'checked="checked"' : '').'>';
                    } else {
                        $rowArray[] = '--';
                    }
                    $rowArray[] = '<label for="'.$key.self::CHOICE_RIGHT.'">'.self::escapeOutput($candidateData[$key]).'</label>';
                    $tableHtml->addRow($rowArray);
                }
            }
            
            $tableHtml->addRow(array('', '', $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, $submitName, $submitId, 'Zapisz', '', ''), ''));

            $result = $tableHtml->__toString();
            
            return $result;
        }
        
        public function run()
        {
            
        }
        
        //can be public if necessary
        /**
        * @desc Merge a list of potential errors into one string
        * @param array list of all error messages
        * @param array list of keys to check for error message
        */
        protected static function glueErrorValues ($errMsgList, $keyList) {
            
            $errValues = array();
            foreach ($keyList as $key) {
                
                if (isset($errMsgList[$key])) { 
                    
                    $errValues[] = $errMsgList[$key];
                }
            }
            
            return (sizeof($errValues) > 0) ? implode(', ', $errValues) : '';
        }
    }