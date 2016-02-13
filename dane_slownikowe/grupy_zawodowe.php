<?php 

    require_once '../conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    
    class ProfessionalGroupsView extends View 
    {
        private $dal;
        
        public function __construct() {
            
            $this->dal = dal::getInstance();
            
            $this->actionList = array(
               
               self::FORM_SZUKAJ        => User::PRIV_ZMIANA_UPRAWNIEN
            );
            
            parent::__construct(self::LOG_IN_LEVEL_LOGGED, User::PRIV_MASOWY_SMS);
        }
        
        const NOWY_KOD_GRUPY         = 'nowy_kod_grupy';
        const STARY_KOD_GRUPY        = 'stary_kod_grupy';
        const STARY_ZAWOD            = 'stary_zawod';
        const NOWY_ZAWOD             = 'nowy_zawod';
        const ID_ZAWOD               = 'id_zawod';
        
        const FORM_SZUKAJ            = 'szukaj';
        const FORM_EDYTUJ            = 'edytuj';
        const FORM_AKTUALIZUJ        = 'aktualizuj';
        
        public function run () {
            //entire page html through here
            $html = '';
            $html .= $this->viewSearchForm();
            
            return $html;
        }
        
        private function viewSearchForm() {
            
            $html = '';
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            $oldCode = !empty($_POST[self::STARY_KOD_GRUPY]) ? (int)$_POST[self::STARY_KOD_GRUPY] : '';
            $newCode = !empty($_POST[self::NOWY_KOD_GRUPY]) ? (int)$_POST[self::NOWY_KOD_GRUPY] : '';
            $oldJobTxt = isset($_POST[self::STARY_ZAWOD]) ? $_POST[self::STARY_ZAWOD] : '';
            
            $html .= '<table>';
            $html .= '<tr><td>Kod grupy (nowy):</td><td>'.$this->htmlControls->_AddTextbox(self::NOWY_KOD_GRUPY, 'id_'.self::NOWY_KOD_GRUPY, $newCode, 4, 4, '').'</td></tr>';
            $html .= '<tr><td>Kod grupy (stary):</td><td>'.$this->htmlControls->_AddTextbox(self::STARY_KOD_GRUPY, 'id_'.self::STARY_KOD_GRUPY, $oldCode, 4, 4, '').'</td></tr>';
            $html .= '<tr><td>Nazwa zawodu (stara):</td><td>'.$this->htmlControls->_AddTextbox(self::STARY_ZAWOD, 'id_'.self::STARY_ZAWOD, $oldJobTxt, 200, 50, '').'</td></tr>';
            $html .= '<tr><td>'.$this->htmlControls->_AddSubmit(User::PRIV_ZMIANA_UPRAWNIEN, self::FORM_SZUKAJ, self::FORM_SZUKAJ, 'Szukaj', '', '').'</td></tr>';
            $html .= '</table>';
            
            $html .= $this->addFormSuf();
            
            if (isset($_POST[self::FORM_SZUKAJ]))
                $html .= $this->viewProfessions();
                
            if (isset($_POST[self::FORM_EDYTUJ]))
                $html .= $this->editProfession();
                
            if (isset($_POST[self::FORM_AKTUALIZUJ]))
                $html .= $this->updateProfession();
            
            return $html;
        }
        
        private function editProfession() {
            
            $html = '';
            
            $idZawod = (int)$_POST[self::ID_ZAWOD];
            
            if (!$idZawod)
                return 'cos nie halo z tym edytuj';
                
            $professionData = $this->dal->PobierzDane('select * from zawod where id = '.$idZawod);
            $professionRow = $professionData[0];
            
            //TODO update form
            //$html = $professionRow['kod_grupy'];
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            $html .= $this->htmlControls->_AddHidden(self::ID_ZAWOD, self::ID_ZAWOD, $idZawod);
            
            $html .= '<table>';
            $html .= '<tr><td>Kod grupy (nowy):</td><td>'.$this->htmlControls->_AddTextbox(self::NOWY_KOD_GRUPY, 'id_'.self::NOWY_KOD_GRUPY, $professionRow['kod_grupy'], 4, 4, '').'</td></tr>';
            $html .= '<tr><td>Kod grupy (stary):</td><td>'.$this->htmlControls->_AddTextbox(self::STARY_KOD_GRUPY, 'id_'.self::STARY_KOD_GRUPY, $professionRow['kod_grupy_2011'], 4, 4, '').'</td></tr>';
            $html .= '<tr><td>Nazwa zawodu (nowa):</td><td>'.$this->htmlControls->_AddTextbox(self::NOWY_ZAWOD, 'id_'.self::NOWY_ZAWOD, $professionRow['nazwa'], 200, 50, '').'</td></tr>';
            $html .= '<tr><td>'.$this->htmlControls->_AddSubmit(User::PRIV_ZMIANA_UPRAWNIEN, self::FORM_AKTUALIZUJ, self::FORM_AKTUALIZUJ, 'Aktualizuj', '', '').'</td></tr>';
            $html .= '</table>';
            
            $html .= $this->addFormSuf();
            
            return $html;
        }
        
        private function updateProfession() {
            
            $idZawod = (int)$_POST[self::ID_ZAWOD];
            $oldCode = !empty($_POST[self::STARY_KOD_GRUPY]) ? (int)$_POST[self::STARY_KOD_GRUPY] : '';
            $newCode = !empty($_POST[self::NOWY_KOD_GRUPY]) ? (int)$_POST[self::NOWY_KOD_GRUPY] : '';
            $newJobTxt = isset($_POST[self::NOWY_ZAWOD]) ? $_POST[self::NOWY_ZAWOD] : '';
            
            if (!$idZawod) {
                
                return 'cos nie halo z tym aktualizuj';
            }
            
            $set = array();
            
            if ($newCode)
                $set[] = 'kod_grupy = '.$newCode;
            if ($oldCode)
                $set[] = 'kod_grupy_2011 = '.$oldCode;
            if ($newJobTxt)
                $set[] = 'nazwa = \''.$newJobTxt.'\'';
            
            if (sizeof($set))
                $this->dal->pgQuery('update zawod set '.implode(', ', $set).' where id = '.$idZawod);
        }
        
        private function viewProfessions() {
            
            $oldCode = !empty($_POST[self::STARY_KOD_GRUPY]) ? (int)$_POST[self::STARY_KOD_GRUPY] : '';
            $newCode = !empty($_POST[self::NOWY_KOD_GRUPY]) ? (int)$_POST[self::NOWY_KOD_GRUPY] : '';
            $oldJobTxt = isset($_POST[self::STARY_ZAWOD]) ? $_POST[self::STARY_ZAWOD] : '';
            
            if (!$oldCode && !$newCode && !$oldJobTxt)
                return 'ni ma czego szukac';
            
            $html = '';
            $searchArray = array();
            
            if ($oldCode)
                $searchArray[] = 'kod_grupy_2011 = \''.$oldCode.'\'';
                
            if ($newCode)
                $searchArray[] = 'kod_grupy = \''.$newCode.'\'';
                
            if ($oldJobTxt)
                $searchArray[] = 'lower(nazwa_org_2011) like lower(\'%'.$oldJobTxt.'%\')';
                
            $tableData = $this->dal->PobierzDane('select * from zawod where '.implode(' and ', $searchArray).';');
            
            $headers = array('Edycja', 'id', 'nazwa', 'kod', 'widoczne', 'nazwa bez zmian', 'stary kod', 'stara nazwa', 'stara nazwa bez zmian');
            
            foreach ($tableData as &$tableRow) {
                
                array_unshift($tableRow, $this->htmlControls->_AddSubmit(User::PRIV_ZMIANA_UPRAWNIEN, self::FORM_EDYTUJ, $tableRow['id'].'edit', 'Edytuj', '', 'onclick="id_zawod.value = '.$tableRow['id'].';"'));
            }
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            $html .= $this->htmlControls->_AddHidden(self::ID_ZAWOD, self::ID_ZAWOD, '');
            
            $htmlTable = new HtmlTable($tableData);
            $htmlTable->setHeader($headers);
                
            $html .= $htmlTable->__toString();
            
            $html .= $this->addFormSuf();
            
            return $html;
        }
    }
    
    CommonUtils::SessionStart();

    try {
        $output = new ProfessionalGroupsView();

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