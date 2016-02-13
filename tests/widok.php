<?php
#TODO: lista do bartusia z rozliczeniem, wyjazdy per przewoznik, formularz do masowych update ow, wsadzenie uprawnien.
require_once '../conf.php';
require_once '../bll/queries.php';
require_once 'bll/BLLViews.php';
require_once '../bll/FileManager.php';
require_once '../ui/UtilsUI.php';
require_once '../ui/HelpersUI.php';
require_once 'Spreadsheet/Excel/Writer.php';
require_once 'bll/BLLBalances.php';

class DeparturesView extends View
{
    const VIEW_ABFAHRT         = 'Abfahrt';
    const VIEW_WYJAZD          = 'Grupa na wyjazd';
    const VIEW_POWROT          = 'Grupa na powrót';

    const SESSION_KWERENDA_SQL          = 'kwerenda_sql';
    const SESSION_SZUKAJ_SQL            = 'szukaj_sql';
    const SESSION_EDYCJA_MASOWA         = 'edycja_masowa';
    const SESSION_WAKATY_UMOWIENI       = 'wakaty_umowieni';
    const SESSION_WAKATY_ZAINTERESOWANI = 'wakaty_zainteresowani';

    const SESSION_OSOBY                 = 'osoby';

    const FORM_WIDOK       = 'widok';
    const FORM_ZMIEN_WIDOK = 'zmien_widok';
    const FORM_RODZAJ      = 'rodzaj';

    const FORM_GENERUJ        = 'generateList';
    const FORM_WYSLIJ_ANKIETY = 'sendQuestionaire';

    //status poki co unused
    const FORM_ZMIEN_STATUS_AKT = 'zmien_a';
    const FORM_ZMIEN_STATUS_PAS = 'zmien_p';

    const FORM_ZMIEN_DATA       = 'zmien_d';

    const DATE_DEPARTURE        = 'data_wyjazd';
    const DATE_RETURN           = 'data_powrot';

    const SELECT_NO_VALUE = '--------';

    const SQL_GET_VIEW_NAME_PATTERN = 'select nazwa from widoki where id = %d;';
    const SQL_GET_VIEW_DATA_PATTERN = 'select gdzie, co, nazwa from widoki_edit where id_widoku = %d;';

    const CACHE_CARRIERS        = 'lista_przewoznikow';

    private $utilsUI, $carriers;

    private $viewsHeaders = array(

    3 => array('nazwa' => 'Abfahrt', 'nag' => array(
                'id'		        => 'Id',
                'imie'		        => 'Imiê',
                'nazwisko'	        => 'Nazwisko',
                'data_urodzenia'	=> 'Data urodzenia',
                'data_wyjazdu'		=> 'Data wyjazdu',
                'ilosc_tyg'			=> 'Iloœæ tygodni',
                'nazwa'			    => 'Klient',
                'biuro'			    => 'Biuro',
                'imie_nazwisko'		=> 'Konsultant',
                'id_wakat'		    => 'Pasywny?',
                'msc_odjazdu'	    => 'Miejsce wsiadania',
                'przewoznik'	    => 'PrzewoŸnik',
                'dodatkowe_osoby'	=> 'Dodatkowe osoby',
    )),
    2 => array('nazwa' => 'Grupa na powrót', 'nag' => array(
                'id'		        => 'Id',
                'imie'		        => 'Imiê',
                'nazwisko'	        => 'Nazwisko',
                'data_urodzenia'	=> 'Data urodzenia',
                'komorka'			=> 'Telefon',
                'data_powrotu'		=> 'Data powrotu',
                'msc_odjazdu'		=> 'Miejsce odjazdu',
                'biuro'		        => 'Biuro',
                'id_przewoznik'		=> 'PrzewoŸnik',
    )),
    1 => array('nazwa' => 'Grupa na wyjazd', 'nag' => array(
                'id'		        => 'Id',
                'imie'		        => 'Imiê',
                'nazwisko'	        => 'Nazwisko',
                'data_urodzenia'	=> 'Data urodzenia',
                'komorka'			=> 'Telefon',
                'data_wyjazdu'		=> 'Data wyjazdu',
                'msc_odjazdu'		=> 'Miejsce odjazdu',
                'biuro'		        => 'Biuro',
                'id_przewoznik'		=> 'PrzewoŸnik',
    )),
    );

