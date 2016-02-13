<?php

ini_set('display_errors', 0);
//ini_set('session.cookie_lifetime', 2400);
//ini_set('session.gc_maxlifetime', 2400);

    //session_name('EENA_REGISTRATION_FORM');
    require_once '../conf.php';
    require_once '../ui/UtilsUI.php';
    require_once '../ui/HelpersUI.php';
    require_once '../bll/definicjeKlas.php';
    require_once '../bll/queries.php';
    require_once 'bll/validationUtils.php';

    /**
    * @desc Form is generated in obfuscated manner: each field is suffixed with local session saved once used obfuscation secret
    * this way form is way harder to be spammed; after registration session is flushed, this way reusage of the same field names is not possible
    * 
    * form requires new util for inputs with label for
    */
    
    class AnkietaView extends View 
    {
        const LABEL_NAZWISKO            = 'Nazwisko';
        const LABEL_IMIE                = 'Imiê';
        const LABEL_PLEC                = 'P³eæ';
        const LABEL_DATA_URODZENIA      = 'Data urodzenia (RRRR-MM-DD)';
        const LABEL_MIEJSCE_URODZENIA   = 'Miejsce urodzenia';
        const LABEL_ULICA               = 'Ulica, numer domu';
        const LABEL_KOD_POCZTOWY        = 'Kod pocztowy';
        const LABEL_MIEJCOWOSC          = 'Miejscowo¶æ';
        const LABEL_TELEFON_STACJONARNY = 'Telefon stacjonarny';
        const LABEL_TELEFON_KOMORKOWY   = 'Telefon komórkowy';
        const LABEL_TELEFON_INNY        = 'Inny numer telefonu (w tym zagraniczny)';
        const LABEL_E_MAIL              = 'E-mail';
        const LABEL_TERMIN_WYJAZDU      = 'Ewentualny termin wyjazdu (RRRR-MM-DD)';
        const LABEL_ILOSC_TYGODNI       = 'Ilo¶æ tygodni';
        const LABEL_ZRODLO_INFORMACJI   = 'Zród³o informacji';
        const LABEL_WYKSZTALCENIE       = 'Wykszta³cenie';
        const LABEL_GRUPA_ZAWODOWA      = 'Grupa zawodowa';
        const LABEL_CHARAKTER_PRACY     = 'Charakter pracy';
        
        const FIELD_SURNAME             = 'surname';
        const FIELD_NAME                = 'name';
        const FIELD_NAME_ID             = 'name_id';
        const FIELD_GENDER              = 'gender';
        const FIELD_GENDER_ID           = 'gender_id';
        const FIELD_BIRTHDATE           = 'birth_date';  
        const FIELD_BIRTHPLACE          = 'birth_place'; 
        const FIELD_BIRTHPLACE_ID       = 'birth_place_id'; 
        const FIELD_STREET              = 'street'; 
        const FIELD_POSTCODE            = 'postcode'; 
        const FIELD_CITY                = 'city'; 
        const FIELD_CITY_ID             = 'city_id'; 
        const FIELD_PHONE               = 'phone'; 
        const FIELD_CELLPHONE           = 'cellphone'; 
        const FIELD_OTHER_PHONE         = 'otherphone'; 
        const FIELD_E_MAIL              = 'email'; 
        const FIELD_DEPARTURE_DATE      = 'departure_date'; 
        const FIELD_WEEKS_COUNT         = 'weeks_count'; 
        const FIELD_INFO_SOURCE         = 'info_source'; 
        const FIELD_INFO_SOURCE_ID      = 'info_source_id'; 
        const FIELD_EDUCATION           = 'education'; 
        const FIELD_EDUCATION_ID        = 'education_id'; 
        const FIELD_EMPLOYMENT_GROUP    = 'employment_group'; 
        const FIELD_EMPLOYMENT_GROUP_ID = 'employment_group_id'; 
        const FIELD_JOB_NATURE          = 'job_nature'; 
        const FIELD_JOB_NATURE_ID       = 'job_nature_id'; 
        const FIELD_AGGREMENT           = 'zgoda'; 

        
        //TODO session popup data fields consts, data retrieval and set
        const SESSION_LANGUAGES            = 'languageCollection';
        const SESSION_FORMER_EMPLOYERS     = 'pracCollection';
        const SESSION_ADDITIONAL_SKILLS    = 'dodUmCollection';
        const SESSION_DRIVING_LICENSE      = 'licenseCollection';
        
        const FIELD_SEPARATOR  = '_';
        const FIELD_SUFFIX_ID  = '_id';
        const FIELD_PREFIX_ID  = 'id_';
        
        const OBF_SESSION_KEY  = 'obfuscation_secret';
        //nie zadziala z powodu hidenow selectow
        const OBFUSCATE_FORM   = true;
        
        const GENDER_MALE      = 1;
        const GENDER_FEMALE    = 2;
        
        const SELF_TYPE        = 2;
        
        const JOB_NATURE_PERMANENT = 1;
        const JOB_NATURE_FREE_TIME = 2;
        const JOB_NATURE_HOLIDAYS  = 3;
        
        const INFO_SOURCE_NO_SOURCE = 1;
        
        const INPUTS_SIZE      = 30;
        const OFFSET_TABINDEX  = 100;
        
        const KEY_ID      = 'id';
        const KEY_NAZWA   = 'nazwa';
        const KEY_LABEL   = 'nazwa_wyswietlana';
        const KEY_ID_TYP  = 'id_typ';
        const KEY_EDYCJA  = 'edycja';
        
        const REGEX_DATA   = '/^\d{4}-\d{2}-\d{2}$/';
        
        private $collections = array();
        
        protected $formsFieldList = array(
            AnkietaView::FIELD_SURNAME, AnkietaView::FIELD_NAME, AnkietaView::FIELD_NAME_ID, AnkietaView::FIELD_GENDER, AnkietaView::FIELD_GENDER_ID, AnkietaView::FIELD_BIRTHDATE, AnkietaView::FIELD_BIRTHPLACE, 
            AnkietaView::FIELD_BIRTHPLACE_ID, AnkietaView::FIELD_STREET, AnkietaView::FIELD_POSTCODE, AnkietaView::FIELD_CITY, AnkietaView::FIELD_CITY_ID, AnkietaView::FIELD_CELLPHONE, AnkietaView::FIELD_OTHER_PHONE,
            AnkietaView::FIELD_E_MAIL, AnkietaView::FIELD_DEPARTURE_DATE, AnkietaView::FIELD_WEEKS_COUNT, AnkietaView::FIELD_INFO_SOURCE, AnkietaView::FIELD_INFO_SOURCE_ID, AnkietaView::FIELD_EDUCATION, 
            AnkietaView::FIELD_EDUCATION_ID, AnkietaView::FIELD_EMPLOYMENT_GROUP, AnkietaView::FIELD_EMPLOYMENT_GROUP_ID, AnkietaView::FIELD_JOB_NATURE, AnkietaView::FIELD_JOB_NATURE_ID, AnkietaView::FIELD_AGGREMENT
        );
        
        protected $genericValidations = array(
            1 => '/^tak|nie$/', //bool
            2 => '/^\d{1,}$/', //int
            3 => '/^\w{1,}$/', //string
            4 => AnkietaView::REGEX_DATA, //date
            5 => '', //daterange
        );
        
        protected $formsValidations = array(
            AnkietaView::FIELD_SURNAME             => '/^\D{3,30}$/', 
            AnkietaView::FIELD_NAME_ID             => '/^\d{1,5}$/', 
            AnkietaView::FIELD_GENDER_ID           => '/1|2/', 
            AnkietaView::FIELD_BIRTHDATE           => AnkietaView::REGEX_DATA, 
            AnkietaView::FIELD_BIRTHPLACE_ID       => '/^\d{1,5}$/',
            AnkietaView::FIELD_STREET              => '/^(.*){5,50}$/', 
            AnkietaView::FIELD_POSTCODE            => '/^\d{2}-\d{3}$/', 
            AnkietaView::FIELD_CITY                => '/^\D{3,50}$/', 
            AnkietaView::FIELD_CITY_ID             => '/^\d{1,5}$/', 
            AnkietaView::FIELD_CELLPHONE           => '/^[5-8]{1}\d{8}$/', 
            AnkietaView::FIELD_OTHER_PHONE         => '/^\d{9,16}$/', 
            AnkietaView::FIELD_E_MAIL              => '/^[\S,@,.,-]{6,35}$/', 
            AnkietaView::FIELD_DEPARTURE_DATE      => AnkietaView::REGEX_DATA, 
            AnkietaView::FIELD_WEEKS_COUNT         => '/^[1-9]{1}\d{0,1}$/', 
            AnkietaView::FIELD_INFO_SOURCE_ID      => '/^\d{1,4}$/', 
            AnkietaView::FIELD_EDUCATION_ID        => '/^\d{1,5}$/', 
            AnkietaView::FIELD_EMPLOYMENT_GROUP    => '/^\D{3,100}$/', 
            AnkietaView::FIELD_EMPLOYMENT_GROUP_ID => '/^\d{1,5}$/', 
            AnkietaView::FIELD_JOB_NATURE_ID       => '/^\d{1,5}$/',
            AnkietaView::FIELD_AGGREMENT           => '/^1$/',
        );
        
        protected $callbackValidations = array(
            AnkietaView::FIELD_BIRTHDATE           => array('ValidationUtils', 'validateDatePast'),
            AnkietaView::FIELD_DEPARTURE_DATE      => array('ValidationUtils', 'validateDateFuture'),
            AnkietaView::FIELD_E_MAIL              => array('ValidationUtils', 'validateEmail'),
        );
        
        protected $field2Label = array(
            AnkietaView::FIELD_SURNAME             => AnkietaView::LABEL_NAZWISKO, 
            AnkietaView::FIELD_NAME_ID             => AnkietaView::LABEL_IMIE, 
            AnkietaView::FIELD_GENDER_ID           => AnkietaView::LABEL_PLEC, 
            AnkietaView::FIELD_BIRTHDATE           => AnkietaView::LABEL_DATA_URODZENIA, 
            AnkietaView::FIELD_BIRTHPLACE_ID       => AnkietaView::LABEL_MIEJSCE_URODZENIA,
            AnkietaView::FIELD_STREET              => AnkietaView::LABEL_ULICA, 
            AnkietaView::FIELD_POSTCODE            => AnkietaView::LABEL_KOD_POCZTOWY, 
            AnkietaView::FIELD_CITY_ID             => AnkietaView::LABEL_MIEJCOWOSC, 
            AnkietaView::FIELD_CITY                => AnkietaView::LABEL_MIEJCOWOSC, 
            
            AnkietaView::FIELD_CELLPHONE           => AnkietaView::LABEL_TELEFON_KOMORKOWY, 
            AnkietaView::FIELD_OTHER_PHONE         => AnkietaView::LABEL_TELEFON_INNY,
            AnkietaView::FIELD_E_MAIL              => AnkietaView::LABEL_E_MAIL, 
            AnkietaView::FIELD_DEPARTURE_DATE      => AnkietaView::LABEL_TERMIN_WYJAZDU, 
            AnkietaView::FIELD_WEEKS_COUNT         => AnkietaView::LABEL_ILOSC_TYGODNI, 
            AnkietaView::FIELD_INFO_SOURCE_ID      => AnkietaView::LABEL_ZRODLO_INFORMACJI, 
            AnkietaView::FIELD_EDUCATION_ID        => AnkietaView::LABEL_WYKSZTALCENIE, 
            AnkietaView::FIELD_EMPLOYMENT_GROUP_ID => AnkietaView::LABEL_GRUPA_ZAWODOWA, 
            AnkietaView::FIELD_JOB_NATURE_ID       => AnkietaView::LABEL_CHARAKTER_PRACY,
            AnkietaView::FIELD_AGGREMENT           => 'Zgoda na przetwarzanie danych', 
        );
        
        protected $formRawData, $formParsedData, $formValidData, $errors = array();
        
        protected $dzis;
        protected $genderSource = array(
            array('id' => AnkietaView::GENDER_MALE, 'nazwa' => 'Mê¿czyzna'),
            array('id' => AnkietaView::GENDER_FEMALE, 'nazwa' => 'Kobieta'),
        );
        
        protected $boolComboSource = array(
            //array('id' => '', 'nazwa' => '--------'),
            array('id' => 'nie', 'nazwa' => 'nie'),
            array('id' => 'tak', 'nazwa' => 'tak'),
        );
        
        protected $jobNatureSource = array(
            array('id' => AnkietaView::JOB_NATURE_PERMANENT, 'nazwa' => 'Sta³a'),
            array('id' => AnkietaView::JOB_NATURE_FREE_TIME, 'nazwa' => 'Urlop'),
            array('id' => AnkietaView::JOB_NATURE_HOLIDAYS, 'nazwa' => 'Wakacje'),
        );
        
        protected $nameSource, $citySource, $infoSource, $educationSource;
        
        protected $obfSec, $qBase, $addElements, $addFormElements;
        
        public $forceSourceId;
        
        public function __construct ()
        {
            parent::__construct(View::LOG_IN_LEVEL_NONE, User::PRIV_NONE_REQUIRED);
            $this->dzis = date('Y-m-d');
            $this->obfSec = isset($_SESSION[AnkietaView::OBF_SESSION_KEY]) ? $_SESSION[AnkietaView::OBF_SESSION_KEY] : uniqid(microtime(), true);
            $_SESSION[AnkietaView::OBF_SESSION_KEY] = $this->obfSec;
            
            $this->qBase = new QueriesBase(
                array (
                    QueriesBase::CONF_ADD_COLUMNS => 'dod_kolumny_ankieta',
                    QueriesBase::CONF_ADD_QUERY_DATA => 'select id, nazwa, nazwa_wyswietlana, id_typ, edycja from dane_dodatkowe_lista where id in (select id_dane_dodatkowe_lista from dane_dodatkowe_internet_lista) order by nazwa;',
                    QueriesBase::CONF_DATA_TABLE => 'dane_dodatkowe_ankieta',
                    QueriesBase::CONF_DATA_TABLE_FKEY => 'id_dane_dodatkowe_internet_lista',
                ), true
            );
    
            $this->addElements = $this->qBase->getAdditionalColumns();
            
            foreach ($this->addElements as $addElement)
            {
                if ('t' === $addElement[self::KEY_EDYCJA])
                {
                    $this->addFormElements[$addElement[self::KEY_NAZWA]] = $addElement;
                    $this->formsFieldList[] = $addElement[self::KEY_NAZWA];
                    
                    $this->formsValidations[$addElement[self::KEY_NAZWA]] = $this->genericValidations[$addElement[self::KEY_ID_TYP]];
                    $this->field2Label[$addElement[self::KEY_NAZWA]] = $addElement[self::KEY_LABEL];
                }
            }

            if (isset($_SESSION[self::SESSION_FORMER_EMPLOYERS]))
            {
                $this->collections[self::SESSION_FORMER_EMPLOYERS] = unserialize($_SESSION[self::SESSION_FORMER_EMPLOYERS]);
            }
            
            if (isset($_SESSION[self::SESSION_ADDITIONAL_SKILLS]))
            {
                $this->collections[self::SESSION_ADDITIONAL_SKILLS] = unserialize($_SESSION[self::SESSION_ADDITIONAL_SKILLS]);
            }
            
            if (isset($_SESSION[self::SESSION_DRIVING_LICENSE]))
            {
                $this->collections[self::SESSION_DRIVING_LICENSE] = unserialize($_SESSION[self::SESSION_DRIVING_LICENSE]);
            }
            if (isset($_SESSION[self::SESSION_LANGUAGES]))
            {
                $this->collections[self::SESSION_LANGUAGES] = unserialize($_SESSION[self::SESSION_LANGUAGES]);
            }
        }
        
        public function getElementName($name, $isId = false) //protected
        {
            $normalName = $name.$this->obfSec;
            
            if (self::OBFUSCATE_FORM === false) 
            {
                if (false === $isId)
                    return $name;
                    
                return AnkietaView::FIELD_PREFIX_ID.$name;
            }
            
            if (true === $isId)
                $normalName = AnkietaView::FIELD_PREFIX_ID.$normalName;

            return md5($normalName);
        }
        
        protected function getElementId($name)
        {
            if (self::OBFUSCATE_FORM === false) //&& false === $isId
                return $name.AnkietaView::FIELD_SUFFIX_ID;
                
            $normalName = $name.AnkietaView::FIELD_SUFFIX_ID.$this->obfSec;

            return md5($normalName);
        }
        
        protected function parseFormData ()
        {
            foreach ($this->formsFieldList as $formField)
            {
                $this->formParsedData[$formField] = $this->formRawData[$this->getElementName($formField)];
            }
        }
        
        protected function validateFormData ()
        {
            foreach ($this->formsValidations as $field => $regex)
            {
                $result = preg_match($regex, $this->formParsedData[$field]);
                if (1 === $result)
                    $this->formValidData[$field] = $this->formParsedData[$field];
                else
                {
                    $this->errors[$field] = 1;
                    $this->formValidData[$field] = null;
                }
            }

            foreach ($this->callbackValidations as $field => $callback)
            {
                if ($this->formValidData[$field])
                {
                    if (false === call_user_func($callback, $this->formValidData[$field]))
                    {
                        $this->errors[$field] = 1;
                        $this->formValidData[$field] = null;
                    }
                }
            }
        }
        
        
        protected function addRecord ()
        {
            //get record id
            $seqNext = $this->controls->dalObj->PobierzDane('select nextval(\'dane_internet_id_seq\') as seq;');
            $newId = $seqNext[0]['seq'];
            
            $source = isset($_GET['source']) && $_GET['source'] == 'adwords1' ? 4 : 1; 
            
            $query = sprintf('insert into dane_internet (id, id_imie, nazwisko, id_plec, data_urodzenia, id_miejscowosc_ur, id_miejscowosc, ulica, kod, telefon, komorka, email, id_wyksztalcenie, id_zawod, data_zgloszenia, 
            id_charakter, data, ilosc_tyg, id_ankieta, id_zrodlo, source, inny_tel) 
            values(%d, %d, \'%s\', %d, \'%s\', %d, %d, \'%s\', \'%s\', 0, %d, \'%s\', %d, %d, \'%s\', %d, \'%s\', %d, %d, %d, %d, \'%s\');',
            $newId,
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_NAME_ID]), 
            ucfirst(strtolower($this->controls->dalObj->escapeString($this->formValidData[self::FIELD_SURNAME]))), 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_GENDER_ID]), 
            $this->controls->dalObj->escapeString($this->formValidData[self::FIELD_BIRTHDATE]), 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_BIRTHPLACE_ID]), 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_CITY_ID]), 
            $this->controls->dalObj->escapeString($this->formValidData[self::FIELD_STREET]), 
            $this->controls->dalObj->escapeString($this->formValidData[self::FIELD_POSTCODE]), 
            
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_CELLPHONE]),
            $this->controls->dalObj->escapeString($this->formValidData[self::FIELD_E_MAIL]), 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_EDUCATION_ID]), 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_EMPLOYMENT_GROUP_ID]), 
            $this->dzis, 
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_JOB_NATURE_ID]),
            $this->controls->dalObj->escapeString($this->formValidData[self::FIELD_DEPARTURE_DATE]),
            $this->controls->dalObj->escapeInt($this->formValidData[self::FIELD_WEEKS_COUNT]),
            self::SELF_TYPE,
            $this->controls->dalObj->escapeInt($source == 4 ? INFO_SOURCE_ID_INTERNET_ADWORDS : $this->formValidData[self::FIELD_INFO_SOURCE_ID]),
            $source,
            $this->controls->dalObj->escapeInt64($this->formValidData[self::FIELD_OTHER_PHONE])
            );
            
            $this->controls->dalObj->pgQuery($query);
            
            if (isset($this->collections[self::SESSION_FORMER_EMPLOYERS]))
            {
                //$empCollection = unserialize($_SESSION[self::SESSION_FORMER_EMPLOYERS]);
                $this->collections[self::SESSION_FORMER_EMPLOYERS]->saveToDb($this->controls->dalObj, $newId);
            }
            
            if (isset($this->collections[self::SESSION_ADDITIONAL_SKILLS]))
            {
                //$skillsCollection = unserialize($_SESSION[self::SESSION_ADDITIONAL_SKILLS]);
                $this->collections[self::SESSION_ADDITIONAL_SKILLS]->saveToDb($this->controls->dalObj, $newId);
            }
            
            if (isset($this->collections[self::SESSION_DRIVING_LICENSE]))
            {
                //$licenseCollection = unserialize($_SESSION[self::SESSION_DRIVING_LICENSE]);
                $this->collections[self::SESSION_DRIVING_LICENSE]->saveToDb($this->controls->dalObj, $newId);
                /*if (false === $licenseCollection->isLicensed())
                    $this->formValidData['posiada_pr_j'] = 'nie';
                if (true === $licenseCollection->isLicensed())
                    $this->formValidData['posiada_pr_j'] = 'tak';*/
            }
            if (isset($this->collections[self::SESSION_LANGUAGES]))
            {
                //$languagesCollection = unserialize($_SESSION[self::SESSION_LANGUAGES]);
                $this->collections[self::SESSION_LANGUAGES]->saveToDb($this->controls->dalObj, $newId);
                /*if (false === $languagesCollection->isLanguaged())
                    $this->formValidData['zna_jezyk'] = 'nie';
                if (true === $languagesCollection->isLanguaged())
                    $this->formValidData['zna_jezyk'] = 'tak';*/
            }
            
            $this->qBase->setAdditionalColumnsData($newId, $this->formValidData);
            return true;
        }
        
        public function setNoLanguage () {
            
            if (!isset($this->collections[self::SESSION_LANGUAGES]))
                $this->collections[self::SESSION_LANGUAGES] = new JezykiObceCollection();
                
            $this->collections[self::SESSION_LANGUAGES]->hasLanguage(false);
            $_SESSION[self::SESSION_LANGUAGES] = serialize($this->collections[self::SESSION_LANGUAGES]);
        }
        
        public function setNoSkills () {
                
            if (!isset($this->collections[self::SESSION_ADDITIONAL_SKILLS]))
                $this->collections[self::SESSION_ADDITIONAL_SKILLS] = new DodatkoweUmiejetnosciCollection();
                
            $this->collections[self::SESSION_ADDITIONAL_SKILLS]->hasSkills(false);
            $_SESSION[self::SESSION_ADDITIONAL_SKILLS] = serialize($this->collections[self::SESSION_ADDITIONAL_SKILLS]);
        }
                
        public function setNoDrvLicense () {
            
            if (!isset($this->collections[self::SESSION_DRIVING_LICENSE]))
                $this->collections[self::SESSION_DRIVING_LICENSE] = new PrawoJazdyCollection();
                
            $this->collections[self::SESSION_DRIVING_LICENSE]->hasLicense(false);
            $_SESSION[self::SESSION_DRIVING_LICENSE] = serialize($this->collections[self::SESSION_DRIVING_LICENSE]);
        }
        
        public function setNoFormerEmployer () {
                
            if (!isset($this->collections[self::SESSION_FORMER_EMPLOYERS]))
                $this->collections[self::SESSION_FORMER_EMPLOYERS] = new PoprzedniPracodawcaCollection();
                
            $this->collections[self::SESSION_FORMER_EMPLOYERS]->hasExperience(false);
            $_SESSION[self::SESSION_FORMER_EMPLOYERS] = serialize($this->collections[self::SESSION_FORMER_EMPLOYERS]);
        }
        
        public function getFormValidData()
        {
            foreach ($this->formValidData as $key => $value)
            {
                $this->formValidData[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES);
            }
            
            return $this->formValidData;
        }
        
        public function submitForm ($formData)
        {
            $this->formRawData = $formData;
            //parse data
            $this->parseFormData();
            //validate data
            $this->validateFormData();
            //add person or generate form again
            
            if (sizeof($this->errors))
            {
                //wszystkie 3 sa zle
                if (isset($this->errors[AnkietaView::FIELD_CELLPHONE], $this->errors[AnkietaView::FIELD_OTHER_PHONE], $this->errors[AnkietaView::FIELD_E_MAIL]))
                    return $this->errors;

                unset($this->errors[AnkietaView::FIELD_CELLPHONE]);
                unset($this->errors[AnkietaView::FIELD_OTHER_PHONE]);
                unset($this->errors[AnkietaView::FIELD_E_MAIL]);
                
                if (sizeof($this->errors))
                    return $this->errors;
                
            }
        }
        
        public function sendForm ($formData) {
                
            $result = $this->submitForm($formData);
            if (is_array($result))
                return $result;
                
            return $this->addRecord();
        }
        
        public function getForm ( $data = array()) 
        {
            $this->nameSource = $this->controls->dalObj->PobierzDane('select id, nazwa from imiona order by nazwa asc;');
            $this->citySource = $this->controls->dalObj->PobierzDane('select id, nazwa from miejscowosc order by nazwa asc;');
            $this->infoSource = $this->controls->dalObj->PobierzDane('select id, nazwa from zrodlo where widoczne = true order by nazwa asc;');
            $this->educationSource = $this->controls->dalObj->PobierzDane('select id, nazwa from wyksztalcenie order by nazwa asc;');
            
            $result = '<div class="headDiv" align="center">
            Wype³nienie formularza rejestracyjnego jest konieczne, aby podj±æ pracê za po¶rednictwem 
            <br />
            naszej agencji zatrudnienia. Jednocze¶nie informujemy, ¿e bêdziemy siê kontaktowaæ wy³±cznie 
            <br />
            z osobami, których kwalifikacje bêd± odpowiadaæ wymaganiom stawianym przez naszych pracodawców.
            </div>';
                        
            $elements = array(
                array(
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_SURNAME), $this->getElementName(self::FIELD_SURNAME, true), $data[self::FIELD_SURNAME], self::LABEL_NAZWISKO, 30, self::INPUTS_SIZE, 'required rightFloat', 1, 'onkeypress="return validations.TextValidate(this, event);"'), 
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_OTHER_PHONE), $this->getElementName(self::FIELD_OTHER_PHONE, true), $data[self::FIELD_OTHER_PHONE], self::LABEL_TELEFON_INNY, 16, self::INPUTS_SIZE, 'psrequired rightFloat', 101, 'onkeypress="return validations.OnlyNumber(this, event);" onchange="return validations.ExtraPhoneValidate(this, event);"'),
                ),
                array(
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_NAME), $this->getElementName(self::FIELD_NAME, true), self::LABEL_IMIE, '', $this->nameSource, $data[self::FIELD_NAME_ID], $this->getElementId(self::FIELD_NAME), 2, 'rightFloat'), 
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_E_MAIL), $this->getElementName(self::FIELD_E_MAIL, true), $data[self::FIELD_E_MAIL], self::LABEL_E_MAIL, 50, self::INPUTS_SIZE, 'psrequired rightFloat', 102, 'onchange="return validations.EmailCheck(this, event);"'),
                ),
                array(
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_GENDER), $this->getElementName(self::FIELD_GENDER, true), self::LABEL_PLEC, '', $this->genderSource, $data[self::FIELD_GENDER_ID], $this->getElementId(self::FIELD_GENDER), 3, 'rightFloat'), 
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_DEPARTURE_DATE), $this->getElementName(self::FIELD_DEPARTURE_DATE, true), $data[self::FIELD_DEPARTURE_DATE], self::LABEL_TERMIN_WYJAZDU, 10, self::INPUTS_SIZE, 'required rightFloat', 103, 'onkeypress="return validations.DateValidate(this, event);" onchange="return validations.FutureDateCheck(this, event);"'),
                ),
                array(
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_BIRTHDATE), $this->getElementName(self::FIELD_BIRTHDATE, true), $data[self::FIELD_BIRTHDATE], self::LABEL_DATA_URODZENIA, 10, self::INPUTS_SIZE, 'required rightFloat', 4, 'onkeypress="return validations.DateValidate(this, event);" onchange="return validations.BirthDateCheck(this, event);"'), 
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_WEEKS_COUNT), $this->getElementName(self::FIELD_WEEKS_COUNT, true), $data[self::FIELD_WEEKS_COUNT], self::LABEL_ILOSC_TYGODNI, 2, self::INPUTS_SIZE, 'required rightFloat', 104, 'onkeypress="return validations.OnlyNumber(this, event);"'),
                ),
                array(
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_BIRTHPLACE), $this->getElementName(self::FIELD_BIRTHPLACE, true), self::LABEL_MIEJSCE_URODZENIA, '', $this->citySource, $data[self::FIELD_BIRTHPLACE_ID], $this->getElementId(self::FIELD_BIRTHPLACE), 5, 'rightFloat'), 
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_INFO_SOURCE), $this->getElementName(self::FIELD_INFO_SOURCE, true), self::LABEL_ZRODLO_INFORMACJI, '', $this->infoSource, $data[self::FIELD_INFO_SOURCE_ID], $this->getElementId(self::FIELD_INFO_SOURCE), 105, 'rightFloat', '', $this->forceSourceId), 
                ),
                array(
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_STREET), $this->getElementName(self::FIELD_STREET, true), $data[self::FIELD_STREET], self::LABEL_ULICA, 50, self::INPUTS_SIZE, 'required rightFloat', 6, ''), 
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_EDUCATION), $this->getElementName(self::FIELD_EDUCATION, true), self::LABEL_WYKSZTALCENIE, '', $this->educationSource, $data[self::FIELD_EDUCATION_ID], $this->getElementId(self::FIELD_EDUCATION), 106, 'rightFloat'), 
                ),
                
                array(
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_POSTCODE), $this->getElementName(self::FIELD_POSTCODE, true), $data[self::FIELD_POSTCODE], self::LABEL_KOD_POCZTOWY, 6, self::INPUTS_SIZE, 'required rightFloat', 7, 'onkeypress="return validations.DateValidate(this, event);"'), 
                    valControl::_PopupChoiceControl($this->getElementName(self::FIELD_EMPLOYMENT_GROUP), $this->getElementName(self::FIELD_EMPLOYMENT_GROUP, true), $data[self::FIELD_EMPLOYMENT_GROUP], 
                        $this->getElementName(self::FIELD_EMPLOYMENT_GROUP_ID), $this->getElementName(self::FIELD_EMPLOYMENT_GROUP_ID, true), $data[self::FIELD_EMPLOYMENT_GROUP_ID], self::LABEL_GRUPA_ZAWODOWA, 'rightFloat', 
                        "wybor_grupy_zaw.php", "grupa_zawodowa", '', 106, 'Naci¶nij WYBIERZ, by okre¶liæ swoj± przynale¿no¶æ do grupy zawodowej.'),
                ),
                
                array(
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_CITY), $this->getElementName(self::FIELD_CITY, true), self::LABEL_MIEJCOWOSC, '', $this->citySource, $data[self::FIELD_CITY_ID], $this->getElementId(self::FIELD_CITY), 7, 'rightFloat'),
                    valControl::_AddSelectWithData($this->getElementName(self::FIELD_JOB_NATURE), $this->getElementName(self::FIELD_JOB_NATURE, true), self::LABEL_CHARAKTER_PRACY, '', $this->jobNatureSource, $data[self::FIELD_JOB_NATURE_ID], $this->getElementId(self::FIELD_JOB_NATURE), 107, 'rightFloat'), 
                ),
