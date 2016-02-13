<?php
    require_once '../conf.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'bll/BLLDaneSlownikowe.php';
    require_once 'adl/Person.php';
    
    class SkillsView extends View
    {
        const FORM_REMOVE_BUTTON        = 'usun_umiejetnosc';
        const FORM_SEEK_SKILL_BUTTON    = 'wyszukaj_umiejetnosc';
        const FORM_NEW_SKILL_BUTTON     = 'dodaj_umiejetnosc';
        const FORM_REMOVE_ID_HIDDEN     = 'id_usun_umiejetnosc';
        const FORM_ADD_ID_HIDDEN        = 'id_dodaj_umiejetnosc';
        const FORM_SEEK_SKILL_TEXTBOX   = 'umiejetnosc';
        
        //should't this extend from parent ?
        public static function instantiate () {
            return new SkillsView();
        }
        
        public function __construct () {
            
            $this->actionList = array(
                //ID_OSOBA                    => User::PRIV_AKTYWNY,
               self::FORM_NEW_SKILL_BUTTON  => User::PRIV_DODAWANIE_REKORDU,
               self::FORM_REMOVE_BUTTON     => User::PRIV_EDYCJA_REKORDU,
            );
            
            parent::__construct();
            $idOsoba = Utils::PodajIdOsoba();
            $this->person = new Person($idOsoba);
            $this->partials = new Partials($this->person);
        }
        
        public function run () {
            //entire page html through here
            $html = $this->viewSkills();
            
            return $html;
        }
        
        protected function viewSkills () {

            // Remove/Add post request servicing here
            
            if (isset($_POST[self::FORM_REMOVE_BUTTON])) {
                
                $skillId = (int)$_POST[self::FORM_REMOVE_ID_HIDDEN];
                if (!$skillId)
                    throw new ViewBadRequestException('Delete person skill error - unsupplied/invalid skill id');
                
                try {
                    $skillsDeleted = $this->person->deleteSkill($skillId);
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }    
                
                if ($skillsDeleted)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            if (isset($_POST[self::FORM_NEW_SKILL_BUTTON])) {
                
                $skillId = (int)$_POST[self::FORM_ADD_ID_HIDDEN];
                if (!$skillId)
                    throw new ViewBadRequestException('Delete person skill error - unsupplied/invalid skill id');
                    
                try {
                    $skillsSet = $this->person->setCompensation(Person::COMPENSATION_TYPE_SKILLS, $this->person->getPersonId(), array($skillId));
                } catch (ProjectLogicException $e) {
                    CommonUtils::mapLogicException($e);
                }
                
                if ($skillsSet)
                    return self::postSuccessfull($_SERVER['REQUEST_URI']);
            }            
            
            $result = '';
            
            $skillsListData = $this->person->getExtraData('getSkillsList', array($this->person->getPersonId()));
            
            $skillsList = null;
            $skillsMeta = null;
            
            if (isset($skillsListData[Model::RESULT_FIELD_DATA])) {
                
                $skillsList = $skillsListData[Model::RESULT_FIELD_DATA];
            }
            if (isset($skillsListData[Model::RESULT_FIELD_METADATA])) {
                $skillsMeta = $skillsListData[Model::RESULT_FIELD_METADATA];
            }
            
            if (isset($skillsMeta[BLLDaneDodatkowe::HAS_SKILLS])) {
                $result .= '<br /><b>Posiada dodatkowe umiejêtno¶ci - '.$skillsMeta[BLLDaneDodatkowe::HAS_SKILLS].'</b><br /><br />';
            } else {
                $result .= '<br /><b>Posiada dodatkowe umiejêtno¶ci - nie podano</b><br /><br />';
            }
            
            if (isset($_POST[self::FORM_SEEK_SKILL_BUTTON])) {
                
                $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                $result .= $this->htmlControls->_AddHidden(self::FORM_ADD_ID_HIDDEN, self::FORM_ADD_ID_HIDDEN, '');
                
                // Found skills
                $skillsFoundTable = new HtmlTable();
                $skillsFoundTable->setHeader(array('Umiejetno¶æ', 'Wybierz'));
                
                $bllDaneSlownikowe = new BLLDaneSlownikowe();
                $data = $bllDaneSlownikowe->getSkillsDifference($this->person->getPersonId(), $_POST[self::FORM_SEEK_SKILL_TEXTBOX]);
                
                if ($data) {
                    foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                        
                        $skillsFoundTable->addRow(array(
                            $row[Model::COLUMN_DICT_NAZWA],
                            $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_NEW_SKILL_BUTTON, self::FORM_NEW_SKILL_BUTTON, 'Wybierz', '', 
                            JsEvents::ONCLICK.'="'.self::FORM_ADD_ID_HIDDEN.'.value='.$row[Model::COLUMN_DICT_ID].';"'),
                        ));
                    }
                
                    $result .= $skillsFoundTable->__toString();
                } else {
                    
                    $result .= '<h2>Brak wyników dla zadanego kryterium.</h2>';
                }
                $result .= $this->addFormSuf();
            }

            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= '<table><tr><td>Wybierz umiejetno¶æ:</td></tr><tr><td>';
            $result .= $this->htmlControls->_AddTextbox(self::FORM_SEEK_SKILL_TEXTBOX, self::FORM_SEEK_SKILL_TEXTBOX, '', 50, 30, '');
            $result .= '</td><td>';
            $result .= $this->htmlControls->_AddSubmit(User::PRIV_AKTYWNY, self::FORM_SEEK_SKILL_BUTTON, self::FORM_SEEK_SKILL_BUTTON, 'Wyszukaj', '', '');
            $result .= '</td></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= $this->htmlControls->_AddHidden(self::FORM_REMOVE_ID_HIDDEN, self::FORM_REMOVE_ID_HIDDEN, '');
                        
            $licznik = 1;
            if ($skillsList) {
                
                $tableHtml = new HtmlTable();
                $tableHtml->setHeader(array('Lp.', 'Umiejetno¶æ', 'Usuñ'));
                
                foreach ($skillsList as $skill)
                {
                    $tableHtml->addRow(
                        array(
                            $licznik, 
                            $skill[Model::COLUMN_DICT_NAZWA], 
                            $this->htmlControls->_AddDeleteSubmit(User::PRIV_EDYCJA_REKORDU, self::FORM_REMOVE_BUTTON, 'id_Usun', 'Usuñ', '', '', 
                                self::FORM_REMOVE_ID_HIDDEN.'.value='.$skill[Model::COLUMN_UMO_ID_WIERSZ].';'),
                        )
                    );

                    $licznik++;
                }
                
                $result .= $tableHtml->__toString();
            }
            
            $result .= $this->htmlControls->_AddNoPrivilegeSubmit('Zamknij', 'id_Zamknij', 'Zamknij', '', JsEvents::ONCLICK.'="window.close();"');
            $result .= $this->addFormSuf();
            
            return $result;
        }
    }
    
    $output = SkillsView::instantiate ();
    $html = $output->execute(false);
    
    