    private $suffixHeaders = array(
    // grupa na wyj
    1 => array(
    // arnold
    2 => array('Miejsce docelowe', 'Osoba kontaktowa')
    )
    );

    public function __construct ()
    {
        $this->actionList = array(
        self::FORM_ZMIEN_WIDOK      => User::PRIV_EDYCJA_REKORDU,
        self::FORM_ZMIEN_DATA       => User::PRIV_EDYCJA_GRUPOWA,
        );

        parent::__construct();
        unset($_SESSION[DeparturesView::SESSION_KWERENDA_SQL]);
        unset($_SESSION[DeparturesView::SESSION_SZUKAJ_SQL]);
        unset($_SESSION[DeparturesView::SESSION_EDYCJA_MASOWA]);
        unset($_SESSION[DeparturesView::SESSION_WAKATY_UMOWIENI]);
        unset($_SESSION[DeparturesView::SESSION_WAKATY_ZAINTERESOWANI]);

        $this->utilsUI = new UtilsUI('', 'id_os');
        $this->getCarriers();
    }

    public function setView($data)
    {
        if (isset($data[DeparturesView::FORM_WIDOK]))
        {
            if ($data[DeparturesView::FORM_WIDOK] != DeparturesView::SELECT_NO_VALUE)
            $_SESSION[DeparturesView::FORM_WIDOK] = $data[DeparturesView::FORM_WIDOK];
            else
            unset($_SESSION[DeparturesView::FORM_WIDOK]);
        }

        if (isset($data['h_os']))
        {
            $_SESSION[DeparturesView::SESSION_OSOBY] = $data['h_os'];
        }
    }

