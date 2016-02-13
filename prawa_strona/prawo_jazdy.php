<?php
    require_once '../conf.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/BLLDaneSlownikowe.php';
    require_once 'adl/Person.php';
    
    class DrivingLicensesView extends View
    {
        const FORM_REMOVE_ID_HIDDEN     = 'id_pj_usun';
        const FORM_ADD_CATEGORY_ID      = 'kategoria_pj_id';
        const FORM_REMOVE_BUTTON        = 'usun_prawo_jazdy';
        const FORM_ADD_BUTTON           = 'dodaj_prawo_jazdy';
        const FORM_CATEGORY_SELECT      = 'kategoria_pj';
        const FORM_ADD_HAS_LICENSE      = 'set_has_license';
        const FORM_REMOVE_HAS_LICENSE   = 'set_has_not_license';
        
        public function __construct () {
            
            $this->actionList = array(
               self::FORM_REMOVE_BUTTON      => User::PRIV_EDYCJA_REKORDU,
               self::FORM_ADD_BUTTON         => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_ADD_HAS_LICENSE    => User::PRIV_EDYCJA_REKORDU,
               self::FORM_REMOVE_HAS_LICENSE => User::PRIV_EDYCJA_REKORDU,
            );
            
            parent::__construct();
            $idOsoba = Utils::PodajIdOsoba();
            $this->person = new Person($idOsoba);
            $this->partials = new Partials($this->person);
        }
        
        public function run () {
            //entire page html through here
            $html = $this->viewDrivingLicenses();
            
            return $html;
        }
        
        protected function viewDrivingLicenses () {
            
            if (isset($_POST[self::FORM_ADD_BUTTON])) {
                
                $licenseId = (int)$_POST[self::FORM_ADD_CATEGORY_ID];
                if (!$licenseId)
                    throw new ViewBadRequestException('Add person driving license error - unsupplied/invalid license id');
                    
                try {
                    $licensesSet = $this->person->setCompensation(Person::COMPENSATION_TYPE_DRIVING_LICENSE, $this->person->getPersonId(), array($licenseId));
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }
                
                if ($licensesSet)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            if (isset($_POST[self::FORM_REMOVE_BUTTON])) {
                
                $rowId = (int)$_POST[self::FORM_REMOVE_ID_HIDDEN];
                if (!$rowId)
                    throw new ViewBadRequestException('Add person driving license error - unsupplied/invalid license id');
                    
                try {
                    $licenseDeleted = $this->person->deleteDrivingLicense($rowId);
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }    
                
                if ($licenseDeleted)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            if (isset($_POST[self::FORM_REMOVE_HAS_LICENSE])) {

                $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                $bllDaneDodatkowe->setAdditionalInfoRow($this->person->getPersonId(), $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE), false);
            }
            
            if (isset($_POST[self::FORM_ADD_HAS_LICENSE])) {
                
                $bllDaneDodatkowe = new BLLDaneDodatkowe(false);
                $bllDaneDodatkowe->setAdditionalInfoRow($this->person->getPersonId(), $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE), true);
            }
            
            $licensesListData = $this->person->getExtraData('getDrivingLicenses', array($this->person->getPersonId()));
            
            $licensesList = null;
            $licensesMeta = null;
            
            if (isset($licensesListData[Model::RESULT_FIELD_DATA])) {
                
                $licensesList = $licensesListData[Model::RESULT_FIELD_DATA];
            }
            if (isset($licensesListData[Model::RESULT_FIELD_METADATA])) {
                $licensesMeta = $licensesListData[Model::RESULT_FIELD_METADATA];
            }
            
            if (isset($licensesMeta[BLLDaneDodatkowe::HAS_DRIVING_LICENSE])) {
                $result = '<b>Posiada prawo jazdy - '.$licensesMeta[BLLDaneDodatkowe::HAS_DRIVING_LICENSE].'</b><br /><br />';
                $hasDrvLicense = $licensesMeta[BLLDaneDodatkowe::HAS_DRIVING_LICENSE] == BLLDaneDodatkowe::BOOL_TRUE;
            } else {
                $result = '<b>Posiada prawo jazdy - nie podano</b><br /><br />';
                $hasDrvLicense = false;
            }
            
            $bllDaneSlownikowe = new BLLDaneSlownikowe();
            $data = $bllDaneSlownikowe->getLicensesDifference($this->person->getPersonId());
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            
            if ($data) {
                // TODO move to html controls
                $result .= $this->controls->AddSelectWithData(self::FORM_CATEGORY_SELECT, self::FORM_CATEGORY_SELECT, '', $data[Model::RESULT_FIELD_DATA], null, self::FORM_ADD_CATEGORY_ID);
                $result .= $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_ADD_BUTTON, 'id_dodaj', 'Dodaj.', '', '');
            }
                        
            $result .= $this->addFormSuf();
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->htmlControls->_AddHidden(self::FORM_REMOVE_ID_HIDDEN, self::FORM_REMOVE_ID_HIDDEN, '');

            $licznik = 1;
            if ($licensesList) {
                
                $tableHtml = new HtmlTable();
                $tableHtml->setHeader(array('Lp.', 'Prawo jazdy', 'Usuñ'));
            
                foreach ($licensesList as $license)
                {
                    $tableHtml->addRow(
                        array(
                            $licznik, 
                            $license[Model::COLUMN_DICT_NAZWA], 
                            $this->htmlControls->_AddDeleteSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_REMOVE_BUTTON, 'id_Usun', 'Usuñ.', '', '', 
                                self::FORM_REMOVE_ID_HIDDEN.'.value='.$license[Model::COLUMN_PPJ_ID_WIERSZ].';'),
                        )
                    );

                    $licznik++;
                }
                
                $result .= $tableHtml->__toString();
            } else {
                
                if ($hasDrvLicense)
                {
                    //enable ze nie ma
                    $result .= $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_REMOVE_HAS_LICENSE, 'id_rm_dl_info', 'Nie posiada prawa jazdy', '', '').'<br />';
                }
                else
                {
                    //enable ze ma
                    $result .= $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_ADD_HAS_LICENSE, 'id_add_dl_info', 'Posiada prawo jazdy', '', '').'<br />';
                }
            }
            
            //$result .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            $result .= $this->addFormSuf();
            
            return $result;
        }
    }
    
    CommonUtils::SessionStart();

    try {
        $output = new DrivingLicensesView();

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
    
    /*echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    echo $html;
    echo '</body></html>';*/

?>