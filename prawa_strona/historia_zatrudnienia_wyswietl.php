<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
        
        $id_osoba = Utils::PodajIdOsoba();
        
        $database = pg_connect($con_str);
        $zapytanie1 = "select klient.nazwa as klient, data_wyjazdu, data_powrotu, ilosc_tyg, uprawnienia.nazwa_uzytkownika as uzytkownik, 
	    msc_odjazdu.nazwa as msc, status.nazwa as status from zatrudnienie join klient on klient.id = zatrudnienie.id_klient 
	    join uprawnienia on uprawnienia.id = zatrudnienie.id_pracownik join status on status.id = zatrudnienie.id_status 
	    join msc_odjazdu on msc_odjazdu.id = zatrudnienie.id_msc_odjazd
	    where zatrudnienie.id_osoba = '".$id_osoba."' order by data_wyjazdu;";

		$result1 = pg_query($database, $zapytanie1);
		$ile = pg_num_rows($result1);
		while ($row1 = pg_fetch_array($result1))
		{
            $klient[] = $row1['klient'];
            $data_wyjazdu[] = $row1['data_wyjazdu'];
            $data_powrotu[] = $row1['data_powrotu'];
            $ilosc_tyg[] = $row1['ilosc_tyg'];
	        $uzytkownik[] = $row1['uzytkownik'];
        	$status[] = $row1['status'];
        	$msc[] = $row1['msc'];
        }
        echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table align = \"CENTER\"><tr>");
        echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
        echo $controls->AddHidden('id_ile', 'ile', '');
        if ($ile != 0)
        {
            for ($j = 0; $j < ($ile / 10); $j++)
            {
                   	echo "<td>";
                    echo $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                    echo "</td>";
            }
            echo("</tr></table>");
            if (isset($_POST['ile']))
            {
                   	echo '<table class="gridTable" border="0" cellspacing="0">';
                   	$p = $_POST['ile'] * 10;
		            $k = $_POST['ile'] * 10 + 10;
                 	if ($k > $ile)
		            {
                		    $k = $ile;
                    }
                    $count = 0;
                    for ($i = $p; $i < $k; $i++)
		            {
                        $count++;
                        $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
		                   echo("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$klient[$i]."</td><td nowrap>".$data_wyjazdu[$i]."
					       </td><td nowrap>".$data_powrotu[$i]."</td><td nowrap>".$ilosc_tyg[$i]."</td><td nowrap>".$uzytkownik[$i]."
					       </td><td nowrap>".$status[$i]."</td><td>".$msc[$i]."</td></tr>");
                	}
                    echo("</table>");
            }
            else
            {
                echo '<table class="gridTable" border="0" cellspacing="0">';
                $p = 0;
                $k = 10;
                if ($k > $ile)
                {
                    $k = $ile;
                }
                $count = 0;
                for ($i = $p; $i < $k; $i++)
                {
                    $count++;
                    $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
		    	    echo("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$klient[$i]."</td><td nowrap>".$data_wyjazdu[$i]."
			        </td><td nowrap>".$data_powrotu[$i]."</td><td nowrap>".$ilosc_tyg[$i]."</td><td nowrap>".$uzytkownik[$i]."
			        </td><td nowrap>".$status[$i]."</td><td>".$msc[$i]."</td></tr>");
                }
                echo("</table>");
            }
        }
        echo '</form>';
        if (isset($_SESSION['edycja_rekordu']))
        {
            echo "<form method='POST' action='../edit/edytuj_ostatni_wakat.php'>";
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddSubmit('edit_ost_wakat', 'id_edit_ost_wakat', 'Edytuj ostatni okres zatrudnienia.', '');
            echo "</form>";
            echo "<form method='POST' action='../dodawanie/wprowadz_do_pas.php'>";
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddSubmit('wpr_do_pas', 'id_wpr_do_pas', 'Wprowad¼ na listê pasywnych.', '');
            echo "</form>";
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>