    /**
     * @desc Wstawienie formularza zmiany kryteriow poszukiwania
     */
    public function addEditViewForm($data)
    {
        if (isset ($_SESSION[DeparturesView::FORM_WIDOK]))
        {
            $nazwa = $this->controls->dalObj->PobierzDane(sprintf(DeparturesView::SQL_GET_VIEW_NAME_PATTERN, (int)$_SESSION[DeparturesView::FORM_WIDOK]));
            $result = '<table align="center"><tr><td>'.$nazwa[0]['nazwa'].'</td></tr></table>';
            $daneWidok = $this->controls->dalObj->PobierzDane(sprintf(DeparturesView::SQL_GET_VIEW_DATA_PATTERN, (int)$_SESSION[DeparturesView::FORM_WIDOK]));

            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']); //'<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            $result .= '<table align="center">';
            foreach ($daneWidok as $row)
            {
                $result .= '<tr><td>'.$this->controls->AddTextbox($row['gdzie'], $row['gdzie'], $row['nazwa'], 50, 30, 'readonly="readonly"').'</td><td>';
                if (strtolower(substr($row['gdzie'], 0, 4)) == "data")
                $result .= $this->controls->AddDateRangebox($row['gdzie'], $row['gdzie'], $row['co'], 21, 25);
                else
                $result .= $this->controls->AddSeekTextbox($row['gdzie'], $row['co'], $row['gdzie'], 30, 30);
                $result .= '</td></tr>';
            }
            $result .= '</table><table align="center"><tr><td>'.$this->controls->AddSubmit(DeparturesView::FORM_ZMIEN_WIDOK, DeparturesView::FORM_ZMIEN_WIDOK, 'Zmieñ', '').'</td></tr></table></form>';
        }
        else
        $result = 'Brak kwerendy.';

        return $result;
    }
    /**
     * @desc Zmiana kryteriow wyszukiwania w bazie i przeladowanie strony
     */
    public function updateEditForm($data)
    {
        $daneWidok = $this->controls->dalObj->PobierzDane(sprintf(DeparturesView::SQL_GET_VIEW_DATA_PATTERN, (int)$_SESSION[DeparturesView::FORM_WIDOK]));
        foreach ($daneWidok as $row)
        {
            $source = $data[$row['gdzie']];
            if (strtolower(substr($row['gdzie'], 0, 4)) == "data")
            {
                if (trim($source) == "")
                $source = date("Y-m-d");
            }
            else
            {
                if (trim($source) == "")
                $source = "%";
            }
            $this->controls->dalObj->pgQuery("update widoki_edit set co = '".$source."' where id_widoku = '".$_SESSION['widok']."' and nazwa = '".$row['nazwa']."';");
        }
        $result = '<script>parent.frames[0].document.widok_forma.submit();</script>';

        return $result;
    }
    /**
     * @desc Masowa zmiana statusow osob spelniajacych kryteria szukania
     */
    public function setStatus ($status, $data)
    {
        $tab_id = '('.substr(str_replace('|', ',', $_SESSION[DeparturesView::SESSION_OSOBY]), 0, strlen($_SESSION[DeparturesView::SESSION_OSOBY]) - 1).')';

        if (strlen($tab_id) > 2)
        {
            $row = $this->controls->dalObj->PobierzDane(stripslashes($_SESSION['widok_sql']));

            $zapytanie_update = "update zatrudnienie set id_status = ".$status." where id_osoba in $tab_id and data_wyjazdu = '".$row[0]['data_wyjazdu']."';";
            $zapytanie_update .= "update stat set id_status = ".$status." where id in $tab_id;";
            $id = explode('|', $_SESSION[DeparturesView::SESSION_OSOBY]);
            $czas = date('Y-m-d H:i:s');

            for ($i = 0; $i < count($id) - 1; $i++)
            {
                $zapytanie_update .= "insert into status_historia values ('".$id[$i]."', ".$_SESSION[UZYTKOWNIK_ID].", '".$czas."', ".$status.");";
            }

            $this->controls->dalObj->pgQuery($zapytanie_update);
        }

        return '<script>wroc();</script>';
    }
    /**
     * @desc Masowa zmiana daty wyjazdu ...
     */
    public function setEmploymentDate($data)
    {
        //todo: walidacja m.in. dat po stronie php ...
        if (isset($data['h_osb']))
        {
            $osobyListaId = explode('|', $data['h_osb']);
            array_pop($osobyListaId);
            foreach ($osobyListaId as &$osobaId)
            $osobaId = (int)$osobaId;

            $tab_id = '('.implode(',', $osobyListaId).')';
        }
        else
        return 'B³±d struktury formularza, nie mo¿na kontynuowaæ.<br /><a href="'.$_SERVER['REQUEST_URI'].'">Powrót</a>';

        $viewQuery = stripslashes($_SESSION['widok_sql']);

        if (strpos($viewQuery, 'data_wyjazdu between') || strpos($viewQuery, 'data_powrotu between'))
        return 'Operacja nie mo¿e byæ wykonana. Wygenerowano grupê dla przedzia³u dat.';

        $row = $this->controls->dalObj->PobierzDane($viewQuery);
        $curParams = array_shift($row);
        $zapytanie_update = '';

        if (isset($data[DeparturesView::DATE_DEPARTURE]))
        {
            if (!$this->CheckFutureDate($data[DeparturesView::DATE_DEPARTURE], $result))
            return $result.'<br /><a href="'.$_SERVER['REQUEST_URI'].'">Powrót</a>';

            if (strlen($tab_id) > 2)
            {
                $zapytanie_update = "update zatrudnienie set data_wyjazdu = '".$data[DeparturesView::DATE_DEPARTURE]."'
                    where id_osoba in $tab_id and data_wyjazdu = '".$curParams['data_wyjazdu']."' 
                    and data_powrotu > '".$data[DeparturesView::DATE_DEPARTURE]."' 
                    and (
                    	select count(*) from zatrudnienie z1 
                    	where z1.id_osoba = zatrudnienie.id_osoba and z1.data_wyjazdu != '".$curParams['data_wyjazdu']."'
                    	and z1.data_wyjazdu <= '".$data[DeparturesView::DATE_DEPARTURE]."' and data_powrotu >= '".$data[DeparturesView::DATE_DEPARTURE]."'
                    	) = 0;";
                 
                $zapytanie_update .= "update dane_osobowe set data = '".$data[DeparturesView::DATE_DEPARTURE]."'
                    where id in $tab_id and (
                    	select count(*) from zatrudnienie z1 
                    	where z1.id_osoba = dane_osobowe.id and z1.data_wyjazdu != '".$curParams['data_wyjazdu']."'
                    	and z1.data_wyjazdu <= '".$data[DeparturesView::DATE_DEPARTURE]."' and data_powrotu >= '".$data[DeparturesView::DATE_DEPARTURE]."'
                    	) = 0;";
            }
            else
            {
                return '¯eby to mia³o sens warto zaznaczyæ jak±¶ osobê. <br /><a href="'.$_SERVER['REQUEST_URI'].'">Powrót</a>';    //Wpisa³e¶/a¶ z³± datê
            }
        }
        else if (isset($data[DeparturesView::DATE_RETURN]))
        {
            if (!$this->CheckFutureDate($data[DeparturesView::DATE_RETURN], $result))
            return $result;

            if (strlen($tab_id) > 2)
            {
                $zapytanie_update = "update zatrudnienie set data_powrotu = '".$data[DeparturesView::DATE_RETURN]."'
                    where id_osoba in $tab_id and data_powrotu = '".$curParams['data_powrotu']."' 
                    and data_wyjazdu < '".$data[DeparturesView::DATE_RETURN]."'
                    and (
                    	select count(*) from zatrudnienie z1 
                    	where z1.id_osoba = zatrudnienie.id_osoba and z1.data_powrotu != '".$curParams['data_powrotu']."'
                    	and z1.data_wyjazdu <= '".$data[DeparturesView::DATE_RETURN]."' and data_powrotu >= '".$data[DeparturesView::DATE_RETURN]."'
                    	) = 0;";
            }
            else
            {
                return '¯eby to mia³o sens warto zaznaczyæ jak±¶ osobê. <br /><a href="'.$_SERVER['REQUEST_URI'].'">Powrót</a>';
            }
        }

        if (strlen($zapytanie_update) > 0)
        $this->controls->dalObj->pgQuery($zapytanie_update);

        $result = '<script>wroc();</script>';

        return $result;
    }
    /**
     * @desc Wyswietlenie listy danych dla danego widoku (grup na wyjazd lub abf)
     */
    public function viewList ($viewId)
    {
        $headerInfo = $this->getViewHeaders($viewId);
        $name = $headerInfo['nazwa'];
        $headers = $headerInfo['nag'];
        $data = $this->getViewData($viewId, $name);

        $result = $this->displayDepartureList($data, $headers, $name, sizeof($data)); // z nazwa pod te durne ify

        //additional forms
        $result .= $this->actionsForms($name);

        return $result;
    }
    /**
     * @desc Wygenerowanie i wyslanie listy w xls
     */
    public function getWorkSheet ($viewId, $carrierId = null, &$errMsg = '') //opcjonalny parametr id przewoznika dojdzie
    {
        $headerInfo = $this->getViewHeaders($viewId);
        $name = $headerInfo['nazwa'];
        $headers = $headerInfo['nag'];
        $data = $this->getViewData($viewId, $name, null, $carrierId);

        $bllViews = new BLLViews();
        $discName = $bllViews->getXlsForView($viewId, $data, $headers, $carrierId, $this->getSuffixHeaders($viewId, $carrierId));

        if ($discName) {
            $bllViews->Output($name);
        } else
        return false;
    }
    /**
     * @desc Sprawdzenie czy data jest w przyszlosci oraz czy jest poprawna
     */