//                array(
//                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_PHONE), $this->getElementName(self::FIELD_PHONE, true), $data[self::FIELD_PHONE], self::LABEL_TELEFON_STACJONARNY, 9, self::INPUTS_SIZE, 'psrequired rightFloat', 8, 'onkeypress="return validations.OnlyNumber(this, event);" onchange="return validations.PhoneValidate(this, event);"'), 
//                ),
                array(
                    valControl::_AddInputWithLabel($this->getElementName(self::FIELD_CELLPHONE), $this->getElementName(self::FIELD_CELLPHONE, true), $data[self::FIELD_CELLPHONE], self::LABEL_TELEFON_KOMORKOWY, 9, self::INPUTS_SIZE, 'psrequired rightFloat', 101, 'onkeypress="return validations.OnlyNumber(this, event);" onchange="return validations.PhoneValidate(this, event, true);"'),
                ),
            );
            $tabIndex = 8;
            $addElements = $this->addFormElements;
           
            // this if compensates for the form odd fields count
            if (sizeof($addElements))
            {
                $element = array_shift($addElements);
                $elements[(sizeof($elements) - 1)][] = $this->getGenericControl($element, ($tabIndex + self::OFFSET_TABINDEX), $data[$element[self::KEY_NAZWA]]);
            }
            $addElementsCount = 0;
            $tabIndex++;
            $controlsRow = array();
            
            foreach($addElements as $element) 
            {
                $addElementsCount++;
                $odd = (($addElementsCount % 2) == 1);
                
                if (true === $odd)
                {
                    $controlsRow = array();
                    $controlsRow[] = $this->getGenericControl($element, $tabIndex, $data[$element[self::KEY_NAZWA]]);
                }
                else 
                {
                    $controlsRow[] = $this->getGenericControl($element, ($tabIndex + self::OFFSET_TABINDEX), $data[$element[self::KEY_NAZWA]]);
                    $elements[] = $controlsRow;
                    $tabIndex++;
                    $controlsRow = array();
                }
            }
            if (sizeof($controlsRow))
                $elements[] = $controlsRow;

            $result .= $this->controls->AddSelectHelpHidden().
            '<table id="mainFormTable" align="center" class="gridTable" border="0" cellspacing="0" cellpadding="3">';
            $count = 0;
            foreach ($elements as $elementsRow) {
                
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                $secondElement = isset($elementsRow[1]) ? $elementsRow[1] : '';
                $result .= '<tr class="'.$css.'">
                    <td>
                        '.$elementsRow[0].'
                    </td>
                    <td>
                        '.$secondElement.'
                    </td>
                </tr>';
            }

            //get session data and fill it
            $langsContent = '';
            $licensesContent = '';
            $employmentContent = '';
            $skillsContent = '';
            
            if (isset($this->collections[self::SESSION_LANGUAGES]))
                $langsContent = $this->collections[self::SESSION_LANGUAGES]->renderInfo();
                
            if (isset($this->collections[self::SESSION_ADDITIONAL_SKILLS]))
                $skillsContent = $this->collections[self::SESSION_ADDITIONAL_SKILLS]->renderInfo();
                
            if (isset($this->collections[self::SESSION_DRIVING_LICENSE]))
                $licensesContent = $this->collections[self::SESSION_DRIVING_LICENSE]->renderInfo();
                
            if (isset($this->collections[self::SESSION_FORMER_EMPLOYERS]))
                $employmentContent = $this->collections[self::SESSION_FORMER_EMPLOYERS]->renderInfo();
                
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            $result .= '<tr class="'.$css.'">
                <td  class="extrasContainer">
                    <div class="divExtraInfo" id="jezykiObce">'.$langsContent.'</div>
                    <div>
                        '.valControl::_AddSubmit('foreignLangs', 'idForeignLangs', 'Wybierz jêzyki obce', 'blocks', 'onclick="FillLanguages();"', 'button').
                        valControl::_AddSubmit('nie_znam_jezykow', 'id_nie_znam_jezykow', 'Nie znam jêzyka obcego', 'blocks', '').'
                    </div>
                </td>

                <td class="extrasContainer">
                    <div class="divExtraInfo" id="prawoJazdy">'.$licensesContent.'</div>
                    <div>
                        '.valControl::_AddSubmit('driversLicense', 'idDriversLicense', 'Wybierz prawo jazdy', 'blocks', 'onclick="FillDriversLicense();"', 'button').
                        valControl::_AddSubmit('nie_mam_prawa_jazdy', 'id_nie_mam_prawa_jazdy', 'Nie mam prawa jazdy', 'blocks', '').'
                    </div>
                </td>

            </tr>';
            //valign="top"
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            $result .= '<tr class="'.$css.'">
                <td class="extrasContainer">
                    <div class="divExtraInfo" id="poprzedniPracodawca">'.$employmentContent.'</div>
                    <div>'.
                        valControl::_AddSubmit('employmentHistory', 'idEmploymentHistory', 'Podaj historiê zatrudnienia', 'blocks', 'onclick="FillFormerEmployees();"', 'button').
                        valControl::_AddSubmit('nie_mam_dosw_zawod', 'id_nie_mam_dosw_zawod', 'Jeszcze nie pracowa³em/am', 'blocks', '').'
                    </div>
                </td>

                <td class="extrasContainer">
                    <div class="divExtraInfo" id="dodatkoweUmiejetnosci">'.$skillsContent.'</div>
                    <div>
                        '.valControl::_AddSubmit('additionalSkills', 'idAdditionalSkills', 'Podaj dodatkowe umiejêtno¶ci', 'blocks', 'onclick="FillAdditionalSkills();"', 'button').
                        valControl::_AddSubmit('nie_mam_umiejetnosci', 'id_nie_mam_umiejetnosci', 'Nie mam umiejêtno¶ci', 'blocks', '').'
                    </div>
                </td>

            </tr>';

            $result .= '</table>';

            return $result;
        }
        
        public function getErrorMsg ()
        {
            $errFields = array_intersect_key($this->field2Label, $this->errors);
            
            return '<div class="errorMsg"><b>Nieprawid³owe dane w nastêpuj±cych polach: </b><br /> '.implode(', ', $errFields).'</div>';
        }
        
        protected function getGenericControl ($element, $tabindex, $value)
        {
            $result = '';

            switch ($element['id_typ'])
            {
                
                case QueriesBase::VALIDATION_INT: 
                $result .= valControl::_AddInputWithLabel($this->getElementName($element[self::KEY_NAZWA]), $this->getElementName($element[self::KEY_NAZWA], true), $value, $element[self::KEY_LABEL], 10, self::INPUTS_SIZE, 'required rightFloat', ($tabindex), 'onkeypress="return validations.OnlyNumber(this, event);"'); 
                break;
                case QueriesBase::VALIDATION_STRING:
                $result .= valControl::_AddInputWithLabel($this->getElementName($element[self::KEY_NAZWA]), $this->getElementName($element[self::KEY_NAZWA], true), $value, $element[self::KEY_LABEL], 50, self::INPUTS_SIZE, 'required rightFloat', ($tabindex), 'onkeypress="return validations.TextValidate(this, event);"');
                break;
                case QueriesBase::VALIDATION_BOOL: 
                $result .= valControl::_AddSelectWithData($this->getElementName($element[self::KEY_NAZWA]), $this->getElementName($element[self::KEY_NAZWA], true), $element[self::KEY_LABEL], '', $this->boolComboSource, $value, $this->getElementId($element[self::KEY_NAZWA]), $tabindex, 'rightFloat');
                break;
            }
            
            return $result;
        }
        
        public function run () {
            
            
        }
    }
    
    try {

    CommonUtils::SessionStart();
    setcookie('ciacho');
    
    $output = new AnkietaView();
    
    $forceSourceId = isset($_GET['sourceId']) ? (int)$_GET['sourceId'] : null;
    if(!is_null($forceSourceId)) {
        $output->forceSourceId = true;
    }
    
    $data = array(
        AnkietaView::FIELD_SURNAME => '',
        AnkietaView::FIELD_NAME => '',
        AnkietaView::FIELD_NAME_ID => null,
        AnkietaView::FIELD_GENDER => '',
        AnkietaView::FIELD_GENDER_ID => null,
        AnkietaView::FIELD_BIRTHDATE => '',
        AnkietaView::FIELD_BIRTHPLACE => '',
        AnkietaView::FIELD_BIRTHPLACE_ID => null,
        AnkietaView::FIELD_STREET => '',
        AnkietaView::FIELD_POSTCODE => '',
        AnkietaView::FIELD_CITY => '',
        AnkietaView::FIELD_CITY_ID => '',
        
        AnkietaView::FIELD_OTHER_PHONE => '',
        
        AnkietaView::FIELD_CELLPHONE => '',
        AnkietaView::FIELD_E_MAIL => '',
        AnkietaView::FIELD_DEPARTURE_DATE => '',
        AnkietaView::FIELD_WEEKS_COUNT => '',
        AnkietaView::FIELD_INFO_SOURCE => AnkietaView::INFO_SOURCE_NO_SOURCE,
        AnkietaView::FIELD_INFO_SOURCE_ID => $forceSourceId,
        AnkietaView::FIELD_EDUCATION => null,
        AnkietaView::FIELD_EDUCATION_ID => null,
        AnkietaView::FIELD_EMPLOYMENT_GROUP => null,
        AnkietaView::FIELD_EMPLOYMENT_GROUP_ID => null,
        AnkietaView::FIELD_JOB_NATURE => null,
        AnkietaView::FIELD_JOB_NATURE_ID => null,
        'wzrost' => null,
        'czyKarany' => null,
    );
        
    $error = '';
    
    // Service popup 'i don't/I can't' buttons.
    if (sizeof($_POST) && !isset($_POST['add'])) 
    {
        $output->submitForm($_POST);
        $data = $output->getFormValidData();
        
        if (isset($_POST['nie_znam_jezykow']))
        {
            $output->setNoLanguage();
        }
        
        if (isset($_POST['nie_mam_umiejetnosci']))
        {
            $output->setNoSkills();
        }
        
        if (isset($_POST['nie_mam_dosw_zawod']))
        {
            $output->setNoFormerEmployer();
        }
        
        if (isset($_POST['nie_mam_prawa_jazdy']))
        {
            $output->setNoDrvLicense();
        }
    }
    
    
    if (sizeof($_POST) && isset($_POST['add'])) 
    {
        $result = $output->sendForm($_POST);
        if (is_array($result))
        {
            $error = $output->getErrorMsg();
            $data = $output->getFormValidData();
        }
        else
        {
            foreach($_SESSION as $key => $value)
            {
                unset($_SESSION[$key]);
            }
            $_SESSION['congratulations'] = true;
            session_write_close();
            header('HTTP/1.1 302');
            header('Location: '.$_SERVER['PHP_SELF']);
        }
    }
    
    $cssFile = 'ankieta';
    if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        $cssFile .= '_ie';
    
    //onload="document.getElementById(\''.$output->getElementName('span_'.AnkietaView::FIELD_CITY).'\').innerHTML = document.getElementById(\''.$output->getElementName(AnkietaView::FIELD_CITY, true).'\').value;"
    echo '<html>
    <head>
        <meta HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
        <script language="javascript" src="../js/script.js"></script>
        <script language="javascript" src="jquery.js"></script>
        <script language="javascript" src="../js/validations.js"></script>
        <script language="javascript" src="../js/utils.js"></script>
        <script language="javascript" src="utils.js"></script>
        <link href="../css/reset.css" rel="stylesheet" type="text/css">
        <link href="../css/'.$cssFile.'.css" rel="stylesheet" type="text/css">
    </head>
    <body onload="alertRegFormCookie(\'ciacho\', \'W³±czenie obs³ugi ciasteczek jest konieczne, formularz nie mo¿e zostaæ poprawnie wype³niony.\', \'cookieAlertMsgBox\', \'mainFormTable,agreementTable,bottomSummaryPlaceHolder,add\');">
    <div id="popup" style="display: none;"></div>';
    
    if (isset($_SESSION['congratulations']) && $_SESSION['congratulations'] === true)
    {
        unset($_SESSION['congratulations']);
        echo '
        <div class="masterCongrats">
        <div class="congratsContainer">
            Formularz zosta³ poprawnie wype³niony i przes³any. Dziêkujemy. <br /><br />
            <a href="'.$_SERVER['PHP_SELF'].'">Powrót</a>
        </div>
        </div>';
    }
    else
    {
        echo $output->addFormPostPre($_SERVER['REQUEST_URI']); //'<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        // Print entire form.
        echo $output->getForm($data);
        
        echo '<table id="agreementTable" align="center"><tr>
        </tr>
        <tr ><td colspan="2">'.$error.'</td></tr>
        <tr><td>'.
        $output->getControls()->_AddCheckbox($output->getElementName(AnkietaView::FIELD_AGGREMENT), AnkietaView::FIELD_AGGREMENT, false, 'onclick="blur();"', '', 1)
        .'</td><td style="width: 880px">';
        //onchange = "checkAll();"
        echo '
        <span><label for="'.AnkietaView::FIELD_AGGREMENT.'">
            Wyra¿am zgodê na przetwarzanie moich danych osobowych zawartych w niniejszym formularzu dla potrzeb realizacji obecnych i przysz³ych procesów rekrutacyjnych 
            zgodnie z Ustaw± z dnia 29 sierpnia 1997 r. o ochronie danych osobowych ( Dz. U. Nr 133, poz. 883 z pó¼n. zm. ). <br/>
            Jednocze¶nie potwierdzam, i¿ zosta³em(am) poinformowany(a) o przys³uguj±cym mi prawie do wgl±du, poprawiania i usuniêcia moich danych osobowych. <br/>
            Administratorem danych osobowych jest E&A Sp. z o.o. z siedzib± przy ul. Ko³³±taja 3/1 w Opolu. Podanie danych osobowych jest dobrowolne.
        </span></label>
        </td></tr></table>
        
        <div id="bottomSummaryPlaceHolder" class="bottomSummary" align="center">Pole wy¶lij jest nieaktywne dopóki wszystkie wymagane (czerwone) pola nie s± zape³nione. Dodatkowo konieczne jest <br />uzupe³nienie przynajmniej jednego pola zielonego, oraz zgoda na przetwarzanie danych osobowych.</div>
        
        <div class="errorMsg" id="cookieAlertMsgBox" style="width: 100%;" align="center"></div>';
        
        echo '<div align="center">'.$output->getControls()->AddSubmit('add', 'add', 'Wy¶lij', 'onclick="return CheckAll();"', '').'</div>';//disabled
        
        echo '</form>';
    }
    
    } catch (ViewException $e) {
    
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
        echo CommonUtils::getViewExceptionMessage($e);
        if (!isset($_COOKIE['ciacho']))
        {
            echo 'Wymagana obs³uga ciasteczek jest wy³±czona.';
        }
    } catch (Exception $e) {
        
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
        echo CommonUtils::getServerErrorMsg();
    }
    
?>
</body>
</html>