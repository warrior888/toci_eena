<?php 

    require_once '../conf.php';
    require_once 'adl/Person.php';

    class Jarograf extends View
    {
        
        const FORM_ADD_JAROGRAF    = 'add_jarograf';
        const FORM_REMOVE_JAROGRAF = 'remove_jarograf';
        
        const FROM_JAROGRAF_CUSTOMER    = 'customer';
        const FROM_JAROGRAF_CUSTOMER_ID = 'customer_id';
        const FROM_JAROGRAF_YEAR        = 'year';
        const FORM_JAROGRAF_FILE        = 'scan_file';
        const FORM_JAROGRAF_PRINT       = 'print_btn';
        const FORM_JAROGRAF_DELETE      = 'delete_btn';
        const FORM_JAROGRAF_DELETE_ID   = 'delete_id';
        const FORM_JAROGRAF_RECEIVED    = 'received';
        const FORM_JAROGRAF_SEND_EMAIL  = 'send_email';
        
        public static function instantiate() {
            return new Jarograf();
        }
        
        public function __construct () {
            $this->actionList = array(
                            self::FORM_ADD_JAROGRAF      => User::PRIV_DODAWANIE_REKORDU,
                            self::FORM_REMOVE_JAROGRAF   => User::PRIV_KASOWANIE_REKORDU,
            );
        
            parent::__construct();
        
            $this->personId = Utils::PodajIdOsoba();
            $this->person = new Person($this->personId);
            $this->partials = new Partials($this->person);
        
            $this->fileManager = new FileManager();
        }
        
        public function run() {
        
            if (isset($_POST[self::FORM_ADD_JAROGRAF])) {
                $result = $this->fileManager->setTaxDoc($this->personId, 
                        $_FILES[self::FORM_JAROGRAF_FILE]['tmp_name'], 
                        $_FILES[self::FORM_JAROGRAF_FILE]['name'], 
                        date("Y") - 1, 
                        $_POST[self::FROM_JAROGRAF_CUSTOMER_ID]);
                
                if(!$result) {
                    echo "Nie uda³o siê dodaæ pliku!!!";
                }
            }
             
            if (isset($_POST[self::FORM_JAROGRAF_DELETE]))
            {
                $this->person->getLogicJarograf()->deleteJarograf($this->user, $_POST[self::FORM_JAROGRAF_DELETE_ID]);
            }
            
            if (isset($_POST[self::FORM_JAROGRAF_SEND_EMAIL]))
            {
                $this->sendEmail();
            }
            
            if (isset($_POST[self::FORM_JAROGRAF_RECEIVED])) {
                $this->person->getLogicJarograf()->setReceived($this->personId, date("Y") -1 , $this->user);
            }
        
            $html = $this->viewForm();
            return $html;
        }
        
        public function viewForm() {
        	$result = '<div style="width: 500px; margin: 0 auto;">';
            $result .= $this->renderAddForm();
            
            
            $jarografList = $this->person->getLogicJarograf()->getJarografs($this->personId);
            $result .= $this->renderAddedJarografs($jarografList);
            
            $result .= '</div>';
            $result .= "<div align = 'CENTER'>";
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);

            $received = $this->person->getLogicJarograf()->checkReceived($this->personId, date("Y") - 1);
            $checked = !is_null($received); 
            $disabled = $checked ? ' disabled=""' : "";
            $result .= 'Jarografy odebrane: ' . $this->htmlControls->_AddCheckbox(self::FORM_JAROGRAF_RECEIVED, self::FORM_JAROGRAF_RECEIVED, $checked, JsEvents::ONCLICK .'= this.form.submit();' . $disabled);
            if ($checked) {
               $result .= $received[Model::RESULT_FIELD_DATA][Model::COLUMN_UPR_IMIE_NAZWISKO] ." ". $received[Model::RESULT_FIELD_DATA][Model::COLUMN_ODB_DATA];
            }
            $result .= '</div>';
            $result .= "<div align = 'CENTER'>";
            $email = $this->person->getEmail();
            if($email != null) {
                $result .= $this->htmlControls->_AddSubmit(User::PRIV_EMAIL, self::FORM_JAROGRAF_SEND_EMAIL, self::FORM_JAROGRAF_SEND_EMAIL, 'Wy¶lij email', '', '');
            }
            $result .= '</div>';
            $result .= "<div align = 'CENTER'>";
            $result .= $this->addFormSuf();
            $result .= $this->htmlControls->_AddSubmit(User::PRIV_NONE_REQUIRED, 'Zamknij', 'Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            
            $result .= '</div>';
            
            return $result;
        }
        
        public function renderAddForm() {
            $result = "";
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI'], array('enctype' => "multipart/form-data"));
            $result .= $this->partials->getNameSurnamePrt().'<br /><hr />';
            $result .= '<table align="center">';
            $result .= '<tr><td>Klient</td>';
            $result .= '<td>';
            $result .= $this->htmlControls->_AddSelect(self::FROM_JAROGRAF_CUSTOMER, self::FROM_JAROGRAF_CUSTOMER, $this->person->getLogicJarograf()->getClients($this->personId), null, self::FROM_JAROGRAF_CUSTOMER_ID);
            $result .= '</td></tr>';
            $result .= '<tr><td>Rok:</td>';
            $result .= '<td>';
            $result .= $this->htmlControls->_AddTextbox(self::FROM_JAROGRAF_YEAR, self::FROM_JAROGRAF_YEAR, date("Y") - 1, 4, 4, 'readonly');
            $result .= '</td></tr>';
            $result .= '<tr><td>Podaj plik:</td>';
            $result .= '<td>';
            $result .= $this->htmlControls->_AddTextbox(self::FORM_JAROGRAF_FILE, self::FORM_JAROGRAF_FILE, '', 100, 40, '', '', '', '', 'file');
            $result .= '</td></tr>';
            $result .= '<tr><td></td>';
            $result .= '<td>' . $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_ADD_JAROGRAF, self::FORM_ADD_JAROGRAF, 'Dodaj', '', '') .'<td/></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        protected function renderAddedJarografs($data) {
        
            $result = "<h2>Jarografy:</h2>";
        
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->htmlControls->_AddHidden(self::FORM_JAROGRAF_DELETE_ID, self::FORM_JAROGRAF_DELETE_ID);
            
            $tasksFoundTable = new HtmlTable();
            $tasksFoundTable->addTableCss('wide');
            $tasksFoundTable->setHeader(array('Lp.', 'Rok', 'Klient', 'Akcje'));
            if ($data) {
                $lp =1;
                foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                    $actions = $this->htmlControls->_AddPopUpButton(self::FORM_JAROGRAF_PRINT, self::FORM_JAROGRAF_PRINT, 'Drukuj', 'str.php?zasob_graficzny='.$row[Model::COLUMN_JRG_PLIK], 680, 920);
                    
                    if ($this->user->isAllowed(User::PRIV_KASOWANIE_REKORDU)) {                        
                        $actions .= $this->htmlControls->_AddDeleteSubmit(User::PRIV_KASOWANIE_REKORDU, self::FORM_JAROGRAF_DELETE, self::FORM_JAROGRAF_DELETE, 'Skasuj', null, null, self::FORM_JAROGRAF_DELETE_ID.'.value = \''.$row[Model::COLUMN_JRG_PLIK].'\'');
                    }
                    
                    $tasksFoundTable->addRow(array($lp,
                                    $row[Model::COLUMN_JRG_ROK],
                                    $row[Model::COLUMN_KLN_NAZWA],
                                    "<a href=\"file.php?".ID_OSOBA."=". $this->personId ."&pit=".$row[Model::COLUMN_JRG_PLIK]."\" target = \"_blank\">Poka¿ </a> " .
                                    $actions
                                    
                    ));
                    $lp++;
                }
        
                $result .= $tasksFoundTable->__toString();
            }
            
            $result .= $this->addFormSuf();
            return $result;
        }
        
        protected function sendEmail() {
            
            $year = date("Y") - 1;
            $mail = new MailSend();
            $text = "Jarograf(y) $year\nPozdrowienia\nE&A";
            
            $files = $this->person->getLogicJarograf()->getFile($this->personId, $year);
            
            foreach ($files[Model::RESULT_FIELD_DATA] as $row)
            {
                $mail->DodajZalacznik(FileManager::getTaxReadTarget($row[Model::COLUMN_JRG_PLIK]));
            }
            $email = $this->person->getEmail(); 
            $mail->DodajOdbiorca($email[Model::COLUMN_EMA_NAZWA]);
            $mail->WyslijMail('Rozliczenie roczne.', $text, 'info@eena.pl', 'info@eena.pl');
        }
        
    }
    
    $output = Jarograf::instantiate();
    $output->execute();
    