    protected function CheckFutureDate ($date, &$errMsg = '')
    {
        if (!$date || strpos($date, '-') === false)
        {
            $errMsg = 'Data jest nieprawid³owa.';
            return false;
        }

        list ($year, $month, $day) = explode('-', $date);
        if (false === checkdate($month, $day, $year))
        {
            $errMsg = 'Data jest nieprawid³owa.';
            return false;
        }

        if ($date < date('Y-m-d'))
        {
            $errMsg = 'Data w przesz³o¶ci, b³±d.';
            return false;
        }

        return true;
    }
    /**
     * @desc Metoda pomocnicza - wytworzenie html listy wynikowej
     */
    private function displayDepartureList ($data, $headers, $name, $count)
    {
        $result = '<form action="../edit/przetwarzaj_dane_osobowe.php" method="GET">'.
        $this->controls->AddHidden('id_os', 'id_os', '')
        .'<table class="gridTable" cellspacing="0">';
        $result .= valControl::_RowsCount($count);
        //dodanie naglowkow
        $result .= $this->utilsUI->buttonsNag();
        $result .= HelpersUI::addTableRow($headers, true);

        $count = 0;
        if (is_array($data))
        foreach ($data as $key => $row)
        {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            $personId = $row['id'];
            $result .= '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
            $result .= $this->utilsUI->buttons($personId);

            $result .= HelpersUI::addTableRow($row, false, $headers);

            $result .= '</tr>';
        }

        $result .= '</table></form>';

        return $result;
    }
    /**
     * @desc Metoda pomocnicza: html opcji pod lista
     */
    private function actionsForms ($name)
    {
        $result = '<div>'.$this->addFormPostPre($_SERVER['REQUEST_URI']); //<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        $inputName = 'Generuj '.$name;

        if ($name != self::VIEW_ABFAHRT)
        {
            //kombo przewoznikow - wziac dane z cache - przebudowac cache najlepiej tu miejscu ...
            $result .= $this->controls->AddSelectRandomQuery('przewoznik', 'id_przewoznik', '', 'select id, nazwa from przewoznik order by nazwa asc;', null, 'przewoznik_id');

        }

        if ($name == self::VIEW_WYJAZD)
        {
            $result .= $this->controls->AddSubmit('rozliczenie', 'id_rozliczenie', 'Generuj rozliczenie', JSEvents::ONCLICK.'="spr_os(2);"');
        }

        $result .= $this->controls->AddSubmit(self::FORM_GENERUJ, self::FORM_GENERUJ, $inputName, '');

        if ($name == self::VIEW_WYJAZD)
        {
            //ten hiden do przewoznika lub masowej edycji
            $result .= $this->controls->AddHidden('h_osb', 'h_osb', '');
            $result .= '<div style="margin-top: 10px;">Nowa data wyjazdu: '. $this->controls->AddDateboxFuture(self::DATE_DEPARTURE, self::DATE_DEPARTURE, '', 10, 10);
            $result .= $this->controls->AddSubmit(self::FORM_ZMIEN_DATA, self::FORM_ZMIEN_DATA, 'Zmieñ datê wyjazdu', JSEvents::ONCLICK.'="spr_os(2);"').'</div>';
        }

        if ($name == self::VIEW_POWROT)
        {
            //ten hiden do przewoznika lub masowej edycji
            $result .= $this->controls->AddHidden('h_osb', 'h_osb', '');
            $result .= '<div style="margin-top: 10px;">Nowa data powrotu: '.$this->controls->AddDateboxFuture(self::DATE_RETURN, self::DATE_RETURN, '', 10, 10);
            $result .= $this->controls->AddSubmit(self::FORM_ZMIEN_DATA, self::FORM_ZMIEN_DATA, 'Zmieñ datê powrotu', JSEvents::ONCLICK.'="spr_os(2);"').'</div>';
        }

        $result .= '</form>';
        if ($name == self::VIEW_ABFAHRT)
        {
            $result .= $this->addFormPostPre('wyslij_ankiety.php'); //'<form method="POST" action="wyslij_ankiety.php">';
            $result .= $this->controls->AddHidden('id_h_lista_osoby_ankieta', 'h_lista_osoby_ankieta', '');
            $result .= $this->controls->AddHidden('h_os', 'h_os', '');
            $result .= $this->controls->AddSubmit(self::FORM_WYSLIJ_ANKIETY, self::FORM_WYSLIJ_ANKIETY, 'Wy¶lij ankiety', JSEvents::ONCLICK.'="spr_os(2);"').'';
            $result .= '</form>';
        }

        $result .= '</div>';

        return $result;
    }
    /**
     * @desc Metoda pomocnicza - wyznaczenie naglowkow kolumn danych do widoku
     */
    private function getViewHeaders ($viewId)
    {
        return $this->viewsHeaders[$viewId];
    }

