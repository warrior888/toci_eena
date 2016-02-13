<?php

    require_once '../conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    
    require_once 'bll/BLLOpisPrac.php';
    require_once 'bll/BLLKlient.php';
    require_once 'bll/HtmlToPdfManager.php';
    
    class JobsDescriptionView extends View 
    {
        const FORM_SUBMIT_GET_DESCRIPTION                       = 'get_description';
        const FORM_SUBMIT_GET_DESCRIPTIONS                      = 'get_descriptions';
        const FORM_SUBMIT_GET_DESCRIPTIONS_COMPENSATION         = 'get_descriptions_compensation';
        
        const FORM_SUBMIT_EDIT_DESCRIPTION                      = 'edit_description';
        const FORM_SUBMIT_DELETE_DESCRIPTION                    = 'delete_description';
        const FORM_SUBMIT_ADD_DESCRIPTION                       = 'add_description';
        
        const FORM_SUBMIT_INSERT_DESCRIPTION                    = 'insert_description';
        const FORM_SUBMIT_UPDATE_DESCRIPTION                    = 'update_description';
        const FORM_SUBMIT_PREVIEW_DESCRIPTION                   = 'preview_description';
        
        const FORM_INPUT_TYPES                      = 'types';
        const FORM_INPUT_TYPE_ID                    = 'type_id';
        const FORM_INPUT_KLIENT_ID                  = 'klient_id';
        const FORM_INPUT_FILTER_KLIENT_ID           = 'filter_klient_id';
        const FORM_INPUT_OPIS                       = 'opis';
        const FORM_INPUT_OPIS_SUROWY                = 'opis_surowy';
        
        const FORM_SESSION_TEMP_TYPE                = 'temp_department_desc_type';
        
        const DATA_COLUMN_TYPE                      = 'typ';
        
        const MAX_DESC_LENGTH                       = 999999;
        
        protected $typesList = array(
            
            0                                   => array('id' => 0, 'nazwa' => '--------'),
            DALOpisPrac::OP_TYPE_INTERNAL       => array('id' => DALOpisPrac::OP_TYPE_INTERNAL, 'nazwa' => 'wewnetrzny'),
            DALOpisPrac::OP_TYPE_EXTERNAL       => array('id' => DALOpisPrac::OP_TYPE_EXTERNAL, 'nazwa' => 'zewnetrzny'),
            DALOpisPrac::OP_TYPE_SHORTENED      => array('id' => DALOpisPrac::OP_TYPE_SHORTENED, 'nazwa' => 'skrócony'),
        );
        
        private $type;
        
        public function __construct() {
            
            $this->actionList = array(
               
               self::FORM_SUBMIT_ADD_DESCRIPTION       => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SUBMIT_EDIT_DESCRIPTION      => User::PRIV_EDYCJA_REKORDU,
               self::FORM_SUBMIT_DELETE_DESCRIPTION    => User::PRIV_KASOWANIE_REKORDU,
               self::FORM_SUBMIT_INSERT_DESCRIPTION    => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_SUBMIT_UPDATE_DESCRIPTION    => User::PRIV_EDYCJA_REKORDU,
            );
            
            parent::__construct(self::LOG_IN_LEVEL_LOGGED, User::PRIV_MASOWY_SMS);
            // TODO is it a good idea (setting type in construct)?
            $this->type = isset($_POST[self::FORM_INPUT_TYPE_ID]) ? (int)$_POST[self::FORM_INPUT_TYPE_ID] : null;
            $this->bll = new BLLOpisPrac();
        }
        
        public function run () {
            //entire page html through here
            $html = '';
            $html .= $this->viewJobsDescription();
            
            return $html;
        }
        
        public function viewJobsDescription () {
            
            $html = '';
            
            $data = array();
            
            $jobDescriptionsHtml = $this->getJobDescriptions();
            
            $bllKlient = new BLLKlient();
            $clientsResult = $bllKlient->getAll();
            $clients = $clientsResult[Model::RESULT_FIELD_DATA];
            array_unshift ($clients, array('id' => null, 'nazwa' => '-------'));

            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= '<table><tr><td>Opis(y) prac:</td>';
            $html .= '<td>'.$this->htmlControls->_AddSelect(self::FORM_INPUT_KLIENT_ID, 'id_'.self::FORM_INPUT_KLIENT_ID, $clients, null, self::FORM_INPUT_FILTER_KLIENT_ID).'</td>';
            $html .= '<td>'.$this->htmlControls->_AddSelect(self::FORM_INPUT_TYPES, 'id_'.self::FORM_INPUT_TYPES, $this->typesList, $this->type, self::FORM_INPUT_TYPE_ID).'</td>';
            $html .= '<td>'.$this->htmlControls->_AddNoPrivilegeSubmit(self::FORM_SUBMIT_GET_DESCRIPTIONS, 'id_'.self::FORM_SUBMIT_GET_DESCRIPTIONS, 'Poka¿', '', '').'</td>';
            $html .= '<td>'.$this->htmlControls->_AddNoPrivilegeSubmit(self::FORM_SUBMIT_GET_DESCRIPTIONS_COMPENSATION, 'id_'.self::FORM_SUBMIT_GET_DESCRIPTIONS_COMPENSATION, 'Poka¿ brakuj±ce', '', '').'</td>';
            $html .= '</tr></table>';
            $html .= $this->addFormSuf();
            
            $html .= $jobDescriptionsHtml;
            
            return $html;
        }
        
        private function getJobDescriptions () {
            
            $html = '';
            // TODO should be unnecessary
            $this->type = isset($_POST[self::FORM_INPUT_TYPE_ID]) ? (int)$_POST[self::FORM_INPUT_TYPE_ID] : null;
            $clientId = isset($_POST[self::FORM_INPUT_FILTER_KLIENT_ID]) ? (int)$_POST[self::FORM_INPUT_FILTER_KLIENT_ID] : null;
            
            $html .= $this->manipulateJobDescriptions();
            
            $jobDescriptions = array();
            $isCompensation = null;
            
            if (isset($_POST[self::FORM_SUBMIT_GET_DESCRIPTIONS])) {
                
                $jobDescriptions = $this->bll->getAll($this->type, $clientId);
                $isCompensation = false;
            }
            
            if (isset($_POST[self::FORM_SUBMIT_GET_DESCRIPTIONS_COMPENSATION])) {
                
                if ($this->type !== DALOpisPrac::OP_TYPE_INTERNAL && $this->type !== DALOpisPrac::OP_TYPE_EXTERNAL && $this->type !== DALOpisPrac::OP_TYPE_SHORTENED)
                    throw new ViewBadRequestException('Missing type information');
                    
                $jobDescriptions = $this->bll->getAll($this->type, null, true);
                $isCompensation = true;
            }
            
            if (sizeof($jobDescriptions) == 2 && isset($jobDescriptions[Model::RESULT_FIELD_DATA])) {
                
                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                $html .= $this->htmlControls->_AddHidden(self::FORM_INPUT_KLIENT_ID, self::FORM_INPUT_KLIENT_ID, '');
                $html .= $this->htmlControls->_AddHidden('update_'.self::FORM_INPUT_TYPE_ID, self::FORM_INPUT_TYPE_ID, $this->type);
                
                $tableData = $jobDescriptions[Model::RESULT_FIELD_DATA];
                $headers = array('Nazwa', 'Typ');
                $columnsOrder = array(Model::COLUMN_KLN_NAZWA, Model::COLUMN_OPR_TYP);

                foreach ($tableData as $jobDescKey => $jobDescription) {
                    
                    if (false === $isCompensation) {
                        
                        // edit existing
                        $tableData[$jobDescKey][self::FORM_SUBMIT_EDIT_DESCRIPTION] = $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_SUBMIT_EDIT_DESCRIPTION, 'id'.self::FORM_SUBMIT_EDIT_DESCRIPTION, 'Edytuj', '', 'onclick=klient_id.value='.$jobDescription[Model::COLUMN_OPR_ID]);
                        $tableData[$jobDescKey][self::FORM_SUBMIT_DELETE_DESCRIPTION] = $this->htmlControls->_AddDeleteSubmit(User::PRIV_KASOWANIE_REKORDU, self::FORM_SUBMIT_DELETE_DESCRIPTION, 'id'.self::FORM_SUBMIT_DELETE_DESCRIPTION, 'Kasuj', '', '', 'klient_id.value='.$jobDescription[Model::COLUMN_OPR_ID].';');
                        $tableData[$jobDescKey][self::DATA_COLUMN_TYPE] = $this->typesList[$tableData[$jobDescKey][self::DATA_COLUMN_TYPE]]['nazwa'];
                    } else {
                        
                        // add new
                        $tableData[$jobDescKey][self::FORM_SUBMIT_ADD_DESCRIPTION] = $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_SUBMIT_ADD_DESCRIPTION, 'id'.self::FORM_SUBMIT_ADD_DESCRIPTION, 'Dodaj', '', 'onclick=klient_id.value='.$jobDescription[Model::COLUMN_ODK_ID]);
                        $tableData[$jobDescKey][self::DATA_COLUMN_TYPE] = $this->typesList[$this->type]['nazwa'];
                    }
                }
                
                if (false === $isCompensation) {
                    
                    $headers[] = 'Edycja';
                    $headers[] = 'Kasowanie';
                    $columnsOrder[] = self::FORM_SUBMIT_EDIT_DESCRIPTION;
                    $columnsOrder[] = self::FORM_SUBMIT_DELETE_DESCRIPTION;
                    
                    $html .= 'Istniej±ce opisy:';
                } else {
                    
                    $headers[] = 'Dodawanie';
                    $columnsOrder[] = self::FORM_SUBMIT_ADD_DESCRIPTION;
                    
                    $html .= 'Brakuj±ce opisy:';
                }
                
                $htmlTable = new HtmlTable($tableData, $columnsOrder);
                $htmlTable->setHeader($headers);
                
                $html .= $htmlTable->__toString();
                $html .= $this->addFormSuf();
            }
            
            return $html;
        }
        
        private function manipulateJobDescriptions () {
            
            $html = '';
            
            $klientId = isset($_POST[self::FORM_INPUT_KLIENT_ID]) ? (int)$_POST[self::FORM_INPUT_KLIENT_ID] : null;
                        
            // todo if for view 
            //$manager = new HtmlToPdfManager();
            
            if (isset($_POST[self::FORM_SUBMIT_PREVIEW_DESCRIPTION])) {

                list($source, $desc, $type) = $this->getSourceDescType($klientId);
                
                $manager = new HtmlToPdfManager();
                
                $manager->setHtml($desc, true);
                
                $manager->OutputPdf(true);
            }
            
            if (isset($_POST[self::FORM_SUBMIT_INSERT_DESCRIPTION]) || isset($_POST[self::FORM_SUBMIT_UPDATE_DESCRIPTION])) {

                list($source, $desc, $type) = $this->getSourceDescType($klientId);
                
                $result = $this->bll->set($klientId, $type, $source, $desc);
                SessionManager::delete(self::FORM_SESSION_TEMP_TYPE);
            }
            
            if (isset($_POST[self::FORM_SUBMIT_DELETE_DESCRIPTION]) || isset($_POST[self::FORM_SUBMIT_EDIT_DESCRIPTION]) || isset($_POST[self::FORM_SUBMIT_ADD_DESCRIPTION])) {            

                if ($klientId < 1 || $this->type < 1) 
                    throw new ViewBadRequestException('Required client id and/or type missing');
                
                SessionManager::set(self::FORM_SESSION_TEMP_TYPE, $this->type);
                
                if (isset($_POST[self::FORM_SUBMIT_DELETE_DESCRIPTION])) {
                    
                    $result = $this->bll->delete($klientId, $this->type);
                    return 'Skasowano <br />';
                }
                
                $descRaw = $desc = '';
                if (isset($_POST[self::FORM_SUBMIT_EDIT_DESCRIPTION])) {
                    
                    $result = $this->bll->get($klientId, $this->type);
                    
                    if ($result !== null) {
                        
                        $data = $result[Model::RESULT_FIELD_DATA];
                        
                        if ($data[Model::COLUMN_OPR_ZRODLO] == DALOpisPrac::SOURCE_EDITOR) {
                            
                            $desc = $data[Model::COLUMN_OPR_OPIS];
                            $descRaw = null;
                        } else {
                            
                            $desc = null;
                            $descRaw = $data[Model::COLUMN_OPR_OPIS];
                        }
                    }
                    
                    $priv = User::PRIV_EDYCJA_REKORDU;
                    $label = 'Aktualizuj';
                    $submitName = self::FORM_SUBMIT_UPDATE_DESCRIPTION;
                }
                
                if (isset($_POST[self::FORM_SUBMIT_ADD_DESCRIPTION])) {
                    
                    $priv = User::PRIV_DODAWANIE_REKORDU;
                    $label = 'Wprowadz';
                    $submitName = self::FORM_SUBMIT_INSERT_DESCRIPTION;
                }

                $type = SessionManager::get(self::FORM_SESSION_TEMP_TYPE);
                
                $html .= 'Wprowadzanie opisu prac, typ:  '.$this->typesList[$type]['nazwa'].' <br />';
                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                
                $html .= $this->htmlControls->_AddHidden(self::FORM_INPUT_KLIENT_ID, self::FORM_INPUT_KLIENT_ID, $klientId);
                // todo err msg
                
                $html .= $this->htmlControls->_AddTextarea(self::FORM_INPUT_OPIS, 'id'.self::FORM_INPUT_OPIS, $desc, self::MAX_DESC_LENGTH, 20, 150, '', '', '', '');

                $html .= $this->htmlControls->_AddTextarea(self::FORM_INPUT_OPIS_SUROWY, 'id'.self::FORM_INPUT_OPIS_SUROWY, $descRaw, self::MAX_DESC_LENGTH, 20, 150, '', '', '', '');
                
                $html .= $this->htmlControls->_AddSubmit($priv, $submitName, 'id'.$submitName, $label, '', '');
                
                $html .= $this->htmlControls->_AddNoPrivilegeSubmit(self::FORM_SUBMIT_PREVIEW_DESCRIPTION, 'id'.self::FORM_SUBMIT_PREVIEW_DESCRIPTION, 'Podgl±d', '', '');
                
                $html .= $this->addFormSuf();
                $html .= '<script type="text/javascript">WYSIWYG.attach("id'.self::FORM_INPUT_OPIS.'", full);
                document.getElementById("id'.self::FORM_INPUT_OPIS_SUROWY.'").value = htmlentities_decode(document.getElementById("id'.self::FORM_INPUT_OPIS_SUROWY.'").value);
                </script>';

                return $html;
            }
            
            return $html;
        }
        
        private function getSourceDescType ($klientId)
        {
            if ($_POST[self::FORM_INPUT_OPIS] == '<br>')
            {
                $_POST[self::FORM_INPUT_OPIS] = '';
            }
            
            $source = (strlen(trim($_POST[self::FORM_INPUT_OPIS])) > 0) ? DALOpisPrac::SOURCE_EDITOR : DALOpisPrac::SOURCE_RAW;
            $desc = ($source == DALOpisPrac::SOURCE_EDITOR) ? $_POST[self::FORM_INPUT_OPIS] : $_POST[self::FORM_INPUT_OPIS_SUROWY];
            
            do {
                $desc = str_replace('\&quot;', '', $desc);
                $desc = str_replace('\\\\', '\\', $desc);
            } while (strpos('\&quot;', $desc) !== false);
            
            $type = SessionManager::get(self::FORM_SESSION_TEMP_TYPE);
            
            if ($klientId < 1 || $type < 1) 
                throw new ViewBadRequestException('Required client id and/or type missing');
                
            return array($source, $desc, $type);
        }
    }
    
    CommonUtils::SessionStart();

    try {
        $output = new JobsDescriptionView();

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
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1, array('cms_edytor/scripts/wysiwyg.js', 'cms_edytor/scripts/wysiwyg-settings.js'));
    echo '<body>';
    echo $html;
    echo '</body></html>';