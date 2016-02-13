<?php
    require_once '../conf.php';
    require_once 'dal/DALDokumenty.php';
    require_once 'adl/Person.php';
    require_once 'bll/additionals.php';
    require_once 'bll/validationUtils.php';
    
    class DocumentsView extends View 
    {
        const FORM_ADD_DOCUMENTS         = 'update_dokumenty';
        
        const FORM_RM_SOFFI_INFO         = 'rm_soffi_info';
        const FORM_ADD_SOFFI_INFO        = 'add_soffi_info';
                
        protected $dal;
        protected $addInfo;
        protected $bllDicts;
        
        public function __construct ()
        {
            $this->actionList = array(
                self::FORM_RM_SOFFI_INFO      => User::PRIV_EDYCJA_REKORDU,
                self::FORM_ADD_SOFFI_INFO     => User::PRIV_EDYCJA_REKORDU,
                self::FORM_ADD_DOCUMENTS      => User::PRIV_EDYCJA_REKORDU,
                
            );
            
            parent::__construct();
            $this->utilsUI = new UtilsUI('', 'id_os');
            $this->id_osoba = Utils::PodajIdOsoba();
            $this->person = new Person($this->id_osoba);
            $this->partials = new Partials($this->person);
            $this->dal = new DALDokumenty();
            $this->addInfo = new AdditionalBool($this->id_osoba);
            $this->bllDicts = new BLLDaneSlownikowe();
            //$this->htmlControls = new HtmlControls();
        }
        
        public function run () {
        
            $html = '';
            
            $html .= $this->viewDocuments();
            
            return $html;
        }
        
        protected function viewDocuments () {

            $html = '';
            
            if (isset($_POST[self::FORM_RM_SOFFI_INFO]) || isset($_POST[self::FORM_ADD_SOFFI_INFO])) 
            {
                $html .= $this->setSoffiInfo();
            }
            
            if (isset($_POST[self::FORM_ADD_DOCUMENTS]))
            {
                $html .= $this->addDocuments();
            }
            
            $dataList = $this->dal->get($this->id_osoba);
            
            $data = $dataList !== null ? $dataList[Model::RESULT_FIELD_DATA][0] : null; 
            
            $passNr = $data !== null ? $data[Model::COLUMN_DOK_PASS_NR] : null;
            $expiryDate = $data !== null ? $data[Model::COLUMN_DOK_DATA_WAZNOSCI] : null;
            $nipNl = $data !== null ? $data[Model::COLUMN_DOK_NIP] : null;
            $bankId = $data !== null ? $data[Model::COLUMN_DOK_ID_BANK] : null;
            $nrKonta = $data !== null ? $data[Model::COLUMN_DOK_NR_KONTA] : null;

            $hasSoffi = $this->addInfo->getSoffiInformation();
            if (!$hasSoffi && strlen($nipNl) > 8)
            {
                $this->addInfo->setSoffiInformation(true);
                $hasSoffi = true;
            }
            
            $txtMa = 'Nie posiada';
            if ($hasSoffi)
                $txtMa = 'Posiada';
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= $this->htmlControls->_AddHidden(ID_OSOBA, ID_OSOBA, $this->id_osoba); 
            $html .= $this->partials->getNameSurnamePrt();
            $html .= '<table><tr><td>Nr paszportu: </td><td>';
            $html .= $this->htmlControls->_AddTextbox('pass_nr', 'id_pass_nr', $passNr, '10', '20', ''); 
            $html .= '</td></tr><tr><td>Data waznosci: </td><td>';
            $html .= $this->htmlControls->_AddDatebox("data_waznosci", "data_waznosci", $expiryDate, 10, 10);
            $html .= '</td></tr><tr>';
            $html .= '<td>Sofi ('.$txtMa.'):</td><td>'.$this->htmlControls->_AddNumberbox("nip", "nip", $nipNl, 9, 10, JsEvents::ONBLUR.' = "soffi(this);"');
            
            if (strlen($nipNl) < 1)
            if ($hasSoffi)
            {
                //enable ze nie ma
                $html .= '&nbsp;'.$this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_RM_SOFFI_INFO, 'id_rm_soffi_info', 'Nie posiada', '', '');
            }
            else
            {
                //enable ze ma
                $html .= '&nbsp;'.$this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_ADD_SOFFI_INFO, 'id_add_soffi_info', 'Posiada', '', '');
            }
            
            $html .= '</td></tr><tr><td>Bank: </td><td>';
            $banksList = $this->bllDicts->getBanksList();

            $html .= $this->htmlControls->_AddSelect('id_banku', 'id_banku', $banksList[Model::RESULT_FIELD_DATA], $bankId, 'bank_id');
            
            $html .= '</td></tr><tr><td>Nr konta: </td><td>';
            $html .= $this->htmlControls->_AddTextbox('nr_konta','id_nr_konta', $nrKonta, '29', '31', '');
            $html .= '</td></tr><tr><td></td><td>';
            $html .= $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_ADD_DOCUMENTS, 'id_update_dokumenty', 'Aktualizuj', '', '');
            $html .= '</td></tr><tr><td></td><td>';
            $html .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
	        $html .= '</td></tr></table>';
	        $html .= $this->addFormSuf();
	        
	        return $html;
        }
        
        protected function setSoffiInfo () {
            
            $html = 'Operacja nie powiod³a siê. <br />';
            if (isset($_POST[self::FORM_RM_SOFFI_INFO]))
            {
                $result = $this->addInfo->setSoffiInformation(false);
                if ($result)
                {
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
            }
            if (isset($_POST[self::FORM_ADD_SOFFI_INFO]))
            {
                $result = $this->addInfo->setSoffiInformation(true);
                if ($result)
                {
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
                }
            }
            
            return $html;
        }
        
        protected function addDocuments() {
                      
            if (!ValidationUtils::validateDate($_POST['data_waznosci']))
            {
	            $_POST['data_waznosci'] = null;
            }
            else
            {
	            $_POST['data_waznosci'] = "'".$_POST['data_waznosci']."'";
            }
            
            $result = $this->dal->set($this->id_osoba, $_POST['pass_nr'], $_POST['data_waznosci'], $_POST['nip'], $_POST['bank_id'], $_POST['nr_konta']);
            if ($result)
                return self::postSuccessfull($_SERVER['REQUEST_URI']);
                
            return 'Operacja nie powiod³a siê. <br />';
        }
    }
    
    
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    try {
        $output = new DocumentsView();

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
    
    CommonUtils::sendOutputBuffer();
?>
</html>