    private function getSuffixHeaders($viewId, $carrierId)
    {
        if (isset($this->suffixHeaders[$viewId], $this->suffixHeaders[$viewId][$carrierId]))
        {
            return $this->suffixHeaders[$viewId][$carrierId];
        }

        return array();
    }

    /**
     * get footers.
     * for view id 1 (departure group) we are interested in carrier id then; the map will have footers for each of them
     * for view id 3 (incoming group, abfahrt) we have to recognize firm id belonging; for each, different footer is required.
     * @param $viewId
     * the entire footer set will be returned, of course.
     */
    private function getViewFooters ($viewId)
    {
        $footers = array(
        1 => array( // bartus, arnold
        1 => array('Zawarte na powy¿szej liœcie dane osobowe firma BARTUŒ Sp. z o.o. z siedzib¹ ul.Magnolii 16, 44-152 Gliwice',
                    'otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje, i¿ s¹ one przetwarzane zgodnie z',
                    'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.'),
        2 => array('Zawarte na powy¿szej liœcie dane osobowe firma Przedsiêbiorstwo Transportowe Arnold z siedzib¹ ul. Budowlanych 6,',
                    '45-205 Opole otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje, i¿ s¹ one przetwarzane zgodnie', 
                    'z wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.')
        ),
        2 => array( // bartus, arnold
        1 => array('Zawarte na powy¿szej liœcie dane osobowe firma BARTUŒ Sp. z o.o. z siedzib¹ ul.Magnolii 16, 44-152 Gliwice',
                    'otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje, i¿ s¹ one przetwarzane zgodnie z', 
                    'wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.'),
        2 => array('Zawarte na powy¿szej liœcie dane osobowe firma Przedsiêbiorstwo Transportowe Arnold z siedzib¹ ul. Budowlanych 6,',
                    '45-205 Opole otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje, i¿ s¹ one przetwarzane zgodnie', 
                    'z wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.')
        ),
        3 =>
        array(
        //    1 => array('Zawarte na powy¿szej liœcie dane osobowe firma E&A uitzendbureau z siedzib¹', 'Emma Goldmanweg 8h, 5032 MN TILBURG otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje,', 'i¿ s¹ one przetwarzane zgodnie z wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.'),
        //    2 => array('Zawarte na powy¿szej liœcie dane osobowe firma T-interim z siedzib¹', 'Stationsstraat 120, 2800 Mechelen otrzyma³a na podstawie obowi¹zuj¹cej umowy o wspó³pracy i gwarantuje,', 'i¿ s¹ one przetwarzane zgodnie z wymogami Ustawy o Ochronie Danych Osobowych z dnia 29.08.1997 r. Dz. U. 1997 Nr 133 poz. 883 z póŸn. zm.')
        )
        );

        return isset($footers[$viewId]) ? $footers[$viewId] : null;
    }

