<?php
    require_once '../conf.php';
    require_once '../bll/queries.php';
    require_once '../bll/BLLDaneInternet.php';
    require_once '../ui/UtilsUI.php';
    require_once '../ui/HelpersUI.php';
    
    require_once '../bll/definicjeKlas.php';
    //grande todo
    
    /**
    * @desc 
    * 
    * Niezbedne funkcjonalnosci: lista filtrowana wg lokalizacji, kodow ? (szukanie)
    * dodawanie kodu do lokalizacji
    * usuwanie kodu z lokalizacji
    * dodawanie lokalizacji ? - slowik zwykly
    * edycja kodu w lokalizacji ?
    */
    class PostalCodesView extends View 
    {
        const ACTION_ADD    = 'dodaj_wpis';
        const ACTION_ERASE  = 'kasuj_wpis';
        
        const ACTION_MESSAGE_EDIT   = 'edytuj_wiadomosc';
        const ACTION_MESSAGE_UPDATE = 'aktualizuj_wiadomosc';
        
        const HIDDEN_ID_KOLUMNA    = 'id_key';
        
        const HIDDEN_ID_MESSAGE         = 'id_message';
        const HIDDEN_ID_UPDATE_MESSAGE  = 'id_update_message';
        const INPUT_MESSAGE             = 'wiadomosc';
        
        const FORM_CODES_SEARCH        = 'szukaj';
        
        const FORM_CODES_ADD           = 'dodaj';
        const FORM_CODES_DELETE        = 'kasuj';
        
        const FORM_COLUMN_CODE         = 'kod'; 
        
        
        const FORM_DEPARTMENT_NAME        = 'filia_nazwa'; 
        const FORM_DEPARTMENT_NAME_SEARCH = 'filia_nazwa_szukanie'; 
        const FORM_CODE_NAME              = 'kod_wartosc';
        const FORM_CODE_PREFIX            = 'kod_prefix';
        const FORM_CODE_RANGE_FROM        = 'kod_zakres_od';
        const FORM_CODE_RANGE_TO          = 'kod_zakres_do';
        
        const VALID_CODE                  = '/^[0-9]{2}\-[0-9]{3}$/'; 
        const CODE_SEPARATOR              = '-';
        
        public function __construct()
        {
            $this->bll = new BLLDaneInternet();
            
            $this->setActionList(
                array(
                    self::FORM_CODES_ADD        => User::PRIV_DODAWANIE_REKORDU,
                    self::FORM_CODES_DELETE     => User::PRIV_KASOWANIE_REKORDU,
                    self::ACTION_MESSAGE_EDIT   => User::PRIV_EDYCJA_REKORDU,
                    self::ACTION_MESSAGE_UPDATE => User::PRIV_EDYCJA_REKORDU,
                )
            );
            
            parent::__construct(self::LOG_IN_LEVEL_LOGGED, User::PRIV_ZMIANA_UPRAWNIEN);
        }
        //#region html forms
        public function addSearchForm ($data = null)
        {
            $id_filia = null;
            $kod = null;
            
            if (isset($data[self::FORM_DEPARTMENT_NAME_SEARCH.'_id']))
            {
                $id_filia = $data[self::FORM_DEPARTMENT_NAME_SEARCH.'_id'];
                $kod = $data[self::FORM_CODE_NAME];
            }
            
            $result = $this->addFormPostPre().'<table><tr>';
            $result .= '<td>Filia: </td><td>'.$this->controls->AddSelectRandomQuerySVbyId(self::FORM_DEPARTMENT_NAME_SEARCH, 'id_'.self::FORM_DEPARTMENT_NAME_SEARCH, '', 'select id, nazwa from firma_filia', $id_filia, self::FORM_DEPARTMENT_NAME_SEARCH.'_id').'</td>';
            $result .= '<td>Kod: </td><td>'.$this->controls->AddTextbox(self::FORM_CODE_NAME, 'id_'.self::FORM_CODE_NAME, $kod, 6, 6, '').'</td>';
            $result .= '<td></td><td>'.$this->controls->AddSubmit(self::FORM_CODES_SEARCH, 'id_'.self::FORM_CODES_SEARCH, 'Szukaj', '').'</td>';
            
            $result .= '</tr></table>'.$this->addFormSuf();
            
            return $result;
        }
        
        public function addAddForm () 
        {
            $result = $this->addFormPostPre().'<table><tr>';
            
            $result .= '<td>Filia: </td><td>'.$this->controls->AddSelectRandomQuery(self::FORM_DEPARTMENT_NAME, 'id_'.self::FORM_DEPARTMENT_NAME, '', 'select id, nazwa from firma_filia', '', self::FORM_DEPARTMENT_NAME.'_id').'</td>';
            $result .= '<td>Prefix kodu: </td><td>'.$this->controls->AddTextbox(self::FORM_CODE_PREFIX, 'id_'.self::FORM_CODE_PREFIX, '', 2, 1, '').'</td>';
            $result .= '<td>Zakres od: </td><td>'.$this->controls->AddTextbox(self::FORM_CODE_RANGE_FROM, 'id_'.self::FORM_CODE_RANGE_FROM, '', 3, 2, '').'</td>';
            $result .= '<td>Zakres do: </td><td>'.$this->controls->AddTextbox(self::FORM_CODE_RANGE_TO, 'id_'.self::FORM_CODE_RANGE_TO, '', 3, 2, '').'</td>';
            $result .= '<td></td><td>'.$this->controls->AddSubmit(self::FORM_CODES_ADD, 'id_'.self::FORM_CODES_ADD, 'Dodaj', '').'</td>';
            
            $result .= '</tr></table>'.$this->addFormSuf();
            
            return $result;
        }
        
        //#endregion html forms
        
        public function showCodesList ($filiaId, $code) 
        {
            $data = $this->getCodesList($filiaId, $code);
            
            if ($data === null)
                return 'Brak danych.';
                        
            $result = $this->addFormPostPre().'<table class="gridTable" border="0" cellspacing="0">';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_KOLUMNA, self::HIDDEN_ID_KOLUMNA, '');
            
            $result .= '<tr><th>Kod</th><th>Miejscowosc</th><th>Przypisana filia</th><th>Kasowanie</th></tr>';
            $count = 0;
            foreach ($data as $row) 
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                $result .= '<tr class="'.$css.'">';
                $result .= '<td>'.$row['kod'].'</td>';
                $result .= '<td>'.$row['miejscowosc'].'</td>';
                $result .= '<td>'.$row['filia'].'</td>';
                  
                $result .= '<td>'.$this->controls->AddDeleteSubmit(self::FORM_CODES_DELETE, 'id_'.self::FORM_CODES_DELETE, 'Kasuj', self::HIDDEN_ID_KOLUMNA.'.value = \''.$row['id'].'\';', '').'</td>';
                $result .= '</tr>';
            }
            
            $result .= '</table>'.$this->addFormSuf();
            
            return $result;
        }
        
        public function deleteColumn ($data)
        {
            $kod = $data[self::HIDDEN_ID_KOLUMNA];
            
            $result = $this->controls->dalObj->pgQuery('delete from kody_rejestracja_filia where kod = \''.$kod.'\'');
            
            if ($result)
                return 'Usuniêto kod poprawnie.<br />';
                
            return 'B³±d przy usuwaniu kodu.<br />';
        }
        
        public function addColumn($data)
        { 
            $id_firma_filia = (int)$data[self::FORM_DEPARTMENT_NAME.'_id'];
            $prefix = (is_numeric($data[self::FORM_CODE_PREFIX]) && strlen($data[self::FORM_CODE_PREFIX]) == 2) ? $data[self::FORM_CODE_PREFIX] : null;
            $rangeFrom = (is_numeric($data[self::FORM_CODE_RANGE_FROM]) && strlen($data[self::FORM_CODE_RANGE_FROM]) == 3) ? $data[self::FORM_CODE_RANGE_FROM] : null;
            $rangeTo = (is_numeric($data[self::FORM_CODE_RANGE_TO]) && strlen($data[self::FORM_CODE_RANGE_TO]) == 3) ? $data[self::FORM_CODE_RANGE_TO] : null;
            
            if (!$prefix || !$rangeFrom)
            {
                return 'Brak danych.<br />';
            }
            
            $kod = $prefix.self::CODE_SEPARATOR.$rangeFrom;
            
            if (!preg_match(self::VALID_CODE, $kod))
            {
                return 'Nieprawid³owy kod.<br />';
            }
            //rozwazyc select najpierw
            $result = true;
            $errMsg = '';
            if ($rangeTo && $rangeFrom < $rangeTo) {
                
                for ($rangeFrom; $rangeFrom <= $rangeTo; $rangeFrom++) {
                    
                    $codeSuffix = str_pad($rangeFrom, 3, '0', STR_PAD_LEFT);
                    $kod = $prefix.self::CODE_SEPARATOR.$codeSuffix;
                    try {
                    
                        $result = $result && $this->controls->dalObj->pgQuery('insert into kody_rejestracja_filia (kod, id_firma_filia) values (\''.$kod.'\', '.$id_firma_filia.')');
                    } catch (DBQueryErrorException $e) {
                        
                        $errMsg .= 'Kod '.$kod.' powtórzony.<br />';
                    }
                }
            } else {

                try {
                    
                    $result = $this->controls->dalObj->pgQuery('insert into kody_rejestracja_filia (kod, id_firma_filia) values (\''.$kod.'\', '.$id_firma_filia.')');
                } catch (DBQueryErrorException $e) {
                        
                    $errMsg .= 'Kod '.$kod.' powtórzony.<br />';
                }
            }
            
            if ($result && strlen($errMsg) == 0)
                return 'Dodano kod poprawnie.<br />';
                
            return 'B³±d w dodawaniu kodu (ów).<br />'.$errMsg;
        }
        
        public function viewSmsWelcomeMessage () {
            
            $html = '';
            
            if (isset($_POST[self::ACTION_MESSAGE_UPDATE])) {
                
                $messageId = (int)$_POST[self::HIDDEN_ID_UPDATE_MESSAGE];
                $message = $_POST[self::INPUT_MESSAGE];
                
                $result = $this->bll->setSmsMessage($messageId, $message);
                
                if ($result)
                    self::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            
            $messages = $this->bll->getSmsMessages();
            $tableData = $messages[Model::RESULT_FIELD_DATA];
            
            if (isset($_POST[self::ACTION_MESSAGE_EDIT])) {
                
                $messageId = (int)$_POST[self::HIDDEN_ID_MESSAGE];
                
                $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
                
                $html .= $this->htmlControls->_AddHidden(self::HIDDEN_ID_UPDATE_MESSAGE, self::HIDDEN_ID_UPDATE_MESSAGE, $messageId);
                $html .= $this->htmlControls->_AddTextarea(self::INPUT_MESSAGE, self::INPUT_MESSAGE, $tableData[$messageId][Model::COLUMN_SKA_TRESC], 160, 5, 60, '', '', '', '');
                $html .= $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::ACTION_MESSAGE_UPDATE, self::ACTION_MESSAGE_UPDATE, 'Aktualizuj', '', '');
                
                $html .= $this->addFormSuf();
            }

            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= $this->htmlControls->_AddHidden(self::HIDDEN_ID_MESSAGE, self::HIDDEN_ID_MESSAGE, '');
            
            $headers = array('Edycja', 'Wiadomo¶æ');
            $columnsOrder = array(self::ACTION_MESSAGE_EDIT, Model::COLUMN_SKA_TRESC);
            
            foreach ($tableData as $key => $entry) {
                
                $tableData[$key][self::ACTION_MESSAGE_EDIT] = $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, self::ACTION_MESSAGE_EDIT, 'id'.self::ACTION_MESSAGE_EDIT.$entry[Model::COLUMN_SKA_ID], 'Edytuj', '', 'onclick='.self::HIDDEN_ID_MESSAGE.'.value='.$entry[Model::COLUMN_SKA_ID]);
            }
                
            $htmlTable = new HtmlTable($tableData, $columnsOrder);
            $htmlTable->setHeader($headers);

            $html .= $htmlTable->__toString();
                
            $html .= $this->addFormSuf();
            
            return $html;
        }
        
        public function run () {
            
            $html = '';
            
            if (isset($_SESSION['zmiana_uprawnien']) && ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['kody_pocztowe_rejestracje'])))
            {                
                $html = $this->addAddForm();
                $html .= '<hr />';
                $html .= $this->addSearchForm($_POST);
                
                if (isset($_POST[PostalCodesView::FORM_CODES_ADD]))
                {
                    $html .= $this->addColumn($_POST);
                }
                
                if (isset($_POST[PostalCodesView::FORM_CODES_DELETE]))
                {
                    $html .= $this->deleteColumn($_POST);
                }
                
                if (isset($_POST[PostalCodesView::FORM_CODES_SEARCH]))
                {
                    $html .= $this->showCodesList($_POST[PostalCodesView::FORM_DEPARTMENT_NAME_SEARCH.'_id'], $_POST[PostalCodesView::FORM_CODE_NAME]);
                }
                
                $html .= '<hr />';
            }
            
            $html .= $this->viewSmsWelcomeMessage();
            
            return $html;
        }    
        
        private function getCodesList ($filiaId, $code)
        {
            return $this->controls->dalObj->PobierzDane(
            'select kody_rejestracja_filia.kod as id, kody_rejestracja_filia.kod, firma_filia.nazwa as filia, miejscowosc.nazwa as miejscowosc  
                from kody_rejestracja_filia join firma_filia on kody_rejestracja_filia.id_firma_filia = firma_filia.id 
                left join kod_pocztowy on kody_rejestracja_filia.kod = kod_pocztowy.kod left join miejscowosc on kod_pocztowy.id_miejscowosc = miejscowosc.id 
                where kody_rejestracja_filia.id_firma_filia = '.$filiaId.' and kody_rejestracja_filia.kod like \''.$code.'\' order by kod', 
            $ilosc_wierszy);
        }
        
    }
    
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();
////////////-------------------------------

    try {
        
        $output = new PostalCodesView();
        
        if (!$output->getUser()->isLogged()) {
            
            require 'logowanie.php';
            die();
        } else {
        
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
    
    CommonUtils::sendOutputBuffer();
?>
</html>