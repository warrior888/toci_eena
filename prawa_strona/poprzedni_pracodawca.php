<?php
    require_once '../conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    require_once 'bll/definicjeKlas.php'; 
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/BLLDaneSlownikowe.php';
    require_once 'adl/Person.php'; 
    
    class FormerEmployersView extends View 
    {
        const DATA_SEPARATOR = '/';
        
        const FIELD_COUNTRY       = 'country';
        const FIELD_COUNTRY_ID    = 'country_id';
        const FIELD_CITY          = 'city';
        const FIELD_EMPLOYER_NAME = 'formerEmployer';
        const FIELD_DEPARTMENT    = 'department';
        const FIELD_POSITION      = 'position';
        const FIELD_PERIOD        = 'period';
        const FIELD_AGENCY        = 'agency';
        const FIELD_OCCUPATION    = 'occupation';
        
        const FIELD_HIDDEN_ID_PRAC = 'id_pracodawca';
        
        const MAX_LENGTH_EMPLOYER_NAME = 30;
        const MAX_LENGTH_COUNTRY       = 30;
        const MAX_LENGTH_CITY          = 30;
        const MAX_LENGTH_DEPARTMENT    = 100;
        const MAX_LENGTH_POSITION      = 100;
        const MAX_LENGTH_PERIOD        = 30;
        const MAX_LENGTH_AGENCY        = 30;
        
        const SUBMIT_ADD    = 'confirm_pprac';
        const SUBMIT_EDIT   = 'edit_pprac';
        const SUBMIT_UPDATE = 'update_pprac';
        const SUBMIT_ERASE  = 'erase_pprac';
        
        private $utilsUI, $id_osoba, $bllDaneSlownikowe;
        
        public function __construct ()
        {
            $this->actionList = array(
               self::SUBMIT_ADD     => User::PRIV_DODAWANIE_REKORDU,
               self::SUBMIT_EDIT    => User::PRIV_EDYCJA_REKORDU,
               self::SUBMIT_UPDATE  => User::PRIV_EDYCJA_REKORDU,
               self::SUBMIT_ERASE   => User::PRIV_KASOWANIE_REKORDU,
            );
            
            parent::__construct();
            $this->utilsUI = new UtilsUI('', 'id_os');
            $this->id_osoba = Utils::PodajIdOsoba();
            $this->person = new Person($this->id_osoba);
            $this->bllDaneSlownikowe = new BLLDaneSlownikowe();
        }
        
        public function getIdOsoba () 
        {
            return $this->id_osoba;
        }
        
        protected function setFormerEmployer ($country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId) 
        {
            return $this->person->setFormerEmployer($country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId);
        }
        
        protected function updateFormerEmployer ($country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId, $rowId) 
        {
            return $this->person->setFormerEmployer($country, $city, $firmName, $department, $position, $period, $agencyName, $occupationId, $rowId);
        }
        
        protected function deleteFormerEmployer ($rowId) 
        {
            return $this->person->deleteFormerEmployer($rowId);
        }
        
        protected function getFormerEmployersList () //$personId 
        {
            return $this->person->getFormerEmployers(); 
        }
        
        protected function getFormerEmployer ($rowId) 
        {
            return $this->person->getFormerEmployer($rowId);
        }
        
        public function viewFormerEmployers ()
        {            
            $employersData = $this->getFormerEmployersList(); //$this->id_osoba
            $data = null;
            $metadata = null;
            
            if (isset($employersData[Model::RESULT_FIELD_DATA]))
                $data = $employersData[Model::RESULT_FIELD_DATA];
            
            if (isset($employersData[Model::RESULT_FIELD_METADATA]))
                $metadata = $employersData[Model::RESULT_FIELD_METADATA];
            
            $result = '';
            if (isset($metadata[BLLDaneDodatkowe::HAS_EMP_HISTORY])) {
                $result .= '<br /><b>Posiada do¶w. zawodowe - '.$metadata[BLLDaneDodatkowe::HAS_EMP_HISTORY].'</b><br /><br />';
            } else {
                $result .= '<br /><b>Posiada do¶w. zawodowe - nie podano</b><br /><br />';
            }        
            //'<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="employersList">'
            $result .= '<table class="gridTable" border="0" cellspacing="0">'.$this->addFormPostPre($_SERVER['REQUEST_URI'], array('name' => 'employersList')).
            $this->htmlControls->_AddHidden(ID_OSOBA, ID_OSOBA, $this->id_osoba).
            $this->controls->AddHidden(self::FIELD_HIDDEN_ID_PRAC, self::FIELD_HIDDEN_ID_PRAC, '');
            
            //$result .= $this->utilsUI->buttonsNag(array('edycja_rekordu' => 1, 'kasowanie_rekordu' => 1));
            $result .= HelpersUI::addTableRow(array('Edycja', 'Kasowanie', 'L.P.', 'Poprzedni pracodawca', 'Agencja', 'Grupa zawodowa'), true);
            $count = 0;
            
            if (is_array($data))
            foreach($data as $row)
            {
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                $result .= '<tr class="'.$css.'">';
                if (empty($row[Model::COLUMN_PPR_ID_ODDZIALY_KLIENT]))
                {
                    $result .= '<td>'.$this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::SUBMIT_EDIT, $row[PoprzedniPracTab::COLUMN_ID], 'Edytuj.', '', "onclick='document.forms.employersList.".self::FIELD_HIDDEN_ID_PRAC.".value=this.id;'").'</td>';

                    $result .=  '<td align="center">'.$this->htmlControls->_AddSubmit(User::PRIV_KASOWANIE_REKORDU, self::SUBMIT_ERASE, $row[PoprzedniPracTab::COLUMN_ID], 'Kasuj.', '', "onclick='document.forms.employersList.".self::FIELD_HIDDEN_ID_PRAC.".value=this.id; return confirm(\"Operacja jest nieodwracalna, czy jeste¶ pewien ?\");'").'</td>';
                }
                else
                {
                    $result .=  '<td>-</td><td>-</td>';
                }
                
                //[PoprzedniPracTab::COLUMN_ID] 
                array_unshift($row, ++$count);
                unset($row[Model::COLUMN_PPR_ID_GRUPA_ZAWODOWA], $row[Model::COLUMN_PPR_ID], $row[Model::COLUMN_PPR_ID_WIERSZ], $row[Model::COLUMN_PPR_ID_ODDZIALY_KLIENT]);
                $result .= HelpersUI::addTableRow($row);
                $result .= '</tr>';
            }
            $result .= '</form></table>';
            
            return $result;
        }
        
        public function viewAddForm ()
        {
            //pañstwo/miasto/nazwa firmy/bran¿a/funkcja/okres zatrudnienia-Poprzedni pracodawca, 
            //Poprzedni pracodawca
            //$this->controls->AddHidden('id_id', 'id', $this->id_osoba).
            $countriesList = $this->bllDaneSlownikowe->getCountriesList();
            
            $result = '<table>'.$this->addFormPostPre($_SERVER['REQUEST_URI']).
            $this->controls->AddHidden(ID_OSOBA, ID_OSOBA, $this->id_osoba).
            '<tr><td>Nazwa firmy:</td><td>'.$this->controls->AddTextbox(self::FIELD_EMPLOYER_NAME, self::FIELD_EMPLOYER_NAME, '', self::MAX_LENGTH_EMPLOYER_NAME, '50', '').'</td></tr>'.
            '<tr><td>Pañstwo:</td><td>'.$this->htmlControls->_AddSelect(self::FIELD_COUNTRY, self::FIELD_COUNTRY, $countriesList[Model::RESULT_FIELD_DATA], null, self::FIELD_COUNTRY_ID, false).'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$this->controls->AddTextbox(self::FIELD_CITY, self::FIELD_CITY, '', self::MAX_LENGTH_CITY, '30', '').'</td></tr>'.
            '<tr><td>Bran¿a:</td><td>'.$this->controls->AddTextbox(self::FIELD_DEPARTMENT, self::FIELD_DEPARTMENT, '', self::MAX_LENGTH_DEPARTMENT, '30', '').'</td></tr>'.
            '<tr><td>Zakres obowi±zków:</td><td>'.$this->controls->AddTextbox(self::FIELD_POSITION, self::FIELD_POSITION, '', self::MAX_LENGTH_POSITION, '30', '').'</td></tr>'.
            '<tr><td>Agencja:</td><td>'.$this->controls->AddTextbox(self::FIELD_AGENCY, self::FIELD_AGENCY, '', self::MAX_LENGTH_AGENCY, '30', '').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$this->controls->AddTextbox(self::FIELD_PERIOD, self::FIELD_PERIOD, '', self::MAX_LENGTH_PERIOD, '30', '').'</td></tr>'.
            '<tr><td>Wykonywany zawód:</td><td>';
            //line adds control with readonly textbox and button to select the thing needed, it also sends by get 
            //table !!! name (to select from !!! - change that !!) and control id so that the child window can return data to a window opened by the button added
            //with the control

            $result .= $this->controls->OccGroupControl("Wybierz", "selectOcc", "occName", "occName", "", self::FIELD_OCCUPATION, self::FIELD_OCCUPATION, "", "wyborSlowPopUp.php?table=".ZawodTab::TABLE_NAME."&txtId=occName&hidId=".self::FIELD_OCCUPATION, "").
            '</td></tr><tr><td>'.$this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::SUBMIT_ADD, self::SUBMIT_ADD, 'Dodaj.', '', '').'</td></tr>'.
            '</form></table>';
            
            return $result;
        }
        
        public function viewEditEmployment ($rowId)
        {
            $data = $this->getFormerEmployer($rowId);
            if (strpos($data[PoprzedniPracTab::COLUMN_NAZWA], self::DATA_SEPARATOR) !== false) 
                list ($country, $city, $firmName, $department, $position, $period) = explode(self::DATA_SEPARATOR, $data[PoprzedniPracTab::COLUMN_NAZWA]);
            else 
            {
                $country = $city = $department = $position = $period = '';
                $firmName = $data[PoprzedniPracTab::COLUMN_NAZWA];
            }
            
            $agencyName = $data[Model::COLUMN_PPR_AGENCJA];
          
            $countriesList = $this->bllDaneSlownikowe->getCountriesList();
            
            $result = '<table>'.$this->addFormPostPre($_SERVER['REQUEST_URI']).
            $this->controls->AddHidden(ID_OSOBA, ID_OSOBA, $this->id_osoba).
            $this->controls->AddHidden(self::FIELD_HIDDEN_ID_PRAC, self::FIELD_HIDDEN_ID_PRAC, $rowId).
            '<tr><td>Nazwa firmy:</td><td>'.$this->controls->AddTextbox(self::FIELD_EMPLOYER_NAME, self::FIELD_EMPLOYER_NAME, $firmName, self::MAX_LENGTH_EMPLOYER_NAME, '50', '').'</td></tr>'.
            '<tr><td>Pañstwo:</td><td>'.$this->htmlControls->_AddSelect(self::FIELD_COUNTRY, self::FIELD_COUNTRY, $countriesList[Model::RESULT_FIELD_DATA], $country, self::FIELD_COUNTRY_ID, false).'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$this->controls->AddTextbox(self::FIELD_CITY, self::FIELD_CITY, $city, self::MAX_LENGTH_CITY, '30', '').'</td></tr>'.
            '<tr><td>Bran¿a:</td><td>'.$this->controls->AddTextbox(self::FIELD_DEPARTMENT, self::FIELD_DEPARTMENT, $department, self::MAX_LENGTH_DEPARTMENT, '30', '').'</td></tr>'.
            '<tr><td>Zakres obowi±zków:</td><td>'.$this->controls->AddTextbox(self::FIELD_POSITION, self::FIELD_POSITION, $position, self::MAX_LENGTH_POSITION, '30', '').'</td></tr>'.
            '<tr><td>Agencja:</td><td>'.$this->controls->AddTextbox(self::FIELD_AGENCY, self::FIELD_AGENCY, $agencyName, self::MAX_LENGTH_AGENCY, '30', '').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$this->controls->AddTextbox(self::FIELD_PERIOD, self::FIELD_PERIOD, $period, self::MAX_LENGTH_PERIOD, '30', '').'</td></tr>'.
            '<tr><td>Wykonywany zawód:</td><td>';
            
            //line adds control with readonly textbox and button to select the thing needed, it also sends by get 
            //table name and controls id so that the child window can return data to a window opened by the button added
            //with the control
            
            //site inside the window enables selecting one of the items, then window closes and returns data to main window
            $result .= $this->controls->OccGroupControl("Wybierz", "selectOcc", "occName", "occName", $data['grupa_zawodowa'], self::FIELD_OCCUPATION, self::FIELD_OCCUPATION, $data[PoprzedniPracTab::COLUMN_ID_GRUPA_ZAWODOWA], "wyborSlowPopUp.php?table=".ZawodTab::TABLE_NAME."&txtId=occName&hidId=".self::FIELD_OCCUPATION, "").
            '</td></tr><tr><td>'.$this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::SUBMIT_UPDATE, $rowId, 'Aktualizuj.', '', '').'</td></tr>'.
            '</form></table>';
            
            return $result;
        }
        
        public function addNewEmployment ($data)
        {
            if (strlen($data[self::FIELD_OCCUPATION]) > 0)
                $result = $this->setFormerEmployer($data[self::FIELD_COUNTRY], $data[self::FIELD_CITY], $data[self::FIELD_EMPLOYER_NAME], 
                    $data[self::FIELD_DEPARTMENT], $data[self::FIELD_POSITION], $data[self::FIELD_PERIOD], $data[self::FIELD_AGENCY],  
                    $data[self::FIELD_OCCUPATION]);
            else
                return 'Podanie zawodu jest konieczne.';
                
            if ($result)
            {
                return true;
            }
            
            return 'B³±d, nie dodano wpisu.';
        }
        
        public function updateEmployment ($data)
        {
            $result = $this->updateFormerEmployer($data[self::FIELD_COUNTRY], $data[self::FIELD_CITY], $data[self::FIELD_EMPLOYER_NAME], 
                $data[self::FIELD_DEPARTMENT], $data[self::FIELD_POSITION], $data[self::FIELD_PERIOD], $data[self::FIELD_AGENCY], 
                $data[self::FIELD_OCCUPATION], $data[self::FIELD_HIDDEN_ID_PRAC]);
            if ($result)
            {
                return 'Zaktualizowano wpis.';
            }
            
            return 'B³±d, nie aktualizowano wpisu.';
        }
        
        public function deleteEmployment ($data)
        {
            $result = $this->deleteFormerEmployer($data[self::FIELD_HIDDEN_ID_PRAC]);
            if ($result)
            {
                return 'Usuniêto wpis.';
            }
            
            return 'B³±d, nie usuniêto wpisu.';
        }
    }
    
    //CommonUtils::outputBufferingOn();
    //CommonUtils::SessionStart();

    //$outHtml = '<html>';
    //$outHtml .= HelpersUI::_addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    //$outHtml .= '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        // Embed into run method (move if isset inside 'module' methods)
        $output = new FormerEmployersView();
        $outHtml = '';

        if (isset($_POST[FormerEmployersView::SUBMIT_UPDATE]))
        {
            $outHtml .= $output->updateEmployment($_POST);
            //View::postSuccessfull($_SERVER['REQUEST_URI']);
        }
        if (isset($_POST[FormerEmployersView::SUBMIT_ERASE]))
        {
            $outHtml .= $output->deleteEmployment($_POST);
            //View::postSuccessfull($_SERVER['REQUEST_URI']);
        }
        if (isset($_POST[FormerEmployersView::SUBMIT_ADD]))
        {
            $result = $output->addNewEmployment($_POST);
            if (true === $result)
            {
                View::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            $outHtml .= $result;
        }
        if (isset($_POST[FormerEmployersView::SUBMIT_EDIT]))
        {
            $outHtml .= $output->viewEditEmployment($_POST[FormerEmployersView::FIELD_HIDDEN_ID_PRAC]);
        }

        $outHtml .= $output->viewFormerEmployers().'<hr />';
              
        if (!isset($_POST[FormerEmployersView::SUBMIT_EDIT]))                                                                                                         
        {
            $outHtml .= $output->viewAddForm();
        }
        
        //echo $outHtml;
    }
    //CommonUtils::sendOutputBuffer();