    /**
     * @desc Metoda pomocnicza: pobranie danych do widoku
     */
    private function getViewData($viewId, $name, $idList = null, $carrierId = null) //lista id do ankiet i rozliczen, moze nie byc tu konieczna
    {
        $sql = $this->controls->dalObj->PobierzDane('select sql from widoki where id = '.$viewId);
        $where = $this->controls->dalObj->PobierzDane('select gdzie, co from widoki_edit where id_widoku = '.$viewId);

        $query = $sql[0]['sql'];

        if (sizeof($where))
        {
            $query .= ' where ';
            foreach ($where as $row)
            {
                if (strtolower(substr($row['gdzie'], 0, 4)) == 'data')
                $query = $this->CreateQueryDate($row['co'], $query, $row['gdzie']);
                else
                $query = $this->CreateQueryOR($row['co'], $query, $row['gdzie']);
            }
            if ($carrierId)
            $query .= ' and id_przewoznik = '.$carrierId;
        }

        $_SESSION['widok_sql'] = isset($_SESSION['widok_sql']) ? $_SESSION['widok_sql'] : null;
        $sesSort = trim(substr($_SESSION['widok_sql'], strpos($_SESSION['widok_sql'], 'order by')));
        //fuck !! sortowanie poprzez sort php dziala elastycznie - wykonuje zapytanie z sesji i zczytuje kolumny, prezentuje je do wyboru
        //jesli jednak user posortuje jeden widok wzgledem jego kolumn 2 widok, ktory ich nie ma sie wywali, chyba ze urwiemy sort
        //stad strpos zeby sprawdzic czy sortujemy to samo zapytanie - jak nie, sortowanie moze byc wadliwe, ucinamy

        $sessionWherePos = strpos($_SESSION['widok_sql'], 'where');
        $queryWherePos = strpos($query, 'where');

        $sessionFrom = substr($_SESSION['widok_sql'], 0, $sessionWherePos);
        $queryFrom = substr($query, 0, $queryWherePos);

        if ($sesSort && $sessionFrom == $queryFrom)
        $query .= ' '.$sesSort;
        else
        $query .= ' order by id asc';

        $_SESSION['widok_sql'] = $query;

        $data = $this->controls->dalObj->PobierzDane($query);
        if ($name == self::VIEW_ABFAHRT)
        {
            if (is_array($data))
            foreach ($data as &$row)
            {
                if (isset($row['id_wakat']))
                {
                    if ($row['id_wakat'] == '1')
                    $row['id_wakat'] = 'tak';
                    else
                    $row['id_wakat'] = 'nie';
                }

                //pobranie dodatkowych osob
                $addPeople = $this->controls->dalObj->PobierzDane('select id_osoby_dod from dodatkowe_osoby where id = '.$row['id'].' and id_osoby_dod in (select id from abfahrt);');
                $people = array();
                if (sizeof($addPeople))
                {
                    foreach ($addPeople as $person)
                    {
                        $people[] = $person['id_osoby_dod'];
                    }
                    $row['dodatkowe_osoby'] = implode(',', $people);
                }
                else
                {
                    $row['dodatkowe_osoby'] = '-----------';
                }
            }
        }
        else
        {
            if (is_array($data))
            foreach ($data as &$row)
            if (isset($row['id_przewoznik']))
            {
                $row['id_przewoznik'] = $this->carriers[$row['id_przewoznik']];
            }
        }

        return $data;
    }

