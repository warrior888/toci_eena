<?php 

    require_once 'conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    require_once 'ui/PartialsUI.php';
    require_once 'adl/Person.php';
    require_once 'adl/Candidate.php';
    //TODO wyciagniecie nowej logiki powyzej rootowego katalogu apache - katalog code ?
    // ! global cache na poziomie bll z invalidacja, pytanie co z pytaniami o szerszym zakresie - pracochlonne, malo efektywne, raczej do porzucenia
    // ! przydalaby sie architektura pozwalajaca na poprawny szerszy purge, zaprojektowac - j.w.
    //view class abstract methods - execute, run ? lub po prostu run, try catch w jednym indexie zrobi 'execute'
    //done - w katalogu ui potrzebne wydzielone klasy widoku podlaczane do funkcjonalnosci (np klasa ponizej), potrzebny szablonowy index new z try catch, potrzebna klasa obslugi bledow
    class RegistrationCompareView extends View 
    {
        const PARAM_ID_DANE_OSOBOWE        = 'id_dane_osobowe';
        const PARAM_ID_DANE_INTERNET       = 'id_dane_internet';
        
        const FORM_SAVE_COMPARE                       = 'saveCompare';
        const FORM_SAVE_SWITCH_PHONE                  = 'saveSwitchPhone';
        const FORM_SAVE_SWITCH_CELL                   = 'saveSwitchCell';
        const FORM_SAVE_SWITCH_OTHER_PHONE            = 'saveSwitchOtherPhone';
        const FORM_SAVE_SWITCH_EMAIL                  = 'saveSwitchEmail';
        const FORM_SAVE_ADD_PHONE                     = 'saveAddPhone';
        const FORM_SAVE_ADD_CELL                      = 'saveAddCell';
        const FORM_SAVE_ADD_OTHER_PHONE               = 'saveAddOtherPhone';
        const FORM_SAVE_ADD_EMAIL                     = 'saveAddEmail';
        const FORM_SAVE_COMPENSATION_SKILLS           = 'saveSkills';
        const FORM_SAVE_COMPENSATION_LICENSE          = 'saveDrivingLicenses';
        const FORM_SAVE_ADD_LANGUAGES                 = 'saveAddLanguages';
        const FORM_SAVE_ADD_FORMER_EMPLOYER           = 'saveAddFormerEmployer';
        const FORM_SAVE_ADD_ADDITIONAL_INFO           = 'saveAddAdditionalInfo';
        const FORM_SAVE_SWITCH_FORMER_EMPLOYER        = 'saveSwitchFormerEmployer';
        const FORM_SAVE_SWITCH_ADDITIONAL_INFO        = 'saveSwitchAdditionalInfo';
        
        
        const FORM_SWITCH                     = 'switch';
        const FORM_SWITCH_PERSON              = 'switchPerson';
        const FORM_SWITCH_CANDIDATE           = 'switchCandidate';
        const FORM_HIDDEN_CANDIDATE_PHONE     = 'candidatePhone';
        const FORM_HIDDEN_PERSON_CELL         = 'personCell';
        const FORM_HIDDEN_PERSON_OTHER_PHONE  = 'personOtherPhone';
        const FORM_HIDDEN_CANDIDATE_CELL      = 'candidateCell';
        const FORM_HIDDEN_CANDIDATE_OTHER_PHONE = 'candidateOtherPhone';
        const FORM_HIDDEN_ID_ADD_DATA         = 'additionalDataId';
        
        const FORM_CHECKBOX_COMPENSATION_IDS  = 'compensationIds';
        const FORM_CHECKBOX_NEW_LANG_IDS      = 'newLangIds';
        const FORM_CHECKBOX_LANG_CONF_IDS     = 'langConfIds';
        const FORM_CHECKBOX_NEW_EMP_IDS       = 'newEmploymentsIds';
        
        const LANG_SEP      = '-';
        
        protected $person;
        protected $candidate;
        
        //determine which cols are for choice and what is the choice
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
        );
        
        protected $rowLabels = array (
            Model::COLUMN_DOS_ID               => 'ID',
            Model::COLUMN_DOS_IMIE             => 'Imiê',
            Model::COLUMN_DOS_NAZWISKO         => 'Nazwisko',
            Model::COLUMN_DOS_PLEC             => 'P³eæ',
            Model::COLUMN_DOS_DATA_URODZENIA   => 'Data urodzenia',
            Model::COLUMN_DOS_MIEJSCOWOSC      => 'Miejscowo¶æ',
            Model::COLUMN_DOS_MIEJSCOWOSC_UR   => 'Miejscowo¶æ urodzenia',
            Model::COLUMN_DOS_ULICA            => 'Ulica',
            Model::COLUMN_DOS_KOD              => 'Kod',
            Model::COLUMN_DOS_WYKSZTALCENIE    => 'Wykszta³cenie',
            Model::COLUMN_DOS_ZAWOD            => 'Zawód',
            Model::COLUMN_DOS_CHARAKTER        => 'Charakter',
            Model::COLUMN_DOS_ILOSC_TYG        => 'Ilo¶æ tygodni',
            Model::COLUMN_DOS_DATA             => 'Data wyjazdu',
            Model::COLUMN_DOS_DATA_ZGLOSZENIA  => 'Data zg³oszenia',
            Model::COLUMN_DOS_ZRODLO           => '¬ród³o',
        );
        
        public function __construct ($data) {
            
            $this->actionList = array(
               self::FORM_SAVE_ADD_ADDITIONAL_INFO      => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_CELL                 => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_OTHER_PHONE          => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_EMAIL                => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_FORMER_EMPLOYER      => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_LANGUAGES            => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_ADD_PHONE                => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SAVE_COMPARE                  => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_COMPENSATION_LICENSE     => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_COMPENSATION_SKILLS      => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_ADDITIONAL_INFO   => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_CELL              => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_OTHER_PHONE       => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_EMAIL             => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_FORMER_EMPLOYER   => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SAVE_SWITCH_PHONE             => User::PRIV_EDYCJA_REKORDU,
            );
            
            parent::__construct();
            
            $idOsoba = CommonUtils::getValidInt($data[self::PARAM_ID_DANE_OSOBOWE]);
            $idOsobaInternet = CommonUtils::getValidInt($data[self::PARAM_ID_DANE_INTERNET]);
            
            if (!$idOsoba || !$idOsobaInternet)
            	throw new ViewBadRequestException('Request invalid, missing id(s)');
            
            $this->person = new Person($idOsoba);
            $this->candidate = new Candidate($idOsobaInternet);
            
            $addInfo = $this->candidate->getLogicDaneDodatkowe()->getAdditionalsDictList(true);
            
            foreach ($addInfo as $key => $value) {
                
                $this->rowLabels[$value[Model::COLUMN_DICT_NAZWA]] = $value[Model::COLUMN_DDL_NAZWA_WYSWIETLANA];
                $this->confRadioChoice[$value[Model::COLUMN_DICT_NAZWA]] = Partials::CHOICE_LEFT;
            }
        }
        
        public function run () {
            
        	$html = '<div class="left_float">';
        	
            $html .= $this->viewComparison();
            
            $html .= '</div><div class="left_float">';
            $html .= $this->viewPhone();
            $html .= $this->viewCell();
            $html .= $this->viewOtherPhone();
            $html .= $this->viewEmail();
            $html .= '</div><div style="clear: both;">';
            $html .= $this->viewExtrasCmp(Candidate::COMPENSATION_TYPE_SKILLS, 'Umiejêtno¶ci', Model::COLUMN_UMO_ID_UMIEJETNOSC, Model::COLUMN_DICT_NAZWA, self::FORM_SAVE_COMPENSATION_SKILLS);
            $html .= $this->viewExtrasCmp(Candidate::COMPENSATION_TYPE_DRIVING_LICENSE, 'Prawo jazdy', Model::COLUMN_PPJ_ID_PRAWO_JAZDY, Model::COLUMN_DICT_NAZWA, self::FORM_SAVE_COMPENSATION_LICENSE);
            $html .= '</div><div style="clear: both;">';
            $html .= $this->viewLanguages();
            $html .= '</div>';
            $html .= $this->viewEmploymentHistory();
            //$html .= $this->viewAdditionalInfo();
            
            return $html;
        }
        // shouldn't this be called Instantiate ?
        public static function initialize () {
            return new RegistrationCompareView($_GET);
        }

        private function viewComparison () {
            
            $personDataSet = $this->person->getPersonData();
            $personData = $personDataSet[Model::RESULT_FIELD_DATA];
            $candidateDataSet = $this->candidate->getPersonData();
            $candidateData = $candidateDataSet[Model::RESULT_FIELD_DATA];
            
            $result = '';
            
            if (isset($_POST[self::FORM_SAVE_COMPARE])) {
                
                try {
                    $result = $this->person->updatePersonCandidateData($this->candidate->getCandidateId(), $_POST);
                } catch (ProjectLogicException $e) {
                    //LogManager::log(LOG_ERR, '['.__CLASS__.'] Logic exception '.$e->getMessage().' while updatePersonCandidateData operation');
                    CommonUtils::mapLogicException($e);
                }
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    
                $result .= 'Brak zmian do wykonania.';
            }
            
            //request uri, query string are in option
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            $partials = new Partials($this->person);
            $result .= $partials->getComparePersonDataForm($personData, $candidateData, array('Pole', 'System', 'Wybór', 'Rejestracja'), 
                $this->confRadioChoice, $this->rowLabels, self::FORM_SAVE_COMPARE, 'id_savecompare');
            
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        private function viewPhone () {
            
            $html = '';
            if (isset($_POST[self::FORM_SAVE_ADD_PHONE])) {
                
                $candidatePhone = (int)$_POST[self::FORM_HIDDEN_CANDIDATE_PHONE];
                if (!$candidatePhone)
                    throw new ViewBadRequestException('Save person/candidate phone error - unsupplied phone');
                    
                //in case of success the whole functionality disappears; on error exception is handled and message shown
                try {
                    $result = $this->person->setPhone($candidatePhone);
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }
                
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    
            } else if (isset($_POST[self::FORM_SAVE_SWITCH_PHONE])) {
                
                $candidatePhone = isset($_POST[self::FORM_HIDDEN_CANDIDATE_PHONE]) ? (int)$_POST[self::FORM_HIDDEN_CANDIDATE_PHONE] : null;
                $rowId = isset($_POST[self::FORM_SWITCH]) ? (int)$_POST[self::FORM_SWITCH] : null;
                if (!$candidatePhone)
                    throw new ViewBadRequestException('Replace person/candidate phone error - unsupplied phone and/or rowId');
                    
                $result = false;
                if (!$rowId)
                    $html .= 'Nie wybrano telefonu do zamiany';
                else {
                //in case of success the whole functionality disappears; on error exception is handled and message shown    
                    try {
                        $result = $this->person->setPhone($candidatePhone, $rowId);
                    } catch (ProjectLogicException $e) {
                        CommonUtils::mapLogicException($e);
                    }
                }
                    
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            $candidatePhone = $this->candidate->getCandidatePhone();
            if (!$candidatePhone)
                return '';
                
            $personPhones = $this->person->getPhones();
            if (!$personPhones)
                return '';
                
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $tableHtml = new HtmlTable();
            $tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array('Telefon'));
            foreach ($personPhones as $personPhone) {
                
                $rowArray = array();
                if ($personPhone[Model::COLUMN_TEL_NAZWA] == $candidatePhone)
                    return '<span>Osoba posiada telefon '.$candidatePhone.'</span><br />';
                    
                $rowArray[] = '<input type="radio" name="'.self::FORM_SWITCH.'" id="tel_'.$personPhone[Model::COLUMN_TEL_NAZWA].'" value="'.$personPhone[Model::COLUMN_TEL_ID_WIERSZ].'" /><label for="tel_'.$personPhone[Model::COLUMN_TEL_NAZWA].'">'.$personPhone[Model::COLUMN_TEL_NAZWA].'</label>';
                
                $tableHtml->addRow($rowArray); 
            }
            
            $tableHtml->addRow(array($candidatePhone.$this->htmlControls->_AddHidden('id_'.self::FORM_HIDDEN_CANDIDATE_PHONE, self::FORM_HIDDEN_CANDIDATE_PHONE, $candidatePhone)), null, 'markedRow');
            $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_PHONE, 'id_'.self::FORM_SAVE_SWITCH_PHONE, 'Zamieñ', '', '')));
            $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_PHONE, 'id_'.self::FORM_SAVE_ADD_PHONE, 'Dodaj nowy', '', '')));
            
            $html .= $tableHtml->__toString().$this->addFormSuf();
            
            return $html;
        }
        
        public function viewCell () {
            
            $html = '';
            $cell = $this->person->getCell();
            $candidateCell = $this->candidate->getCandidateCell();
            
            if (isset($_POST[self::FORM_SAVE_ADD_CELL])) {
                
                if (!$cell && $candidateCell) {
                    
                    try {
                        $result = $this->person->setCell($candidateCell);
                    } catch (ProjectLogicException $e) {
                        CommonUtils::mapLogicException($e);
                    }
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
                
                throw new ViewBadRequestException('Candidate save new cell invalid request, env: '.var_export($_POST, true)); 
            } else if (isset($_POST[self::FORM_SAVE_SWITCH_CELL])) {
                //there is 1 to 1 relation, so we rely on db data, it is enough to know we are ordered to switch
                
                if ($cell && $candidateCell) {
                    
                    if ($cell[Model::COLUMN_TEK_NAZWA] != $candidateCell) {
                        try {
                            $result = $this->person->setCell($candidateCell, $cell[Model::COLUMN_TEK_NAZWA]);
                        } catch (ProjectLogicException $e) {
                            CommonUtils::mapLogicException($e);
                        }
                        if ($result)
                            return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    }
                }
                
                throw new ViewBadRequestException('Candidate replace cell invalid request');
            }

            if (!$candidateCell)
                return '';
             
            $switch = true;
            if (!$cell)
                $switch = false;
            else if ($cell[Model::COLUMN_TEK_NAZWA] == $candidateCell)
                return '<span>Osoba posiada telefon komórkowy '.$candidateCell.'</span><br />';
                
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $tableHtml = new HtmlTable();
            $tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array('Komórka'));
            
            $tableHtml->addRow(array($cell[Model::COLUMN_TEK_NAZWA].$this->htmlControls->_AddHidden('id_'.self::FORM_HIDDEN_PERSON_CELL, self::FORM_HIDDEN_PERSON_CELL, $cell[Model::COLUMN_TEK_ID_WIERSZ])));
            $tableHtml->addRow(array($candidateCell.$this->htmlControls->_AddHidden('id_'.self::FORM_HIDDEN_CANDIDATE_CELL, self::FORM_HIDDEN_CANDIDATE_CELL, $candidateCell)), null, 'markedRow');
            
            if ($switch)
                $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_CELL, 'id_'.self::FORM_SAVE_SWITCH_CELL, 'Zamieñ', '', '')));
            else
                $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_CELL, 'id_'.self::FORM_SAVE_ADD_CELL, 'Dodaj nowy', '', '')));
            
            $html .= $tableHtml->__toString().$this->addFormSuf();
            
            return $html;
        }

        public function viewOtherPhone () {
            
            $html = '';
            if (isset($_POST[self::FORM_SAVE_ADD_OTHER_PHONE])) {
                
                $candidatePhone = $_POST[self::FORM_HIDDEN_CANDIDATE_OTHER_PHONE];
                if (!preg_match('/^[0-9]+$/', $candidatePhone))
                    throw new ViewBadRequestException('Save person/candidate other phone error - unsupplied phone');
                    
                //in case of success the whole functionality disappears; on error exception is handled and message shown
                try {
                    $result = $this->person->setExtraPhone($candidatePhone);
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }
                
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    
            } else if (isset($_POST[self::FORM_SAVE_SWITCH_OTHER_PHONE])) {
                
                $candidatePhone = isset($_POST[self::FORM_HIDDEN_CANDIDATE_OTHER_PHONE]) ? $_POST[self::FORM_HIDDEN_CANDIDATE_OTHER_PHONE] : null;
                $rowId = isset($_POST[self::FORM_SWITCH]) ? (int)$_POST[self::FORM_SWITCH] : null;
                if (!preg_match('/^[0-9]+$/', $candidatePhone))
                    throw new ViewBadRequestException('Replace person/candidate other phone error - unsupplied phone and/or rowId');
                    
                $result = false;
                if (!$rowId)
                    $html .= 'Nie wybrano innego telefonu do zamiany';
                else {
                //in case of success the whole functionality disappears; on error exception is handled and message shown    
                    try {
                        $result = $this->person->setExtraPhone($candidatePhone, $rowId);
                    } catch (ProjectLogicException $e) {
                        CommonUtils::mapLogicException($e);
                    }
                }
                    
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            $candidatePhone = $this->candidate->getCandidateExtraPhone();
            if (!$candidatePhone)
                return '';
                
            $personPhones = $this->person->getExtraPhones();
            if (!$personPhones)
                return '';
                
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $tableHtml = new HtmlTable();
            $tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array('Inny telefon'));
            foreach ($personPhones as $personPhone) {
                
                $rowArray = array();
                if ($personPhone[Model::COLUMN_TEI_NAZWA] == $candidatePhone)
                    return '<span>Osoba posiada inny telefon '.$candidatePhone.'</span><br />';
                    
                $rowArray[] = '<input type="radio" name="'.self::FORM_SWITCH.'" id="tel_'.$personPhone[Model::COLUMN_TEI_NAZWA].'" value="'.$personPhone[Model::COLUMN_TEI_ID_WIERSZ].'" /><label for="tel_'.$personPhone[Model::COLUMN_TEI_NAZWA].'">'.$personPhone[Model::COLUMN_TEI_NAZWA].'</label>';
                
                $tableHtml->addRow($rowArray); 
            }
            
            $tableHtml->addRow(array($candidatePhone.$this->htmlControls->_AddHidden('id_'.self::FORM_HIDDEN_CANDIDATE_OTHER_PHONE, self::FORM_HIDDEN_CANDIDATE_OTHER_PHONE, $candidatePhone)), null, 'markedRow');
            $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_OTHER_PHONE, 'id_'.self::FORM_SAVE_SWITCH_OTHER_PHONE, 'Zamieñ', '', '')));
            $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_OTHER_PHONE, 'id_'.self::FORM_SAVE_ADD_OTHER_PHONE, 'Dodaj nowy', '', '')));
            
            $html .= $tableHtml->__toString().$this->addFormSuf();
            
            return $html;
        }
        
        public function viewEmail () {
            
            $html = '';
            $email = $this->person->getEmail();
            $candidateEmail = $this->candidate->getCandidateEmail();
            
            if (isset($_POST[self::FORM_SAVE_ADD_EMAIL])) {
                
                if (!$email && $candidateEmail) {
                    
                    try {
                        $result = $this->person->setEmail($candidateEmail);
                    } catch (ProjectLogicException $e) {
                        CommonUtils::mapLogicException($e);
                    }
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
                
                throw new ViewBadRequestException('Candidate save new email invalid request by '.$this->user->getUserName().', env: '.var_export($_POST, true)); 
            } else if (isset($_POST[self::FORM_SAVE_SWITCH_EMAIL])) {
                //there is 1 to 1 relation, so we rely on db data, it is enought to know we are ordered to switch
                
                if ($email && $candidateEmail) {
                    
                    if ($email[Model::COLUMN_EMA_NAZWA] != $candidateEmail) {
                        try {
                            $result = $this->person->setEmail($candidateEmail, $email[Model::COLUMN_EMA_NAZWA]);
                        } catch (ProjectLogicException $e) {
                            CommonUtils::mapLogicException($e);
                        }
                        if ($result)
                            return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    }
                }
                
                throw new ViewBadRequestException('Candidate replace email invalid request by '.$this->user->getUserName().', env: '.var_export($_POST, true));
            }

            if (!$candidateEmail)
                return '';
             
            $switch = true;
            if (!$email)
                $switch = false;
            else if ($email[Model::COLUMN_EMA_NAZWA] == $candidateEmail)
                return '<span>Osoba posiada e-mail '.$candidateEmail.'</span><br />';
                
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $tableHtml = new HtmlTable();
            $tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array('E-mail'));
            
            $tableHtml->addRow(array($email[Model::COLUMN_EMA_NAZWA]));
            $tableHtml->addRow(array($candidateEmail), null, 'markedRow');
            
            if ($switch)
                $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_EMAIL, 'id_'.self::FORM_SAVE_SWITCH_EMAIL, 'Zamieñ', '', '')));
            else
                $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_EMAIL, 'id_'.self::FORM_SAVE_ADD_EMAIL, 'Dodaj nowy', '', '')));
            
            $html .= $tableHtml->__toString().$this->addFormSuf();
            
            return $html;
        }
        
        public function viewLanguages () {
            
            $html = '';

            if (isset($_POST[self::FORM_SAVE_ADD_LANGUAGES])) {
                
                //if (!isset($_POST[self::FORM_CHECKBOX_NEW_LANG_IDS], $_POST[self::FORM_CHECKBOX_LANG_CONF_IDS]))
                //    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                
                $newLangIdsList = isset($_POST[self::FORM_CHECKBOX_NEW_LANG_IDS]) ? $_POST[self::FORM_CHECKBOX_NEW_LANG_IDS] : array();
                $confLangIdsList = isset($_POST[self::FORM_CHECKBOX_LANG_CONF_IDS]) ? $_POST[self::FORM_CHECKBOX_LANG_CONF_IDS] : array();
                    
                if (!is_array($newLangIdsList) || !is_array($confLangIdsList) || (sizeof($newLangIdsList) == 0 && sizeof($confLangIdsList) == 0)) {
                    
                    //throw new ViewBadRequestException('Candidate add new langs invalid request');
                    $html .= '<label class="error">Zaznacz jêzyk do dodania.</label>';
                } else {
                
                    $newLangs = array();
                    
                    foreach ($newLangIdsList as $langInput) {
                        list($langId, $langLevel) = explode(self::LANG_SEP, $langInput);
                        $_langId = (int)$langId;
                        $_langLevel = (int)$langLevel;
                        
                        if ($_langId < 1 || $_langLevel < 1)
                            throw new ViewBadRequestException('Candidate add new langs invalid request');
                        
                        $newLangs[$_langId] = array(Model::COLUMN_ZNJ_ID_JEZYK => $_langId, Model::COLUMN_ZNJ_ID_POZIOM => $_langLevel);
                    }
                    
                    foreach ($confLangIdsList as $langInput) {
                        list($langId, $langLevel) = explode(self::LANG_SEP, $langInput);
                        $_langId = (int)$langId;
                        $_langLevel = (int)$langLevel;
                        
                        if ($_langId < 1 || $_langLevel < 1)
                            throw new ViewBadRequestException('Candidate add conf langs invalid request');
                        
                        $newLangs[$_langId] = array(Model::COLUMN_ZNJ_ID_JEZYK => $_langId, Model::COLUMN_ZNJ_ID_POZIOM => $_langLevel, Model::COLUMN_ZJE_ID_KONSULTANT => $this->user->getUserId());
                    }
                    
                    $result = $this->person->setLanguages($newLangs);
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
            }
            
            $candidateLanguages = $this->candidate->getLanguagesCompensation($this->person->getPersonId());
            
            if (!is_null($candidateLanguages)) {
                
                $languages = $this->person->getLanguages();

                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                $tableHtml = new HtmlTable();
                //$tableHtml->addTableCss('left_float');
                $tableHtml->setHeader(array('Znalezione jêzyki', 'System', 'Potwierdzony ?', 'Rejestracja'));
                
                $newLangsCount = 0;
                
                
                foreach ($candidateLanguages as $langId => $language) {
                    
                    $newLangsCount++;
                    if (isset($languages[$langId])) {
                        
                        $personLang = $languages[$langId];
                        
                        if ($personLang[Model::COLUMN_JEZ_POZIOM] == $language[Model::COLUMN_JEZ_POZIOM])
                            continue;
                        
                        $boolConfirmed = !is_null($personLang[Model::COLUMN_ZJE_DATA]);
                        $confirmed = $boolConfirmed ? 
                        $personLang[Model::COLUMN_ZJE_DATA].', '.$personLang[Model::COLUMN_UPR_IMIE_NAZWISKO] : 'nie';
                        
                        $level = $personLang[Model::COLUMN_JEZ_POZIOM];
                        $css = null;
                    } else {
                        $boolConfirmed = false;
                        $confirmed = $level = 'brak';
                        $css = 'markedRow';
                    }
        
                    $checkboxName = $boolConfirmed ? self::FORM_CHECKBOX_LANG_CONF_IDS : self::FORM_CHECKBOX_NEW_LANG_IDS;
                    $checkboxChecked = !$boolConfirmed;
        
                    $jezykLabel = $language[Model::COLUMN_JEZ_JEZYK];
                    if ($boolConfirmed)
                        $jezykLabel .= ' * ';
                    
                    $tableHtml->addRow(
                        array(
                            $this->htmlControls->_AddCheckbox(
                                $checkboxName.'[]', 
                                'id_'.$checkboxName.$langId, 
                                $checkboxChecked, 
                                '', 
                                $jezykLabel, 
                                $language[Model::COLUMN_JIN_ID_JEZYK].self::LANG_SEP.$language[Model::COLUMN_JIN_ID_POZIOM]
                            ), 
                            $level, 
                            $confirmed,
                            $language[Model::COLUMN_JEZ_POZIOM]
                        ), null, $css
                    );                
                }
                
                if ($newLangsCount > 0) {
                    
                    $tableHtml->addRow(array(
                        $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_LANGUAGES, 'id_'.self::FORM_SAVE_ADD_LANGUAGES, 'Dodaj zaznaczone', '', ''),
                        '', '', ''
                        ));
                    $html .= $tableHtml->__toString(); 
                }
                
                $html .= $this->addFormSuf().
                '* - jêzyki, których zaznaczenie oznacza potwierdzenie poziomu znajomo¶ci';
            } else {
                $html .= 'Nie zg³oszono jêzyków.';
            }
            
            return $html;
        }
        
        public function viewEmploymentHistory () {
            
            $html = '';
            //TODO switch
            if (isset($_POST[self::FORM_SAVE_SWITCH_FORMER_EMPLOYER]))
            {
                if (isset($_POST[self::FORM_SWITCH_PERSON], $_POST[self::FORM_SWITCH_CANDIDATE])) {
                    
                    $personRowId = (int)$_POST[self::FORM_SWITCH_PERSON];
                    $candidateRowId = (int)$_POST[self::FORM_SWITCH_CANDIDATE];
                    
                    if ($personRowId && $candidateRowId) {
                        
                        $result = $this->person->setFormerEmployerFromCandidate($candidateRowId, $personRowId);
                        if ($result)
                            return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    } else {
                        
                        throw new ViewBadRequestException('Invalid values for employers switch operation');
                    }
                } else {
                    
                    $html .= '<label class="error">Nie zaznaczono wymaganych opcji - pracodawcy do podmiany i/lub podmienianego.</label>';
                }                
            }
            
            if (isset($_POST[self::FORM_SAVE_ADD_FORMER_EMPLOYER])) {
                
                if (!isset($_POST[self::FORM_CHECKBOX_NEW_EMP_IDS]) || !is_array($_POST[self::FORM_CHECKBOX_NEW_EMP_IDS])) {
                    
                    //throw new ViewBadRequestException('Candidate add new employments entries invalid request - no checkbox field of array type');
                    $html .= '<label class="error">Zaznacz poprzedniego pracodawcê do dodania.</error>';
                } else {
                    
                    $newEmploymentsId = array();
                    
                    foreach ($_POST[self::FORM_CHECKBOX_NEW_EMP_IDS] as $employmentId) {

                        $_employmentId = (int)$employmentId;
                        
                        if ($_employmentId < 1)
                            throw new ViewBadRequestException('Candidate add new employments invalid request - id is not int');
                        
                        $newEmploymentsId[] = $_employmentId;
                    }
                    
                    if (sizeof($newEmploymentsId)) {
                        $result = $this->person->setFormerEmployerFromCandidate($newEmploymentsId);
                        if ($result)
                            return self::postSuccessfull($_SERVER['REQUEST_URI']);
                    }
                }
            }
            
            $candidateEmpHistoryData = $this->candidate->getFormerEmployers();
            $candidateEmpHistory = $candidateEmpHistoryData[Model::RESULT_FIELD_DATA];
            if ($candidateEmpHistory) {
                
                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                
                $personEmpHistoryData = $this->person->getFormerEmployers();
                $personEmpHistory = isset($personEmpHistoryData[Model::RESULT_FIELD_DATA]) ? $personEmpHistoryData[Model::RESULT_FIELD_DATA] : null;

                $hasHistory = false;
                if ($personEmpHistory) {
                    
                    $hasHistory = true;
                    $tableHtmlPEH = new HtmlTable();
                    //$tableHtmlPEH->addTableCss('left_float');
                    $tableHtmlPEH->setHeader(array('Poprzedni pracodawca - system', 'Podmieñ'));
                
                    foreach ($personEmpHistory as $empHistoryRow) {
                        
                        $tableHtmlPEH->addRow(
                            array(
                                '<label for="personEmp_'.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ].'">'.
                                    $empHistoryRow[Model::COLUMN_PPR_NAZWA].' - '.$empHistoryRow[Model::COLUMN_GRU_GRUPA_ZAWODOWA].
                                '</label>',
                                '<input type="radio" name="'.self::FORM_SWITCH_PERSON.'" id="personEmp_'.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ].'" 
                                value="'.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ].'" />'
                            ));
                    }

                    $html .= $tableHtmlPEH->__toString();
                }
                
                $tableHtmlCEH = new HtmlTable();
                //$tableHtmlCEH->addTableCss('left_float');
                
                $headers = array('Poprzedni pracodawca - rejestracja', 'Dodaj nowy');
                $buttons = 
                array(
                    '', 
                    $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_FORMER_EMPLOYER, self::FORM_SAVE_ADD_FORMER_EMPLOYER, 'Dodaj nowy', '', '')
                );
                
                if ($hasHistory) {
                    array_unshift($headers, 'Podmieñ');
                    array_unshift($buttons, $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_FORMER_EMPLOYER, self::FORM_SAVE_SWITCH_FORMER_EMPLOYER, 'Podmieñ', '', ''));
                }
                    
                $tableHtmlCEH->setHeader($headers);
                
                foreach ($candidateEmpHistory as $empHistoryRow) {
                    
                    $row = array(
                            '<label for="candidateEmp_'.$empHistoryRow[Model::COLUMN_PPA_ID_WIERSZ].'">'.
                                $empHistoryRow[Model::COLUMN_PPR_NAZWA].' - '.$empHistoryRow[Model::COLUMN_GRU_GRUPA_ZAWODOWA].
                            '</label>',
                            $this->htmlControls->_AddCheckbox(
                                self::FORM_CHECKBOX_NEW_EMP_IDS.'[]', 
                                'id_'.self::FORM_CHECKBOX_NEW_EMP_IDS.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ], 
                                false, 
                                '', 
                                '', 
                                $empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ]
                            )
                        );
                    if ($hasHistory) {
                        array_unshift($row, '<input type="radio" name="'.self::FORM_SWITCH_CANDIDATE.'" 
                        id="candidateEmp_'.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ].'" value="'.$empHistoryRow[Model::COLUMN_PPR_ID_WIERSZ].'" />');
                    }
                        
                    $tableHtmlCEH->addRow($row);
                }
                
                $tableHtmlCEH->addRow($buttons);
                $html .= $tableHtmlCEH->__toString();
                $html .= $this->addFormSuf();
            }
            
            return $html;
        }
        
        //TODO remove when additionals tested for correctness
        /*public function viewAdditionalInfo () {
            
            $html = '';
            
            if (isset($_POST[self::FORM_SAVE_SWITCH_ADDITIONAL_INFO])) {
                
                if (!isset($_POST[self::FORM_HIDDEN_ID_ADD_DATA]) || !strpos($_POST[self::FORM_HIDDEN_ID_ADD_DATA], self::LANG_SEP))
                    throw new ViewBadRequestException('Invalid values for additional info switch operation');
                    
                list($personInfoId, $candidateInfoId) = explode(self::LANG_SEP, $_POST[self::FORM_HIDDEN_ID_ADD_DATA]);
                
                $_personInfoId = (int)$personInfoId;
                $_candidateInfoId = (int)$candidateInfoId;
                
                if ($_personInfoId < 1 || $_candidateInfoId < 1)
                    throw new ViewBadRequestException('Invalid values for additional info switch operation');
                    
                $result = $this->person->setAdditionalInfoFromCandidate($_candidateInfoId, $_personInfoId);
                
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            if (isset($_POST[self::FORM_SAVE_ADD_ADDITIONAL_INFO])) {
                                                                      //yes, I know php will do that cast on its own anyway
                if (!isset($_POST[self::FORM_HIDDEN_ID_ADD_DATA]) || 1 > (int)$_POST[self::FORM_HIDDEN_ID_ADD_DATA])
                    throw new ViewBadRequestException('Invalid values for additional info add operation');
                    
                $_candidateInfoId = (int)$_POST[self::FORM_HIDDEN_ID_ADD_DATA];
                    
                $result = $this->person->setAdditionalInfoFromCandidate($_candidateInfoId);
                
                if ($result)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= $this->htmlControls->_AddHidden(self::FORM_HIDDEN_ID_ADD_DATA, self::FORM_HIDDEN_ID_ADD_DATA, null);
            //TODO FIXME fix that - should entirely be unnecessary
            $additionalInfoData = $this->candidate->getAdditionalInfo();
            $personAdditionalInfoData = $this->person->getAdditionalInfo();
            
            $additionalInfo = $additionalInfoData[Model::RESULT_FIELD_DATA];
            $personAdditionalInfo = $personAdditionalInfoData[Model::RESULT_FIELD_DATA];
            
            $tableHtml = new HtmlTable();
            //$tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array('Nazwa', 'Warto¶æ', 'Akcja'));
            $showTable = false;
            
            foreach ($additionalInfo as $addDataKey => $addDataRow) {
                
                if (isset($personAdditionalInfo[$addDataKey]) 
                    && ($personAdditionalInfo[$addDataKey][Model::COLUMN_DDO_WARTOSC] != $addDataRow[Model::COLUMN_DDO_WARTOSC])) {
                        
                    $showTable = true;
                    $tableHtml->addRow(
                        array(
                            $addDataRow[Model::COLUMN_DDL_NAZWA_WYSWIETLANA], 
                            $personAdditionalInfo[$addDataKey][Model::COLUMN_DDO_WARTOSC].' - '.$addDataRow[Model::COLUMN_DDO_WARTOSC],
                            $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SAVE_SWITCH_ADDITIONAL_INFO, self::FORM_SAVE_SWITCH_ADDITIONAL_INFO, 'Podmieñ', '', 
                            JsEvents::ONCLICK.'="'.self::FORM_HIDDEN_ID_ADD_DATA.'.value = \''.
                            $personAdditionalInfo[$addDataKey][Model::COLUMN_DDO_ID].self::LANG_SEP.$addDataRow[Model::COLUMN_DDO_ID].'\'"'),
                        )
                    );
                } 
                
                if (!isset($personAdditionalInfo[$addDataKey])) {
                    
                    $showTable = true;
                    $tableHtml->addRow(
                        array(
                            $addDataRow[Model::COLUMN_DDL_NAZWA_WYSWIETLANA], 
                            $addDataRow[Model::COLUMN_DDO_WARTOSC],
                            $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SAVE_ADD_ADDITIONAL_INFO, 
                            self::FORM_SAVE_ADD_ADDITIONAL_INFO, 'Dodaj', '', 
                            JsEvents::ONCLICK.'="'.self::FORM_HIDDEN_ID_ADD_DATA.'.value = '.$addDataRow[Model::COLUMN_DDO_ID].'"'),
                        ), null, 'markedRow'
                    );
                }
            }
            
            if ($showTable)
                $html .= $tableHtml->__toString();
                
            $html .= $this->addFormSuf();
            
            return $html;
        }*/
        
        /**
        * @desc designed for driving license and skills
        * params needed: submit name, error msg, 
        */
        public function viewExtrasCmp ($compensationType, $tblHeader, $idIndex, $nameIndex, $submit) {
            
            //a list with checkboxes checked by default and add button for data present in internet data and absent in person data
            $html = '';
            
            if (isset($_POST[$submit])) {
                                                              
                if (!isset($_POST[self::FORM_CHECKBOX_COMPENSATION_IDS]) || !is_array($_POST[self::FORM_CHECKBOX_COMPENSATION_IDS])) {
                    //throw new ViewBadRequestException('Bad save '.$compensationType.' request');
                    $html .= '<label class="error">Wybierz conajmniej jeden element.</label>';
                } else {
                
                    foreach ($_POST[self::FORM_CHECKBOX_COMPENSATION_IDS] as $key => $value) {
                        
                        $_value = (int)$value;
                        //cut off strings, negative numbers
                        if ($_value < 1)
                            throw new ViewBadRequestException();
                            
                        $_POST[self::FORM_CHECKBOX_COMPENSATION_IDS][$key] = $_value;
                    }
                    
                    $result = $this->person->setCompensation($compensationType, $this->person->getPersonId(), $_POST[self::FORM_CHECKBOX_COMPENSATION_IDS]);
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
            }
            
            $data = $this->candidate->getCompensation($compensationType, $this->person->getPersonId());
            if (!$data)
                return $tblHeader.' znajduj± siê na li¶cie osoby.<br />';
                
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $tableHtml = new HtmlTable(); //$data, array('nazwa')
            $tableHtml->addTableCss('left_float');
            $tableHtml->setHeader(array($tblHeader));
            
            foreach ($data as $row) {
                $tableHtml->addRow(
                    array(
                        $this->htmlControls->_AddCheckbox(
                            self::FORM_CHECKBOX_COMPENSATION_IDS.'[]', 
                            'id_'.$compensationType.self::FORM_CHECKBOX_COMPENSATION_IDS.$row[$idIndex], 
                            true, 
                            '', 
                            $row[$nameIndex], 
                            $row[$idIndex]
                        )
                    ), null, 'markedRow'
                );
            }
            
            $tableHtml->addRow(array($this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, $submit, 'id_'.$submit, 'Dodaj zaznaczone', '', '')));
            
            $html .= $tableHtml->__toString().$this->addFormSuf();
            
            return $html;
        }
    }
    
    /**
    * !!! end of view class definition
    */
    
    CommonUtils::SessionStart();
    
    try {
        $output = RegistrationCompareView::initialize();

        //change to adl user ever since
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
    	//this log is nonsense given log expects project exception, this will fail anyway
    	LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
    	$html = CommonUtils::getServerErrorMsg();
    }
    
    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    echo $html;
    echo '</body>';
    echo '</html>';
?>

