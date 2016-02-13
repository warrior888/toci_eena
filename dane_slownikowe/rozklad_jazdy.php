<?php
    require_once '../conf.php';
    require_once '../bll/queries.php';
    require_once '../ui/UtilsUI.php';
    require_once '../ui/HelpersUI.php';
    
    class DepartureListView extends View 
    {
        const ACTION_ADD    = 'dodaj_wpis';
        const ACTION_EDIT   = 'edytuj_wpis';
        const ACTION_ERASE  = 'kasuj_wpis';
        const ACTION_UPDATE = 'aktualizuj_wpis';
        
        const HIDDEN_ID_KOLUMNA    = 'id_rozklad_wpis';
        const HIDDEN_ID_UP_KOLUMNA = 'id_up_rozklad_wpis';
        const HIDDEN_ID_DZIEN      = 'id_dzien';
        const HIDDEN_ID_PRZEWOZNIK = 'id_przewoznik';
        const HIDDEN_ID_MSC        = 'id_msc_odjazd';
        
        const FORM_TRAVELER_NAME       = 'przewoznik';
        const FORM_PLACE_NAME          = 'place';
        const FORM_DAY_NAME            = 'dzien';
        const FORM_DEPARTURES_SEARCH   = 'szukaj';
        
        const FORM_DEPARTURES_EDIT     = 'edytuj';
        const FORM_DEPARTURES_ADD      = 'dodaj';
        const FORM_DEPARTURES_UPDATE   = 'aktualizuj';
        const FORM_DEPARTURES_HIDE     = 'ukryj';
        const FORM_DEPARTURES_SHOW     = 'pokaz';
        const FORM_DEPARTURES_DELETE   = 'usun';
        
        const FORM_COLUMN_DISPLAY_NAME = 'nazwa_wysw_kolumny';
        const FORM_COLUMN_TYPE_ID      = 'typ_id';
        
        const FORM_COLUMN_ANKIETA      = 'ankieta';  
        
        private $dayList = array (
            1 => array('id' => 1, 'nazwa' => 'Poniedzia³ek'),
            2 => array('id' => 2, 'nazwa' => 'Wtorek'),
            3 => array('id' => 3, 'nazwa' => '¦roda'),
            4 => array('id' => 4, 'nazwa' => 'Czwartek'),
            5 => array('id' => 5, 'nazwa' => 'Pi±tek'),
            6 => array('id' => 6, 'nazwa' => 'Sobota'),
            0 => array('id' => 0, 'nazwa' => 'Niedziela'),
        );
        
        private $hourList = array();
        private $minutesList = array(array('id' => 0, 'nazwa' => '00'), array('id' => 5, 'nazwa' => '05'));
        
        public function __construct()
        {
            parent::__construct();
            for ($i = 1; $i < 24; $i++)
            {
                if ($i < 10)
                    $i = '0'.$i;
                    
                $this->hourList[] = array('id' => $i, 'nazwa' => $i);
            }
            
            for ($i = 10; $i < 60; $i+=5)
            {
                $this->minutesList[] = array('id' => $i, 'nazwa' => $i);
            }
        }
        
        public function addSearchForm ($data = null)
        {
            $id_przewoznik = null;
            $id_dzien      = null;
            
            if (isset($data[self::FORM_TRAVELER_NAME.'_id']))
            {
                $id_przewoznik = $data[self::FORM_TRAVELER_NAME.'_id'];
                $id_dzien = $data[self::FORM_DAY_NAME.'_id'];
            }
            
            $result = $this->addFormPostPre().'<table><tr>';
            $result .= '<td>Przewo¼nik: </td><td>'.$this->controls->AddSelectRandomQuerySVbyId(self::FORM_TRAVELER_NAME, 'id_'.self::FORM_TRAVELER_NAME, '', 'select id, nazwa from przewoznik', $id_przewoznik, self::FORM_TRAVELER_NAME.'_id').'</td>';
            $result .= '<td>Dzieñ: </td><td>'.$this->controls->AddSelectWithData(self::FORM_DAY_NAME, 'id_'.self::FORM_DAY_NAME, '', $this->dayList, $id_dzien, self::FORM_DAY_NAME.'_id').'</td>';
            $result .= '<td></td><td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_SEARCH, 'id_'.self::FORM_DEPARTURES_SEARCH, 'Szukaj', '').'</td>';
            
            $result .= '</tr></table>'.$this->addFormSuf();
            
            return $result;
        }
        
        public function addAddForm ($travelerId, $dayId) 
        {
            $result = $this->addFormPostPre().'<table><tr>';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_DZIEN, self::HIDDEN_ID_DZIEN, $dayId);
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_PRZEWOZNIK, self::HIDDEN_ID_PRZEWOZNIK, $travelerId);
            
            $places = $this->getAllDeparturesList();
            
            $result .= '<td>'.$this->htmlControls->_AddSelect(self::FORM_PLACE_NAME, self::FORM_PLACE_NAME, $places, null, self::HIDDEN_ID_MSC).'</td>';
            
            $result .= '<td>'.$this->dayList[$dayId]['nazwa'].'</td></tr>';
            $result .= '<tr><td>Godzina odjazdu: </td><td>'.
            $this->controls->AddSelectWithData('hour', 'id_hour', '', $this->hourList, null, 'hour_id').':'.
            $this->controls->AddSelectWithData('minute', 'id_minute', '', $this->minutesList, null, 'minute_id').'</td></tr><tr>';
            $result .= '<td>Godzina powrotu: </td><td>'.
            $this->controls->AddSelectWithData('hour_return', 'id_hour_return', '', $this->hourList, null, 'hour_id_return').':'.
            $this->controls->AddSelectWithData('minute_return', 'id_minute_return', '', $this->minutesList, null, 'minute_id_return').'</td></tr><tr>';

            $result .= '<td>Przystanek:</td>';
            $result .= '<td>'.$this->controls->AddTextbox('bus_stop', 'id_bus_stop', '', 120, 120, '').'</td></tr><tr>';
            $result .= '<td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_ADD, 'id_'.self::FORM_DEPARTURES_ADD, 'Dodaj', '', '').'</td>';
            
            $result .= '</tr></table>'.$this->addFormSuf();
            
            return $result;
        }
        
        public function showDepartureList ($travelerId, $dayId) 
        {
            $data = $this->getDeparturesList($travelerId, $dayId);
            $availableData = $this->getAvailableDeparturesList($travelerId, $dayId);
            
            $result = $this->addAddForm($travelerId, $dayId);
            
            if ($data === null)
                return $result.'<br />Brak danych.';
                        
            $result .= $this->addFormPostPre().'<table class="gridTable" border="0" cellspacing="0">';
            $result .= "<tr><th>Miasto</th><th>Odjazd</th><th>Powrót</th><th>Przystanek</th><th colspan=3></th></tr>";
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_KOLUMNA, self::HIDDEN_ID_KOLUMNA, '');
            $count = 0;
            foreach ($data as $row) 
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                $result .= '<tr class="'.$css.'">';
                $result .= '<td>'.$row['msc_odjazd'].'</td>';
                $result .= '<td>'.$row['godzina'].'</td>';
                $result .= '<td>'.$row['godzina_powrotu'].'</td>';
                $result .= '<td>'.$row['przystanek'].'</td>';
                $result .= '<td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_EDIT, 'id_'.self::FORM_DEPARTURES_EDIT, 'Edytuj', JSEvents::ONCLICK.'="'.self::HIDDEN_ID_KOLUMNA.'.value = '.$row['id'].'";').'</td>';
                if ($row['active'] == 't')
                    $result .= '<td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_HIDE, 'id_'.self::FORM_DEPARTURES_HIDE, 'Ukryj', JSEvents::ONCLICK.'="'.self::HIDDEN_ID_KOLUMNA.'.value = '.$row['id'].'";').'</td>';
                else
                    $result .= '<td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_SHOW, 'id_'.self::FORM_DEPARTURES_SHOW, 'Pokaz', JSEvents::ONCLICK.'="'.self::HIDDEN_ID_KOLUMNA.'.value = '.$row['id'].'";').'</td>';
                    
                $result .= '<td>'.$this->controls->AddDeleteSubmit(self::FORM_DEPARTURES_DELETE, 'id_'.self::FORM_DEPARTURES_DELETE, 'Kasuj', self::HIDDEN_ID_KOLUMNA.'.value = '.$row['id'].';', '').'</td>';
                $result .= '</tr>';
            }
            
            $result .= '</table>'.$this->addFormSuf();
            
            //-----------------
            
            
            /*$result .= '<hr /> Dostepne miejsca odjazdu: <hr />';
            
            $result .= $this->addFormPostPre().'<table class="gridTable" border="0" cellspacing="0">';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_UP_KOLUMNA, self::HIDDEN_ID_UP_KOLUMNA, '');
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_DZIEN, self::HIDDEN_ID_DZIEN, $dayId);
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_PRZEWOZNIK, self::HIDDEN_ID_PRZEWOZNIK, $travelerId);
            $count = 0;
            foreach ($availableData as $row) 
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                $result .= '<tr class="'.$css.'">';
                $result .= '<td>'.$row['nazwa'].'</td>';
                $result .= '<td>'.$this->dayList[$dayId]['nazwa'].'</td>';
                $result .= '<td>Godzina: </td><td>'.
                $this->controls->AddSelectWithData('hour'.$row['id'], 'id_hour', '', $this->hourList, null, 'hour_id').':'.
                $this->controls->AddSelectWithData('minute'.$row['id'], 'id_minute', '', $this->minutesList, null, 'minute_id').'</td>';
                $result .= '<td>Przystanek:</td>';
                $result .= '<td>'.$this->controls->AddTextbox('bus_stop'.$row['id'], 'id_bus_stop', '', 30, 30, '').'</td>';
                $result .= '<td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_ADD, 'id_'.self::FORM_DEPARTURES_ADD, 'Dodaj', JSEvents::ONCLICK.'="'.self::HIDDEN_ID_UP_KOLUMNA.'.value = '.$row['id'].'";').'</td>';

                $result .= '</tr>';
            }
            
            $result .= '</table>'.$this->addFormSuf();*/
            
            return $result;
        }
        
        public function addEditColumnForm ($columnId)
        {
            return $this->addAddUpdateForm($this->getColumnInfo($columnId));
        }
        
        public function editColumn ($data)
        {
            $editData = $this->getDepartureListItem($data[self::HIDDEN_ID_KOLUMNA]);
            if (!$editData)
                return 'B³±d ! Brak danych.';
            
            $result = $this->addFormPostPre().'<table>';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_UP_KOLUMNA, self::HIDDEN_ID_UP_KOLUMNA, $data[self::HIDDEN_ID_KOLUMNA]);
            $result .= '<tr><td>Przewoznik:</td>';
            $result .= '<td>'.$editData['przewoznik'].'</td></tr>';
            $result .= '<tr><td>Miejsce odjazdu:</td>';
            $result .= '<td>'.$editData['msc_odjazd'].'</td></tr>';
            list ($hour, $minute) = explode(':', $editData['godzina']);
            list ($hour_return, $minute_return) = explode(':', $editData['godzina_powrotu']);
            $result .= '<tr><td>Godzina odjazdu: </td><td>'.
            $this->controls->AddSelectWithData('hour', 'id_hour', '', $this->hourList, $hour, 'hour_id').':'.
            $this->controls->AddSelectWithData('minute', 'id_minute', '', $this->minutesList, $minute, 'minute_id').'</td></tr>';
            $result .= '<tr><td>Godzina powrotu: </td><td>'.
            $this->controls->AddSelectWithData('hour_return', 'id_hour_return', '', $this->hourList, $hour_return, 'hour_id_return').':'.
            $this->controls->AddSelectWithData('minute_return', 'id_minute_return', '', $this->minutesList, $minute_return, 'minute_id_return').'</td></tr>';
            $result .= '<tr><td>Przystanek:</td>';
            $result .= '<td>'.$this->controls->AddTextbox('bus_stop', 'id_bus_stop', $editData['przystanek'], 120, 120, '').'</td></tr>';
            $result .= '<tr><td>'.$this->controls->AddSubmit(self::FORM_DEPARTURES_UPDATE, 'id_'.self::FORM_DEPARTURES_UPDATE, 'Aktualizuj', '').'</td>';
            $result .= '</tr></table>'.$this->addFormSuf();
            
            return $result;
        }
        
        public function updateColumn ($data)
        {
            $hour = $data['hour'].':'.$data['minute'];
            $hour_return = $data['hour_return'].':'.$data['minute_return'];
            $bus_stop = addslashes($data['bus_stop']);
            $id = (int)$data[self::HIDDEN_ID_UP_KOLUMNA];
            
            $this->controls->dalObj->pgQuery('update rozklad_jazdy set godzina = \''.$hour.'\', godzina_powrotu = \''.$hour_return.'\', przystanek = \''.$bus_stop.'\' where id = '.$id);
        }
        
        public function hideColumn ($data, $hide = true)
        {
            $id = (int)$data[self::HIDDEN_ID_KOLUMNA];
            if ($hide)
                $this->controls->dalObj->pgQuery('update rozklad_jazdy set active = false where id = '.$id);
            else
                $this->controls->dalObj->pgQuery('update rozklad_jazdy set active = true where id = '.$id);
        }
        
        public function deleteColumn ($data)
        {
            return $this->controls->dalObj->pgQuery('delete from rozklad_jazdy where rozklad_jazdy.id = '.$data[self::HIDDEN_ID_KOLUMNA]);
        }
        
        public function addColumn($data)
        { 
            $id_msc = (int)$data[self::HIDDEN_ID_MSC];
            //var_dump(isset($data[self::HIDDEN_ID_DZIEN]), $data[self::HIDDEN_ID_DZIEN]);
            if (isset($data[self::HIDDEN_ID_DZIEN]) && $data[self::HIDDEN_ID_PRZEWOZNIK])
            {
                try {
                    
                    $result = $this->controls->dalObj->pgQuery('insert into rozklad_jazdy (id_przewoznik,dzien,id_msc_odjazdu,godzina,godzina_powrotu,przystanek) values 
                    ('.$data[self::HIDDEN_ID_PRZEWOZNIK].','.$data[self::HIDDEN_ID_DZIEN].','.$id_msc.',\''.$data['hour'].':'.$data['minute'].'\',\''.$data['hour_return'].':'.$data['minute_return'].'\',\''.$data['bus_stop'].'\')');
                } catch (DBQueryErrorException $e) {
                    
                    $result = false;
                }
                
                if (!$result)
                    return 'Operacja nie powiod³a siê, najpewniej przystanek juz zdefiniowany.';
            }
            
            return;
        }    

        private function getDepartureListItem ($itemId)
        {
            $itemId = (int)$itemId;
            $data = $this->controls->dalObj->PobierzDane('select rozklad_jazdy.id, msc_odjazdu.nazwa as msc_odjazd, rozklad_jazdy.godzina, rozklad_jazdy.godzina_powrotu, rozklad_jazdy.przystanek, przewoznik.nazwa as przewoznik
                from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id
                join przewoznik on rozklad_jazdy.id_przewoznik = przewoznik.id
                where rozklad_jazdy.id = '.$itemId);
                
            return sizeof($data) ? array_shift($data) : null;
        }
        
        private function getDeparturesList ($travelerId, $dayId)
        {
            return $this->controls->dalObj->PobierzDane(
            'select rozklad_jazdy.id, msc_odjazdu.nazwa as msc_odjazd, rozklad_jazdy.godzina, rozklad_jazdy.godzina_powrotu, rozklad_jazdy.przystanek, rozklad_jazdy.active 
                from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id 
                where rozklad_jazdy.id_przewoznik = '.$travelerId.' and rozklad_jazdy.dzien = '.$dayId.' order by godzina', 
            $ilosc_wierszy);
        }
        
        private function getAvailableDeparturesList ($travelerId, $dayId) 
        {
            //nazwa not in (\'\', \'\') and and
            return $this->controls->dalObj->PobierzDane(
            'select id, nazwa from msc_odjazdu where id not in (select id_msc_odjazdu from rozklad_jazdy where id_przewoznik = '.$travelerId.' and dzien = '.$dayId.' order by nazwa asc)', 
            $ilosc_wierszy);
        }
        
        private function getAllDeparturesList () 
        {
            return $this->controls->dalObj->PobierzDane('select id, nazwa from msc_odjazdu order by nazwa', $ilosc_wierszy);
        }
    }
    
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();
////////////-------------------------------
    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    include("../bll/definicjeKlas.php");
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {

        if (isset($_SESSION['zmiana_uprawnien']) && ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['rozklad_jazdy'])))
        {
            $output = new DepartureListView();
                                                                       
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_ADD]))
            {
                echo $output->addColumn($_POST);
            }
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_DELETE]))
            {
                $output->deleteColumn($_POST);
            }
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_UPDATE]))
            {
                $output->updateColumn($_POST);
            }
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_HIDE]))
            {
                $output->hideColumn($_POST, true);
            }
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_SHOW]))
            {
                $output->hideColumn($_POST, false);
            }
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_EDIT]))
            {
                //mozna dodac util form insert edit 
                echo $output->editColumn($_POST);
                //echo $output->addEditColumnForm($_POST[AddColumnsView::HIDDEN_ID_KOLUMNA]);
            }
            
            //dodatkowe pola dla osob
            echo $output->addSearchForm($_POST);
            
            if (isset($_POST[DepartureListView::FORM_DEPARTURES_SEARCH]))
            {
                echo $output->showDepartureList($_POST[DepartureListView::FORM_TRAVELER_NAME.'_id'], $_POST[DepartureListView::FORM_DAY_NAME.'_id']);
            }
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