    /**
     * travelers settlement xls
     * @param $peopleList
     * @param $viewId
     * @param $carrierId
     * @param $errMsg
     */
    public function getSettlementWorkSheet ($peopleList, $viewId, $carrierId, &$errMsg = '')
    {
        if (strlen($peopleList) > 0)
        $tab_id = '('.substr(str_replace('|', ',', $peopleList), 0, strlen($peopleList) - 1).')';
        else
        {
            $errMsg = 'Nie wybrano osób. ';
            return false;
        }

        //todo: get and check name
        $headerInfo = $this->getViewHeaders($viewId);

        $dateCriteriaQuery = "select co from widoki_edit where id_widoku = '".$viewId."' and gdzie like 'data%';";
        $dateCriteria = $this->controls->dalObj->PobierzDane($dateCriteriaQuery);
        if (!isset($dateCriteria[0]))
        {
            $errMsg = 'B³¹d pobierania kryteriów pobrania rozliczeñ. Dziwne.';
            return false;
        }

        $tab_data = explode(' ', $dateCriteria[0]['co']);
        if (count($tab_data) == 1)
        {
            $query_data = "select nazwisko, imie, data_urodzenia, data_wyjazdu, biuro, cena from rozliczenie where data_wyjazdu = '".$tab_data[0]."' and id_przewoznik = ".$carrierId." and id in $tab_id;";
        }
        else if (count($tab_data) == 2)
        {
            $query_data = "select nazwisko, imie, data_urodzenia, data_wyjazdu, biuro, cena from rozliczenie where data_wyjazdu between '".$tab_data[0]."' and '".$tab_data[1]."' and id_przewoznik = ".$carrierId." and id in $tab_id;";
        }

        $data = $this->controls->dalObj->PobierzDane($query_data);
        $sheetName = 'Rozliczenie '.$this->carriers[$carrierId].'.xls';

        $nag = array('Nazwisko', 'Imiê', 'Data urodzenia', 'Data wyjazdu', 'Biuro', 'Cena biletu');
        $bllViews = new BLLViews();
        $discName = $bllViews->getXlsForData($data, $nag, 'rozliczenie');

        if(!$discName)
        return $discName;

        $bllViews->Output($sheetName);
    }

