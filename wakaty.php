<?php
    session_start();
    
	require_once 'conf.php';
	require_once 'dal/DALWakaty.php';
	require_once 'bll/BLLZatrudnienie.php';
    
	// TODO alter the form in top
	
    class VacatsView extends View
    {
        const SESSION_KWERENDA_SQL          = 'kwerenda_sql';
        const SESSION_SZUKAJ_SQL            = 'szukaj_sql';
        const SESSION_EDYCJA_MASOWA         = 'edycja_masowa';
        const SESSION_WIDOK_SQL             = 'widok_sql';
        
        const SESSION_WAKATY_UMOWIENI       = 'wakaty_umowieni';
        const SESSION_WAKATY_ZAINTERESOWANI = 'wakaty_zainteresowani';
        const SESSION_WAKATY_APLIKUJACY     = 'wakaty_aplikujacy';
        
        private $utilsUI, $dalWakaty, $bllZatrudnienie;
        
        public function __construct ()
        {
            $this->actionList = array(
               
            );
            
            $this->dalWakaty = new DALWakaty();
            $this->bllZatrudnienie = new BLLZatrudnienie();
            
            parent::__construct();

            unset($_SESSION[VacatsView::SESSION_KWERENDA_SQL]);
            unset($_SESSION[VacatsView::SESSION_SZUKAJ_SQL]);
            unset($_SESSION[VacatsView::SESSION_EDYCJA_MASOWA]);
            unset($_SESSION[VacatsView::SESSION_WIDOK_SQL]);
            
            $this->utilsUI = new UtilsUI('', 'id_os');
        }
        
        public function run() {
            
            $html = '';
            
            $vacatId = (int)$_GET['wakat'];
            if ($vacatId > 0)
            {
                $html .= $this->getVacats($vacatId);
            }
            
            return $html;
        }
        
        private function getVacats($vacatId) 
        {
            $umowieni = $this->getVacatData($vacatId, ID_DECYZJA_UMOWIONY);
            $zainteresowani = $this->getVacatData($vacatId, ID_DECYZJA_ZAINTERESOWANY);
            $aplikujacy = $this->getVacatData($vacatId, ID_DECYZJA_APLIKUJACY);

            $vacatData = $this->dalWakaty->get($vacatId);
            $vacat = $vacatData[Model::RESULT_FIELD_DATA][0];
            $vacatDepartmentId = $vacat[Model::COLUMN_WAK_ID_ODDZIAL];
            
            $html = '';
            
            $html .= $this->htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
                onclick="var url = \'../prawa_strona/wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'='.$vacatDepartmentId.'\';
                window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"', 'button');
            
            $html .= '<form action="edit/przetwarzaj_dane_osobowe.php" method="GET">';
            $html .= $this->htmlControls->_AddHidden('id_id_os', 'id_os', '');
            
            
            
            return $html;
        }
        
        private function getVacatData ($vacatId, $decisionId)
        {
            $html = '';
            
            try {
                $data = $this->bllZatrudnienie->getEmployPretendents($vacatId, $decisionId);
                
            } catch (ProjectLogicException $e) {
                $html .= $e->getCustomMessage();
            }
            
            return $html;
        }
    } 
    
    CommonUtils::SessionStart();
    
    try {
        $output = new VacatsView();
        
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
    
    
    /*
  <HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
     */
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        $controls = new valControl();
        
        unset($_SESSION['edycja_masowa']);
        unset($_SESSION['kwerenda_sql']);
        unset($_SESSION['szukaj_sql']);
        unset($_SESSION['widok_sql']);
	    if ((isset($_GET['wakat'])) && ($_GET['wakat']) != "--------")
	    {
            $vacatDetailsQuery = 'select wakat.data_wyjazdu, wakat.id_oddzial from wakat where wakat.id = '.(int)$_GET['wakat'];
            
            $vacatDetailsData = $controls->dalObj->PobierzDane($vacatDetailsQuery);
            
            if (!isset($vacatDetailsData[0])) {
                
                die('Nie znaleziony wakat.');
            }
            
            $vacatDetails = $vacatDetailsData[0];
            $vacatDepartureDate = $vacatDetails['data_wyjazdu'];
            $vacatDepartmentId = $vacatDetails['id_oddzial'];
            
		    $n = "Id, Imie, Nazwisko, P³eæ, Data urodzenia, Nr paszportu, Nr sofi, Bank, Jêzyki, Konsultant, Status, Decyzja, Ilosc tygodni, Zadania dnia, Uzgodnienia, Pasywny";
		    $z = "SELECT dane_osobowe.id, dane_osobowe.imie, dane_osobowe.nazwisko, plec.nazwa as plec, 
            dane_osobowe.data_urodzenia, dokumenty.pass_nr, dokumenty.nip, bank.nazwa AS bank, 
            uprawnienia.imie_nazwisko, status.nazwa AS status, decyzja.nazwa as decyzja, 
            zatrudnienie.ilosc_tyg, wakat.id as id_wakatu
            FROM dane_osobowe
            JOIN plec ON dane_osobowe.id_plec = plec.id
            LEFT JOIN dokumenty ON dane_osobowe.id = dokumenty.id
            JOIN zatrudnienie ON zatrudnienie.id_osoba = dane_osobowe.id
            JOIN wakat on wakat.id = zatrudnienie.id_wakat
            JOIN uprawnienia ON zatrudnienie.id_pracownik = uprawnienia.id
            JOIN status ON zatrudnienie.id_status = status.id
            JOIN decyzja ON zatrudnienie.id_decyzja = decyzja.id
            LEFT JOIN bank ON dokumenty.id_bank = bank.id ";

		    $_SESSION['wakaty_umowieni'] = isset($_SESSION['wakaty_umowieni']) ? $_SESSION['wakaty_umowieni'] : null;
            
		    $zapytanie = $z."where zatrudnienie.id_decyzja = ".ID_DECYZJA_UMOWIONY." and zatrudnienie.data_wyjazdu = '".$vacatDepartureDate."' and zatrudnienie.id_oddzial = ".$vacatDepartmentId." order by dane_osobowe.id asc;";
            
            $sql_tmp = trim(substr($zapytanie, 0, strpos($zapytanie, "order by")));
            $sql_ses = trim(substr($_SESSION['wakaty_umowieni'], 0, strpos($_SESSION['wakaty_umowieni'], "order by")));
            if (($sql_ses != "") && ($sql_tmp != ""))
            {
                $wynik = strcmp($sql_ses, $sql_tmp);
                if ($wynik == 0)
                    $zapytanie = trim(str_replace(";", " ", $_SESSION['wakaty_umowieni']));
            }
            $_SESSION['wakaty_umowieni'] = $zapytanie;

            
            
            echo $controls->_AddSubmit('', '', 'Wewnetrzny opis pracy', '', '
                onclick="var url = \'../prawa_strona/wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'='.$vacatDepartmentId.'\';
                window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"', 'button');
            
		    echo "<form action='edit/przetwarzaj_dane_osobowe.php' method='GET'>";
            echo $controls->AddHidden('id_id_os', 'id_os', '');
		    $wynik = $controls->dalObj->PobierzDane($zapytanie, $ilosc_wierszy);
		    echo '<table class="gridTable" cellspacing="0">';
            echo valControl::_RowsCount($ilosc_wierszy);
		    echo '<tr>';
		    $n1 = explode(",", $n);
		    require("buttons_nag.php");
		    for ($j = 0; $j < count($n1); $j++)
		    {
			    echo "<th nowrap align='CENTER'>";
			    echo $n1[$j];
			    echo '</th>';
		    }		
		    echo "</tr>";
            $count = 0;
            if (is_array($wynik))
		    foreach ($wynik as $wiersz)
		    {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
			    echo '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
			    require("buttons.php");
                $i = 0;
                $wakat_id = array_pop($wiersz);
	    	    foreach ($wiersz as $key => $kolumna)
                {
				    if ($i == 8)
				    {
					    $zp = "select jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where znane_jezyki.id = '".$wiersz['id']."' order by jezyk asc;";	
					    $q = $controls->dalObj->PobierzDane($zp, $ilosc_jezykow);
					    echo "<td nowrap align='CENTER'>";
					    echo("<table>");
                        if ($ilosc_jezykow > 0)
					    foreach ($q as $w)
					    {
						    echo("<tr><td nowrap>{$w['jezyk']}: {$w['poziom']}</td></tr>");
					    }
                        if ($ilosc_jezykow == 0)
                        {
                            echo "<td nowrap align='CENTER'>";
                            echo '--------';
                            echo("</td>");
                        }
					    echo("</table>");
					    echo "</td>";
					    echo "<td nowrap align='CENTER'>";
                        if ($wiersz[$key] != "")
                        {
                            echo $wiersz[$key];
                        }
                        else
                        {
                            echo '--------';   
                        }
					    echo "</td>";
				    }
				    else
				    {
					    echo "<td nowrap align='CENTER'>";
                        if ($wiersz[$key] != "")
                        {
                            echo $wiersz[$key];
                        }
                        else
                        {
                            echo '--------';   
                        }
					    echo "</td>";
				    }
                    $i++;
                }
                $zapytanie_zadania_dnia = "select data, problem from zadania_dnia where data_wpisu = (select max(data_wpisu) from zadania_dnia where id = '".$wiersz['id']."' and active = 'true') and id = '".$wiersz['id']."';";
                $query_zadania_dnia = $controls->dalObj->PobierzDane($zapytanie_zadania_dnia, $ilosc_zadan_dnia);
                if ($ilosc_zadan_dnia == 1)
                {
                    $row_zadania_dnia = $query_zadania_dnia[0];
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['data']}");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['problem']}");
                    echo("</td>");
                }
                else if ($ilosc_zadan_dnia == 0)
                {
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                }
                echo "<td nowrap align='CENTER'>";
                if ($wakat_id != "1")
                {
                    echo("Nie");
                }
                else
                {
                    echo("Tak");
                }
                echo("</td>");
			    echo "</tr>";
		    }
		    echo("</table>");
		    echo("<hr align='CENTER' />");
		    
            $_SESSION['wakaty_zainteresowani'] = isset($_SESSION['wakaty_zainteresowani']) ? $_SESSION['wakaty_zainteresowani'] : null;
		    $zapytanie = $z."where zatrudnienie.id_decyzja = ".ID_DECYZJA_ZAINTERESOWANY." and zatrudnienie.data_wyjazdu = '".$vacatDepartureDate."' and zatrudnienie.id_oddzial = ".$vacatDepartmentId." order by dane_osobowe.id asc;";
            $sql_tmp = trim(substr($zapytanie, 0, strpos($zapytanie, "order by")));
            $sql_ses = trim(substr($_SESSION['wakaty_zainteresowani'], 0, strpos($_SESSION['wakaty_zainteresowani'], "order by")));
            if (($sql_ses != "") && ($sql_tmp != ""))
            {
                $wynik = strcmp($sql_ses, $sql_tmp);
                if ($wynik == 0)
                    $zapytanie = trim(str_replace(";", " ", $_SESSION['wakaty_zainteresowani']));
            }
            $_SESSION['wakaty_zainteresowani'] = $zapytanie;
		    //echo("$zapytanie");
		    $wynik = $controls->dalObj->PobierzDane($zapytanie, $zainteresowanych);
            
            echo '<table class="gridTable" cellspacing="0">';
            echo valControl::_RowsCount($zainteresowanych);
		    
		    echo '<tr>';
		    $n1 = explode(",", $n);
		    require("buttons_nag.php");
		    for ($j = 0; $j < count($n1); $j++)
		    {
			    echo "<th nowrap align='CENTER'>";
			    echo $n1[$j];
			    echo("</th>");
		    }		
		    echo '</tr>';
            $count = 0;
            $LicznikLiczPorz = 0;
            
            if (is_array($wynik))
		    foreach ($wynik as $wiersz)
		    {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
			    echo '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
			    require("buttons.php");
                
	    	    foreach ($wiersz as $klucz => $kolumna)
                {
                    if ($klucz == 'id_wakatu')
                        continue;
                        
				    if ($klucz == 'imie_nazwisko')
				    {
					    $zp = "select jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where znane_jezyki.id = '".$wiersz['id']."' order by jezyk asc;";	
					    $q = $controls->dalObj->PobierzDane($zp, $iloscJezykow);
					    echo "<td nowrap align='CENTER'>";
					    echo("<table>");
                        if ($iloscJezykow > 0)
					    foreach ($q as $w)
					    {
						    echo("<tr><td nowrap>{$w['jezyk']}: {$w['poziom']}</td></tr>");
					    }
                        if ($iloscJezykow == 0)
                        {
                            echo "<td nowrap align='CENTER'>";
                            echo("--------");
                            echo("</td>");
                        }
					    echo("</table>");
					    echo "</td>";
					    echo "<td nowrap align='CENTER'>";
                        if ($kolumna != "")
                        {
                            echo $kolumna;
                        }
                        else
                        {
                            echo("--------");   
                        }
					    echo "</td>";
				    }
				    else
				    {
					    echo "<td nowrap align='CENTER'>";
                        if ($kolumna != "")
                        {
                            echo $kolumna;
                        }
                        else
                        {
                            echo("--------");   
                        }
					    echo "</td>";
				    }
                }
                $zapytanie_zadania_dnia = "select data, problem from zadania_dnia where data_wpisu = (select max(data_wpisu) from zadania_dnia where id = '".$wiersz['id']."' and active = 'true') and id = '".$wiersz['id']."';";
                $query_zadania_dnia = $controls->dalObj->PobierzDane($zapytanie_zadania_dnia, $zadanDnia);
                
                if ($zadanDnia == 1)
                {
                    $row_zadania_dnia = $query_zadania_dnia[0];
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['data']}");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['problem']}");
                    echo("</td>");
                }
                else if ($zadanDnia == 0)
                {
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                }
                echo "<td nowrap align='CENTER'>";
                if ($wiersz['id_wakatu'] != "1")
                {
                    echo("Nie");
                }
                else
                {
                    echo("Tak");
                }
                echo("</td>");
			    echo "</tr>";
		    }
		    echo("</table>");
		    
		    echo("<hr align='CENTER' />");
		    
            $_SESSION['wakaty_aplikujacy'] = isset($_SESSION['wakaty_aplikujacy']) ? $_SESSION['wakaty_aplikujacy'] : null;
		    $zapytanie = $z."where zatrudnienie.id_decyzja = ".ID_DECYZJA_APLIKUJACY." and zatrudnienie.data_wyjazdu = '".$vacatDepartureDate."' and zatrudnienie.id_oddzial = ".$vacatDepartmentId." order by dane_osobowe.id asc;";
            $sql_tmp = trim(substr($zapytanie, 0, strpos($zapytanie, "order by")));
            $sql_ses = trim(substr($_SESSION['wakaty_aplikujacy'], 0, strpos($_SESSION['wakaty_aplikujacy'], "order by")));
            if (($sql_ses != "") && ($sql_tmp != ""))
            {
                $wynik = strcmp($sql_ses, $sql_tmp);
                if ($wynik == 0)
                    $zapytanie = trim(str_replace(";", " ", $_SESSION['wakaty_aplikujacy']));
            }
            $_SESSION['wakaty_aplikujacy'] = $zapytanie;
		    //echo("$zapytanie");
		    $wynik = $controls->dalObj->PobierzDane($zapytanie, $aplikujacych);
            
            echo '<table class="gridTable" cellspacing="0">';
            echo valControl::_RowsCount($aplikujacych);
		    
		    echo '<tr>';
		    $n1 = explode(",", $n);
		    require("buttons_nag.php");
		    for ($j = 0; $j < count($n1); $j++)
		    {
			    echo "<th nowrap align='CENTER'>";
			    echo $n1[$j];
			    echo("</th>");
		    }		
		    echo '</tr>';
            $count = 0;
            $LicznikLiczPorz = 0;
            
            if (is_array($wynik))
		    foreach ($wynik as $wiersz)
		    {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
			    echo '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
			    require("buttons.php");
                
	    	    foreach ($wiersz as $klucz => $kolumna)
                {
                    if ($klucz == 'id_wakatu')
                        continue;
                        
				    if ($klucz == 'imie_nazwisko')
				    {
					    $zp = "select jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where znane_jezyki.id = '".$wiersz['id']."' order by jezyk asc;";	
					    $q = $controls->dalObj->PobierzDane($zp, $iloscJezykow);
					    echo "<td nowrap align='CENTER'>";
					    echo("<table>");
                        if ($iloscJezykow > 0)
					    foreach ($q as $w)
					    {
						    echo("<tr><td nowrap>{$w['jezyk']}: {$w['poziom']}</td></tr>");
					    }
                        if ($iloscJezykow == 0)
                        {
                            echo "<td nowrap align='CENTER'>";
                            echo("--------");
                            echo("</td>");
                        }
					    echo("</table>");
					    echo "</td>";
					    echo "<td nowrap align='CENTER'>";
                        if ($kolumna != "")
                        {
                            echo $kolumna;
                        }
                        else
                        {
                            echo("--------");   
                        }
					    echo "</td>";
				    }
				    else
				    {
					    echo "<td nowrap align='CENTER'>";
                        if ($kolumna != "")
                        {
                            echo $kolumna;
                        }
                        else
                        {
                            echo("--------");   
                        }
					    echo "</td>";
				    }
                }
                $zapytanie_zadania_dnia = "select data, problem from zadania_dnia where data_wpisu = (select max(data_wpisu) from zadania_dnia where id = '".$wiersz['id']."' and active = 'true') and id = '".$wiersz['id']."';";
                $query_zadania_dnia = $controls->dalObj->PobierzDane($zapytanie_zadania_dnia, $zadanDnia);
                
                if ($zadanDnia == 1)
                {
                    $row_zadania_dnia = $query_zadania_dnia[0];
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['data']}");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("{$row_zadania_dnia['problem']}");
                    echo("</td>");
                }
                else if ($zadanDnia == 0)
                {
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                    echo "<td nowrap align='CENTER'>";
                    echo("--------");
                    echo("</td>");
                }
                echo "<td nowrap align='CENTER'>";
                if ($wiersz['id_wakatu'] != "1")
                {
                    echo("Nie");
                }
                else
                {
                    echo("Tak");
                }
                echo("</td>");
			    echo "</tr>";
		    }
		    echo("</table></form>");
	    }
        
        require("stopka.php");
    }
?>
</html>
