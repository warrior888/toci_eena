<?php
    require_once '../conf.php';
    require_once 'adl/Person.php';
    require_once 'bll/validationUtils.php';
    
    abstract class ContactStrategy {
        
        protected $person;
        
        public function __construct(Person $person) {
            
            $this->person = $person;
        }
        
        public function getPrefixedName ($name) {
            
            return $this->getPrefix().$name;
        }
        
        abstract public function getPrefix();
        abstract public function validate($contact);
        abstract public function santize($contact);
        abstract public function set($setItem, $replaceItem = null, $allowDelete = false);
        abstract public function get();
        abstract public function getColumnName();
        abstract public function getColumnId();
        abstract public function getLabel();
        abstract public function getTextField(HtmlControls $controls, $name, $value, $errMsg); 
    }
    
    class PhonesStrategy extends ContactStrategy {
        
        public function __construct(Person $person) {
            
            parent::__construct($person);
        }
        
        public function getPrefix() {
            
            return 'REGULAR_PHONE';
        }
        
        public function validate($contact) {
            
            return ValidationUtils::validatePhone($contact);
        }
        
        public function santize($contact) {
            
            return (int)$contact;
        }
        
        public function set($setItem, $replaceItem = null, $allowDelete = false) {
            
            return $this->person->setPhone($setItem, $replaceItem, $allowDelete);
        }
                              
        public function get() {
            
            return $this->person->getPhones();
        }
        
        public function getColumnName() {
            
            return Model::COLUMN_TEL_NAZWA;
        }
        
        public function getColumnId() {
            
            return Model::COLUMN_TEL_ID_WIERSZ;
        }
        
        public function getLabel() {
            
            return 'Telefon stacjonarny';
        }
        
        public function getTextField(HtmlControls $controls, $name, $value, $errMsg) {
            
            return $controls->_AddNumberbox($name, $name, $value, 9, 12, 'onblur="validations.PhoneValidate(this, event);"', '', $errMsg);
        }
    }
    
    class ExtraPhonesStrategy extends ContactStrategy {
        
        public function __construct(Person $person) {
            
            parent::__construct($person);
        }
        
        public function getPrefix() {
            
            return 'OTHER_PHONE';
        }
        
        public function validate($contact) {
            
            return ValidationUtils::validateExtraPhone($contact);
        }
        
        public function santize($contact) {
            
            // extra phone exceeds 2 ^ 31
            return $contact;
        }
        
        public function set($setItem, $replaceItem = null, $allowDelete = false) {
            
            return $this->person->setExtraPhone($setItem, $replaceItem, $allowDelete);
        }
                              
        public function get() {
            
            return $this->person->getExtraPhones();
        }
        
        public function getColumnName() {
            
            return Model::COLUMN_TEI_NAZWA;
        }
        
        public function getColumnId() {
            
            return Model::COLUMN_TEI_ID_WIERSZ;
        }
        
        public function getLabel() {
            
            return 'Telefon inny';
        }
        
        public function getTextField(HtmlControls $controls, $name, $value, $errMsg) {
            
            return $controls->_AddNumberbox($name, $name, $value, 16, 18, 'onblur="validations.ExtraPhoneValidate(this, event);"', '', $errMsg);
        }
    }
    
    class CellsStrategy extends ContactStrategy {
        
        public function __construct(Person $person) {
            
            parent::__construct($person);
        }
        
        public function getPrefix() {
            
            return 'CELL_PHONE';
        }
        
        public function validate($contact) {
            
            return ValidationUtils::validatePhone($contact);
        }
        
        public function santize($contact) {
            
            return (int)$contact;
        }
        
        public function set($setItem, $replaceItem = null, $allowDelete = false) {
            
            return $this->person->setCell($setItem, $replaceItem, $allowDelete);
        }
                              
        public function get() {
            
            return $this->person->getCell();
        }
                
        public function getColumnName() {
            
            return Model::COLUMN_TEK_NAZWA;
        }
        
        public function getColumnId() {
            
            return Model::COLUMN_TEK_ID_WIERSZ;
        }
        
        public function getLabel() {
            
            return 'Telefon komórkowy';
        }
        
        public function getTextField(HtmlControls $controls, $name, $value, $errMsg) {
            
            return $controls->_AddNumberbox($name, $name, $value, 9, 12, 'onblur="validations.PhoneValidate(this, event, true);"', '', $errMsg);
        }
    }
    
    class EmailStrategy extends ContactStrategy {
        
        public function __construct(Person $person) {
            
            parent::__construct($person);
        }
        
        public function getPrefix() {
            
            return 'EMAIL';
        }
        
        public function validate($contact) {
            
            return ValidationUtils::validateEmail($contact);
        }
        
        public function santize($contact) {
            
            return strip_tags($contact);
        }
        
        public function set($setItem, $replaceItem = null, $allowDelete = false) {
            
            return $this->person->setEmail($setItem, $replaceItem, $allowDelete);
        }
                              
        public function get() {
            
            return $this->person->getEmail();
        }
        
        public function getColumnName() {
            
            return Model::COLUMN_EMA_NAZWA;
        }
        
        public function getColumnId() {
            
            return Model::COLUMN_EMA_ID_WIERSZ;
        }
        
        public function getLabel() {
            
            return 'E-mail';
        }
        
        public function getTextField(HtmlControls $controls, $name, $value, $errMsg) {
            
            return $controls->_AddTextbox($name, $name, $value, 50, 25, 'onblur="validations.EmailCheck(this, event);"', '', $errMsg);
        }
    }
    
    class ContactsView extends View {
        
        const FORM_ADD_PHONE             = 'addPhone';
        const FORM_ADD_EXTRA_PHONE       = 'addExtraPhone';
        const FORM_UPDATE_PHONE          = 'updatePhone';
        const FORM_UPDATE_EXTRA_PHONE    = 'updateExtraPhone';
        const FORM_ADD_CELL              = 'addCell';
        const FORM_UPDATE_CELL           = 'updateCell';
        const FORM_ADD_EMAIL             = 'addEmail';
        const FORM_UPDATE_EMAIL          = 'updateEmail';
        
        const FORM_FIELD_PHONE_ID        = 'hiddenPhoneId';
        const FORM_FIELD_CELL_ID         = 'hiddenCellId';
        const FORM_FIELD_PHONE           = 'phoneValue_%s';
        const FORM_FIELD_NEW_PHONE       = 'phoneValue_new';
        const FORM_FIELD_CELL            = 'cellValue';
        const FORM_FIELD_OLD_CELL        = 'cellOldValue';
        
        protected $phonesStrategy;
        protected $extraPhonesStrategy;
        protected $cellsStrategy;
        protected $emailStrategy;
        
        public function __construct () {
            
            $idOsoba = Utils::PodajIdOsoba();
            $this->person = new Person($idOsoba);
            
            $this->phonesStrategy = new PhonesStrategy($this->person);
            $this->extraPhonesStrategy = new ExtraPhonesStrategy($this->person);
            $this->cellsStrategy = new CellsStrategy($this->person);
            $this->emailStrategy = new EmailStrategy($this->person);

            $this->actionList = array(
               $this->phonesStrategy->getPrefixedName(self::FORM_UPDATE_PHONE)                  => User::PRIV_EDYCJA_REKORDU,
               $this->phonesStrategy->getPrefixedName(self::FORM_ADD_PHONE)                     => User::PRIV_DODAWANIE_REKORDU,
               $this->extraPhonesStrategy->getPrefixedName(self::FORM_UPDATE_EXTRA_PHONE)       => User::PRIV_EDYCJA_REKORDU,
               $this->extraPhonesStrategy->getPrefixedName(self::FORM_ADD_EXTRA_PHONE)          => User::PRIV_DODAWANIE_REKORDU,
               $this->cellsStrategy->getPrefixedName(self::FORM_UPDATE_CELL)                    => User::PRIV_EDYCJA_REKORDU,
               $this->cellsStrategy->getPrefixedName(self::FORM_ADD_CELL)                       => User::PRIV_DODAWANIE_REKORDU,
               $this->emailStrategy->getPrefixedName(self::FORM_UPDATE_EMAIL)                   => User::PRIV_EDYCJA_REKORDU,
               $this->emailStrategy->getPrefixedName(self::FORM_ADD_EMAIL)                      => User::PRIV_DODAWANIE_REKORDU,
            );
            
            parent::__construct();
            $this->partials = new Partials($this->person);
        }
        
        public function run () {
            
            //entire page html through here
            $html = $this->partials->getNameSurnamePrt().'<br /><hr />';
            
            $html .= $this->viewPhones($this->phonesStrategy);
            $html .= '<hr />';
            $html .= $this->viewCells($this->cellsStrategy);
            $html .= '<hr />';
            $html .= $this->viewPhones($this->extraPhonesStrategy);
            $html .= '<hr />';
            $html .= $this->viewCells($this->emailStrategy);
            
            $html .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            
            return $html;
        }
        
        protected function viewPhones (ContactStrategy $contactStrategy) {
            
            $html = '';
            // error message(s) per each html input control, once occured set under proper key
            $errMsgs = array();
            $fieldNameUpdatePhone = $contactStrategy->getPrefix().self::FORM_UPDATE_PHONE;
            $fieldNameAddPhone = $contactStrategy->getPrefix().self::FORM_ADD_PHONE;
            
            $fieldNamePhoneId = $contactStrategy->getPrefix().self::FORM_FIELD_PHONE_ID;
            $fieldNamePhone = $contactStrategy->getPrefix().self::FORM_FIELD_PHONE;
            $fieldNameNewPhone = $contactStrategy->getPrefix().self::FORM_FIELD_NEW_PHONE;
            
            if (isset($_POST[$fieldNameUpdatePhone])) {
                
                $rowId = (int)$_POST[$fieldNamePhoneId];
                
                if ($rowId > 0) {
                    
                    $phoneField = sprintf($fieldNamePhone, $rowId);
                    $phone = $contactStrategy->santize($_POST[$phoneField]);
                    
                    //validate phone
                    if ($contactStrategy->validate($phone) || $phone == 0) {
                        
                        $result = $contactStrategy->set($phone, $rowId, true);
                        if ($result)
                            return self::postSuccessfull($_SERVER['REQUEST_URI']);
                            
                        $errMsgs[$phoneField] = 'Operacja nie powiod³a siê, spróbuj ponownie.';
                    } else {
                        
                        $errMsgs[$phoneField] = 'Wprowadzony telefon '.View::escapeOutput($_POST[$phoneField]).' jest nieprawid³owy.';
                    }
                } else {
                    
                    $errMsgs[$phoneField] = '¯±danie jest niew³a¶ciwe.';
                }
            }
            
            if (isset($_POST[$fieldNameAddPhone])) {
                
                $newPhone = $contactStrategy->santize($_POST[$fieldNameNewPhone]);
                if ($contactStrategy->validate($newPhone)) {
                    
                    $result = $contactStrategy->set($newPhone);
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                        
                    $errMsgs[$fieldNameNewPhone] = 'Operacja nie powiod³a siê, spróbuj ponownie.';
                } else {
                    
                    $errMsgs[$fieldNameNewPhone] = 'Wprowadzony telefon '.View::escapeOutput($_POST[$fieldNameNewPhone]).' jest nieprawid³owy.';
                }
            }
            
            $phones = $contactStrategy->get();
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= $this->htmlControls->_AddHidden($fieldNamePhoneId, $fieldNamePhoneId, '');
            $html .= '<table>';
            
            if ($phones)
            foreach ($phones as $phone) {
                
                $html .= '<tr><td>'.$contactStrategy->getLabel().': </td>';                
                $phoneField = sprintf($fieldNamePhone, $phone[$contactStrategy->getColumnId()]);
                                                                                                                  
                $html .= '<td>'.$contactStrategy->getTextField($this->htmlControls, $phoneField, $phone[$contactStrategy->getColumnName()], 
                        isset($errMsgs[$phoneField]) ? $errMsgs[$phoneField] : '')
                        .'</td>';
                $html .= '<td>'.$this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, $fieldNameUpdatePhone, $phone[$contactStrategy->getColumnId()], 'Aktualizuj', '', 
                    'onclick="'.$fieldNamePhoneId.'.value = '.$phone[$contactStrategy->getColumnId()].';"')
                    .'<a href="sip:'.$phone[$contactStrategy->getColumnName()].'"><img src="/zdj/phone-51-24.png" class="phoneIco"></a></td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr><td>'.$contactStrategy->getLabel().': </td>';
            
            $html .= '<td>'.$contactStrategy->getTextField($this->htmlControls, $fieldNameNewPhone, '', isset($errMsgs[$fieldNameNewPhone]) ? $errMsgs[$fieldNameNewPhone] : '')
                    .'</td>';
            $html .= '<td>'.$this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, $fieldNameAddPhone, $fieldNameAddPhone, 'Dodaj.', '', '').'</td>';
                
            $html .= '</tr>';
            
            $html .= '</table>';
            $html .= $this->addFormSuf();
            
            return $html;
        }
        
        protected function viewCells (ContactStrategy $contactStrategy) {
            
            $html = '';
            $errMsg = '';
            
            $fieldNameAddCell = $contactStrategy->getPrefixedName(self::FORM_ADD_CELL);
            $fieldNameUpdateCell = $contactStrategy->getPrefixedName(self::FORM_UPDATE_CELL);
            $fieldNameOldCell = $contactStrategy->getPrefixedName(self::FORM_FIELD_OLD_CELL);
            $fieldNameCell = $contactStrategy->getPrefixedName(self::FORM_FIELD_CELL);
            $fieldNameCellId = $contactStrategy->getPrefixedName(self::FORM_FIELD_CELL_ID);
            
            if (isset($_POST[$fieldNameAddCell]) || isset($_POST[$fieldNameUpdateCell])) {
                
                $oldCell = strlen($contactStrategy->santize($_POST[$fieldNameOldCell])) > 0 ? $contactStrategy->santize($_POST[$fieldNameOldCell]) : null;
                $cell = $contactStrategy->santize($_POST[$fieldNameCell]);
                
                if ($contactStrategy->validate($cell) || empty($cell)) {
                    
                    $result = $contactStrategy->set($cell, $oldCell, true);
                    if ($result)
                        return self::postSuccessfull($_SERVER['REQUEST_URI']);
                        
                    $errMsg = 'Operacja nie powiod³a siê, spróbuj ponownie.';
                } else {
                    
                    $errMsg = 'Wprowadzony '.strtolower($contactStrategy->getLabel()).' '.View::escapeOutput($_POST[$fieldNameCell]).' jest nieprawid³owy.';
                }
            }
            
            $cell = $contactStrategy->get();
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= '<table>';
            
            $priviledge = User::PRIV_DODAWANIE_REKORDU;
            $actionName = $fieldNameAddCell;
            $text = 'Dodaj.';
            $value = null;
            $cellId = null;
            $phoneIco = null;
            
            if ($cell) {
                
                $priviledge = User::PRIV_EDYCJA_REKORDU;
                $actionName = $fieldNameUpdateCell;
                $text = 'Aktualizuj';
                $value = $cell[$contactStrategy->getColumnName()];
                $cellId = $cell[$contactStrategy->getColumnId()];
                
                if($contactStrategy instanceof CellsStrategy)
                {
                    $phoneIco = '<a href="sip:'.$cell[$contactStrategy->getColumnName()].'"><img src="/zdj/phone-51-24.png" class="phoneIco"></a>';
                }
            }
            
            $html .= '<tr><td>'.$contactStrategy->getLabel().': </td>';
            
            $html .= '<td>'.$contactStrategy->getTextField($this->htmlControls, $fieldNameCell, $value, $errMsg)
                    .'</td>';
            $html .= '<td>'.$this->htmlControls->_AddSubmit($priviledge, $actionName, $actionName, $text, '', '').$phoneIco.'</td>';

            $html .= '</tr></table>';
            $html .= $this->htmlControls->_AddHidden($fieldNameCellId, $fieldNameCellId, $cellId);
            $html .= $this->htmlControls->_AddHidden($fieldNameOldCell, $fieldNameOldCell, $value);
            $html .= $this->addFormSuf();
            
            return $html;
        }
    }
    
    
    
    //echo '<html>';
    //HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    //echo '<body>';
    //echo $html;
    //echo '</body></html>';