    //poprawic ta dramatyczna logike
    private function CreateQueryDate($matrixDate, $query, $where)
    {
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $matrixDate) || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\ [0-9]{4}-[0-9]{2}-[0-9]{2}$/', $matrixDate))
        $tab_data = explode(" ", $matrixDate);
        else
        $tab_data[0] = date('Y-m-d');

        if (count($tab_data) == 1)
        {
            if (substr($query, strlen($query) - 6, strlen($query)) == "where ")
            {
                $query .= " (".$where." = '".trim($tab_data[0])."')";
            }
            else
            {
                $query .= " and (".$where." = '".trim($tab_data[0])."')";
            }
        }
        else if (count($tab_data) == 2)
        {
            if (substr($query, strlen($query) - 6, strlen($query)) == "where ")
            {
                $query .= " (".$where." between '".trim($tab_data[0])."' and '".trim($tab_data[1])."') ";
            }
            else
            {
                $query .= " and (".$where." between '".trim($tab_data[0])."' and '".trim($tab_data[1])."') ";
            }
        }
        return $query;
    }
    //poprawic
    private function CreateQueryOR($matrixData, $query, $where)
    {
        $data = explode(",", $matrixData);
        $tmp = substr($query, strlen($query) - 6, strlen($query));
        if (substr($query, strlen($query) - 6, strlen($query)) == "where ")
        {
            $query .= "(";
            for ($i = 0; $i < count($data); $i++)
            {
                $query .= " lower(".$where.") like lower('".trim($data[$i])."') or ";
            }
            $query = substr($query, 0, strlen($query) - 3);
            $query .= ")";
        }
        else
        {
            $query .= " and (";
            for ($i = 0; $i < count($data); $i++)
            {
                $query .= " lower(".$where.") like lower('".trim($data[$i])."') or ";
            }
            $query = substr($query, 0, strlen($query) - 3);
            $query .= ") ";
        }
        return $query;
    }

    private function getCarriers ()
    {
        if ($this->carriers)
        {
            return $this->carriers;
        }

        $carriers = PermanentCache::get(self::CACHE_CARRIERS);
        if (!$carriers)
        {
            $data = $this->controls->dalObj->PobierzDane('select id,nazwa from przewoznik order by nazwa asc');
            foreach ($data as $row)
            {
                $result[$row['id']] = $row['nazwa'];
            }
            PermanentCache::set(self::CACHE_CARRIERS, $result);
            $this->carriers = $result;
        }
        else
        {
            $this->carriers = $carriers;
        }

        return $this->carriers;
    }
}

$balances = new BLLBalances();

$balances->getActivesXls('2013-06-19');
$balances->Output('test');
