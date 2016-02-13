<?php
    
    require_once '../conf.php';
    require_once 'adl/Person.php';
    require_once '../bll/FileManager.php';
    require_once '../bll/BLLScans.php';
    require_once '../widoki/ankieta.php';
    include 'f_image_operations.php';
    
    class AddScansView extends View {
        
        const FORM_ADD_SCAN                 = 'add_document';
        const FORM_GENERATE_QUESTIONNAIRE   = 'generate_questionnaire';
        const FORM_SCAN_FILE                = 'scan_file';
        const FORM_SCAN_TYPE                = 'scan_type';
        const FORM_SCAN_TYPE_ID             = 'scan_type_id';
        
        protected $personId, $fileManager;
        
        public static function instantiate() {
            return new AddScansView();
        }
        
        public function __construct () {
            $this->actionList = array(
                            self::FORM_ADD_SCAN                 => User::PRIV_DODAWANIE_REKORDU,
                            self::FORM_GENERATE_QUESTIONNAIRE   => User::PRIV_DRUK_ANKIETY,
            );
        
            parent::__construct();
            
            $this->personId = Utils::PodajIdOsoba();
            $this->person = new Person($this->personId);
            $this->partials = new Partials($this->person);
            
            $this->fileManager = new FileManager();
        }
        
        public function run() {
            
            if (isset($_POST[self::FORM_ADD_SCAN])) {
                list($result, $err) = $this->fileManager->setScanDoc($this->personId, 
                        $_FILES[self::FORM_SCAN_FILE]['tmp_name'], 
                        $_FILES[self::FORM_SCAN_FILE]['name'], 
                        $_POST[self::FORM_SCAN_TYPE], 
                        $_POST[self::FORM_SCAN_TYPE_ID]);
                if (!$result) {
                    echo $err;
                }
            }
                       
            if (isset($_POST[self::FORM_GENERATE_QUESTIONNAIRE]))
            {
                create_ankieta($this->personId);
            }
        
            $html = $this->viewForm();
            return $html;
        }
        
        protected function viewForm() {
            $result = '<div style="width: 400px; margin: 0 auto;">';
            $result .= $this->renderAddForm();
            $result .= $this->renderQuestionnaireForm();
            
            $scanList = $this->person->getLogicScans()->getPersonScans($this->person->getPersonId());
            $result .= $this->renderAddedScans($scanList);
            
            $result .= '</div>';
            return $result;
        }
        
        protected function renderAddForm() {
            $result = "";
        
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI'], array('enctype' => "multipart/form-data"));
            $result .= $this->partials->getNameSurnamePrt().'<br /><hr />';
            $result .= '<table align="center">';
            $result .= '<tr><td>Plik:</td><td>Rodzaj dokumentu:</td><td></td></tr>';
            $result .= '<tr><td>';
            $result .= $this->htmlControls->_AddTextbox(self::FORM_SCAN_FILE, self::FORM_SCAN_FILE, '', 100, 40, '', '', '', '', 'file');
            $result .= $this->htmlControls->_AddHidden('id_MAX_FILE_SIZE', 'MAX_FILE_SIZE', '10000');
            $result .= '</td><td>';
            $result .= $this->htmlControls->_AddSelect(self::FORM_SCAN_TYPE, self::FORM_SCAN_TYPE, $this->person->getLogicScans()->getStanTypes(), null, self::FORM_SCAN_TYPE_ID);
            $result .= '</td>';
            $result .= '<td>' . $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_ADD_SCAN, self::FORM_ADD_SCAN, 'Dodaj', '', '') .'<td/></tr>';
            $result .= '</td></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
        
            return $result;
        }
        
        protected function renderQuestionnaireForm() {
            $result = "";
        	$result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);            
            $result .= 'Plik ankiety: ';
            if ($this->fileManager->scanDocExists($this->personId, FileManager::getAnkietaName($this->personId))) {
                $result .= "<a href='file.php?".ID_OSOBA.'='.$this->personId."' target='_blank'>Pobierz plik ankiety</a>";
            }
            $result .= '</br>';
            $result .= $this->htmlControls->_AddSubmit(User::PRIV_DRUK_ANKIETY, self::FORM_GENERATE_QUESTIONNAIRE, self::FORM_GENERATE_QUESTIONNAIRE, 'Generuj ankietê', '', '');
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        protected function renderAddedScans($data) {
        
            $result = "<h2>Dostêpne dokumenty:</h2>";
            
            $tasksFoundTable = new HtmlTable();
            $tasksFoundTable->addTableCss('wide');
            $tasksFoundTable->setHeader(array('Lp.', 'Plik', 'Poka¿'));
            if ($data) {
                $lp =1;
                foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                    $tasksFoundTable->addRow(array($lp,
                                    $row[Model::COLUMN_DSK_NAZWA_PLIK],
                                    '<a href="file.php?'.ID_OSOBA.'='.$this->person->getPersonId().'&name='. $row[Model::COLUMN_DSK_NAZWA_PLIK].'" target="_blank">Poka¿</a>'
                    ));
                    $lp++;
                }
        
                $result .= $tasksFoundTable->__toString();
            }
            
            return $result;
        }
    }
   
    $output = AddScansView::instantiate();
    $output->execute();
    
