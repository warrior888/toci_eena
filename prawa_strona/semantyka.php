<?php
    require_once '../conf.php';
    //CommonUtils::outputBufferingOn();
    //CommonUtils::SessionStart();

    //echo '<html>';
    //HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    //echo '<body>';
    
    class SemanticsView extends View {}
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();

        $id_osoba = Utils::PodajIdOsoba();
        //echo("{$id_osoba}");
        $query = "select id,id_imie,nazwisko from dane_osobowe WHERE id = '".$id_osoba."';";
		$database = pg_connect($con_str);
		$wynik = pg_query($database, $query);
		
		$settlementsHtml = '';
		
		if (pg_num_rows($wynik) == 0)
		{
			$settlementsHtml .= "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{
		    $semanticsView = new SemanticsView();
		    
	        if (isset($_POST['dodaj_ustalenia']))
	        {
		        $co = addslashes($_POST['ustalenia']);
		        if (trim($co) != "")
		        {
			        $zapytanie = "insert into semantyka values ('".$id_osoba."', '".date("Y-m-d")."', (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".$co."');";
			        $database = pg_connect($con_str);
			        $result = pg_query($database, $zapytanie);
		        }
		        
		        return View::postSuccessfull($_SERVER['REQUEST_URI']);
	        }
	        
            $settlementsHtml .= '<div style="float: left;">'.$semanticsView->addFormPostPre($_SERVER['REQUEST_URI']).'<table>';
            $settlementsHtml .= '</td></tr></table><table><tr><td>Ustalenia: </td></tr><tr><td><textarea wrap="ON" cols="35" rows="5" maxlength="300" name="ustalenia" class="formfield"></textarea></td></tr></table>';
            
	        $zapytanie1 = "select imie_nazwisko from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
	        $result1 = pg_query($database, $zapytanie1);
	        $row1 = pg_fetch_array($result1);
	        
            $settlementsHtml .= ("<table><tr><td>Konsultant: ".$row1['imie_nazwisko']."</td></tr></table>");
            $settlementsHtml .= ("<table><tr><td>Data wpisu: ".date("Y - m - d")."</td></tr>");
            $settlementsHtml .= ("</table>");
            $settlementsHtml .=  "<table><tr><td>";
            $settlementsHtml .=  $controls->AddSubmit('dodaj_ustalenia', 'id_dodaj_ustalenia', 'Dodaj', '');
            $settlementsHtml .=  '</td></tr></table></form></div>';
            
	        $zapytanie2 = "select semantyka.data_kontaktu as data, uprawnienia.imie_nazwisko as konsultant, semantyka.ustalenia from semantyka join uprawnienia on semantyka.id_konsultant = uprawnienia.id where semantyka.id = '".$id_osoba."' order by semantyka.data_kontaktu desc;";
	        $result2 = pg_query($database, $zapytanie2);
	        $ile = pg_num_rows($result2);
	        while ($row2 = pg_fetch_array($result2))
	        {
                $data[] = $row2['data'];
                $konsultant[] = $row2['konsultant'];
                $u[] = $row2['ustalenia'];
            }
	        $settlementsHtml .= '<div style="float: left;">'.$semanticsView->addFormPostPre($_SERVER['REQUEST_URI']).'<table><tr>';
            $settlementsHtml .= $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            $settlementsHtml .= $controls->AddHidden('id_ile', 'ile', '');
	        if ($ile != 0)
            {
                for ($j = 0; $j < ($ile / 5); $j++)
                {
                    $settlementsHtml .= "<td>";
                    $settlementsHtml .= $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                    $settlementsHtml .= "</td>";
                }
                $settlementsHtml .= '</tr></table>';
                if (isset($_POST['ile']))
                {
                    $settlementsHtml .= '<table class="gridTable" border="0">';
                    $p = $_POST['ile'] * 5;
                    $k = $_POST['ile'] * 5 + 5;
                    if ($k > $ile)
                    {
                        $k = $ile;
                    }
		            $settlementsHtml .= ("<tr><th nowrap>Lp.</th><th nowrap>Data wpisu</th><th nowrap>Konsultant</th><th nowrap>Ustalenia</th></tr>");
		            $count = 0;
                    for ($i = $p; $i < $k; $i++)
                    {
                        $count++;
                        $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                        $settlementsHtml .= ("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td>".$u[$i]."</td></tr>");
                    }
                    $settlementsHtml .= ("</table>");
                }
                else
                {
                    $settlementsHtml .= '<table class="gridTable" border="0" cellspacing="0">';
                    $_POST['ile'] = isset($_POST['ile']) ? $_POST['ile'] : 0;
                    $p = $_POST['ile'] * 5;
                    $k = $_POST['ile'] * 5 + 5;
                    if ($k > $ile)
                    {
                        $k = $ile;
                    }
		            $settlementsHtml .= ("<tr><th nowrap>Lp.</th><th nowrap>Data wpisu</th><th nowrap>Konsultant</th><th nowrap>Ustalenia</th></tr>");
                    $count = 0;
                    for ($i = $p; $i < $k; $i++)
                    {
                        $count++;
                        $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow'; 
                        $settlementsHtml .= ("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td>".$u[$i]."</td></tr>");
                    }
                    $settlementsHtml .= ("</table>");
                }
            }
	        $settlementsHtml .= ("</form>");
	        $settlementsHtml .= "<table><tr><td>";
            //$settlementsHtml .= $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
            $settlementsHtml .= '</td></tr></table></div>';
        }
    }
    
    //CommonUtils::sendOutputBuffer();