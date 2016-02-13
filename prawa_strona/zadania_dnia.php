<?php
    
    require_once '../conf.php';
    require_once 'adl/Person.php';
    
    class TasksView extends View {
        
        const FORM_NEW_TASK_BUTTON              = 'dodaj_zadanie';
        const FORM_RESOLVE_TASK_CHECKBOX        = 'rozwiaz_zadanie';
        const FORM_RESOLVED_TASK_CHECKBOX       = 'rozwiazane_zadanie';
        const FORM_TASK_DESCRIPTIPON_TEXTAREA   = 'zadania';
        const FORM_TASK_DATE                    = 'data_zadania';
        const FROM_TASK_HIDDEN                  = 'zadanie';
        
        
        protected $personData;
        
        public static function instantiate () {
            return new TasksView();
        }
        
        public function __construct () {
            $this->actionList = array(
                    self::FORM_NEW_TASK_BUTTON        => User::PRIV_DODAWANIE_REKORDU,
                    self::FORM_RESOLVE_TASK_CHECKBOX  => User::PRIV_EDYCJA_REKORDU,
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
            
            if (isset($_POST[self::FORM_NEW_TASK_BUTTON])) {
                $params[Model::COLUMN_ZDN_ID] = $personId;
                $params[Model::COLUMN_ZDN_ID_KONSULTANT] = $_SESSION['id_uzytkownik'];
                $params[Model::COLUMN_ZDN_INSERT_DATA] = date("Y-m-d H:i:s");
                $params[Model::COLUMN_ZDN_DATA] = $_POST[self::FORM_TASK_DATE];
                $params[Model::COLUMN_ZDN_PROBLEM] = $_POST[self::FORM_TASK_DESCRIPTIPON_TEXTAREA];
                $params[Model::COLUMN_ZDN_ACTIVE] = true;
                
                $this->person->getLogicZadaniaDnia()->setTask($personId, $params);
            }
            if (isset($_POST[self::FORM_RESOLVE_TASK_CHECKBOX])) {
                $params[Model::COLUMN_ZDN_ACTIVE] = false;
                $params[Model::COLUMN_ZDN_ROW_ID] = $_POST[self::FROM_TASK_HIDDEN];
                $this->person->getLogicZadaniaDnia()->setTask($personId, $params);
            }
            
            $html = $this->viewTasks();
            return $html;
        }
        
        protected function viewTasks() {
            $result = '<div style="width: 800px; margin: 0 auto;">';
            $result .= $this->renderAddForm();

            $activeTaskListData = $this->person->getLogicZadaniaDnia()->getActiveTaskList($this->person->getPersonId(), true);
            $result .= $this->renderActiveTasks($activeTaskListData);
            
            $resolvedTaskListData = $this->person->getLogicZadaniaDnia()->getResolvedTaskList($this->person->getPersonId(), false);
            $result .= $this->renderResolvedTasks($resolvedTaskListData);
                        
            $result .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            $result .= '</div>';
            return $result;
        }
        
        protected function renderAddForm() {
            $result = "";
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->partials->getNameSurnamePrt().'<br /><hr />';
            $result .= '<table align="center">';
            $result .= '<tr><td colspan="2">Zadanie:</td></tr>';
            $result .= '<tr><td colspan="2">' . $this->htmlControls->_AddTextarea(self::FORM_TASK_DESCRIPTIPON_TEXTAREA, self::FORM_TASK_DESCRIPTIPON_TEXTAREA, '', 140, 5, 50, '') . '</td></tr>';
            $result .= '<tr><td>Data:</td><td>' . $this->htmlControls->_AddDatebox(self::FORM_TASK_DATE, self::FORM_TASK_DATE, '', 10, 10) . '</td></tr>';
            $result .= '<tr><td>Konsultant: '.$_SESSION['uzytkownik'].'<td/></tr>';
            $result .= '<tr><td>Data wpisu: '. date("Y-m-d H:i:s") .'<td/></tr>';
            $result .= '<tr><td></td><td>' . $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_NEW_TASK_BUTTON, self::FORM_NEW_TASK_BUTTON, 'Dodaj', '', '') .'<td/></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        protected function renderActiveTasks($data) {
            
            $result = "<h2>Nowe zadania</h2>";
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->htmlControls->_AddHidden(self::FROM_TASK_HIDDEN, self::FROM_TASK_HIDDEN);
            
            $tasksFoundTable = new HtmlTable();
            $tasksFoundTable->addTableCss('wide');
            $tasksFoundTable->setHeader(array('Lp.', 'Data zadania', 'Zadanie', 'Konsultant', 'Data wpisu', 'Rozwi±zane'));
            if ($data) {
                $lp = 1;
                foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                    $tasksFoundTable->addRow(array($lp,
                                                    $row[Model::COLUMN_ZDN_DATA],
                                                    $row[Model::COLUMN_ZDN_PROBLEM],
                                                    $row[Model::COLUMN_UPR_IMIE_NAZWISKO],
                                                    $row[Model::COLUMN_ZDN_INSERT_DATA],
                                                    $this->htmlControls->_AddCheckbox(self::FORM_RESOLVE_TASK_CHECKBOX, 
                                                            self::FORM_RESOLVE_TASK_CHECKBOX, 
                                                            false, 
                                                            'onclick="zadanie.value = this.value; this.form.submit();"', 
                                                            '', 
                                                            $row[Model::COLUMN_ZDN_ROW_ID]
                                                    ),
                    ));
                    $lp++;
                }
            
                $result .= $tasksFoundTable->__toString();
            }
            
            $result .= $this->addFormSuf();
            
            return $result;
        }
        
        protected function renderResolvedTasks($data) {
        
            $result = "<h2>Rozwi±zane zadania</h2>";

            $result .= $this->htmlControls->_AddHidden(self::FROM_TASK_HIDDEN, self::FROM_TASK_HIDDEN);
        
            $tasksFoundTable = new HtmlTable();
            $tasksFoundTable->addTableCss('wide');
            $tasksFoundTable->setHeader(array('Lp.', 'Data zadania', 'Zadanie', 'Konsultant', 'Data wpisu', 'Rozwi±zane'));
            if ($data) {
                $lp =1;
                foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                    $tasksFoundTable->addRow(array($lp,
                                    $row[Model::COLUMN_ZDN_DATA],
                                    $row[Model::COLUMN_ZDN_PROBLEM],
                                    $row[Model::COLUMN_UPR_IMIE_NAZWISKO],
                                    $row[Model::COLUMN_ZDN_INSERT_DATA],
                                    $this->htmlControls->_AddCheckbox(self::FORM_RESOLVED_TASK_CHECKBOX,
                                            self::FORM_RESOLVED_TASK_CHECKBOX,
                                            false,
                                            'disabled="disabled" checked',
                                            ''
                                    ),
                    ));
                    $lp++;
                }
        
                $result .= $tasksFoundTable->__toString();
            }
        

            return $result;
        }
    }
    
    $output = TasksView::instantiate();
    $output->execute();
    
    