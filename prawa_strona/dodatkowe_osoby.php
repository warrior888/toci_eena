<?php
    
    require_once '../conf.php';
    require_once 'adl/Person.php';
    
    class AdditionalPersonsView extends View {
        
        const FORM_ADD_PERSON_BUTTON        = "add_person";
        const FORM_REMOVE_PERSON_BUTTON     = "remove_person";
        const FORM_PERSON_ID_NUMBER         = 'person_id';
        const FROM_PERSON_ID_REMOVE_HIDDEN  = 'remove_id';
        
        public static function instantiate () {
            return new AdditionalPersonsView();
        }
        
        public function __construct () {
            $this->actionList = array(
                            self::FORM_ADD_PERSON_BUTTON        => User::PRIV_DODAWANIE_REKORDU,
                            self::FORM_REMOVE_PERSON_BUTTON     => User::PRIV_EDYCJA_REKORDU,
            );
        
            parent::__construct();
            $idOsoba = Utils::PodajIdOsoba();
            $this->person = new Person($idOsoba);
            $this->partials = new Partials($this->person);
            $this->personData = $this->person->getPersonData();
            $this->personData = $this->personData[Model::RESULT_FIELD_DATA];
        
        }
        
        public function run() {
        
            $personId = (int)$_GET['id_osoba'];
        
            if (isset($_POST[self::FORM_ADD_PERSON_BUTTON])) {
                $params[Model::COLUMN_DODOS_ID] = $personId;
                $params[Model::COLUMN_DODOS_ID_OSOBY_DOD] = $_POST[self::FORM_PERSON_ID_NUMBER];
                
                $this->person->getLogicDodatkoweOsoby()->addPerson($personId, $params);
            }
            if (isset($_POST[self::FORM_REMOVE_PERSON_BUTTON])) {
                $params[Model::COLUMN_DODOS_ID] = $personId;
                $params[Model::COLUMN_DODOS_ID_OSOBY_DOD] = $_POST[self::FROM_PERSON_ID_REMOVE_HIDDEN];
                $this->person->getLogicDodatkoweOsoby()->removePerson($personId, $params);
            }
        
            $html = $this->viewPersons();
            return $html;
        }
        
        protected function viewPersons() {
            $result = '<div style="width: 400px; margin: 0 auto;">';
            $result .= $this->renderAddForm();

            $personList = $this->person->getLogicDodatkoweOsoby()->getPersonList($this->person->getPersonId());
            $result .= $this->renderAddedPersons($personList);

            $result .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            $result .= '</div>';
            return $result;
        }
        
        protected function renderAddForm() {
            $result = "";
        
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->partials->getNameSurnamePrt().'<br /><hr />';
            $result .= '<table align="center">';
            $result .= '<tr><td>Wpisz ID osoby dodatkowej: '. $this->htmlControls->_AddNumberbox(self::FORM_PERSON_ID_NUMBER, self::FORM_PERSON_ID_NUMBER, '', 8, 7, '') .'</td>';
            $result .= '<td>' . $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_ADD_PERSON_BUTTON, self::FORM_ADD_PERSON_BUTTON, 'Dodaj', '', '') .'<td/></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
        
            return $result;
        }
        
        protected function renderAddedPersons($data) {
        
            $result = "<h2>Osoby dodatkowe:</h2>";
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            $result .= $this->htmlControls->_AddHidden(self::FROM_PERSON_ID_REMOVE_HIDDEN, self::FROM_PERSON_ID_REMOVE_HIDDEN);
            
        
            $tasksFoundTable = new HtmlTable();
            $tasksFoundTable->addTableCss('wide');
            $tasksFoundTable->setHeader(array('Lp.', 'Imiê i nazwisko', 'Data urodzenia', ''));
            if ($data) {
                $lp =1;
                foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                    $tasksFoundTable->addRow(array($lp,
                                    $row[Model::COLUMN_DICT_NAZWA] . " " . $row[Model::COLUMN_DIN_NAZWISKO],
                                    $row[Model::COLUMN_DIN_DATA_URODZENIA],
                                    $this->htmlControls->_AddDeleteSubmit(User::PRIV_EDYCJA_REKORDU,
                                            self::FORM_REMOVE_PERSON_BUTTON,
                                            $row[Model::COLUMN_DODOS_ID_OSOBY_DOD], 
                                            'Usuñ', 
                                            '', 
                                            '',
                                            self::FROM_PERSON_ID_REMOVE_HIDDEN.'.value=this.id;'
                                    ),
                    ));
                    $lp++;
                }
        
                $result .= $tasksFoundTable->__toString();
            }
            $result .= $this->addFormSuf();
        
            return $result;
        }
    }
    

    $output = AdditionalPersonsView::instantiate();
    $output->execute();
    